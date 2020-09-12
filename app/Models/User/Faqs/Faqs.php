<?php

namespace App\Models\User\Faqs;

use Illuminate\Database\Eloquent\Model;

class Faqs extends Model
{
    // 更新する項目の定義
    protected $fillable = ['bucket_id', 'faq_name', 'view_count'];
}
