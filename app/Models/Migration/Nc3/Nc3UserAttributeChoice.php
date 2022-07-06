<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

/**
 * ユーザ項目-オプション項目
 */
class Nc3UserAttributeChoice extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'user_attribute_choices';
}
