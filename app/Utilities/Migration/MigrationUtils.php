<?php

namespace App\Utilities\Migration;

use Illuminate\Support\Arr;

/**
 * 移行関連Utils
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 移行
 * @package Util
 */
class MigrationUtils
{
    /**
     * 配列の値の取得
     */
    public static function getArrayValue($array, $key1, $key2 = null, $default = "")
    {
        if (is_null($key2)) {
            return Arr::get($array, $key1, $default);
        }
        return Arr::get($array, "$key1.$key2", $default);
    }

    /**
     * HTML からimg タグの src 属性を取得
     */
    public static function getContentImage($content)
    {
        $pattern = '/<img.*?src\s*=\s*[\"|\'](.*?)[\"|\'].*?>/i';
        return self::getContentPregMatchAll($content, $pattern, 1);
    }

    /**
     * HTML からimg タグ全体を取得
     */
    public static function getContentImageTag($content)
    {
        $pattern = '/<img.*?src\s*=\s*[\"|\'](.*?)[\"|\'].*?>/i';

        if (preg_match_all($pattern, $content, $images)) {
            if (is_array($images) && isset($images[0])) {
                return $images;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * HTML からimg タグの style 属性を取得
     */
    public static function getImageStyle($content)
    {
        $pattern = '/<img.*?style\s*=\s*[\"|\'](.*?)[\"|\'].*?>/i';
        return self::getContentPregMatchAll($content, $pattern, 1);
    }

    /**
     * HTML からiframe タグの style 属性を取得
     */
    public static function getIframeStyle($content)
    {
        $pattern = '/<iframe.*?style\s*=\s*[\"|\'](.*?)[\"|\'].*?>/i';
        return self::getContentPregMatchAll($content, $pattern, 1);
    }

    /**
     * HTML からiframe タグの src 属性を取得
     */
    public static function getIframeSrc($content)
    {
        $pattern = '/<iframe.*?src\s*=\s*[\"|\'](.*?)[\"|\'].*?>/i';
        return self::getContentPregMatchAll($content, $pattern, 1);
    }

    /**
     * HTML からa タグの href 属性を取得
     */
    public static function getContentAnchor($content)
    {
        $pattern = "|<a.*?href=\"(.*?)\".*?>(.*?)</a>|mis";
        return self::getContentPregMatchAll($content, $pattern, 1);
    }

    /**
     * HTML から href,src 属性を取得
     */
    public static function getContentHrefOrSrc($content)
    {
        $pattern = '/(?<=href=").*?(?=")|(?<=src=").*?(?=")/i';
        return self::getContentPregMatchAll($content, $pattern, 0);
    }

    /**
     * HTML から preg_match_all を使って特定項目を取得
     */
    private static function getContentPregMatchAll($content, string $pattern, int $get_matches_idx)
    {
        if (preg_match_all($pattern, $content, $matches)) {
            if (is_array($matches) && isset($matches[$get_matches_idx])) {
                return $matches[$get_matches_idx];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
