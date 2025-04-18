<?php

namespace App\Plugins\User\Learningtasks\Exceptions;

use Exception;
use Throwable;

/**
 * 関連する設定が無効になっているため、機能が利用できない場合にスローされる例外
 */
class FeatureDisabledException extends Exception
{
    public function __construct(string $message = "指定された機能は無効になっています。", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
