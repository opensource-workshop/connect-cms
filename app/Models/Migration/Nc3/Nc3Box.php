<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3Box extends Model
{
    /**
     * フレームの配置場所
     * 1:Header, 2:Major(Left), 3:Main, 4:Minor(Right), 5:Footer
     */
    const
        container_type_header = 1,
        container_type_left = 2,
        container_type_main = 3,
        container_type_right = 4,
        container_type_footer = 5;

    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'boxes';
}
