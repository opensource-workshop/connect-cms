<?php

namespace App\Plugins\User\Learningtasks\Exceptions;

use Exception;
use Throwable;

/**
 * CSVヘッダーが不正な場合にスローされるカスタム例外クラス。
 */
class CsvInvalidHeaderException extends Exception
{
    /**
     * CsvInvalidHeaderException のコンストラクタ。
     *
     * @param string $message エラーメッセージ。指定されなければデフォルト値を使用。
     * @param int $code エラーコード。
     * @param Throwable|null $previous 前の例外（例外チェーン用）。
     * @throws Throwable
     */
    public function __construct(string $message = "CSVヘッダーが不正です。", int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
