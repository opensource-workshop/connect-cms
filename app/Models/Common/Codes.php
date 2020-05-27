<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\UserableNohistory;

/**
 * コードテーブルのモデル
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コード管理
 * @package Model
 */
class Codes extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 論理削除
    use SoftDeletes;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'codes_help_messages_alias_key',
        'plugin_name',
        'buckets_id',
        'prefix',
        'type_name',
        'type_code1',
        'type_code2',
        'type_code3',
        'type_code4',
        'type_code5',
        'code',
        'value',
        'additional1',
        'additional2',
        'additional3',
        'additional4',
        'additional5',
        'display_sequence',
    ];

    /**
     *  コードから値を取り出し
     */
    public static function getCodeToValue($codes, $target_code, $return_colum = 'value')
    {
        foreach ($codes as $code) {
            if ($code->code == $target_code) {
                // return $code->value;
                return $code->$return_colum;
            }
        }
        return null;
    }
}
