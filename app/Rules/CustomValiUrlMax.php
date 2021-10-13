<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * URL 項目最大バイト数越えチェック
 */
class CustomValiUrlMax implements Rule
{
    // バイト数計算する際、.env の APP_URL の文字数を加算するかどうか。（初期値は加算しない）
    protected $add_app_url;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($add_app_url = false)
    {
        $this->add_app_url = $add_app_url;
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
        // 8,190 バイトまではOK
        if ($this->add_app_url) {
            $value = config('app.url') . $value;
        }
        return (strlen($value) > config('connect.URL_MAX_BYTE')) ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->add_app_url) {
            $max_str = config('connect.URL_MAX_BYTE') - strlen(config('app.url'));
        } else {
            $max_str = config('connect.URL_MAX_BYTE');
        }

        return ':attributeには' . number_format($max_str) . 'バイト以下の文字列を指定してください。';
    }
}
