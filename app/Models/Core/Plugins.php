<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Plugins extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'plugin_name', 'plugin_name_full', 'display_flag'
    ];
}
