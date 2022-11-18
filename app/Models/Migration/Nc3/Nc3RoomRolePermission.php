<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3RoomRolePermission extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'room_role_permissions';

    /**
     * ルームのブロック権限 をfirstOrNewで取得
     */
    public static function firstOrNewRoomRolePermission(int $room_id, string $role_key, string $permission)
    {
        return Nc3RolesRoom::select('roles_rooms.role_key', 'room_role_permissions.*')
            ->join('room_role_permissions', function ($join) use ($permission) {
                $join->on('room_role_permissions.roles_room_id', '=', 'roles_rooms.id')
                    ->where('room_role_permissions.permission', $permission);
            })
            ->where('roles_rooms.room_id', $room_id)
            ->where('roles_rooms.role_key', $role_key)
            ->first() ?? new Nc3RoomRolePermission();
    }
}
