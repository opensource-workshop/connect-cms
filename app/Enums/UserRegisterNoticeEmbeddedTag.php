<?php

namespace App\Enums;

use App\Enums\NoticeEmbeddedTag;

/**
 * ユーザ本登録の通知の埋め込みタグ
 */
final class UserRegisterNoticeEmbeddedTag extends NoticeEmbeddedTag
{
    // 定数メンバ
    const to_datetime = 'to_datetime';

    // key/valueの連想配列
    const enum = [
        self::site_name => 'サイト名',
        self::body => '本文',
        self::to_datetime => '登録日時',
    ];

    /**
     * 埋め込みタグの説明を取得
     */
    public static function getDescriptionEmbeddedTags(bool $use_title = false, bool $use_body = false): array
    {
        // 埋め込みタグ, 内容
        $embedded_tags[] = ['[[' . self::site_name . ']]', self::getDescription(self::site_name)];
        $embedded_tags[] = ['[[' . self::body . ']]', self::getDescription(self::body)];
        $embedded_tags[] = ['[[' . self::to_datetime . ']]', self::getDescription(self::to_datetime)];
        return $embedded_tags;
    }
}
