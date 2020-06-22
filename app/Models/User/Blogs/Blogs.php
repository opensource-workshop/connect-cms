<?php

namespace App\Models\User\Blogs;

use Illuminate\Database\Eloquent\Model;

class Blogs extends Model
{
    // 更新する項目の定義
    protected $fillable = ['bucket_id', 'blog_name', 'view_count'];
}
