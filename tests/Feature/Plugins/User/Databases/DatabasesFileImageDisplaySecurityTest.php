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
use Tests\TestCase;

/**
 * データベースのファイル名由来表示がHTMLとして解釈されないことを検証する。
 *
 * 保存済みアップロードの元ファイル名を一覧・詳細テンプレートへ渡し、
 * file型のリンク本文、ファイルID由来のhref属性、image型のalt属性が表示時に安全化されることを守る。
 */
class DatabasesFileImageDisplaySecurityTest extends TestCase
{
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
     * 一覧表示のfile型では、元ファイル名にHTML断片が含まれてもリンク本文としてだけ表示されること。
     */
    public function testListViewEscapesFileColumnOriginalName(): void
    {
        [$input, $column, $input_col] = $this->createStoredUploadValue(
            DatabaseColumnType::file,
            '<img src=x onerror=alert(1)>.pdf'
        );

        $html = view('plugins.user.databases.default.databases_include_value', [
            'input_cols' => collect([$input_col]),
            'input' => $input,
            'column' => $column,
        ])->render();

        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;.pdf', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $html);
    }

    /**
     * 詳細表示のfile型でも、元ファイル名にHTML断片が含まれてもリンク本文としてだけ表示されること。
     */
    public function testDetailViewEscapesFileColumnOriginalName(): void
    {
        [$input, $column, $input_col] = $this->createStoredUploadValue(
            DatabaseColumnType::file,
            '<script>alert(1)</script>.pdf'
        );

        $html = view('plugins.user.databases.default.databases_include_detail_value', [
            'input_cols' => collect([$input_col]),
            'inputs' => $input,
            'column' => $column,
        ])->render();

        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;.pdf', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    /**
     * 一覧表示のfile型では、保存済みのファイルID値が属性を壊す文字列でもhrefへ混入しないこと。
     */
    public function testListViewConstrainsFileColumnHrefToUploadId(): void
    {
        [$input, $column, $input_col] = $this->createStoredUploadValue(
            DatabaseColumnType::file,
            'report.pdf'
        );
        $input_col->value .= '" onclick="alert(1)';
        $input_col->download_count = 0;

        $html = view('plugins.user.databases.default.databases_include_value', [
            'input_cols' => collect([$input_col]),
            'input' => $input,
            'column' => $column,
        ])->render();

        $this->assertStringContainsString('/file/' . (int)$input_col->value . '" target="_blank"', $html);
        $this->assertStringNotContainsString('onclick="alert(1)', $html);
    }

    /**
     * 詳細表示のfile型でも、保存済みのファイルID値が属性を壊す文字列でもhrefへ混入しないこと。
     */
    public function testDetailViewConstrainsFileColumnHrefToUploadId(): void
    {
        [$input, $column, $input_col] = $this->createStoredUploadValue(
            DatabaseColumnType::file,
            'report.pdf'
        );
        $input_col->value .= '" onclick="alert(1)';
        $input_col->download_count = 0;

        $html = view('plugins.user.databases.default.databases_include_detail_value', [
            'input_cols' => collect([$input_col]),
            'inputs' => $input,
            'column' => $column,
        ])->render();

        $this->assertStringContainsString('/file/' . (int)$input_col->value . '" target="_blank"', $html);
        $this->assertStringNotContainsString('onclick="alert(1)', $html);
    }

    /**
     * 一覧表示のimage型では、元ファイル名がalt属性を壊さないようエスケープされること。
     */
    public function testListViewEscapesImageColumnAltText(): void
    {
        [$input, $column, $input_col] = $this->createStoredUploadValue(
            DatabaseColumnType::image,
            'photo" onerror="alert(1).jpg'
        );

        $html = view('plugins.user.databases.default.databases_include_value', [
            'input_cols' => collect([$input_col]),
            'input' => $input,
            'column' => $column,
        ])->render();

        $this->assertStringContainsString('alt="photo&quot; onerror=&quot;alert(1)"', $html);
        $this->assertStringNotContainsString('" onerror="alert(1)', $html);
    }

    /**
     * 表示テンプレート検証用に、アップロードIDを保持する入力値を作成する。
     *
     * @return array{0: DatabasesInputs, 1: DatabasesColumns, 2: DatabasesInputCols}
     */
    private function createStoredUploadValue(string $column_type, string $client_original_name): array
    {
        [$page, , $column] = $this->createDatabaseSetup($column_type);
        $extension = pathinfo($client_original_name, PATHINFO_EXTENSION) ?: 'txt';
        $upload = Uploads::factory()->create([
            'client_original_name' => $client_original_name,
            'extension' => $extension,
            'plugin_name' => 'databases',
            'page_id' => $page->id,
        ]);

        $input = new DatabasesInputs();
        $input->databases_id = $column->databases_id;
        $input->status = StatusType::active;
        $input->posted_at = Carbon::parse('2026-06-19 10:00:00');
        $input->display_sequence = 1;
        $input->save();

        $input_col = new DatabasesInputCols();
        $input_col->databases_inputs_id = $input->id;
        $input_col->databases_columns_id = $column->id;
        $input_col->value = (string)$upload->id;
        $input_col->save();

        $input_col->client_original_name = $upload->client_original_name;
        $input_col->download_count = 0;
        $input_col->play_count = 0;

        return [$input, $column, $input_col];
    }

    /**
     * テスト用のページ、フレーム、バケツ、データベース、対象型カラムを作成する。
     *
     * @return array{0: Page, 1: Frame, 2: DatabasesColumns}
     */
    private function createDatabaseSetup(string $column_type): array
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
        $database->databases_name = 'ファイル名表示テストDB';
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
        $column->column_name = 'アップロード';
        $column->required = 0;
        $column->display_sequence = 1;
        $column->save();

        return [$page, $frame, $column];
    }
}
