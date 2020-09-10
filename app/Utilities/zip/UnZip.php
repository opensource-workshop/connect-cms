<?php

namespace App\Utilities\zip;

use Illuminate\Support\Facades\Log;

class Unzip
{
    /**
     * ZipArchiveクラスが使えるか
     */
    public static function useZipArchive(): bool
    {
        return class_exists('ZipArchive');
    }

    /**
     * 一時的な解凍フォルダ名
     */
    public static function getTmpDir(): string
    {
        return uniqid('', true);
    }

    /**
     * ZIP解凍
     */
    public static function unzip(string $zip_path, string $unzip_dir_full_path)
    {
        $zip = new \ZipArchive();
        // $res = $zip->open(storage_path('app/') . $path);
        $res = $zip->open($zip_path);
        if ($res !== true) {
            // ZIPファイルオープン失敗
            $error_msg = "ZIPファイルを開く時にエラーが発生しました。（エラーコード：{$res}）ZIPファイルを確認して作り直してください。";
            return $error_msg;
        }

        $index = 0;
        while ($zipEntry = $zip->statIndex($index)) {
            $zipEntryName = $zipEntry['name'];

            // windowsでZIPファイルを作成でZIP内のファイル名が日本語の場合、ファイル名はsjis_winになるためエンコード必要
            $zipEntry2 = $zip->statIndex($index, \ZipArchive::FL_ENC_RAW);
            $zipEntry2Name = $zipEntry2['name'];
            $destName = mb_convert_encoding($zipEntry2Name, \CsvCharacterCode::utf_8, \CsvCharacterCode::sjis_win.", ".\CsvCharacterCode::utf_8);
            // 末尾.csv（大文字小文字区別しない）は.csv(小文字)にリネーム
            // $destName = preg_replace('/\.csv$/i', '.csv', $destName);

            // utf-8にリネーム
            if ($zip->renameName($zipEntryName, $destName) === false) {
                // zip内リネームエラー
                $zip->close();
                $error_msg = "ZIPファイル内のファイル名変更時にエラーが発生しました。";
                return $error_msg;
            }

            // 1ファイルずつzipから取り出して解凍
            if ($zip->extractTo($unzip_dir_full_path, $destName) === false) {
                // zip解凍エラー
                $zip->close();
                $error_msg = "ZIPファイル解凍時にエラーが発生しました。";
                return $error_msg;
            }
            $index++;
        }
        $zip->close();

        return true;
    }

    /**
     * 空でないディレクトリを削除
     * copy by https://qiita.com/suin/items/b5445ff6fbc21e8d883a
     */
    public static function rmdirNotEmpty($dir)
    {
        if (!file_exists($dir)) {
            // ディレクトリがなければ処理しない
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir() === true) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }
}
