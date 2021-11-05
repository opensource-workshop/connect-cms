<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use Illuminate\Support\Facades\Lang;

/**
 * 同値チェック
 */
class CustomValiConfirmed implements Rule
{
    protected $attr;                // バリデーション対象の項目名（エラーメッセージに表示させる項目名）
    protected $confirmed_value;     // 同値チェックするペアの値

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($attr, $confirmed_value)
    {
        $this->attr = $attr;
        $this->confirmed_value = $confirmed_value;
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
        return $this->confirmed_value == $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->attr . Lang::get('messages.not_match_confirmation_value');
    }
}
