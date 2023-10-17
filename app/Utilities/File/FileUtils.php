<?php

namespace App\Utilities\File;

use App\Enums\ImageMimetype;

class FileUtils
{
    /**
     * サイズのフォーマット
     */
    public static function getFormatSize($size, $r = 0)
    {
        $units = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }
        return round($size, $r).$units[$i];
    }

    /**
     * 画像ファイルならtrueを返す
     *
     * @return boolean
     */
    public static function isImage($mimetype) : bool
    {
        return in_array($mimetype, ImageMimetype::getMemberKeys()) ? true : false;
    }
}
