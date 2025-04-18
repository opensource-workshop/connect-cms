<?php

namespace App\Plugins\User\Learningtasks\Exceptions;

use Exception;

class AlreadyEvaluatedException extends Exception
{
    /**
     * AlreadyEvaluatedException のコンストラクタ。
     *
     * @param string $message エラーメッセージ。指定されなければデフォルト値を使用。
     * @param int $code エラーコード。
     * @param Throwable|null $previous 前の例外（例外チェーン用）。
     */
    public function __construct(
        string $message = "対象の提出は既に評価済みです。", // デフォルトメッセージ
        int $code = 0,
        ?Throwable $previous = null
    ) {
        // 親クラス (Exception) のコンストラクタを呼び出す
        parent::__construct($message, $code, $previous);
    }
}
