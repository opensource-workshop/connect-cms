<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use Illuminate\Validation\Concerns\ValidatesAttributes;

use Illuminate\Support\Facades\Lang;

/**
 * 複数カンマ区切りのメールアドレスチェック
 */
class CustomValiEmails implements Rule
{
    // Laravelのvalidateチェックメソッド
    use ValidatesAttributes;

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
     * Validate that an attribute is a valid e-mail address.
     *
     * @param  string  $attribute 項目名
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // カンマ区切り文字列を配列に
        $emails = explode(',', $value);

        foreach ($emails as $email) {
            // laravel validate to email チェック. default RFCValidation.
            if (! $this->validateEmail($attribute, trim($email), ['rfc'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return Lang::get('validation.email');
    }
}
