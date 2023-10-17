<?php

namespace App\Utilities\File;

use App\Enums\ImageMimetype;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;

class FileUtils
{
    /**
     * サイズのフォーマット(round)
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
     * サイズのフォーマット(小数点)
     */
    public static function getFormatSizeDecimalPoint(int $size): string
    {
        $units = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }
        return sprintf('%1.2f', $size).$units[$i];
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

    /**
     * 指定ディレクトリ配下のサブディレクトリを含めたファイル一覧取得
     */
    public static function getFileList(string $dir): array
    {
        $iterator = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($iterator);
        $list = array();
        foreach ($iterator as $fileinfo) { // $fileinfoはSplFiIeInfoオブジェクト
            if ($fileinfo->isFile()) {
                $list[] = $fileinfo->getPathname();
            }
        }
        return $list;
    }
}
