<?php

namespace App\Models\User\Blogs;

use Illuminate\Database\Eloquent\Model;

class BlogsFrames extends Model
{
    // 更新する項目の定義
    protected $fillable = ['blogs_id', 'frames_id', 'scope', 'scope_value', 'important_view'];
}
