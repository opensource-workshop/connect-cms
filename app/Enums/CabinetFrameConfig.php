<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * キャビネットのフレーム設定項目
 */
final class CabinetFrameConfig extends EnumsBase
{
    // 定数メンバ
    const sort = 'sort';
    const show_download_count = 'show_download_count';
    const show_created_name = 'show_created_name';
    const show_updated_name = 'show_updated_name';

    // key/valueの連想配列
    const enum = [
        self::sort => '並び順',
        self::show_download_count => 'ダウンロード件数',
        self::show_created_name => '作成者',
        self::show_updated_name => '更新者',
    ];
}
