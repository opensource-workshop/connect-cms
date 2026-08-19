<?php

namespace Tests\Unit\Utilities\File;

use App\Utilities\File\FileUtils;
use Tests\TestCase;

/**
 * FileUtils::parseIniFile() を対象にした単体テスト。
 *
 * テーマ名に半角カッコ等の ini 予約文字が含まれていても画面が落ちないこと、
 * および既存の themes.ini（ダブルクォートなし）がこれまで通り読めることを検証する。
 *
 * \Log ファサードを使うため、素の PHPUnit ではなく Laravel の TestCase を継承する。
 */
class FileUtilsParseIniFileTest extends TestCase
{
    /**
     * テストで作成した ini ファイルのパス
     */
    private $test_ini_paths = [];

    /**
     * テストで作成した ini ファイルの削除
     */
    protected function tearDown(): void
    {
        foreach ($this->test_ini_paths as $test_ini_path) {
            if (file_exists($test_ini_path)) {
                unlink($test_ini_path);
            }
        }
        $this->test_ini_paths = [];

        parent::tearDown();
    }

    /**
     * ini ファイルを一時ディレクトリに作成する
     */
    private function makeIniFile($contents): string
    {
        $ini_path = sys_get_temp_dir() . '/cc_test_' . uniqid() . '.ini';
        file_put_contents($ini_path, $contents);
        $this->test_ini_paths[] = $ini_path;

        return $ini_path;
    }

    /**
     * 既存の themes.ini（ダブルクォートなし）がこれまで通り読めることを守る。
     *
     * @dataProvider existingThemesIniProvider
     */
    public function testParseIniFileExistingFormat($contents, $expected)
    {
        $result = FileUtils::parseIniFile($this->makeIniFile($contents));
        $this->assertEquals($expected, $result);
    }

    /**
     * 既存の themes.ini のパターン（public/themes 配下の実ファイルに合わせたもの）
     */
    public function existingThemesIniProvider()
    {
        return [
            // Defaults/Blue/themes.ini など
            "英数字" => ["[base]\ntheme_name = Blue\n", ['theme_name' => 'Blue']],
            // Users/hpsc/themes.ini など
            "アンダースコア" => ["[base]\ntheme_name = hpsc_second\n", ['theme_name' => 'hpsc_second']],
            // 日本語のテーマ名
            "日本語" => ["[base]\ntheme_name = カスタムテーマ１\n", ['theme_name' => 'カスタムテーマ１']],
            // Users/themes.ini などのグループ用
            "グループ用" => [
                "[base]\ntheme_name = Users グループ\ntheme_dir = group\n",
                ['theme_name' => 'Users グループ', 'theme_dir' => 'group'],
            ],
            // 値の前後の空白は取り除かれる
            "前後の空白" => ["[base]\ntheme_name =   Blue  \n", ['theme_name' => 'Blue']],
            // Users/themes.ini などの先頭コメント
            "コメント行あり" => [";comment\n[base]\ntheme_name = hpsc\n", ['theme_name' => 'hpsc']],
            // 数字のみ
            "数字" => ["[base]\ntheme_name = 2024\n", ['theme_name' => '2024']],
        ];
    }

    /**
     * ダブルクォートで囲まれていない ini 予約文字が、エラーにならず読めることを守る。
     *
     * @dataProvider reservedCharThemesIniProvider
     */
    public function testParseIniFileReservedChar($contents, $expected)
    {
        $result = FileUtils::parseIniFile($this->makeIniFile($contents));
        $this->assertEquals($expected, $result);
    }

    /**
     * ini 予約文字を含む themes.ini のパターン
     */
    public function reservedCharThemesIniProvider()
    {
        return [
            // Issue #2465 の再現ケース
            "半角カッコ" => [
                "[base]\ntheme_name = theme_user_02 (clear-steelblue)\n",
                ['theme_name' => 'theme_user_02 (clear-steelblue)'],
            ],
            "その他の予約文字" => [
                "[base]\ntheme_name = A!B|C&D\n",
                ['theme_name' => 'A!B|C&D'],
            ],
        ];
    }

    /**
     * ダブルクォートで囲んだ値は、ダブルクォートが外れて読めることを守る。
     */
    public function testParseIniFileQuoted()
    {
        $ini_path = $this->makeIniFile("[base]\ntheme_name = \"theme_user_02 (clear-steelblue)\"\n");

        $result = FileUtils::parseIniFile($ini_path);

        $this->assertEquals(['theme_name' => 'theme_user_02 (clear-steelblue)'], $result);
    }

    /**
     * FileUtils::escapeIniValue() で書き出した値が、そのまま読み戻せることを守る。
     */
    public function testParseIniFileRoundTrip()
    {
        $theme_name = 'テーマA (clear-steelblue)';
        $ini_path = $this->makeIniFile('[base]' . "\n" . 'theme_name = ' . FileUtils::escapeIniValue($theme_name) . "\n");

        $result = FileUtils::parseIniFile($ini_path);

        $this->assertEquals($theme_name, $result['theme_name']);
    }

    /**
     * 復旧できないほど壊れた ini ファイルでも、例外を投げずに空配列を返すことを守る。
     */
    public function testParseIniFileBroken()
    {
        // セクション行が閉じていないため、読み込みに失敗する
        $ini_path = $this->makeIniFile("[base\ntheme_name = テーマA\n");

        $result = FileUtils::parseIniFile($ini_path);

        $this->assertSame([], $result);
    }

    /**
     * 存在しないファイルを指定しても、例外を投げずに空配列を返すことを守る。
     */
    public function testParseIniFileNotExists()
    {
        $result = FileUtils::parseIniFile(sys_get_temp_dir() . '/cc_test_not_exists_' . uniqid() . '.ini');

        $this->assertSame([], $result);
    }
}
