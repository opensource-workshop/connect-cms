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
class AdminLinkTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * 以下のエラーが出たので、とりあえず、関数名に 2 をつけてある。
     * Fatal error: Access level to Tests\Browser\Common\AdminLinkTest::addPlugin() must be public (as in class Tests\DuskTestCase) in C:\SitesLaravel\connect-cms\htdocs\conne
     * ct-cms\tests\Browser\Common\AdminLinkTest.php on line 19
     * PHP Fatal error:  Access level to Tests\Browser\Common\AdminLinkTest::addPlugin() must be public (as in class Tests\DuskTestCase) in C:\SitesLaravel\connect-cms\htdocs\
     * connect-cms\tests\Browser\Common\AdminLinkTest.php on line 19
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1);
        $this->adminLink();
        $this->addContentsPlugin();
    }

    /**
     * 管理機能
     */
    private function adminLink()
    {
        // 管理機能
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertTitleContains('Connect-CMS')
                    ->waitFor('#dropdown_manage')
                    ->screenshot('common/admin_link/index/images/admin_link1');

            $browser->click('#dropdown_manage')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('common/admin_link/index/images/admin_link2');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/admin_link/index/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'admin_link',
             'plugin_title' => '管理機能',
             'plugin_desc' => 'プラグイン追加や管理者メニューなど、サイト管理機能への入り口です。',
             'method_name' => 'index',
             'method_title' => '管理機能',
             'method_desc' => '管理機能メニューを開きます。',
             'method_detail' => '',
             'html_path' => 'common/admin_link/index/index.html',
             'img_args' => '[
                 {"path": "common/admin_link/index/images/admin_link1",
                  "name": "ログイン状態",
                  "methods": [
                     {"method": "trim_h", "args": [0,200]},
                     {"method": "arc", "args": [1080,30,120,50,6]}
                  ],
                  "comment": "<ul class=\"mb-0\"><li>権限のあるユーザでログインしている場合、管理機能のリンクが表示されます。</li></ul>"
                 },
                 {"path": "common/admin_link/index/images/admin_link2",
                  "name": "管理機能へのリンク",
                  "methods": [
                     {"method": "trim_h", "args": [0,300]},
                     {"method": "rectangle", "args": [950,50,1100,100]}
                  ],
                  "comment": "<ul class=\"mb-0\"><li>権限がある項目が表示されます。</li><li>プレビューモードは編集用のリンクなどが消えて、ゲストが見ている状態の画面を確認することができます。</li></ul>"
                 }]',
            'level' => 'basic',
            'test_result' => 'OK']
        );
    }

    /**
     * プラグイン追加
     */
    private function addContentsPlugin()
    {
        // 固定記事をプラグイン追加
        $this->addPluginModal(PluginName::getPluginName(PluginName::contents));

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/admin_link/plugin/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'admin_link',
             'plugin_title' => 'プラグイン',
             'plugin_desc' => 'Connect-CMSで共通的に使用する機能について説明します。',
             'method_name' => 'plugin',
             'method_title' => 'プラグイン追加',
             'method_desc' => 'プラグイン追加の方法を紹介します。',
             'method_detail' => 'プラグインの追加方法は、各プラグインで共通です。',
             'html_path' => 'common/admin_link/plugin/index.html',
             'img_args' => '[
                 {"path": "common/admin_link/plugin/images/add_plugin1",
                  "name": "プラグイン追加",
                  "methods": [
                     {"method": "trim_h", "args": [0,250]},
                     {"method": "arc", "args": [1080,30,120,50,6]}
                 ]},
                 {"path": "common/admin_link/plugin/images/add_plugin2",
                  "name": "ヘッダーに追加",
                  "methods": [
                     {"method": "trim_h", "args": [0,400]},
                     {"method": "arc", "args": [640,130,200,50,6]}
                 ]},
                 {"path": "common/admin_link/plugin/images/add_plugin3",
                  "name": "固定記事をクリック",
                  "methods": [
                     {"method": "trim_h", "args": [0,600]},
                     {"method": "arc", "args": [640,215,200,40,6]}
                 ]},
                 {"path": "common/admin_link/plugin/images/add_plugin4",
                  "name": "プラグインの追加完了",
                  "methods": [
                     {"method": "trim_h", "args": [0,300]}
                 ]}]',
             'level' => 'basic',
             'test_result' => 'OK']
        );

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

            // change: もっと見る対応で、固定記事編集画面のtinyMCEが2つになったため修正
            $browser->driver->executeScript('tinyMCE.get(0).setContent(\'<h1>Test Description</h1>\')');
            $browser->screenshot('common/plugin/edit_content2');
            //$browser->wysiwyg('tinymce', '#contents-5-form', '<h2>value</h2>');
            //$browser->keys('#contents', 'Text');

            $browser->press('登録確定')
                    ->assertSee('Test Description')
                    ->screenshot('common/plugin/edit_content3');
        });
    }
}
