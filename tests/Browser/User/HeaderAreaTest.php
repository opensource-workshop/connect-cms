<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;

/**
 * ヘッダーエリアテスト
 *
 * 下記いずれかのコマンドでテスト実行
 *
 * batファイルで実行)
 * > tests\bin\connect-cms-test.bat
 *
 * グループ指定でまとめてテスト実行)
 * php artisan dusk --group=user
 *
 * クラス指定して１つだけテスト実行)
 * php artisan dusk tests\Browser\User\HeaderArea.php
 *
 * tests\Browser 配下のファイル末尾Test.phpを全てテスト実行)
 * php artisan dusk
 */
class HeaderAreaTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests `php artisan dusk --group=user`
     */
    public function testInvoke()
    {
        $this->login(1);
        $this->pluginAddModal();
        $this->editContent();
    }

    /**
     * プラグイン追加
     */
    private function pluginAddModal()
    {
        $this->browse(function (Browser $browser) {
            // 管理機能からプラグイン追加で固定記事を追加する。
            $browser->visit('/')
                    ->clickLink('管理機能')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);

            $browser->clickLink('プラグイン追加')
                    ->assertTitleContains('Connect-CMS');

            // 早すぎると、プラグイン追加ダイアログが表示しきれないので、1秒待つ。
            $browser->pause(1000);
            $this->screenshot($browser);

            $browser->select('add_plugin', 'contents')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);
        });
    }

    /**
     * 固定記事の編集
     */
    private function editContent()
    {
        $header_first_content_frame = Frame::where('area_id', 0)->orderBy('display_sequence', 'asc')->first();
        if (empty($header_first_content_frame)) {
            $this->assertFalse(false);
        }

        $this->browse(function (Browser $browser) use ($header_first_content_frame) {
            // 管理機能からプラグイン追加で固定記事を追加する。
            $browser->visit('/')
                    ->click('#contents-' . $header_first_content_frame->id . '-edit-button')
                    ->assertTitleContains('Connect-CMS');
            $this->screenshot($browser);

            // $browser->driver->executeScript('tinyMCE.get(\'contents\').setContent(\'<h1>Test Description</h1>\')');
            $browser->driver->executeScript('tinyMCE.activeEditor.setContent(\'<h1>Test Description</h1>\')');
            //$browser->wysiwyg('tinymce', '#contents-5-form', '<h2>value</h2>');
            //$browser->keys('#contents', 'Text');

            $this->screenshot($browser);
        });
    }
}
