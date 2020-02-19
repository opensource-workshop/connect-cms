<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * 文字の幅数チェック
 */
class CustomVali_CheckWidthForString implements Rule
{
    protected $attr;                // バリデーション対象の項目名（エラーメッセージに表示させる項目名）
    protected $target_digits;       // バリデーション対象の文字数
    protected $comparison_digits;   // 制限文字数（半角換算）

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($attr, $comparison_digits)
    {
        $this->attr = $attr;
        $this->comparison_digits = $comparison_digits;
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
        $this->target_digits = mb_strwidth($value);
        // 文字幅が指定文字数以下であること。（全角は2文字換算、半角は1文字換算）
        return $this->target_digits <= $this->comparison_digits;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return \App::getLocale() == \Locale::ja ? 
            $this->attr . 'は全角' . floor($this->comparison_digits/2) . '文字（半角' . $this->comparison_digits . '文字）以内で入力してください。(半角換算した文字数：' . $this->target_digits . ')' :
            'Enter ' . $this->attr . ' within ' . floor($this->comparison_digits/2) . ' full-width (' . $this->comparison_digits . ' half-width) characters. (Number of half-width characters converted: ' . $this->target_digits . ')';
    }
}
