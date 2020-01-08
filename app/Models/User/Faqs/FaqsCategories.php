<?php

namespace App\Models\User\Faqs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Userable;

class FaqsCategories extends Model
{
    // 論理削除
    use SoftDeletes;

    // 保存時のユーザー関連データの保持
    use Userable;

    // 更新する項目の定義
    protected $fillable = ['faqs_id', 'categories_id', 'view_flag', 'display_sequence'];
}
