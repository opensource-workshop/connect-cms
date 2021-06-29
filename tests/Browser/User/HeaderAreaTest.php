<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;

use App\Enums\PluginName;

/**
 * ヘッダーエリアテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class HeaderAreaTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1);

        // 固定記事をプラグイン追加
        $this->addPluginModal(PluginName::getPluginName(PluginName::contents));

        $this->editContent();
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

            $browser->press('登録確定')
                    ->assertSee('Test Description');

            $this->screenshot($browser);
        });
    }
}
