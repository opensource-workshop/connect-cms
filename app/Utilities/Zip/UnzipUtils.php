<?php

namespace App\Utilities\Zip;

use Illuminate\Support\Facades\Log;

class UnzipUtils
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

    /**
     * ZIP解凍（ファイルを連番変換）
     */
    public static function unzipSerialNumber($zip_path, $plugin_name)
    {
        // zipを展開するフォルダ
        $tmp_dir = UnzipUtils::getTmpDir();
        $unzip_dir_full_path = storage_path('app/') . "tmp/{$plugin_name}/{$tmp_dir}/";

        // 連番ファイル用フォルダ
        $tmp_seq_dir = UnzipUtils::getTmpDir();
        \Storage::makeDirectory("tmp/{$plugin_name}/{$tmp_seq_dir}/");

        // zip展開とパスの保持
        $album_paths = array(); // ファイル階層とアルバム内の階層
        $za = new \ZipArchive();
        $za->open(storage_path('app/') . $zip_path);
        for ($i = 0; $i < $za->numFiles; $i++) {
            $zip_entry = $za->statIndex($i);
            $default_name = $zip_entry['name']; // statIndexで変換しない名前（これでファイル検索しないと見つけられない）

            // ファイルを指定してzipから展開
            $za->extractTo($unzip_dir_full_path, $default_name);

            // 展開ディレクトリから、連番ディレクトリへファイルを移動（ディレクトリの場合は処理しない）
            if (substr($default_name, -1) != '/') {
                \Storage::move("tmp/{$plugin_name}/{$tmp_dir}/" . $default_name, "tmp/{$plugin_name}/{$tmp_seq_dir}/" . $i . '.' . pathinfo($zip_entry['name'], PATHINFO_EXTENSION));

                // UTF-8 でファイルパスを取得
                $raw_infos = $za->statIndex($i, \ZipArchive::FL_ENC_RAW); // FL_ENC_RAW を付けなければ、後でUTF-8 に変換できない。
                $path_utf8 = mb_convert_encoding($raw_infos['name'], \CsvCharacterCode::utf_8, \CsvCharacterCode::sjis_win.", ".\CsvCharacterCode::utf_8);

                $album_paths[$i]['album_path'] = $path_utf8;
                $album_paths[$i]['is_folder'] = (substr($default_name, -1) != '/') ? false : true;
            }
        }
        $za->close();

        // ファイル一覧
        $tmp_files = \Storage::allFiles("tmp/{$plugin_name}/{$tmp_seq_dir}/");
        foreach ($tmp_files as $tmp_file) {
            $album_paths[pathinfo($tmp_file, PATHINFO_FILENAME)]['src_path'] = $tmp_file;
        }

        return [$album_paths, ["tmp/{$plugin_name}/{$tmp_dir}/", "tmp/{$plugin_name}/{$tmp_seq_dir}/"]];
    }

    /**
     * ZIP解凍（ファイルを連番変換）のテンポラリ・ファイル＆ディレクトリの削除
     */
    public static function deleteUnzipTmp($tmp_dirs, $delete_path)
    {
        \Storage::deleteDirectory($tmp_dirs[0]);
        \Storage::deleteDirectory($tmp_dirs[1]);
        \Storage::delete($delete_path);
    }
}
