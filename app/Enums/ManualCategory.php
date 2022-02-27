<?php

namespace App\Enums;

/**
 * マニュアル・カテゴリ
 */
final class ManualCategory extends EnumsBase
{
    // 定数メンバ
    const blueprint = 'blueprint';
    const common = 'common';
    const manage = 'manage';
    const manage_data = 'manage_data';
    const user = 'user';
    const mypage = 'mypage';
    const error = 'error';
    const usage = 'usage';

    // key/valueの連想配列
    const enum = [
        self::blueprint => '設計',
        self::common => '共通機能',
        self::manage => '管理者',
        self::manage_data => '管理者(データ管理)',
        self::user => '一般ユーザ',
        self::mypage => 'マイページ',
        self::error => 'エラー説明',
        self::usage => '逆引き',
    ];
}
