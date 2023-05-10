<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * 検索対象プラグイン
 */
final class FormsRegisterTargetPlugin extends EnumsBase
{
    // 定数メンバ
    const blogs = 'blogs';
    const bbses = 'bbses';
    const faq = 'faqs';

    // key/valueの連想配列
    const enum = [
        self::blogs => 'ブログ',
        self::bbses => '掲示板',
        self::faq => 'FAQ',
    ];

    /**
     * フレーム指定できるプラグイン取得
     */
    public static function getPluginsCanSpecifiedFrames()
    {
        $enum = static::enum;

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
