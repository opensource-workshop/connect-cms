<?php

namespace App\Enums;

/**
 * フォーム項目区分
 */
final class FormColumnType extends EnumsBase
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
    const time_from_to = 'time_from_to';
    const file = 'file';
    const group = 'group';

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
        self::time_from_to=>'時間型(FromTo)',
        self::file=>'ファイル型',
        self::group=>'まとめ行',
    ];
}
