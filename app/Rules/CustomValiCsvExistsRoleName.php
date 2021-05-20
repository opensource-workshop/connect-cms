<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use App\Models\Core\Configs;

use App\Plugins\Manage\UserManage\UsersTool;
use App\Utilities\String\StringUtils;

/**
 * CSV用 役割名の存在チェックバリデーション
 */
class CustomValiCsvExistsRoleName implements Rule
{
    protected $configs = null;

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
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute 項目名
     * @param  mixed  $value 画像のフルパスセットする
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

        // 役割設定取得
        $this->configs = Configs::where('category', 'original_role')->get();

        foreach ($check_values as $check_value) {
            $target_group = $this->configs->where('value', $check_value);
            if ($target_group->isEmpty()) {
                // 役割名がないならエラー
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
        return ':attributeには ' . implode(',', $this->configs->implode('value', ', ')) . ' のうちいずれかを指定してください。';
    }
}
