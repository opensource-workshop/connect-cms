<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

class Nc3FaqQuestion extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'faq_questions';
}
