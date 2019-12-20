<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
    static public function getValues($name = null)
    {
        if (empty($name)) {
            $configs = Configs::get();
        }
        else {
            $configs = Configs::where('name', $name)->get();
        }

        // Config データの変換
        foreach ( $configs as $config ) {
            $configs_array[$config->name] = $config->value;
        }
        return $configs_array;
    }
}
