<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * スマホメニューの表示形式
 */
final class SmartphoneMenuTemplateType extends EnumsBase
{
    // 定数メンバ
    const none = '';
    const opencurrenttree = 'opencurrenttree';

    // key/valueの連想配列
    const enum = [
        self::none => 'ページをすべて表示する',
        self::opencurrenttree => '今いるページ配下を表示する',
    ];
}
