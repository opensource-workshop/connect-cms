<?php

namespace App\Enums;

use App\Enums\NoticeEmbeddedTag;

/**
 * 施設予約の通知の埋め込みタグ
 */
final class ReservationNoticeEmbeddedTag extends NoticeEmbeddedTag
{
    // 定数メンバ
    const facility_name = 'facility_name';
    const booking_time = 'booking_time';
    const rrule = 'rrule';

    // key/valueの連想配列
    const enum = [
        self::site_name => 'サイト名',
        self::method => '処理名',
        self::title => 'タイトル',
        self::body => '本文',
        self::facility_name => '施設名',
        self::booking_time => '利用日時',
        self::rrule => '繰返し',
        self::url => 'URL',
        self::delete_comment => '削除時のコメント',
        self::created_name => '登録者',
        self::created_at => '登録日時',
        self::updated_name => '更新者',
        self::updated_at => '更新日時',
    ];

    /**
     * 埋め込みタグの説明を取得
     *
     * @see \App\Models\Common\BucketsMail 件名で使えないタグは BucketsMail::getFormattedSubject() 参照
     */
    public static function getDescriptionEmbeddedTags(bool $use_title = false, bool $use_body = false): array
    {
        // 埋め込みタグ, 内容
        $embedded_tags[] = ['[[' . self::site_name . ']]', self::getDescription(self::site_name)];
        $embedded_tags[] = ['[[' . self::method . ']]', self::getDescription(self::method)];
        $embedded_tags[] = ['[[' . self::title . ']]', self::getDescription(self::title)];
        $embedded_tags[] = ['[[' . self::facility_name . ']]', self::getDescription(self::facility_name)];
        $embedded_tags[] = ['[[' . self::booking_time . ']]', self::getDescription(self::booking_time)];
        $embedded_tags[] = ['[[' . self::rrule . ']]', self::getDescription(self::rrule)];
        $embedded_tags[] = ['[[' . self::url . ']]', self::getDescription(self::url)];
        $embedded_tags[] = ['[[' . self::delete_comment . ']]', self::getDescription(self::delete_comment)];
        $embedded_tags[] = ['[[' . self::created_name . ']]', self::getDescription(self::created_name)];
        $embedded_tags[] = ['[[' . self::created_at . ']]', self::getDescription(self::created_at)];
        $embedded_tags[] = ['[[' . self::updated_name . ']]', self::getDescription(self::updated_name)];
        $embedded_tags[] = ['[[' . self::updated_at . ']]', self::getDescription(self::updated_at)];
        return $embedded_tags;
    }
}
