<?php

namespace App\Enums;

use App\Enums\EnumsBase;

/**
 * ページのmeta robots設定
 */
final class PageMetaRobots extends EnumsBase
{
    const noindex = 'noindex';
    const nofollow = 'nofollow';
    const noarchive = 'noarchive';
    const nosnippet = 'nosnippet';
    const noimageindex = 'noimageindex';
    const notranslate = 'notranslate';

    const enum = [
        self::noindex => '検索結果に表示させない（noindex）',
        self::nofollow => 'ページ内のリンク先を検索エンジンに辿らせない（nofollow）',
        self::noarchive => '検索結果にキャッシュ（保存版）を出さない（noarchive）',
        self::nosnippet => '検索結果にページの説明を表示させない（nosnippet）',
        self::noimageindex => 'ページ内の画像を画像検索に載せない（noimageindex）',
    ];

    /**
     * 指定された値を説明文の配列に変換
     */
    public static function descriptions(array $values): array
    {
        $members = static::getMembers();

        $descriptions = [];
        foreach ($values as $value) {
            if (array_key_exists($value, $members)) {
                $descriptions[] = $members[$value];
            }
        }

        return $descriptions;
    }
}
