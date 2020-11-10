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
    
    /* New用に追加 */
    protected $fillable = ['linklist_id','target_blank_flag'];
}
