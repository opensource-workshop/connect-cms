<?php

namespace App\Plugins\User\Learningtasks\Exceptions;

use Exception;
use Throwable;

/**
 * 評価対象となる提出記録が見つからない場合にスローされる例外
 */
class SubmissionNotFoundException extends Exception
{
    /**
     * コンストラクタ
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "評価対象の提出記録が見つかりません。", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
