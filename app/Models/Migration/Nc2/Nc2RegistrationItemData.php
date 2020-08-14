<?php

namespace App\Models\Migration\Nc2;

use Illuminate\Database\Eloquent\Model;

class Nc2RegistrationItemData extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc2';

    /**
     * テーブル名の指定
     */
    protected $table = 'registration_item_data';
}
