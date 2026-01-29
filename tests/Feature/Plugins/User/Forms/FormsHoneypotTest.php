<?php

namespace Tests\Feature\Plugins\User\Forms;

use App\Enums\SpamBlockType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\SpamBlockHistory;
use App\Models\Common\SpamList;
use App\Models\Core\UsersRoles;
use App\Models\User\Forms\Forms;
use App\Models\User\Forms\FormsColumns;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * フォームプラグイン ハニーポット機能テスト
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category フォームプラグイン
 */
class FormsHoneypotTest extends TestCase
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
    private function createFormSetup()
    {
        $page = Page::factory()->create();
        $bucket = Buckets::factory()->create(['plugin_name' => 'forms']);
        $frame = Frame::factory()->create([
            'page_id' => $page->id,
            'plugin_name' => 'forms',
            'bucket_id' => $bucket->id,
        ]);

        $form = Forms::factory()->create(['bucket_id' => $bucket->id]);

        return [$page, $frame, $bucket, $form];
    }

    /**
     * ハニーポットをスパムリストに追加
     */
    private function addHoneypotToSpamList($form, $is_global = false): SpamList
    {
        return SpamList::factory()->create([
            'target_plugin_name' => 'forms',
            'target_id' => $is_global ? null : $form->id,
            'block_type' => SpamBlockType::honeypot,
            'block_value' => null,
        ]);
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
     * addSpamList(): ハニーポットをスパムリストに追加できる
     */
    public function testAddSpamListCanAddHoneypot(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $admin = $this->createAdminUser();

        // Act
        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/forms/addSpamList/{$page->id}/{$frame->id}/{$form->id}",
            [
                'block_type' => SpamBlockType::honeypot,
                // ハニーポットは値不要
                'redirect_path' => "/plugin/forms/editSpamFilter/{$page->id}/{$frame->id}",
            ]
        );

        // Assert
        $response->assertStatus(302);
        $response->assertSessionHas('flash_message');

        $this->assertDatabaseHas('spam_lists', [
            'target_plugin_name' => 'forms',
            'target_id' => $form->id,
            'block_type' => SpamBlockType::honeypot,
            'block_value' => null,
        ]);
    }

    /**
     * deleteSpamList(): ハニーポットをスパムリストから削除できる
     */
    public function testDeleteSpamListCanRemoveHoneypot(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $admin = $this->createAdminUser();
        $honeypot = $this->addHoneypotToSpamList($form);

        // Act
        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/forms/deleteSpamList/{$page->id}/{$frame->id}/{$honeypot->id}",
            [
                'redirect_path' => "/plugin/forms/editSpamFilter/{$page->id}/{$frame->id}",
            ]
        );

        // Assert
        $response->assertStatus(302);
        // 論理削除されていることを確認
        $this->assertSoftDeleted('spam_lists', [
            'id' => $honeypot->id,
        ]);
    }

    /**
     * publicConfirm(): ハニーポットが空なら確認画面に進める（履歴は記録されない）
     */
    public function testPublicConfirmAllowsEmptyHoneypot(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $this->addHoneypotToSpamList($form);

        // テキストカラムを作成
        $text_column = FormsColumns::factory()->textType()->create(['forms_id' => $form->id]);

        $history_count_before = SpamBlockHistory::count();

        // Act: /plugin/ を使用して直接メソッドを呼び出す
        $response = $this->post(
            "/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $text_column->id => 'テスト入力',
                ],
                'website_url' => '', // ハニーポットは空
            ]
        );

        // Assert: ハニーポットブロック履歴が作成されていない
        $this->assertEquals($history_count_before, SpamBlockHistory::where('block_type', SpamBlockType::honeypot)->count());
    }

    /**
     * publicConfirm(): ハニーポットに値があるとブロックされ、履歴が記録される
     */
    public function testPublicConfirmBlocksFilledHoneypot(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $honeypot = $this->addHoneypotToSpamList($form);

        // テキストカラムを作成
        $text_column = FormsColumns::factory()->textType()->create(['forms_id' => $form->id]);

        // Act: /plugin/ を使用して直接メソッドを呼び出す
        $response = $this->post(
            "/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $text_column->id => 'テスト入力',
                ],
                'website_url' => 'http://spam-site.com', // ハニーポットに値がある
            ]
        );

        // Assert: ハニーポットブロック履歴が作成されている
        $this->assertDatabaseHas('spam_block_histories', [
            'forms_id' => $form->id,
            'block_type' => SpamBlockType::honeypot,
            'block_value' => 'http://spam-site.com',
            'spam_list_id' => $honeypot->id,
        ]);

        // レスポンスにエラーメッセージが含まれている
        $response->assertSee('不正な投稿が検出されました。');
    }

    /**
     * publicConfirm(): ハニーポット無効時は値があってもブロックされない
     */
    public function testPublicConfirmDoesNotBlockWhenHoneypotDisabled(): void
    {
        // Arrange: ハニーポット無効（スパムリストに登録なし）
        [$page, $frame, $bucket, $form] = $this->createFormSetup();

        // テキストカラムを作成
        $text_column = FormsColumns::factory()->textType()->create(['forms_id' => $form->id]);

        $history_count_before = SpamBlockHistory::count();

        // Act
        $response = $this->post(
            "/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $text_column->id => 'テスト入力',
                ],
                'website_url' => 'http://spam-site.com', // ハニーポットに値がある
            ]
        );

        // Assert: ハニーポットブロック履歴が作成されていない
        $this->assertEquals($history_count_before, SpamBlockHistory::where('block_type', SpamBlockType::honeypot)->count());
    }

    /**
     * publicConfirm(): グローバルハニーポットでもブロックされる
     */
    public function testPublicConfirmBlocksWithGlobalHoneypot(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $honeypot = $this->addHoneypotToSpamList($form, true); // グローバル設定

        // テキストカラムを作成
        $text_column = FormsColumns::factory()->textType()->create(['forms_id' => $form->id]);

        // Act
        $response = $this->post(
            "/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $text_column->id => 'テスト入力',
                ],
                'website_url' => 'http://spam-site.com',
            ]
        );

        // Assert: グローバルハニーポットでもブロックされる
        $this->assertDatabaseHas('spam_block_histories', [
            'forms_id' => $form->id,
            'block_type' => SpamBlockType::honeypot,
            'spam_list_id' => $honeypot->id,
        ]);
    }

    /**
     * publicStore(): ハニーポットに値があるとブロックされる（二重防御）
     */
    public function testPublicStoreBlocksFilledHoneypot(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $honeypot = $this->addHoneypotToSpamList($form);

        // テキストカラムを作成
        $text_column = FormsColumns::factory()->textType()->create(['forms_id' => $form->id]);

        // Act: 直接登録にPOST (リダイレクト経由)
        $response = $this->post(
            "/redirect/plugin/forms/publicStore/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $text_column->id => 'テスト入力',
                ],
                'website_url' => 'http://spam-site.com',
                'redirect_path' => $page->permanent_link,
            ]
        );

        // Assert: リダイレクトされる
        $response->assertRedirect();

        // spam_block_histories にレコードが作成されている
        $this->assertDatabaseHas('spam_block_histories', [
            'forms_id' => $form->id,
            'block_type' => SpamBlockType::honeypot,
            'spam_list_id' => $honeypot->id,
        ]);
    }

    /**
     * 履歴に記録されるデータが正しい
     */
    public function testHoneypotBlockHistoryRecordsCorrectData(): void
    {
        // Arrange
        [$page, $frame, $bucket, $form] = $this->createFormSetup();
        $honeypot = $this->addHoneypotToSpamList($form);

        // テキストカラムを作成
        $text_column = FormsColumns::factory()->textType()->create(['forms_id' => $form->id]);

        // Act
        $response = $this->post(
            "/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $text_column->id => 'テスト入力',
                ],
                'website_url' => 'bot-filled-value',
            ],
            ['REMOTE_ADDR' => '10.0.0.1']
        );

        // Assert: 記録されたデータの各フィールドが正しい
        $history = SpamBlockHistory::latest('id')->first();
        $this->assertNotNull($history);
        $this->assertEquals($honeypot->id, $history->spam_list_id);
        $this->assertEquals($form->id, $history->forms_id);
        $this->assertEquals(SpamBlockType::honeypot, $history->block_type);
        $this->assertEquals('bot-filled-value', $history->block_value);
        $this->assertNull($history->submitted_email);
        $this->assertNotNull($history->client_ip);
        $this->assertNotNull($history->created_at);
    }
}
