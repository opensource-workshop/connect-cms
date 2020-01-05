<?php

namespace App\Models\User\Opacs;

use Illuminate\Database\Eloquent\Model;

class OpacsFrames extends Model
{
    // 更新する項目の定義
    protected $fillable = ['opacs_id', 'frames_id', 'view_form'];
}
