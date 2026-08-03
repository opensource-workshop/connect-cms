<?php

namespace Tests\Feature\Plugins\User\Databases;

use App\Enums\DatabaseColumnType;
use App\Enums\StatusType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\User\Databases\Databases;
use App\Models\User\Databases\DatabasesColumns;
use App\Models\User\Databases\DatabasesFrames;
use App\Models\User\Databases\DatabasesInputCols;
use App\Models\User\Databases\DatabasesInputs;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * データベースのリンク型カラムに対する保存時検証と表示時エスケープを検証する。
 *
 * 公開ページに保存されるリンク値が、確認画面経由・直接保存経由のどちらでも
 * URLとして制限され、既存の不正値もHTMLとして実行されないことを守る。
 */
class DatabasesLinkColumnSecurityTest extends TestCase
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
     * リンク型は確認画面へ進む時点で危険なURLスキームを拒否すること。
     */
    public function testPublicConfirmRejectsUnsafeLinkValue(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $column] = $this->createDatabaseSetup();

        $response = $this->actingAs($admin)->post(
            "/plugin/databases/publicConfirm/{$page->id}/{$frame->id}",
            $this->buildPostData($column, 'javascript:alert(1)')
        );

        $response->assertOk();
        $response->assertDontSee('databases_confirm', false);
    }

    /**
     * 確認画面を迂回して直接保存されても、リンク型の不正値は保存されないこと。
     */
    public function testPublicStoreRejectsDirectUnsafeLinkValue(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $column] = $this->createDatabaseSetup();
        $payload = '"><img src=x onerror=alert(1)><a href="';

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/databases/publicStore/{$page->id}/{$frame->id}",
            $this->buildPostData($column, $payload)
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('databases_input_cols', [
            'databases_columns_id' => $column->id,
            'value' => $payload,
        ]);
    }

    /**
     * 確認画面を迂回した保存でも、http/httpsのリンク型URLは保存できること。
     */
    public function testPublicStoreAcceptsDirectSafeHttpLinkValue(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $column] = $this->createDatabaseSetup();
        $url = 'https://example.test/path?q=1';

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/databases/publicStore/{$page->id}/{$frame->id}",
            $this->buildPostData($column, $url)
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('databases_input_cols', [
            'databases_columns_id' => $column->id,
            'value' => $url,
        ]);
    }

    /**
     * 確認画面を迂回した保存でも、サイト内相対URLはリンク型URLとして保存できること。
     */
    public function testPublicStoreAcceptsDirectInternalLinkValue(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $column] = $this->createDatabaseSetup();
        $url = '/plugin/databases/list?frame_id=1#frame-1';

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/databases/publicStore/{$page->id}/{$frame->id}",
            $this->buildPostData($column, $url)
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('databases_input_cols', [
            'databases_columns_id' => $column->id,
            'value' => $url,
        ]);
    }

    /**
     * プロトコル相対URLは外部ホストへ誘導できるため、リンク型URLとして保存しないこと。
     */
    public function testPublicStoreRejectsProtocolRelativeLinkValue(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $column] = $this->createDatabaseSetup();
        $url = '//example.test/path';

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/databases/publicStore/{$page->id}/{$frame->id}",
            $this->buildPostData($column, $url)
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('databases_input_cols', [
            'databases_columns_id' => $column->id,
            'value' => $url,
        ]);
    }

    /**
     * 確認画面で一時保存済みの画像IDは、保存時の再検証で画像ファイル本体として扱わないこと。
     */
    public function testPublicStoreAcceptsConfirmedImageUploadId(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $column] = $this->createDatabaseSetup(DatabaseColumnType::image);
        $upload = Uploads::factory()->jpg()->create([
            'plugin_name' => 'databases',
            'page_id' => $page->id,
            'temporary_flag' => 1,
        ]);

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/databases/publicStore/{$page->id}/{$frame->id}",
            $this->buildPostData($column, (string)$upload->id)
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('databases_input_cols', [
            'databases_columns_id' => $column->id,
            'value' => (string)$upload->id,
        ]);
        $this->assertDatabaseHas('uploads', [
            'id' => $upload->id,
            'temporary_flag' => 0,
        ]);
    }

    /**
     * 確認画面を迂回して保存へファイル本体を送っても、確認済みアップロードIDとして扱わないこと。
     */
    public function testPublicStoreRejectsDirectImageFileUpload(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $column] = $this->createDatabaseSetup(DatabaseColumnType::image);
        $file = UploadedFile::fake()->image('direct.jpg');

        $post_data = $this->buildPostData($column, '');
        $post_data['databases_columns_value'][$column->id] = $file;

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/databases/publicStore/{$page->id}/{$frame->id}",
            $post_data
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('databases_input_cols', [
            'databases_columns_id' => $column->id,
        ]);
    }

    /**
     * 一覧表示では既存の危険なリンク型値がHTMLとして出力されないこと。
     */
    public function testListViewEscapesStoredUnsafeLinkValue(): void
    {
        [$input, $column, $input_col] = $this->createStoredLinkValue('"><img src=x onerror=alert(1)>');

        $html = view('plugins.user.databases.default.databases_include_value', [
            'input_cols' => collect([$input_col]),
            'input' => $input,
            'column' => $column,
        ])->render();

        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $html);
        $this->assertStringNotContainsString('<a href="', $html);
    }

    /**
     * 詳細表示では既存のjavascriptスキーム値がクリック可能なリンクにならないこと。
     */
    public function testDetailViewDoesNotLinkStoredUnsafeScheme(): void
    {
        [$input, $column, $input_col] = $this->createStoredLinkValue('javascript:alert(1)');

        $html = view('plugins.user.databases.default.databases_include_detail_value', [
            'input_cols' => collect([$input_col]),
            'inputs' => $input,
            'column' => $column,
        ])->render();

        $this->assertStringContainsString('javascript:alert(1)', $html);
        $this->assertStringNotContainsString('<a href="javascript:alert(1)"', $html);
    }

    /**
     * 一覧表示では安全なリンク型URLをエスケープしたアンカーとして表示すること。
     */
    public function testListViewRendersSafeLinkWithNoopener(): void
    {
        [$input, $column, $input_col] = $this->createStoredLinkValue('https://example.test/path?q=1');

        $html = view('plugins.user.databases.default.databases_include_value', [
            'input_cols' => collect([$input_col]),
            'input' => $input,
            'column' => $column,
        ])->render();

        $this->assertStringContainsString('href="https://example.test/path?q=1"', $html);
        $this->assertStringContainsString('target="_blank" rel="noopener noreferrer"', $html);
    }

    /**
     * 一覧表示ではサイト内相対URLも安全なアンカーとして表示すること。
     */
    public function testListViewRendersInternalLinkWithNoopener(): void
    {
        [$input, $column, $input_col] = $this->createStoredLinkValue('/inside/page?key=value#section');

        $html = view('plugins.user.databases.default.databases_include_value', [
            'input_cols' => collect([$input_col]),
            'input' => $input,
            'column' => $column,
        ])->render();

        $this->assertStringContainsString('href="/inside/page?key=value#section"', $html);
        $this->assertStringContainsString('target="_blank" rel="noopener noreferrer"', $html);
    }

    /**
     * テスト用のページ、フレーム、バケツ、データベース、リンク型カラムを作成する。
     *
     * @return array{0: Page, 1: Frame, 2: DatabasesColumns}
     */
    private function createDatabaseSetup(string $column_type = DatabaseColumnType::link): array
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
        $database->databases_name = 'リンク型テストDB';
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
        $column->column_type = $column_type;
        $column->column_name = 'リンク';
        $column->required = 0;
        $column->display_sequence = 1;
        $column->save();

        return [$page, $frame, $column];
    }

    /**
     * 登録系HTTPリクエストに必要な固定項目とリンク型入力値を組み立てる。
     */
    private function buildPostData(DatabasesColumns $column, string $value): array
    {
        return [
            'databases_columns_value' => [
                $column->id => $value,
            ],
            'posted_at' => '2026-06-18 10:00',
            'expires_at' => '',
            'display_sequence' => '1',
            'categories_id' => '',
        ];
    }

    /**
     * 表示テンプレート検証用に保存済みリンク型入力値を作成する。
     *
     * @return array{0: DatabasesInputs, 1: DatabasesColumns, 2: DatabasesInputCols}
     */
    private function createStoredLinkValue(string $value): array
    {
        [, , $column] = $this->createDatabaseSetup();

        $input = new DatabasesInputs();
        $input->databases_id = $column->databases_id;
        $input->status = StatusType::active;
        $input->posted_at = Carbon::parse('2026-06-18 10:00:00');
        $input->display_sequence = 1;
        $input->save();

        $input_col = new DatabasesInputCols();
        $input_col->databases_inputs_id = $input->id;
        $input_col->databases_columns_id = $column->id;
        $input_col->value = $value;
        $input_col->save();

        return [$input, $column, $input_col];
    }
}
