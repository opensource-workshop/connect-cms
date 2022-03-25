<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 通知の埋め込みタグ
 */
class NoticeEmbeddedTag extends EnumsBase
{
    // 定数メンバ
    const site_name = 'site_name';
    const method = 'method';
    const title = 'title';
    const body = 'body';
    const url = 'url';
    const delete_comment = 'delete_comment';
    const created_name = 'created_name';
    const created_at = 'created_at';
    const updated_name = 'updated_name';
    const updated_at = 'updated_at';

    // key/valueの連想配列
    const enum = [
        self::site_name => 'サイト名',
        self::method => '処理名',
        self::title => 'タイトル',
        self::body => 'HTMLを除去した本文',
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
        if ($use_title) {
            $embedded_tags[] = ['[[' . self::title . ']]', self::getDescription(self::title)];
        }
        if ($use_body) {
            $embedded_tags[] = ['[[' . self::body . ']]', self::getDescription(self::body)];
        }
        $embedded_tags[] = ['[[' . self::url . ']]', self::getDescription(self::url)];
        $embedded_tags[] = ['[[' . self::delete_comment . ']]', self::getDescription(self::delete_comment)];
        $embedded_tags[] = ['[[' . self::created_name . ']]', self::getDescription(self::created_name)];
        $embedded_tags[] = ['[[' . self::created_at . ']]', self::getDescription(self::created_at)];
        $embedded_tags[] = ['[[' . self::updated_name . ']]', self::getDescription(self::updated_name)];
        $embedded_tags[] = ['[[' . self::updated_at . ']]', self::getDescription(self::updated_at)];
        return $embedded_tags;
    }
}
