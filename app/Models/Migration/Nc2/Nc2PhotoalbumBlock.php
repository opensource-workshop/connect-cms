<?php

namespace App\Models\Migration\Nc2;

use Illuminate\Database\Eloquent\Model;

class Nc2PhotoalbumBlock extends Model
{
    // アルバム一覧表示
    const DISPLAY_ALBUM_LIST = 0;
    // アルバムのスライド表示
    const DISPLAY_SLIDESHOW = 1;

    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc2';

    /**
     * テーブル名の指定
     */
    protected $table = 'photoalbum_block';
}
