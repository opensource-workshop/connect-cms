<?php

namespace App\Models\Migration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MigrationMapping extends Model
{
    use HasFactory;

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['target_source_table', 'source_key', 'destination_key', 'note'];
}
