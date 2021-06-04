<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use App\Plugins\Manage\UserManage\UsersTool;
use App\Utilities\String\StringUtils;

/**
 * CSV用 パイプ区切りの文字は、該当の名前かチェックするバリデーション
 */
class CustomValiCsvExistsName implements Rule
{
    protected $names = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $names)
    {
        $this->names = $names;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute 項目名
     * @param  mixed  $value パイプ区切りの文字
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            // 空ならOK
            return true;
        }

        // 複数選択のバリデーションの入力値は、配列が前提のため、配列に変換する。
        $check_values = explode(UsersTool::CHECKBOX_SEPARATOR, $value);
        // 配列値の入力値をトリム (preg_replace(/u)で置換. /u = UTF-8 として処理)
        $check_values = StringUtils::trimInput($check_values);
        // \Log::debug(var_export($value, true));
        // \Log::debug(var_export($check_values, true));

        foreach ($check_values as $check_value) {
            if (! in_array($check_value, $this->names)) {
                // 該当がないならエラー
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
        return ':attributeには ' . implode(', ', $this->names) . ' のうちいずれかを指定してください。';
    }
}
