<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 通知Job関係
 */
final class NoticeJobType extends EnumsBase
{
    // 定数メンバ
    const notice_create   = 'notice_create';
    const notice_relate   = 'notice_relate';
    const notice_update   = 'notice_update';
    const notice_delete   = 'notice_delete';
    const notice_approval = 'notice_approval';
    const notice_approved = 'notice_approved';

    // key/valueの連想配列
    const enum = [
        self::notice_create   => '登録',
        self::notice_relate   => '関連記事通知',
        self::notice_update   => '変更',
        self::notice_delete   => '削除',
        self::notice_approval => '承認待ち',
        self::notice_approved => '承認済み',
    ];
}
