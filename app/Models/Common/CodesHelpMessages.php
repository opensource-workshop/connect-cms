<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

/**
 * 注釈設定テーブルのモデル
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コード管理
 * @package Model
 */
class CodesHelpMessages extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    /**
    * create()やupdate()で入力を受け付ける ホワイトリスト
    */
    protected $fillable = [
        'alias_key',
        'name',
        'plugin_name_help_message',
        'buckets_id_help_message',
        'prefix_help_message',
        'type_name_help_message',
        'type_code1_help_message',
        'type_code2_help_message',
        'type_code3_help_message',
        'type_code4_help_message',
        'type_code5_help_message',
        'code_help_message',
        'value_help_message',
        'additional1_help_message',
        'additional2_help_message',
        'additional3_help_message',
        'additional4_help_message',
        'additional5_help_message',
        'display_sequence_help_message',
        'codes_help_messages_id_help_message',
        'display_sequence',
    ];
}
