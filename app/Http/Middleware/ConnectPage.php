<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

use App\Models\Core\Configs;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Models\Common\Permalink;
use App\Models\Migration\MigrationMapping;
use App\Enums\AreaType;

class ConnectPage
{
    /**
     * カレントページ
     */
    public $page = null;

    /**
     * Handle an incoming request.
     *
     * ・request->attributesにセット
     *   ・page
     *   ・pages
     *   ・top_page
     *   ・page_tree （pageがあれば）
     *   ・frame （routeにframe_idがある場合。frameが存在しなければnull）
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

        // frame_id があれば先にフレームを取得し、同一リクエスト内で再利用する。
        $route_frame_id = $request->route('frame_id');
        if (!empty($route_frame_id)) {
            $request->attributes->add(['frame' => Frame::find($route_frame_id)]);
        }

        // ページの特定
        $route_page_id = $request->route('page_id');
        if (!empty($route_page_id)) {
            // ページID が渡ってきた場合
            $this->page = Page::where('id', $route_page_id)->first();
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
        View::share('page_list', Page::defaultOrderWithDepth(null, $this->page));
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
            View::share('page_list', Page::defaultOrderWithDepth(null, $this->page));

            return $next($request);
        }

        // 自分のページツリーの最後（root）にトップが入っていなければ、トップページをページツリーの最後に追加する
        // ※ 403判定にトップページも含める
        // copy by app\Models\Common\Page::getPageTreeByGoingBackParent()
        if (!is_null($page_tree) && $page_tree[count($page_tree)-1]->id != $top_page->id) {
            $page_tree->push($top_page);
        }

        // 現在のページが参照可能か判定して、NG なら403 ページを振り向ける。
        // （ページがある（管理画面ではページがない）＆IP制限がかかっていない場合は参照OK）
        // HTTP ステータスコード（null なら200）
        $http_status_code = $this->checkPageForbidden($request, $page_tree, $router);
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
            View::share('page_list', Page::defaultOrderWithDepth(null, $this->page));
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
        $route_name = is_null($router->current()) ? null : $router->current()->getName();
        if (!$this->isPageLimitCheckRoute($route_name)) {
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
                // block_id から frames の MigrationMapping 取得
                $frame_mapping = $this->getFrameMappingFromBlockId($block_id, $permalink->nc2_block_id);
                if (!empty($frame_mapping)) {
                    $frame = Frame::find($frame_mapping->destination_key);
                    if (!empty($frame)) {
                        // 見つけた詳細にリダイレクトする。
                        Redirect::to('/plugin/' . $permalink->plugin_name . '/' . $permalink->action . '/' . $frame->page_id . '/' . $frame->id. '/' . $permalink->unique_id . "#frame-" . $frame->id)->send();
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
     * block_id から frames の MigrationMapping 取得
     */
    private function getFrameMappingFromBlockId(?int $block_id, ?int $permalink_nc2_block_id): ?MigrationMapping
    {
        if (empty($block_id)) {
            // 空なら permalinks.nc2_block_id で取得
            $frame_mapping = MigrationMapping::where('target_source_table', 'frames')->where('source_key', $permalink_nc2_block_id)->first();

        } else {
            $frame_mapping = MigrationMapping::where('target_source_table', 'frames')->where('source_key', $block_id)->first();
            if (empty($frame_mapping)) {
                // 空なら permalinks.nc2_block_id で再取得
                $frame_mapping = MigrationMapping::where('target_source_table', 'frames')->where('source_key', $permalink_nc2_block_id)->first();
            }
        }
        return $frame_mapping;
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
    private function checkPageForbidden($request, $page_tree, $router)
    {
        // 対象となる処理は、ページ/フレームの情報を受け取るルートとする。
        $route_name = is_null($router->current()) ? null : $router->current()->getName();
        if (!$this->isPageLimitCheckRoute($route_name)) {
            // 対象外の処理なので、戻る
            return;
        }

        // page_id と frame_id の組み合わせが不整合なら、不正アクセスとして 403 扱いにする。
        if (!$this->isValidPageAndFrame($request, $page_tree)) {
            return $this->doForbidden();
        }

        if ($this->page && get_class($this->page) == 'App\Models\Common\Page') {
            // 親子ページを加味してページ表示できるか
            $is_view = $this->page->isVisibleAncestorsAndSelf($page_tree);
            if (!$is_view) {
                // 403 対象
                return $this->doForbidden();
            }
        }
        // 参照可能
        return;
    }

    /**
     * ページ閲覧制限チェックの対象ルート判定
     */
    private function isPageLimitCheckRoute($route_name)
    {
        return in_array($route_name, [
            'get_plugin',
            'post_plugin',
            'get_json',
            'post_json',
            'post_redirect',
            'get_redirect',
            'post_download',
            'get_download',
            'get_all',
            'post_all',
        ], true);
    }

    /**
     * page_id と frame_id の整合性判定
     */
    private function isValidPageAndFrame($request, $page_tree)
    {
        $route_page_id = $request->route('page_id');
        $route_frame_id = $request->route('frame_id');

        // frame_id を伴わないルートは、ページとフレームの組み合わせ判定自体が不要。
        if (empty($route_frame_id)) {
            return true;
        }

        // frame_id があるのに page_id がない組み合わせは、対象ページを特定できないので不正扱い。
        if (empty($route_page_id)) {
            return false;
        }

        // frame は handle() で先に読み込んでいる前提。取得できない frame_id は不正扱い。
        $frame = $request->attributes->get('frame');
        if (empty($frame)) {
            return false;
        }

        // メインエリアは継承しないため、配置ページと現在ページが完全一致する場合だけ許可する。
        if ((int)$frame->area_id === AreaType::main) {
            return ((int)$frame->page_id === (int)$route_page_id);
        }

        // 共通エリアでも、配置ページ本人からの操作は従来通り許可する。
        if ((int)$frame->page_id === (int)$route_page_id) {
            return true;
        }

        // 共通エリアは「現在ページの祖先ツリー上で実際に採用されるフレームか」を判定する。
        // そのため、現在ページと祖先ツリーが解決できない場合は許可できない。
        if (empty($page_tree) || empty($this->page) || empty($this->page->id)) {
            return false;
        }

        $effective_frames = $this->getEffectiveCommonAreaFrames($page_tree, (int)$frame->area_id);

        return $effective_frames->contains(function ($effective_frame) use ($frame) {
            return (int)($effective_frame->frame_id ?? $effective_frame->id) === (int)$frame->id;
        });
    }

    /**
     * 現ページで実際に採用される共通エリアフレームを取得する。
     */
    private function getEffectiveCommonAreaFrames($page_tree, int $area_id): Collection
    {
        if (empty($this->page) || empty($this->page->id)) {
            return collect();
        }

        $normalized_page_tree = $this->normalizePageTreeForCommonArea($page_tree);
        if ($normalized_page_tree->isEmpty()) {
            return collect();
        }

        if (!$this->isCommonAreaVisibleOnCurrentPage($normalized_page_tree, $area_id)) {
            return collect();
        }

        $page_ids = $normalized_page_tree->pluck('id')->filter()->all();
        if (empty($page_ids)) {
            return collect();
        }

        $effective_page_id = null;
        $effective_frames = collect();
        foreach ($this->queryCommonAreaFrames($page_ids, $area_id) as $frame) {
            if (is_null($effective_page_id)) {
                $effective_page_id = (int)$frame->page_id;
            }

            if ((int)$frame->page_id !== $effective_page_id) {
                break;
            }

            $effective_frames->push($frame);
        }

        return $effective_frames;
    }

    /**
     * 共通エリア判定用にページツリーを正規化する。
     */
    private function normalizePageTreeForCommonArea($page_tree): Collection
    {
        if (empty($page_tree)) {
            return collect();
        }

        $normalized_page_tree = collect($page_tree->all());
        $top_page = Page::getTopPage();
        $language_top_page = $this->getLanguageTopPage();

        $excluded_page_ids = collect([
            $top_page->id ?? null,
            $language_top_page->id ?? null,
        ])->filter()->map(function ($page_id) {
            return (int)$page_id;
        })->all();

        $normalized_page_tree = $normalized_page_tree->filter(function ($tree_page) use ($excluded_page_ids) {
            return !empty($tree_page)
                && !empty($tree_page->id)
                && !in_array((int)$tree_page->id, $excluded_page_ids, true);
        })->values();

        if (!empty($language_top_page) && !empty($language_top_page->id)) {
            $normalized_page_tree->push($language_top_page);
        }

        if (!empty($top_page) && !empty($top_page->id)) {
            if (empty($language_top_page) || (int)$language_top_page->id !== (int)$top_page->id) {
                $normalized_page_tree->push($top_page);
            }
        }

        return $normalized_page_tree;
    }

    /**
     * 現在ページで共通エリアが描画対象か判定する。
     */
    private function isCommonAreaVisibleOnCurrentPage(Collection $page_tree, int $area_id): bool
    {
        $layout_array = $this->getLayoutArrayForCommonArea($page_tree);

        if ($area_id === AreaType::header) {
            return $layout_array[0] == '1';
        }
        if ($area_id === AreaType::left) {
            return $layout_array[1] == '1';
        }
        if ($area_id === AreaType::right) {
            return $layout_array[2] == '1';
        }
        if ($area_id === AreaType::footer) {
            return $layout_array[3] == '1';
        }

        return false;
    }

    /**
     * 共通エリア判定用のレイアウト配列を取得する。
     */
    private function getLayoutArrayForCommonArea(Collection $page_tree): array
    {
        $layout_array = explode('|', $this->getLayoutForCommonArea($page_tree));
        if (count($layout_array) !== 4) {
            return [1, 1, 1, 1];
        }

        return $layout_array;
    }

    /**
     * 共通エリア判定用のレイアウトを取得する。
     */
    private function getLayoutForCommonArea(Collection $page_tree): string
    {
        $layout_default = config('connect.BASE_LAYOUT_DEFAULT');
        if (empty($this->page)) {
            return $layout_default;
        }

        $layout = null;
        foreach ($page_tree as $tree_page) {
            if (empty($tree_page) || empty($tree_page->layout)) {
                continue;
            }

            if ($tree_page->id != $this->page->id
                && !is_null($tree_page->layout_inherit_flag)
                && (int)$tree_page->layout_inherit_flag === 0) {
                continue;
            }

            $layout = $tree_page->layout;
            break;
        }

        if (empty($layout)) {
            $layout = Configs::getSharedConfigsValue('base_layout', $layout_default);
        }
        if (empty($layout)) {
            $layout = $layout_default;
        }

        return $layout;
    }

    /**
     * 共通エリアの継承候補フレームを取得する。
     */
    private function queryCommonAreaFrames(array $page_ids, int $area_id): Collection
    {
        return Frame::whereIn('frames.page_id', $page_ids)
            ->where('frames.area_id', $area_id)
            ->select('frames.*', 'frames.id as frame_id', 'pages.id as page_id')
            ->join('pages', 'pages.id', '=', 'frames.page_id')
            ->where(function ($query) {
                $query->where('page_only', 0)
                    ->orWhere(function ($query2) {
                        $query2->where('page_only', 1)
                            ->where('page_id', $this->page->id);
                    })
                    ->orWhere('page_only', 2);
            })
            ->orderBy('pages._lft', 'desc')
            ->orderBy('frames.display_sequence', 'asc')
            ->get();
    }

    /**
     * 多言語トップページを取得する。
     */
    private function getLanguageTopPage(): ?Page
    {
        if (empty($this->page) || empty($this->page->permanent_link) || !$this->isLanguageMultiOn()) {
            return null;
        }

        $languages = Configs::getLanguages();
        if (empty($languages)) {
            return null;
        }

        $page_language = $this->getPageLanguageFromPage($languages);
        if (empty($page_language)) {
            return null;
        }

        return Page::where('permanent_link', '/' . $page_language)->first();
    }

    /**
     * ページオブジェクトから言語を取得する。
     */
    private function getPageLanguageFromPage($languages)
    {
        $page_language = null;
        $page_paths = explode('/', $this->page->permanent_link);
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
     * 多言語設定が有効か判定する。
     */
    private function isLanguageMultiOn(): bool
    {
        foreach (Configs::getSharedConfigs() as $config) {
            if ($config->name !== 'language_multi_on') {
                continue;
            }

            return $config->value == '1';
        }

        return false;
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
