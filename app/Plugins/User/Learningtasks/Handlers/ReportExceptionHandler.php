<?php

namespace App\Plugins\User\Learningtasks\Handlers;

use App\Plugins\User\Learningtasks\Contracts\RowProcessorExceptionHandlerInterface as HandlerInterface;
use App\Plugins\User\Learningtasks\Exceptions\AlreadyEvaluatedException;
use App\Plugins\User\Learningtasks\Exceptions\InvalidStudentException;
use App\Plugins\User\Learningtasks\Exceptions\SubmissionNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReportExceptionHandler implements HandlerInterface
{
    // このハンドラ固有のエラー/スキップタイプ定数
    public const TYPE_VALIDATION = 'validation_error';
    public const TYPE_INVALID_STUDENT = 'invalid_student';
    public const TYPE_ALREADY_EVALUATED = 'already_evaluated';
    public const TYPE_SUBMISSION_NOT_FOUND = 'submission_not_found';
    public const TYPE_USER_NOT_FOUND = 'processing_error_user_not_found';

    /**
     * レポート評価インポートにおける例外を処理する
     */
    public function handle(Throwable $e): ?array
    {
        if ($e instanceof ValidationException) {
            // バリデーションエラーは 'error'
            return [
                HandlerInterface::KEY_OUTCOME => HandlerInterface::OUTCOME_ERROR,
                HandlerInterface::KEY_TYPE => self::TYPE_VALIDATION,
                HandlerInterface::KEY_LOG_LEVEL => HandlerInterface::LOG_WARN
            ];
        } elseif ($e instanceof InvalidStudentException) {
            // 受講生エラーは 'skip'
            return [
                HandlerInterface::KEY_OUTCOME => HandlerInterface::OUTCOME_SKIP,
                HandlerInterface::KEY_TYPE => self::TYPE_INVALID_STUDENT,
                HandlerInterface::KEY_LOG_LEVEL => HandlerInterface::LOG_INFO
            ];
        } elseif ($e instanceof AlreadyEvaluatedException) {
            // 評価済みエラーは 'skip'
            return [
                HandlerInterface::KEY_OUTCOME => HandlerInterface::OUTCOME_SKIP,
                HandlerInterface::KEY_TYPE => self::TYPE_ALREADY_EVALUATED,
                HandlerInterface::KEY_LOG_LEVEL => HandlerInterface::LOG_INFO
            ];
        } elseif ($e instanceof SubmissionNotFoundException) {
            // 提出なしエラーは 'error'
            return [
                HandlerInterface::KEY_OUTCOME => HandlerInterface::OUTCOME_ERROR,
                HandlerInterface::KEY_TYPE => self::TYPE_SUBMISSION_NOT_FOUND,
                HandlerInterface::KEY_LOG_LEVEL => HandlerInterface::LOG_ERROR
            ];
        } elseif ($e instanceof ModelNotFoundException) {
            // ユーザーが見つからないエラーは 'error'
            return [
                HandlerInterface::KEY_OUTCOME => HandlerInterface::OUTCOME_ERROR,
                HandlerInterface::KEY_TYPE => self::TYPE_USER_NOT_FOUND,
                HandlerInterface::KEY_LOG_LEVEL => HandlerInterface::LOG_ERROR
            ];
        }
        // 上記以外は処理不能（予期せぬエラー）として null を返す
        return null;
    }

    /**
     * ValidationException のメッセージ整形
     */
    public function formatValidationMessage(ValidationException $e): string
    {
        return implode(' ', array_merge(...array_values($e->errors())));
    }
}
