<?php

namespace Tests\Browser\Common;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\Dusks;
use App\Models\User\Contents\Contents;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * パスワード付きページテスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class PasswordPageTest extends DuskTestCase
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
        $this->setPassword();
        $this->logout();
        $this->viewPage();
    }

    /**
     * ログイン
     */
    private function setPassword()
    {
        // パスワード設定
        $this->browse(function (Browser $browser) {
            $page = $this->firstOrCreatePage('/password');

            $browser->visit('/manage/page/edit/' . $page->id)
                    ->assertTitleContains('Connect-CMS')
                    ->type('password', 'pass123')
                    ->screenshot('common/password_page/index/images/setPassword')
                    ->scrollIntoView('footer')
                    ->press('ページ更新')
                    ->pause(500);    // github actionsの安定性のためにpress後に少し待つ
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/password_page/index/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'password_page',
             'plugin_title' => 'パスワード付きページ',
             'plugin_desc' => 'パスワード付きページを作成できます。',
             'method_name' => 'index',
             'method_title' => 'パスワード設定',
             'method_desc' => 'パスワードはページ管理のページ編集で入力することができます。',
             'method_detail' => '',
             'html_path' => 'common/password_page/index/index.html',
             'img_args' => '[
                 {"path": "common/password_page/index/images/setPassword",
                  "name": "パスワード設定"
                 }
             ]',
             'level' => 'basic',
             'test_result' => 'OK']
        );
    }

    /**
     * パスワードページの表示
     */
    private function viewPage()
    {
        // *** ログアウト状態でパスワードページ＞パスワード入力＞ページの閲覧
        $this->browse(function (Browser $browser) {
            $browser->visit('/password')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('common/password_page/viewPage/images/viewPage1')
                    ->type('password', 'pass123')
                    ->screenshot('common/password_page/viewPage/images/inputPassword')
                    ->press('ページ閲覧');
        });

        // *** ログインして固定記事を作成
        // ※ $this->browse()内で$this->login(), $this->logout() はなるべく使わない。$this->login(), $this->logout() は内部で$this->browse()を使っているため、入れ子呼び出しになり、ログインできたり・できなかったりする事あり（github actions+php8.1等）
        $this->login(1);
        $this->browse(function (Browser $browser) {
            // データクリア
            $page = Page::where('permanent_link', '/password')->first();
            $frame = Frame::where('page_id', $page->id)->where('plugin_name', 'contents')->first();
            if (!empty($frame)) {
                $bucket = Buckets::find($frame->bucket_id);
                if (!empty($bucket)) {
                    Contents::where('bucket_id', $bucket->id)->forceDelete();
                    Buckets::find($bucket->id)->forceDelete();
                }
                $frame->forceDelete();
            }
        });

        // 固定記事を作成
        // ※ $this->browse() 入れ子対応。下記メソッドはなるべく$this->browse()内で使わない
        $this->addPluginModal('contents', '/password', 2, false);

        $this->browse(function (Browser $browser) {
            $bucket = Buckets::create(['bucket_name' => 'パスワード付きページテスト', 'plugin_name' => 'contents']);

            // 初めは記事は文字のみ。
            $content = Contents::create(['bucket_id' => $bucket->id, 'content_text' => '<p>パスワード付きページのテストです。</p>', 'status' => 0]);

            $frame = Frame::orderBy('id', 'desc')->first();
            $frame->update(['bucket_id' => $bucket->id]);
        });
        // パスワード入力済みセッションをクリアさせないため、ログアウトしない
        // $this->logout();

        // *** パスワード入力済みのため、パスワード付きページの閲覧できる（固定記事あり）
        $this->browse(function (Browser $browser) {
            $browser->visit('/password')
                    ->screenshot('common/password_page/viewPage/images/viewPage2');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'common/password_page/viewPage/index.html'],
            ['category' => 'common',
             'sort' => 2,
             'plugin_name' => 'password_page',
             'plugin_title' => 'パスワード付きページ',
             'plugin_desc' => 'パスワード付きページを作成できます。',
             'method_name' => 'viewPage',
             'method_title' => 'ページ閲覧',
             'method_desc' => 'パスワード付きページを閲覧します。',
             'method_detail' => '',
             'html_path' => 'common/password_page/viewPage/index.html',
             'img_args' => '[
                 {"path": "common/password_page/viewPage/images/viewPage1",
                  "name": "パスワード要求",
                  "comment": "<ul class=\"mb-0\"><li>パスワード付きページを開くと、パスワードが要求されます。</li></ul>"
                 },
                 {"path": "common/password_page/viewPage/images/inputPassword",
                  "name": "パスワード入力"
                 },
                 {"path": "common/password_page/viewPage/images/viewPage2",
                  "name": "パスワード付きページの閲覧",
                  "comment": "<ul class=\"mb-0\"><li>パスワード付きページを閲覧できます。</li></ul>"
                 }
             ]',
             'level' => 'basic',
             'test_result' => 'OK']
        );
    }
}
