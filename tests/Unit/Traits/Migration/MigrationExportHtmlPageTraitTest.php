<?php

namespace Tests\Unit\Traits\Migration;

use App\Traits\Migration\MigrationExportHtmlPageTrait;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MigrationExportHtmlPageTraitTest extends TestCase
{
    /**
     * 非グローバルURLは事前検証で拒否し、HTTPヘルパーを呼ばないこと
     *
     * @return void
     */
    public function testNonGlobalUrlIsNotImported()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportHtmlPageTraitTarget();

        $target->runMigrationHtmlPage('http://127.0.0.1/test/index.html', 1001);

        $this->assertSame([], $target->requestedGetUrls());
        $this->assertFrameNotSaved(1001);
    }

    /**
     * グローバルURLはHTTPヘルパーを呼び、HTMLを保存すること
     *
     * @return void
     */
    public function testGlobalUrlHtmlIsImported()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportHtmlPageTraitTarget();
        $target->queueGetResponse([
            'body' => '<html><body><p>ok</p></body></html>',
            'http_code' => 200,
            'location' => '',
            'content_disposition' => '',
        ]);

        $url = 'http://8.8.8.8/source/index.html';
        $page_id = 1002;
        $target->runMigrationHtmlPage($url, $page_id);

        $this->assertSame([$url], $target->requestedGetUrls());
        $this->assertFrameSavedContains($page_id, '<p>ok</p>');
    }

    /**
     * DOMDocumentの解析警告が発生するHTMLでも取り込みを継続して保存すること
     *
     * @return void
     */
    public function testImportContinuesWhenDomDocumentEmitsHtmlParseWarnings()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportHtmlPageTraitTarget();
        $target->queueGetResponse([
            'body' => '<html><body><p>before &nobr; after</p><p>ok</p></body></html>',
            'http_code' => 200,
            'location' => '',
            'content_disposition' => '',
        ]);

        $url = 'http://8.8.8.8/source/invalid-entity.html';
        $page_id = 1006;

        $target->runMigrationHtmlPage($url, $page_id);

        $this->assertSame([$url], $target->requestedGetUrls());
        $this->assertFrameSavedContains($page_id, '<p>ok</p>');
    }

    /**
     * リダイレクト先が非グローバルURLなら保存せずに中断すること
     *
     * @return void
     */
    public function testImportStopsWhenRedirectDestinationIsNonGlobal()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportHtmlPageTraitTarget();
        $target->queueGetResponse([
            'body' => '',
            'http_code' => 302,
            'location' => 'http://127.0.0.1/internal/index.html',
            'content_disposition' => '',
        ]);

        $url = 'http://8.8.8.8/start/index.html';
        $page_id = 1003;
        $target->runMigrationHtmlPage($url, $page_id);

        $this->assertSame([$url], $target->requestedGetUrls());
        $this->assertFrameNotSaved($page_id);
    }

    /**
     * グローバルなリダイレクト先には追従してHTMLを保存すること
     *
     * @return void
     */
    public function testImportFollowsGlobalRedirectAndSavesHtml()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportHtmlPageTraitTarget();
        $target->queueGetResponse([
            'body' => '',
            'http_code' => 302,
            'location' => '/moved/index.html',
            'content_disposition' => '',
        ]);
        $target->queueGetResponse([
            'body' => '<html><body><h1>redirected</h1></body></html>',
            'http_code' => 200,
            'location' => '',
            'content_disposition' => '',
        ]);

        $start_url = 'http://8.8.8.8/start/index.html';
        $page_id = 1004;
        $target->runMigrationHtmlPage($start_url, $page_id);

        $this->assertSame([
            'http://8.8.8.8/start/index.html',
            'http://8.8.8.8/moved/index.html',
        ], $target->requestedGetUrls());
        $this->assertFrameSavedContains($page_id, '<h1>redirected</h1>');
    }

    /**
     * 画像ダウンロード失敗時は画像をスキップし、本文の保存は継続すること
     *
     * @return void
     */
    public function testImportSkipsImageWhenImageDownloadFailsAndStillSavesBody()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportHtmlPageTraitTarget();
        $image_url = 'http://8.8.8.8/files/missing.png';
        $target->queueGetResponse([
            'body' => '<html><body><p><img src="' . $image_url . '" alt="img"></p><p>KeepBody</p></body></html>',
            'http_code' => 200,
            'location' => '',
            'content_disposition' => '',
        ]);
        $target->queueDownloadException(new \RuntimeException('image download failed'));

        $page_id = 1005;
        $target->runMigrationHtmlPage('http://8.8.8.8/source/image-fail.html', $page_id);

        $this->assertSame(['http://8.8.8.8/source/image-fail.html'], $target->requestedGetUrls());
        $this->assertSame([$image_url], $target->requestedDownloadUrls());

        $html = Storage::get("migration/import/pages/{$page_id}/frame_0001.html");
        $ini = Storage::get("migration/import/pages/{$page_id}/frame_0001.ini");

        $this->assertStringContainsString('KeepBody', $html);
        $this->assertStringContainsString($image_url, $html);
        $this->assertStringNotContainsString('frame_0001_1.', $html);
        $this->assertStringContainsString('[image_names]', $ini);
        $this->assertStringNotContainsString('frame_0001_1', $ini);
    }

    /**
     * 移行用ストレージをfake化する
     *
     * @return void
     */
    private function fakeMigrationStorage(): void
    {
        Storage::fake(config('filesystems.default', 'local'));
    }

    /**
     * フレームHTML/INIが保存されていないことを検証する
     *
     * @param int $page_id
     * @return void
     */
    private function assertFrameNotSaved(int $page_id): void
    {
        Storage::assertMissing("migration/import/pages/{$page_id}/frame_0001.html");
        Storage::assertMissing("migration/import/pages/{$page_id}/frame_0001.ini");
    }

    /**
     * フレームHTML/INIが保存され、HTML内に期待文字列を含むことを検証する
     *
     * @param int $page_id
     * @param string $expected_fragment
     * @return void
     */
    private function assertFrameSavedContains(int $page_id, string $expected_fragment): void
    {
        Storage::assertExists("migration/import/pages/{$page_id}/frame_0001.html");
        Storage::assertExists("migration/import/pages/{$page_id}/frame_0001.ini");
        $this->assertStringContainsString($expected_fragment, Storage::get("migration/import/pages/{$page_id}/frame_0001.html"));
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- Test double is intentionally colocated with the test.
class TestableMigrationExportHtmlPageTraitTarget
{
    use MigrationExportHtmlPageTrait {
        migrationHtmlPage as private traitMigrationHtmlPage;
    }

    /** @var array */
    private $http_get_calls = [];

    /** @var array */
    private $queued_get_responses = [];

    /** @var array */
    private $download_calls = [];

    /** @var array */
    private $queued_download_results = [];

    public function runMigrationHtmlPage(string $url, int $page_id): void
    {
        $this->traitMigrationHtmlPage($url, $page_id);
    }

    public function queueGetResponse(array $response): void
    {
        $this->queued_get_responses[] = $response;
    }

    /**
     * ダウンロード例外をキューに積む
     *
     * @param \RuntimeException $exception
     * @return void
     */
    public function queueDownloadException(\RuntimeException $exception): void
    {
        $this->queued_download_results[] = ['type' => 'exception', 'value' => $exception];
    }

    /**
     * GETリクエストされたURL一覧を返す
     *
     * @return array
     */
    public function requestedGetUrls(): array
    {
        return $this->http_get_calls;
    }

    /**
     * ダウンロードURL一覧を返す
     *
     * @return array
     */
    public function requestedDownloadUrls(): array
    {
        return $this->download_calls;
    }

    protected function migrationHttpCreateClient(array $http_options = []): Client
    {
        return new Client();
    }

    protected function migrationHttpGet(Client $http_client, string $url, array $http_options = []): array
    {
        $this->http_get_calls[] = $url;

        if (empty($this->queued_get_responses)) {
            throw new \RuntimeException('No queued migrationHttpGet response.');
        }

        return array_shift($this->queued_get_responses);
    }

    protected function migrationHttpDownloadToFile(Client $http_client, string $url, string $sink_path, array $http_options = []): array
    {
        $this->download_calls[] = $url;

        if (empty($this->queued_download_results)) {
            throw new \RuntimeException('Unexpected migrationHttpDownloadToFile call in this test.');
        }

        $queued = array_shift($this->queued_download_results);
        if ($queued['type'] === 'exception') {
            throw $queued['value'];
        }

        throw new \RuntimeException('Unsupported queued download result type.');
    }
}
