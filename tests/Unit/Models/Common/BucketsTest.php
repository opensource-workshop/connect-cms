<?php

namespace Tests\Unit\Models\Common;

use App\Models\Common\Buckets;
use App\Models\Common\BucketsRoles;
use App\Models\Common\Frame;
use App\Models\Common\Group;
use App\Models\Common\GroupUser;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Buckets モデルのユニットテスト
 *
 * canPostUser() / needApprovalUser() はページ権限による昇格をマージしてから
 * 判定する必要があるため、以下を個別に検証する。
 *  (i)   BASE 権限モデレータ
 *  (ii)  ページ権限昇格モデレータ
 *  (iii) 未ログイン
 *  (iv)  $frame = null のフォールバック
 */
class BucketsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 初期設定
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * サービスコンテナにページ文脈付きの Request を注入する。
     * Buckets::canPostUser() / needApprovalUser() が参照する attributes をここで仕込む。
     */
    private function bindRequestWithPage(?Page $page): Request
    {
        $request = new Request();
        if ($page) {
            $request->attributes->set('page', $page);
            $request->attributes->set('page_tree', collect([$page]));
        }
        $this->app->instance(Request::class, $request);
        return $request;
    }

    /**
     * 指定ロールに投稿権限 ON ／承認要否を任意指定したバケツを作る。
     */
    private function createBucketWithRole(string $role, int $post_flag = 1, int $approval_flag = 0): Buckets
    {
        $bucket = Buckets::factory()->create();
        BucketsRoles::create([
            'buckets_id' => $bucket->id,
            'role' => $role,
            'post_flag' => $post_flag,
            'approval_flag' => $approval_flag,
        ]);
        return $bucket;
    }

    /**
     * グループに所属するユーザを作り、指定ページ／ロールのページ権限を付与する。
     */
    private function grantPageRoleViaGroup(User $user, Page $page, string $role_name): void
    {
        $group = Group::factory()->create();
        GroupUser::create(['group_id' => $group->id, 'user_id' => $user->id]);
        PageRole::create([
            'page_id' => $page->id,
            'group_id' => $group->id,
            'target' => 'base',
            'role_name' => $role_name,
            'role_value' => 1,
        ]);
    }

    /**
     * (i) BASE 権限モデレータ：BASE ロールに直接投稿権限があるユーザは投稿可能
     */
    public function testCanPostUserReturnsTrueWhenBaseRoleGrantsPost(): void
    {
        $bucket = $this->createBucketWithRole('role_article');
        $this->bindRequestWithPage(null);

        $user = User::factory()->create();
        $user->user_roles = ['base' => ['role_article' => 1]];

        $this->assertTrue($bucket->canPostUser($user));
    }

    /**
     * (ii) ページ権限昇格モデレータ：BASE はゲストでも、ページ権限昇格で投稿可能になる
     *
     * 本 PR の主要な回帰防止ポイント。
     */
    public function testCanPostUserReturnsTrueWhenPageRoleElevatesGuestToPostableRole(): void
    {
        $bucket = $this->createBucketWithRole('role_article');

        $page = Page::factory()->create();
        Page::fixTree();
        $page->refresh();

        $user = User::factory()->create();
        $user->user_roles = ['base' => ['role_guest' => 1]];

        $this->grantPageRoleViaGroup($user, $page, 'role_article');

        $frame = new Frame(['page_id' => $page->id]);
        $this->bindRequestWithPage($page);

        $this->assertTrue($bucket->canPostUser($user, $frame));
    }

    /**
     * (iii) 未ログイン：$user が null の場合は常に false
     */
    public function testCanPostUserReturnsFalseForUnauthenticatedUser(): void
    {
        $bucket = $this->createBucketWithRole('role_article');
        $this->bindRequestWithPage(null);

        $this->assertFalse($bucket->canPostUser(null));
    }

    /**
     * (iv) $frame = null のフォールバック：フレーム未指定でも $request の page 文脈から昇格を解決できる
     */
    public function testCanPostUserResolvesPageRoleWithoutFrame(): void
    {
        $bucket = $this->createBucketWithRole('role_article');

        $page = Page::factory()->create();
        Page::fixTree();
        $page->refresh();

        $user = User::factory()->create();
        $user->user_roles = ['base' => ['role_guest' => 1]];

        $this->grantPageRoleViaGroup($user, $page, 'role_article');

        $this->bindRequestWithPage($page);

        // 第2引数 $frame を渡さず、デフォルト null でも同等に解決できる事
        $this->assertTrue($bucket->canPostUser($user));
    }

    /**
     * ネガティブケース：BASE ゲストのみ・ページ権限昇格なしのユーザは投稿不可
     */
    public function testCanPostUserReturnsFalseForGuestWithoutPageElevation(): void
    {
        $bucket = $this->createBucketWithRole('role_article');
        $this->bindRequestWithPage(null);

        $user = User::factory()->create();
        $user->user_roles = ['base' => ['role_guest' => 1]];

        $this->assertFalse($bucket->canPostUser($user));
    }

    /**
     * コンパニオンテスト：needApprovalUser() もページ権限昇格を認識して承認要否を正しく返す事
     *
     * canPostUser() と同じ前段処理（ConnectRoleTrait 経由）を使うため、
     * 将来 canPostUser() の修正が波及した際に回帰検知できるよう双子テストとして残す。
     */
    public function testNeedApprovalUserRecognizesPageRoleElevation(): void
    {
        // バケツは role_article に対して「承認不要（approval_flag = 0）」
        $bucket = $this->createBucketWithRole('role_article', 1, 0);

        $page = Page::factory()->create();
        Page::fixTree();
        $page->refresh();

        $user = User::factory()->create();
        $user->user_roles = ['base' => ['role_guest' => 1]];

        $this->grantPageRoleViaGroup($user, $page, 'role_article');

        $frame = new Frame(['page_id' => $page->id]);
        $this->bindRequestWithPage($page);

        // ページ権限昇格が認識されなければ「BASE ゲストのみ」→ needApprovalUser は true のままになる。
        // 正しく昇格が効けば role_article の approval_flag=0 により承認不要 (false) となる。
        $this->assertFalse($bucket->needApprovalUser($user, $frame));
    }
}
