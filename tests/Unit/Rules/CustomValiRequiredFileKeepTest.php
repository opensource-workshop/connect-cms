<?php

namespace Tests\Unit\Rules;

use App\Models\User\Databases\DatabasesInputCols;
use App\Rules\CustomValiRequiredFileKeep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Tests\TestCase;

/**
 * CustomValiRequiredFileKeep ルールのユニットテスト
 *
 * 検証観点:
 * - 新規: 未アップロードはエラー
 * - 編集: 既存あり＋削除なしは成功
 * - 編集: 既存あり＋削除ありはエラー
 * - 新規アップロードがあれば成功
 */
class CustomValiRequiredFileKeepTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Request に擬似ルートを紐づけ、グローバル request() を差し替える
     *
     * @param Request $request 擬似リクエスト
     * @param int|null $row_id ルートパラメータ id（null で新規）
     * @return void
     */
    private function bindRequest(Request $request, ?int $row_id = null): void
    {
        // ルート風のスタブを用意し、parameter('id') を返す
        $route = new class($row_id) {
            private $row_id;
            public function __construct($row_id) { $this->row_id = $row_id; }
            public function parameter($key) { return $key === 'id' ? $this->row_id : null; }
        };
        $request->setRouteResolver(fn () => $route);

        // 現在のrequest() を差し替え
        $this->app->instance('request', $request);
    }

    /**
     * 新規: 未アップロードはエラー
     */
    public function testNewRecordWithoutUploadFails()
    {
        $column_id = 55;
        $rule = new CustomValiRequiredFileKeep($column_id);

        $request = Request::create('/dummy', 'POST');
        // id パラメータ未設定（新規）
        $this->bindRequest($request, null);

        $this->assertFalse($rule->passes("databases_columns_value.$column_id", null));
    }

    /**
     * 編集: 既存ファイルあり、削除チェックなし、アップロードなし → 成功
     */
    public function testEditWithExistingNoDeletePasses()
    {
        $column_id = 55;
        $inputs_id = 123;
        // 既存ファイルあり
        DatabasesInputCols::withoutEvents(function () use ($inputs_id, $column_id) {
            DatabasesInputCols::create([
                'databases_inputs_id' => $inputs_id,
                'databases_columns_id' => $column_id,
                'value' => 999, // uploads.id を想定した非NULL値
            ]);
        });

        $rule = new CustomValiRequiredFileKeep($column_id);

        $request = Request::create('/dummy', 'POST');
        $this->bindRequest($request, $inputs_id);

        $this->assertTrue($rule->passes("databases_columns_value.$column_id", null));
    }

    /**
     * 編集: 既存ファイルあり、削除チェックあり、アップロードなし → エラー
     */
    public function testEditWithExistingAndDeleteFails()
    {
        $column_id = 55;
        $inputs_id = 123;
        // 既存ファイルあり
        DatabasesInputCols::withoutEvents(function () use ($inputs_id, $column_id) {
            DatabasesInputCols::create([
                'databases_inputs_id' => $inputs_id,
                'databases_columns_id' => $column_id,
                'value' => 999,
            ]);
        });

        $rule = new CustomValiRequiredFileKeep($column_id);

        // 削除チェックあり（キー・値に同じIDが入る送信形）
        $request = Request::create('/dummy', 'POST', [
            'delete_upload_column_ids' => [ (string)$column_id => (string)$column_id ],
        ]);
        $this->bindRequest($request, $inputs_id);

        $this->assertFalse($rule->passes("databases_columns_value.$column_id", null));
    }

    /**
     * 新規アップロードがあれば成功
     */
    public function testNewUploadPasses()
    {
        $column_id = 55;
        $inputs_id = 123;

        $rule = new CustomValiRequiredFileKeep($column_id);

        // 新規アップロードを擬似
        $file = UploadedFile::fake()->create('sample.txt', 1, 'text/plain');
        $request = Request::create('/dummy', 'POST', [], [], [
            'databases_columns_value' => [ (string)$column_id => $file ],
        ]);
        $this->bindRequest($request, $inputs_id);

        $this->assertTrue($rule->passes("databases_columns_value.$column_id", null));
    }
}
