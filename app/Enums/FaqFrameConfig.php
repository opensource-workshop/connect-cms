<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * FAQのフレーム設定項目
 */
final class FaqFrameConfig extends EnumsBase
{
    // 定数メンバ
    const faq_display_created_name = 'faq_display_created_name';
    const faq_narrowing_down_type = 'faq_narrowing_down_type';

    // key/valueの連想配列
    const enum = [
        self::faq_display_created_name => '投稿者名',
        self::faq_narrowing_down_type => '絞り込み機能',
    ];
}
