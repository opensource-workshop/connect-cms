<?php

namespace Tests\Feature\Plugins\User\Databases;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * Databases の新規バケツ初期権限を検証する。
 *
 * データベース設定の新規保存経路で、サイト管理の投稿権限初期値が
 * 新しいバケツへ適用されることを守る。
 */
class DatabasesDefaultBucketRolesFeatureTest extends TestCase
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
     * データベース新規作成では、サイト管理の初期値どおりの投稿権限が作られること。
     */
    public function testSaveBucketsCreatesDefaultRolesOnDatabaseCreation(): void
    {
        $this->assertSaveBucketsCreatesDefaultRoles('databases', [
            'databases_name' => 'データベーステスト',
            'copy_databases_id' => '',
            'posted_role_display_control_flag' => 0,
            'search_results_empty_message' => '',
            'use_like' => 0,
            'like_button_name' => '',
            'mail_send_flag' => 0,
            'mail_send_address' => '',
            'user_mail_send_flag' => 0,
            'from_mail_name' => '',
            'mail_subject' => '',
            'mail_databaseat' => '',
            'data_save_flag' => 0,
            'after_message' => '',
            'numbering_use_flag' => 0,
            'numbering_prefix' => '',
            'save_searched_word' => 0,
            'full_text_search' => 0,
        ]);
    }
}
