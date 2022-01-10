<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Dusks extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['category', 'sort', 'method', 'test_result', 'html_path', 'function_title', 'method_desc', 'function_desc'];
}
