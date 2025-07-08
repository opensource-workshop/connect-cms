<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Nc3Video extends Model
{
    use HasFactory;
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'videos';

    /**
     * タイムスタンプ管理
     */
    public $timestamps = false;

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'key',
        'block_id',
        'title',
        'description',
        'is_latest',
        'language_id',
        'category_id',
        'is_active',
        'created',
        'modified',
        'created_user',
        'modified_user',
    ];
}
