<?php

namespace Tests\Unit\Utilities\File;

use PHPUnit\Framework\TestCase;
use App\Utilities\File\FileUtils;

class FileUtilsTest extends TestCase
{
    /**
     * ファイル名を有効なものに変換するテスト
     *
     * @dataProvider validFilenameProvider
     */
    public function testToValidFilename($input, $expected)
    {
        $result = FileUtils::toValidFilename($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * ファイル名を有効なものに変換するテストのデータプロバイダ
     */
    public function validFilenameProvider()
    {
        return [
            // 禁止文字を含むファイル名
            ['test<file>.txt', 'test＜file＞.txt'],
            ['invalid|name?.txt', 'invalid｜name？.txt'],
            ['C:\\path\\to\\file.txt', 'C：＼path＼to＼file.txt'],
            ['file:name*.txt', 'file：name＊.txt'],

            // 禁止文字を含まないファイル名
            ['valid_filename.txt', 'valid_filename.txt'],

            // 空文字
            ['', ''],
        ];
    }

    /**
     * ini ファイルの値に変換するテスト
     *
     * @dataProvider escapeIniValueProvider
     */
    public function testEscapeIniValue($input, $expected)
    {
        $result = FileUtils::escapeIniValue($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * ini ファイルの値に変換するテストのデータプロバイダ
     */
    public function escapeIniValueProvider()
    {
        return [
            // ini の予約文字はダブルクォートで囲むことで使える
            ['theme_user_02 (clear-steelblue)', '"theme_user_02 (clear-steelblue)"'],
            ['テーマA (青)', '"テーマA (青)"'],
            ['A!B|C&D', '"A!B|C&D"'],
            ['コメント;付き', '"コメント;付き"'],

            // ダブルクォート・円記号・改行は除去する
            ['テーマ"A"', '"テーマA"'],
            ['テーマ\\A', '"テーマA"'],
            ["テーマ\r\nA", '"テーマA"'],

            // 予約文字を含まない値はそのままダブルクォートで囲むだけ
            ['Default', '"Default"'],

            // 空文字
            ['', '""'],
        ];
    }
}
