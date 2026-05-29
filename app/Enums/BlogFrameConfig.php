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
    const blog_display_posted_time = 'blog_display_posted_time';
    const blog_posted_at_format = 'blog_posted_at_format';
    const blog_display_twitter_button = 'blog_display_twitter_button';
    const blog_display_facebook_button = 'blog_display_facebook_button';
    const blog_view_count = 'blog_view_count';

    // key/valueの連想配列
    const enum = [
        self::blog_display_created_name => '投稿者名',
        self::blog_display_posted_time => '投稿日時の時刻表示',
        self::blog_posted_at_format => '投稿日時表示形式',
        self::blog_display_twitter_button => 'Twitterアイコン表示',
        self::blog_display_facebook_button => 'Facebookアイコン表示',
        self::blog_view_count => '表示件数',
    ];
}
