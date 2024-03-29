<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3MultidatabaseFrameSetting extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'multidatabase_frame_settings';
}
