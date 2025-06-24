<?php

namespace Tests\Unit\Models\User\Learningtasks;

use App\Models\User\Learningtasks\LearningtasksUsersStatuses;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningtasksUsersStatusesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * commentがnullまたは空文字の場合、word_countは0を返すことをテスト
     */
    public function testWordCountIsZeroWhenCommentIsNullOrEmpty(): void
    {
        $model = new LearningtasksUsersStatuses(['comment' => null]);
        $this->assertSame(0, $model->word_count);
        $model = new LearningtasksUsersStatuses(['comment' => '']);
        $this->assertSame(0, $model->word_count);
    }

    /**
     * commentの内容に応じてword_countが正しい値を返すことをテスト
     */
    public function testWordCountReturnsExpectedValue(): void
    {
        $model = new LearningtasksUsersStatuses(['comment' => 'This is a test']);
        $this->assertSame(4, $model->word_count);
    }

    /**
     * commentがnullまたは空文字の場合、char_countは0を返すことをテスト
     */
    public function testCharCountIsZeroWhenCommentIsNullOrEmpty(): void
    {
        $model = new LearningtasksUsersStatuses(['comment' => null]);
        $this->assertSame(0, $model->char_count);
        $model = new LearningtasksUsersStatuses(['comment' => '']);
        $this->assertSame(0, $model->char_count);
    }

    /**
     * commentの内容に応じてchar_countが正しい値を返すことをテスト
     */
    public function testCharCountReturnsExpectedValue(): void
    {
        $model = new LearningtasksUsersStatuses(['comment' => 'abc']);
        $this->assertSame(3, $model->char_count);
        $model = new LearningtasksUsersStatuses(['comment' => 'テストword']);
        $this->assertSame(7, $model->char_count);
    }
}
