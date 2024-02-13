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

    /**
     * localeをセット
     * fgetcsv(), pathinfo()等の処理前に実行する
     */
    public static function setLocale(): void
    {
        if (0 === strpos(PHP_OS, 'WIN')) {
            // *** win
            // fgetcsv() は ロケール設定の影響を受け、xampp環境＋日本語文字列で誤動作したため、ロケール設定する。
            //
            // [詳細]
            // ・xampp環境のロケールは、LC_CTYPE=Japanese_Japan.932 でした。
            //   これだと、「ド」の次のカンマが認識できなくなり 1カラム 'コード,値' で誤認識される。また末尾改行の処理も誤動作おこしました。
            //   \Log::debug(var_export(setlocale(LC_ALL, "0"), true));
            //   [2021-05-07 17:26:12] local.DEBUG: 'LC_COLLATE=C;LC_CTYPE=Japanese_Japan.932;LC_MONETARY=C;LC_NUMERIC=C;LC_TIME=C'
            // ・fgetcsv() https://www.php.net/manual/ja/function.fgetcsv.php
            //
            // [windowsのlocale]
            // ・https://learn.microsoft.com/en-us/openspecs/windows_protocols/ms-lcid/a9eac961-e77d-41a6-90a5-ce1a8b0cdb9c
            //   > (表列)Language tag ＞ ja-JP
            setlocale(LC_ALL, 'ja-JP.UTF-8');
        } else {
            // *** linux
            // 環境によってはsetlocale しておかないと、ファイル名がうまくpathinfo で取得できなかった。
            // 2020-12-15 Connect-CMS 公式サイトで、ファイル名が空になったり一部しか取得できないケースがあった。
            setlocale(LC_ALL, 'ja_JP.UTF-8');
        }
    }
}
