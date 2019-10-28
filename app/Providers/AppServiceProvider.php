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
    public function check_authority($user, $authority, $args = null)
    {
        // preview モードのチェック付きの場合はpreview モードなら権限ナシで返す。
        $request = app(\Illuminate\Http\Request::class);

        // 引数をバラシてPOST を取得
        list($post, $plugin_name, $mode_switch) = $this->check_args_obj($args);

        // モードスイッチがプレビューなら表示しないになっていれば、権限ナシで返す。
        if ($mode_switch == 'preview_off' && $request->mode == 'preview') {
            return false;
        }

        // プレビュー判断はココまで
        if ($authority == 'preview') {
            return true;
        }

        // ログインしていない場合は権限なし
        if (empty($user)) {
            return false;
        }

        // 指定された権限を含むロールをループする。
        foreach (config('cc_role.CC_AUTHORITY')[$authority] as $role) {

            // ユーザの保持しているロールをループ
            foreach ($user['user_roles'] as $target) {

                // ターゲット処理をループ
                foreach ($target as $user_role => $user_role_value) {

                    // 必要なロールを保持している
                    if ($role == $user_role && $user_role_value) {

                        // 他者の記事を更新できる権限の場合は、記事作成者のチェックは不要
                        if (($user_role == 'role_article_admin') ||
                            ($user_role == 'role_approval')) {
                            return true;
                        }

                        // 自分のオブジェクトチェックが必要ならチェックする
                        if (empty($post)) {
                            return true;
                        }
                        else {
                            if ((($authority == 'buckets.delete') ||
                                 ($authority == 'posts.create') ||
                                 ($authority == 'posts.update') ||
                                 ($authority == 'posts.delete')) &&
                                ($user->id == $post->created_id)) {
                                return true;
                            }
                            else {
                                // 複数ロールをチェックするため、ここではreturn しない。
                                // return false;
                            }
                        }
                        // 複数ロールをチェックするため、ここではreturn しない。
                        // return true;
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

        // 指定された権限を含むロールをループする。
        // 記事追加は記事管理者でもOKのような処理のため。
        foreach (config('cc_role.CC_ROLE_HIERARCHY')[$role] as $checck_role) {

            // ユーザの保持しているロールをループ
            foreach ($user['user_roles'] as $target) {

                // ターゲット処理をループ
                foreach ($target as $user_role => $user_role_value) {

                    // 必要なロールを保持している場合は、権限ありとして true を返す。
                    if ($checck_role == $user_role && $user_role_value) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * POST、プラグイン名の引数をチェックし、変数にして返却
     *
     * @return boolean
     */
    public function check_args_obj($args)
    {
        $post = ($args != null) ? $args[0] : null;
        $plugin_name = ($args != null && is_array($args) && count($args) > 1) ? $args[1] : null;
        $mode_switch = ($args != null && is_array($args) && count($args) > 2) ? $args[2] : null;
        return [$post, $plugin_name, $mode_switch];
    }

    /**
     * Bootstrap any application services.
     * Larvel の仕様で引数はuserオブジェクト＋1つしか受け付けないため、
     * ($user, $args = null) で受付。
     * $args は [$post, $plugin_name] の配列オブジェクト
     *
     * @return void
     */
    public function boot()
    {
        // 認可サービス(Gate)利用の準備
        $this->registerPolicies();

        // *** ロールから確認（一般）

        // 記事追加
        Gate::define('role_reporter', function ($user, $args = null) {
            return $this->check_role($user, 'role_reporter');
        });

        // プラグイン配置
        Gate::define('role_arrangement', function ($user, $args = null) {
            return $this->check_role($user, 'role_arrangement');
        });

        // 承認
        Gate::define('role_approval', function ($user, $args = null) {
            return $this->check_role($user, 'role_approval');
        });

        // 記事修正（モデレータ）
        Gate::define('role_article', function ($user, $args = null) {
            return $this->check_role($user, 'role_article');
        });

        // 記事管理者
        Gate::define('role_article_admin', function ($user, $args = null) {
            return $this->check_role($user, 'role_article_admin');
        });

        // *** ロールから確認（管理）

        // ページ管理
        Gate::define('admin_page', function ($user, $args = null) {
            return $this->check_role($user, 'admin_page');
        });

        // サイト管理
        Gate::define('admin_site', function ($user, $args = null) {
            return $this->check_role($user, 'admin_site');
        });

        // ユーザー管理
        Gate::define('admin_user', function ($user, $args = null) {
            return $this->check_role($user, 'admin_user');
        });

        // システム管理
        Gate::define('admin_system', function ($user, $args = null) {
            return $this->check_role($user, 'admin_system');
        });

        // *** フレームの権限から確認

        // フレーム追加
        Gate::define('frames.create', function ($user, $args = null) {
            return $this->check_authority($user, 'frames.create', $args);
        });

        // フレーム移動
        Gate::define('frames.move', function ($user, $args = null) {
            return $this->check_authority($user, 'frames.move', $args);
        });

        // フレーム編集
        Gate::define('frames.edit', function ($user, $args = null) {
            return $this->check_authority($user, 'frames.edit', $args);
        });

        // フレーム選択
        Gate::define('frames.change', function ($user, $args = null) {
            return $this->check_authority($user, 'frames.change', $args);
        });

        // フレーム削除
        Gate::define('frames.delete', function ($user, $args = null) {
            return $this->check_authority($user, 'frames.delete', $args);
        });

        // *** バケツの権限から確認

        // バケツ作成
        Gate::define('buckets.create', function ($user, $args = null) {
            return $this->check_authority($user, 'buckets.create', $args);
        });

        // バケツ削除
        Gate::define('buckets.delete', function ($user, $args = null) {
            return $this->check_authority($user, 'buckets.delete', $args);
        });

        // カラム追加
        Gate::define('buckets.addColumn', function ($user, $args = null) {
            return $this->check_authority($user, 'buckets.addColumn', $args);
        });

        // カラム編集
        Gate::define('buckets.editColumn', function ($user, $args = null) {
            return $this->check_authority($user, 'buckets.editColumn', $args);
        });

        // カラム削除
        Gate::define('buckets.deleteColumn', function ($user, $args = null) {
            return $this->check_authority($user, 'buckets.deleteColumn', $args);
        });

        // カラム再設定
        Gate::define('buckets.reloadColumn', function ($user, $args = null) {
            return $this->check_authority($user, 'buckets.reloadColumn', $args);
        });

        // カラム上移動
        Gate::define('buckets.upColumnSequence', function ($user, $args = null) {
            return $this->check_authority($user, 'buckets.upColumnSequence', $args);
        });

        // カラム下移動
        Gate::define('buckets.downColumnSequence', function ($user, $args = null) {
            return $this->check_authority($user, 'buckets.downColumnSequence', $args);
        });

        // カラム保存
        Gate::define('buckets.saveColumn', function ($user, $args = null) {
            return $this->check_authority($user, 'buckets.saveColumn', $args);
        });

        // CSVダウンロード
        Gate::define('buckets.downloadCsv', function ($user, $args = null) {
            return $this->check_authority($user, 'buckets.downloadCsv', $args);
        });

        // *** 記事の権限から確認

        // 記事追加
        Gate::define('posts.create', function ($user, $args = null) {
            return $this->check_authority($user, 'posts.create', $args);
        });

        // 記事変更
        Gate::define('posts.update', function ($user, $args = null) {
            return $this->check_authority($user, 'posts.update', $args);
        });

        // 記事削除
        Gate::define('posts.delete', function ($user, $args = null) {
            return $this->check_authority($user, 'posts.delete', $args);
        });

        // 記事承認
        Gate::define('posts.approval', function ($user, $args = null) {
            return $this->check_authority($user, 'posts.approval', $args);
        });

        // *** システム権限から確認

        // システム管理者権限の有無確認
/*
        Gate::define(config('cc_role.ROLE_SYSTEM_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_SYSTEM_MANAGER')) {
                return true;
            }
            return false;
        });
*/
        // サイト管理者権限の有無確認
/*
        Gate::define(config('cc_role.ROLE_SITE_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_SITE_MANAGER')) {
                return true;
            }
            return false;
        });
*/
        // ユーザ管理者権限の有無確認
/*
        Gate::define(config('cc_role.ROLE_USER_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_USER_MANAGER')) {
                return true;
            }
            return false;
        });
*/
        // ページ管理者権限の有無確認
/*
        Gate::define(config('cc_role.ROLE_PAGE_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_PAGE_MANAGER')) {
                return true;
            }
            return false;
        });
*/
        // 運用管理者権限の有無確認
/*
        Gate::define(config('cc_role.ROLE_OPERATION_MANAGER'), function ($user) {
            if ($user->role == config('cc_role.ROLE_OPERATION_MANAGER')) {
                return true;
            }
            return false;
        });
*/
        // *** その他判定用

        // プレビューのための判定
        Gate::define('preview', function ($user, $args = null) {
            return $this->check_authority($user, 'preview', $args);
        });

        // 管理メニュー表示判定（管理機能 or 記事関連の権限に付与がある場合）
        Gate::define('role_manage_or_post', function ($user, $args = null) {
            // ページ管理
            if ($this->check_role($user, 'admin_page')) {
                return true;
            }
            // サイト管理
            if ($this->check_role($user, 'admin_site')) {
                return true;
            }
            // ユーザー管理
            if ($this->check_role($user, 'admin_user')) {
                return true;
            }
            // システム管理
            if ($this->check_role($user, 'admin_system')) {
                return true;
            }
            // 配置
            if ($this->check_role($user, 'role_arrangement')) {
                return true;
            }
            // 記事追加
            if ($this->check_role($user, 'role_reporter')) {
                return true;
            }
            // 記事追加
            if ($this->check_role($user, 'role_article')) {
                return true;
            }
            // 記事管理者
            if ($this->check_role($user, 'role_article_admin')) {
                return true;
            }
            return false;
        });

        // 管理メニュー表示判定（管理機能のどこかに付与がある場合）
        Gate::define('role_manage_on', function ($user, $args = null) {
            // ページ管理
            if ($this->check_role($user, 'admin_page')) {
                return true;
            }
            // サイト管理
            if ($this->check_role($user, 'admin_site')) {
                return true;
            }
            // ユーザー管理
            if ($this->check_role($user, 'admin_user')) {
                return true;
            }
            // システム管理
            if ($this->check_role($user, 'admin_system')) {
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
