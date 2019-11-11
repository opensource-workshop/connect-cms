<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['display_sequence', 'category', 'color', 'background_color'];

}
