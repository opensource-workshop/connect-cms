<?php

namespace Tests\Unit\Plugins\User\Learningtasks\Factories;

use App\Plugins\User\Learningtasks\Contracts\RowProcessorInterface;
use App\Plugins\User\Learningtasks\Factories\RowProcessorFactory;
use App\Plugins\User\Learningtasks\Services\LearningtaskEvaluationRowProcessor;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * RowProcessorFactory のユニットテストクラス
 * @coversDefaultClass \App\Plugins\User\Learningtasks\Factories\RowProcessorFactory
 */
class RowProcessorFactoryTest extends TestCase
{
    /** @var RowProcessorFactory */
    private $factory;

    /** 各テスト前に Factory を準備 */
    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new RowProcessorFactory();
    }

    /**
     * "report" タイプが指定された場合に LearningtaskEvaluationRowProcessor が返ることをテスト
     * @test
     * @covers ::make
     * @group learningtasks
     * @group learningtasks-factory
     */
    public function makeReturnsEvaluationProcessorForReportType(): void
    {
        // Arrange: コンテナが Processor を解決できるように準備 (任意だが確実性のため)
        // ServiceProvider で bind されていれば不要な場合も多いが、テスト内で明示的に bind しても良い。
        // Auto-wiring が効くはずなので、ここでは bind しないで試す。
        // $this->app->bind(LearningtaskEvaluationRowProcessor::class);

        $import_type = 'report';

        // Act: Factory の make メソッドを実行 (内部で app() が呼ばれる)
        $processor = $this->factory->make($import_type);

        // Assert: 返されたインスタンスが期待した型であること、およびインターフェースを実装していることを確認
        $this->assertInstanceOf(LearningtaskEvaluationRowProcessor::class, $processor, 'report タイプは LearningtaskEvaluationRowProcessor を返す');
        $this->assertInstanceOf(RowProcessorInterface::class, $processor, '返されたオブジェクトはインターフェースを実装している');
    }

    /**
     * 未知のタイプが指定された場合に InvalidArgumentException がスローされることをテスト
     * @test
     * @covers ::make
     * @group learningtasks
     * @group learningtasks-factory
     */
    public function makeThrowsExceptionForUnknownType(): void
    {
        // Arrange
        $unknown_type = 'unknown_type_string';

        // Assert: 例外がスローされることを期待
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches("/未知の行処理タイプ.*{$unknown_type}/");

        // Act: Factory の make メソッドを実行
        $this->factory->make($unknown_type);
    }

    // 将来 'exam' タイプが追加された際のテストケース例
    // public function testMakeReturnsExamProcessorForExamType(): void
    // {
    //     // Arrange
    //     // $this->app->bind(LearningtaskExamRowProcessor::class); // 必要なら bind
    //     $import_type = 'exam';
    //     // Act
    //     $processor = $this->factory->make($import_type);
    //     // Assert
    //     $this->assertInstanceOf(LearningtaskExamRowProcessor::class, $processor);
    //     $this->assertInstanceOf(RowProcessorInterface::class, $processor);
    // }
}
