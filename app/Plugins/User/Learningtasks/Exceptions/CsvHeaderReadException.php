<?php

namespace App\Plugins\User\Learningtasks\Exceptions;

use Exception;
use Throwable;

/**
 * CSVヘッダー行の読み取りに失敗した場合にスローされるカスタム例外クラス。
 */
class CsvHeaderReadException extends Exception {
    /**
     * CsvHeaderReadException のコンストラクタ。
     *
     * @param string $message エラーメッセージ。指定されなければデフォルト値を使用。
     * @param int $code エラーコード。
     * @param Throwable|null $previous 前の例外（例外チェーン用）。
     * @throws Throwable
     */
     public function __construct(string $message = "CSVヘッダー行の読み取りに失敗しました。", int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
