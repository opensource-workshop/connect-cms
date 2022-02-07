<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * > tests\bin\connect-cms-test.bat
 */
class AuthManageTest extends DuskTestCase
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
        $this->auth();
        $this->update();
        $this->ldap();
        $this->shibboleth();
        $this->netcommons2();
        $this->netcommons2Update();
    }

    /**
     * 認証設定の表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/auth')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/auth/index/images/index');
        });

        $this->browse(function (Browser $browser) {
            $browser->scrollIntoView('footer')
                    ->screenshot('manage/auth/index/images/index2');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/auth/index/images/index",
             "name": "認証設定"
            },
            {"path": "manage/auth/index/images/index2",
             "name": "認証設定２",
             "comment": "<ul class=\"mb-0\"><li>外部認証に加えて、通常ログインも使用できる設定があります。</li><li>LDAP認証またはNetCommons2認証で通常ログインも「使用する」場合、外部認証でログインできなかったら、連続して通常ログインを行います。</li><li>Shibboleth認証で通常ログインも「使用する」場合、ログインURL を直接入力して通常ログインを行います。</li></ul>"
            }
        ]');
    }

    /**
     * 認証設定の入力
     */
    private function auth()
    {
        $this->browse(function (Browser $browser) {
            $browser->click('#label_use_auth_method_1')
                    ->check('confirm_auth')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/auth/index/images/auth');
        });
    }

    /**
     * 認証設定の更新
     */
    private function update()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/auth/index/images/update');
        });
    }

    /**
     * LDAP認証の表示
     */
    private function ldap()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/auth/ldap')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/auth/ldap/images/ldap');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/auth/ldap/images/ldap",
             "name": "LDAP認証",
             "comment": "<ul class=\"mb-0\"><li>phpモジュールの <span style=\"color:#e83e8c;\">php_ldap</span> を使い、LDAP認証を行います。</li><li><span style=\"color:#e83e8c;\">php_ldap</span> が有効かどうか、当画面で確認できます。</li><li>Ldap形式のDN（例：<span style=\"color:#e83e8c;\">uid=test001,ou=People,dc=example,dc=com</span>）とActive Directory形式のDN（例：<span style=\"color:#e83e8c;\">testuser001@example.com</span>）に対応しています。</li></ul>"
            }
        ]');
    }

    /**
     * Shibboleth認証の表示
     */
    private function shibboleth()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/auth/shibboleth')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/auth/shibboleth/images/shibboleth');
        });

        // マニュアル用データ出力
        $this->putManualData('[
            {"path": "manage/auth/shibboleth/images/shibboleth",
             "name": "Shibboleth認証",
             "comment": "<ul class=\"mb-0\"><li>Apacheモジュールの <span style=\"color:#e83e8c;\">mod_shib</span> を使い、Shibboleth認証します。</li><li>Shibboleth認証の設定を確認できます。</li><li>Shibboleth認証の設定は、設定ファイル <span style=\"color:#e83e8c;\">config/cc_shibboleth_config.sample.php</span> をコピーして <span style=\"color:#e83e8c;\">config/cc_shibboleth_config.php</span> を作成して設定します。</li></ul>"
            }
        ]');
    }

    /**
     * NetCommons2認証の表示
     */
    private function netcommons2()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/auth/netcommons2')
                    ->type('auth_netcomons2_site_url', 'http://nc2.localhost')
                    ->type('auth_netcomons2_site_key', 'key_1234')
                    ->type('auth_netcomons2_salt', 'salt_1234')
                    ->type('auth_netcomons2_add_role', 'original_role:student')
                    ->type('auth_netcomons2_admin_password', 'admin_password_1234')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/auth/netcommons2/images/netcommons2');
        });
    }

    /**
     * NetCommons2認証の更新
     */
    private function netcommons2Update()
    {
        $this->browse(function (Browser $browser) {
            $browser->press('更新')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/auth/netcommons2/images/netcommons2Update');
        });
    }
}
