<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Configs extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['name', 'value', 'category'];

}
