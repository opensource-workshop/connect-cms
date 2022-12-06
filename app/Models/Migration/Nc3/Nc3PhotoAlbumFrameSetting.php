<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3PhotoAlbumFrameSetting extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'photo_album_frame_settings';

    // アルバム一覧表示
    const DISPLAY_ALBUM_LIST = 1;
    // 写真一覧表示
    const DISPLAY_PHOTO_LIST = 2;
    // アルバムのスライド表示
    const DISPLAY_SLIDESHOW = 3;

    // アルバムの表示順
    const albums_order_new  = 'PhotoAlbum.modified desc',       // 新着順
        albums_order_create = 'PhotoAlbum.created asc',         // 登録順
        albums_order_title  = 'PhotoAlbum.name asc';            // タイトル順

    // 写真の表示順
    const photos_order_new  = 'PhotoAlbumPhoto.modified desc',  // 新着順
        photos_order_create = 'PhotoAlbumPhoto.created asc';    // 登録順
}
