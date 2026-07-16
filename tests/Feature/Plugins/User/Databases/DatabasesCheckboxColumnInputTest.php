<?php

namespace Tests\Feature\Plugins\User\Databases;

use App\Enums\DatabaseColumnType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\User\Databases\Databases;
use App\Models\User\Databases\DatabasesColumns;
use App\Models\User\Databases\DatabasesColumnsSelects;
use App\Models\User\Databases\DatabasesFrames;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * データベースの複数選択型カラムの未入力時の確認・保存動作を検証する。
 *
 * ブラウザから未選択のチェックボックス値が送信されない境界条件を、公開メソッド経由の
 * HTTPリクエストで再現し、確認画面と保存処理の入力補完仕様を守る。
 */
class DatabasesCheckboxColumnInputTest extends TestCase
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
     * 任意の複数選択型が未選択でも、確認画面で空値として扱いシステムエラーにしないこと。
     */
    public function testPublicConfirmAcceptsUnselectedOptionalCheckboxColumn(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame] = $this->createDatabaseSetup();

        $response = $this->actingAs($admin)->post(
            "/plugin/databases/publicConfirm/{$page->id}/{$frame->id}",
            $this->buildPostDataWithoutColumnValue()
        );

        $response->assertOk();
        $response->assertSee('以下の内容でよろしいですか？');
    }

    /**
     * 任意の複数選択型が未選択でも、保存時は空値として登録できること。
     */
    public function testPublicStoreAcceptsUnselectedOptionalCheckboxColumn(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $column] = $this->createDatabaseSetup();

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/databases/publicStore/{$page->id}/{$frame->id}",
            $this->buildPostDataWithoutColumnValue()
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('databases_input_cols', [
            'databases_columns_id' => $column->id,
            'value' => '',
        ]);
    }

    /**
     * テスト用のページ、フレーム、バケツ、データベース、複数選択型カラムを作成する。
     *
     * @return array{0: Page, 1: Frame, 2: DatabasesColumns}
     */
    private function createDatabaseSetup(): array
    {
        $page = Page::factory()->create();
        $bucket = Buckets::factory()->create(['plugin_name' => 'databases']);
        $frame = Frame::create([
            'page_id' => $page->id,
            'area_id' => 2,
            'plugin_name' => 'databases',
            'bucket_id' => $bucket->id,
            'template' => 'default',
            'display_sequence' => 1,
        ]);

        $database = new Databases();
        $database->bucket_id = $bucket->id;
        $database->databases_name = '複数選択型テストDB';
        $database->data_save_flag = 1;
        $database->save();

        DatabasesFrames::create([
            'databases_id' => $database->id,
            'frames_id' => $frame->id,
            'use_search_flag' => 1,
            'use_select_flag' => 1,
            'view_count' => 10,
            'default_hide' => 0,
            'use_filter_flag' => 0,
        ]);

        $column = new DatabasesColumns();
        $column->databases_id = $database->id;
        $column->column_type = DatabaseColumnType::checkbox;
        $column->column_name = '複数選択';
        $column->required = 0;
        $column->display_sequence = 1;
        $column->save();

        DatabasesColumnsSelects::create([
            'databases_columns_id' => $column->id,
            'value' => '選択肢1',
            'display_sequence' => 1,
        ]);

        return [$page, $frame, $column];
    }

    /**
     * 登録系HTTPリクエストに必要な固定項目だけを組み立てる。
     */
    private function buildPostDataWithoutColumnValue(): array
    {
        return [
            'posted_at' => '2026-06-18 10:00',
            'expires_at' => '',
            'display_sequence' => '1',
            'categories_id' => '',
        ];
    }
}
