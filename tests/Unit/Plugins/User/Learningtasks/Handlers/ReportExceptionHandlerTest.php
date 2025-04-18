<?php

namespace Tests\Unit\Plugins\User\Learningtasks\Handlers;

// --- テスト対象クラス ---
use App\Plugins\User\Learningtasks\Handlers\ReportExceptionHandler;
// --- テストで使用する例外クラス ---
use App\Plugins\User\Learningtasks\Exceptions\AlreadyEvaluatedException;
use App\Plugins\User\Learningtasks\Exceptions\InvalidStudentException;
use App\Plugins\User\Learningtasks\Exceptions\SubmissionNotFoundException;
use Exception; // Generic Exception
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException; // Laravel Validation Exception
use InvalidArgumentException; // その他の汎用例外の例
use Error; // PHP Error (Throwable)
use Throwable; // Type hint 用
// --- Testing ---
use Illuminate\Contracts\Validation\Validator as ValidatorContract; // Validator モック用
use Illuminate\Support\MessageBag;
use Mockery; // Validator モック用
use Tests\TestCase;


/**
 * ReportExceptionHandler のユニットテストクラス
 * @coversDefaultClass \App\Plugins\User\Learningtasks\Handlers\ReportExceptionHandler
 */
class ReportExceptionHandlerTest extends TestCase
{
    /** @var ReportExceptionHandler */
    private $handler;

    /** 各テスト前にハンドラを準備 */
    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new ReportExceptionHandler();
    }

     /** 各テスト後に Mockery コンテナをクリーンアップ (Validator モック用) */
     protected function tearDown(): void
     {
         parent::tearDown();
         Mockery::close();
     }

    /**
     * handle メソッドが各種例外に対して正しい設定配列または null を返すことをテスト
     *
     * @test
     * @covers ::handle
     * @dataProvider exceptionDataProvider
     * @group learningtasks
     * @group learningtasks-handler
     */
    public function handleReturnsCorrectConfigForExceptions(Throwable $exception, ?array $expected_config): void
    {
        // Act: ハンドラの handle メソッドを実行
        $actual_config = $this->handler->handle($exception);

        // Assert: 戻り値が期待通りか検証
        $this->assertEquals($expected_config, $actual_config);
    }

    /**
     * handle メソッドテスト用のデータプロバイダ
     *
     * @return array<string, array{exception: Throwable, expected_config: ?array}>
     */
    public function exceptionDataProvider(): array
    {
        // ValidationException のインスタンス化には Validator が必要。
        // instanceof の型チェックだけが目的なら、単純なモックで代用する。
        /** @var \Illuminate\Contracts\Validation\Validator|MockInterface $validatorMock */
        $validatorMock = Mockery::mock(ValidatorContract::class);
        // errors() が MessageBag を返すように設定 (ValidationException のコンストラクタで必要)
        $validatorMock->shouldReceive('errors')->andReturn(new MessageBag());
        $validationException = new ValidationException($validatorMock);

        // テストケース名 => [渡す例外オブジェクト, 期待される戻り値] の配列
        return [
            'ValidationExceptionの場合' => [
                'exception' => $validationException, // モックを使う
                'expected_config' => ['outcome' => 'error', 'type' => 'validation_error', 'log_level' => 'warning']
            ],
            'InvalidStudentExceptionの場合' => [
                'exception' => new InvalidStudentException(),
                'expected_config' => ['outcome' => 'skip', 'type' => 'invalid_student', 'log_level' => 'info']
            ],
            'AlreadyEvaluatedExceptionの場合' => [
                'exception' => new AlreadyEvaluatedException(),
                'expected_config' => ['outcome' => 'skip', 'type' => 'already_evaluated', 'log_level' => 'info']
            ],
            'SubmissionNotFoundExceptionの場合' => [
                'exception' => new SubmissionNotFoundException(),
                'expected_config' => ['outcome' => 'error', 'type' => 'submission_not_found', 'log_level' => 'error']
            ],
            'ModelNotFoundExceptionの場合' => [
                'exception' => new ModelNotFoundException("Test model not found"), // メッセージは任意
                'expected_config' => ['outcome' => 'error', 'type' => 'processing_error_user_not_found', 'log_level' => 'error']
            ],
            'その他の汎用Exceptionの場合' => [
                'exception' => new Exception("Generic error"), // マップにない Exception
                'expected_config' => null // ハンドルできないので null を期待
            ],
            'PHP Errorの場合' => [
                'exception' => new Error("Generic PHP error"), // マップにない Throwable (Error)
                'expected_config' => null // ハンドルできないので null を期待
             ],
             'マップにない他のExceptionの場合' => [
                 'exception' => new InvalidArgumentException("Other type"),
                 'expected_config' => null
             ],
        ];
    }
}
