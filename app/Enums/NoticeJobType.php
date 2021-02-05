<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 通知Job関係
 */
final class NoticeJobType extends EnumsBase
{
    // 定数メンバ
    const notice_create = 'notice_create';
    const notice_update = 'notice_update';
    const notice_delete = 'notice_delete';

    // key/valueの連想配列
    const enum = [
        self::notice_create => '登録',
        self::notice_update => '変更',
        self::notice_delete => '削除',
    ];
}
