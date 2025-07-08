<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nc3PhotoAlbum extends Model
{
    use HasFactory;

    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'photo_albums';

    /**
     * タイムスタンプ管理
     */
    public $timestamps = false;
}
