<?php

namespace App\Utilities\Request;

/**
 * リクエスト関連
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @package Utilities
 */
class RequestUtils
{
    /**
     * botチェック
     */
    public static function isBot($request): bool
    {
        $is_bot = false;
        $ua = $request->header('User-Agent');
        $bots = [
            'bot',
            'spider',
            'crawler',
            'Linguee',
            'proximic',
            'GrapeshotCrawler',
            'Mappy',
            'MegaIndex',
            'ltx71',
            'integralads',
            'Yandex',
            'Y!',               // Yahoo!JAPAN
            'Slurp',            // yahoo
            'ichiro',           // goo
            'goo_vsearch',      // goo
            'gooblogsearch',    // goo
            'netEstate',
            'Yeti',             // Naver
            'Daum',
            'Seekport',
            'Qwantify',
            'GoogleImageProxy', // google
            'QQBrowser',
            'ManicTime',
            'Hatena',
            'PocketImageCache',
            'Feedly',
            'Tiny Tiny RSS',
            'Barkrowler',
            'SISTRIX Crawler',
            'woorankreview',
            'MegaIndex',
            'Megalodon',
            'Steeler',
            'dataxu',
            'ias-sg',
            'go-resty',
            'python-requests',
            'meg',
            'Scrapy',
            'GoogleOther',
        ];

        foreach ($bots as $bot) {
            // 大文字小文字を区別せず ユーザーエージェントに bot が含まれているかチェック
            if (strpos($ua, $bot) !== false) {
                // bot
                $is_bot = true;
            }
        }

        return $is_bot;
    }
}
