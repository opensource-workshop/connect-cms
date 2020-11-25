<?php

namespace App\Models\User\Blogs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

class BlogsPosts extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // 定数メンバ
    const read_more_button_default = '続きを読む';
    const close_more_button_default = '閉じる';

    // 日付型の場合、$dates にカラムを指定しておく。
    protected $dates = ['posted_at'];

    // 更新する項目の定義
    protected $fillable = ['contents_id', 'blogs_id', 'post_title', 'post_text', 'post_text2', 'read_more_flag', 'read_more_button', 'close_more_button', 'categories_id', 'important', 'status', 'posted_at'];
}
