<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class Uploads extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['client_original_name', 'mimetype', 'extension', 'size', 'plugin_name', 'temporary_flag', 'created_id'];
}
