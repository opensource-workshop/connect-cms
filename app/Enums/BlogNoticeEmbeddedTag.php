<?php

namespace App\Enums;

use App\Enums\NoticeEmbeddedTag;

/**
 * ブログの通知の埋め込みタグ
 */
final class BlogNoticeEmbeddedTag extends NoticeEmbeddedTag
{
    // 定数メンバ
    const posted_at = 'posted_at';
    const important = 'important';
    const body2 = 'body2';
    const category = 'category';
    const tag = 'tag';

    // key/valueの連想配列
    const enum = [
        self::site_name => 'サイト名',
        self::method => '処理名',
        self::title => 'タイトル',
        self::posted_at => '投稿日時',
        self::important => '重要記事なら"重要記事"と表示',
        self::body => 'HTMLを除去した本文',
        self::body2 => 'HTMLを除去した続き本文',
        self::category => 'カテゴリ',
        self::tag => 'タグ',
        self::url => 'URL',
        self::delete_comment => '削除時のコメント',
        self::created_name => '登録者',
        self::created_at => '登録日時',
        self::updated_name => '更新者',
        self::updated_at => '更新日時',
    ];

    /**
     * 埋め込みタグの説明を取得
     */
    public static function getDescriptionEmbeddedTags(bool $use_title = false, bool $use_body = false): array
    {
        // 埋め込みタグ, 内容
        $embedded_tags[] = ['[[' . self::site_name . ']]', self::getDescription(self::site_name)];
        $embedded_tags[] = ['[[' . self::method . ']]', self::getDescription(self::method)];
        $embedded_tags[] = ['[[' . self::title . ']]', self::getDescription(self::title)];
        $embedded_tags[] = ['[[' . self::body . ']]', self::getDescription(self::body)];
        $embedded_tags[] = ['[[' . self::body2 . ']]', self::getDescription(self::body2)];
        $embedded_tags[] = ['[[' . self::important . ']]', self::getDescription(self::important)];
        $embedded_tags[] = ['[[' . self::category . ']]', self::getDescription(self::category)];
        $embedded_tags[] = ['[[' . self::tag . ']]', self::getDescription(self::tag)];
        $embedded_tags[] = ['[[' . self::url . ']]', self::getDescription(self::url)];
        $embedded_tags[] = ['[[' . self::delete_comment . ']]', self::getDescription(self::delete_comment)];
        $embedded_tags[] = ['[[' . self::posted_at . ']]', self::getDescription(self::posted_at)];
        $embedded_tags[] = ['[[' . self::created_name . ']]', self::getDescription(self::created_name)];
        $embedded_tags[] = ['[[' . self::created_at . ']]', self::getDescription(self::created_at)];
        $embedded_tags[] = ['[[' . self::updated_name . ']]', self::getDescription(self::updated_name)];
        $embedded_tags[] = ['[[' . self::updated_at . ']]', self::getDescription(self::updated_at)];
        return $embedded_tags;
    }
}
