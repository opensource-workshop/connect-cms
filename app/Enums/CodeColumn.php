<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * コード管理のカラム
 */
final class CodeColumn extends EnumsBase
{
    // 定数メンバ
    const codes_help_messages_name = 'codes_help_messages_name';
    const plugin_name = 'plugin_name';
    const buckets_name = 'buckets_name';
    const buckets_id = 'buckets_id';
    const prefix = 'prefix';
    const type_name = 'type_name';
    const type_code1 = 'type_code1';
    const type_code2 = 'type_code2';
    const type_code3 = 'type_code3';
    const type_code4 = 'type_code4';
    const type_code5 = 'type_code5';
    const code = 'code';
    const value = 'value';
    const additional1 = 'additional1';
    const additional2 = 'additional2';
    const additional3 = 'additional3';
    const additional4 = 'additional4';
    const additional5 = 'additional5';
    const display_sequence = 'display_sequence';

    // key/valueの連想配列
    const enum = [
        self::codes_help_messages_name => '注釈名',
        self::plugin_name => 'プラグイン',
        self::buckets_name => 'buckets_name',
        self::buckets_id => 'buckets_id',
        self::prefix => 'prefix',
        self::type_name => 'type_name',
        self::type_code1 => 'type_code1',
        self::type_code2 => 'type_code2',
        self::type_code3 => 'type_code3',
        self::type_code4 => 'type_code4',
        self::type_code5 => 'type_code5',
        self::code => 'コード',
        self::value => '値',
        self::additional1 => 'additional1',
        self::additional2 => 'additional2',
        self::additional3 => 'additional3',
        self::additional4 => 'additional4',
        self::additional5 => 'additional5',
        self::display_sequence => '表示順',
    ];

    /**
     * 一覧表示 のkey/valueの連想配列を返す
     */
    public static function getIndexColumn()
    {
        $sort_flags = static::enum;
        // plugin_nameは 一覧に必ず表示する項目 のため、取り除く
        unset($sort_flags[self::plugin_name]);
        return $sort_flags;
    }
}
