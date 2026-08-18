<?php

namespace Tests\Unit\Plugins;

use App\Plugins\PluginBase;
use Illuminate\Support\Facades\File;
use ReflectionMethod;
use Tests\TestCase;

/**
 * PluginBase::getThemes() を対象にした単体テスト。
 *
 * ページ管理・サイト管理・テーマチェンジャーが使うテーマ一覧取得で、
 * themes.ini にダブルクォートなしの半角カッコがあっても画面が落ちないことを検証する。
 */
class PluginBaseGetThemesTest extends TestCase
{
    /**
     * テストで作成したテーマディレクトリ
     */
    private $test_theme_dirs = [];

    /**
     * テストで作成したテーマディレクトリの削除
     */
    protected function tearDown(): void
    {
        foreach ($this->test_theme_dirs as $test_theme_dir) {
            if (File::isDirectory($test_theme_dir)) {
                File::deleteDirectory($test_theme_dir);
            }
        }
        $this->test_theme_dirs = [];

        parent::tearDown();
    }

    /**
     * テスト用のユーザ・テーマディレクトリと themes.ini を作成する
     */
    private function makeUserTheme($dir_name, $themes_ini): string
    {
        $theme_dir = public_path() . '/themes/Users/' . $dir_name;
        File::makeDirectory($theme_dir, 0775, true);
        $this->test_theme_dirs[] = $theme_dir;

        File::put($theme_dir . '/themes.ini', $themes_ini);

        return $theme_dir;
    }

    /**
     * PluginBase::getThemes() の実行（protected のためリフレクションで呼ぶ）
     */
    private function getThemes(): array
    {
        $plugin_base = new PluginBase();
        $method = new ReflectionMethod(PluginBase::class, 'getThemes');
        $method->setAccessible(true);

        try {
            return $method->invoke($plugin_base);
        } finally {
            // PluginBase のコンストラクタで設定されたエラーハンドラを元に戻す
            restore_error_handler();
        }
    }

    /**
     * テーマ一覧から指定ディレクトリのテーマを探す
     */
    private function findUserTheme(array $themes, $dir_name)
    {
        foreach ($themes as $theme) {
            if (!array_key_exists('themes', $theme)) {
                continue;
            }
            foreach ($theme['themes'] as $sub_theme) {
                if ($sub_theme['dir'] === 'Users/' . $dir_name) {
                    return $sub_theme;
                }
            }
        }
        return null;
    }

    /**
     * ダブルクォートなしの半角カッコを含む themes.ini があっても、
     * 例外を投げずにテーマ一覧が取得できることを守る。（Issue #2465）
     */
    public function testGetThemesWithReservedCharThemeName()
    {
        $dir_name = 'cc_test_paren_' . uniqid();
        $this->makeUserTheme($dir_name, "[base]\ntheme_name = theme_user_02 (clear-steelblue)\n");

        $themes = $this->getThemes();

        $this->assertNotEmpty($themes);
        $theme = $this->findUserTheme($themes, $dir_name);
        $this->assertNotNull($theme);
        $this->assertSame('theme_user_02 (clear-steelblue)', $theme['name']);
    }

    /**
     * ダブルクォートなしの既存 themes.ini が、これまで通り読めることを守る。
     */
    public function testGetThemesWithExistingThemeName()
    {
        $dir_name = 'cc_test_plain_' . uniqid();
        $this->makeUserTheme($dir_name, "[base]\ntheme_name = カスタムテーマ１\n");

        $themes = $this->getThemes();

        $theme = $this->findUserTheme($themes, $dir_name);
        $this->assertNotNull($theme);
        $this->assertSame('カスタムテーマ１', $theme['name']);
    }

    /**
     * 復旧できないほど壊れた themes.ini があっても、
     * 例外を投げずディレクトリ名をテーマ名として一覧が取得できることを守る。
     */
    public function testGetThemesWithBrokenThemesIni()
    {
        $dir_name = 'cc_test_broken_' . uniqid();
        // セクション行が閉じていないため、読み込みに失敗する
        $this->makeUserTheme($dir_name, "[base\ntheme_name = テーマA\n");

        $themes = $this->getThemes();

        $theme = $this->findUserTheme($themes, $dir_name);
        $this->assertNotNull($theme);
        $this->assertSame($dir_name, $theme['name']);
    }
}
