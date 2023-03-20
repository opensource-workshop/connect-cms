<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class UserSection extends Model
{
    // 更新する項目の定義
    protected $fillable = [
        'user_id',
        'section_id',
    ];

    /**
     * 組織
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
