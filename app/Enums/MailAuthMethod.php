<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * メール認証方式
 */
final class MailAuthMethod extends EnumsBase
{
    // 定数メンバ
    const smtp = 'smtp';
    const oauth2_microsoft365_app = 'oauth2_microsoft365_app';

    // key/valueの連想配列
    const enum = [
        self::smtp => 'SMTP認証',
        self::oauth2_microsoft365_app => 'Microsoft 365 OAuth2（アプリケーション許可）',
    ];
}
