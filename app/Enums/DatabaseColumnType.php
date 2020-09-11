<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * データベース項目区分
 */
final class DatabaseColumnType extends EnumsBase
{
    // 定数メンバ
    const text = 'text';
    const textarea = 'textarea';
    const radio = 'radio';
    const checkbox = 'checkbox';
    const select = 'select';
    const mail = 'mail';
    // const birthday = 'birthday';
    // const datetime = 'datetime';
    const date = 'date';
    const dates_ym = 'dates_ym';
    const time = 'time';
    const link = 'link';
    const file = 'file';
    const image = 'image';
    const video = 'video';
    const wysiwyg = 'wysiwyg';
    // delete:「行グループ」「列グループ」追加に伴い、機能してない 項目の型「まとめ行」を廃止
    // const group = 'group';
    const created = 'created';
    const updated = 'updated';
    const posted = 'posted';
    const display = 'display';

    // 複数年月型（テキスト入力）のデフォルトキャプション
    const dates_ym_caption = 'yyyy/mm形式で年月を入力してください。また複数入力したい場合は、カンマ「,」区切りで入力してください。';

    // key/valueの連想配列
    const enum = [
        self::text => '1行文字列型',
        self::textarea => '複数行文字列型',
        self::radio => '単一選択型',
        self::checkbox => '複数選択型',
        self::select => 'リストボックス型',
        self::mail => 'メールアドレス型',
        // self::birthday=>'生年月日型',
        // self::datetime=>'日付＆時間型',
        self::date => '日付型',
        self::dates_ym => '複数年月型（テキスト入力）',
        self::time => '時間型',
        self::link => 'リンク型',
        self::file => 'ファイル型',
        self::image => '画像型',
        self::video => '動画型',
        self::wysiwyg => 'ウィジウィグ型',
        // delete:「行グループ」「列グループ」追加に伴い、機能してない 項目の型「まとめ行」を廃止
        // self::group=>'まとめ行',
        self::created => '登録日型（自動更新）',
        self::updated => '更新日型（自動更新）',
        self::posted => '公開日型（表示のみ）',
        self::display => '表示順型（表示のみ）',
    ];
}
