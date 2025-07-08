<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nc3PhotoAlbumDisplayAlbum extends Model
{
    use HasFactory;

    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'photo_album_display_albums';

    /**
     * タイムスタンプ管理
     */
    public $timestamps = false;

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'frame_key',
        'photoalbum_key',
        'display_type',
        'display_sequence',
        'display_number',
        'plugin_key',
        'block_id',
        'language_id',
        'is_origin',
        'is_translation',
        'is_original_copy',
        'status',
        'is_active',
        'is_latest',
        'created_user',
        'created',
        'modified_user',
        'modified',
    ];
}
