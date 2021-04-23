<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

/**
 * 該当トランザクションテーブルに指定ID($value)のレコードが存在しないこと
 * ※マスタテーブルレコード削除時にトランザクションテーブルに該当マスタ参照が残っていないことのチェックを想定
 */
class CustomVali_TableRecordNotExist implements Rule
{
    // トランザクションテーブル名を指定
    protected $transaction_table_name;
    // トランザクションテーブル内で外部キー参照しているカラム名（マスタテーブルのキー値を保持しているカラム）を指定
    protected $foreign_key_column_name;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($transaction_table_name, $foreign_key_column_name)
    {
        $this->transaction_table_name = $transaction_table_name;
        $this->foreign_key_column_name = $foreign_key_column_name;
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
        // dd($this->transaction_table_name, $this->foreign_key_column_name);
        return DB::table($this->transaction_table_name)
            ->where($this->foreign_key_column_name, $value)
            ->first() === null;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return \Lang::get('messages.cannot_be_delete_refers_to_the_information');
    }
}
