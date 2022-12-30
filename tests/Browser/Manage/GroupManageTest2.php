<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Core\Dusks;

/**
 * > tests\bin\connect-cms-test.bat
 */
class GroupManageTest2 extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1);
        $this->index();
        $this->edit2(1);
    }

    /**
     * index の表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/group')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/group/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/group/index/images/index",
             "name": "グループ一覧",
             "comment": "<ul class=\"mb-0\"><li>登録されているグループの一覧です。</li><li>編集アイコンで登録済みのユーザ一覧画面に遷移します。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * グループ変更画面
     */
    private function edit2($id)
    {
        $this->browse(function (Browser $browser) use ($id) {
            $browser->visit('/manage/group/edit/' . $id)
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/group/edit2/images/edit2');
        });

        // マニュアル用データ出力
        $dusk = Dusks::putManualData(
            ['html_path' => 'manage/group/edit2/index.html'],
            ['category' => 'manage',
             'sort' => 3,
             'plugin_name' => 'group',
             'plugin_title' => 'グループ管理',
             'plugin_desc' => 'ユーザをグループとして設定できます。<br />このグループにページ毎の権限を付与することができます。',
             'method_name' => 'edit2',
             'method_title' => 'グループ変更',
             'method_desc' => 'グループ名の変更及び、参加ユーザを一覧で確認できます。',
             'method_detail' => '',
             'html_path' => 'manage/group/edit2/index.html',
             'img_args' => '[
                {"path": "manage/group/edit2/images/edit2",
                 "name": "グループ変更",
                 "comment": "<ul class=\"mb-0\"><li>ユーザのグループ追加後の画面です。</li><li>登録済みのユーザが一覧表示され、グループからユーザの削除もできます。</li></ul>"
                }
             ]',
            'level' => null,
            'test_result' => 'OK']
        );
    }
}
