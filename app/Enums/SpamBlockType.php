<?php

namespace App\Enums;

/**
 * スパムブロック種別
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スパム管理
 * @package Enum
 */
class SpamBlockType extends EnumsBase
{
    // 定数メンバ
    const email = 'email';
    const domain = 'domain';
    const ip_address = 'ip_address';
    const honeypot = 'honeypot';

    // key/valueの連想配列
    const enum = [
        self::email => 'メールアドレス',
        self::domain => 'ドメイン',
        self::ip_address => 'IPアドレス',
        self::honeypot => 'ハニーポット',
    ];
}
