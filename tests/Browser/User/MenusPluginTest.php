<?php

namespace Tests\Browser\User;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\PluginName;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Dusks;

class MenusPluginTest extends DuskTestCase
{
    /**
     * テンプレート一覧
     */
    private $templates = [
       'default' => 'default',
       'opencurrenttree' => 'ディレクトリ展開式',
       'opencurrenttree_for_design' => 'ディレクトリ展開式 デザイン用',
       'tab' => 'タブ',
       'dropdown' => 'ドロップダウン',
       'mouseover_dropdown' => 'マウスオーバードロップダウン',
       'mouseover_dropdown_no_root' => 'マウスオーバードロップダウン（ルートなし）',
       'mouseover_dropdown_no_rootlink' => 'マウスオーバードロップダウン（ルートのリンクなし）',
       'mouseover_dropdown_no_rootlink_for_design' => 'マウスオーバードロップダウン（ルートのリンクなし）デザイン用',
       'mouseover_dropdown_no_rootlink_for_icon' => 'マウスオーバードロップダウン（ルートのリンクなし）アイコン用',
       'breadcrumbs' => 'パンくず',
       'sitemap' => 'サイトマップ',
       'sitemap_no_rootlink' => 'サイトマップ（ルートのリンクなし）',
       'footersitemap' => 'フッター用サイトマップ',
       'footersitemap_no_rootrink' => 'フッター用サイトマップ（ルートのリンクなし）',
       'parentsandchild' => '親子のみ',
       'tab_flat' => 'タブフラット',
       'ancestor_descendant_sibling' => '親子兄弟'
    ];

    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->index();
        $this->login(1);
        $this->select();
        $this->setTemplate();
        $this->logout();
        $this->index();
        $this->screenshotTemplate();
    }

    /**
     * インデックス
     */
    private function index()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/menus/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('user/menus/index/images/index');
    }

    /**
     * ページ選択
     */
    private function select()
    {
        // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('menus', '/', 1);
        $this->test_frame->frame_title = null;
        $this->test_frame->frame_design = 'none';
        $this->test_frame->save();

        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/menus/select/' . $this->test_frame->page_id . '/' . $this->test_frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/menus/select/images/select');

            $browser->scrollIntoView('footer')
                    ->screenshot('user/menus/select/images/select2');
        });

        // マニュアル用データ出力
        $this->putManualData('user/menus/select/images/select,user/menus/select/images/select2');
    }

    /**
     * テンプレート
     */
    private function setTemplate()
    {
        foreach ($this->templates as $template_name => $template_desc) {
            $this->setTemplateImpl($template_name, $template_desc);
        }
    }

    /**
     * テンプレート
     */
    private function setTemplateImpl($template_name, $template_desc)
    {
        $page = Page::where('permanent_link', '/test/menu')->first();
        $frame = Frame::where('page_id', $page->id)->where('template', $template_name)->first();

        if (empty($frame)) {
            $this->addPlugin('menus', '/test/menu', 2);
            $this->test_frame->frame_title = $template_desc;
            $this->test_frame->template = $template_name;
            $this->test_frame->save();
        }
    }

    /**
     * テンプレート
     */
    private function screenshotTemplate()
    {
        foreach ($this->templates as $template_name => $template_desc) {
            $this->screenshotTemplateImpl($template_name, $template_desc);
        }

        // テンプレートのスクリーンショット
        $img_args = "";
        foreach ($this->templates as $template_name => $template_desc) {
            $img_args .=<<< EOF
{"path": "user/menus/template/images/{$template_name}",
 "name": "{$template_desc}",
 "comment": "<ul class=\"mb-0\"><li>{$template_desc}</li></ul>"
}
EOF;
            if (array_key_last($this->templates) != $template_name) {
                $img_args .= ",";
            }
        }

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'user/menus/template/index.html'],
            ['category' => 'user',
             'sort' => 2,
             'plugin_name' => 'menus',
             'plugin_title' => 'メニュー',
             'plugin_desc' => '',
             'method_name' => 'template',
             'method_title' => 'テンプレート',
             'method_desc' => 'メニュープラグインで選択できるテンプレートを紹介します。',
             'method_detail' => '',
             'html_path' => 'user/menus/template/index.html',
             'img_args' => '[' . $img_args . ']',
             'test_result' => 'OK'
            ]
        );
    }

    /**
     * テンプレート
     */
    private function screenshotTemplateImpl($template_name, $template_desc)
    {
        $page = Page::where('permanent_link', '/test/menu')->first();
        $frame = Frame::where('page_id', $page->id)->where('template', $template_name)->first();

        $this->browse(function (Browser $browser) use ($frame, $template_name) {
            $browser->visit('/test/menu#frame-' . $frame->id)
                    ->assertPathBeginsWith('/')
                    ->screenshot('user/menus/template/images/' . $template_name);
        });
    }
}
