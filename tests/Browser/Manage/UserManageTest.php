<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Core\Configs;

/**
 * > tests\bin\connect-cms-test.bat
 */
class UserManageTest extends DuskTestCase
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
        $this->originalRole('1', 'student', '学生');
        $this->saveOriginalRoles();
        $this->originalRole('2', 'teacher', '教員');
        $this->saveOriginalRoles();
        $this->originalRole();
        $this->regist();
        $this->register();
        $this->import();
        $this->submitImport();
        $this->autoRegist();
        $this->bulkDelete();
    }

    /**
     * index の表示
     */
    private function index()
    {
        // ユーザ一覧の表示
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/user')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/user/index/images/index');
        });

        // 絞り込み画面
        $this->browse(function (Browser $browser) {
            $browser->click('#user_search_condition')
                    ->assertTitleContains('Connect-CMS')
                    ->pause(500)
                    ->screenshot('manage/user/index/images/user_search_condition');
        });
        $this->browse(function (Browser $browser) {
            $browser->scrollIntoView('#user_search_condition_status');
            $browser->screenshot('manage/user/index/images/user_search_condition2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/user/index/images/index",
             "name": "ユーザ一覧",
             "comment": "<ul class=\"mb-0\"><li>ユーザの一覧が表示されます。</li><li>「グループ」列の編集ボタンから、「グループ参加」画面に飛べます。</li><li>「最終ログイン日時」列の履歴リンクから、「ログイン履歴」画面に飛べます</li></ul>"
            },
            {"path": "manage/user/index/images/user_search_condition",
             "name": "絞り込み画面"
            },
            {"path": "manage/user/index/images/user_search_condition2",
             "name": "絞り込み画面２",
             "comment": "<ul class=\"mb-0\"><li>様々な条件でユーザを絞り込むことができます。</li><li>絞り込んだ内容でダウンロードすることができます。</li></ul>"
            }
        ]', null, 3, 'basic');
    }

    /**
     * 役割設定画面
     */
    private function originalRole($add_additional1 = null, $add_name = null, $add_value = null)
    {
        // 値が渡ってくれば、テスト実行、値がなければマニュアル用の表示のみ
        if ($add_additional1) {
            $this->browse(function (Browser $browser) use ($add_additional1, $add_name, $add_value) {
                $browser->visit('/manage/user/originalRole')
                        ->type('add_additional1', $add_additional1)
                        ->type('add_name', $add_name)
                        ->type('add_value', $add_value)
                        ->assertPathIs('/manage/user/originalRole')
                        ->screenshot('manage/user/originalRole/images/originalRole' . $add_additional1);
            });
        } else {
            $this->browse(function (Browser $browser) {
                $browser->visit('/manage/user/originalRole')
                        ->assertPathIs('/manage/user/originalRole')
                        ->screenshot('manage/user/originalRole/images/originalRole');
            });

            // マニュアル用データ出力
            $this->putManualData('[
                {"path": "manage/user/originalRole/images/originalRole",
                 "name": "役割設定",
                 "comment": "<ul class=\"mb-0\"><li>ユーザに付与する役割を設定できます。</li><li>ここで設定する役割の意味は、各プラグインの機能に依存します。</li><li>例えばOpac（蔵書管理・蔵書貸し出し）プラグインでは、貸し出し日数を学生は〇日、教員は〇日、という設定が可能になっています。</li><li>課題管理プラグインで利用できる「定義名」は、 student, teacher です。</li></ul>"
                }
            ]', null, 3);
        }
    }

    /**
     * 役割設定追加処理
     */
    private function saveOriginalRoles()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('変更')
                    ->assertPathIs('/manage/user/originalRole')
                    ->screenshot('manage/user/saveOriginalRoles/images/saveOriginalRoles');
        });
    }

    /**
     * ユーザ登録画面
     */
    private function regist()
    {
        $this->browse(function (Browser $browser) {

            // 役割設定を取得して、学生にする。
            $original_role_student = Configs::where('category', 'original_role')->where('name', 'student')->first();

            $browser->visit('/manage/user/regist')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/user/regist/images/regist1');

            $browser->scrollIntoView('footer')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/user/regist/images/regist2');

            $browser->visit('/manage/user/regist')
                    ->type('name', 'テストユーザ')
                    ->type('userid', 'test-user')
                    ->type('email', 'test@osws.jp')
                    ->type('password', 'test-user')
                    ->type('password_confirmation', 'test-user')
                    ->click('#label_role_reporter')
                    ->click('#label_original_role' . $original_role_student->id)
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/user/regist/images/regist3');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/user/regist/images/regist1",
             "name": "ユーザ登録１"
            },
            {"path": "manage/user/regist/images/regist2",
             "name": "ユーザ登録２",
             "comment": "<ul class=\"mb-0\"><li>ユーザを登録・変更することができます。また、編集画面ではユーザの削除もできます。</li><li>権限については、権限・役割のページで説明します。</li><li>ユーザ情報に任意項目を追加できます。<ul class=\"mb-0\"><li>任意項目を追加・編集する画面はありません。今後追加する予定です。</li><li>直接DBにデータ投入を行う事で任意項目を追加できます。詳しくはGithub wikiのUserページを参照してください。<br /><a href=\"https://github.com/opensource-workshop/connect-cms/wiki/User\" target=\"_blank\" rel=\"noopener\" class=\"cc-icon-external\">https://github.com/opensource-workshop/connect-cms/wiki/User</a></li></ul></li></ul>"
            }
        ]', null, 3);
    }

    /**
     * ユーザ登録処理
     */
    private function register()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('ユーザ登録')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/user/register/images/register');
        });
    }

    /**
     * CSVインポート処理
     */
    private function import()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/user/import')
                    ->attach('users_csv', __DIR__.'/users.csv')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/user/import/images/import');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/user/import/images/import",
             "name": "CSVインポート",
             "comment": "<ul class=\"mb-0\"><li>CSVファイルを使って、ユーザを一括登録できます。</li><li>「id」に値がある行はユーザ更新します。</li><li>「id」が空の行はユーザを登録します。</li><li>１つの項目に複数値を登録する場合は、|（パイプ）文字で区切ってCSVに登録します。<br />例えば、「権限」「グループ」「役割設定」項目が対象です。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * CSVインポート処理
     */
    private function submitImport()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/user/import')
                    ->attach('users_csv', __DIR__.'/users.csv')
                    ->press('インポート')
                    ->acceptDialog()
                    ->pause(500)
                    ->assertDontSee('500')        // "500" 文字がない事
                    ->screenshot('manage/user/import/images/submitImport');
        });
    }

    /**
     * index の2ページ目表示
     */
    private function indexPage2()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/user?page=2')
                ->assertSee('ユーザ一覧')
                ->pause(500)
                ->assertDontSee('500')        // "500" 文字がない事
                ->screenshot('manage/user/import/images/import2');
        });
    }

    /**
     * 自動ユーザ登録設定
     */
    private function autoRegist()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/user/autoRegist')
                ->assertTitleContains('Connect-CMS')
                ->screenshot('manage/user/autoRegist/images/autoRegist1')
                ->scrollIntoView('#user_register_temporary_regist_mail_flag')
                ->screenshot('manage/user/autoRegist/images/autoRegist2')
                ->scrollIntoView('#div_user_register_mail_subject')
                ->screenshot('manage/user/autoRegist/images/autoRegist3')
                ->scrollIntoView('#div_user_register_approved_mail_subject')
                ->screenshot('manage/user/autoRegist/images/autoRegist4')
                ->scrollIntoView('#div_user_register_requre_privacy')
                ->screenshot('manage/user/autoRegist/images/autoRegist5');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/user/autoRegist/images/autoRegist1",
             "name": "自動ユーザ登録",
             "comment": "<ul class=\"mb-0\"><li>自動ユーザ登録の使用の許可、自動ユーザ登録時の管理者の承認の要不要、自動ユーザ登録時の通知先メールアドレスを設定できます。</li></ul>"
            },
            {"path": "manage/user/autoRegist/images/autoRegist2",
             "name": "仮登録メール",
             "comment": "<ul class=\"mb-0\"><li>仮登録機能を使用することができます。仮登録機能とは、自動ユーザ登録時に、ユーザ自身がメールアドレスをクリックして、本登録に進む機能です。</li></ul>"
            },
            {"path": "manage/user/autoRegist/images/autoRegist3",
             "name": "本登録メール",
             "comment": "<ul class=\"mb-0\"><li>ユーザが登録できた際に送信するメールのフォーマットを設定できます。承認が必要な場合は登録申請メールになります。</li></ul>"
            },
            {"path": "manage/user/autoRegist/images/autoRegist4",
             "name": "承認完了メール",
             "comment": "<ul class=\"mb-0\"><li>ユーザが承認された際に送信するメールのフォーマットを設定できます。</li></ul>"
            },
            {"path": "manage/user/autoRegist/images/autoRegist5",
             "name": "個人情報保護への同意や追記文章、初期コンテンツ権限",
             "comment": "<ul class=\"mb-0\"><li>ユーザ登録する際に規約など文章に同意を求めることができます。また、登録時のコンテンツ権限も設定できます。</li></ul>"
            }
        ]', null, 3);
    }

    /**
     * 一括削除
     */
    private function bulkDelete()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/user/bulkDelete')
                ->assertTitleContains('Connect-CMS')
                ->screenshot('manage/user/bulkDelete/images/bulkDelete');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/user/bulkDelete/images/bulkDelete",
             "name": "一括削除",
             "comment": "<ul class=\"mb-0\"><li>状態が「仮削除」のユーザを一括削除します。</li><li>削除対象ユーザは、[ ユーザ一覧 ] の絞り込み条件で状態「仮削除」で絞り込む事で確認できます。</li><li>ユーザを「仮削除」に一括更新したい場合は、[ CSVインポート ] で状態を 3 (仮削除) に更新してください。<br />１人づつ変更するのであれば、ユーザ変更画面から状態を「仮削除」に変更できます。</li></ul>"
            }
        ]', null, 3);
    }
}
