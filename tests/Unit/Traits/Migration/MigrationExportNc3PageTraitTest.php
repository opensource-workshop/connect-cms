<?php

namespace Tests\Unit\Traits\Migration;

use App\Traits\Migration\MigrationExportNc3PageTrait;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class MigrationExportNc3PageTraitTest extends TestCase
{
    /**
     * @var int[]
     */
    private $real_storage_page_ids_to_cleanup = [2005, 2006, 2007, 2008];

    protected function tearDown(): void
    {
        foreach ($this->real_storage_page_ids_to_cleanup as $page_id) {
            File::deleteDirectory(storage_path("app/migration/import/pages/{$page_id}"));
        }

        parent::tearDown();
    }

    /**
     * 非グローバルURLは事前検証で拒否し、インポートしないこと
     *
     * @return void
     */
    public function testNonGlobalUrlIsNotImported()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportNc3PageTraitTarget();

        $target->runMigrationNc3Page('http://127.0.0.1/nc3/index.html', 2001);

        $this->assertSame([], $target->requestedGetUrls());
        $this->assertFrameNotSaved(2001, 1);
    }

    /**
     * ページ取得失敗時は中断し、フレームを保存しないこと
     *
     * @return void
     */
    public function testImportStopsWhenPageFetchFails()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportNc3PageTraitTarget();
        $target->queueGetException(new RuntimeException('network failed'));

        $url = 'http://8.8.8.8/nc3/index.html';
        $target->runMigrationNc3Page($url, 2002);

        $this->assertSame([$url], $target->requestedGetUrls());
        $this->assertFrameNotSaved(2002, 1);
    }

    /**
     * NC3の1フレームをConnect-CMS形式として保存できること
     *
     * @return void
     */
    public function testSingleNc3SectionIsImportedAsSingleFrame()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportNc3PageTraitTarget();
        $target->queueGetResponse([
            'body' => $this->buildNc3Html([
                [
                    'id' => 'frame-101',
                    'class' => 'panel panel-primary',
                    'title' => 'Notice',
                    'content' => '<p>BodyA</p>',
                ],
            ]),
            'http_code' => 200,
            'location' => '',
            'content_disposition' => '',
        ]);

        $url = 'http://8.8.8.8/nc3/index.html';
        $page_id = 2003;
        $target->runMigrationNc3Page($url, $page_id);

        $this->assertSame([$url], $target->requestedGetUrls());
        $this->assertNc3FrameSavedContains($page_id, 1, '<p>BodyA</p>');
        $this->assertStringContainsString('frame_title = "Notice"', Storage::get("migration/import/pages/{$page_id}/frame_0001.ini"));
        $this->assertStringContainsString('source_key = "101"', Storage::get("migration/import/pages/{$page_id}/frame_0001.ini"));
    }

    /**
     * DOMDocumentの解析警告が発生するNC3 HTMLでも取り込みを継続して保存すること
     *
     * @return void
     */
    public function testImportContinuesWhenDomDocumentEmitsHtmlParseWarnings()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportNc3PageTraitTarget();
        $target->queueGetResponse([
            'body' => $this->buildNc3Html([
                [
                    'id' => 'frame-111',
                    'class' => 'panel panel-primary',
                    'title' => 'WarnHtml',
                    'content' => '<p>before &nobr; after</p><p>BodyWarn</p>',
                ],
            ]),
            'http_code' => 200,
            'location' => '',
            'content_disposition' => '',
        ]);

        $url = 'http://8.8.8.8/nc3/warn.html';
        $page_id = 2009;
        $target->runMigrationNc3Page($url, $page_id);

        $this->assertSame([$url], $target->requestedGetUrls());
        $this->assertNc3FrameSavedContains($page_id, 1, '<p>BodyWarn</p>');
    }

    /**
     * 複数セクションを複数フレームとして保存できること
     *
     * @return void
     */
    public function testMultipleNc3SectionsAreImportedAsMultipleFrames()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportNc3PageTraitTarget();
        $target->queueGetResponse([
            'body' => $this->buildNc3Html([
                [
                    'id' => 'frame-201',
                    'class' => 'panel panel-info',
                    'title' => 'Frame1',
                    'content' => '<p>Body1</p>',
                ],
                [
                    'id' => 'frame-202',
                    'class' => 'panel panel-success',
                    'title' => 'Frame2',
                    'content' => '<p>Body2</p>',
                ],
            ]),
            'http_code' => 200,
            'location' => '',
            'content_disposition' => '',
        ]);

        $page_id = 2004;
        $target->runMigrationNc3Page('http://8.8.8.8/nc3/multi.html', $page_id);

        $this->assertNc3FrameSavedContains($page_id, 1, '<p>Body1</p>');
        $this->assertNc3FrameSavedContains($page_id, 2, '<p>Body2</p>');
        $this->assertStringContainsString('frame_title = "Frame1"', Storage::get("migration/import/pages/{$page_id}/frame_0001.ini"));
        $this->assertStringContainsString('frame_title = "Frame2"', Storage::get("migration/import/pages/{$page_id}/frame_0002.ini"));
    }

    /**
     * 画像ダウンロードを経由して画像参照をローカルファイル名へ置換すること
     *
     * @return void
     */
    public function testImageDownloadIsImportedAndReferencedByLocalFileName()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportNc3PageTraitTarget();
        $image_url = 'http://8.8.8.8/files/image.png';
        $target->queueGetResponse([
            'body' => $this->buildNc3Html([
                [
                    'id' => 'frame-301',
                    'class' => 'panel panel-primary',
                    'title' => 'WithImage',
                    'content' => '<p><img src="' . $image_url . '" alt="img"></p>',
                ],
            ]),
            'http_code' => 200,
            'location' => '',
            'content_disposition' => '',
        ]);
        $target->queueDownloadResponse([
            'url' => $image_url,
            'bytes' => $this->tinyPngBinary(),
            'content_disposition' => "Content-Disposition: attachment;filename*=UTF-8''sample-image.png",
        ]);

        $page_id = 2005;
        $target->runMigrationNc3Page('http://8.8.8.8/nc3/image.html', $page_id);

        $this->assertSame([$image_url], $target->requestedDownloadUrls());
        $html = Storage::get("migration/import/pages/{$page_id}/frame_0001.html");
        $ini = Storage::get("migration/import/pages/{$page_id}/frame_0001.ini");

        $this->assertStringContainsString('frame_0001_1.png', $html);
        $this->assertStringNotContainsString($image_url, $html);
        $this->assertStringContainsString('[image_names]', $ini);
        $this->assertStringContainsString('frame_0001_1.png = "sample-image.png"', $ini);
    }

    /**
     * 添付ファイルダウンロードを経由してリンク参照をローカルファイル名へ置換すること
     *
     * @return void
     */
    public function testAttachmentDownloadIsImportedAndReferencedByLocalFileName()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportNc3PageTraitTarget();
        $file_url = 'http://8.8.8.8/cabinet_files/download/123';
        $target->queueGetResponse([
            'body' => $this->buildNc3Html([
                [
                    'id' => 'frame-401',
                    'class' => 'panel panel-info',
                    'title' => 'WithFile',
                    'content' => '<p><a href="' . $file_url . '">download</a></p>',
                ],
            ]),
            'http_code' => 200,
            'location' => '',
            'content_disposition' => '',
        ]);
        $target->queueDownloadResponse([
            'url' => $file_url,
            'bytes' => "dummy file body\n",
            'content_disposition' => "Content-Disposition: attachment;filename*=UTF-8''doc.txt",
        ]);

        $page_id = 2006;
        $target->runMigrationNc3Page('http://8.8.8.8/nc3/file.html', $page_id);

        $this->assertSame([$file_url], $target->requestedDownloadUrls());
        $html = Storage::get("migration/import/pages/{$page_id}/frame_0001.html");
        $ini = Storage::get("migration/import/pages/{$page_id}/frame_0001.ini");

        $this->assertStringContainsString('frame_0001_file_1.txt', $html);
        $this->assertStringNotContainsString($file_url, $html);
        $this->assertStringContainsString('[file_names]', $ini);
        $this->assertStringContainsString('frame_0001_file_1.txt = "doc.txt"', $ini);
    }

    /**
     * 画像ダウンロード失敗時は画像をスキップし、本文の保存は継続すること
     *
     * @return void
     */
    public function testImportSkipsImageWhenImageDownloadFailsAndStillSavesBody()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportNc3PageTraitTarget();
        $image_url = 'http://8.8.8.8/files/missing.png';
        $target->queueGetResponse([
            'body' => $this->buildNc3Html([
                [
                    'id' => 'frame-501',
                    'class' => 'panel panel-primary',
                    'title' => 'ImageFail',
                    'content' => '<p><img src="' . $image_url . '" alt="img"></p><p>KeepBody</p>',
                ],
            ]),
            'http_code' => 200,
            'location' => '',
            'content_disposition' => '',
        ]);
        $target->queueDownloadException(new RuntimeException('image download failed'));

        $page_id = 2007;
        $target->runMigrationNc3Page('http://8.8.8.8/nc3/image-fail.html', $page_id);

        $this->assertSame([$image_url], $target->requestedDownloadUrls());
        $html = Storage::get("migration/import/pages/{$page_id}/frame_0001.html");
        $ini = Storage::get("migration/import/pages/{$page_id}/frame_0001.ini");

        $this->assertStringContainsString('KeepBody', $html);
        $this->assertStringContainsString($image_url, $html);
        $this->assertStringNotContainsString('frame_0001_1.png', $html);
        $this->assertStringContainsString('[image_names]', $ini);
        $this->assertStringNotContainsString('frame_0001_1.png =', $ini);
    }

    /**
     * 添付ダウンロード失敗時は添付をスキップし、本文の保存は継続すること
     *
     * @return void
     */
    public function testImportSkipsAttachmentWhenAttachmentDownloadFailsAndStillSavesBody()
    {
        $this->fakeMigrationStorage();

        $target = new TestableMigrationExportNc3PageTraitTarget();
        $file_url = 'http://8.8.8.8/cabinet_files/download/999';
        $target->queueGetResponse([
            'body' => $this->buildNc3Html([
                [
                    'id' => 'frame-601',
                    'class' => 'panel panel-info',
                    'title' => 'FileFail',
                    'content' => '<p><a href="' . $file_url . '">download</a></p><p>KeepBody</p>',
                ],
            ]),
            'http_code' => 200,
            'location' => '',
            'content_disposition' => '',
        ]);
        $target->queueDownloadException(new RuntimeException('attachment download failed'));

        $page_id = 2008;
        $target->runMigrationNc3Page('http://8.8.8.8/nc3/file-fail.html', $page_id);

        $this->assertSame([$file_url], $target->requestedDownloadUrls());
        $html = Storage::get("migration/import/pages/{$page_id}/frame_0001.html");
        $ini = Storage::get("migration/import/pages/{$page_id}/frame_0001.ini");

        $this->assertStringContainsString('KeepBody', $html);
        $this->assertStringContainsString($file_url, $html);
        $this->assertStringNotContainsString('frame_0001_file_1.txt', $html);
        $this->assertStringContainsString('[file_names]', $ini);
        $this->assertStringNotContainsString('frame_0001_file_1.txt =', $ini);
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
     * 指定フレームのHTML/INIが保存されていないことを検証する
     *
     * @param int $page_id
     * @param int $frame_index
     * @return void
     */
    private function assertFrameNotSaved(int $page_id, int $frame_index): void
    {
        $frame = sprintf('%04d', $frame_index);
        Storage::assertMissing("migration/import/pages/{$page_id}/frame_{$frame}.html");
        Storage::assertMissing("migration/import/pages/{$page_id}/frame_{$frame}.ini");
    }

    /**
     * 指定フレームのHTML/INIが保存され、HTMLに期待文字列を含むことを検証する
     *
     * @param int $page_id
     * @param int $frame_index
     * @param string $expected_fragment
     * @return void
     */
    private function assertNc3FrameSavedContains(int $page_id, int $frame_index, string $expected_fragment): void
    {
        $frame = sprintf('%04d', $frame_index);
        Storage::assertExists("migration/import/pages/{$page_id}/frame_{$frame}.html");
        Storage::assertExists("migration/import/pages/{$page_id}/frame_{$frame}.ini");
        $this->assertStringContainsString($expected_fragment, Storage::get("migration/import/pages/{$page_id}/frame_{$frame}.html"));
    }

    /**
     * テスト用の最小NC3 HTMLを生成する
     *
     * @param array $sections
     * @return string
     */
    private function buildNc3Html(array $sections): string
    {
        $section_html = '';
        foreach ($sections as $section) {
            $section_html .= '<section id="' . $section['id'] . '" class="' . $section['class'] . '">';
            $section_html .= '<div class="panel-heading"><span>' . $section['title'] . '</span></div>';
            $section_html .= '<div class="panel-body"><article>' . $section['content'] . '</article></div>';
            $section_html .= '</section>';
        }

        return '<html><body><div id="container-main">' . $section_html . '</div></body></html>';
    }

    /**
     * 1x1 PNGのバイナリを返す
     *
     * @return string
     */
    private function tinyPngBinary(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO7Z0r8AAAAASUVORK5CYII=');
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses -- Test double is intentionally colocated with the test.
class TestableMigrationExportNc3PageTraitTarget
{
    use MigrationExportNc3PageTrait {
        migrationNC3Page as private traitMigrationNc3Page;
    }

    /** @var array */
    private $http_get_calls = [];

    /** @var array */
    private $queued_get_results = [];

    /** @var array */
    private $download_calls = [];

    /** @var array */
    private $queued_download_results = [];

    public function runMigrationNc3Page(string $url, int $page_id): void
    {
        $this->traitMigrationNc3Page($url, $page_id);
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
     * ページ取得レスポンスをキューに積む
     *
     * @param array $response
     * @return void
     */
    public function queueGetResponse(array $response): void
    {
        $this->queued_get_results[] = ['type' => 'response', 'value' => $response];
    }

    /**
     * ページ取得例外をキューに積む
     *
     * @param \RuntimeException $exception
     * @return void
     */
    public function queueGetException(RuntimeException $exception): void
    {
        $this->queued_get_results[] = ['type' => 'exception', 'value' => $exception];
    }

    /**
     * ダウンロード応答をキューに積む
     *
     * @param array $response
     * @return void
     */
    public function queueDownloadResponse(array $response): void
    {
        $this->queued_download_results[] = ['type' => 'response', 'value' => $response];
    }

    /**
     * ダウンロード例外をキューに積む
     *
     * @param \RuntimeException $exception
     * @return void
     */
    public function queueDownloadException(RuntimeException $exception): void
    {
        $this->queued_download_results[] = ['type' => 'exception', 'value' => $exception];
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

    protected function migrationHttpCreateClientForNc3(array $http_options = [])
    {
        return new Client();
    }

    protected function migrationHttpGetForNc3($http_client, string $url, array $http_options = []): array
    {
        $this->http_get_calls[] = $url;

        if (empty($this->queued_get_results)) {
            throw new RuntimeException('No queued migrationHttpGetForNc3 result.');
        }

        $queued = array_shift($this->queued_get_results);
        if ($queued['type'] === 'exception') {
            throw $queued['value'];
        }

        return $queued['value'];
    }

    protected function migrationHttpDownloadToFileForNc3($http_client, string $url, string $sink_path, array $http_options = []): array
    {
        $this->download_calls[] = $url;

        if (empty($this->queued_download_results)) {
            throw new RuntimeException('No queued migrationHttpDownloadToFileForNc3 result.');
        }

        $queued = array_shift($this->queued_download_results);
        if ($queued['type'] === 'exception') {
            throw $queued['value'];
        }

        $response = $queued['value'];
        if (isset($response['url']) && $response['url'] !== $url) {
            throw new RuntimeException('Unexpected download URL. expected=' . $response['url'] . ' actual=' . $url);
        }

        $bytes = (string) ($response['bytes'] ?? '');

        // trait内のgetimagesize()用に実ファイルも作成する（storage_path/app直参照のため）
        $directory = dirname($sink_path);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        file_put_contents($sink_path, $bytes);

        // Storage::fake() 側のファイル操作（exists/move/delete）でも見えるように同時に保存する
        $storage_app_prefix = storage_path('app') . DIRECTORY_SEPARATOR;
        if (strpos($sink_path, $storage_app_prefix) === 0) {
            $relative_path = str_replace(DIRECTORY_SEPARATOR, '/', substr($sink_path, strlen($storage_app_prefix)));
            Storage::put($relative_path, $bytes);
        }

        return [
            'body' => '',
            'http_code' => (int) ($response['http_code'] ?? 200),
            'location' => (string) ($response['location'] ?? ''),
            'content_disposition' => (string) ($response['content_disposition'] ?? ''),
        ];
    }
}
