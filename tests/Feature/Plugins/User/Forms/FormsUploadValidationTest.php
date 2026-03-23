<?php

namespace Tests\Feature\Plugins\User\Forms;

use App\Enums\FormColumnType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\User\Forms\Forms;
use App\Models\User\Forms\FormsColumns;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * フォームのファイルアップロード制限を検証するFeatureテスト。
 */
class FormsUploadValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * テスト用フォーム（ファイル型カラム1つ）を作成する。
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
        $form = Forms::factory()->create([
            'bucket_id' => $bucket->id,
            'form_mode' => 'form',
            'data_save_flag' => 1,
        ]);
        $column = FormsColumns::factory()->create([
            'forms_id' => $form->id,
            'column_type' => FormColumnType::file,
            'column_name' => '添付ファイル',
            'required' => 1,
            'display_sequence' => 1,
        ]);

        return [$page, $frame, $column];
    }

    /**
     * 許可された拡張子/MIME/サイズのファイルは確認画面へ進み、uploadsに保存されること。
     */
    public function testPublicConfirmAcceptsAllowedFileUpload(): void
    {
        [$page, $frame, $column] = $this->createFormSetup();

        $file = UploadedFile::fake()->image('safe.jpg')->size(100);

        $response = $this->post(
            "/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $column->id => $file,
                ],
            ]
        );

        $response->assertStatus(200);
        $this->assertSame(1, Uploads::where('plugin_name', 'forms')->count());
        $this->assertDatabaseHas('uploads', [
            'plugin_name' => 'forms',
            'extension' => 'jpg',
            'temporary_flag' => 1,
        ]);
    }

    /**
     * 許可外拡張子は拒否され、uploadsに保存されないこと。
     */
    public function testPublicConfirmRejectsDisallowedExtension(): void
    {
        [$page, $frame, $column] = $this->createFormSetup();

        $file = UploadedFile::fake()->create('attack.js', 10, 'application/javascript');

        $response = $this->post(
            "/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $column->id => $file,
                ],
            ]
        );

        $response->assertStatus(200);
        $this->assertSame(0, Uploads::where('plugin_name', 'forms')->count());
    }

    /**
     * 許可外MIMEタイプは拒否され、uploadsに保存されないこと。
     */
    public function testPublicConfirmRejectsDisallowedMimetype(): void
    {
        [$page, $frame, $column] = $this->createFormSetup();

        $file = UploadedFile::fake()->create('mismatch.jpg', 10, 'text/html');

        $response = $this->post(
            "/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $column->id => $file,
                ],
            ]
        );

        $response->assertStatus(200);
        $this->assertSame(0, Uploads::where('plugin_name', 'forms')->count());
    }

    /**
     * 他拡張子で許可されるMIMEでも、拡張子との組み合わせが不一致なら拒否されること。
     */
    public function testPublicConfirmRejectsMimeTypeAllowedForDifferentExtension(): void
    {
        [$page, $frame, $column] = $this->createFormSetup();
        $column->rule_file_extensions = 'jpg,txt';
        $column->save();

        $file = UploadedFile::fake()->create('mismatch.jpg', 10, 'text/plain');

        $response = $this->post(
            "/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $column->id => $file,
                ],
            ]
        );

        $response->assertStatus(200);
        $this->assertSame(0, Uploads::where('plugin_name', 'forms')->count());
    }

    /**
     * 許可サイズを超えるファイルは拒否され、uploadsに保存されないこと。
     */
    public function testPublicConfirmRejectsOverLimitSize(): void
    {
        [$page, $frame, $column] = $this->createFormSetup();
        $column->rule_file_max_kb = 10;
        $column->save();

        $file = UploadedFile::fake()->image('large.jpg')->size(11);

        $response = $this->post(
            "/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $column->id => $file,
                ],
            ]
        );

        $response->assertStatus(200);
        $this->assertSame(0, Uploads::where('plugin_name', 'forms')->count());
    }

    /**
     * 最大サイズ未入力時はPHPのアップロード上限を使って拒否すること。
     */
    public function testPublicConfirmUsesPhpUploadLimitWhenColumnMaxIsEmpty(): void
    {
        [$page, $frame, $column] = $this->createFormSetup();
        $column->rule_file_max_kb = null;
        $column->save();

        $php_upload_max_kb = max(1, (int) floor(((float) UploadedFile::getMaxFilesize()) / 1024));
        if ($php_upload_max_kb > 8192) {
            $this->markTestSkipped('PHP upload上限が大きいため、このテストをスキップします。');
        }

        $file = UploadedFile::fake()->image('too-large.jpg')->size($php_upload_max_kb + 1);

        $response = $this->post(
            "/plugin/forms/publicConfirm/{$page->id}/{$frame->id}",
            [
                'forms_columns_value' => [
                    $column->id => $file,
                ],
            ]
        );

        $response->assertStatus(200);
        $this->assertSame(0, Uploads::where('plugin_name', 'forms')->count());
    }
}
