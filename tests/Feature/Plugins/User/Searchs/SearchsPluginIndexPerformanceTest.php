<?php

namespace Tests\Feature\Plugins\User\Searchs;

use App\Models\Common\Group;
use App\Models\Common\GroupUser;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Plugins\User\Searchs\SearchsPlugin;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * サイト内検索プラグイン INDEXパフォーマンステスト
 */
class SearchsPluginIndexPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private $test_user;
    private $test_page;
    private $test_frame;
    private $test_group;
    private $searchs_plugin;

    /**
     * 各テスト前のセットアップ
     */
    protected function setUp(): void
    {
        parent::setUp();

        // DB Seeder を実行して初期データ(configs等)を投入
        $this->seed();

        // テスト用ユーザー作成
        $this->test_user = User::factory()->create([
            'name' => 'test_user',
            'email' => 'test@example.com',
        ]);

        // テスト用グループ作成（複数のグループ）
        $this->test_groups = [];
        for ($i = 1; $i <= 5; $i++) {
            $this->test_groups[] = Group::create([
                'name' => "test_group_{$i}",
                'display_sequence' => $i,
            ]);
        }

        // テストユーザーを複数のグループに所属させる
        foreach ($this->test_groups as $group) {
            GroupUser::create([
                'group_id' => $group->id,
                'user_id' => $this->test_user->id,
                'group_role' => 'general',
            ]);
        }

        // 最初のグループを互換性のため保持
        $this->test_group = $this->test_groups[0];

        // ログイン
        Auth::login($this->test_user);

        // SearchsPlugin インスタンス作成
        $this->searchs_plugin = new SearchsPlugin();
    }

    /**
     * INDEXなしでのパフォーマンステスト
     *
     * @group index-performance
     */
    public function testPerformanceWithoutIndexes()
    {
        // INDEXを削除
        $this->dropIndexes();

        // 大量のページとPageRole データを作成
        $this->createLargeDataset();

        // パフォーマンス測定
        $result = $this->measurePerformance();

        echo "\n=== Performance WITHOUT Indexes ===\n";
        echo "実行時間: {$result['execution_time']}ms\n";
        echo "クエリ数: {$result['query_count']}回\n";
        echo "PageRoleクエリ時間: {$result['page_role_query_time']}ms\n";
        echo "PageRoleクエリ実行回数: {$result['page_role_query_count']}回\n";

        // 結果をファイルに保存（比較用）
        file_put_contents('/tmp/performance_without_indexes.json', json_encode($result));

        // 基本的な検証
        $this->assertIsArray($result['visible_page_ids']);
        $this->assertNotEmpty($result['visible_page_ids']);
    }

    /**
     * INDEXありでのパフォーマンステスト
     *
     * @group index-performance
     */
    public function testPerformanceWithIndexes()
    {
        // INDEXを作成
        $this->createIndexes();

        // 大量のページとPageRole データを作成
        $this->createLargeDataset();

        // パフォーマンス測定
        $result = $this->measurePerformance();

        echo "\n=== Performance WITH Indexes ===\n";
        echo "実行時間: {$result['execution_time']}ms\n";
        echo "クエリ数: {$result['query_count']}回\n";
        echo "PageRoleクエリ時間: {$result['page_role_query_time']}ms\n";
        echo "PageRoleクエリ実行回数: {$result['page_role_query_count']}回\n";

        // 前回のINDEXなし結果と比較
        if (file_exists('/tmp/performance_without_indexes.json')) {
            $without_indexes = json_decode(file_get_contents('/tmp/performance_without_indexes.json'), true);
            $improvement_ratio = ($without_indexes['execution_time'] - $result['execution_time']) / $without_indexes['execution_time'] * 100;
            $page_role_improvement = ($without_indexes['page_role_query_time'] - $result['page_role_query_time']) / $without_indexes['page_role_query_time'] * 100;

            echo "\n=== INDEX Performance Improvement ===\n";
            echo "全体実行時間改善: " . number_format($improvement_ratio, 1) . "%\n";
            echo "PageRoleクエリ改善: " . number_format($page_role_improvement, 1) . "%\n";

            // INDEXの効果を検証（最低でも10%の改善を期待）
            $this->assertGreaterThan(10, $improvement_ratio, "INDEXによる実行時間改善が10%未満です");
            $this->assertGreaterThan(10, $page_role_improvement, "INDEXによるPageRoleクエリ改善が10%未満です");
        }

        // 結果の基本検証
        $this->assertIsArray($result['visible_page_ids']);
        $this->assertNotEmpty($result['visible_page_ids']);
    }

    /**
     * パフォーマンス測定
     */
    private function measurePerformance()
    {
        // クエリログ有効化
        DB::flushQueryLog();
        DB::enableQueryLog();

        // 実行時間測定開始
        $start_time = microtime(true);

        // fetchSearchablePageIds を実行
        $request = new Request();
        $reflection = new \ReflectionClass($this->searchs_plugin);
        $method = $reflection->getMethod('fetchSearchablePageIds');
        $method->setAccessible(true);

        $visible_page_ids = $method->invokeArgs($this->searchs_plugin, [$request]);

        // 実行時間測定終了
        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time) * 1000; // ミリ秒

        // クエリログ取得
        $query_log = DB::getQueryLog();
        $query_count = count($query_log);

        // PageRoleクエリの詳細分析
        $page_role_queries = array_filter($query_log, function ($log) {
            return strpos($log['query'], 'page_roles') !== false &&
                   strpos($log['query'], 'group_users') !== false;
        });

        $page_role_query_time = array_sum(array_column($page_role_queries, 'time'));
        $page_role_query_count = count($page_role_queries);

        return [
            'execution_time' => $execution_time,
            'query_count' => $query_count,
            'page_role_query_time' => $page_role_query_time,
            'page_role_query_count' => $page_role_query_count,
            'visible_page_ids' => $visible_page_ids,
            'query_log' => $query_log
        ];
    }

    /**
     * 大量のテストデータ作成
     */
    private function createLargeDataset()
    {
        // 1. 大量のユーザーとグループを作成
        $users = [];
        $groups = [];

        // 1,000人のユーザーを作成（メモリ効率と実行時間を考慮）
        for ($i = 1; $i <= 1000; $i++) {
            $users[] = User::factory()->make([
                'name' => "test_user_{$i}",
                'email' => "test{$i}@example.com",
                'userid' => "test{$i}",
            ]);
            $users[$i-1]->save();
        }

        // 20個のグループを作成
        for ($i = 1; $i <= 20; $i++) {
            $groups[] = Group::create([
                'name' => "test_group_{$i}",
                'display_sequence' => $i,
            ]);
        }

        // 2. group_usersテーブルに大量のデータを作成（INDEXテスト用）
        foreach ($users as $user) {
            // 各ユーザーを3-5個のグループにランダムに所属させる
            $user_groups = collect($groups)->random(rand(3, 5));
            foreach ($user_groups as $group) {
                GroupUser::create([
                    'group_id' => $group->id,
                    'user_id' => $user->id,
                    'group_role' => 'general',
                ]);
            }
        }

        // 3. 1000ページ作成（階層構造）
        $root_page = Page::create([
            'page_name' => 'root',
            'permanent_link' => 'root',
            'membership_flag' => 0,
        ]);

        $pages = [$root_page];

        // 20個の親ページを作成
        for ($i = 1; $i <= 20; $i++) {
            $parent = Page::create([
                'page_name' => "parent_{$i}",
                'permanent_link' => "parent_{$i}",
                'membership_flag' => 1, // メンバーシップページ
            ]);
            $parent->appendToNode($root_page);
            $pages[] = $parent;

            // 各親ページに49個の子ページを作成（20 * 49 + 20 + 1 = 1000ページ）
            for ($j = 1; $j <= 49; $j++) {
                $child = Page::create([
                    'page_name' => "child_{$i}_{$j}",
                    'permanent_link' => "child_{$i}_{$j}",
                    'membership_flag' => 1,
                ]);
                $child->appendToNode($parent);
                $pages[] = $child;
            }
        }

        // 4. PageRole データを大量作成（複数グループ × 複数ページ）
        foreach ($pages as $page) {
            if ($page->membership_flag == 1) {
                // 各ページに3-8個のグループの権限を設定
                $page_groups = collect($groups)->random(rand(3, 8));
                foreach ($page_groups as $group) {
                    PageRole::create([
                        'page_id' => $page->id,
                        'group_id' => $group->id,
                        'target' => 'page',
                        'role_name' => 'role_article',
                        'role_value' => 1,
                    ]);
                }
            }
        }

        echo "\n=== Dataset Created ===\n";
        echo "ユーザー数: " . count($users) . "\n";
        echo "グループ数: " . count($groups) . "\n";
        echo "group_users数: " . GroupUser::count() . "\n";
        echo "ページ数: " . count($pages) . "\n";
        echo "page_roles数: " . PageRole::count() . "\n";
    }

    /**
     * INDEXを作成
     */
    private function createIndexes()
    {
        try {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_page_roles_on_page_id_and_group_id ON page_roles (page_id, group_id)');
        } catch (\Exception $e) {
            // INDEXが既に存在する場合は無視
        }

        try {
            DB::statement('CREATE INDEX IF NOT EXISTS idx_group_users_on_user_id_and_group_id ON group_users (user_id, group_id)');
        } catch (\Exception $e) {
            // INDEXが既に存在する場合は無視
        }
    }

    /**
     * INDEXを削除
     */
    private function dropIndexes()
    {
        try {
            DB::statement('DROP INDEX IF EXISTS idx_page_roles_on_page_id_and_group_id ON page_roles');
        } catch (\Exception $e) {
            // INDEXが存在しない場合は無視
        }

        try {
            DB::statement('DROP INDEX IF EXISTS idx_group_users_on_user_id_and_group_id ON group_users');
        } catch (\Exception $e) {
            // INDEXが存在しない場合は無視
        }
    }

    /**
     * テスト後のクリーンアップ
     */
    protected function tearDown(): void
    {
        // 一時ファイルを削除
        if (file_exists('/tmp/performance_without_indexes.json')) {
            unlink('/tmp/performance_without_indexes.json');
        }

        parent::tearDown();
    }
}
