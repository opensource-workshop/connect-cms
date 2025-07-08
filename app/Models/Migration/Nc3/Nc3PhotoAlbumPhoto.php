<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nc3PhotoAlbumPhoto extends Model
{
    use HasFactory;

    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'photo_album_photos';

    /**
     * タイムスタンプ管理
     */
    public $timestamps = false;

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'album_key',
        'key',
        'title',
        'description',
        'language_id',
        'status',
        'block_id',
        'is_latest',
        'is_active',
        'created_user',
        'created',
        'modified_user',
        'modified',
        'upload_id',
    ];
}
