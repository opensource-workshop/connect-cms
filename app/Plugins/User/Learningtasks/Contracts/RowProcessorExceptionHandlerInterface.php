<?php

namespace App\Plugins\User\Learningtasks\Contracts;

use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * CSV行処理中の例外ハンドリング戦略を定義するインターフェース
 */
interface RowProcessorExceptionHandlerInterface
{
    // 結果を表す定数
    public const OUTCOME_ERROR = 'error';
    public const OUTCOME_SKIP = 'skip';

    // ログレベルを表す定数 (Log ファサードのメソッド名に合わせる)
    public const LOG_ERROR = 'error';
    public const LOG_WARN = 'warning';
    public const LOG_INFO = 'info';
    public const LOG_DEBUG = 'debug';

    // 配列キーを示す定数
    public const KEY_OUTCOME = 'outcome';
    public const KEY_TYPE = 'type';
    public const KEY_LOG_LEVEL = 'log_level';

    /**
     * 捕捉された例外を処理し、その結果（エラーかスキップか、詳細タイプ、ログレベル）を返す。
     *
     * @param Throwable $e 捕捉された例外オブジェクト
     * @return array|null 処理方法を示す配列 ['outcome' => 'error'|'skip', 'type' => string, 'log_level' => string] を返す。
     * 処理できない/予期せぬ例外の場合は null を返すことも可能（その場合は Importer 側で 'unexpected_error' として扱われる）。
     */
    public function handle(Throwable $e): ?array;

    /**
     * ValidationException から整形済みのメッセージを取得するヘルパ
     * @param Throwable $e 捕捉された例外オブジェクト
     * @return string
     */
    public function formatValidationMessage(ValidationException $e): string;
}
