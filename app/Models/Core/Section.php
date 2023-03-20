<?php

namespace App\Models\Core;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    // 更新する項目の定義
    protected $fillable = [
        'code',
        'name',
        'display_sequence',
    ];

    /**
     * 利用者所属
     */
    public function users() // phpcs:ignore
    {
        return $this->belongsToMany(User::class, 'user_sections', 'section_id', 'user_id');
    }
}
