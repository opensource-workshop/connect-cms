<?php

namespace App\Models\User\Photoalbums;

use Illuminate\Database\Eloquent\Model;

use App\UserableNohistory;

class Photoalbum extends Model
{
    // 保存時のユーザー関連データの保持
    use UserableNohistory;

    // 更新する項目の定義
    protected $fillable = ['bucket_id', 'name', 'image_upload_max_size','image_upload_max_px', 'video_upload_max_size'];
}
