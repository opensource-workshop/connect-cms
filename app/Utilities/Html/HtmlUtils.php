<?php

namespace App\Utilities\Html;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\Storage;

/**
 * HtmlUtils
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @package Utilities
 */
class HtmlUtils
{
    /**
     * HTMLPurifierのクラスインスタンスを取得
     */
    public static function getHtmlPurifier()
    {
        // HTMLPurifierを設定するためのクラスを生成する
        $config = HTMLPurifier_Config::createDefault();

        if (!Storage::exists('tmp/htmlpurifier')) {
            Storage::makeDirectory('tmp/htmlpurifier');
        }
        $config->set('Cache.SerializerPath', storage_path('app/tmp/htmlpurifier'));

        // bugfix: class指定を許可は、デフォルト null ですべてのクラスが許可されている http://htmlpurifier.org/live/configdoc/plain.html#Attr.AllowedClasses
        // $config->set('Attr.AllowedClasses', array()); // class指定を許可する
        $config->set('Attr.AllowedClasses', null); // class指定を許可する
        $config->set('Attr.EnableID', true);          // id属性を許可する
        $config->set('Filter.YouTube', true);         // Youtube埋め込みを許可する
        $config->set('HTML.TargetBlank', true);       // target="_blank" が使えるようにする
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%'); //allow YouTube and Vimeo

        $purifier = new HTMLPurifier($config);
        return $purifier;
    }
}
