<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * 排他処理チェック
 * 「storage/framework/」配下に、指定名称のロックファイルが存在していた場合は排他エラーを返す
 * ※使用時はチェック処理呼び出し側で事前にロックファイルの配置処理／撤去処理が必要です。
 * ※処理フローイメージ：
 * --- バリデート処理（本バリデータCall）
 * --- ロックファイル配置処理
 * --- 本処理
 * --- ロックファイル撤去処理
 */
class CustomValiExclusiveProcessing implements Rule
{
    protected $lock_file_name;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($lock_file_name)
    {
        $this->lock_file_name = $lock_file_name;
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
        // 排他処理用の物理ファイルが存在しないこと
        return !file_exists(storage_path('framework/' . $this->lock_file_name));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '他の処理が動作しています。しばらく経ってから操作してください。';
    }
}
