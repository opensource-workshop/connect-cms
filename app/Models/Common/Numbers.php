<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class Numbers extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['plugin_name', 'buckets_id', 'serial_number', 'prefix'];
}
