<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class BucketsRoles extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'buckets_id',
        'role',
        'post_flag',
        'approval_flag',
    ];
}
