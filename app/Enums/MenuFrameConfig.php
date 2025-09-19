<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * メニューのフレーム設定項目
 */
final class MenuFrameConfig extends EnumsBase
{
    // 定数メンバ
    const menu_allow_moderator_edit = 'menu_allow_moderator_edit';

    // key/valueの連想配列
    const enum = [
        self::menu_allow_moderator_edit => 'モデレータ編集許可',
    ];
}
