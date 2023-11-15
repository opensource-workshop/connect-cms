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
    const blog_display_twitter_button = 'blog_display_twitter_button';
    const blog_display_facebook_button = 'blog_display_facebook_button';
    const blog_view_count = 'blog_view_count';

    // key/valueの連想配列
    const enum = [
        self::blog_display_created_name => '投稿者名',
        self::blog_display_twitter_button => 'Twitterアイコン表示',
        self::blog_display_facebook_button => 'Facebookアイコン表示',
        self::blog_view_count => '表示件数',
    ];
}
