<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ユーザ状態
 */
final class UserStatus extends EnumsBase
{
    // 定数メンバ
    /** 利用可能 */
    const active = 0;
    /** 利用不可 */
    const not_active = 1;
    /** 仮削除 */
    const temporary_delete = 3;
    /** 仮登録 */
    const temporary = 2;
    /** 承認待ち */
    const pending_approval = 4;

    // key/valueの連想配列
    const enum = [
        self::not_active => '利用不可',
        self::active => '利用可能',
        self::pending_approval  => '承認待ち',
        self::temporary_delete => '仮削除',
        self::temporary => '仮登録',
    ];

    /**
     * 選択可能なユーザ状態のkey配列を返す
     */
    public static function getChooseableKeys()
    {
        $enum = self::enum;
        // 仮登録, 承認待ちは外す
        unset($enum[self::temporary]);
        unset($enum[self::pending_approval]);

        return array_keys($enum);
    }
}
