<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;

use Gate;

//use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;

//class AppServiceProvider extends ServiceProvider
class AppServiceProvider extends AuthServiceProvider
{
    /**
     * ユーザーが指定された権限を保持しているかチェックする。
     *
     * @return boolean
     */
    public function check_authority($user, $authority)
    {
        // ログインしていない場合は権限なし
        if (empty($user)) {
            return false;
        }

        // 指定された権限を含むロールをループする。
        foreach (config('cc_role.CC_AUTHORITY')[$authority] as $role) {
            // ユーザの保持しているロールをループ
            foreach ($user['user_rolses'] as $target) {
                // ターゲット処理をループ
                foreach ($target as $user_role => $user_role_value) {
                    // 必要なロールを保持している場合は、権限ありとして true を返す。
                    if ($role == $user_role && $user_role_value) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * ユーザーが指定された役割を保持しているかチェックする。
     *
     * @return boolean
     */
    public function check_role($user, $role)
    {
        // ログインしていない場合は権限なし
        if (empty($user)) {
            return false;
        }

        // ユーザの保持しているロールをループ
        foreach ($user['user_rolses'] as $target) {
            // ターゲット処理をループ
            foreach ($target as $user_role => $user_role_value) {
                // 必要なロールを保持している場合は、権限ありとして true を返す。
                if ($role == $user_role && $user_role_value) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 認可サービス(Gate)利用の準備
        $this->registerPolicies();

        // *** ロールから確認（一般）

        // 記事追加
        Gate::define('role_reporter', function ($user, $plugin_name = null, $post = null) {
            return $this->check_role($user, 'role_reporter');
        });

        // プラグイン配置
        Gate::define('role_arrangement', function ($user, $plugin_name = null, $post = null) {
            return $this->check_role($user, 'role_arrangement');
        });

        // 承認
        Gate::define('role_approval', function ($user, $plugin_name = null, $post = null) {
            return $this->check_role($user, 'role_approval');
        });

        // 記事修正（モデレータ）
        Gate::define('role_article', function ($user, $plugin_name = null, $post = null) {
            return $this->check_role($user, 'role_article');
        });

        // 記事管理者
        Gate::define('role_article_admin', function ($user, $plugin_name = null, $post = null) {
            return $this->check_role($user, 'role_article_admin');
        });

        // *** ロールから確認（管理）

        // ページ管理
        Gate::define('admin_page', function ($user, $plugin_name = null, $post = null) {
            return $this->check_role($user, 'admin_page');
        });

        // サイト管理
        Gate::define('admin_site', function ($user, $plugin_name = null, $post = null) {
            return $this->check_role($user, 'admin_site');
        });

        // ユーザー管理
        Gate::define('admin_user', function ($user, $plugin_name = null, $post = null) {
            return $this->check_role($user, 'admin_user');
        });

        // システム管理
        Gate::define('admin_system', function ($user, $plugin_name = null, $post = null) {
            return $this->check_role($user, 'admin_system');
        });


        // *** 記事の権限から確認

        // 記事追加
        Gate::define('posts.create', function ($user, $plugin_name = null, $post = null) {
            return $this->check_authority($user, 'posts.create');
        });

        // 記事変更
//        Gate::define('posts.update', function ($user, $plugin_name = null, $post = null) {
        Gate::define('posts.update', function ($user, $args = null) {

            $post = ($args != null) ? $args[0] : null;
            $plugin_name = ($args != null && is_array($args) && count($args) > 1) ? $args[1] : null;

            if ( !$this->check_authority($user, 'posts.update') ) {
                return false;
            }
            if ( empty($post) ) {
                return true;
            }
            else {
                if ( $user->id == $post->created_id ) {
                    return true;
                }
            }
            return false;
        });

        // 記事削除
        Gate::define('posts.delete', function ($user, $plugin_name = null, $post = null) {
            return $this->check_authority($user, 'posts.delete');
        });

        // 記事承認
        Gate::define('posts.approval', function ($user, $plugin_name = null, $post = null) {
            return $this->check_authority($user, 'posts.approval');
        });

        // *** システム権限から確認

        // システム管理者権限の有無確認
        Gate::define(config('cc_role.ROLE_SYSTEM_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_SYSTEM_MANAGER')) {
                return true;
            }
            return false;
        });

        // サイト管理者権限の有無確認
        Gate::define(config('cc_role.ROLE_SITE_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_SITE_MANAGER')) {
                return true;
            }
            return false;
        });

        // ユーザ管理者権限の有無確認
        Gate::define(config('cc_role.ROLE_USER_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_USER_MANAGER')) {
                return true;
            }
            return false;
        });

        // ページ管理者権限の有無確認
        Gate::define(config('cc_role.ROLE_PAGE_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_PAGE_MANAGER')) {
                return true;
            }
            return false;
        });

        // 運用管理者権限の有無確認
        Gate::define(config('cc_role.ROLE_OPERATION_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_OPERATION_MANAGER')) {
                return true;
            }
            return false;
        });

        return false;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
