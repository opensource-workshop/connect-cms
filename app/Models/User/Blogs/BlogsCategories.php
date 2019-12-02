<?php

namespace App\Models\User\Blogs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

class BlogsCategories extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // 更新する項目の定義
    protected $fillable = ['blogs_id', 'categories_id', 'view_flag', 'display_sequence'];
}
