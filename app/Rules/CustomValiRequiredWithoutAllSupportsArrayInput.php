<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * いずれか必須. 配列入力にも対応
 */
class CustomValiRequiredWithoutAllSupportsArrayInput  implements Rule
{
    protected $values = null;
    protected $name = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $values, string $name)
    {
        $this->values = $values;
        $this->name = $name;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute 項目名
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->values[] = $value;

        foreach ($this->values as $val) {

            if (is_array($val)) {
                // 配列チェック
                foreach ($val as $v) {
                    if (! empty($v)) {
                        // 値ありならOK
                        return true;
                    }
                }

            } else {
                // 文字列の想定
                if (! empty($val)) {
                    // 値ありならOK
                    return true;
                }
            }
        }

        // 該当がないならエラー
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "{$this->name} のうちいずれかを入力してください。";
    }
}
