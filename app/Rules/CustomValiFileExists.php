<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * ファイルフルパス存在チェック
 */
class CustomValiFileExists implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!file_exists($value)) {
            // エラー
            return false;
        }

        if (!is_file($value)) {
            // エラー
            return false;
        }

        // 正常
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attributeに入力されたファイルが存在しません。';
    }
}
