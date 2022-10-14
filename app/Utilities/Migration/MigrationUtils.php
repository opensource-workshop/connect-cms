<?php

namespace App\Utilities\Migration;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

use App\Models\Core\Configs;

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
    private static function getContentImageTag($content)
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
    private static function getImageStyle($content)
    {
        $pattern = '/<img.*?style\s*=\s*[\"|\'](.*?)[\"|\'].*?>/i';
        return self::getContentPregMatchAll($content, $pattern, 1);
    }

    /**
     * HTML からiframe タグの style 属性を取得
     */
    private static function getIframeStyle($content)
    {
        $pattern = '/<iframe.*?style\s*=\s*[\"|\'](.*?)[\"|\'].*?>/i';
        return self::getContentPregMatchAll($content, $pattern, 1);
    }

    /**
     * HTML からiframe タグの src 属性を取得
     */
    private static function getIframeSrc($content)
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
     * HTML から preg_match_all を使ってタグや属性等を取得
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

    /**
     * 全体から画像のclassを探し、img_fluid_min_width で指定された大きさ以上なら、img-fluid クラスをつける。
     */
    public static function convertContentImageClassToImgFluid(?string $content, string $import_dir, int $img_fluid_min_width): ?string
    {
        // 画像全体にレスポンシブCSS を適用する。
        $img_srcs = self::getContentImageTag($content);
        if (!empty($img_srcs)) {
            $img_srcs_0 = array_unique($img_srcs[0]);
            foreach ($img_srcs_0 as $key => $img_src) {
                if (stripos($img_src, '../../uploads') !== false && stripos($img_src, 'class=') === false) {
                    // 画像のファイル名。$file_name には、最初に "/" がつく。
                    $last_slash_pos = mb_strripos($img_srcs[1][$key], '/');
                    $file_name = mb_substr($img_srcs[1][$key], $last_slash_pos);
                    $file_path = storage_path() . "/app/{$import_dir}uploads{$file_name}";
                    // 画像が存在し、img_fluid_min_width で指定された大きさ以上なら、img-fluid クラスをつける。
                    if (File::exists($file_path)) {
                        $imagesize = getimagesize($file_path);
                        if (is_array($imagesize) && $imagesize[0] >= $img_fluid_min_width) {
                            $new_img_src = str_replace('<img ', '<img class="img-fluid" ', $img_src);
                            $content = str_replace($img_src, $new_img_src, $content);
                        }
                    }
                }
            }
        }
        return $content;
    }

    /**
     * 画像のstyleを探し、height をmax-height に変換する。
     */
    public static function convertContentImageHeightToMaxHeight(?string $content): ?string
    {
        $img_styles = self::getImageStyle($content);
        if (!empty($img_styles)) {
            $img_styles = array_unique($img_styles);
            foreach ($img_styles as $img_style) {
                $new_img_style = str_replace('height', 'max-height', $img_style);
                $new_img_style = str_replace('max-max-height', 'max-height', $new_img_style);
                $content = str_replace($img_style, $new_img_style, $content);
            }
        }
        return $content;
    }

    /**
     * Iframeのstyleを探し、width を 100% に変換する。
     */
    public static function convertContentIframeWidthTo100percent(?string $content): ?string
    {
        // Google Map 埋め込み時のスマホ用対応。widthを 100% に変更
        $iframe_srces = self::getIframeSrc($content);
        if (!empty($iframe_srces)) {
            // iFrame のsrc を取得（複数の可能性もあり）
            $iframe_styles = self::getIframeStyle($content);
            if (!empty($iframe_styles)) {
                foreach ($iframe_styles as $iframe_style) {
                    $width_pos = strpos($iframe_style, 'width');
                    $width_length = strpos($iframe_style, ";", $width_pos) - $width_pos + 1;
                    $iframe_style_width = substr($iframe_style, $width_pos, $width_length);
                    if (!empty($iframe_style_width)) {
                        $content = str_replace($iframe_style_width, "width:100%;", $content);
                    }
                }
            }
        }
        return $content;
    }

    /**
     * HTML からGoogle Analytics タグ部分を削除
     */
    public static function deleteGATag($content)
    {
        preg_match_all('/<script(.*?)script>/is', $content, $matches);

        foreach ($matches[0] as $matche) {
            if (stripos($matche, 'www.google-analytics.com/analytics.js')) {
                $content = str_replace($matche, '', $content);
            }
            if (stripos($matche, 'GoogleAnalyticsObject')) {
                $content = str_replace($matche, '', $content);
            }
        }
        return $content;
    }

    /**
     * サイト基本設定をインポート
     */
    public static function updateConfig($name, $ini, $category = 'general')
    {
        if (!array_key_exists('basic', $ini)) {
            return;
        }

        if (array_key_exists($name, $ini['basic'])) {
            $config = Configs::updateOrCreate(
                ['name'     => $name],
                ['name'     => $name,
                 'value'    => $ini['basic'][$name],
                 'category' => $category]
            );
        }
    }

    /**
     * ID のゼロ埋め
     */
    public static function zeroSuppress($id, ?int $size = 4): string
    {
        // ページID がとりあえず、1万ページ未満で想定。
        // ここの桁数を上げれば、さらに大きなページ数でも処理可能
        $size_str = sprintf("%'.02d", $size);

        return sprintf("%'." . $size_str . "d", $id);
    }
}
