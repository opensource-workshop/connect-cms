<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Models\Common\Frame;

use App\User;
use App\Models\Common\Page;
use App\Models\Common\PageRole;

trait ConnectRoleTrait
{
    /**
     * ユーザーが指定された役割を保持しているかチェックする。
     * (ConnectCommonTraitから移動してきた)
     *
     * @return boolean
     */
    public function checkRole($user, $role)
    {
        // ログインしていない場合は権限なし
        if (empty($user)) {
            return false;
        }

        $request = app(Request::class);

        // app\Http\Middleware\ConnectPage.php でセットした値
        // bugfix: $request->get(); で $request->attributes の値をとっては「いけなかった」。$request->get()は、
        //         $request->attributes, $request->query, $request->request の順に値を取得しているため、
        //         $request->get('page'); とすると、ユーザ管理のページネーション（http://localhost/manage/user?page=2）とした時、page="2"が取得できバグった。
        // $page = $request->get('page');
        // $page_tree = $request->get('page_tree');
        $page = $request->attributes->get('page');
        $page_tree = $request->attributes->get('page_tree');

        // 自分のページから親を遡ってページロールを取得
        $page_roles = $this->getPageRolesByGoingBackParent($page, $page_tree);

        // 指定された権限を含むロールをループする。
        return $this->checkRoleHierarchy($user, $role, $page_roles);
    }

    /**
     * 自分のページから親を遡ってページロールを取得
     */
    private function getPageRolesByGoingBackParent($page, ?Collection $page_tree) : Collection
    {
        // dd($page, $page_tree);
        if (!$page && is_null($page_tree)) {
            // ログインのPOST時（http://localhost/login）に どちらもnullなので 空コレクションを返す
            // 管理画面(http://localhost/manage)で $page=false & $page_tree=null になるので 空コレクションを返す
            return collect();
        }

        $page = $page ?? new Page();

        // 自分のページから親を遡って取得
        $page_tree = $page->getPageTreeByGoingBackParent($page_tree);

        // 自分及び先祖ページにグループ権限が設定されていなければ戻る
        $page_roles = collect();
        foreach ($page_tree as $page) {
            if (! $page->page_roles->isEmpty()) {
                $page_roles = $page->page_roles;
                break;
            }
        }
        // dd($page_roles);

        return $page_roles;
    }

    /**
     * 自分のページから親を遡ってコンテナページを取得
     */
    private function getContainerPageByGoingBackParent($page, ?Collection $page_tree) : ?Page
    {
        if (!$page && is_null($page_tree)) {
            // ログインのPOST時（http://localhost/login）に どちらもnullなので 空コレクションを返す
            // 管理画面(http://localhost/manage)で $page=false & $page_tree=null になるので 空コレクションを返す
            return collect();
        }

        $page = $page ?? new Page();

        // 自分のページから親を遡って取得（＋トップページ）
        $page_tree = $page->getPageTreeByGoingBackParent($page_tree);

        // 自分及び先祖ページにグループ権限が設定されていなければ戻る
        $container_page = null;
        foreach ($page_tree as $page) {
            if ($page->container_flag) {
                $container_page = $page;
                break;
            }
        }

        return $container_page;
    }

    /**
     * フレームからさかのぼってページ権限を取得、ユーザーが指定された役割を保持しているかチェックする。
     */
    public function checkRoleFromFrame(?User $user, string $role, ?Frame $frame) : bool
    {
        // ログインしていない場合は権限なし
        if (empty($user)) {
            return false;
        }

        $request = app(Request::class);

        // app\Http\Middleware\ConnectPage.php でセットした値
        $page = $request->attributes->get('page');
        $page_tree = $request->attributes->get('page_tree');

        // フレームがあれば、フレームを配置したページから親を遡ってページロールを取得
        $page_roles = $this->choicePageRolesByGoingBackParentPageOrFramePage($page, $page_tree, $frame);

        // 指定された権限を含むロールをループする。
        return $this->checkRoleHierarchy($user, $role, $page_roles);
    }

    /**
     * フレームがあれば、フレームを配置したページから親を遡ってページロールを取得
     */
    public function choicePageRolesByGoingBackParentPageOrFramePage($page, ?Collection $page_tree, ?Frame $frame) : Collection
    {
        // frame->page_id を基にページロール取得
        // $page_roles = $page_roles ?? collect();
        // $page_roles = $page_roles->where('page_id', $frame->page_id);
        // \Log::debug(var_export($page_roles, true));

        // \Log::debug('[' . __METHOD__ . '] ' . __FILE__ . ' (line ' . __LINE__ . ')');
        // \Log::debug(var_export($frame->page_id, true));
        // \Log::debug(var_export($page->id, true));

        if (empty($frame)) {
            // フレームがセットされてないため、なにも変更しない

        } elseif ($page->id === $frame->page_id) {
            // フレームを配置したページと同じ（メインエリア等）
            // 自ページのため、なにも変更しない

        } else {
            // フレームを配置したページと違う（ヘッダーエリアや左エリア等）
            // フレームの配置ページIDから、親を遡らせる。
            $page = new Page();
            $page->id = $frame->page_id;

            // nullを指定する事で、フレームの配置ページから親を遡ってページツリーを再取得する。
            $page_tree = null;
        }

        // ページから親を遡ってページロールを取得
        $page_roles = $this->getPageRolesByGoingBackParent($page, $page_tree);

        return $page_roles;
    }

    /**
     * フレームがあれば、フレームを配置したページから親を遡ってコンテナページを取得
     */
    public function choiceContainerPageByGoingBackParentPageOrFramePage($page, ?Collection $page_tree, ?Frame $frame) : ?Page
    {
        if (empty($frame)) {
            // フレームがセットされてないため、なにも変更しない

        } elseif ($page->id === $frame->page_id) {
            // フレームを配置したページと同じ（メインエリア等）
            // 自ページのため、なにも変更しない

        } else {
            // フレームを配置したページと違う（ヘッダーエリアや左エリア等）
            // フレームの配置ページIDから、親を遡らせる。
            $page = new Page();
            $page->id = $frame->page_id;

            // nullを指定する事で、フレームの配置ページから親を遡ってページツリーを再取得する。
            $page_tree = null;
        }

        // ページから親を遡ってコンテナページを取得
        $container_page = $this->getContainerPageByGoingBackParent($page, $page_tree);

        return $container_page;
    }

    /**
     * 指定された権限を含むロールをループしてチェックする。
     */
    public function checkRoleHierarchy(User $user, ?string $role, ?Collection $page_roles): bool
    {
        // ユーザロール取得。所属グループのページ権限あったら、そっちからとる
        $user_roles = $this->choiceUserRolesOrPageRoles($user, $page_roles);

        // 指定された権限を含むロールをループする。
        // 記事追加はコンテンツ管理者でもOKのような処理のため。
        foreach (config('cc_role.CC_ROLE_HIERARCHY')[$role] as $check_role) {
            // ユーザの保持しているロールをループ
            // foreach ((array)$user->user_roles as $target) {
            foreach ($user_roles as $target) {
                // ターゲット処理をループ
                foreach ($target as $user_role => $user_role_value) {
                    // 必要なロールを保持している場合は、権限ありとして true を返す。
                    if ($check_role == $user_role && $user_role_value) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * ユーザロール取得。所属グループのページ権限あったら、そっちからとる
     *
     * @see \App\Models\Core\UsersRoles ::getUsersRoles()
     */
    public function choiceUserRolesOrPageRoles(User $user, ?Collection $page_roles): array
    {
        // $user_roles[target][role_name] = role_value;
        // $user_roles['base'] = ['role_reporter' => 1];    // ←ページ権限あれば上書き
        // $user_roles['manage'] = ['admin_system' => 1];   // ←ページ権限ではセットしてないので、そのまま
        $user_roles = $user->user_roles;

        // 所属グループのページ権限取得
        $user_page_roles = $this->getUserPageRoles($user, $page_roles);

        // 所属グループのページ権限があったら、ユーザロールをページ権限に差替える
        if (isset($user_page_roles['base'])) {
            // ページ権限はbase(コンテンツ権限)のみ。manage(管理権限)はセットしてない。
            $user_roles['base'] = $user_page_roles['base'];
        }

        return $user_roles;
    }

    /**
     * 所属グループのページ権限取得
     */
    private function getUserPageRoles(User $user, ?Collection $page_roles): array
    {
        $user_page_roles_array = [];

        if (is_null($page_roles) || $page_roles->isEmpty()) {
            return $user_page_roles_array;
        }

        // 所属グループのページ権限取得
        // ユーザからグループID取得
        $user_group_ids = $user->group_users->pluck('group_id');

        // ページ権限から、所属しているグループの権限取得
        $user_page_roles = $page_roles->whereIn('group_id', $user_group_ids);
        // dd($user_page_roles);

        // user_rolesと同じ配列に変換
        // ※ グループに複数所属していて、両方のグループに権限が設定されていたら、両方ともの権限を持ちます。
        //    例）Aグループ：モデレータ, 編集者
        //        Bグループ：編集者, ゲスト
        //        => 両方のグループ所属のユーザ権限は、モデレータ, 編集者, ゲスト
        $user_page_roles_array = PageRole::rolesToArray($user_page_roles);
        // dd($user_page_roles_array);

        return $user_page_roles_array;
    }
}
