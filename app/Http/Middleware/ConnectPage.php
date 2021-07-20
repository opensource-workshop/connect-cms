<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

use App\Models\Core\Configs;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Models\Common\Permalink;
use App\Models\Migration\MigrationMapping;

class ConnectPage
{
    /**
     * カレントページ
     */
    public $page = null;

    /**
     * Handle an incoming request.
     *
     * ・requestにセット
     *   ・page
     *   ・pages
     *   ・page_tree （pageがあれば）
     *   ・http_status_code （403, 404エラー時で403,404ページを指定していた場合）
     * ・全ビュー間のデータ共有
     *   ・page_list
     *   ・page_roles (PageRole::getPageRoles() で取得した全ページロール)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // \Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');

        // 別メソッドで使用するために保持しておく。
        // $this->router = $router;

        $router = app(Router::class);

        // ページの特定
        if (!empty($request->page_id)) {
            // ページID が渡ってきた場合
            $this->page = Page::where('id', $request->page_id)->first();
        } else {
            // ページID が渡されなかった場合、URL から取得
            $this->page = $this->getCurrentPage();
        }

        // 下層ページへ自動転送
        if ($this->page && $this->page->transfer_lower_page_flag) {
            // メニュー表示ONで、下層ページ一番上
            $lower_top_page = Page::where('parent_id', $this->page->id)
                                    ->where('base_display_flag', '1')
                                    ->orderBy('_lft', 'asc')
                                    ->first();

            if ($lower_top_page) {
                // 下層ページにリダイレクトする。
                // もしpermanent_linkが''の場合、トップページに遷移した。
                Redirect::to($lower_top_page->permanent_link)->send();
            }
        }
        // requestにセット
        $request->attributes->add(['page' => $this->page]);
        // $request->attributes->add(['page_roles' => PageRole::get()]);

        // トップページを取得
        $top_page = Page::orderBy('_lft', 'asc')->first();
        $request->attributes->add(['top_page' => $top_page]);

        // *** 全ビュー間のデータ共有
        // ハンバーガーメニューで使用するページの一覧（ConnectController::view から移動してきた）
        View::share('page_list', Page::defaultOrderWithDepth('flat', $this->page));
        // ページに対する権限
        View::share('page_roles', PageRole::getPageRoles());


        // ページ一覧データはカレントページの取得後に取得。多言語対応をカレントページで判定しているため。
        if ($this->page && get_class($this->page) == 'App\Models\Common\Page') {
            // Page データ
            // $this->pages = Page::defaultOrderWithDepth('flat', $this->page);
            $pages = Page::defaultOrderWithDepth('flat', $this->page);
        } else {
            // Page データ
            // $this->pages = Page::defaultOrder()->get();
            $pages = Page::defaultOrder()->get();
        }
        // requestにセット
        $request->attributes->add(['pages' => $pages]);

        // 自分のページから親を遡って取得
        $page_tree = null;
        if ($this->page && get_class($this->page) == 'App\Models\Common\Page') {
            // $this->page_tree = $this->getAncestorsAndSelf($this->page->id);
            // 自分のページから親を遡って取得
            $page_tree = Page::reversed()->ancestorsAndSelf($this->page->id);

            // requestにセット
            $request->attributes->add(['page_tree' => $page_tree]);
        }


        // ページがあるか判定し、なければ、404 ページを表示する。
        // 対象となる処理は、画面を持つルートの処理とする。
        // 404 ページは、管理画面でエラーページが設定されている場合はそのページを呼ぶ。
        // ただし、多言語対応がONの場合は、現在のページ用に作成された404 ページを呼ぶ。
        // 現在のページ用の404 ページがない場合は、デフォルト言語の404 ページを呼ぶ。
        // 404 ページの設定がない場合は、Connect-CMS デフォルトの404 ページを呼ぶ。
        // 404 ページを表示する場合は、HTTP ステータスコードも404 にしたいため、インスタンス変数でステータスコードを保持しておき、画面出力時に設定する。

        // HTTP ステータスコード（null なら200）
        $http_status_code = $this->checkPageNotFound($request, $router);
        if ($http_status_code) {
            // requestにセット
            $request->attributes->add(['http_status_code' => $http_status_code]);
            // $this->checkPageNotFound() で404時に、$this->page にセットされることがあるため、ここで詰めなおし
            $request->attributes->add(['page' => $this->page]);

            // 自分のページから親を遡って取得
            $page_tree = Page::reversed()->ancestorsAndSelf($this->page->id);
            // requestにセット
            $request->attributes->add(['page_tree' => $page_tree]);

            // *** 全ビュー間のデータ共有
            // ハンバーガーメニューで使用するページの一覧（ConnectController::view から移動してきた）
            View::share('page_list', Page::defaultOrderWithDepth('flat', $this->page));

            return $next($request);
        }

        // 現在のページが参照可能か判定して、NG なら403 ページを振り向ける。
        // （ページがある（管理画面ではページがない）＆IP制限がかかっていない場合は参照OK）
        // HTTP ステータスコード（null なら200）
        $http_status_code = $this->checkPageForbidden($page_tree, $router);
        if ($http_status_code) {
            // requestにセット
            $request->attributes->add(['http_status_code' => $http_status_code]);
            // $this->checkPageForbidden() で403時に、$this->page にセットされることがあるため、ここで詰めなおし
            $request->attributes->add(['page' => $this->page]);

            // 自分のページから親を遡って取得
            $page_tree = Page::reversed()->ancestorsAndSelf($this->page->id);
            // requestにセット
            $request->attributes->add(['page_tree' => $page_tree]);

            // *** 全ビュー間のデータ共有
            // ハンバーガーメニューで使用するページの一覧（ConnectController::view から移動してきた）
            View::share('page_list', Page::defaultOrderWithDepth('flat', $this->page));
        }

        return $next($request);
    }

    /**
     * 表示しているページのオブジェクトを取得
     * （ConnectController から移動してきた）
     */
    private function getCurrentPage()
    {
        // ページデータ取得のため、URL から現在のURL パスを判定する。
        $current_url = url()->current();
        $base_url = url('/');
        $current_permanent_link = str_replace($base_url, '', $current_url);

        // URLデコード
        $current_permanent_link = urldecode($current_permanent_link);

        // トップページの判定
        if (empty($current_permanent_link)) {
            $current_permanent_link = "/";
        }

        // URL パスでPage テーブル検索
        $page = Page::where('permanent_link', '=', $current_permanent_link)->first();
        if (empty($page)) {
            return false;
        }
        return $page;
    }

    /**
     * 404 判定
     * （ConnectController から移動してきた）
     */
    private function checkPageNotFound($request, $router)
    {
        // Log::debug($router->current()->getName());
        // Log::debug($router->current()->getActionMethod());

        // 特別なパス（管理画面の最初など）は404 扱いにしない。
        if (array_key_exists($request->path(), config('connect.CC_SPECIAL_PATH_MANAGE'))) {
            // 対象外の処理なので、戻る
            return;
        }

        // 特別なパス（マイページ画面の最初など）は404 扱いにしない。
        if (array_key_exists($request->path(), config('connect.CC_SPECIAL_PATH_MYPAGE'))) {
            // 対象外の処理なので、戻る
            return;
        }

        // 特別なパス（特殊な一般画面など）は404 扱いにしない。
        if (array_key_exists($request->path(), config('connect.CC_SPECIAL_PATH'))) {
            // 対象外の処理なので、戻る
            return;
        }

        // 対象となる処理は、画面を持つルートの処理とする。
        // bugfix: php artisan route:list 実行時「Call to a member function getName() on null」エラー対応
        // $route_name = $router->current()->getName();
        $route_name = is_null($router->current()) ? null : $router->current()->getName();
        if ($route_name == 'get_plugin'    ||
            $route_name == 'post_plugin'   ||
            $route_name == 'post_redirect' ||
            $route_name == 'get_redirect'  ||
            $route_name == 'get_all'       ||
            $route_name == 'post_all') {
            // 対象として次へ
        } else {
            // 対象外の処理なので、戻る
            return;
        }

        // ページがない場合
        if ($this->page === false || empty($this->page) || empty($this->page->id)) {
            // 404 対象として次へ
        } else {
            // ページありとして、戻る
            return;
        }

        // 固定URL があれば、詳細処理にリダイレクトする。
        $allRouteParams = $router->getCurrentRoute()->parameters();
        if (array_key_exists('all', $allRouteParams) && !empty($allRouteParams['all'])) {
            // 固定URL 分解
            $route_param_all = $allRouteParams['all'];
            $route_param_all_no_block = substr($route_param_all, 0, stripos($route_param_all, '-'));

            // 固定URL がデータとして存在するか。
            if (empty($route_param_all_no_block)) {
                $permalink = Permalink::where('short_url', $route_param_all)->first();
            } else {
                $permalink = Permalink::where('short_url', $route_param_all)->orWhere('short_url', $route_param_all_no_block)->first();
            }

            // NetCommons2 の固定URL の処理
            if (!empty($permalink) && $permalink->migrate_source == 'NetCommons2') {
                // ページとフレームを探す。
                $block_id = substr($route_param_all, stripos($route_param_all, '-') + 1);
                if (!empty($block_id)) {
                    $frame_mapping = MigrationMapping::where('target_source_table', 'frames')->where('source_key', $block_id)->first();
                    if (!empty($frame_mapping)) {
                        $frame = Frame::find($frame_mapping->destination_key);
                        if (!empty($frame)) {
                            // 見つけた詳細にリダイレクトする。
                            Redirect::to('/plugin/' . $permalink->plugin_name . '/' . $permalink->action . '/' . $frame->page_id . '/' . $frame->id. '/' . $permalink->unique_id . "#frame-" . $frame->id)->send();
                        }
                    }
                }
            }
        }

        // 表示中の他言語を取得（設定がない場合は空が返る）
        // $view_language = $this->getPageLanguage4url($this->getLanguages());
        $view_language = $this->getPageLanguage4url(Configs::getLanguages());

        $http_status_code = null;

        // 設定されている404 ページを探す。
        // $configs = $this->getConfigs('array');
        $configs = Configs::getSharedConfigs('array');
        if (array_key_exists('page_permanent_link_404', $configs)) {
            $this->page = $this->getPage($configs['page_permanent_link_404']->value, $view_language);
            if (empty($this->page)) {
                // 再度、デフォルト言語で調査
                $this->page = $this->getPage($configs['page_permanent_link_404']->value);
                if (empty($this->page)) {
                    // Connect-CMS のデフォルト404 ページ
                    abort(404, 'ページがありません。');
                } else {
                    // $this->page_id = $this->page->id;
                    // $this->http_status_code = 404;
                    $http_status_code = 404;
                }
            } else {
                // $this->page_id = $this->page->id;
                // $this->http_status_code = 404;
                $http_status_code = 404;
            }
        } else {
            abort(404, 'ページがありません。');
        }
        // return;
        return $http_status_code;
    }

    /**
     * ページの言語の取得
     * （ConnectController から移動してきた）
     */
    private function getPageLanguage4url($languages)
    {
        // ページの言語
        $page_language = null;

        $current_url = url()->current();
        $base_url = url('/');
        $current_permanent_link = str_replace($base_url, '', $current_url);

        // 今、表示しているページの言語を判定
        $page_paths = explode('/', $current_permanent_link);
        if ($page_paths && is_array($page_paths) && array_key_exists(1, $page_paths)) {
            foreach ($languages as $language) {
                if (trim($language->additional1, '/') == $page_paths[1]) {
                    $page_language = $page_paths[1];
                    break;
                }
            }
        }
        return $page_language;
    }

    /**
     * URLからページIDを取得
     * （ConnectCommonTrait から移動してきた）
     */
    private function getPage($permanent_link, $language = null)
    {
        // 多言語指定されたとき
        if (!empty($language)) {
            $page = Page::where('permanent_link', '/' . $language . $permanent_link)->first();
            if (!empty($page)) {
                return $page;
            }
        }
        // 多言語指定されていない or 多言語側にページがない場合は全体から探す。

        // ページ確認
        return Page::where('permanent_link', $permanent_link)->first();
    }

    /**
     * 403 判定
     * 403 にする場合は、戻り先で処理の無効化を行う可能性もあるので、HTTPステータスコードを返す。
     * （ConnectController から移動してきた）
     *
     * （$this->page 有り＋チェックする $route_name なら、参照可否チェック）
     */
    private function checkPageForbidden($page_tree, $router)
    {
        // プラグイン管理者権限以上ならOK
        //$user = Auth::user();//ログインしたユーザーを取得
        //if (isset($user) && $user->can('role_arrangement')) {
        //    return;
        //}

        // $router = app(\Illuminate\Routing\Router::class);

        // 対象となる処理は、画面を持つルートの処理とする。
        // $route_name = $this->router->current()->getName();
        $route_name = $router->current()->getName();
        if ($route_name == 'get_plugin'    ||
            $route_name == 'post_plugin'   ||
            $route_name == 'post_redirect' ||
            $route_name == 'get_redirect'  ||
            $route_name == 'get_all'       ||
            $route_name == 'post_all') {
            // 対象として次へ
        } else {
            // 対象外の処理なので、戻る
            return;
        }

        // 参照できない場合
        $user = Auth::user();  // 権限チェックをpage のisView で行うためにユーザを渡す。
        $check_no_display_flag = true; // ページ直接の参照可否チェックをしたいので、表示フラグは見ない。表示フラグは隠しページ用。

        if ($this->page && get_class($this->page) == 'App\Models\Common\Page') {
            // 自分のページから親を遡って取得
            // $page_tree = $this->getAncestorsAndSelf($this->page->id);

            // 自分のページ＋先祖ページのpage_roles を取得
            $ids = null;
            // $ids_collection = $this->page_tree->pluck('id');
            $ids_collection = $page_tree->pluck('id');
            if ($ids_collection) {
                $ids = $ids_collection->all();
            }
            // $page_roles = $this->getPageRoles($ids);
            $page_roles = PageRole::getPageRoles($ids);

            // ページをループして表示可否をチェック
            // 継承関係を加味するために is_view 変数を使用。
            $is_view = true;
            foreach ($page_tree as $page_obj) {
                $check_page_roles = null;
                if ($page_roles) {
                    $check_page_roles = $page_roles->where('page_id', $page_obj->id);
                }
                $is_view = $page_obj->isView($user, $check_no_display_flag, $is_view, $check_page_roles);
            }
            if (!$is_view) {
                // 403 対象
                return $this->doForbidden();
            }
        }
        // 参照可能
        return;
    }

    /**
     * 403 処理
     * （ConnectController から移動してきた）
     */
    private function doForbidden()
    {
        // 表示中の他言語を取得（設定がない場合は空が返る）
        // $view_language = $this->getPageLanguage4url($this->getLanguages());
        $view_language = $this->getPageLanguage4url(Configs::getLanguages());

        $http_status_code = null;

        // 設定されている403 ページを探す。
        // $configs = $this->getConfigs('array');
        $configs = Configs::getSharedConfigs('array');
        if (array_key_exists('page_permanent_link_403', $configs)) {
            $this->page = $this->getPage($configs['page_permanent_link_403']->value, $view_language);
            if (empty($this->page)) {
                // 再度、デフォルト言語で調査
                $this->page = $this->getPage($configs['page_permanent_link_403']->value);
                if (empty($this->page)) {
                    // Connect-CMS のデフォルト403 ページ
                    abort(403, 'ページ参照権限がありません。');
                } else {
                    // $this->page_id = $this->page->id;
                    // $this->http_status_code = 403;
                    $http_status_code = 403;
                }
            } else {
                // $this->page_id = $this->page->id;
                // $this->http_status_code = 403;
                $http_status_code = 403;
            }
        } else {
            abort(403, 'ページ参照権限がありません。');
        }
        // return $this->http_status_code;
        return $http_status_code;
    }
}
