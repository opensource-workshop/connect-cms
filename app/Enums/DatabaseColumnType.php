<?php

namespace App\Enums;

/**
 * フォーム項目区分
 */
final class DatabaseColumnType
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
    const file = 'file';
    const image = 'image';
    const video = 'video';
    const wysiwyg = 'wysiwyg';
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
        self::file=>'ファイル型',
        self::image=>'画像型',
        self::video=>'動画型',
        self::wysiwyg=>'ウィジウィグ',
        self::group=>'まとめ行',
    ];

    /*
    * 対応した和名を返す
    */
    public static function getDescription($key): string
    {
        return self::enum[$key];
    }

    /*
    * key/valueの連想配列を返す
    */
    public static function getMembers(){
        return self::enum;
    }
}
