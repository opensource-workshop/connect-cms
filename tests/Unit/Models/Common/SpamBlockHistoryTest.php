<?php

namespace Tests\Unit\Models\Common;

use App\Enums\SpamBlockType;
use App\Models\Common\SpamBlockHistory;
use App\Models\Common\SpamList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpamBlockHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * fillable属性でレコードを作成できる
     */
    public function testCreateBlockHistory(): void
    {
        // Arrange
        $spam_list = SpamList::factory()->global()->create();

        // Act
        $history = SpamBlockHistory::create([
            'spam_list_id' => $spam_list->id,
            'forms_id' => 1,
            'block_type' => SpamBlockType::email,
            'block_value' => 'spam@example.com',
            'client_ip' => '192.168.1.1',
            'submitted_email' => 'user@example.com',
        ]);

        // Assert
        $this->assertDatabaseHas('spam_block_histories', [
            'id' => $history->id,
            'spam_list_id' => $spam_list->id,
            'forms_id' => 1,
            'block_type' => SpamBlockType::email,
            'block_value' => 'spam@example.com',
            'client_ip' => '192.168.1.1',
            'submitted_email' => 'user@example.com',
        ]);
    }

    /**
     * updated_at が使用されない（UPDATED_AT = null）
     */
    public function testUpdatedAtIsNull(): void
    {
        // Act
        $history = SpamBlockHistory::factory()->create();

        // Assert: updated_at カラムは存在しないため、属性に含まれない
        $this->assertNull(SpamBlockHistory::UPDATED_AT);
        $this->assertNotNull($history->created_at);
    }

    /**
     * spamList() リレーションが正しく動作する
     */
    public function testSpamListRelation(): void
    {
        // Arrange
        $spam_list = SpamList::factory()->global()->create([
            'block_type' => SpamBlockType::email,
            'block_value' => 'spam@example.com',
        ]);

        $history = SpamBlockHistory::factory()->withSpamList($spam_list->id)->create([
            'block_type' => SpamBlockType::email,
            'block_value' => 'spam@example.com',
        ]);

        // Act
        $related = $history->spamList;

        // Assert
        $this->assertNotNull($related);
        $this->assertEquals($spam_list->id, $related->id);
        $this->assertInstanceOf(SpamList::class, $related);
    }

    /**
     * spam_list_id に対応するレコードが存在しない場合 null を返す
     */
    public function testSpamListRelationReturnsNullWhenDeleted(): void
    {
        // Arrange: 存在しないIDを指定
        $history = SpamBlockHistory::factory()->create([
            'spam_list_id' => 99999,
        ]);

        // Act
        $related = $history->spamList;

        // Assert
        $this->assertNull($related);
    }

    /**
     * forms_id が null でも作成できる
     */
    public function testFormsIdIsNullable(): void
    {
        // Act
        $history = SpamBlockHistory::factory()->create([
            'forms_id' => null,
        ]);

        // Assert
        $this->assertDatabaseHas('spam_block_histories', [
            'id' => $history->id,
            'forms_id' => null,
        ]);
    }

    /**
     * client_ip が null でも作成できる
     */
    public function testClientIpIsNullable(): void
    {
        // Act
        $history = SpamBlockHistory::factory()->create([
            'client_ip' => null,
        ]);

        // Assert
        $this->assertDatabaseHas('spam_block_histories', [
            'id' => $history->id,
            'client_ip' => null,
        ]);
    }

    /**
     * submitted_email が null でも作成できる
     */
    public function testSubmittedEmailIsNullable(): void
    {
        // Act
        $history = SpamBlockHistory::factory()->create([
            'submitted_email' => null,
        ]);

        // Assert
        $this->assertDatabaseHas('spam_block_histories', [
            'id' => $history->id,
            'submitted_email' => null,
        ]);
    }
}
