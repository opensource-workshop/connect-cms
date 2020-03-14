<?php

namespace App\Models\User\Databases;

use Illuminate\Database\Eloquent\Model;

class DatabasesFrames extends Model
{
    // 更新する項目の定義
    protected $fillable = ['databases_id', 'frames_id', 'use_search_flag', 'use_select_flag', 'use_sort_flag', 'view_count', 'default_hide', 'created_at', 'updated_at'];

//    /**
//     *  ファイル系の詳細データの取得
//     */
//    public function getViewCountAttribute() {
//        return "AAA";
//    }
}
