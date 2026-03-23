<?php

namespace Tests\Feature\Mypage;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ProfileMypageUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * URL引数のIDを指定しても、ログインユーザー以外のプロフィールは更新できない。
     */
    public function testProfileUpdatePathIdCannotUpdateAnotherUser(): void
    {
        $attacker = User::factory()->create([
            'name' => 'attacker',
            'userid' => 'attacker-userid',
            'email' => 'attacker@example.com',
            'columns_set_id' => 1,
        ]);

        $victim = User::factory()->create([
            'name' => 'victim',
            'userid' => 'victim-userid',
            'email' => 'victim@example.com',
            'columns_set_id' => 1,
        ]);

        $response = $this->actingAs($attacker)->post("/mypage/profile/update/{$victim->id}", [
            'name' => $attacker->name,
            'userid' => $attacker->userid,
            'email' => 'attacker-updated@example.com',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(url('/mypage/profile'));
        $response->assertSessionHas('flash_message', '更新しました。');

        $this->assertSame('attacker-updated@example.com', $attacker->fresh()->email);
        $this->assertSame('victim@example.com', $victim->fresh()->email);
    }

    /**
     * フォーム送信先（IDなし）でもログインユーザーのプロフィールを更新できる。
     */
    public function testProfileUpdateWithoutPathIdUpdatesLoggedInUser(): void
    {
        $user = User::factory()->create([
            'name' => 'self-user',
            'userid' => 'self-userid',
            'email' => 'self@example.com',
            'columns_set_id' => 1,
        ]);

        $response = $this->actingAs($user)->post('/mypage/profile/update', [
            'name' => $user->name,
            'userid' => $user->userid,
            'email' => 'self-updated@example.com',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(url('/mypage/profile'));
        $response->assertSessionHas('flash_message', '更新しました。');

        $this->assertSame('self-updated@example.com', $user->fresh()->email);
    }
}
