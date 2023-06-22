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
        $pattern = '/<img.*?style\s*=\s*[\"\'](.*?)[\"\'].*?>/i';
        return self::getContentPregMatchAll($content, $pattern, 1);
    }

    /**
     * HTML からiframe タグを取得
     */
    private static function getIframe($content)
    {
        $pattern = '/<iframe(".*?"|\'.*?\'|[^\'"])*?>/i';
        return self::getContentPregMatchAll($content, $pattern, 0);
    }

    /**
     * iframe タグの style 属性を取得
     * iframe タグの style 属性は任意のため、HTMLではなくiframeタグのみから取得する
     */
    private static function getIframeStyle($iframe_tag)
    {
        $pattern = '/<iframe.*?style\s*=\s*[\"\'](.*?)[\"\'].*?>/i';
        // ※ iframe のstyleは必須じゃないので、この正規表現でHTMLからstyle取得だと、下記のようなものを取得して誤作動した
        // string(137) "<iframe src="//www.youtube.com/embed/xxxxxx" width="800" height="449" allowfullscreen=""></iframe></p><p style="text-align:center;">"
        return self::getContentPregMatchAll($iframe_tag, $pattern, 1);
    }

    /**
     * HTML からiframe タグの src 属性を取得
     */
    private static function getIframeSrc($content)
    {
        $pattern = '/<iframe.*?src\s*=\s*[\"\'](.*?)[\"\'].*?>/i';
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
                // ../../uploadsあり。class=なし
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

                // ../../uploadsあり。class=あり。img-responsiveあり(nc3対応)
                if (stripos($img_src, '../../uploads') !== false && stripos($img_src, 'class=') !== false && stripos($img_src, 'img-responsive') !== false) {
                    // 画像のファイル名。$file_name には、最初に "/" がつく。
                    $last_slash_pos = mb_strripos($img_srcs[1][$key], '/');
                    $file_name = mb_substr($img_srcs[1][$key], $last_slash_pos);
                    $file_path = storage_path() . "/app/{$import_dir}uploads{$file_name}";
                    // 画像が存在し、img_fluid_min_width で指定された大きさ以上なら、img-fluid クラスをつける。
                    if (File::exists($file_path)) {
                        $imagesize = getimagesize($file_path);
                        if (is_array($imagesize) && $imagesize[0] >= $img_fluid_min_width) {
                            $new_img_src = str_replace('img-responsive', 'img-fluid', $img_src);
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

            // iframeのstyle属性は任意のため、ない事がある。そのため置換する場合、下記で対応
            // ・まず該当のiframeタグを取得（A）
            // ・該当タグから 目的の属性（style）を取得
            // ・該当タグを置換（A’）
            // ・コンテンツの（A）タグを（A’）タグに置換

            // iFrame タグ取得（複数の可能性もあり）
            $iframe_tags = self::getIframe($content);
            if (!empty($iframe_tags)) {
                foreach ($iframe_tags as $iframe_tag) {

                    // iFrame のsrc を取得（複数の可能性もあり）
                    $iframe_styles = self::getIframeStyle($iframe_tag);
                    if (!empty($iframe_styles)) {
                        foreach ($iframe_styles as $iframe_style) {
                            $width_pos = strpos($iframe_style, 'width');
                            $width_length = strpos($iframe_style, ";", $width_pos) - $width_pos + 1;
                            $iframe_style_width = substr($iframe_style, $width_pos, $width_length);
                            if (!empty($iframe_style_width)) {
                                // iframeタグ内を置換
                                $iframe_tag_replace = str_replace($iframe_style_width, "width:100%;", $iframe_tag);

                                // コンテンツのiframeタグのみ置換
                                $content = str_replace($iframe_tag, $iframe_tag_replace, $content);
                            }
                        }
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
     * HTML からNC3絵文字を削除
     *
     * ・まずimgタグを取得
     * ・imgタグから 目的のclass = "nc-title-icon" を取得
     * ・該当imgを消す
     * ※ いきなり imgタグから 目的のclass = "nc-title-icon" を取得 すると、正規表現の加減で、1行の「imgタグ(NC3絵文字)Pタグimgタグ(NC3絵文字)」が間違って取得されるため。
     *
     * @link https://regexper.com/#%2F%3Cimg.*%3F%28class%5Cs*%3D%5Cs*%5B%5C%22%5C'%5Dnc-title-icon%5B%5C%22%5C'%5D%29.*%3F%3E%2Fi
     * @link https://www.php.net/manual/ja/reference.pcre.pattern.modifiers.php
     */
    public static function deleteNc3Emoji($content)
    {
        // ・まずimgタグを取得
        $imgs = self::getContentImageTag($content);
        if (!$imgs) {
            return $content;
        }

        foreach ($imgs[0] as $img) {
            // ・imgタグから 目的のclass = "nc-title-icon" を取得
            $pattern = '/<img.*?(class\s*=\s*[\"\']nc-title-icon[\"\']).*?>/i';
            preg_match_all($pattern, $img, $matches);
            foreach ($matches[0] as $matche) {
                // ・該当imgを消す
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

    /**
     * ファイル名から拡張子を取得
     */
    public static function getExtension($filename)
    {
        $filepath = pathinfo($filename);
        return $filepath['extension'];
    }

    /**
     * 半角 @ を全角 ＠ に変換する。
     */
    public static function replaceFullwidthAt($str)
    {
        return str_replace('@', '＠', $str);
    }
}
