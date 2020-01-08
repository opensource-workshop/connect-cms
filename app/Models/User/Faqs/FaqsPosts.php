<?php

namespace App\Models\User\Faqs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

class FaqsPosts extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // 日付型の場合、$dates にカラムを指定しておく。
    protected $dates = ['posted_at'];
}
