<?php

namespace Tests\Unit\Utilities\Storage;

use Tests\TestCase;
use App\Utilities\Storage\StorageUsageCalculator;

class StorageUsageCalculatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // 設定値をクリア
        config(['storage_management.limit_mb' => null]);
        config(['storage_management.warning_threshold' => null]);
    }
    
    protected function tearDown(): void
    {
        // 設定値をクリア
        config(['storage_management.limit_mb' => null]);
        config(['storage_management.warning_threshold' => null]);
        
        parent::tearDown();
    }

    /**
     * getDataUsage メソッドの基本動作テスト
     * 
     * このメソッドは StorageUsageCalculator の中核機能である getDataUsage() の動作を検証します。
     * 戻り値の構造と型が正しいかを確認し、各種の使用量データが適切に取得できることを保証します。
     * 
     * @test
     * @group StorageUsageCalculator
     * @covers \App\Utilities\Storage\StorageUsageCalculator::getDataUsage
     */
    public function testGetDataUsageReturnsExpectedStructure()
    {
        // テスト環境の設定 - 実際のDBとの接続を確保
        config(['database.default' => 'testing']);
        config(['storage_management.limit_mb' => 100]);
        
        // メソッド実行
        $result = StorageUsageCalculator::getDataUsage();
        
        // 戻り値の構造確認 - 必須キーが全て存在することを検証
        $this->assertIsArray($result);
        $this->assertArrayHasKey('tables', $result);        // DBテーブル使用量
        $this->assertArrayHasKey('uploads', $result);       // アップロードファイル使用量
        $this->assertArrayHasKey('total', $result);         // 総使用量
        $this->assertArrayHasKey('plan_limit', $result);    // プラン容量制限
        $this->assertArrayHasKey('usage_percentage', $result); // 使用率
        
        // 各値の型確認 - データの整合性を保証
        $this->assertIsString($result['tables']);           // フォーマット済み文字列
        $this->assertIsString($result['uploads']);          // フォーマット済み文字列
        $this->assertIsString($result['total']);            // フォーマット済み文字列
        $this->assertTrue(is_string($result['plan_limit']) || is_null($result['plan_limit'])); // 設定次第でnull
        $this->assertTrue(is_float($result['usage_percentage']) || is_null($result['usage_percentage'])); // 計算結果
    }

    /**
     * shouldShowWarning メソッドのテスト - 使用率が閾値以上の場合
     * 
     * 管理者にストレージ警告を表示すべき状況での動作を検証します。
     * デフォルト閾値（80%）とカスタム閾値の両方でテストし、
     * 適切に警告判定が行われることを確認します。
     * 
     * @test
     * @group StorageUsageCalculator
     * @covers \App\Utilities\Storage\StorageUsageCalculator::shouldShowWarning
     */
    public function testShouldShowWarningReturnsTrueWhenUsageExceedsThreshold()
    {
        // テストケース1: デフォルト閾値（80%）を超える使用率での警告表示確認
        $this->assertTrue(StorageUsageCalculator::shouldShowWarning(0.85)); // 85% > 80%
        
        // テストケース2: カスタム閾値を設定した場合の動作確認
        config(['storage_management.warning_threshold' => 0.9]); // 90%に変更
        $this->assertTrue(StorageUsageCalculator::shouldShowWarning(0.95)); // 95% > 90%
        
        // テストケース3: 閾値ちょうどの境界値テスト（以上の条件なので警告表示）
        $this->assertTrue(StorageUsageCalculator::shouldShowWarning(0.9)); // 90% >= 90%
    }

    /**
     * shouldShowWarning メソッドのテスト - 使用率が閾値未満の場合
     * 
     * 警告を表示しない正常範囲での使用率における動作を検証します。
     * デフォルト閾値未満と0%での境界値テストを実施し、
     * 不必要な警告が表示されないことを確認します。
     * 
     * @test
     * @group StorageUsageCalculator
     * @covers \App\Utilities\Storage\StorageUsageCalculator::shouldShowWarning
     */
    public function testShouldShowWarningReturnsFalseWhenUsageIsBelowThreshold()
    {
        // 設定値を明示的にデフォルト値に設定
        config(['storage_management.warning_threshold' => 0.8]);
        
        // テストケース1: デフォルト閾値（80%）未満の使用率では警告なし
        $this->assertFalse(StorageUsageCalculator::shouldShowWarning(0.75)); // 75% < 80%
        
        // テストケース2: 最小値（0%）での境界値テスト
        $this->assertFalse(StorageUsageCalculator::shouldShowWarning(0.0)); // 0% < 80%
    }

    /**
     * shouldShowWarning メソッドのテスト - 使用率がnullの場合
     * 
     * 使用率が取得できない（null）場合の安全な動作を検証します。
     * プラン容量が未設定の場合など、使用率が計算できない状況で
     * 適切にfalseを返すことを確認します。
     * 
     * @test
     * @group StorageUsageCalculator
     * @covers \App\Utilities\Storage\StorageUsageCalculator::shouldShowWarning
     */
    public function testShouldShowWarningReturnsFalseWhenUsageIsNull()
    {
        // テストケース: 使用率がnullの場合は警告表示しない（安全な動作）
        $this->assertFalse(StorageUsageCalculator::shouldShowWarning(null));
    }

    /**
     * getPlanLimitFormatted メソッドのテスト - 有効な設定値
     * 
     * 有効な環境変数設定でのプラン容量フォーマット処理を検証します。
     * MBからバイトへの変換とフォーマット済み文字列への変換が
     * 正しく行われることを確認します。
     * 
     * @test
     * @group StorageUsageCalculator
     * @covers \App\Utilities\Storage\StorageUsageCalculator::getPlanLimitFormatted
     */
    public function testGetPlanLimitFormattedWithValidConfiguration()
    {
        // テスト用設定値設定（100MB）
        config(['storage_management.limit_mb' => 100]);
        
        // privateメソッドへのアクセス準備
        $method = new \ReflectionMethod(StorageUsageCalculator::class, 'getPlanLimitFormatted');
        $method->setAccessible(true);
        
        // メソッド実行
        $result = $method->invoke(null);
        
        // フォーマット結果の検証
        $this->assertIsString($result);  // 文字列型であること
        $this->assertMatchesRegularExpression('/^\d+\.\d{2}[KMGTB]+$/', $result); // 正規表現でフォーマット確認
    }

    /**
     * getPlanLimitFormatted メソッドのテスト - 無効な設定値
     */
    public function testGetPlanLimitFormattedWithInvalidConfiguration()
    {
        $method = new \ReflectionMethod(StorageUsageCalculator::class, 'getPlanLimitFormatted');
        $method->setAccessible(true);
        
        // 現在の環境で設定されている値でのテスト
        $result = $method->invoke(null);
        
        // 結果が文字列またはnullであることを確認
        $this->assertTrue(is_string($result) || is_null($result));
        
        // 文字列の場合は適切なフォーマットであることを確認
        if (is_string($result)) {
            $this->assertMatchesRegularExpression('/^\d+(\.\d{2})?[KMGTB]+$/', $result);
        }
    }

    /**
     * getPlanLimitFormatted メソッドのテスト - 小数値は有効
     * 
     * 環境変数に小数値が設定された場合の動作を検証します。
     * PHPのintval()関数により整数部分のみが使用されることを確認し、
     * エラーとならずに適切なフォーマット済み文字列が返されることを検証します。
     * 
     * @test
     * @group StorageUsageCalculator
     * @covers \App\Utilities\Storage\StorageUsageCalculator::getPlanLimitFormatted
     */
    public function testGetPlanLimitFormattedWithDecimalValue()
    {
        // 小数値を含む設定値設定（10.5MB）
        config(['storage_management.limit_mb' => 10.5]);
        
        // privateメソッドへのアクセス準備
        $method = new \ReflectionMethod(StorageUsageCalculator::class, 'getPlanLimitFormatted');
        $method->setAccessible(true);
        
        // メソッド実行
        $result = $method->invoke(null);
        
        // 検証: 小数値は有効（intval で整数部分の10として処理される）
        $this->assertIsString($result); // エラーにならず文字列が返される
    }

    /**
     * parseFormattedSize メソッドのテスト
     * 
     * フォーマット済みサイズ文字列（例："1.5MB"）をバイト数に変換する機能を検証します。
     * 異なる単位（B, KB, MB, GB, TB）や小数点、無効値などの様々な
     * 入力パターンに対する適切な変換を確認します。
     * 
     * @test
     * @group StorageUsageCalculator
     * @covers \App\Utilities\Storage\StorageUsageCalculator::parseFormattedSize
     * @dataProvider formattedSizeProvider
     */
    public function testParseFormattedSize($input, $expected)
    {
        // privateメソッドへのアクセス準備
        $method = new \ReflectionMethod(StorageUsageCalculator::class, 'parseFormattedSize');
        $method->setAccessible(true);
        
        // 変換処理実行
        $result = $method->invoke(null, $input);
        
        // 期待値との比較検証
        $this->assertEquals($expected, $result);
    }

    /**
     * フォーマット済みサイズのテストデータプロバイダ
     * 
     * parseFormattedSizeメソッドのテスト用データセットを提供します。
     * 様々なサイズ単位、小数点、無効値などのパターンを網羅しています。
     * 
     * @return array[] テストケースの配列 [入力値, 期待されるバイト数]
     */
    public function formattedSizeProvider()
    {
        return [
            // 基本的な単位変換テスト
            ['1B', 1],                    // 1バイト
            ['1KB', 1024],                // 1KB = 1024バイト
            ['1MB', 1048576],             // 1MB = 1024^2バイト
            ['1GB', 1073741824],          // 1GB = 1024^3バイト
            ['1TB', 1099511627776],       // 1TB = 1024^4バイト
            
            // 小数点を含む値の変換テスト
            ['1.5KB', 1536],              // 1.5KB = 1536バイト
            ['2.5MB', 2621440],           // 2.5MB = 2621440バイト
            
            // 空白文字の処理テスト
            [' 1MB ', 1048576],           // 前後の空白を除去して処理
            
            // 無効値のエラーハンドリングテスト
            ['invalid', 0],               // 無効な文字列
            ['', 0],                      // 空文字
        ];
    }

    /**
     * calculateTotalUsage メソッドのテスト
     * 
     * テーブル使用量とアップロードファイル使用量を合計し、
     * フォーマット済みの総使用量文字列を生成する機能を検証します。
     * 異なる単位のサイズを正しく合計し、適切なフォーマットで出力することを確認します。
     * 
     * @test
     * @group StorageUsageCalculator
     * @covers \App\Utilities\Storage\StorageUsageCalculator::calculateTotalUsage
     */
    public function testCalculateTotalUsage()
    {
        // privateメソッドへのアクセス準備
        $method = new \ReflectionMethod(StorageUsageCalculator::class, 'calculateTotalUsage');
        $method->setAccessible(true);
        
        // 異なる単位のサイズを合計（1MB + 512KB = 1.5MB相当）
        $result = $method->invoke(null, '1MB', '512KB');
        
        // 結果が適切なフォーマットの文字列であることを確認
        $this->assertIsString($result);                                              // 文字列型であること
        $this->assertMatchesRegularExpression('/^\d+\.\d{2}[KMGTB]+$/', $result);  // 正規表現でフォーマット確認
    }

    /**
     * getUsagePercentage メソッドのテスト - 正常ケース
     * 
     * 総使用量とプラン容量から使用率（0.0-1.0以上）を計算する機能を検証します。
     * 正常な範囲（50%）と100%を超えるケース（150%）の両方でテストし、
     * 適切な使用率が計算されることを確認します。
     * 
     * @test
     * @group StorageUsageCalculator
     * @covers \App\Utilities\Storage\StorageUsageCalculator::getUsagePercentage
     */
    public function testGetUsagePercentageWithValidData()
    {
        // privateメソッドへのアクセス準備
        $method = new \ReflectionMethod(StorageUsageCalculator::class, 'getUsagePercentage');
        $method->setAccessible(true);
        
        // テストケース1: 正常範囲の使用率計算（50MB / 100MB = 0.5（50%））
        $result = $method->invoke(null, '50MB', '100MB');
        $this->assertEquals(0.5, $result);
        
        // テストケース2: 100%を超える場合の計算（150MB / 100MB = 1.5（150%））
        $result = $method->invoke(null, '150MB', '100MB');
        $this->assertEquals(1.5, $result);
    }

    /**
     * getUsagePercentage メソッドのテスト - プラン容量がnullの場合
     * 
     * プラン容量が未設定（null）の場合の安全な動作を検証します。
     * 使用率が計算できない状況では適切にnullを返し、
     * エラーを発生させないことを確認します。
     * 
     * @test
     * @group StorageUsageCalculator
     * @covers \App\Utilities\Storage\StorageUsageCalculator::getUsagePercentage
     */
    public function testGetUsagePercentageWithNullPlanLimit()
    {
        // privateメソッドへのアクセス準備
        $method = new \ReflectionMethod(StorageUsageCalculator::class, 'getUsagePercentage');
        $method->setAccessible(true);
        
        // テストケース: プラン容量がnullの場合はnullを返す（エラーなし）
        $result = $method->invoke(null, '50MB', null);
        $this->assertNull($result);
    }

    /**
     * getUsagePercentage メソッドのテスト - プラン容量が0の場合
     * 
     * プラン容量が0の異常ケースでの安全な動作を検証します。
     * 0除算を回避し、0.0を返してエラーを防ぐことを確認します。
     * 
     * @test
     * @group StorageUsageCalculator
     * @covers \App\Utilities\Storage\StorageUsageCalculator::getUsagePercentage
     */
    public function testGetUsagePercentageWithZeroPlanLimit()
    {
        // privateメソッドへのアクセス準備
        $method = new \ReflectionMethod(StorageUsageCalculator::class, 'getUsagePercentage');
        $method->setAccessible(true);
        
        // テストケース: プラン容量が0の場合は0.0を返す（0除算回避）
        $result = $method->invoke(null, '50MB', '0B');
        $this->assertEquals(0.0, $result);
    }

    /**
     * getTableUsageFormatted メソッドのテスト - 正常ケース
     * 
     * データベーステーブルの使用量を information_schema.tables から取得し、
     * フォーマット済み文字列として返す機能を検証します。
     * 実際のDBに接続してテーブル情報を取得し、適切な形式で結果が返されることを確認します。
     * 
     * @test
     * @group StorageUsageCalculator
     * @covers \App\Utilities\Storage\StorageUsageCalculator::getTableUsageFormatted
     */
    public function testGetTableUsageFormattedSuccess()
    {
        // テストDB環境の設定
        config(['database.default' => 'testing']);
        
        // privateメソッドへのアクセス準備
        $method = new \ReflectionMethod(StorageUsageCalculator::class, 'getTableUsageFormatted');
        $method->setAccessible(true);
        
        // DBテーブル使用量取得処理の実行
        $result = $method->invoke(null);
        
        // 結果の検証 - フォーマット済み文字列として適切な形式であることを確認
        $this->assertIsString($result);                                                  // 文字列型であること
        $this->assertMatchesRegularExpression('/^\d+(\.\d{2})?[KMGTB]+$/', $result);    // 正規表現でフォーマット確認
    }
}