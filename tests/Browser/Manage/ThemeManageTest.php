<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Uploads;

class ThemeManageTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1); // user id = 1(admin)でログイン
        $this->index();
        //$this->generateIndex();
    }

    /**
     * テーマ一覧
     */
    private function index()
    {
        // テスト用テーマがなければコピーする。
        // Laravel Fileクラスのexistsはディレクトリでも判定できました。
        if (!\Storage::disk('public_real')->exists('themes/Users/theme1')) {
            $files = \Storage::disk('manual')->allFiles('copy_data/theme1');
            foreach ($files as $file) {
                if (!\Storage::disk('public_real')->exists('/themes/Users/' . str_replace('copy_data', '', dirname($file)))) {
                    \Storage::disk('public_real')->makeDirectory('/themes/Users/' . str_replace('copy_data', '', dirname($file)));
                }
                \Storage::disk('public_real')->put('/themes/Users/' . str_replace('copy_data', '', $file), \Storage::disk('manual')->get($file));
            }
        }

        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/theme')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/theme/index/images/index');

            $browser->click('#css_edit_1')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/theme/index/images/css_edit');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/theme/index/images/index",
             "name": "アップロードファイル一覧",
             "comment": "<ul class=\"mb-0\"><li>アップロードファイルを一覧表示できます。</li></ul>"
            },
            {"path": "manage/theme/index/images/css_edit",
             "name": "アップロードファイル編集",
             "comment": "<ul class=\"mb-0\"><li>ファイル名の変更が可能です。</li></ul>"
            }
        ]');
    }

    /**
     * ユーザファイル
     */
    private function userdir()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/uploadfile/userdir')
                    ->assertTitle('Connect-CMS')
                    ->screenshot('manage/uploadfile/userdir/images/userdir');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/uploadfile/userdir/images/userdir",
             "name": "ユーザディレクトリ一覧",
             "comment": "<ul class=\"mb-0\"><li>サーバ上のファイルを操作できる環境用の設定です。</li><li>storage/user 配下のディレクトリをConnect-CMS で閲覧制御できます。</li><li>ファイル参照時は /file/user/ディレクトリ名/ファイル名 となります。</li></ul>"
            }
        ]');
    }
}
