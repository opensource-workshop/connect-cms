<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Support\Facades\Log;

class Configs extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['name', 'value', 'category', 'additional1', 'additional2', 'additional3', 'additional4', 'additional5'];

    /**
     * 値から改行を取りにぞいたものを返す
     */
    public function getNobrValue()
    {
        return str_replace("\r\n", "", $this->value);
    }

    /**
     * name をキーにした配列で返す。
     */
    public static function getValues($name = null)
    {
        if (empty($name)) {
            $configs = Configs::get();
        } else {
            $configs = Configs::where('name', $name)->get();
        }

        // Config データの変換
        foreach ($configs as $config) {
            $configs_array[$config->name] = $config->value;
        }
        return $configs_array;
    }

    /**
     * 外部認証設定 取得
     */
    public static function getAuthMethod()
    {
        // Config チェック
        $use_auth_method = Configs::where('name', 'use_auth_method')->first();

        // 外部認証を使用しない場合、newで戻す(空として扱う)
        if (empty($use_auth_method) || $use_auth_method->value == '0') {
            return new Configs();
        }

        $auth_method = Configs::where('name', 'auth_method')->first();

        // 使用する外部認証がない場合、newで戻す(空として扱う)
        if (empty($auth_method) || $auth_method->value == '') {
            return new Configs();
        }

        // auth_methodで取得したconfigsを返す
        return $auth_method;
    }
}
