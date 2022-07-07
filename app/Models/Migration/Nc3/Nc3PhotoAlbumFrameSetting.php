<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3PhotoAlbumFrameSetting extends Model
{
    // アルバム一覧表示
    const DISPLAY_ALBUM_LIST = 1;
    // 写真一覧表示
    const DISPLAY_PHOTO_LIST = 2;
    // アルバムのスライド表示
    const DISPLAY_SLIDESHOW = 3;

    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'photo_album_frame_settings';
}
