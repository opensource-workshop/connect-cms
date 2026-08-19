<?php

namespace App\Utilities\Preview;

class PreviewDevice
{
    /** 現在のブラウザサイズを利用する選択値 */
    public const CURRENT = 'current';

    /** プレビューで選択できる画面サイズ */
    private const DEVICES = [
        self::CURRENT => [
            'label' => '現在の画面',
            'width' => null,
            'height' => null,
        ],
        'pc' => [
            'label' => 'PC',
            'width' => 1200,
            'height' => 800,
        ],
        'tablet' => [
            'label' => 'タブレット',
            'width' => 768,
            'height' => 1024,
        ],
        'smartphone' => [
            'label' => 'スマホ',
            'width' => 390,
            'height' => 844,
        ],
    ];

    /** 未指定または不正な画面サイズを現在の画面として扱う */
    public static function normalize(?string $device): string
    {
        return array_key_exists($device, self::DEVICES) ? $device : self::CURRENT;
    }

    /** 画面サイズ選択肢を返す */
    public static function devices(): array
    {
        return self::DEVICES;
    }

    /** 選択した画面サイズの表示名を返す */
    public static function label(?string $device): string
    {
        return self::DEVICES[self::normalize($device)]['label'];
    }

    /** プレビュー対象URLへiframe内表示用のクエリを付ける */
    public static function frameUrl(string $permanent_link): string
    {
        return self::appendQuery($permanent_link, [
            'mode' => 'preview',
            'preview_frame' => '1',
        ]);
    }

    /** クエリがある場合だけURLへ付与する */
    private static function appendQuery(string $path, array $query): string
    {
        return empty($query) ? $path : $path . '?' . http_build_query($query);
    }
}
