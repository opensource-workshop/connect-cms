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
    const time = 'time';
    const link = 'link';
    const file = 'file';
    const image = 'image';
    const video = 'video';
    const wysiwyg = 'wysiwyg';
    // delete:「行グループ」「列グループ」追加に伴い、機能してない 項目の型「まとめ行」を廃止
    // const group = 'group';

    // move: ソート用項目のため、App\Enums\DatabaseSortFlag.phpに移動
    // const created_asc    = 'created_asc';
    // const created_desc   = 'created_desc';
    // const updated_asc    = 'updated_asc';
    // const updated_desc   = 'updated_desc';
    // const random_session = 'random_session';
    // const random_every   = 'random_every';

    // key/valueの連想配列
    const enum = [
        self::text=>'1行文字列型',
        self::textarea=>'複数行文字列型',
        self::radio=>'単一選択型',
        self::checkbox=>'複数選択型',
        self::select=>'リストボックス型',
        self::mail=>'メールアドレス型',
        // self::birthday=>'生年月日型',
        // self::datetime=>'日付＆時間型',
        self::date=>'日付型',
        self::time=>'時間型',
        self::link=>'リンク型',
        self::file=>'ファイル型',
        self::image=>'画像型',
        self::video=>'動画型',
        self::wysiwyg=>'ウィジウィグ',
        // delete:「行グループ」「列グループ」追加に伴い、機能してない 項目の型「まとめ行」を廃止
        // self::group=>'まとめ行',

        // move: ソート用項目のため、App\Enums\DatabaseSortFlag.phpに移動
        // self::created_asc    => '登録日（古い順）',
        // self::created_desc   => '登録日（新しい順）',
        // self::updated_asc    => '更新日（古い順）',
        // self::updated_desc   => '更新日（新しい順）',
        // self::random_session => 'ランダム（セッション）',
        // self::random_every   => 'ランダム（毎回）',
    ];
}
