<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ユーザ項目区分
 */
final class UserColumnType extends EnumsBase
{
    // 定数メンバ
    const text = 'text';
    const textarea = 'textarea';
    const radio = 'radio';
    const checkbox = 'checkbox';
    const select = 'select';
    const mail = 'mail';

    // key/valueの連想配列
    const enum = [
        self::text => '1行文字列型',
        self::textarea => '複数行文字列型',
        self::radio => '単一選択型',
        self::checkbox => '複数選択型',
        self::select => 'リストボックス型',
        self::mail => 'メールアドレス型',
    ];
}
