<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * > tests\bin\connect-cms-test.bat
 */
class ApiManageTest extends DuskTestCase
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
        $this->update();
    }

    /**
     * Secret Code の入力
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/api')
                    ->type('secret_name', 'テスト')
                    ->type('secret_code', 'secret_1234')
                    ->type('ip_address', '192.168.10.101')
                    ->click('#label_apis_Opac')
                    ->click('#label_apis_User')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/api/index/images/index');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/api/index/images/index",
             "name": "Secret Code 一覧",
             "comment": "<ul class=\"mb-0\"><li>Connect-CMSでは、APIでの通信機能があります。</li><li>API管理では、秘密コードを設定し、秘密コード単位にIPアドレスでの制限や使用APIを選択することができます。</li><li>API毎の設定などは、マニュアルのAPIページを参照してください。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * Secret Code 登録
     */
    private function update()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/api/index/images/update');
        });
    }
}
