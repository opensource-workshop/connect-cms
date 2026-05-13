<?php

namespace Tests\Feature\Plugins\User\Reservations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * Reservations の新規バケツ初期権限を検証する。
 *
 * 施設予約設定の新規保存経路で、サイト管理の投稿権限初期値が
 * 新しいバケツへ適用されることを守る。
 */
class ReservationsDefaultBucketRolesFeatureTest extends TestCase
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
     * 施設予約新規作成では、サイト管理の初期値どおりの投稿権限が作られること。
     */
    public function testSaveBucketsCreatesDefaultRolesOnReservationCreation(): void
    {
        $this->assertSaveBucketsCreatesDefaultRoles('reservations', [
            'reservations_id' => '',
            'reservation_name' => '施設予約テスト',
        ]);
    }
}
