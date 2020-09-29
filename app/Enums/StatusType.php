<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ステータス関係
 */
final class StatusType extends EnumsBase
{
    // 定数メンバ
    // 参考) https://github.com/opensource-workshop/connect-cms/wiki/Data-history-policy（データ履歴の方針）
    const active = 0;
    const temporary = 1;
    const approval_pending = 2;
    const history = 9;

    // key/valueの連想配列
    const enum = [
        self::active => '公開',
        self::temporary => '一時保存',
        self::approval_pending => '承認待ち',
        self::history => '履歴・データ削除',
    ];
}
