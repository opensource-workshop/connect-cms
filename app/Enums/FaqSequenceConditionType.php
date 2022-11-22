<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * FAQの順序条件
 * @see resources\views\plugins\user\faqs\default\faqs_edit_faq.blade.php
 */
final class FaqSequenceConditionType extends EnumsBase
{
    // 定数メンバ
    const latest_order = 0;
    const post_order = 1;
    const display_sequence_order = 2;
    const category_order = 3;

    // key/valueの連想配列
    const enum = [
        self::latest_order => '最新順',
        self::post_order => '投稿順',
        self::display_sequence_order => '指定順',
        self::category_order => 'カテゴリ順',
    ];
}
