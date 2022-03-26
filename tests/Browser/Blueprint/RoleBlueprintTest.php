<?php

namespace Tests\Browser\Blueprint;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Dusks;

use App\Enums\PluginName;

/**
 * 権限・テストクラス
 */
class RoleBlueprintTest extends DuskTestCase
{
    private $dusk_index = null;

    /**
     * テストする関数の制御
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function test()
    {
        $this->init();
        $this->login(1);
        $this->roleList();
        $this->editBucketsRoles();
        $this->logout();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // マニュアルデータの削除
        Dusks::where('category', 'blueprint')->where('plugin_name', 'role')->delete();

        // ブログ準備
        // 順番に実行されれば、ブログプラグインはできているため、初期化の必要はない。
        // $this->initPlugin('blogs', '/test/blog');
    }

    /**
     * 権限の種類
     */
    private function roleList()
    {
        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertPathBeginsWith('/');
        });

        // マニュアル用データ出力（権限）
        $this->dusk_index = Dusks::create([
            'category' => 'blueprint',
            'sort' => 2,
            'plugin_name' => 'role',
            'plugin_title' => '権限',
            'plugin_desc' => 'Connect-CMSの権限について説明します。',
            'method_name' => 'index',
            'method_title' => '権限の種類',
            'method_desc' => '権限とは、管理機能やプラグインの配置ができる権限や記事の投稿ができる権限などのように、各操作を許可するものを指します。',
            'method_detail' => '',
            'html_path' => 'blueprint/role/index/index.html',
            'test_result' => 'OK',
        ]);
    }

    /**
     * プラグインの権限設定
     */
    private function editBucketsRoles()
    {
        // ブラウザ起動
        $this->browse(function (Browser $browser) {
            // ブログページの情報取得
            $blog_page = Page::where('permanent_link', '/test/blog')->first();
            if (is_null($blog_page)) {
                // テスト実行順により /test/blog がまだ無い場合は作成して取得
                $this->initPlugin('blogs', '/test/blog');
                $blog_page = Page::where('permanent_link', '/test/blog')->first();
            }

            $blog_frame = Frame::where('page_id', $blog_page->id)->where('plugin_name', 'blogs')->first();

            $browser->visit('/plugin/blogs/editBucketsRoles/' . $blog_page->id . '/' . $blog_frame->id . '#frame-' . $blog_frame->id)
                    ->screenshot('blueprint/role/editBucketsRoles/images/editBucketsRoles');
        });

        // マニュアル用データ出力（権限）
        $dusk_index = Dusks::create([
            'category' => 'blueprint',
            'sort' => 2,
            'plugin_name' => 'role',
            'plugin_title' => '権限',
            'plugin_desc' => 'Connect-CMSの権限について説明します。',
            'method_name' => 'editBucketsRoles',
            'method_title' => 'プラグインの権限設定',
            'method_desc' => '各プラグインでは、モデレータと編集者に記事投稿と承認有無を設定できます。',
            'method_detail' => 'プラグインによっては権限設定のないものや、承認機能がないものもあります。',
            'html_path' => 'blueprint/role/editBucketsRoles/index.html',
            'img_args' => 'blueprint/role/editBucketsRoles/images/editBucketsRoles',
            'test_result' => 'OK',
            'parent_id' => $this->dusk_index->id,
        ]);
    }
}
