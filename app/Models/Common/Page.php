<?php

namespace App\Models\Common;

// use RecursiveIteratorIterator;
// use RecursiveArrayIterator;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request as FacadeRequest;

use Kalnoy\Nestedset\NodeTrait;

use App\Models\Core\Configs;
use App\Traits\ConnectCommonTrait;

class Page extends Model
{
    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = ['page_name', 'permanent_link', 'background_color', 'header_color', 'theme',  'layout', 'base_display_flag', 'membership_flag', 'ip_address', 'othersite_url', 'othersite_url_target', 'class', 'password'];

    use NodeTrait;
    use ConnectCommonTrait;

    /**
     * hasMany 設定
     * - hasManyは、$user->page_roles で使うので、変数名と同義になるので、このメソッド名はphpcs除外
     * - hasManyは、値があるなしに関わらず collection 型を返す。値がなければ空の collection 型を返す。
     */
    public function page_roles()    // phpcs:ignore
    {
        return $this->hasMany(PageRole::class);
    }

    /**
     * 言語設定があれば、特定の言語ページのみに絞る
     */
    public static function getPageIds($current_page_obj = null, $menu = null, $setting_mode = false)
    {
        $page_ids = array();
        $pages = self::getPages($current_page_obj, $menu, $setting_mode);
        foreach ($pages as $page) {
            $page_ids[] = $page->id;
        }
        return $page_ids;
    }

    /**
     * 言語設定があれば、特定の言語ページのみに絞る
     */
    public static function getPages($current_page_obj = null, $menu = null, $setting_mode = false)
    {
        // current_page_obj がない場合は、ページデータを全て取得（管理画面など）
        // 表示順は入れ子集合モデルの順番
        if (empty($current_page_obj)) {
            return self::defaultOrder()->get();
        }

        // メニューで表示するページが絞られている場合は、選択したページのみ取得する。
        $where_page_ids = array();
        if (!empty($menu) && $menu->select_flag == 1 && !empty($menu->page_ids)) {
            $where_page_ids = explode(',', $menu->page_ids);
        }

        // 多言語の使用有無取得
        $language_multi_on_record = Configs::where('name', 'language_multi_on')->first();
        $language_multi_on = ($language_multi_on_record) ? $language_multi_on_record->value : null;

        // 多言語モードでない場合 or 設定モードの場合 は表示設定されているページデータを全て取得
        if (!$language_multi_on || $setting_mode) {
            return self::defaultOrder()->where(function ($query_menu) use ($where_page_ids) {
                             // メニューによるページ選択
                if (!empty($where_page_ids)) {
                    $query_menu->whereIn('id', $where_page_ids);
                }
            })->get();
        }

        // 使用する言語リストの取得
        $languages = Configs::where('category', 'language')->orderBy('additional1', 'asc')->get();

        // 現在の言語
        $current_language = null;

        // 今、表示しているページの言語を判定
        $current_page_paths = explode('/', $current_page_obj['permanent_link']);
        if ($current_page_paths && is_array($current_page_paths) && array_key_exists(1, $current_page_paths)) {
            foreach ($languages as $language) {
                if (trim($language->additional1, '/') == $current_page_paths[1]) {
                    $current_language = $current_page_paths[1];
                    break;
                }
            }
        }
        //echo $current_language;

        // 表示言語がデフォルトなら、多言語のページを表示しない。多言語なら、その言語のみに絞り込む。
        // デフォルトの場合、言語設定にある他の言語のpermanent_link を対象外にする。
        if (empty($current_language)) {
            $ret = self::defaultOrder()
                       ->where(function ($query) use ($languages) {
                        foreach ($languages as $language) {
                            if ($language->additional1 == '/') {
                                // デフォルト言語 "/" は表示するので、除外の対象外
                                continue;
                            }
                            $query->where('permanent_link', 'not like', $language->additional1 . '%');
                        }
                       })
                       ->where(function ($query_menu) use ($where_page_ids) {
                           // メニューによるページ選択
                        if (!empty($where_page_ids)) {
                            $query_menu->whereIn('id', $where_page_ids);
                        }
                       })
                       ->get();

//Log::debug(json_encode( $ret, JSON_UNESCAPED_UNICODE));

            return $ret;
        } else {
            return self::defaultOrder()
                       ->where(function ($query_lang) use ($current_language) {
                           // 多言語トップページは /en のように後ろに / がない。 /en* だと、/env なども拾ってしまう。
                           $query_lang->where('permanent_link', 'like', '/' . $current_language . '/%')
                                      ->orWhere('permanent_link', '/' . $current_language);
                       })
                       ->where(function ($query_menu) use ($where_page_ids) {
                           // メニューによるページ選択
                        if (!empty($where_page_ids)) {
                            $query_menu->whereIn('id', $where_page_ids);
                        }
                       })
                       ->get();
        }
    }

    /**
     * ページデータ取得＆深さの追加関数
     *
     * @param int $frame_id
     * @return view
     */
    public static function defaultOrderWithDepth($format = null, $current_page_obj = null, $menu = null, $setting_mode = false)
    {
        // ページデータを全て取得
        // 表示順は入れ子集合モデルの順番
        // メニューの表示チェックは、読むデータを絞るものではなく、表示のON/OFF に使用する。（本来の階層は維持する）
        //$pages = self::getPages($current_page_obj, $menu, $setting_mode);
        $pages = self::getPages($current_page_obj, null, $setting_mode);

        //Log::debug($pages);

        // メニューの階層を表現するために、一度ツリーにしたものを取得し、クロージャで深さを追加
        $tree = $pages->toTree();
        //Log::debug(json_encode( $tree, JSON_UNESCAPED_UNICODE));

        // メニューが選択表示＆表示選択データが渡されたら
        $where_page_ids = null;
        if (!empty($menu) && $menu->select_flag == 1) {
            $where_page_ids = explode(',', $menu->page_ids);
        }

        // クロージャでページ配列を再帰ループし、深さを追加する。
        // テンプレートでは深さをもとにデザイン処理する。
        $traverse = function ($pages, $prefix = '-', $depth = -1, $display_flag = 1) use (&$traverse, $where_page_ids, $menu) {
            $depth = $depth+1;
            foreach ($pages as $page) {
                $page->depth = $depth;
                //$page->page_name = $page->page_name;

                // 表示フラグを親を引き継いで保持
                // 表示フラグはローカル変数に保持して、子要素の再帰呼び出し処理へ引き継ぐ
                // (自身のページの表示/非表示はこの後、メニュー設定で変更されるため)

                // メニュー設定を見ない or メニュー設定でページ設定の条件を使用するとなっている場合は、基本表示フラグを反映
                if (empty($menu) || $menu->select_flag === 0) {
                    $page_display_flag = ($page->base_display_flag == 0 || $display_flag == 0 ? 0 : 1);
                } else {
                    $page_display_flag = ($display_flag == 0 ? 0 : 1);
                }
                $page->display_flag = $page_display_flag;

                // メニュー設定からの表示/非表示の反映(メニューが選択表示＆選択されていなかったら display_flag に 0 を)
                if (!empty($where_page_ids) && !in_array($page->id, $where_page_ids)) {
                    $page->display_flag = 0;
                }

                // 再帰呼び出し(表示フラグはメニュー設定の反映されていないページ情報のものを渡す)
                $traverse($page->children, $prefix.'-', $depth, $page_display_flag);
            }
        };
        $traverse($tree);

        if ($format == 'flat') {
            return $pages;
        }

        //Log::debug(json_encode( $tree, JSON_UNESCAPED_UNICODE));
        return $tree;
    }

    /**
     * クラス名取得
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * リンク用URL取得
     */
    public function getLinkUrl($trim_str = null)
    {
        if ($trim_str == null) {
            return $this->permanent_link;
        }

        return trim($this->permanent_link, $trim_str);
    }

    /**
     * CSS セレクタ用クラス用取得
     */
    public function getPermanentlinkClassname()
    {
        if (empty(trim($this->permanent_link, '/'))) {
            return "home";
        }
        return str_replace('/', '-', trim($this->permanent_link, '/'));
    }

    /**
     * 表示可否の判断
     */
    public function isView($user = null, $check_no_display_flag = false, $view_default = null, $check_page_roles = null)
    {
        // $check_no_display_flag がtrue なら、display_flag を考慮しない。
        if ($this->display_flag == 1) {
            // 以下のip以降のチェックに進む
        } else {
            if ($check_no_display_flag) {
                // 以下のip以降のチェックに進む
            } else {
                return false;
            }
        }

        // 認証情報が空（ログインしていない状態）or プラグイン管理者権限を持たない場合
        // 上記条件の場合のみ、IPアドレスチェックを行う
        if (empty($user) || !$user->can('role_arrangement')) {
            // IP アドレス制限があれば、IP アドレスチェック
            if (!empty($this->ip_address)) {
                // IP アドレスをループしてチェック。
                // 一つでもOKなら、OK とする。
                $ip_address_check = false;
                $ip_addresses = explode(',', $this->ip_address);
                foreach ($ip_addresses as $ip_address) {
                    if ($this->isRangeIp(FacadeRequest::ip(), trim($ip_address))) {
                        $ip_address_check = true;
                    }
                }
                // 設定されたIPアドレスのどれにも合致しなかったため、参照NG
                if (!$ip_address_check) {
                    return false;
                }
            }
        }

        // ログインユーザ全員参加の場合は、ログインしていればOKをチェックする。
        if ($this->membership_flag == 2) {
            if (!empty($user)) {
                return true;
            } else {
                return false;
            }
        }

        // メンバーシップページの場合は、参加条件をチェックする。
        if ($this->membership_flag == 1) {
            if ($check_page_roles && $check_page_roles->where('user_id', $user->id)->where('page_id', $this->id)->count() > 0) {
                return true;
            } else {
                return false;
            }
        }

        // view_default を加味する。
        // Top       IP制限なし
        //    2nd    IP制限ありでNG  <--- この場合、ここが有効
        //       3rd IP制限なし      <--- ここでview_default(false が渡される)を返す。
        // ---
        // Top       IP制限なし
        //    2nd    IP制限ありでNG
        //       3rd IP制限ありでOK  <--- この場合、ここが有効(true を返す)
        if ($view_default !== null) {
            return $view_default;
        }

        // 制限にかからなっかったのでOK
        return true;
    }

    /**
     * 親子ページを加味して 表示可否の判断
     */
    public function isVisibleAncestorsAndSelf(Collection $page_tree) : bool
    {
        // 権限チェックをpage のisView で行うためにユーザを渡す。
        $user = Auth::user();
        // ページ直接の参照可否チェックをしたいので、表示フラグは見ない。表示フラグは隠しページ用。
        $check_no_display_flag = true;
        // 自分のページ＋先祖ページのpage_roles を取得
        $page_roles = PageRole::getPageRoles($page_tree->pluck('id'));

        // ページをループして表示可否をチェック
        // 継承関係を加味するために is_view 変数を使用。
        $is_view = true;
        foreach ($page_tree as $page_obj) {

            // IP アドレス制限　　　　　　　　　　　　　　　　：親子ともに設定あったら、子⇒親に遡って全てチェック
            // ログインユーザ全員参加やメンバーシップページ設定：親子ともに設定あったら、子だけでチェック

            // ページに直接、ログインユーザ全員参加やメンバーシップページが設定されている場合は、親を見ずに、該当ページ（一番下の子=$page_tree[0]）だけで判断する。
            if ($page_obj->membership_flag == 2) {
                if (empty($user)) {
                    // 403 対象. 見えないページ
                    return false;
                } else {
                    // 見えるページ
                    return true;
                }
            } elseif ($page_obj->membership_flag == 1) {
                if (empty($page_roles)) {
                    return false;
                }
                $check_page_roles = $page_roles->where('page_id', $page_obj->id);
                $is_view = $page_obj->isView($user, $check_no_display_flag, $is_view, $check_page_roles);
                return $is_view;
            }
            // 以降は親を見る処理（IP アドレス制限）

            // IP アドレス制限用。上でmembership_flag=1,2チェック済みのため、ここの isView() のmembership_flag=1,2チェックは実質使われない。
            $is_view = $page_obj->isView($user, $check_no_display_flag, $is_view);
        }

        return $is_view;
    }

    /**
     * ページのURLを返す
     */
    public function getUrl()
    {
        if (!empty($this->othersite_url)) {
            return $this->othersite_url;
        }
        return url("/") . $this->permanent_link;
    }

    /**
     * ページのリンク用target タグを返す
     */
    public function getUrlTargetTag()
    {
        $return_str = '';
        if ($this->othersite_url_target) {
            $return_str .= 'target="_blank"';
        }

        return $return_str;
    }

    /**
     * レイアウト判定
     */
    public function isArea($area)
    {
        // レイアウトの数値詰めを取得
        $simple_layout = $this->getSimpleLayout();

        // レイアウト設定があれば、4文字あるはず
        if (mb_strlen($simple_layout) != 4) {
            return false;
        }

        // ヘッダーの確認
        if (mb_strtolower($area) == 'header') {
            if (mb_substr($simple_layout, 0, 1) == '1') {
                return true;
            } else {
                return false;
            }
        }

        // 左の確認
        if (mb_strtolower($area) == 'left') {
            if (mb_substr($simple_layout, 1, 1) == '1') {
                return true;
            } else {
                return false;
            }
        }

        // 右の確認
        if (mb_strtolower($area) == 'right') {
            if (mb_substr($simple_layout, 2, 1) == '1') {
                return true;
            } else {
                return false;
            }
        }

        // フッターの確認
        if (mb_strtolower($area) == 'footer') {
            if (mb_substr($simple_layout, 3, 1) == '1') {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * レイアウト取得
     */
    public function getSimpleLayout()
    {
        return str_replace('|', '', $this->layout);
    }

    /**
     * レイアウト取得
     */
    public function getLayoutTitle()
    {
        if ($this->getSimpleLayout() == '0000') {
            return "メインのみ";
        }
        if ($this->getSimpleLayout() == '0001') {
            return "フッター";
        }
        if ($this->getSimpleLayout() == '0010') {
            return "右";
        }
        if ($this->getSimpleLayout() == '0011') {
            return "右、フッター";
        }
        if ($this->getSimpleLayout() == '0100') {
            return "左";
        }
        if ($this->getSimpleLayout() == '0101') {
            return "左、フッター";
        }
        if ($this->getSimpleLayout() == '0110') {
            return "左、右";
        }
        if ($this->getSimpleLayout() == '0111') {
            return "左、右、フッター";
        }
        if ($this->getSimpleLayout() == '1000') {
            return "ヘッダー";
        }
        if ($this->getSimpleLayout() == '1001') {
            return "ヘッダー、フッター";
        }
        if ($this->getSimpleLayout() == '1010') {
            return "ヘッダー、右";
        }
        if ($this->getSimpleLayout() == '1011') {
            return "ヘッダー、右、フッター";
        }
        if ($this->getSimpleLayout() == '1100') {
            return "ヘッダー、左";
        }
        if ($this->getSimpleLayout() == '1101') {
            return "ヘッダー、左、フッター";
        }
        if ($this->getSimpleLayout() == '1110') {
            return "ヘッダー、左、右";
        }
        if ($this->getSimpleLayout() == '1111') {
            return "ヘッダー、左、右、フッター";
        }
    }

    /**
     * パスワードを要求するかの判断
     */
    public function isRequestPassword($request, $page_tree)
    {
        // 自分のページから親を遡って取得
        $page_tree = $this->getPageTreeByGoingBackParent($page_tree);

        // 自分及び先祖ページに閲覧パスワードが設定されていなければ戻る
        $check_page = null;
        foreach ($page_tree as $page) {
            if (!empty($page->password)) {
                $check_page = $page;
                break;
            }
        }
        if (empty($check_page)) {
            return false;
        }

        // セッション中に該当ページの認証情報があるかチェック
        if ($request->session()->has('page_auth.'.$check_page->id) && $request->session()->get('page_auth.'.$check_page->id) == 'authed') {
            // すでに認証されているので、問題なし
            return false;
        }

        // 認証を要求
        return true;
    }

    /**
     * 自分のページから親を遡ってページツリーを取得
     */
    public function getPageTreeByGoingBackParent(?Collection $page_tree): Collection
    {
        // 自分のページから親を遡って取得
        if (empty($page_tree)) {
            $page_tree = Page::reversed()->ancestorsAndSelf($this->id);
        }
        // \Log::debug(var_export($page_tree, true));

        if ($page_tree->isEmpty()) {
            // $page_tree=null & $this->id=null の場合、$page_tree が空コレクションになる事に対応
            return $page_tree;
        }

        // トップページを取得
        // $top_page = Page::orderBy('_lft', 'asc')->first();
        $top_page = self::getTopPage();

        // 自分のページツリーの最後（root）にトップが入っていなければ、トップページをページツリーの最後に追加する
        if ($page_tree[count($page_tree)-1]->id != $top_page->id) {
            $page_tree->push($top_page);
        }

        return $page_tree;
    }

    /**
     * トップページを取得
     */
    public static function getTopPage()
    {
        $request = app(Request::class);

        // app\Http\Middleware\ConnectPage.php でセットした値
        $top_page = $request->attributes->get('top_page');

        if (is_null($top_page)) {
            $top_page = Page::orderBy('_lft', 'asc')->first();
        }

        return $top_page;
    }

    /**
     * パスワードチェックの判定
     */
    public function checkPassword($password, $page_tree)
    {
        // トップページを取得
        // $top_page = Page::orderBy('_lft', 'asc')->first();
        $top_page = self::getTopPage();

        // 自分のページツリーの最後（root）にトップが入っていなければ、トップページをページツリーの最後に追加する
        if ($page_tree[count($page_tree)-1]->id != $top_page->id) {
            $page_tree->push($top_page);
        }

        // パスワードチェック
        foreach ($page_tree as $page) {
            // パスワードがあるページをチェックする。
            if (empty($page->password)) {
                continue;
            }
            if ($page->password == $password) {
                return true;
            }
        }
        return false;
    }

    /**
     * 表示する子ページが存在する
     */
    public function existChildrenPagesToDisplay($children)
    {
        $display_children = $children->where('display_flag', 1);
        return $display_children->isNotEmpty();
    }

    /**
     * アクティブ・マークを付けるページID の算出
     *
     * アクティブ・マークを付けるページとは、以下の条件を満たすもの
     * 選択されているページ＆表示中のページ
     * 選択されているページが非表示の場合、表示されている中での、最後の上位階層のページ
     *
     * プログラムでの、値の見つけ方
     * PHP の参照変数を宣言し、アクティブ・マークのページIDを保持する。
     * 再帰関数でページを順に見ていき、条件に合致するページIDで参照変数を上書きながら進む
     * 残ったページIDがアクティブ・マークのページになる
     *
     * (resources/views/plugins/user/menus/tab_flat/menus.blade.php より移動
     *  1ページに複数tab_flatを配置すると、function factorial()の重複定義エラーになるため。)
     */
    public static function getTabFlatActivePageId($pages, $ancestors, $page_roles): int
    {
        $active_page_id = 0;
        foreach ($pages as $page) {
            self::factorial($page, $ancestors, $page_roles, $active_page_id);
        }
        return $active_page_id;
    }

    /**
     * アクティブ・マークを付けるページID を探す再帰関数
     */
    private static function factorial($page, $ancestors, $page_roles, int &$active_page_id): void
    {
        if ($page->isView(Auth::user(), false, true, $page_roles)) {
            if ($ancestors->contains('id', $page->id)) {
                $active_page_id = $page->id;
            }
        }

        if (count($page->children) > 0) {
            foreach ($page->children as $children) {
                self::factorial($children, $ancestors, $page_roles, $active_page_id);
            }
        }
        return;
    }
}
