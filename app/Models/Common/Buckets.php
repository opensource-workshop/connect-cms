<?php

namespace App\Models\Common;

use App\Models\Common\BucketsRoles;
use App\Models\Core\Configs;
use App\Traits\ConnectRoleTrait;
use Database\Factories\Common\BucketsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Buckets extends Model
{
    use ConnectRoleTrait;
    use HasFactory;

    /**
     * 新規バケツの投稿権限初期値に対応するConfig名
     */
    private const DEFAULT_NEW_BUCKET_POST_ROLE_CONFIGS = [
        'role_article' => 'new_bucket_role_article_post_flag',
        'role_reporter' => 'new_bucket_role_reporter_post_flag',
    ];

    /**
     * 新規バケツの投稿権限初期値を適用する対象プラグイン
     */
    private const DEFAULT_POST_ROLE_TARGET_PLUGINS = [
        'bbses',
        'blogs',
        'cabinets',
        'calendars',
        'contents',
        'databases',
        'faqs',
        'photoalbums',
        'reservations',
        'slideshows',
    ];

    /**
     * create()やupdate()で入力を受け付ける ホワイトリスト
     */
    protected $fillable = [
        'id',
        'bucket_name',
        'plugin_name',
        'container_page_id',
    ];

    // Buckets のrole
    private $buckets_roles = null;

    protected static function booted()
    {
        static::deleting(function ($bucket) {
            BucketsRoles::where('buckets_id', $bucket->id)->delete();
        });
    }

    /**
     * 投稿権限データをrole の配列で返却
     */
    public function getPostArrayBucketsRoles()
    {
        // Buckets に対するrole の取得
        $this->getBucketsRoles();

        if (empty($this->buckets_roles)) {
            return array();
        }
        $return_roles = array();
        foreach ($this->buckets_roles as $buckets_role) {
            if ($buckets_role->post_flag == 1) {
                $return_roles[] = $buckets_role->role;
            }
        }
        return $return_roles;
    }

    /**
     * Buckets に対するrole の取得
     */
    public function getBucketsRoles()
    {
//echo $this->id;
        // すでに読み込み済みならば、読み込み済みデータを返す
        // 権限更新の際に、更新前データを保持 ＞ 更新でデータ変わる ＞ 古い状態を使用になるので、毎回、読む形に変更
        //if ($this->buckets_roles) {
        //    return $this->editArrayBucketsRoles();
        //}

        $this->buckets_roles = BucketsRoles::where('buckets_id', $this->id)
                                           ->get();

        // delete: editArrayBucketsRoles()内で、getBucketsRoles()を呼び出すように見直し
        // return $this->editArrayBucketsRoles();
    }

    /**
     * 投稿権限の保持確認
     */
    public function canPost($role)
    {
        // すでに読み込み済みならば、読み込み済みデータを返す
        // 権限更新の際に、更新前データを保持 ＞ 更新でデータ変わる ＞ 古い状態を使用になるので、毎回、読む形に変更
        //if ($this->buckets_roles == null) {
        //    $this->getBucketsRoles();
        //}

        // Buckets に対するrole の取得
        $this->getBucketsRoles();

        // 渡された権限がバケツに投稿できる権限かどうかのチェック
        foreach ($this->buckets_roles as $buckets_role) {
            if ($buckets_role->role == $role && $buckets_role->post_flag == 1) {
                return true;
            }
        }
        return false;

//        $roles = explode(',', $this->post_role);
//        return in_array($role, $roles);
    }

    /**
     * ユーザーの投稿権限の有無確認
     */
    public function canPostUser($user, $frame = null)
    {
        // ユーザーの BASE 権限（ページ権限で昇格した場合も含む）を全て確認し、ひとつでも投稿権限があれば、投稿可能となる。
        if (empty($user)) {
            return false;
        }

        $request = app(Request::class);

        // app\Http\Middleware\ConnectPage.php でセットした値
        $page = $request->attributes->get('page');
        $page_tree = $request->attributes->get('page_tree');

        // フレームがあれば、フレームを配置したページから親を遡ってページロールを取得
        $page_roles = $this->choicePageRolesByGoingBackParentPageOrFramePage($page, $page_tree, $frame);

        // ユーザロール取得。所属グループのページ権限あったら、そっちからとる
        $user_roles = $this->choiceUserRolesOrPageRoles($user, $page_roles);

        if (!array_key_exists('base', (array)$user_roles)) {
            return false;
        }

        $user_roles_base = $user_roles['base'];
        if (empty($user_roles_base)) {
            return false;
        }

        foreach ($user_roles_base as $user_role => $value) {
            // コンテンツ管理者権限、プラグイン管理者権限があれば、投稿可能
            if ($user_role == 'role_article_admin' || $user_role == 'role_arrangement') {
                return true;
            }

            // 保持している権限の内、ひとつでも投稿可能なら、投稿OK
            if ($value == 1) {
                $can_post = $this->canPost($user_role);
                if ($can_post == true) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 承認の有無確認
     */
    public function needApproval($role)
    {
        // Buckets に対するrole の取得
        // 権限更新の際に、更新前データを保持 ＞ 更新でデータ変わる ＞ 古い状態を使用になるので、毎回、読む形に変更
        //if ($this->buckets_roles == null) {
        //    $this->getBucketsRoles();
        //}

        // Buckets に対するrole の取得
        $this->getBucketsRoles();

        // 渡された権限が承認が必要かどうかのチェック
        foreach ($this->buckets_roles as $buckets_role) {
            if ($buckets_role->role == $role && $buckets_role->approval_flag == 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * ユーザーの承認の有無確認
     */
    public function needApprovalUser($user, $frame = null)
    {
        // ユーザーの持つBASE権限を全て確認し、全てで承認が必要な場合、承認が必要となる。
        // (権限のひとつでも、承認が不要な場合は、承認は不要になる)
        if (empty($user)) {
            return false;
        }

        $request = app(Request::class);

        // app\Http\Middleware\ConnectPage.php でセットした値
        $page = $request->attributes->get('page');
        $page_tree = $request->attributes->get('page_tree');
        // dd($page, $page->page_roles);

        // フレームがあれば、フレームを配置したページから親を遡ってページロールを取得
        $page_roles = $this->choicePageRolesByGoingBackParentPageOrFramePage($page, $page_tree, $frame);

        // ユーザロール取得。所属グループのページ権限あったら、そっちからとる
        $user_roles = $this->choiceUserRolesOrPageRoles($user, $page_roles);

        if (!array_key_exists('base', (array)$user_roles)) {
            return false;
        }

        // $user_roles_base = $user->user_roles['base'];
        $user_roles_base = $user_roles['base'];
        if (empty($user_roles_base)) {
            return false;
        }

        foreach ($user_roles_base as $user_role => $value) {
            // 承認チェックしない
            // ・ゲスト権限は見るだけユーザのため
            // ・プラグイン管理者権限に記事投稿権限はないため
            if ($user_role == 'role_guest' || $user_role == 'role_arrangement') {
                continue;
            }

            // コンテンツ管理者権限、承認者権限があれば、承認不要
            if ($user_role == 'role_article_admin' || $user_role == 'role_approval') {
                return false;
            }

            // 保持している権限の内、ひとつでも承認不要なら、承認不要
            if ($value == 1) {
                $role_approval = $this->needApproval($user_role);
                if ($role_approval == false) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 新規バケツ作成時の投稿権限初期値を適用する。
     */
    public function initializeDefaultPostRoles(): void
    {
        $default_post_role_flags = self::getDefaultNewBucketPostRoleFlags();

        foreach ($default_post_role_flags as $role => $post_flag) {
            if ((int) $post_flag !== 1) {
                continue;
            }

            BucketsRoles::firstOrCreate(
                [
                    'buckets_id' => $this->id,
                    'role' => $role,
                ],
                [
                    'post_flag' => 1,
                    'approval_flag' => 0,
                ]
            );
        }
    }

    /**
     * 投稿権限設定を持つプラグインの新規バケツなら、投稿権限初期値を適用する。
     */
    public function initializeDefaultPostRolesIfTargetPlugin(): void
    {
        if (!self::isDefaultPostRoleTargetPlugin($this->plugin_name)) {
            return;
        }

        $this->initializeDefaultPostRoles();
    }

    /**
     * 新規バケツを作成し、対象プラグインだけ投稿権限初期値を適用する。
     */
    public static function createWithDefaultPostRoles(array $attributes): self
    {
        $bucket = self::create($attributes);
        $bucket->initializeDefaultPostRolesIfTargetPlugin();

        return $bucket;
    }

    /**
     * バケツを作成または更新し、新規作成時だけ投稿権限初期値を適用する。
     */
    public static function updateOrCreateWithDefaultPostRoles(array $attributes, array $values = []): self
    {
        $bucket = self::updateOrCreate($attributes, $values);

        if ($bucket->wasRecentlyCreated) {
            $bucket->initializeDefaultPostRolesIfTargetPlugin();
        }

        return $bucket;
    }

    /**
     * 新規バケツの投稿権限初期値を適用する対象プラグインか判定する。
     */
    public static function isDefaultPostRoleTargetPlugin(?string $plugin_name): bool
    {
        return in_array($plugin_name, self::DEFAULT_POST_ROLE_TARGET_PLUGINS, true);
    }

    /**
     * 新規バケツ作成時の投稿権限初期値を取得する。
     */
    public static function getDefaultNewBucketPostRoleFlags(): array
    {
        $config_names = array_values(self::DEFAULT_NEW_BUCKET_POST_ROLE_CONFIGS);
        $configs = Configs::getSharedConfigs();

        if ($configs->isEmpty()) {
            $configs = Configs::whereIn('name', $config_names)->get();
        }

        return self::resolveDefaultNewBucketPostRoleFlags($configs);
    }

    /**
     * Config群から新規バケツ作成時の投稿権限初期値を解決する。
     */
    private static function resolveDefaultNewBucketPostRoleFlags($configs): array
    {
        $configs = collect($configs);

        return [
            'role_article' => (int) Configs::getConfigsValue($configs, self::DEFAULT_NEW_BUCKET_POST_ROLE_CONFIGS['role_article'], 0),
            'role_reporter' => (int) Configs::getConfigsValue($configs, self::DEFAULT_NEW_BUCKET_POST_ROLE_CONFIGS['role_reporter'], 0),
        ];
    }

    protected static function newFactory()
    {
        return BucketsFactory::new();
    }
}
