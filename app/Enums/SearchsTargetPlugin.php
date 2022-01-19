<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 検索対象プラグイン
 */
final class SearchsTargetPlugin extends EnumsBase
{
    // 定数メンバ
    const contents = 'contents';
    const blogs = 'blogs';
    const bbses = 'bbses';
    const databases = 'databases';

    // key/valueの連想配列
    const enum = [
        self::contents => '固定記事',
        self::blogs => 'ブログ',
        self::bbses => '掲示板',
        self::databases => 'データベース',
    ];

    /**
     * フレーム指定できるプラグイン取得
     */
    public static function getPluginsCanSpecifiedFrames()
    {
        $enum = static::enum;
        // 固定記事は除外
        unset($enum[self::contents]);

        return $enum;
    }

    /**
     * フレーム指定できるプラグインキー取得
     */
    public static function getKeysPluginsCanSpecifiedFrames()
    {
        return array_keys(self::getPluginsCanSpecifiedFrames());
    }
}
