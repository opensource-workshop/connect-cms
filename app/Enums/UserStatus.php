<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ユーザ状態
 */
final class UserStatus extends EnumsBase
{
    // 定数メンバ
    const active = 0;
    const not_active = 1;
    const temporary_delete = 3;
    const temporary = 2;
    const pending_approval = 4;

    // key/valueの連想配列
    const enum = [
        self::active => '利用可能',
        self::not_active => '利用不可',
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
        // 仮登録は外す
        unset($enum[self::temporary]);
        // dd($enum);

        return array_keys($enum);
    }
}
