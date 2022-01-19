<?php

namespace Tests\Browser\Common;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;
use App\Models\Core\Dusks;

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

        // マニュアル用データ出力
        $dusk = Dusks::updateOrCreate(['html_path' => 'common/index/index/index.html'],[
            'category' => 'common',
            'sort' => 2,
            'plugin_name' => 'index',
            'plugin_title' => 'プラグイン',
            'plugin_desc' => 'Connect-CMSで共通的に使用する機能について説明します。',
            'method_name' => 'index',
            'method_title' => 'プラグイン追加',
            'method_desc' => 'プラグイン追加の方法を紹介します。',
            'method_detail' => 'プラグインの追加方法は、各プラグインで共通です。',
            'html_path' => 'common/index/index/index.html',
            'img_paths' => '[
                {"name": "common/index/index/images/add_plugin1", "img_methods": [
                    {"img_method": "trim_h", "args": [0,250]},
                    {"img_method": "arc", "args": [1670,75,200,50,10]}
                ]},
                {"name": "common/index/index/images/add_plugin2", "img_methods": [
                    {"img_method": "trim_h", "args": [0,400]},
                    {"img_method": "arc", "args": [960,130,200,50,10]}
                ]},
                {"name": "common/index/index/images/add_plugin3", "img_methods": [
                    {"img_method": "trim_h", "args": [0,600]},
                    {"img_method": "arc", "args": [960,230,200,40,10]}
                ]},
                {"name": "common/index/index/images/add_plugin4", "img_methods": [{"img_method": "trim_h", "args": [0,300]}]}
            ]',
            'test_result' => 'OK',
        ]);

        // 固定記事の編集
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
            // プラグインの（右上）歯車マーク押下
            $browser->visit('/')
                    ->click('#contents-' . $header_first_content_frame->id . '-edit-button')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('common/plugin/edit_content1');

            // $browser->driver->executeScript('tinyMCE.get(\'contents\').setContent(\'<h1>Test Description</h1>\')');
            $browser->driver->executeScript('tinyMCE.activeEditor.setContent(\'<h1>Test Description</h1>\')');
            $browser->screenshot('common/plugin/edit_content3');
            //$browser->wysiwyg('tinymce', '#contents-5-form', '<h2>value</h2>');
            //$browser->keys('#contents', 'Text');

            $browser->press('登録確定')
                    ->assertSee('Test Description')
                    ->screenshot('common/plugin/edit_content3');
        });
    }
}
