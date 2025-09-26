<?php

namespace Tests\Feature\Plugins\User\Slideshows;

use App\Enums\ShowType;
use App\Models\Common\Buckets;
use App\Models\Common\BucketsRoles;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\Core\UsersRoles;
use App\Models\User\Slideshows\Slideshows;
use App\Models\User\Slideshows\SlideshowsItems;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlideshowsEditPermissionFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\Common\Page */
    private $page;

    /** @var \App\Models\Common\Frame */
    private $frame;

    /** @var \App\Models\Common\Buckets */
    private $bucket;

    /** @var \App\Models\User\Slideshows\Slideshows */
    private $slideshow;

    /** @var \App\Models\User\Slideshows\SlideshowsItems */
    private $item;

    /** @var string */
    private $index_path;

    /** @var string */
    private $edit_path;

    /** @var string */
    private $edit_button_url;

    /** @var string */
    private $update_items_path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->page = Page::where('permanent_link', '/')->first() ?? Page::factory()->create([
            'permanent_link' => '/',
            'page_name' => 'home',
        ]);

        $this->bucket = Buckets::factory()->create([
            'bucket_name' => 'テストスライド',
            'plugin_name' => 'slideshows',
        ]);

        $this->frame = Frame::factory()->create([
            'page_id' => $this->page->id,
            'area_id' => 2,
            'plugin_name' => 'slideshows',
            'bucket_id' => $this->bucket->id,
            'display_sequence' => 1,
        ]);

        $this->slideshow = Slideshows::create([
            'bucket_id' => $this->bucket->id,
            'slideshows_name' => 'テストスライド',
            'control_display_flag' => ShowType::show,
            'indicators_display_flag' => ShowType::show,
            'fade_use_flag' => ShowType::not_show,
            'image_interval' => 5000,
            'height' => null,
        ]);

        $upload = Uploads::factory()->jpg()->create([
            'plugin_name' => 'slideshows',
            'page_id' => $this->page->id,
        ]);

        $this->item = SlideshowsItems::create([
            'slideshows_id' => $this->slideshow->id,
            'image_path' => 'dummy/path.jpg',
            'uploads_id' => $upload->id,
            'link_url' => null,
            'link_target' => null,
            'caption' => null,
            'display_flag' => ShowType::show,
            'display_sequence' => 1,
        ]);

        foreach (['role_article', 'role_reporter'] as $role) {
            BucketsRoles::create([
                'buckets_id' => $this->bucket->id,
                'role' => $role,
                'post_flag' => 0,
                'approval_flag' => 0,
            ]);
        }

        $this->index_path = "/plugin/slideshows/index/{$this->page->id}/{$this->frame->id}";
        $this->edit_path = "/plugin/slideshows/editItem/{$this->page->id}/{$this->frame->id}";
        $this->edit_button_url = sprintf('%s%s#frame-%d', url('/'), $this->edit_path, $this->frame->id);
        $this->update_items_path = "/redirect/plugin/slideshows/updateItems/{$this->page->id}/{$this->frame->id}";
    }

    private function enableModeratorEdit(): void
    {
        BucketsRoles::where('buckets_id', $this->bucket->id)
            ->where('role', 'role_article')
            ->update(['post_flag' => 1]);
    }

    private function enableEditorEdit(): void
    {
        BucketsRoles::where('buckets_id', $this->bucket->id)
            ->where('role', 'role_reporter')
            ->update(['post_flag' => 1]);
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

    private function buildUpdatePayload(string $caption, string $link_url): array
    {
        return [
            'redirect_path' => $this->edit_button_url,
            'slideshows_id' => $this->slideshow->id,
            'display_flags' => [$this->item->id => ShowType::show],
            'link_urls' => [$this->item->id => $link_url],
            'captions' => [$this->item->id => $caption],
            'link_targets' => [$this->item->id => '_self'],
        ];
    }

    /**
     * 権限設定しないとモデレータは編集画面にも公開画面の編集ボタンにもアクセスできないこと。
     */
    public function testModeratorCannotAccessEditScreen(): void
    {
        $user = $this->createUserWithRole('role_article');

        $index_response = $this->actingAs($user)->get($this->index_path);
        $index_response->assertStatus(200);
        $index_response->assertDontSee($this->edit_button_url);

        $edit_response = $this->actingAs($user)->get($this->edit_path);
        $edit_response->assertStatus(200);
        $edit_response->assertSee('権限がありません');
    }

    /**
     * モデレータは権限設定すると編集画面にも公開画面の編集ボタンにもアクセスできること。
     */
    public function testModeratorCanAccessEditScreen(): void
    {
        $this->enableModeratorEdit();
        $user = $this->createUserWithRole('role_article');

        $index_response = $this->actingAs($user)->get($this->index_path);
        $index_response->assertStatus(200);
        $index_response->assertSee($this->edit_button_url);

        $edit_response = $this->actingAs($user)->get($this->edit_path);
        $edit_response->assertStatus(200);
        $edit_response->assertSee('項目の追加行');
    }

    /**
     * 権限設定しないと編集者は編集画面にも公開画面の編集ボタンにもアクセスできないこと。
     */
    public function testEditorCannotAccessEditScreen(): void
    {
        $user = $this->createUserWithRole('role_reporter');

        $index_response = $this->actingAs($user)->get($this->index_path);
        $index_response->assertStatus(200);
        $index_response->assertDontSee($this->edit_button_url);

        $edit_response = $this->actingAs($user)->get($this->edit_path);
        $edit_response->assertStatus(200);
        $edit_response->assertSee('権限がありません');
    }

    /**
     * 編集者は権限設定すると編集画面にも公開画面の編集ボタンにもアクセスできること。
     */
    public function testEditorCanAccessEditScreen(): void
    {
        $this->enableEditorEdit();
        $user = $this->createUserWithRole('role_reporter');

        $index_response = $this->actingAs($user)->get($this->index_path);
        $index_response->assertStatus(200);
        $index_response->assertSee($this->edit_button_url);

        $edit_response = $this->actingAs($user)->get($this->edit_path);
        $edit_response->assertStatus(200);
        $edit_response->assertSee('項目の追加行');
    }

    /**
     * プラグイン管理者は編集画面にアクセスできず、公開画面にも編集ボタンが表示されないこと。
     */
    public function testPluginManagerCannotAccessEditScreen(): void
    {
        $user = $this->createUserWithRole('role_arrangement');

        $index_response = $this->actingAs($user)->get($this->index_path);
        $index_response->assertStatus(200);
        $index_response->assertDontSee($this->edit_button_url);

        $edit_response = $this->actingAs($user)->get($this->edit_path);
        $edit_response->assertStatus(200);
        $edit_response->assertSee('権限がありません');
    }

    /**
     * モデレータは項目更新ができること。
     */
    public function testModeratorCanUpdateItems(): void
    {
        $this->enableModeratorEdit();
        $user = $this->createUserWithRole('role_article');

        $payload = $this->buildUpdatePayload('モデレータ更新', 'https://example.com/moderator');

        $response = $this->actingAs($user)->post($this->update_items_path, $payload);

        $response->assertStatus(302);
        $this->assertEquals('モデレータ更新', $this->item->fresh()->caption);
        $this->assertEquals('https://example.com/moderator', $this->item->fresh()->link_url);
    }

    /**
     * 権限がないモデレータは項目更新できないこと。
     */
    public function testModeratorCannotUpdateItemsWithoutPermission(): void
    {
        $user = $this->createUserWithRole('role_article');

        $payload = $this->buildUpdatePayload('モデレータ更新NG', 'https://example.com/moderator-ng');

        $response = $this->actingAs($user)->post($this->update_items_path, $payload);

        $response->assertStatus(200);
        $response->assertSee('権限がありません');
        $this->assertNull($this->item->fresh()->caption);
        $this->assertNull($this->item->fresh()->link_url);
    }

    /**
     * 編集者は項目更新ができること。
     */
    public function testEditorCanUpdateItems(): void
    {
        $this->enableEditorEdit();
        $user = $this->createUserWithRole('role_reporter');

        $payload = $this->buildUpdatePayload('編集者更新', 'https://example.com/editor');

        $response = $this->actingAs($user)->post($this->update_items_path, $payload);

        $response->assertStatus(302);
        $this->assertEquals('編集者更新', $this->item->fresh()->caption);
        $this->assertEquals('https://example.com/editor', $this->item->fresh()->link_url);
    }

    /**
     * 権限がない編集者は項目更新できないこと。
     */
    public function testEditorCannotUpdateItemsWithoutPermission(): void
    {
        $user = $this->createUserWithRole('role_reporter');

        $payload = $this->buildUpdatePayload('編集者更新NG', 'https://example.com/editor-ng');

        $response = $this->actingAs($user)->post($this->update_items_path, $payload);

        $response->assertStatus(200);
        $response->assertSee('権限がありません');
        $this->assertNull($this->item->fresh()->caption);
        $this->assertNull($this->item->fresh()->link_url);
    }

    /**
     * プラグイン管理者は項目更新ができないこと。
     */
    public function testPluginManagerCannotUpdateItems(): void
    {
        $user = $this->createUserWithRole('role_arrangement');

        $payload = $this->buildUpdatePayload('管理者更新NG', 'https://example.com/deny');

        $response = $this->actingAs($user)->post($this->update_items_path, $payload);

        $response->assertStatus(200);
        $response->assertSee('権限がありません');
        $this->assertNull($this->item->fresh()->caption);
        $this->assertNull($this->item->fresh()->link_url);
    }
}
