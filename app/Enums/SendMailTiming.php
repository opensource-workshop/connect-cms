<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 送信方法
 */
final class SendMailTiming extends EnumsBase
{
    // 定数メンバ
    // $timing = 1:スケジュール送信（非同期送信、デフォルト), 0:即時送信（同期送信）
    const sync = 0;
    const async = 1;

    // key/valueの連想配列
    const enum = [
        self::sync => '即時送信',
        self::async => 'スケジュール送信',
    ];
}
