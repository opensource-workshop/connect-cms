<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3ReservationLocationsApprovalUser extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'reservation_locations_approval_users';
}
