<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
//use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Queue\Events\JobFailed;

use App\Traits\ConnectRoleTrait;

use App\Models\Common\Page;

use App\Enums\PluginName;

//class AppServiceProvider extends ServiceProvider
class AppServiceProvider extends AuthServiceProvider
{
    use ConnectRoleTrait;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
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
        // varcharのデフォルト文字長は、191バイトにする対応を入れる。ただし、2024年6月30日以降はこの対応を消す可能性がある。
        // これは、MySQL5.7.7 以上では必要ない対応であるが、Redhat7 デフォルトのMariaDB では必要なもののため、Redhat7 のセキュリティアップデートが終わる2024年6月30日を考慮したものである。
        // see) https://readouble.com/laravel/6.x/ja/migrations.html#creating-indexes
        Schema::defaultStringLength(191);

        // 認可サービス(Gate)利用の準備
        $this->registerPolicies();

        // *** ロールから確認（一般）

        // ゲスト
        Gate::define('role_guest', function ($user, $args = null) {
            return $this->checkRole($user, 'role_guest');
        });

        // 編集者
        Gate::define('role_reporter', function ($user, $args = null) {
            return $this->checkRole($user, 'role_reporter');
        });

        // プラグイン管理者
        Gate::define('role_arrangement', function ($user, $args = null) {
            return $this->checkRole($user, 'role_arrangement');
        });

        // 承認者
        Gate::define('role_approval', function ($user, $args = null) {
            return $this->checkRole($user, 'role_approval');
        });

        // 記事修正（モデレータ）
        Gate::define('role_article', function ($user, $args = null) {
            return $this->checkRole($user, 'role_article');
        });

        // コンテンツ管理者
        Gate::define('role_article_admin', function ($user, $args = null) {
            return $this->checkRole($user, 'role_article_admin');
        });

        // *** ロールから確認（管理）

        // ページ管理
        Gate::define('admin_page', function ($user, $args = null) {
            return $this->checkRole($user, 'admin_page');
        });

        // サイト管理
        Gate::define('admin_site', function ($user, $args = null) {
            return $this->checkRole($user, 'admin_site');
        });

        // ユーザー管理
        Gate::define('admin_user', function ($user, $args = null) {
            return $this->checkRole($user, 'admin_user');
        });

        // システム管理
        Gate::define('admin_system', function ($user, $args = null) {
            return $this->checkRole($user, 'admin_system');
        });

        // *** フレームの権限から確認

        // フレーム追加
        Gate::define('frames.create', function ($user, $args = null) {
            return $this->checkAuthority($user, 'frames.create', $args);
        });

        // フレーム移動
        Gate::define('frames.move', function ($user, $args = null) {
            return $this->checkAuthority($user, 'frames.move', $args);
        });

        // フレーム編集
        Gate::define('frames.edit', function ($user, $args = null) {
            return $this->checkAuthority($user, 'frames.edit', $args);
        });

        // フレーム選択
        Gate::define('frames.change', function ($user, $args = null) {
            return $this->checkAuthority($user, 'frames.change', $args);
        });

        // フレーム削除
        Gate::define('frames.delete', function ($user, $args = null) {
            return $this->checkAuthority($user, 'frames.delete', $args);
        });

        // *** バケツの権限から確認

        // バケツ作成
        Gate::define('buckets.create', function ($user, $args = null) {
            return $this->checkAuthority($user, 'buckets.create', $args);
        });

        // バケツ削除
        Gate::define('buckets.delete', function ($user, $args = null) {
            return $this->checkAuthority($user, 'buckets.delete', $args);
        });

        // カラム追加
        Gate::define('buckets.addColumn', function ($user, $args = null) {
            return $this->checkAuthority($user, 'buckets.addColumn', $args);
        });

        // カラム編集
        Gate::define('buckets.editColumn', function ($user, $args = null) {
            return $this->checkAuthority($user, 'buckets.editColumn', $args);
        });

        // カラム削除
        Gate::define('buckets.deleteColumn', function ($user, $args = null) {
            return $this->checkAuthority($user, 'buckets.deleteColumn', $args);
        });

        // カラム再設定
        Gate::define('buckets.reloadColumn', function ($user, $args = null) {
            return $this->checkAuthority($user, 'buckets.reloadColumn', $args);
        });

        // カラム上移動
        Gate::define('buckets.upColumnSequence', function ($user, $args = null) {
            return $this->checkAuthority($user, 'buckets.upColumnSequence', $args);
        });

        // カラム下移動
        Gate::define('buckets.downColumnSequence', function ($user, $args = null) {
            return $this->checkAuthority($user, 'buckets.downColumnSequence', $args);
        });

        // カラム保存
        Gate::define('buckets.saveColumn', function ($user, $args = null) {
            return $this->checkAuthority($user, 'buckets.saveColumn', $args);
        });

        // CSVダウンロード
        Gate::define('buckets.downloadCsv', function ($user, $args = null) {
            return $this->checkAuthority($user, 'buckets.downloadCsv', $args);
        });

        // CSVアップロード（CSVインポート）
        Gate::define('buckets.uploadCsv', function ($user, $args = null) {
            return $this->checkAuthority($user, 'buckets.uploadCsv', $args);
        });

        // *** 記事の権限から確認

        // 記事追加
        Gate::define('posts.create', function ($user, $args = null) {
            return $this->checkAuthority($user, 'posts.create', $args);
        });

        // 記事変更
        Gate::define('posts.update', function ($user, $args = null) {
            //print_r($args);
            return $this->checkAuthority($user, 'posts.update', $args);
        });

        // 記事削除
        Gate::define('posts.delete', function ($user, $args = null) {
            return $this->checkAuthority($user, 'posts.delete', $args);
        });

        // 記事承認
        Gate::define('posts.approval', function ($user, $args = null) {
            return $this->checkAuthority($user, 'posts.approval', $args);
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
            return $this->checkAuthority($user, 'preview', $args);
        });

        // 変更 or 承認 判定
        Gate::define('role_update_or_approval', function ($user, $args = null) {

            // 記事変更
            if ($this->checkAuthority($user, 'posts.update', $args)) {
                return true;
            }
            // 記事承認
            if ($this->checkAuthority($user, 'posts.approval', $args)) {
                return true;
            }
            return false;
        });

        // 管理メニュー表示判定（管理機能 or コンテンツ権限に付与がある場合）
        Gate::define('role_manage_or_post', function ($user, $args = null) {

            // ページ管理
            if ($this->checkRole($user, 'admin_page')) {
                return true;
            }
            // サイト管理
            if ($this->checkRole($user, 'admin_site')) {
                return true;
            }
            // ユーザー管理
            if ($this->checkRole($user, 'admin_user')) {
                return true;
            }
            // システム管理
            if ($this->checkRole($user, 'admin_system')) {
                return true;
            }
            // プラグイン管理者
            if ($this->checkRole($user, 'role_arrangement')) {
                return true;
            }
            // delete: 編集者, モデレータ権限は管理メニューで使える機能がないため、使わせない
            // // 編集者
            // if ($this->checkRole($user, 'role_reporter')) {
            //     return true;
            // }
            // // モデレータ
            // if ($this->checkRole($user, 'role_article')) {
            //     return true;
            // }
            // コンテンツ管理者
            if ($this->checkRole($user, 'role_article_admin')) {
                return true;
            }
            return false;
        });

        // 管理メニュー表示判定（管理機能のどこかに付与がある場合）
        Gate::define('role_manage_on', function ($user, $args = null) {
            // ページ管理
            if ($this->checkRole($user, 'admin_page')) {
                return true;
            }
            // サイト管理
            if ($this->checkRole($user, 'admin_site')) {
                return true;
            }
            // ユーザー管理
            if ($this->checkRole($user, 'admin_user')) {
                return true;
            }
            // システム管理
            if ($this->checkRole($user, 'admin_system')) {
                return true;
            }
            return false;
        });

        // プラグイン設定権限
        Gate::define('role_frame_header', function ($user, $args = null) {
            // プラグイン管理者
            if ($this->checkRoleFrame($user, 'role_arrangement', $args)) {
                return true;
            }
            return false;
        });

        // ジョブ失敗イベント
        Queue::failing(function (JobFailed $event) {
            // エラーログ出力
            Log::error("Queue::failing ID:{$event->job->getJobId()} Connection:{$event->connectionName} Message:{$event->exception->getMessage()}");
        });

        return false;
    }

    /**
     * ユーザーが指定された権限を保持しているかチェックする。
     * (ConnectCommonTraitから移動してきた)
     *
     * @return boolean
     */
    private function checkAuthority($user, $authority, $args = null)
    {
        // preview モードのチェック付きの場合はpreview モードなら権限ナシで返す。
        $request = app(Request::class);

        // 引数をバラシてPOST を取得
        // list($post, $plugin_name, $mode_switch, $buckets_obj) = $this->checkArgsObj($args);
        list($post, $plugin_name, $buckets_obj, $frame) = $this->checkArgsObj($args);

        // モードスイッチがプレビューなら表示しないになっていれば、権限ナシで返す。
        // if ($mode_switch == 'preview_off' && $request->mode == 'preview') {

        // モードスイッチがプレビューなら、無条件に権限ナシで返す。
        if ($request->mode == 'preview') {
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

        // チェックする権限を決定
        // Buckets にrole が指定されていれば、それを使用。
        //   - Buckets の role は post_flag(投稿できる), approval_flag(承認が必要)の２つのフラグあり。
        //     ここではpost_flag(投稿できる)のみ取得してチェックする。
        //     記事の承認は、ユーザ権限のrole_approvalでチェックするので、approval_flag(承認が必要)ではチェックしない。
        // Buckets にrole が指定されていなければ、標準のrole を使用
        $checkRoles = config('cc_role.CC_AUTHORITY')[$authority];
        // $post_buckets_roles = $this->getPostBucketsRoles($buckets_obj);

        // Buckets role からチェックロール追加は、記事系の権限のみに絞る。
        if (in_array($authority, ['posts.create', 'posts.update', 'posts.delete', 'posts.approval'])) {

            $post_buckets_roles = $this->getPostBucketsRoles($buckets_obj);

            // if (!empty($this->getPostBucketsRoles($buckets_obj))) {
            if (!empty($post_buckets_roles)) {
                $checkRoles = array();
                // $post_buckets_roles = $this->getPostBucketsRoles($buckets_obj);

                // Buckets に設定されたrole から、関連role を取得してチェック。
                foreach ($post_buckets_roles as $post_buckets_role) {
                    $checkRoles = array_merge($checkRoles, config('cc_role.CC_ROLE_HIERARCHY')[$post_buckets_role]);
                }
                // 配列は添字型になるので、array_merge で結合してから重複を取り除く
                $checkRoles = array_unique($checkRoles);
            }
        }

        // app\Http\Middleware\ConnectPage.php でセットした値
        $page = $request->attributes->get('page');
        $page_tree = $request->attributes->get('page_tree');
        // dd($page, $page->page_roles);

        // フレームがあれば、フレームを配置したページから親を遡ってページロールを取得
        $page_roles = $this->choicePageRolesByGoingBackParentPageOrFramePage($page, $page_tree, $frame);

        // ユーザロール取得。所属グループのページ権限あったら、そっちからとる
        $user_roles = $this->choiceUserRolesOrPageRoles($user, $page_roles);

        // 指定された権限を含むロールをループする。
        // foreach (config('cc_role.CC_AUTHORITY')[$authority] as $role) {
        foreach ($checkRoles as $checkRole) {
            // ユーザの保持しているロールをループ
            // foreach ($user['user_roles'] as $target) {
            // foreach ((array)$user->user_roles as $target) {
            foreach ($user_roles as $target) {
                // ターゲット処理をループ
                foreach ($target as $user_role => $user_role_value) {
                    // 要求されているのが承認権限の場合、Buckets の投稿権限にはないため、ここでチェックする。
                    // bugfix:  モデレータに「承認が必要」としても、モデレータは自分で承認できてしまう不具合修正
                    //          承認権限チェック（$authority == 'posts.approval'）なのに、ここでtrueとならず、必要なロールを保持している（$user_role == 'role_article'）でtrueとなっていた。
                    //          承認権限チェックとそれ以外でif文見直す。
                    // if ($authority == 'posts.approval' && $user_role == 'role_approval') {
                    //     return true;
                    // }
                    if ($authority == 'posts.approval') {
                        // bugfix: コンテンツ管理者（role_article_admin）で承認ボタンが表示されなかったため、role_article_adminを追加
                        if ($user_role == 'role_article_admin' || $user_role == 'role_approval') {
                            return true;
                        }
                    } else {
                        // 必要なロールを保持している
                        if ($checkRole == $user_role && $user_role_value) {
                            // bugfix: 固定記事、権限設定の「投稿できる」権限が機能してないバグ修正
                            //        ここで role_article モデレータ（他ユーザの記事も更新）を許可すると、
                            //        固定記事の権限設定で モデレータ を 投稿できるOFF でも設定を無視して、投稿できてしまう。
                            // 他者の記事を更新できる権限の場合は、記事作成者のチェックは不要
                            // if (($user_role == 'role_article_admin') ||
                            //     ($user_role == 'role_article') ||
                            //     ($user_role == 'role_approval')) {
                            if ($user_role == 'role_article_admin' || $user_role == 'role_approval') {
                                return true;
                            }

                            // モデレータ（role_article ）で 固定記事以外は、許可
                            if ($user_role == 'role_article' &&
                                $plugin_name != PluginName::getPluginName(PluginName::contents)) {
                                return true;
                            }

                            // 自分のオブジェクトチェックが必要ならチェックする
                            if (empty($post)) {
                                return true;
                            } else {

                                // bugfix: 固定記事の場合、権限設定で 投稿できるON なら $post->created_id 以外でも編集可
                                if ($plugin_name == PluginName::getPluginName(PluginName::contents)) {

                                    if ($authority == 'posts.create' ||
                                        $authority == 'posts.update' ||
                                        $authority == 'posts.delete') {

                                        return true;
                                    }
                                } else {
                                    // 固定記事プラグイン以外

                                    // 投稿者なら編集可
                                    if ((($authority == 'buckets.delete') ||
                                        ($authority == 'posts.create') ||
                                        ($authority == 'posts.update') ||
                                        ($authority == 'posts.delete')) &&
                                        ($user->id == $post->created_id)) {

                                        return true;
                                    } else {
                                        // 複数ロールをチェックするため、ここではreturn しない。
                                        // return false;
                                    }
                                }

                            }
                            // 複数ロールをチェックするため、ここではreturn しない。
                            // return true;
                        }
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
    private function checkArgsObj($args)
    {
        $post        = ($args != null) ? $args[0] : null;
        $plugin_name = ($args != null && is_array($args) && count($args) > 1) ? $args[1] : null;
        // $mode_switch = ($args != null && is_array($args) && count($args) > 2) ? $args[2] : null;
        $buckets     = ($args != null && is_array($args) && count($args) > 2) ? $args[2] : null;
        $frame       = ($args != null && is_array($args) && count($args) > 3) ? $args[3] : null;

        // return [$post, $plugin_name, $mode_switch, $buckets];
        return [$post, $plugin_name, $buckets, $frame];
    }

    /**
     * Buckets の投稿権限データをrole の配列で返却
     * (ConnectCommonTraitから移動してきた)
     *
     * @return boolean|array
     */
    private function getPostBucketsRoles($buckets)
    {
        // Buckets オブジェクトがない場合はfalse を返す。
        if (empty($buckets)) {
            return false;
        }

        // Buckets オブジェクトでない場合もfalse
        if (!is_object($buckets) || get_class($buckets) != "App\Models\Common\Buckets") {
            return false;
        }

        // return $buckets->getBucketsRoles();
        return $buckets->getPostArrayBucketsRoles();

        // // Buckets にrole がない場合などで、Buckets のrole を使用しない場合はfalse を返す。
        // if (empty($buckets)) {
        //     return false;
        // }
        // // Buckets オブジェクトでない場合もfalse
        // if (!is_object($buckets) || get_class($buckets) != "App\Models\Common\Buckets") {
        //     return false;
        // }
        // // role を配列にして返却
        // $roles = null;
        // if ($buckets->post_role) {
        //     $roles = explode(',', $buckets->post_role);
        // }
        // if (empty($roles)) {
        //     return false;
        // }
        // return $roles;
    }

    /**
     * ユーザーが指定された役割を保持しているか、frame->page_id からページロール(役割)をチェックする。
     */
    private function checkRoleFrame($user, $role, $args = null): bool
    {
        // ログインしていない場合は権限なし
        if (empty($user)) {
            return false;
        }

        list($post, $plugin_name, $buckets_obj, $frame) = $this->checkArgsObj($args);

        // frameがない場合はfalse を返す。
        if (empty($frame)) {
            return false;
        }

        $request = app(Request::class);

        // app\Http\Middleware\ConnectPage.php でセットした値
        // $page_roles = $request->attributes->get('page_roles');
        $page = $request->attributes->get('page');
        $page_tree = $request->attributes->get('page_tree');

        // フレームがあれば、フレームを配置したページから親を遡ってページロールを取得
        $page_roles = $this->choicePageRolesByGoingBackParentPageOrFramePage($page, $page_tree, $frame);


        // 指定された権限を含むロールをループする。
        return $this->checkRoleHierarchy($user, $role, $page_roles);
    }
}
