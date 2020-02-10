<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CustomVali_AlphaNumForMultiByte implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 半角英数のみ許可
     * ※LaravelデフォルトのalphaNumだと全角文字が許容されてしまう為
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match('/^[A-Za-z\d]+$/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.alpha_num');
    }
}
