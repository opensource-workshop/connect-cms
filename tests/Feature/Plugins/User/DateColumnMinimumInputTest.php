<?php

namespace Tests\Feature\Plugins\User;

use App\Enums\DatabaseColumnType;
use App\Enums\FormColumnType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\User\Databases\Databases;
use App\Models\User\Databases\DatabasesColumns;
use App\Models\User\Databases\DatabasesFrames;
use App\Models\User\Forms\Forms;
use App\Models\User\Forms\FormsColumns;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * フォームとデータベースの日付下限を入力画面と確認処理で検証する。
 */
class DateColumnMinimumInputTest extends TestCase
{
    use DefaultBucketRolesFeatureTestTrait;
    use RefreshDatabase;

    /**
     * フォームでは「0」のとき当日から受け付け、未設定なら過去日も受け付ける。
     */
    public function testFormsDateMinimum(): void
    {
        $this->seed();
        [$page, $frame, $bucket] = $this->createFrame('forms');
        $form = Forms::factory()->create(['bucket_id' => $bucket->id]);
        $column = FormsColumns::factory()->create([
            'forms_id' => $form->id,
            'column_type' => FormColumnType::date,
            'rule_date_after_equal' => '0',
        ]);

        $url = "/plugin/forms/publicConfirm/{$page->id}/{$frame->id}";
        $today = Carbon::today();
        $post_data = ['forms_columns_value' => [$column->id => $today->copy()->subDay()->format('Y/m/d')]];

        $this->post($url, $post_data)
            ->assertOk()
            ->assertSee('minDate: new Date("' . $today->format('Y-m-d') . 'T00:00:00")', false)
            ->assertDontSee('以下の内容でよろしいですか？');

        $this->post("/redirect/plugin/forms/publicStore/{$page->id}/{$frame->id}", $post_data)
            ->assertRedirect();
        $this->assertDatabaseMissing('forms_inputs', ['forms_id' => $form->id]);

        $this->post($url, ['forms_columns_value' => [$column->id => $today->format('Y/m/d')]])
            ->assertOk()
            ->assertSee('以下の内容でよろしいですか？');

        $column->update(['rule_date_after_equal' => null]);
        $this->post($url, $post_data)->assertOk()->assertSee('以下の内容でよろしいですか？');
    }

    /**
     * データベースでは「0」と正の日数に応じて入力できる最初の日が変わる。
     */
    public function testDatabasesDateMinimum(): void
    {
        $this->seed();
        [$page, $frame, $bucket] = $this->createFrame('databases');
        $database = new Databases();
        $database->bucket_id = $bucket->id;
        $database->databases_name = '日付型テストDB';
        $database->data_save_flag = 1;
        $database->save();

        DatabasesFrames::create([
            'databases_id' => $database->id,
            'frames_id' => $frame->id,
            'view_count' => 10,
            'use_filter_flag' => 0,
        ]);

        $column = new DatabasesColumns([
            'databases_id' => $database->id,
            'column_type' => DatabaseColumnType::date,
            'column_name' => '日付',
            'required' => 0,
            'display_sequence' => 1,
        ]);
        $column->rule_date_after_equal = '0';
        $column->save();

        $this->actingAs($this->createContentAdminUser());
        $url = "/plugin/databases/publicConfirm/{$page->id}/{$frame->id}";
        $today = Carbon::today();
        $post_data = [
            'posted_at' => $today->format('Y-m-d H:i'),
            'expires_at' => '',
            'display_sequence' => '1',
            'categories_id' => '',
            'databases_columns_value' => [$column->id => $today->copy()->subDay()->format('Y/m/d')],
        ];

        $this->post($url, $post_data)
            ->assertOk()
            ->assertSee('minDate: new Date("' . $today->format('Y-m-d') . 'T00:00:00")', false)
            ->assertDontSee('以下の内容でよろしいですか？');

        $post_data['databases_columns_value'][$column->id] = $today->format('Y/m/d');
        $this->post($url, $post_data)->assertOk()->assertSee('以下の内容でよろしいですか？');

        $column->rule_date_after_equal = '1';
        $column->save();
        $this->post($url, $post_data)->assertOk()->assertDontSee('以下の内容でよろしいですか？');

        $post_data['databases_columns_value'][$column->id] = $today->copy()->addDay()->format('Y/m/d');
        $this->post($url, $post_data)->assertOk()->assertSee('以下の内容でよろしいですか？');
    }

    /**
     * 指定プラグインの公開リクエストに必要なページとフレームを作る。
     */
    private function createFrame(string $plugin): array
    {
        $page = Page::factory()->create();
        $bucket = Buckets::factory()->create(['plugin_name' => $plugin]);
        $frame = Frame::factory()->create([
            'page_id' => $page->id,
            'area_id' => 2,
            'plugin_name' => $plugin,
            'bucket_id' => $bucket->id,
            'template' => 'default',
        ]);

        return [$page, $frame, $bucket];
    }
}
