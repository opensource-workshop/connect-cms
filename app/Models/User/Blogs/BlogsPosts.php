<?php

namespace App\Models\User\Blogs;

use Illuminate\Database\Eloquent\Model;
use App\Userable;

class BlogsPosts extends Model
{

    // 保存時のユーザー関連データの保持
    use Userable;

    // 日付型の場合、$dates にカラムを指定しておく。
    protected $dates = ['posted_at'];
}
