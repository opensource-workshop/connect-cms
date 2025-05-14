<?php

namespace Tests\Unit\Plugins\User\Learningtasks\Factories;

use App\Enums\LearningtaskExportType;
use App\Enums\LearningtaskImportType;
use App\Enums\LearningtaskUseFunction;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Plugins\User\Learningtasks\Contracts\ColumnDefinitionInterface;
use App\Plugins\User\Learningtasks\Exceptions\FeatureDisabledException;
use App\Plugins\User\Learningtasks\Factories\ColumnDefinitionFactory;
use App\Plugins\User\Learningtasks\Services\LearningtaskReportColumnDefinition;
use App\Plugins\User\Learningtasks\Services\LearningtaskSettingChecker;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * ColumnDefinitionFactory のユニットテストクラス
 * @coversDefaultClass \App\Plugins\User\Learningtasks\Factories\ColumnDefinitionFactory
 */
class ColumnDefinitionFactoryTest extends TestCase
{
    /** @var ColumnDefinitionFactory */
    private $factory;

    /** @var LearningtaskSettingChecker|MockInterface */
    private $mock_checker;

    /** 各テスト前に Factory を準備 */
    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new ColumnDefinitionFactory();
        // テストメソッド内で isEnabled の振る舞いを設定するため、基本的なモックだけ用意
        $this->mock_checker = Mockery::mock(LearningtaskSettingChecker::class);
    }

    /**
     * "report" タイプが指定された場合に LearningtaskReportColumnDefinition が返ることをテスト
     * @test
     * @covers ::make
     * @group learningtasks
     * @group learningtasks-factory
     */
    public function makeReturnsReportDefinitionForReportType(): void
    {
        // Arrange: SettingChecker の isEnabled が true を返すようにオーバーロードモックを設定
        $this->mock_checker->shouldReceive('isEnabled')
                     ->with(LearningtaskUseFunction::use_report_evaluate) // 正しい設定名を確認
                     ->once() // 1回呼ばれるはず
                     ->andReturn(true); // ★ 機能有効

        // Act: Factory の make メソッドを実行
        $definition = $this->factory->make(LearningtaskImportType::report, $this->mock_checker);

        // Assert: 正しいインスタンスが返ることを確認
        $this->assertInstanceOf(LearningtaskReportColumnDefinition::class, $definition);
        $this->assertInstanceOf(ColumnDefinitionInterface::class, $definition);
        // (任意) 返された Definition が内部にモックされた Checker を持っているかの確認も可能だが複雑になる
    }

    /**
     * make - report: 設定無効時に FeatureDisabledException がスローされるテスト
     * @test
     * @covers ::make
     * @group learningtasks
     * @group learningtasks-factory
     */
    public function makeThrowsFeatureDisabledExceptionWhenSettingDisabled(): void
    {
        // Arrange: SettingChecker の isEnabled が false を返すようにオーバーロードモックを設定
        $this->mock_checker->shouldReceive('isEnabled')
                    ->with(LearningtaskUseFunction::use_report_evaluate)
                    ->once()
                    ->andReturn(false); // ★ 機能無効

         // Assert: FeatureDisabledException がスローされることを期待
         $this->expectException(FeatureDisabledException::class);
         // 例外メッセージも確認
         $this->expectExceptionMessageMatches('/レポート評価機能が有効になっていません。/');

         // Act: Factory の make メソッドを実行
         $this->factory->make(LearningtaskImportType::report, $this->mock_checker);
    }


    /**
     * make - 未知のタイプ: InvalidArgumentException がスローされるテスト (基本的に変更なし)
     * @test
     * @covers ::make
     * @group learningtasks
     * @group learningtasks-factory
     */
    public function makeThrowsExceptionForUnknownType(): void
    {
        // Arrange
        $unknown_type = 'unknown_type_string';

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/未知のカラム定義タイプ.*{$unknown_type}/");

        // Act
        $this->factory->make($unknown_type, $this->mock_checker);
    }

    /**
     * "export report" タイプが指定された場合に LearningtaskReportColumnDefinition が返ることをテスト
     * @test
     * @covers ::make
     * @group learningtasks
     * @group learningtasks-factory
     */
    public function makeReturnsReportDefinitionForExportReportType(): void
    {
        // Act: Factory の make メソッドを実行
        $definition = $this->factory->make(LearningtaskExportType::report, $this->mock_checker);

        // Assert: 正しいインスタンスが返ることを確認
        $this->assertInstanceOf(LearningtaskReportColumnDefinition::class, $definition);
        $this->assertInstanceOf(ColumnDefinitionInterface::class, $definition);
        // (任意) 返された Definition が内部にモックされた Checker を持っているかの確認も可能だが複雑になる
    }

    // 将来 'exam' タイプが追加された際のテストケース例
    // public function testMakeReturnsExamDefinitionForExamType(): void
    // {
    //     // Arrange
    //     $mock_post = Mockery::mock(LearningtasksPosts::class);
    //     $import_type = 'exam';
    //     // Act
    //     $definition = $this->factory->make($import_type, $mock_post);
    //     // Assert
    //     $this->assertInstanceOf(LearningtaskExamColumnDefinition::class, $definition); // Exam用クラスを確認
    //     $this->assertInstanceOf(ColumnDefinitionInterface::class, $definition);
    // }
}
