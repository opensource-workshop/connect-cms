<?php

namespace App\Models\User\Blogs;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class Blogs extends Model
{
    // 保存時のユーザー関連データの保持（履歴なしUserable）
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = ['bucket_id', 'blog_name', 'view_count'];
}
