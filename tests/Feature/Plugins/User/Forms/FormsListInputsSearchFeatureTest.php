<?php

namespace Tests\Feature\Plugins\User\Forms;

use App\Enums\FormColumnType;
use App\Enums\FormStatusType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\User\Forms\Forms;
use App\Models\User\Forms\FormsColumns;
use App\Models\User\Forms\FormsInputCols;
use App\Models\User\Forms\FormsInputs;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * フォーム登録一覧の検索と表示件数制御を検証する。
 *
 * HTTP経路で登録一覧を開き、利用者が入力値・状態・登録日・表示件数で
 * 目的の登録データへ絞り込めることを守る。
 */
class FormsListInputsSearchFeatureTest extends TestCase
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
     * 登録一覧では、フォーム項目の入力値に一致する登録データだけを表示できること。
     */
    public function testListInputsCanSearchByInputValue(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $form] = $this->createFormSetup();
        $column = FormsColumns::factory()->textType()->create(['forms_id' => $form->id]);

        $this->createFormInput($form, $column, 'MATCHする回答');
        $this->createFormInput($form, $column, '対象外の回答');

        $response = $this->actingAs($admin)
            ->get("/plugin/forms/listInputs/{$page->id}/{$frame->id}/{$form->id}?keyword=MATCH");

        $response->assertOk();
        $response->assertSee('MATCHする回答');
        $response->assertDontSee('対象外の回答');
        $response->assertSee('name="keyword"', false);
        $response->assertSee('value="MATCH"', false);
        $response->assertSee('クリア');
    }

    /**
     * 登録一覧では、ファイル型項目に添付されたファイル名で登録データを検索できること。
     */
    public function testListInputsCanSearchByUploadedFileName(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $form] = $this->createFormSetup();
        $file_column = FormsColumns::factory()->create([
            'forms_id' => $form->id,
            'column_type' => FormColumnType::file,
        ]);
        $text_column = FormsColumns::factory()->textType()->create(['forms_id' => $form->id]);
        $target_upload = Uploads::factory()->create(['client_original_name' => 'target-report.pdf']);
        $other_upload = Uploads::factory()->create(['client_original_name' => 'other-report.pdf']);

        $target_input = $this->createFormInput($form, $file_column, (string)$target_upload->id);
        $this->createFormInputCol($target_input, $text_column, 'ファイル名検索の対象');
        $other_input = $this->createFormInput($form, $file_column, (string)$other_upload->id);
        $this->createFormInputCol($other_input, $text_column, 'ファイル名検索の対象外');

        $response = $this->actingAs($admin)
            ->get("/plugin/forms/listInputs/{$page->id}/{$frame->id}/{$form->id}?keyword=target-report");

        $response->assertOk();
        $response->assertSee('ファイル名検索の対象');
        $response->assertDontSee('ファイル名検索の対象外');
        $response->assertSee('target-report.pdf');
        $response->assertDontSee('other-report.pdf');
    }

    /**
     * 登録一覧の添付ファイル名検索では、通常入力値がアップロードIDと一致してもファイル名では誤一致しないこと。
     */
    public function testListInputsDoesNotMatchUploadFileNameThroughTextValue(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $form] = $this->createFormSetup();
        $text_column = FormsColumns::factory()->textType()->create(['forms_id' => $form->id]);
        $upload = Uploads::factory()->create(['client_original_name' => 'private-file-name.pdf']);

        $this->createFormInput($form, $text_column, (string)$upload->id);

        $response = $this->actingAs($admin)
            ->get("/plugin/forms/listInputs/{$page->id}/{$frame->id}/{$form->id}?keyword=private-file-name");

        $response->assertOk();
        $response->assertSee('0 件');
        $response->assertDontSee('private-file-name.pdf');
    }

    /**
     * 登録一覧のキーワード検索では、ファイル型項目に保存されたアップロードID自体は検索対象にしないこと。
     */
    public function testListInputsDoesNotSearchUploadIdAsFileColumnValue(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $form] = $this->createFormSetup();
        $file_column = FormsColumns::factory()->create([
            'forms_id' => $form->id,
            'column_type' => FormColumnType::file,
        ]);
        $upload = Uploads::factory()->create(['client_original_name' => 'file-id-target.pdf']);

        $this->createFormInput($form, $file_column, (string)$upload->id);

        $response = $this->actingAs($admin)
            ->get("/plugin/forms/listInputs/{$page->id}/{$frame->id}/{$form->id}?keyword={$upload->id}");

        $response->assertOk();
        $response->assertSee('0 件');
        $response->assertDontSee('file-id-target.pdf');
    }

    /**
     * 登録一覧では、状態と登録日の範囲指定を組み合わせて登録データを絞り込めること。
     */
    public function testListInputsCanSearchByStatusAndCreatedDate(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $form] = $this->createFormSetup();
        $column = FormsColumns::factory()->textType()->create(['forms_id' => $form->id]);

        $this->createFormInput($form, $column, '対象の仮登録', [
            'status' => FormStatusType::temporary,
            'created_at' => Carbon::parse('2026-05-10 10:00:00'),
        ]);
        $this->createFormInput($form, $column, '日付範囲外の仮登録', [
            'status' => FormStatusType::temporary,
            'created_at' => Carbon::parse('2026-04-30 10:00:00'),
        ]);
        $this->createFormInput($form, $column, '本登録の回答', [
            'status' => FormStatusType::active,
            'created_at' => Carbon::parse('2026-05-10 10:00:00'),
        ]);

        $response = $this->actingAs($admin)->get(
            "/plugin/forms/listInputs/{$page->id}/{$frame->id}/{$form->id}"
            . '?status=1&created_from=2026-05-01&created_to=2026-05-31'
        );

        $response->assertOk();
        $response->assertSee('対象の仮登録');
        $response->assertDontSee('日付範囲外の仮登録');
        $response->assertDontSee('本登録の回答');
        $response->assertSee('value="1"', false);
        $response->assertSee('selected >仮登録', false);
        $response->assertSee('value="2026-05-01"', false);
        $response->assertSee('value="2026-05-31"', false);
    }

    /**
     * 登録日の範囲検索では、開始日と終了日の当日中の登録を含み、その前後の日付は除外すること。
     */
    public function testListInputsCreatedDateSearchIncludesBoundaryDates(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $form] = $this->createFormSetup();
        $column = FormsColumns::factory()->textType()->create(['forms_id' => $form->id]);

        $this->createFormInput($form, $column, '開始日ちょうどの回答', [
            'created_at' => Carbon::parse('2026-05-01 00:00:00'),
        ]);
        $this->createFormInput($form, $column, '終了日終端の回答', [
            'created_at' => Carbon::parse('2026-05-31 23:59:59'),
        ]);
        $this->createFormInput($form, $column, '開始日前日の回答', [
            'created_at' => Carbon::parse('2026-04-30 23:59:59'),
        ]);
        $this->createFormInput($form, $column, '終了日翌日の回答', [
            'created_at' => Carbon::parse('2026-06-01 00:00:00'),
        ]);

        $response = $this->actingAs($admin)->get(
            "/plugin/forms/listInputs/{$page->id}/{$frame->id}/{$form->id}"
            . '?created_from=2026-05-01&created_to=2026-05-31'
        );

        $response->assertOk();
        $response->assertSee('開始日ちょうどの回答');
        $response->assertSee('終了日終端の回答');
        $response->assertDontSee('開始日前日の回答');
        $response->assertDontSee('終了日翌日の回答');
        $response->assertSee('created_from=2026-05-01', false);
        $response->assertSee('created_to=2026-05-31', false);
    }

    /**
     * 登録一覧では、表示件数を超える登録データがあっても指定件数分だけ一覧に表示すること。
     */
    public function testListInputsDisplaysOnlyPerPageCount(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $form] = $this->createFormSetup();
        $column = FormsColumns::factory()->textType()->create(['forms_id' => $form->id]);

        for ($i = 1; $i <= 11; $i++) {
            $this->createFormInput($form, $column, sprintf('表示件数上限確認%02d', $i), [
                'created_at' => Carbon::parse("2026-05-{$i} 10:00:00"),
            ]);
        }

        $response = $this->actingAs($admin)
            ->get("/plugin/forms/listInputs/{$page->id}/{$frame->id}/{$form->id}");

        $response->assertOk();
        $this->assertSame(10, substr_count($response->getContent(), '表示件数上限確認'));
        $response->assertDontSee('表示件数上限確認01');
    }

    /**
     * 登録一覧では、表示件数を増やすと初期表示件数では次ページになる登録データも同じページで確認できること。
     */
    public function testListInputsCanChangePerPage(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $form] = $this->createFormSetup();
        $column = FormsColumns::factory()->textType()->create(['forms_id' => $form->id]);

        for ($i = 1; $i <= 11; $i++) {
            $this->createFormInput($form, $column, sprintf('表示件数確認%02d', $i), [
                'created_at' => Carbon::parse("2026-05-{$i} 10:00:00"),
            ]);
        }

        $this->actingAs($admin)->post(
            "/redirect/plugin/forms/indexCount/{$page->id}/{$frame->id}",
            [
                'view_count_spectator' => 20,
                'redirect_path' => "/plugin/forms/listInputs/{$page->id}/{$frame->id}/{$form->id}#frame-{$frame->id}",
            ]
        )->assertStatus(302);

        $changed_response = $this->actingAs($admin)
            ->get("/plugin/forms/listInputs/{$page->id}/{$frame->id}/{$form->id}");

        $changed_response->assertOk();
        $changed_response->assertSee('表示件数確認01');
        $changed_response->assertSee('value="20"', false);
        $changed_response->assertSee('selected >20件', false);
    }

    /**
     * 登録一覧検索に必要なページ、フレーム、バケツ、フォームをまとめて作成する。
     *
     * @return array{0: Page, 1: Frame, 2: Forms}
     */
    private function createFormSetup(): array
    {
        $page = Page::factory()->create();
        $bucket = Buckets::factory()->create(['plugin_name' => 'forms']);
        $frame = Frame::create([
            'page_id' => $page->id,
            'area_id' => 2,
            'plugin_name' => 'forms',
            'bucket_id' => $bucket->id,
            'template' => 'default',
            'display_sequence' => 1,
        ]);
        $form = Forms::factory()->create(['bucket_id' => $bucket->id]);

        return [$page, $frame, $form];
    }

    /**
     * 登録一覧検索用に登録データと指定カラムの入力値を作成する。
     */
    private function createFormInput(Forms $form, FormsColumns $column, string $value, array $attributes = []): FormsInputs
    {
        $input = FormsInputs::factory()->create(array_merge([
            'forms_id' => $form->id,
            'created_at' => Carbon::parse('2026-05-01 10:00:00'),
        ], $attributes));

        FormsInputCols::factory()->create([
            'forms_inputs_id' => $input->id,
            'forms_columns_id' => $column->id,
            'value' => $value,
        ]);

        return $input;
    }

    /**
     * 1つの登録データに複数カラムの検索値を持たせるための入力値を追加する。
     */
    private function createFormInputCol(FormsInputs $input, FormsColumns $column, string $value): FormsInputCols
    {
        return FormsInputCols::factory()->create([
            'forms_inputs_id' => $input->id,
            'forms_columns_id' => $column->id,
            'value' => $value,
        ]);
    }
}
