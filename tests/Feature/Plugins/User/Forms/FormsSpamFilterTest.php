<?php

namespace Tests\Feature\Plugins\User\Forms;

use App\Enums\FormColumnType;
use App\Enums\SpamBlockType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\SpamBlockHistory;
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

    /**
     * 確認画面でスパムブロック時に spam_block_histories にレコードが作成される
     */
    public function testSpamBlockCreatesHistoryRecordOnConfirm(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup(true);

        // メールカラムを作成
        $email_column = FormsColumns::factory()->emailType()->create(['forms_id' => $form->id]);

        // IPアドレスでブロックするスパムリスト
        $spam_list = SpamList::factory()->forForm($form->id)->create([
            'target_plugin_name' => 'forms',
            'block_type' => SpamBlockType::ip_address,
            'block_value' => '127.0.0.1',
        ]);

        // Act: 確認画面にPOST
        $response = $this->post(
            "/redirect/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $email_column->id => 'test@example.com',
                ],
                'redirect_path' => $page->permanent_link,
            ],
            ['REMOTE_ADDR' => '127.0.0.1']
        );

        // Assert: spam_block_histories にレコードが作成されている
        $this->assertDatabaseHas('spam_block_histories', [
            'spam_list_id' => $spam_list->id,
            'forms_id' => $form->id,
            'block_type' => SpamBlockType::ip_address,
            'block_value' => '127.0.0.1',
        ]);
    }

    /**
     * publicStore（直接登録）でスパムブロック時にレコードが作成される
     */
    public function testSpamBlockCreatesHistoryRecordOnPublicStore(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup(true);

        // メールカラムを作成
        $email_column = FormsColumns::factory()->emailType()->create(['forms_id' => $form->id]);

        // メールアドレスでブロックするスパムリスト
        $spam_list = SpamList::factory()->global()->create([
            'target_plugin_name' => 'forms',
            'block_type' => SpamBlockType::email,
            'block_value' => 'spam@example.com',
        ]);

        // Act: 直接登録にPOST
        $response = $this->post(
            "/redirect/plugin/forms/publicStore/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $email_column->id => 'spam@example.com',
                ],
                'redirect_path' => $page->permanent_link,
            ]
        );

        // Assert: spam_block_histories にレコードが作成されている
        $this->assertDatabaseHas('spam_block_histories', [
            'spam_list_id' => $spam_list->id,
            'forms_id' => $form->id,
            'block_type' => SpamBlockType::email,
            'block_value' => 'spam@example.com',
            'submitted_email' => 'spam@example.com',
        ]);
    }

    /**
     * 記録されるデータが正しい
     */
    public function testSpamBlockHistoryRecordsCorrectData(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup(true);

        // メールカラムを作成
        $email_column = FormsColumns::factory()->emailType()->create(['forms_id' => $form->id]);

        // ドメインでブロックするスパムリスト
        $spam_list = SpamList::factory()->forForm($form->id)->create([
            'target_plugin_name' => 'forms',
            'block_type' => SpamBlockType::domain,
            'block_value' => 'spam-domain.com',
        ]);

        // Act: 確認画面にPOST（ドメインがマッチするメールアドレスを送信）
        $response = $this->post(
            "/redirect/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $email_column->id => 'user@spam-domain.com',
                ],
                'redirect_path' => $page->permanent_link,
            ],
            ['REMOTE_ADDR' => '10.0.0.1']
        );

        // Assert: 記録されたデータの各フィールドが正しい
        $history = SpamBlockHistory::latest('id')->first();
        $this->assertNotNull($history);
        $this->assertEquals($spam_list->id, $history->spam_list_id);
        $this->assertEquals($form->id, $history->forms_id);
        $this->assertEquals(SpamBlockType::domain, $history->block_type);
        $this->assertEquals('spam-domain.com', $history->block_value);
        $this->assertEquals('user@spam-domain.com', $history->submitted_email);
        $this->assertNotNull($history->client_ip);
        $this->assertNotNull($history->created_at);
    }
}
