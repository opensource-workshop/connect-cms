<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * MySQL のText 型項目最大バイト数越えチェック
 */
class CustomValiTextMax implements Rule
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
     * Validate that an attribute is a valid e-mail address.
     *
     * @param  string  $attribute 項目名
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return (strlen($value) > config('connect.TEXT_MAX_BYTE')) ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attributeには' . number_format(config('connect.TEXT_MAX_BYTE')) . 'バイト以下の文字列を指定してください。';
    }
}
