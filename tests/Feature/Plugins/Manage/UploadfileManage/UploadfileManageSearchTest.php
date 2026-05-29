<?php

namespace Tests\Feature\Plugins\Manage\UploadfileManage;

use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\Core\Plugins;
use App\Plugins\Manage\UploadfileManage\UploadfileManage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * アップロードファイル管理一覧の検索・並べ替えを検証するFeatureテスト。
 *
 * 管理プラグインの公開メソッドを通して、ユーザーが指定した条件が一覧結果へ反映されることを守る。
 */
class UploadfileManageSearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * テスト前に初期データを投入する。
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /**
     * ファイルサイズ、ページ名、アップロード日付、プラグインの条件を組み合わせても、該当ファイルだけに絞り込めること。
     */
    public function testIndexCanFilterBySizePageCreatedAtAndPlugin(): void
    {
        $target_page = Page::factory()->create(['page_name' => '教材ページ', 'permanent_link' => '/materials']);
        $other_page = Page::factory()->create(['page_name' => '連絡ページ', 'permanent_link' => '/notices']);
        $this->savePlugin('forms', 'フォーム');
        $this->savePlugin('blogs', 'ブログ');

        Uploads::factory()->create([
            'client_original_name' => 'target-form.pdf',
            'size' => 2048,
            'plugin_name' => 'forms',
            'page_id' => $target_page->id,
            'created_at' => '2024-06-10 10:00:00',
        ]);
        Uploads::factory()->create([
            'client_original_name' => 'too-small.pdf',
            'size' => 512,
            'plugin_name' => 'forms',
            'page_id' => $target_page->id,
            'created_at' => '2024-06-10 10:00:00',
        ]);
        Uploads::factory()->create([
            'client_original_name' => 'wrong-page.pdf',
            'size' => 2048,
            'plugin_name' => 'forms',
            'page_id' => $other_page->id,
            'created_at' => '2024-06-10 10:00:00',
        ]);
        Uploads::factory()->create([
            'client_original_name' => 'wrong-plugin.pdf',
            'size' => 2048,
            'plugin_name' => 'blogs',
            'page_id' => $target_page->id,
            'created_at' => '2024-06-10 10:00:00',
        ]);
        Uploads::factory()->create([
            'client_original_name' => 'wrong-date.pdf',
            'size' => 2048,
            'plugin_name' => 'forms',
            'page_id' => $target_page->id,
            'created_at' => '2024-07-10 10:00:00',
        ]);

        $plugin = new UploadfileManage();
        $response = $plugin->search($this->makeRequest('POST', '/manage/uploadfile/search', [
            'search_condition' => [
                'size_from' => '1',
                'size_to' => '3',
                'size_unit' => 'KB',
                'page_name' => '教材',
                'created_at_from' => '2024-06-01',
                'created_at_to' => '2024-06-30',
                'plugin_names' => ['forms'],
                'sort' => 'id_desc',
            ],
        ]));

        $this->assertSame(url('/manage/uploadfile'), $response->getTargetUrl());

        $this->assertSame(['target-form.pdf'], $this->getUploadFileNames($plugin->index($this->makeRequest('GET', '/manage/uploadfile'))));
    }

    /**
     * IDを指定した場合は、同名や近い属性のファイルではなく指定IDのファイルだけを表示できること。
     */
    public function testIndexCanFilterById(): void
    {
        $target = Uploads::factory()->create(['client_original_name' => 'id-target.pdf']);
        Uploads::factory()->create(['client_original_name' => 'id-other.pdf']);

        $plugin = new UploadfileManage();
        $response = $plugin->search($this->makeRequest('POST', '/manage/uploadfile/search', [
            'search_condition' => [
                'id' => $target->id,
                'sort' => 'id_desc',
            ],
        ]));

        $this->assertSame(url('/manage/uploadfile'), $response->getTargetUrl());

        $this->assertSame(['id-target.pdf'], $this->getUploadFileNames($plugin->index($this->makeRequest('GET', '/manage/uploadfile'))));
    }

    /**
     * 検索条件を指定せずに検索や表示件数変更をしても、既定の単位や並べ替えだけで条件設定中扱いにならないこと。
     */
    public function testIndexDoesNotTreatDefaultSizeUnitAsSearchCondition(): void
    {
        $plugin = new UploadfileManage();
        $plugin->search($this->makeRequest('POST', '/manage/uploadfile/search', [
            'search_condition' => [
                'sort' => 'id_desc',
            ],
            'uploadfile_per_page' => 50,
        ]));

        $view = $plugin->index($this->makeRequest('GET', '/manage/uploadfile'));

        $this->assertFalse($view->getData()['is_search_condition_set']);
    }

    /**
     * プラグインはチェックボックスで複数選択でき、選択したプラグインのファイルだけをまとめて表示できること。
     */
    public function testIndexCanFilterByMultiplePlugins(): void
    {
        $this->savePlugin('forms', 'フォーム');
        $this->savePlugin('blogs', 'ブログ');
        $this->savePlugin('cabinets', 'キャビネット');
        Uploads::factory()->create(['client_original_name' => 'forms-file.pdf', 'plugin_name' => 'forms']);
        Uploads::factory()->create(['client_original_name' => 'blogs-file.pdf', 'plugin_name' => 'blogs']);
        Uploads::factory()->create(['client_original_name' => 'cabinets-file.pdf', 'plugin_name' => 'cabinets']);

        $plugin = new UploadfileManage();
        $plugin->search($this->makeRequest('POST', '/manage/uploadfile/search', [
            'search_condition' => [
                'plugin_names' => ['forms', 'blogs'],
                'sort' => 'client_original_name_asc',
            ],
        ]));

        $this->assertSame(
            ['blogs-file.pdf', 'forms-file.pdf'],
            $this->getUploadFileNames($plugin->index($this->makeRequest('GET', '/manage/uploadfile')))
        );
    }

    /**
     * ファイルサイズの下限・上限ちょうどのファイルは検索結果に含まれ、範囲外の直前・直後は除外されること。
     */
    public function testIndexIncludesFileSizeBoundaryValues(): void
    {
        Uploads::factory()->create(['client_original_name' => 'size-before.txt', 'size' => 1023]);
        Uploads::factory()->create(['client_original_name' => 'size-lower.txt', 'size' => 1024]);
        Uploads::factory()->create(['client_original_name' => 'size-upper.txt', 'size' => 2048]);
        Uploads::factory()->create(['client_original_name' => 'size-after.txt', 'size' => 2049]);

        $plugin = new UploadfileManage();
        $plugin->search($this->makeRequest('POST', '/manage/uploadfile/search', [
            'search_condition' => [
                'size_from' => '1',
                'size_to' => '2',
                'size_unit' => 'KB',
                'sort' => 'size_asc',
            ],
        ]));

        $this->assertSame(
            ['size-lower.txt', 'size-upper.txt'],
            $this->getUploadFileNames($plugin->index($this->makeRequest('GET', '/manage/uploadfile')))
        );
    }

    /**
     * アップロード日付の開始日・終了日に作成されたファイルは時刻に関わらず含まれ、範囲外の日付は除外されること。
     */
    public function testIndexIncludesUploadDateBoundaryValues(): void
    {
        Uploads::factory()->create(['client_original_name' => 'date-before.txt', 'created_at' => '2024-05-31 23:59:59']);
        Uploads::factory()->create(['client_original_name' => 'date-lower.txt', 'created_at' => '2024-06-01 00:00:00']);
        Uploads::factory()->create(['client_original_name' => 'date-upper.txt', 'created_at' => '2024-06-30 23:59:59']);
        Uploads::factory()->create(['client_original_name' => 'date-after.txt', 'created_at' => '2024-07-01 00:00:00']);

        $plugin = new UploadfileManage();
        $plugin->search($this->makeRequest('POST', '/manage/uploadfile/search', [
            'search_condition' => [
                'created_at_from' => '2024-06-01',
                'created_at_to' => '2024-06-30',
                'sort' => 'created_at_asc',
            ],
        ]));

        $this->assertSame(
            ['date-lower.txt', 'date-upper.txt'],
            $this->getUploadFileNames($plugin->index($this->makeRequest('GET', '/manage/uploadfile')))
        );
    }

    /**
     * ID、ファイルサイズ、アップロード日付、プラグイン、ページ名の並べ替えが一覧の表示順に反映されること。
     */
    public function testIndexCanSortByRequestedColumns(): void
    {
        $page_beta = Page::factory()->create(['page_name' => 'Beta page', 'permanent_link' => '/beta']);
        $page_gamma = Page::factory()->create(['page_name' => 'Gamma page', 'permanent_link' => '/gamma']);
        $page_alpha = Page::factory()->create(['page_name' => 'Alpha page', 'permanent_link' => '/alpha']);
        $this->savePlugin('alpha', 'Alpha plugin', 30);
        $this->savePlugin('zeta', 'Zeta plugin', 10);
        $this->savePlugin('middle', 'Middle plugin', 20);

        Uploads::factory()->create([
            'client_original_name' => 'sort-one.txt',
            'size' => 200,
            'plugin_name' => 'alpha',
            'page_id' => $page_beta->id,
            'created_at' => '2024-02-01 00:00:00',
        ]);
        Uploads::factory()->create([
            'client_original_name' => 'sort-two.txt',
            'size' => 100,
            'plugin_name' => 'zeta',
            'page_id' => $page_gamma->id,
            'created_at' => '2024-01-01 00:00:00',
        ]);
        Uploads::factory()->create([
            'client_original_name' => 'sort-three.txt',
            'size' => 300,
            'plugin_name' => 'middle',
            'page_id' => $page_alpha->id,
            'created_at' => '2024-03-01 00:00:00',
        ]);

        $plugin = new UploadfileManage();
        $this->assertUploadOrder($plugin, 'id_asc', ['sort-one.txt', 'sort-two.txt', 'sort-three.txt']);
        $this->assertUploadOrder($plugin, 'id_desc', ['sort-three.txt', 'sort-two.txt', 'sort-one.txt']);
        $this->assertUploadOrder($plugin, 'size_asc', ['sort-two.txt', 'sort-one.txt', 'sort-three.txt']);
        $this->assertUploadOrder($plugin, 'created_at_asc', ['sort-two.txt', 'sort-one.txt', 'sort-three.txt']);
        $this->assertUploadOrder($plugin, 'plugin_name_asc', ['sort-two.txt', 'sort-three.txt', 'sort-one.txt']);
        $this->assertUploadOrder($plugin, 'page_name_asc', ['sort-three.txt', 'sort-one.txt', 'sort-two.txt']);
    }

    /**
     * テスト対象メソッドに渡すセッション付きリクエストを作成する。
     */
    private function makeRequest(string $method, string $uri, array $parameters = []): Request
    {
        $request = Request::create($uri, $method, $parameters);
        $request->setLaravelSession($this->app['session.store']);

        return $request;
    }

    /**
     * 検索・並べ替えで参照するプラグイン表示名を用意する。
     */
    private function savePlugin(string $plugin_name, string $plugin_name_full, int $display_sequence = 0): void
    {
        $plugin = Plugins::updateOrCreate(
            ['plugin_name' => $plugin_name],
            ['plugin_name_full' => $plugin_name_full, 'display_flag' => 1]
        );
        $plugin->display_sequence = $display_sequence;
        $plugin->save();
    }

    /**
     * 指定した並べ替え条件でファイル名が期待順に表示されることを確認する。
     */
    private function assertUploadOrder(UploadfileManage $plugin, string $sort, array $expected_file_names): void
    {
        session(['search_condition' => ['sort' => $sort]]);

        $this->assertSame($expected_file_names, $this->getUploadFileNames($plugin->index($this->makeRequest('GET', '/manage/uploadfile'))));
    }

    /**
     * 一覧Viewから表示対象のファイル名だけを取り出す。
     */
    private function getUploadFileNames($view): array
    {
        return $view->getData()['uploads']->getCollection()->pluck('client_original_name')->all();
    }
}
