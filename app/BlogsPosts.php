<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlogsPosts extends Model
{
    // 日付型の場合、$dates にカラムを指定しておく。
    protected $dates = ['posted_at'];
}
