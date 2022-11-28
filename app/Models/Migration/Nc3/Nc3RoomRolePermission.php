<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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

    /**
     * room_idの配列 からルーム権限 を取得
     */
    public static function getRoomRolePermissionsByRoomIds($room_ids): Collection
    {
        return Nc3RolesRoom::
            select(
                'roles_rooms.room_id',
                'roles_rooms.role_key',
                'room_role_permissions.*',
                'block_role_permissions.value as block_role_permission_value'
            )
            ->join('room_role_permissions', function ($join) {
                $join->on('room_role_permissions.roles_room_id', '=', 'roles_rooms.id');
            })
            ->join('block_role_permissions', function ($join) {
                $join->on('block_role_permissions.roles_room_id', '=', 'roles_rooms.id');
            })
            ->whereIn('roles_rooms.room_id', $room_ids)
            ->get();
    }
}
