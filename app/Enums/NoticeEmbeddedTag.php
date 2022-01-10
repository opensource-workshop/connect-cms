<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 通知の埋め込みタグ
 */
final class NoticeEmbeddedTag extends EnumsBase
{
    // 定数メンバ
    const method = 'method';
    const title = 'title';
    const body = 'body';
    const url = 'url';
    const delete_comment = 'delete_comment';

    // key/valueの連想配列
    const enum = [
        self::method => '処理名',
        self::title => '記事のタイトル',
        self::body => '本文',
        self::url => '削除前のURL',
        self::delete_comment => '削除時のコメント',
    ];
}
