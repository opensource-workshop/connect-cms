<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 通知の埋め込みタグ
 */
final class NoticeEmbeddedTag extends EnumsBase
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
        self::title => '記事のタイトル',
        self::body => '本文',
        self::url => '削除前のURL',
        self::delete_comment => '削除時のコメント',
        self::created_name => '登録者',
        self::created_at => '登録日時',
        self::updated_name => '更新者',
        self::updated_at => '更新日時',
    ];
}
