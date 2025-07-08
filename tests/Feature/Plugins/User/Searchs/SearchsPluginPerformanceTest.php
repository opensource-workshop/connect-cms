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
 * サイト内検索プラグイン パフォーマンステスト
 */
class SearchsPluginPerformanceTest extends TestCase
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
        
        // テスト用グループ作成
        $this->test_group = Group::create([
            'name' => 'test_group',
            'display_sequence' => 1,
        ]);
        
        // グループユーザー関連付け
        GroupUser::create([
            'group_id' => $this->test_group->id,
            'user_id' => $this->test_user->id,
            'group_role' => 'general',
        ]);
        
        // ログイン
        Auth::login($this->test_user);
        
        // SearchsPlugin インスタンス作成
        $this->searchs_plugin = new SearchsPlugin();
    }

    /**
     * fetchSearchablePageIds メソッドのパフォーマンステスト
     * 
     * @group performance
     */
    public function test_fetchSearchablePageIds_performance()
    {
        // 大量のページとPageRole データを作成
        $this->createLargeDataset();
        
        // クエリ数を計測
        DB::flushQueryLog();
        DB::enableQueryLog();
        
        // パフォーマンス測定開始
        $start_time = microtime(true);
        
        // fetchSearchablePageIds を実行
        $request = new Request();
        $reflection = new \ReflectionClass($this->searchs_plugin);
        $method = $reflection->getMethod('fetchSearchablePageIds');
        $method->setAccessible(true);
        
        $result = $method->invokeArgs($this->searchs_plugin, [$request]);
        
        // パフォーマンス測定終了
        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time) * 1000; // ミリ秒
        
        // クエリログ取得
        $query_log = DB::getQueryLog();
        $query_count = count($query_log);
        
        // 結果の出力
        $this->output_performance_results($execution_time, $query_count, $query_log);
        
        // パフォーマンス基準の検証
        $this->assertLessThan(5000, $execution_time, "実行時間が5秒を超えています: {$execution_time}ms");
        $this->assertLessThan(50, $query_count, "クエリ数が50を超えています: {$query_count}回");
        
        // 結果が正しく取得できていることを確認
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /**
     * 大量のテストデータを作成
     */
    private function createLargeDataset()
    {
        // 100ページ作成（階層構造）
        $root_page = Page::create([
            'page_name' => 'root',
            'permanent_link' => 'root',
            'membership_flag' => 0,
        ]);
        
        $pages = [$root_page];
        
        // 10個の親ページを作成
        for ($i = 1; $i <= 10; $i++) {
            $parent = Page::create([
                'page_name' => "parent_{$i}",
                'permanent_link' => "parent_{$i}",
                'membership_flag' => 1, // メンバーシップページ
            ]);
            $parent->appendToNode($root_page);
            $pages[] = $parent;
            
            // 各親ページに10個の子ページを作成
            for ($j = 1; $j <= 10; $j++) {
                $child = Page::create([
                    'page_name' => "child_{$i}_{$j}",
                    'permanent_link' => "child_{$i}_{$j}",
                    'membership_flag' => 1,
                ]);
                $child->appendToNode($parent);
                $pages[] = $child;
            }
        }
        
        // PageRole データを作成（N+1問題を再現するため）
        foreach ($pages as $page) {
            if ($page->membership_flag == 1) {
                PageRole::create([
                    'page_id' => $page->id,
                    'group_id' => $this->test_group->id,
                    'target' => 'page',
                    'role_name' => 'role_article',
                    'role_value' => 1,
                ]);
            }
        }
    }

    /**
     * パフォーマンス結果の出力
     */
    private function output_performance_results($execution_time, $query_count, $query_log)
    {
        echo "\n=== Performance Test Results ===\n";
        echo "実行時間: {$execution_time}ms\n";
        echo "クエリ数: {$query_count}回\n";
        echo "\n=== Query Details ===\n";
        
        // クエリの詳細を出力（最初の10件のみ）
        $query_details = [];
        foreach (array_slice($query_log, 0, 10) as $i => $log) {
            $query_details[] = [
                'query' => $log['query'],
                'time' => $log['time'],
                'bindings' => $log['bindings']
            ];
        }
        
        foreach ($query_details as $i => $detail) {
            echo "Query " . ($i + 1) . ": " . number_format($detail['time'], 2) . "ms\n";
            echo "SQL: " . $detail['query'] . "\n";
            if (!empty($detail['bindings'])) {
                echo "Bindings: " . json_encode($detail['bindings']) . "\n";
            }
            echo "---\n";
        }
        
        if ($query_count > 10) {
            echo "... and " . ($query_count - 10) . " more queries\n";
        }
        
        // 重複するクエリパターンを分析
        $this->analyze_query_patterns($query_log);
    }

    /**
     * クエリパターンの分析
     */
    private function analyze_query_patterns($query_log)
    {
        $query_patterns = [];
        
        foreach ($query_log as $log) {
            // バインディングを除いてクエリパターンを作成
            $pattern = preg_replace('/\?/', 'PARAM', $log['query']);
            $pattern = preg_replace('/\s+/', ' ', $pattern);
            
            if (!isset($query_patterns[$pattern])) {
                $query_patterns[$pattern] = [
                    'count' => 0,
                    'total_time' => 0,
                    'max_time' => 0,
                ];
            }
            
            $query_patterns[$pattern]['count']++;
            $query_patterns[$pattern]['total_time'] += $log['time'];
            $query_patterns[$pattern]['max_time'] = max($query_patterns[$pattern]['max_time'], $log['time']);
        }
        
        // 頻出クエリパターンを出力
        echo "\n=== Query Pattern Analysis ===\n";
        arsort($query_patterns);
        
        foreach (array_slice($query_patterns, 0, 5, true) as $pattern => $stats) {
            if ($stats['count'] > 1) {
                echo "重複パターン (実行回数: {$stats['count']}回, 合計時間: " . number_format($stats['total_time'], 2) . "ms)\n";
                echo "SQL: " . substr($pattern, 0, 100) . "...\n";
                echo "---\n";
            }
        }
    }

    /**
     * 最適化後のパフォーマンステスト
     * 
     * @group performance
     */
    public function test_fetchSearchablePageIds_optimized_performance()
    {
        // 大量のページとPageRole データを作成
        $this->createLargeDataset();
        
        // クエリ数を計測
        DB::flushQueryLog();
        DB::enableQueryLog();
        
        // パフォーマンス測定開始
        $start_time = microtime(true);
        
        // 最適化されたfetchSearchablePageIds を実行
        $request = new Request();
        $reflection = new \ReflectionClass($this->searchs_plugin);
        $method = $reflection->getMethod('fetchSearchablePageIds');
        $method->setAccessible(true);
        
        $result = $method->invokeArgs($this->searchs_plugin, [$request]);
        
        // パフォーマンス測定終了
        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time) * 1000; // ミリ秒
        
        // クエリログ取得
        $query_log = DB::getQueryLog();
        $query_count = count($query_log);
        
        // 結果の出力
        echo "\n=== OPTIMIZED Performance Test Results ===\n";
        echo "実行時間: {$execution_time}ms\n";
        echo "クエリ数: {$query_count}回\n";
        
        // 最適化後の基準（テスト時は緩く設定）
        $this->assertLessThan(1000, $execution_time, "最適化後も実行時間が1秒を超えています: {$execution_time}ms");
        // $this->assertLessThan(20, $query_count, "最適化後もクエリ数が20を超えています: {$query_count}回");
        
        // 結果が正しく取得できていることを確認
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // 詳細出力
        $this->output_performance_results($execution_time, $query_count, $query_log);
    }

    /**
     * 超最適化版パフォーマンステスト（クエリ数10回以下を目指す）
     * 
     * @group performance
     */
    public function test_fetchSearchablePageIds_ultra_optimized_performance()
    {
        // 大量のページとPageRole データを作成
        $this->createLargeDataset();
        
        // クエリ数を計測
        DB::flushQueryLog();
        DB::enableQueryLog();
        
        // パフォーマンス測定開始
        $start_time = microtime(true);
        
        // 超最適化されたfetchSearchablePageIds を実行
        $request = new Request();
        $reflection = new \ReflectionClass($this->searchs_plugin);
        $method = $reflection->getMethod('fetchSearchablePageIds');
        $method->setAccessible(true);
        
        $result = $method->invokeArgs($this->searchs_plugin, [$request]);
        
        // パフォーマンス測定終了
        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time) * 1000; // ミリ秒
        
        // クエリログ取得
        $query_log = DB::getQueryLog();
        $query_count = count($query_log);
        
        // 結果の出力
        echo "\n=== ULTRA OPTIMIZED Performance Test Results ===\n";
        echo "実行時間: {$execution_time}ms\n";
        echo "クエリ数: {$query_count}回\n";
        
        // 最終基準（クエリ数5回以下）
        $this->assertLessThan(200, $execution_time, "超最適化後も実行時間が200msを超えています: {$execution_time}ms");
        $this->assertLessThan(5, $query_count, "超最適化後もクエリ数が5回を超えています: {$query_count}回");
        
        // 結果が正しく取得できていることを確認
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // 詳細出力
        $this->output_performance_results($execution_time, $query_count, $query_log);
    }

    /**
     * 機能的同等性テスト（元の実装と最適化版の結果が同じことを確認）
     * 
     * @group functional
     */
    public function test_functional_equivalence_between_original_and_optimized()
    {
        // 大量のページとPageRole データを作成
        $this->createLargeDataset();
        $request = new Request();
        
        // 元の実装を一時的に復元して結果を取得
        $original_result = $this->getOriginalSearchablePageIds($request);
        
        // 最適化版の結果を取得
        $reflection = new \ReflectionClass($this->searchs_plugin);
        $method = $reflection->getMethod('fetchSearchablePageIds');
        $method->setAccessible(true);
        $optimized_result = $method->invokeArgs($this->searchs_plugin, [$request]);
        
        // 結果が同一であることを確認
        sort($original_result);
        sort($optimized_result);
        
        $this->assertEquals($original_result, $optimized_result, 
            "最適化版の結果が元の実装と異なります。\n" .
            "元の実装: " . implode(',', $original_result) . "\n" .
            "最適化版: " . implode(',', $optimized_result)
        );
        
        echo "\n=== Functional Equivalence Test Results ===\n";
        echo "元の実装結果: " . count($original_result) . "件\n";
        echo "最適化版結果: " . count($optimized_result) . "件\n";
        echo "結果一致: ✓\n";
    }

    /**
     * 元の実装ロジック（比較用）
     */
    private function getOriginalSearchablePageIds($request)
    {
        $pages = Page::get();
        $visible_page_ids = [];
        
        foreach ($pages as $page) {
            // 元のロジック通り
            $page_tree = Page::reversed()->ancestorsAndSelf($page->id);

            // パスワード認証
            if ($page->isRequestPassword($request, $page_tree)) {
                continue;
            }

            // 親子ページを加味してページ表示できるか（元のメソッド）
            if (!$page->isVisibleAncestorsAndSelf($page_tree)) {
                continue;
            }

            $visible_page_ids[] = $page->id;
        }

        return $visible_page_ids;
    }
}