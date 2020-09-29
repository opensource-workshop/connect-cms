<?php

namespace App\Models\Migration\Nc2;

use Illuminate\Database\Eloquent\Model;

class Nc2LinklistBlock extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc2';

    /**
     * テーブル名の指定
     */
    protected $table = 'linklist_block';
}
