<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3Room extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'rooms';

    const
        role_key_room_administrator = 'room_administrator',  // ルーム管理者
        role_key_chief_editor       = 'chief_editor',        // 編集長
        role_key_editor             = 'editor',              // 編集者
        role_key_general_user       = 'general_user',        // 一般
        role_key_visitor            = 'visitor';             // ゲスト
}
