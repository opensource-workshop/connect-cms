<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * フォトアルバムのフレーム設定項目
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
 * @package Controller
 */
final class PhotoalbumFrameConfig extends EnumsBase
{
    // 定数メンバ
    const sort = 'sort';

    // key/valueの連想配列
    const enum = [
        self::sort => '並び順',
    ];
}
