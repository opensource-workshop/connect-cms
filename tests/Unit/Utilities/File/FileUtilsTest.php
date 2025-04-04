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
}
