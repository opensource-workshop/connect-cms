<?php

namespace App\Enums;

use App\Enums\NoticeEmbeddedTag;

/**
 * データベースの通知の埋め込みタグ
 */
final class DatabaseNoticeEmbeddedTag extends NoticeEmbeddedTag
{
    // 定数メンバ
    const posted_at = 'posted_at';
    const expires_at = 'expires_at';
    const display_sequence = 'display_sequence';

    // key/valueの連想配列
    const enum = [
        self::site_name => 'サイト名',
        self::method => '処理名',
        self::title => 'タイトル',
        self::body => '本文',
        self::posted_at => '公開日時',
        self::expires_at => '公開終了日時',
        self::display_sequence => '表示順',
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
        $embedded_tags[] = ['[[' . self::site_name . ']]',        self::getDescription(self::site_name)];
        $embedded_tags[] = ['[[' . self::method . ']]',           self::getDescription(self::method)];
        $embedded_tags[] = ['[[' . self::title . ']]',            self::getDescription(self::title)];
        $embedded_tags[] = ['[[' . self::posted_at . ']]',        self::getDescription(self::posted_at)];
        $embedded_tags[] = ['[[' . self::expires_at . ']]',       self::getDescription(self::expires_at)];
        $embedded_tags[] = ['[[' . self::display_sequence . ']]', self::getDescription(self::display_sequence)];
        $embedded_tags[] = ['[[' . self::url . ']]',              self::getDescription(self::url)];
        $embedded_tags[] = ['[[' . self::delete_comment . ']]',   self::getDescription(self::delete_comment)];
        $embedded_tags[] = ['[[' . self::created_name . ']]',     self::getDescription(self::created_name)];
        $embedded_tags[] = ['[[' . self::created_at . ']]',       self::getDescription(self::created_at)];
        $embedded_tags[] = ['[[' . self::updated_name . ']]',     self::getDescription(self::updated_name)];
        $embedded_tags[] = ['[[' . self::updated_at . ']]',       self::getDescription(self::updated_at)];
        return $embedded_tags;
    }
}
