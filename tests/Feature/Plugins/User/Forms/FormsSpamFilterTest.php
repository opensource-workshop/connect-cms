<?php

namespace Tests\Feature\Plugins\User\Forms;

use App\Enums\SpamBlockType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\SpamList;
use App\Models\Core\UsersRoles;
use App\Models\User\Forms\Forms;
use App\Models\User\Forms\FormsColumns;
use App\Models\User\Forms\FormsInputs;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormsSpamFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * テスト用のページ、フレーム、バケツ、フォームを作成
     */
    private function createFormSetup($with_spam_filter = false)
    {
        $page = Page::factory()->create();
        $bucket = Buckets::factory()->create(['plugin_name' => 'forms']);
        $frame = Frame::factory()->create([
            'page_id' => $page->id,
            'plugin_name' => 'forms',
            'bucket_id' => $bucket->id,
        ]);

        $form_data = ['bucket_id' => $bucket->id];
        if ($with_spam_filter) {
            $form_data['use_spam_filter_flag'] = 1;
            $form_data['spam_filter_message'] = 'スパムとして検出されました。';
        }

        $form = Forms::factory()->create($form_data);

        return [$page, $frame, $bucket, $form];
    }

    /**
     * コンテンツ管理者ユーザーを作成
     */
    private function createAdminUser(): User
    {
        $user = User::factory()->create();
        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'base',
            'role_name' => 'role_article_admin',
        ]);
        return $user;
    }

    /**
     * saveSpamFilter(): スパムフィルタリングを有効にできる
     */
    public function testSaveSpamFilterCanEnableFiltering(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $admin = $this->createAdminUser();

        // Act
        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/forms/saveSpamFilter/{$page->id}/{$frame->id}/{$form->id}",
            [
                'use_spam_filter_flag' => 1,
                'spam_filter_message' => 'カスタムメッセージ',
                'redirect_path' => "/plugin/forms/editSpamFilter/{$page->id}/{$frame->id}",
            ]
        );

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHas('flash_message');

        $this->assertDatabaseHas('forms', [
            'id' => $form->id,
            'use_spam_filter_flag' => 1,
            'spam_filter_message' => 'カスタムメッセージ',
        ]);
    }

    /**
     * saveSpamFilter(): スパムフィルタリングを無効にできる
     */
    public function testSaveSpamFilterCanDisableFiltering(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup(true);
        $admin = $this->createAdminUser();

        // Act
        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/forms/saveSpamFilter/{$page->id}/{$frame->id}/{$form->id}",
            [
                'use_spam_filter_flag' => 0,
                'redirect_path' => "/plugin/forms/editSpamFilter/{$page->id}/{$frame->id}",
            ]
        );

        // Assert
        $response->assertStatus(302);

        $this->assertDatabaseHas('forms', [
            'id' => $form->id,
            'use_spam_filter_flag' => 0,
        ]);
    }

    /**
     * addSpamList(): フォーム個別のスパムリストを追加できる
     */
    public function testAddSpamListCanAddFormSpecificSpamList(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $admin = $this->createAdminUser();

        // Act
        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/forms/addSpamList/{$page->id}/{$frame->id}/{$form->id}",
            [
                'block_type' => SpamBlockType::email,
                'block_value' => 'spam@example.com',
                'memo' => 'テストメモ',
                'redirect_path' => "/plugin/forms/editSpamFilter/{$page->id}/{$frame->id}",
            ]
        );

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHas('flash_message');

        $this->assertDatabaseHas('spam_lists', [
            'target_plugin_name' => 'forms',
            'target_id' => $form->id,
            'block_type' => SpamBlockType::email,
            'block_value' => 'spam@example.com',
            'memo' => 'テストメモ',
        ]);
    }

    /**
     * addSpamList(): block_typeが必須
     */
    public function testAddSpamListValidatesBlockTypeRequired(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $admin = $this->createAdminUser();

        // Act
        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/forms/addSpamList/{$page->id}/{$frame->id}/{$form->id}",
            [
                'block_value' => 'test@example.com',
                'redirect_path' => "/plugin/forms/editSpamFilter/{$page->id}/{$frame->id}",
            ]
        );

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHasErrors('block_type');
    }

    /**
     * addToSpamListFromInput(): IPアドレスをスパムリストに追加できる
     */
    public function testAddToSpamListFromInputCanAddIpAddress(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $admin = $this->createAdminUser();

        $input = FormsInputs::factory()->withIpAddress('192.168.1.100')->create([
            'forms_id' => $form->id,
        ]);

        // Act
        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/forms/addToSpamListFromInput/{$page->id}/{$frame->id}/{$input->id}",
            [
                'add_ip_address' => 1,
                'scope_type' => 'form',
                'memo' => 'スパム投稿',
                'redirect_path' => "/plugin/forms/listInputs/{$page->id}/{$frame->id}",
            ]
        );

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHas('flash_message');

        $this->assertDatabaseHas('spam_lists', [
            'target_plugin_name' => 'forms',
            'target_id' => $form->id,
            'block_type' => SpamBlockType::ip_address,
            'block_value' => '192.168.1.100',
            'memo' => 'スパム投稿',
        ]);
    }

    /**
     * addToSpamListFromInput(): メールアドレスをスパムリストに追加できる
     */
    public function testAddToSpamListFromInputCanAddEmail(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $admin = $this->createAdminUser();

        $input = FormsInputs::factory()->create(['forms_id' => $form->id]);
        $email_column = FormsColumns::factory()->emailType()->create(['forms_id' => $form->id]);
        \App\Models\User\Forms\FormsInputCols::factory()->create([
            'forms_inputs_id' => $input->id,
            'forms_columns_id' => $email_column->id,
            'value' => 'spam@example.com',
        ]);

        // Act
        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/forms/addToSpamListFromInput/{$page->id}/{$frame->id}/{$input->id}",
            [
                'add_email' => 1,
                'scope_type' => 'global',
                'redirect_path' => "/plugin/forms/listInputs/{$page->id}/{$frame->id}",
            ]
        );

        // Assert
        $response->assertStatus(302);
        $this->assertDatabaseHas('spam_lists', [
            'target_plugin_name' => 'forms',
            'target_id' => null, // global
            'block_type' => SpamBlockType::email,
            'block_value' => 'spam@example.com',
        ]);
    }

    /**
     * addToSpamListFromInput(): ドメインをスパムリストに追加できる
     */
    public function testAddToSpamListFromInputCanAddDomain(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $admin = $this->createAdminUser();

        $input = FormsInputs::factory()->create(['forms_id' => $form->id]);
        $email_column = FormsColumns::factory()->emailType()->create(['forms_id' => $form->id]);
        \App\Models\User\Forms\FormsInputCols::factory()->create([
            'forms_inputs_id' => $input->id,
            'forms_columns_id' => $email_column->id,
            'value' => 'user@spam-domain.com',
        ]);

        // Act
        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/forms/addToSpamListFromInput/{$page->id}/{$frame->id}/{$input->id}",
            [
                'add_domain' => 1,
                'scope_type' => 'global',
                'redirect_path' => "/plugin/forms/listInputs/{$page->id}/{$frame->id}",
            ]
        );

        // Assert
        $response->assertStatus(302);
        $this->assertDatabaseHas('spam_lists', [
            'target_plugin_name' => 'forms',
            'target_id' => null,
            'block_type' => SpamBlockType::domain,
            'block_value' => 'spam-domain.com',
        ]);
    }

    /**
     * addToSpamListFromInput(): 複数項目を同時に追加できる
     */
    public function testAddToSpamListFromInputCanAddMultipleItems(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $admin = $this->createAdminUser();

        $input = FormsInputs::factory()->withIpAddress('192.168.1.100')->create([
            'forms_id' => $form->id,
        ]);
        $email_column = FormsColumns::factory()->emailType()->create(['forms_id' => $form->id]);
        \App\Models\User\Forms\FormsInputCols::factory()->create([
            'forms_inputs_id' => $input->id,
            'forms_columns_id' => $email_column->id,
            'value' => 'user@spam-domain.com',
        ]);

        // Act: IPアドレス、メール、ドメインを同時に追加
        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/forms/addToSpamListFromInput/{$page->id}/{$frame->id}/{$input->id}",
            [
                'add_ip_address' => 1,
                'add_email' => 1,
                'add_domain' => 1,
                'scope_type' => 'global',
                'redirect_path' => "/plugin/forms/listInputs/{$page->id}/{$frame->id}",
            ]
        );

        // Assert: 3件追加される
        $response->assertStatus(302);
        $this->assertEquals(3, SpamList::count());
    }

    /**
     * addToSpamListFromInput(): 重複データは追加されない
     */
    public function testAddToSpamListFromInputDoesNotAddDuplicates(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $admin = $this->createAdminUser();

        $input = FormsInputs::factory()->withIpAddress('192.168.1.100')->create([
            'forms_id' => $form->id,
        ]);

        // 事前にスパムリストに追加
        SpamList::factory()->forForm($form->id)->create([
            'target_plugin_name' => 'forms',
            'block_type' => SpamBlockType::ip_address,
            'block_value' => '192.168.1.100',
        ]);

        $count_before = SpamList::count();

        // Act: 同じデータで追加試行
        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/forms/addToSpamListFromInput/{$page->id}/{$frame->id}/{$input->id}",
            [
                'add_ip_address' => 1,
                'scope_type' => 'form',
                'redirect_path' => "/plugin/forms/listInputs/{$page->id}/{$frame->id}",
            ]
        );

        // Assert: 件数が増えていない
        $response->assertStatus(302);
        $this->assertEquals($count_before, SpamList::count());

        // フラッシュメッセージに登録済みの旨が含まれる
        $flash_message = session('flash_message');
        $this->assertStringContainsString('登録済み', $flash_message);
    }
}
