<?php

namespace Tests\Unit\Plugins\User\Learningtasks\Factories;

use App\Plugins\User\Learningtasks\Contracts\RowProcessorExceptionHandlerInterface;
use App\Plugins\User\Learningtasks\Factories\ExceptionHandlerFactory;
use App\Plugins\User\Learningtasks\Handlers\ReportExceptionHandler;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * ExceptionHandlerFactory のユニットテストクラス
 * @coversDefaultClass \App\Plugins\User\Learningtasks\Factories\ExceptionHandlerFactory
 */
class ExceptionHandlerFactoryTest extends TestCase
{
    /** @var ExceptionHandlerFactory */
    private $factory;

    /** 各テスト前に Factory を準備 */
    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new ExceptionHandlerFactory();
    }

    /**
     * make メソッドが "report" タイプに対して ReportExceptionHandler を返すことをテスト
     * @test
     * @covers ::make
     * @group learningtasks
     * @group learningtasks-factory
     */
    public function makeReturnsReportHandlerForReportType(): void
    {
        // Arrange
        $import_type = 'report';

        // Act: Factory の make メソッドを実行
        $handler = $this->factory->make($import_type);

        // Assert: 正しいインスタンスタイプとインターフェース実装を確認
        $this->assertInstanceOf(ReportExceptionHandler::class, $handler);
        $this->assertInstanceOf(RowProcessorExceptionHandlerInterface::class, $handler);
    }

    /**
     * make メソッドが未知のタイプに対して InvalidArgumentException をスローすることをテスト
     * @test
     * @covers ::make
     * @group learningtasks
     * @group learningtasks-factory
     */
    public function makeThrowsExceptionForUnknownType(): void
    {
        // Arrange
        $unknown_type = 'some_invalid_type_string';

        // Assert: 例外のスローを期待
        $this->expectException(InvalidArgumentException::class);
        // 例外メッセージも検証 (Factory 内のメッセージと一致させる)
        $this->expectExceptionMessageMatches("/未知のインポートタイプに対応する例外ハンドラが見つかりません.*{$unknown_type}/");

        // Act: Factory の make メソッドを実行
        $this->factory->make($unknown_type);
    }

    // 将来 'exam' タイプを追加した場合のテスト例 (今はスキップ)
    /**
     * make メソッドが "exam" タイプに対して ExamExceptionHandler を返すテスト (将来用)
     * @test
     * @group learningtasks
     * @group learningtasks-factory
     * @group learningtasks-future
     */
    // public function makeReturnsExamHandlerForExamType(): void
    // {
    //     $this->markTestSkipped('ExamExceptionHandler is not yet implemented.');
    //
    //     // Arrange
    //     $import_type = 'exam';
    //     // bind が setUp になければここで bind
    //     // $this->app->bind(ExamExceptionHandler::class);
    //
    //     // Act
    //     $handler = $this->factory->make($import_type);
    //
    //     // Assert
    //     $this->assertInstanceOf(ExamExceptionHandler::class, $handler);
    //     $this->assertInstanceOf(RowProcessorExceptionHandlerInterface::class, $handler);
    // }
}
