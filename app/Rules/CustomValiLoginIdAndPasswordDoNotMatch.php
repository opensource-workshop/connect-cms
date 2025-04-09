<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * ログインIDとパスワードの不一致チェック
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ユーザ登録
 * @package Rule
 */
class CustomValiLoginIdAndPasswordDoNotMatch implements Rule
{
    /** ログインID */
    protected $login_id;
    /** ログインIDのカラム名 */
    protected $login_id_column_name;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(?string $login_id, string $login_id_column_name)
    {
        $this->login_id = $login_id;
        $this->login_id_column_name = $login_id_column_name;
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
        if ($value == $this->login_id) {
            return false;
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
        return ":attributeには{$this->login_id_column_name}と同じ文字列は指定できません。";
    }
}
