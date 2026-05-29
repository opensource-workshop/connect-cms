<?php

namespace Tests\Feature\Plugins\User\Calendars;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * Calendars の新規バケツ初期権限を検証する。
 *
 * カレンダー設定の新規保存経路で、サイト管理の投稿権限初期値が
 * 新しいバケツへ適用されることを守る。
 */
class CalendarsDefaultBucketRolesFeatureTest extends TestCase
{
    use DefaultBucketRolesFeatureTestTrait;
    use RefreshDatabase;

    /**
     * テスト前に初期データを投入する。
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /**
     * カレンダー新規作成では、サイト管理の初期値どおりの投稿権限が作られること。
     */
    public function testSaveBucketsCreatesDefaultRolesOnCalendarCreation(): void
    {
        $this->assertSaveBucketsCreatesDefaultRoles('calendars', [
            'name' => 'カレンダーテスト',
        ]);
    }
}
