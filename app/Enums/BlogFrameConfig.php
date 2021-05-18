<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ブログのフレーム設定項目
 */
final class BlogFrameConfig extends EnumsBase
{
    // 定数メンバ
    const blog_display_created_name = 'blog_display_created_name';

    // key/valueの連想配列
    const enum = [
        self::blog_display_created_name => '投稿者名',
    ];
}
