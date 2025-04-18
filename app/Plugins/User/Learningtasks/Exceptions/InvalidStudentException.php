<?php

namespace App\Plugins\User\Learningtasks\Exceptions;

use Exception;
use Throwable;

/**
 * インポート対象のユーザーが、当該課題の受講生として登録されていない場合に
 * スローされるカスタム例外クラス。
 */
class InvalidStudentException extends Exception
{
    /**
     * InvalidStudentException のコンストラクタ。
     *
     * @param string $message エラーメッセージ。指定されなければデフォルト値を使用。
     * @param int $code エラーコード。
     * @param Throwable|null $previous 前の例外（例外チェーン用）。
     */
    public function __construct(
        string $message = "指定されたユーザーはこの課題の受講生ではありません。", // デフォルトメッセージ
        int $code = 0,
        ?Throwable $previous = null
    ) {
        // 親クラス (Exception) のコンストラクタを呼び出す
        parent::__construct($message, $code, $previous);
    }
}
