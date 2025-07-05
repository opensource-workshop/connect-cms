<?php

namespace App\Models\Migration\Nc3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nc3RegistrationQuestion extends Model
{
    use HasFactory;
    
    /**
     * タイムスタンプの自動更新を無効にする
     */
    public $timestamps = false;
    /**
     * 使用するDB Connection
     */
    protected $connection = 'nc3';

    /**
     * テーブル名の指定
     */
    protected $table = 'registration_questions';

    /** 権限 */
    const
        question_type_radio = 1,
        question_type_checkbox = 2,
        question_type_text = 3,
        question_type_textarea = 4,
        question_type_date = 7,
        question_type_select = 8,
        question_type_mail = 9,
        question_type_file = 10;

    /**
     * 選択肢項目か
     */
    public static function isOptionItem($question_type): bool
    {
        $option_items = [
            self::question_type_radio,
            self::question_type_checkbox,
            self::question_type_select,
        ];
        return in_array($question_type, $option_items);
    }
}
