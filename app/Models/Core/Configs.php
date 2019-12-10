<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Configs extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['name', 'value', 'category', 'additional1'];

    public function getNobrValue()
    {
        return str_replace("\r\n", "", $this->value);
    }
}
