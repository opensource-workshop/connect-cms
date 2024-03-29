<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Lang;

use App\Models\Core\UsersInputCols;
use App\User;

use App\Plugins\Manage\UserManage\UsersTool;

use App\Enums\UserColumnType;

/**
 * ユーザー情報のメールと、ユーザー追加項目のメールのユニークチェック
 */
class CustomValiUserEmailUnique implements Rule
{
    protected $user_id;
    /** 項目セットID */
    protected $columns_set_id;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(?int $columns_set_id, $user_id = null)
    {
        $this->user_id = $user_id;
        $this->columns_set_id = $columns_set_id;
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
        // *** ユーザ情報
        // 自分以外でメール項目に同じメールがあるか
        $input_cols = User::where('id', '!=', $this->user_id)
            ->where('email', $value)
            ->first();

        if (!empty($input_cols)) {
            // 値ありはメール重複
            return false;
        }

        // *** ユーザ追加項目
        // カラム取得
        $users_columns = UsersTool::getUsersColumns($this->columns_set_id);

        $users_column_ids = [];
        foreach ($users_columns as $users_column) {
            if ($users_column->column_type == UserColumnType::mail) {
                // メールのカラムidのみ抽出
                $users_column_ids[] = $users_column->id;
            }
        }

        if (empty($users_column_ids)) {
            // 追加項目でメール項目がなければチェックしない（チェックOKとみなす）
            return true;
        }

        // 自分以外でメール項目に同じメールがあるか
        $input_cols = UsersInputCols::where('users_id', '!=', $this->user_id)
            ->whereIn('users_columns_id', $users_column_ids)
            ->where('value', $value)
            ->first();

        if (!empty($input_cols)) {
            // 値ありはメール重複
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
        return Lang::get('validation.unique');
    }
}
