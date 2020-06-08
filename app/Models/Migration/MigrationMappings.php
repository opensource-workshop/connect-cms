<?php

namespace App\Models\Migration;

use Illuminate\Database\Eloquent\Model;

class MigrationMappings extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['target_source_table', 'source_id', 'destination_id', 'note'];
}
