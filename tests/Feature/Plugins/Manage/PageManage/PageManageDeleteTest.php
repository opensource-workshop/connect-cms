<?php

namespace Tests\Feature\Plugins\Manage\PageManage;

use App\Models\Common\Page;
use App\Models\Core\UsersRoles;
use App\Plugins\Manage\PageManage\PageManage;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * ページ管理画面からの移動と削除で子ページが取り残されないことを確認する。
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PageManageDeleteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 子がいる親を編集すると、子ページの移動を案内する。
     */
    public function testParentEditShowsHowToMoveChildrenBeforeDeletion(): void
    {
        [$user, $parent] = $this->createPageTree();

        $this->actingAs($user)->get('/manage/page/edit/' . $parent->id)
            ->assertOk()
            ->assertSee('子ページがあるため削除できません。')
            ->assertDontSee('本当に削除する');
    }

    /**
     * 子がいる間は、削除のリクエストを送信しても親子ともに残る。
     */
    public function testParentCannotBeDeletedWhileItHasChildren(): void
    {
        [$user, $parent, $child] = $this->createPageTree();

        $this->actingAs($user)->post('/manage/page/destroy/' . $parent->id)
            ->assertRedirect('/manage/page')
            ->assertSessionHasErrors('page_delete');

        $this->assertDatabaseHas('pages', ['id' => $parent->id]);
        $this->assertDatabaseHas('pages', ['id' => $child->id, 'parent_id' => $parent->id]);
    }

    /**
     * 子が最上位へ移動済みなら、元の親だけを削除できる。
     */
    public function testParentCanBeDeletedAfterItsChildBecomesARootPage(): void
    {
        [$user, $parent, $child] = $this->createPageTree();
        $child->saveAsRoot();
        $child->recalcDepthWithDescendants();

        $this->actingAs($user)->post('/manage/page/destroy/' . $parent->id)
            ->assertRedirect('/manage/page')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('pages', ['id' => $parent->id]);
        $this->assertDatabaseHas('pages', [
            'id' => $child->id,
            'parent_id' => null,
            'permanent_link' => '/child-for-delete',
        ]);
    }

    /**
     * 親を失ったページは一覧に表示され、移動操作で復旧できることが分かる。
     */
    public function testPageListShowsRecoveryInstructionsForOrphanedPage(): void
    {
        [$user, $parent, $child] = $this->createPageTree();
        Page::whereKey($parent->id)->delete();

        $this->actingAs($user)->get('/manage/page')
            ->assertOk()
            ->assertSee('対応が必要なページが1件あります')
            ->assertSee('閲覧者がメニューからページにたどり着けないことがあります。')
            ->assertSee('公開中のページを優先して')
            ->assertSee('親ページなし')
            ->assertSee('対象ページを表示（1件）')
            ->assertSee('href="#' . $child->id . '"', false)
            ->assertSee('id="move_level_' . $child->id . '"', false);
    }

    /**
     * 対象が多い場合は初期表示で一覧を閉じ、展開すれば各ページ行へのリンクを使える。
     */
    public function testOrphanPageLinksAreCollapsedWhenThereAreMany(): void
    {
        [$user, $parent, $child] = $this->createPageTree();
        for ($index = 1; $index <= 5; $index++) {
            $page = Page::factory()->make(['permanent_link' => '/orphan-for-list-' . $index]);
            $page->appendToNode($parent)->save();
        }
        Page::whereKey($parent->id)->delete();

        $response = $this->actingAs($user)->get('/manage/page');
        $response->assertOk()->assertSeeInOrder([
            '<summary>対象ページを表示（6件）</summary>',
            'href="#' . $child->id . '"',
            '</details>',
        ], false);
        $this->assertMatchesRegularExpression(
            '/<details\s*>\s*<summary>対象ページを表示（6件）<\/summary>/u',
            $response->getContent()
        );
    }

    /**
     * 独立した公開ページは、元の親を削除した後も未ログインで固定リンクから閲覧できる。
     */
    public function testIndependentPublicPageRemainsAccessibleByItsPermanentLink(): void
    {
        [, $parent, $child] = $this->createPageTree();
        $child->update(['base_display_flag' => 1]);
        $page_manager = new PageManage();
        $page_manager->movePage(new Request(['destination_id' => '0']), $child->id);
        $page_manager->destroy(new Request(), $parent->id);

        $this->get($child->permanent_link)->assertOk();
    }

    /**
     * 管理画面の操作に必要なページ管理者と親子ページを用意する。
     */
    private function createPageTree(): array
    {
        $this->seed();
        $user = User::factory()->create();
        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'manage',
            'role_name' => 'admin_page',
            'role_value' => 1,
        ]);
        $parent = Page::factory()->create(['permanent_link' => '/parent-for-delete']);
        $child = Page::factory()->make(['permanent_link' => '/child-for-delete']);
        $child->appendToNode($parent)->save();

        return [$user, $parent, $child];
    }
}
