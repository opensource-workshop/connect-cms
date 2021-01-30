<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class BucketsMail extends Model
{
    // firstOrNew で使うためにguarded が必要だった。
    // ない場合は「Illuminate\Database\Eloquent\MassAssignmentException: bucket_id」でエラーになった。
    protected $guarded = ['buckets_id'];
}
