<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3Room extends Model
{
    const
        WHOLE_SITE_ID = '1',
        PUBLIC_SPACE_ID = '2',
        PRIVATE_SPACE_ID = '3',
        COMMUNITY_SPACE_ID = '4';

    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'rooms';
}
