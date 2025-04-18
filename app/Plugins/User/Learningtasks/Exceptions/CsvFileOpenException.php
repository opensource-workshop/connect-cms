<?php

namespace App\Plugins\User\Learningtasks\Exceptions;

use Exception;
use Throwable;

/**
 * CSVファイルを開けなかった場合にスローされるカスタム例外クラス。
 */
class CsvFileOpenException extends Exception {
    /**
     * CsvFileOpenException のコンストラクタ。
     *
     * @param string $message エラーメッセージ。指定されなければデフォルト値を使用。
     * @param int $code エラーコード。
     * @param Throwable|null $previous 前の例外（例外チェーン用）。
     * @throws Throwable
     */
    public function __construct(string $message = "CSVファイルを開けませんでした。", int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
