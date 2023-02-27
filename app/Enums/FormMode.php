<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * フォームモード
 */
final class FormMode extends EnumsBase
{
    // 定数メンバ
    const form = 'form';
    const questionnaire = 'questionnaire';

    // key/valueの連想配列
    const enum = [
        self::form => 'フォームとして使用する',
        self::questionnaire => 'アンケートとして使用する',
    ];

    /**
     * 初期値
     */
    public static function getDefault()
    {
        return self::form;
    }
}
