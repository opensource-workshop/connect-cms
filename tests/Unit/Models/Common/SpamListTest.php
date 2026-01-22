<?php

namespace Tests\Unit\Models\Common;

use App\Enums\SpamBlockType;
use App\Models\Common\SpamList;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpamListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * getFormsSpamLists(): forms_idを指定した場合、該当フォームと全体のスパムリストを取得
     */
    public function testGetFormsSpamListsWithFormsId(): void
    {
        // Arrange: テストデータ作成
        $target_forms_id = 1;
        $other_forms_id = 2;

        // 対象フォーム用スパムリスト
        $spam_for_target = SpamList::factory()->forForm($target_forms_id)->create();
        // 全体スパムリスト
        $spam_global = SpamList::factory()->global()->create();
        // 他のフォーム用スパムリスト（取得されないはず）
        $spam_for_other = SpamList::factory()->forForm($other_forms_id)->create();

        // Act: メソッド実行
        $result = SpamList::getFormsSpamLists($target_forms_id);

        // Assert: 対象フォームと全体のスパムリストのみ取得される
        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $spam_for_target->id));
        $this->assertTrue($result->contains('id', $spam_global->id));
        $this->assertFalse($result->contains('id', $spam_for_other->id));
    }

    /**
     * getFormsSpamLists(): forms_idがnullの場合、全体のみのスパムリストを取得
     */
    public function testGetFormsSpamListsWithNullFormsId(): void
    {
        // Arrange
        $forms_id = 1;

        // 全体スパムリスト
        $spam_global = SpamList::factory()->global()->create();
        // フォーム個別スパムリスト（取得されないはず）
        $spam_for_form = SpamList::factory()->forForm($forms_id)->create();

        // Act
        $result = SpamList::getFormsSpamLists(null);

        // Assert: 全体のみ取得される
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains('id', $spam_global->id));
        $this->assertFalse($result->contains('id', $spam_for_form->id));
    }

    /**
     * getFormsSpamLists(): block_typeとcreated_atで正しくソート
     */
    public function testGetFormsSpamListsOrderedByBlockTypeAndCreatedAt(): void
    {
        // Arrange: 異なるblock_typeとcreated_atでデータ作成
        $spam1 = SpamList::factory()->global()->create([
            'block_type' => SpamBlockType::email,
            'created_at' => now()->subDays(2),
        ]);
        $spam2 = SpamList::factory()->global()->create([
            'block_type' => SpamBlockType::email,
            'created_at' => now()->subDays(1),
        ]);
        $spam3 = SpamList::factory()->global()->create([
            'block_type' => SpamBlockType::domain,
            'created_at' => now()->subDays(3),
        ]);
        $spam4 = SpamList::factory()->global()->create([
            'block_type' => SpamBlockType::ip_address,
            'created_at' => now(),
        ]);

        // Act
        $result = SpamList::getFormsSpamLists(null);

        // Assert: block_type順、同じblock_typeならcreated_at降順
        $this->assertCount(4, $result);

        // block_typeでグループ化してチェック
        $email_spams = $result->filter(fn($s) => $s->block_type == SpamBlockType::email)->values();
        $this->assertEquals($spam2->id, $email_spams[0]->id);
        $this->assertEquals($spam1->id, $email_spams[1]->id);

        $domain_spams = $result->filter(fn($s) => $s->block_type == SpamBlockType::domain)->values();
        $this->assertEquals($spam3->id, $domain_spams[0]->id);

        $ip_spams = $result->filter(fn($s) => $s->block_type == SpamBlockType::ip_address)->values();
        $this->assertEquals($spam4->id, $ip_spams[0]->id);
    }

    /**
     * getFormsSpamLists(): 境界値 - スパムリストが0件の場合
     */
    public function testGetFormsSpamListsWithNoData(): void
    {
        // Act
        $result = SpamList::getFormsSpamLists(1);

        // Assert: 空のCollectionが返る
        $this->assertCount(0, $result);
    }

    /**
     * getFormsSpamLists(): 異なるプラグインのスパムリストは含まれない
     */
    public function testGetFormsSpamListsExcludesOtherPlugins(): void
    {
        // Arrange
        $forms_id = 1;

        // forms用スパムリスト
        $spam_forms = SpamList::factory()->forForm($forms_id)->create([
            'target_plugin_name' => 'forms',
        ]);

        // 他のプラグイン用スパムリスト（取得されないはず）
        $spam_other_plugin = SpamList::factory()->forForm($forms_id)->create([
            'target_plugin_name' => 'bbs',
        ]);

        // Act
        $result = SpamList::getFormsSpamLists($forms_id);

        // Assert: forms用のみ取得される
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains('id', $spam_forms->id));
        $this->assertFalse($result->contains('id', $spam_other_plugin->id));
    }

    /**
     * addIfNotExists(): 新規データを正常に追加できる
     */
    public function testAddIfNotExistsAddsNewRecord(): void
    {
        // Arrange
        $target_plugin_name = 'forms';
        $target_id = 1;
        $block_type = SpamBlockType::email;
        $block_value = 'spam@example.com';
        $memo = 'テストメモ';

        // 現在のユーザーを設定（UserableNohistoryのため）
        $user = User::factory()->create();
        $this->actingAs($user);

        // Act
        $result = SpamList::addIfNotExists($target_plugin_name, $target_id, $block_type, $block_value, $memo);

        // Assert: 追加成功（戻り値true）
        $this->assertTrue($result);

        // データが存在する
        $this->assertDatabaseHas('spam_lists', [
            'target_plugin_name' => $target_plugin_name,
            'target_id' => $target_id,
            'block_type' => $block_type,
            'block_value' => $block_value,
            'memo' => $memo,
            'created_id' => $user->id,
        ]);
    }

    /**
     * addIfNotExists(): 重複データは追加しない
     */
    public function testAddIfNotExistsDoesNotAddDuplicate(): void
    {
        // Arrange: 既存データ作成
        $target_plugin_name = 'forms';
        $target_id = 1;
        $block_type = SpamBlockType::email;
        $block_value = 'spam@example.com';

        $existing_spam = SpamList::factory()->create([
            'target_plugin_name' => $target_plugin_name,
            'target_id' => $target_id,
            'block_type' => $block_type,
            'block_value' => $block_value,
            'memo' => '既存メモ',
        ]);

        $count_before = SpamList::count();

        // Act: 同じデータで追加試行
        $result = SpamList::addIfNotExists($target_plugin_name, $target_id, $block_type, $block_value, '新しいメモ');

        // Assert: 追加失敗（戻り値false）
        $this->assertFalse($result);

        // レコード数が増えていない
        $this->assertEquals($count_before, SpamList::count());
    }

    /**
     * addIfNotExists(): memoは重複判定に影響しない
     */
    public function testAddIfNotExistsMemoDoesNotAffectDuplication(): void
    {
        // Arrange: 既存データ作成
        $target_plugin_name = 'forms';
        $target_id = 1;
        $block_type = SpamBlockType::email;
        $block_value = 'spam@example.com';

        SpamList::factory()->create([
            'target_plugin_name' => $target_plugin_name,
            'target_id' => $target_id,
            'block_type' => $block_type,
            'block_value' => $block_value,
            'memo' => 'メモ1',
        ]);

        // Act: memoだけ違うデータで追加試行
        $result = SpamList::addIfNotExists($target_plugin_name, $target_id, $block_type, $block_value, 'メモ2');

        // Assert: 重複と判定される
        $this->assertFalse($result);
    }

    /**
     * addIfNotExists(): target_idがnullでも正しく動作
     */
    public function testAddIfNotExistsWorksWithNullTargetId(): void
    {
        // Arrange
        $target_plugin_name = 'forms';
        $target_id = null;
        $block_type = SpamBlockType::domain;
        $block_value = 'spam-domain.com';

        $user = User::factory()->create();
        $this->actingAs($user);

        // Act
        $result = SpamList::addIfNotExists($target_plugin_name, $target_id, $block_type, $block_value);

        // Assert: 追加成功
        $this->assertTrue($result);
        $this->assertDatabaseHas('spam_lists', [
            'target_plugin_name' => $target_plugin_name,
            'target_id' => null,
            'block_type' => $block_type,
            'block_value' => $block_value,
        ]);

        // 再度追加試行
        $result2 = SpamList::addIfNotExists($target_plugin_name, $target_id, $block_type, $block_value);

        // Assert: 重複で追加失敗
        $this->assertFalse($result2);
    }

    /**
     * isGlobalScope(): target_idがnullの場合、trueを返す
     */
    public function testIsGlobalScopeReturnsTrueWhenTargetIdIsNull(): void
    {
        // Arrange
        $spam = SpamList::factory()->global()->create();

        // Act & Assert
        $this->assertTrue($spam->isGlobalScope());
    }

    /**
     * isGlobalScope(): target_idが設定されている場合、falseを返す
     */
    public function testIsGlobalScopeReturnsFalseWhenTargetIdIsSet(): void
    {
        // Arrange
        $spam = SpamList::factory()->forForm(1)->create();

        // Act & Assert
        $this->assertFalse($spam->isGlobalScope());
    }
}
