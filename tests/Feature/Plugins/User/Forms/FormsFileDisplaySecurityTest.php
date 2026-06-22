<?php

namespace Tests\Feature\Plugins\User\Forms;

use App\Enums\FormColumnType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\User\Forms\Forms;
use App\Models\User\Forms\FormsColumns;
use App\Models\User\Forms\FormsInputCols;
use App\Models\User\Forms\FormsInputs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * フォーム登録一覧のファイル名由来表示がHTMLとして解釈されないことを検証する。
 *
 * 保存済みアップロードの元ファイル名を表示テンプレートへ渡し、
 * file型のリンク本文とファイルID由来のhref属性が表示時に安全化されることを守る。
 */
class FormsFileDisplaySecurityTest extends TestCase
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
     * 登録一覧のfile型では、元ファイル名にHTML断片が含まれてもリンク本文としてだけ表示されること。
     */
    public function testListInputsEscapesFileColumnOriginalName(): void
    {
        $upload = Uploads::factory()->create([
            'client_original_name' => '<img src=x onerror=alert(1)>.pdf',
            'extension' => 'pdf',
            'plugin_name' => 'forms',
        ]);
        $bucket = Buckets::factory()->create(['plugin_name' => 'forms']);
        $form = Forms::factory()->create(['bucket_id' => $bucket->id]);
        $column = FormsColumns::factory()->create([
            'forms_id' => $form->id,
            'column_type' => FormColumnType::file,
        ]);
        $input = FormsInputs::factory()->create([
            'forms_id' => $form->id,
        ]);
        $input_col = FormsInputCols::factory()->create([
            'forms_inputs_id' => $input->id,
            'forms_columns_id' => $column->id,
            'value' => (string)$upload->id,
        ]);
        $input_col->client_original_name = $upload->client_original_name;

        $html = view('plugins.user.forms.default.forms_include_value', [
            'input_cols' => collect([$input_col]),
            'input' => $input,
            'column' => $column,
        ])->render();

        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;.pdf', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $html);
    }

    /**
     * 登録一覧のfile型では、保存済みのファイルID値が属性を壊す文字列でもhrefへ混入しないこと。
     */
    public function testListInputsConstrainsFileColumnHrefToUploadId(): void
    {
        [$page, , $form] = $this->createFormSetup();
        $upload = Uploads::factory()->create([
            'client_original_name' => 'report.pdf',
            'extension' => 'pdf',
            'plugin_name' => 'forms',
            'page_id' => $page->id,
        ]);
        $column = FormsColumns::factory()->create([
            'forms_id' => $form->id,
            'column_type' => FormColumnType::file,
        ]);
        $input = FormsInputs::factory()->create([
            'forms_id' => $form->id,
        ]);
        $input_col = FormsInputCols::factory()->create([
            'forms_inputs_id' => $input->id,
            'forms_columns_id' => $column->id,
            'value' => $upload->id . '" onclick="alert(1)',
        ]);
        $input_col->client_original_name = $upload->client_original_name;

        $html = view('plugins.user.forms.default.forms_include_value', [
            'input_cols' => collect([$input_col]),
            'input' => $input,
            'column' => $column,
        ])->render();

        $this->assertStringContainsString('/file/' . $upload->id . '" target="_blank"', $html);
        $this->assertStringNotContainsString('onclick="alert(1)', $html);
    }

    /**
     * 最終登録では、確認画面のhidden値を改ざんしたファイルID文字列を保存できないこと。
     */
    public function testPublicStoreRejectsTamperedFileColumnValue(): void
    {
        [$page, $frame, $form] = $this->createFormSetup();
        $column = FormsColumns::factory()->create([
            'forms_id' => $form->id,
            'column_type' => FormColumnType::file,
            'required' => 0,
        ]);

        $response = $this->post(
            "/redirect/plugin/forms/publicStore/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $column->id => '1" onclick="alert(1)',
                ],
                'redirect_path' => "/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            ]
        );

        $response->assertStatus(302);
        $this->assertDatabaseMissing('forms_input_cols', [
            'forms_columns_id' => $column->id,
            'value' => '1" onclick="alert(1)',
        ]);
    }

    /**
     * 最終登録では、同じページのフォームでアップロードされた正規のファイルIDは保存できること。
     */
    public function testPublicStoreAcceptsValidFileColumnValue(): void
    {
        [$page, $frame, $form] = $this->createFormSetup();
        $column = FormsColumns::factory()->create([
            'forms_id' => $form->id,
            'column_type' => FormColumnType::file,
            'required' => 0,
        ]);
        $upload = Uploads::factory()->create([
            'client_original_name' => 'report.pdf',
            'extension' => 'pdf',
            'plugin_name' => 'forms',
            'page_id' => $page->id,
            'temporary_flag' => 1,
        ]);

        $response = $this->post(
            "/redirect/plugin/forms/publicStore/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $column->id => (string)$upload->id,
                ],
                'redirect_path' => "/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            ]
        );

        $response->assertStatus(302);
        $this->assertDatabaseHas('forms_input_cols', [
            'forms_columns_id' => $column->id,
            'value' => (string)$upload->id,
        ]);
        $this->assertDatabaseHas('uploads', [
            'id' => $upload->id,
            'temporary_flag' => 0,
        ]);
    }

    /**
     * 表示と登録の境界を検証するためのページ、フレーム、バケツ、フォームを作成する。
     *
     * @return array{0: Page, 1: Frame, 2: Forms}
     */
    private function createFormSetup(): array
    {
        $page = Page::factory()->create();
        $bucket = Buckets::factory()->create(['plugin_name' => 'forms']);
        $frame = Frame::factory()->create([
            'page_id' => $page->id,
            'plugin_name' => 'forms',
            'bucket_id' => $bucket->id,
        ]);
        $form = Forms::factory()->create(['bucket_id' => $bucket->id]);

        return [$page, $frame, $form];
    }
}
