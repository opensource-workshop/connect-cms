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
        $this->editCss();
        $this->editJs();
        $this->listImages();
        $this->editName();
        $this->generateIndex();
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

            $browser->click('#image_edit_1')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/theme/index/images/image_edit');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/theme/index/images/index",
             "name": "ユーザ・テーマ一覧",
             "comment": "<ul class=\"mb-0\"><li>ユーザ・テーマを一覧表示できます。</li></ul>"
            }
        ]');
    }

    /**
     * ユーザ・テーマのCSS編集
     */
    private function editCss()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/theme')
                    ->click('#css_edit_1')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/theme/editCss/images/editCss');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/theme/editCss/images/editCss",
             "name": "ユーザ・テーマのCSS編集",
             "comment": "<ul class=\"mb-0\"><li>ユーザ・テーマ毎のCSSを編集できます。</li></ul>"
            }
        ]');
    }

    /**
     * ユーザ・テーマのJavaScript編集
     */
    private function editJs()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/theme')
                    ->click('#js_edit_1')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/theme/editJs/images/editJs');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/theme/editJs/images/editJs",
             "name": "ユーザ・テーマのJavaScript編集",
             "comment": "<ul class=\"mb-0\"><li>ユーザ・テーマ毎のJavaScriptを編集できます。</li></ul>"
            }
        ]');
    }

    /**
     * ユーザ・テーマの画像管理
     */
    private function listImages()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/theme')
                    ->click('#image_edit_1')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/theme/listImages/images/listImages');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/theme/listImages/images/listImages",
             "name": "ユーザ・テーマの画像管理",
             "comment": "<ul class=\"mb-0\"><li>ユーザ・テーマ毎の画像を追加・削除できます。</li></ul>"
            }
        ]');
    }

    /**
     * ユーザ・テーマの名前管理
     */
    private function editName()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/theme')
                    ->click('#name_edit_1')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/theme/editName/images/editName');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/theme/editName/images/editName",
             "name": "ユーザ・テーマの名前の編集",
             "comment": "<ul class=\"mb-0\"><li>ユーザ・テーマの名前を変更できます。</li></ul>"
            }
        ]');
    }

    /**
     * カスタムテーマ生成
     */
    private function generateIndex()
    {
        // 実行
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/theme/generateIndex')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/theme/generateIndex/images/generateIndex');

            $browser->scrollIntoView('footer')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/theme/generateIndex/images/generateIndex2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/theme/generateIndex/images/generateIndex",
             "name": "カスタムテーマ生成"
            },
            {"path": "manage/theme/generateIndex/images/generateIndex2",
             "name": "カスタムテーマ生成２",
             "comment": "<ul class=\"mb-0\"><li>ディレクトリ名とテーマ名を決めて、テーマを生成します。</li><li>テーマセットから選ぶことで、楽にテーマを作ることができます。</li><li>メニューの形式や書体をそれぞれ選択して、テーマを生成することもできます。</li></ul>"
            }
        ]');
    }
}
