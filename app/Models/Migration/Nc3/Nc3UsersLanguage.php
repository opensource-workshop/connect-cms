<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Model;

/**
 * ユーザの多言語項目（氏名・プロフィール等）
 */
class Nc3UsersLanguage extends Model
{
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'users_languages';
}
