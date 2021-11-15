<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * フォトアルバム並べ替え項目
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォトアルバム・プラグイン
 * @package Controller
 */

final class PhotoalbumSort extends EnumsBase
{
    // 定数メンバの'_'の前半
    // 注意：_ は区切り文字に使っているため、const内に _ を含めない事
    const name = 'name';
    const created = 'created';
    const updated = 'updated';

    // 定数メンバの'_'の後半
    const order_asc     = 'asc';
    const order_desc    = 'desc';

    // 定数メンバ
    // PHP 5.6.0 以降はconstで文字連結可能 https://www.php.net/manual/ja/language.oop5.constants.php
    const name_asc     = self::name . '_' . self::order_asc;
    const name_desc    = self::name . '_' . self::order_desc;
    const created_asc    = self::created . '_' . self::order_asc;
    const created_desc   = self::created . '_' . self::order_desc;
    // const updated_asc    = self::updated . '_' . self::order_asc;
    // const updated_desc   = self::updated . '_' . self::order_desc;

    // key/valueの連想配列
    const enum = [
        self::name_asc    => '名前順（昇順）',
        self::name_desc   => '名前順（降順）',
        self::created_asc    => '登録日（古い順）',
        self::created_desc   => '登録日（新しい順）',
        // self::updated_asc    => '更新日（古い順）',
        // self::updated_desc   => '更新日（新しい順）',
    ];
}
