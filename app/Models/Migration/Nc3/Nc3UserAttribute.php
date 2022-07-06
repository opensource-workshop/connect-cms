<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

/**
 * ユーザ項目
 */
class Nc3UserAttribute extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'user_attributes';
}
