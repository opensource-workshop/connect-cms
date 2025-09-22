<?php

namespace Tests\Feature\Plugins\User\Menus;

use App\Enums\MenuFrameConfig;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Core\UsersRoles;
use App\Models\User\Menus\Menu;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenusModeratorEditFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\Common\Page */
    private $page;

    /** @var \App\Models\Common\Frame */
    private $frame;

    /** @var string */
    private $select_url;

    /** @var string */
    private $save_select_url;

    /** @var string */
    private $index_url;

    /** @var string */
    private $save_frame_roles_url;

    /** @var string */
    private $edit_frame_roles_url;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->page = Page::where('permanent_link', '/')->first();
        if (!$this->page) {
            $this->page = Page::factory()->create([
                'permanent_link' => '/',
                'page_name' => 'home',
            ]);
        }

        $this->frame = Frame::factory()->create([
            'page_id' => $this->page->id,
            'area_id' => 2,
            'plugin_name' => 'menus',
            'bucket_id' => null,
            'template' => 'default',
            'display_sequence' => 1,
        ]);

        $this->frame->frame_id = $this->frame->id;

        $this->select_url = "/plugin/menus/select/{$this->page->id}/{$this->frame->id}";
        $this->save_select_url = "/plugin/menus/saveSelect/{$this->page->id}/{$this->frame->id}";
        $this->index_url = "/plugin/menus/index/{$this->page->id}/{$this->frame->id}";
        $this->save_frame_roles_url = "/redirect/plugin/menus/saveFrameRoles/{$this->page->id}/{$this->frame->id}";
        $this->edit_frame_roles_url = "/plugin/menus/editFrameRoles/{$this->page->id}/{$this->frame->id}";
    }

    /**
     * モデレーターロールのユーザーを作成する。
     */
    private function createModeratorUser(): User
    {
        return $this->createUserWithRole('role_article');
    }

    /**
     * コンテンツ管理者ロールのユーザーを作成する。
     */
    private function createAdminUser(): User
    {
        return $this->createUserWithRole('role_article_admin');
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();

        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'base',
            'role_name' => $role,
            'role_value' => 1,
        ]);

        return $user;
    }

    private function enableModeratorEdit(): void
    {
        $admin = $this->createAdminUser();
        $redirect_path = url($this->edit_frame_roles_url . "#frame-{$this->frame->id}");

        $response = $this->actingAs($admin)->post($this->save_frame_roles_url, [
            'redirect_path' => $redirect_path,
            MenuFrameConfig::menu_allow_moderator_edit => 1,
        ]);

        $response->assertStatus(302);
    }

    /**
     * モデレータは許可が無い場合、ページ選択画面にアクセスできない。
     */
    public function testModeratorCannotAccessSelectionWhenNotAllowed(): void
    {
        $user = $this->createModeratorUser();

        $response = $this->actingAs($user)->get($this->select_url);

        $response->assertStatus(200);
        $response->assertSee('権限がありません');
        $this->assertEquals(0, Menu::count());

        $post_response = $this->actingAs($user)->post($this->save_select_url, [
            'select_flag' => 1,
            'page_select' => [$this->page->id],
            'folder_close_font' => 0,
            'folder_open_font' => 0,
            'indent_font' => 0,
        ]);

        $post_response->assertStatus(200);
        $post_response->assertSee('権限がありません');
        $this->assertEquals(0, Menu::count());
    }

    /**
     * モデレータは許可設定が有効であれば編集できる。
     */
    public function testModeratorCanEditWhenAllowed(): void
    {
        $this->enableModeratorEdit();
        $user = $this->createModeratorUser();

        $response = $this->actingAs($user)->get($this->select_url);
        $response->assertStatus(200);
        // ページ選択の画面が表示されることを確認
        $response->assertSee('ページの表示');
        $response->assertDontSee('設定メニュー');

        $payload = [
            'select_flag' => 1,
            'page_select' => [$this->page->id],
            'folder_close_font' => 0,
            'folder_open_font' => 0,
            'indent_font' => 0,
        ];

        $post_response = $this->actingAs($user)->post($this->save_select_url, $payload);

        $post_response->assertStatus(200);

        $menu = Menu::where('frame_id', $this->frame->id)->first();
        $this->assertNotNull($menu);
        $this->assertEquals(1, $menu->select_flag);
        $this->assertEquals((string) $this->page->id, $menu->page_ids);
        $this->assertEquals(0, $menu->folder_close_font);
        $this->assertEquals(0, $menu->folder_open_font);
        $this->assertEquals(0, $menu->indent_font);
    }

    /**
     * 管理者は設定に関係なく編集できる。
     */
    public function testAdminCanEditWithoutModeratorSetting(): void
    {
        $user = $this->createAdminUser();

        $response = $this->actingAs($user)->get($this->select_url);
        $response->assertStatus(200);
        // ページ選択の画面が表示されることを確認
        $response->assertSee('ページの表示');
        $response->assertSee('設定メニュー');
    }

    /**
     * 権限設定保存がフレーム設定を更新し、フラッシュメッセージを残す。
     */
    public function testSaveFrameRolesPersistsSettingAndFlashesMessage(): void
    {
        $user = $this->createAdminUser();
        $redirect_path = url($this->edit_frame_roles_url . "#frame-{$this->frame->id}");

        $response = $this->actingAs($user)->post($this->save_frame_roles_url, [
            'redirect_path' => $redirect_path,
            MenuFrameConfig::menu_allow_moderator_edit => 1,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect($redirect_path);
        $response->assertSessionHas("flash_message_for_frame{$this->frame->id}", '更新しました。');

        $this->assertDatabaseHas('frame_configs', [
            'frame_id' => $this->frame->id,
            'name' => MenuFrameConfig::menu_allow_moderator_edit,
            'value' => '1',
        ]);

        $second_response = $this->actingAs($user)->post($this->save_frame_roles_url, [
            'redirect_path' => $redirect_path,
            MenuFrameConfig::menu_allow_moderator_edit => 0,
        ]);

        $second_response->assertStatus(302);
        $second_response->assertRedirect($redirect_path);
        $this->assertDatabaseHas('frame_configs', [
            'frame_id' => $this->frame->id,
            'name' => MenuFrameConfig::menu_allow_moderator_edit,
            'value' => '0',
        ]);
    }

    /**
     * モデレータは権限設定保存を実行できない。
     */
    public function testModeratorCannotSaveFrameRoles(): void
    {
        $user = $this->createModeratorUser();
        $redirect_path = url($this->edit_frame_roles_url . "#frame-{$this->frame->id}");

        $response = $this->actingAs($user)->post($this->save_frame_roles_url, [
            'redirect_path' => $redirect_path,
            MenuFrameConfig::menu_allow_moderator_edit => 1,
        ]);

        $response->assertStatus(200);
        $response->assertSee('権限がありません');

        $this->assertDatabaseMissing('frame_configs', [
            'frame_id' => $this->frame->id,
            'name' => MenuFrameConfig::menu_allow_moderator_edit,
        ]);
    }

    /**
     * 編集ボタンは権限に応じて表示が切り替わる。
     */
    public function testEditButtonVisibilityReflectsPermissions(): void
    {
        $moderator = $this->createModeratorUser();
        $admin = $this->createAdminUser();

        // 権限なしではモデレータに表示されない
        $response_for_moderator = $this->actingAs($moderator)->get('/');
        $response_for_moderator->assertStatus(200);
        $response_for_moderator->assertDontSee('menu-edit-button');

        // 管理者は設定が無くても表示される
        $response_for_admin = $this->actingAs($admin)->get('/');
        $response_for_admin->assertStatus(200);
        $response_for_admin->assertSee('menu-edit-button');

        // 権限を付与するとモデレータにも表示される
        $this->enableModeratorEdit();
        $permitted_response = $this->actingAs($moderator)->get('/');
        $permitted_response->assertStatus(200);
        $permitted_response->assertSee('menu-edit-button');
        $permitted_response->assertDontSee('設定メニュー');
    }
}
