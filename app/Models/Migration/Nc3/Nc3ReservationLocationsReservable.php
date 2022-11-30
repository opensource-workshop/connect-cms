<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Nc3ReservationLocationsReservable extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'reservation_location_reservable';

    /**
     * block_role_permissionsのvalueをblock_key,permission,role_keyで取得
     */
    public static function getReservableValue(Collection $reservation_location_reservables, string $location_key, string $role_key, ?string $default = '0'): string
    {
        $reservable = $reservation_location_reservables->where('location_key', $location_key)->firstWhere('role_key', $role_key) ?? new Nc3ReservationLocationsReservable();
        return $reservable->value ?? $default;
    }
}
