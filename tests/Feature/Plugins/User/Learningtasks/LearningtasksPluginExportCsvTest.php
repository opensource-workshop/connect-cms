<?php

namespace Tests\Feature\Plugins\User\Learningtasks;

use App\Enums\CsvCharacterCode;
use App\Enums\LearningtaskExportType;
use App\Enums\LearningtaskUseFunction;
use App\Enums\RoleName;
use App\Models\Common\Page;
use App\Models\Core\UsersRoles;
use App\User;
use App\Models\User\Learningtasks\Learningtasks;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Models\User\Learningtasks\LearningtasksUseSettings;
use App\Models\User\Learningtasks\LearningtasksUsers;
use App\Models\User\Learningtasks\LearningtasksUsersStatuses;
use App\Utilities\Csv\CsvUtils;
use App\Utilities\File\FileUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningtasksPluginExportCsvTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 各テスト前のセットアップ
     */
    protected function setUp(): void
    {
        parent::setUp();

        // DB Seeder を実行して初期データ(configs等)を投入
        $this->seed();
    }

    /** CSVファイルの内容を生成するヘルパー */
    private function createCsvContent(array $headers, array $rows): string
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $headers);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);
        return $content;
    }

    /** CSV文字列をパースして配列に変換するヘルパー (BOM除去も行う) */
    private function parseCsvString(string $csv_string): array
    {
        // UTF-8 BOM があれば除去
        if (strpos($csv_string, CsvUtils::bom) === 0) {
            $csv_string = substr($csv_string, strlen(CsvUtils::bom));
        }
        // 改行で各行に分割 (trimで最後の空行を除去)
        $lines = explode("\n", trim($csv_string));
        $data = [];
        foreach ($lines as $line) {
            if (!empty($line)) { // 空行は無視
                // PHP標準のstr_getcsvでパース (ダブルクォートやカンマを考慮)
                $data[] = str_getcsv($line);
            }
        }
        return $data;
    }

    /**
     * 正常系: 権限のあるユーザーが【全カラムオプション有効時】のレポート評価CSVを正しくエクスポートできるテスト
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-export
     */
    public function authorizedUserCanExportReportCsvWithAllColumns(): void
    {
        // Arrange: テストデータの準備
        // 1. ページ、課題投稿の親、課題投稿を作成
        $page = Page::factory()->create();
        $learningtask_parent = Learningtasks::factory()->create();
        $post = LearningtasksPosts::factory()->create([
            'learningtasks_id' => $learningtask_parent->id,
            'post_title' => 'エクスポートテスト用課題', // ファイル名確認のため
            'student_join_flag' => 3, // 配置ページのメンバーシップ受講者から選ぶ
            'teacher_join_flag' => 3, // 配置ページのメンバーシップ教員から選ぶ
        ]);

        // 2. エクスポート実行ユーザーを作成し、権限を付与 (例: 教員として登録)
        $exporter_user = User::factory()->create();
        LearningtasksUsers::factory()->create([
            'post_id' => $post->id,
            'user_id' => $exporter_user->id,
            'role_name' => RoleName::teacher
        ]);
        UsersRoles::factory()->create([
            'users_id' => $exporter_user->id,
            'target' => 'base',
            'role_name' => 'role_guest', // ゲスト
        ]);
        UsersRoles::factory()->create([
            'users_id' => $exporter_user->id,
            'target' => 'original_role',
            'role_name' => 'teacher', // 教員
        ]);

        // 3. レポート評価関連機能を有効にする設定を作成 (ColumnDefinition に影響する)
        LearningtasksUseSettings::factory()->create([
            'post_id' => $post->id, 'learningtasks_id' => $learningtask_parent->id,
            'use_function' => LearningtaskUseFunction::use_report, 'value' => 'on'
        ]);
        LearningtasksUseSettings::factory()->create([
            'post_id' => $post->id, 'learningtasks_id' => $learningtask_parent->id,
            'use_function' => LearningtaskUseFunction::use_report_comment, 'value' => 'on'
        ]);
        LearningtasksUseSettings::factory()->create([
            'post_id' => $post->id, 'learningtasks_id' => $learningtask_parent->id,
            'use_function' => LearningtaskUseFunction::use_report_file, 'value' => 'on'
        ]);
        LearningtasksUseSettings::factory()->create([
            'post_id' => $post->id, 'learningtasks_id' => $learningtask_parent->id,
            'use_function' => LearningtaskUseFunction::use_report_evaluate, 'value' => 'on'
        ]);
        LearningtasksUseSettings::factory()->create([
            'post_id' => $post->id, 'learningtasks_id' => $learningtask_parent->id,
            'use_function' => LearningtaskUseFunction::use_report_evaluate_comment, 'value' => 'on'
        ]);

        // 4. エクスポート対象の学生ユーザーと提出・評価データを作成
        $student1 = User::factory()->create(['userid' => 'student001', 'name' => 'テスト 学生壱']);
        $student2 = User::factory()->create(['userid' => 'student002', 'name' => 'テスト 学生弐']);
        // 受講生として課題投稿に登録
        LearningtasksUsers::factory()->create(['post_id' => $post->id, 'user_id' => $student1->id, 'role_name' => RoleName::student]);
        LearningtasksUsers::factory()->create(['post_id' => $post->id, 'user_id' => $student2->id, 'role_name' => RoleName::student]);
        // 提出記録と評価記録
        $submission1_created_at = now()->subDays(2)->toDateTimeString(); // 日時比較用
        $submission1 = LearningtasksUsersStatuses::factory()->create([
            'post_id' => $post->id,
            'user_id' => $student1->id,
            'task_status' => 1,
            'comment' => '学生1の提出本文',
            'upload_id' => 123, // 仮のファイルID
            'created_at' => $submission1_created_at
        ]);
        LearningtasksUsersStatuses::factory()->create([
            'post_id' => $post->id,
            'user_id' => $student1->id,
            'task_status' => 2,
            'grade' => 'A',
            'comment' => '学生1の評価コメント',
            'created_id' => $exporter_user->id,
            'created_at' => now()->subDays(1)
        ]);
        $submission2_created_at = now()->subHours(5)->toDateTimeString();
        $submission2 = LearningtasksUsersStatuses::factory()->create([
            'post_id' => $post->id,
            'user_id' => $student2->id,
            'task_status' => 1,
            'comment' => '学生2の本文のみ',
            'upload_id' => null, // ファイルなし
            'created_at' => $submission2_created_at
        ]);
        // student2 は評価なし (CSVでは評価関連カラムが空になるはず)

        // 5. 期待されるヘッダー (ColumnDefinition と設定に依存する)
        //    このテストでは評価と評価コメントが有効なので、それらが含まれる
        $expected_headers = ['ログインID', 'ユーザ名', '提出日時', '提出回数', '本文', 'ファイルURL', '評価', '評価コメント'];

        // 6. リクエストURLとパラメータを準備
        $frame_id = 1; // 仮のフレームID
        $url = "/download/plugin/learningtasks/exportCsv/{$page->id}/{$frame_id}/{$post->id}"; // Controller の exportCsv メソッドのURL
        $query_params = [
            'export_type' => LearningtaskExportType::report,
            'character_code' => CsvCharacterCode::utf_8, // テストは UTF-8 で行う
        ];

        // Act: 認証済みユーザーとしてGETリクエストをシミュレート
        $url_with_query = $url . '?' . http_build_query($query_params);
        $response = $this->actingAs($exporter_user)->get($url_with_query);

        // Assert: レスポンスの検証
        // 1. ステータスコード
        $response->assertStatus(200); // OK
        // 2. Content-Type ヘッダー
        $content_type_Header = $response->headers->get('Content-Type');
        $this->assertNotNull($content_type_Header, 'Content-Type header should exist.');
        $this->assertEquals('text/csv; charset=UTF-8', $content_type_Header, 'Content-Type header is incorrect.');

        // 3. Content-Disposition ヘッダー (ファイル名) の検証
        $raw_expected_filename = FileUtils::toValidFilename($post->post_title . '_Export.csv');
        $contentDispositionHeader = $response->headers->get('Content-Disposition');
        $this->assertNotNull($contentDispositionHeader, 'Content-Disposition header should exist.');

        // 3a. 'attachment' が含まれることを確認
        $this->assertStringContainsString('attachment', $contentDispositionHeader, 'Content-Disposition should indicate attachment.');

        // 3b. RFC 5987/6266 形式の filename* が正しいエンコードされたファイル名を含むことを確認
        $expected_filename_star_part = "filename*=utf-8''" . rawurlencode($raw_expected_filename);
        $this->assertStringContainsString($expected_filename_star_part, $contentDispositionHeader, 'Content-Disposition filename* part is incorrect.');

        // 4. CSV内容の検証
        // StreamedResponse の内容はコールバック実行時に出力されるため、
        // ob_start/ob_get_clean で出力バッファをキャプチャして取得します。
        ob_start();
        $response->sendContent(); // ストリームコールバックを実行し、内容を出力バッファへ
        $csv_content = ob_get_clean();
        $this->assertIsString($csv_content);
        // UTF-8 BOM の確認 (CsvUtils で付与されるため)
        $this->assertTrue(strpos($csv_content, CsvUtils::bom) === 0, 'UTF-8 CSV should have BOM');

        // CSV内容をパースして配列として検証
        $parsed_data = $this->parseCsvString($csv_content);

        $this->assertCount(3, $parsed_data, 'CSVはヘッダー行 + 学生2名分のデータ行であるべき'); // Header + 2 students
        // ヘッダー行の確認
        $this->assertEquals($expected_headers, $parsed_data[0], 'ヘッダー行が期待通りであること');
        // データ行1 (student1) の確認 - ヘッダー順に注意
        $site_url_for_file = url('/');
        $this->assertEquals($student1->userid, $parsed_data[1][0], 'Row1: ログインID');
        $this->assertEquals($student1->name, $parsed_data[1][1], 'Row1: ユーザ名');
        $this->assertEquals($submission1_created_at, (string)$parsed_data[1][2], 'Row1: 提出日時');
        $this->assertEquals('1', $parsed_data[1][3], 'Row1: 提出回数');
        $this->assertEquals('学生1の提出本文', $parsed_data[1][4], 'Row1: 本文');
        $this->assertEquals($site_url_for_file . '/file/123', $parsed_data[1][5], 'Row1: ファイルURL');
        $this->assertEquals('A', $parsed_data[1][6], 'Row1: 評価');
        $this->assertEquals('学生1の評価コメント', $parsed_data[1][7], 'Row1: 評価コメント');

        // データ行2 (student2) の確認
        $this->assertEquals($student2->userid, $parsed_data[2][0], 'Row2: ログインID');
        $this->assertEquals($student2->name, $parsed_data[2][1], 'Row2: ユーザ名');
        $this->assertEquals($submission2_created_at, (string)$parsed_data[2][2], 'Row2: 提出日時');
        $this->assertEquals('1', $parsed_data[2][3], 'Row2: 提出回数');
        $this->assertEquals('学生2の本文のみ', $parsed_data[2][4], 'Row2: 本文');
        $this->assertEquals('', $parsed_data[2][5], 'Row2: ファイルURL (なし)');
        $this->assertEquals('', $parsed_data[2][6], 'Row2: 評価 (なし)');
        $this->assertEquals('', $parsed_data[2][7], 'Row2: 評価コメント (なし)');
    }

    /**
     * 権限なし(ゲスト): CSVエクスポート(GET)がログインリダイレクトされるテスト
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-export-auth
     */
    public function guestCannotExportCsv(): void
    {
        // Arrange: アクセス先の準備
        $page = Page::factory()->create();
        $post = LearningtasksPosts::factory()->create();
        $frame_id = 1;
        $url_params = ['export_type' => LearningtaskExportType::report, 'character_code' => CsvCharacterCode::utf_8];
        $url = "/download/plugin/learningtasks/exportCsv/{$page->id}/{$frame_id}/{$post->id}" . '?' . http_build_query($url_params);

        // Act: ログインせずに GET リクエストを実行
        $response = $this->get($url);

        // Assert: 403レスポンス
        $response->assertStatus(200); // canメソッドチェック:Connect-CMSの仕様で200レスポンスとなる
        $response->assertSeeText('403 Forbidden');
    }

    /**
     * 権限なし(ログイン済): CSVエクスポート(GET)が403エラーになるテスト
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-export-auth
     */
    public function unauthorizedUserCannotExportCsv(): void
    {
        // Arrange: ページ、投稿、権限のないユーザーを準備
        $page = Page::factory()->create();
        $post = LearningtasksPosts::factory()->create();
        $unauthorized_user = User::factory()->create(); // 例: 学生ロールのみなど
        UsersRoles::factory()->create([
            'users_id' => $unauthorized_user->id,
            'target' => 'base',
            'role_name' => 'role_guest', // ゲスト
        ]);
        $frame_id = 1;
        $url_params = ['export_type' => LearningtaskExportType::report, 'character_code' => CsvCharacterCode::utf_8];
        $url = "/download/plugin/learningtasks/exportCsv/{$page->id}/{$frame_id}/{$post->id}" . '?' . http_build_query($url_params);

        // Act: 権限のないユーザーとして GET リクエストを実行
        $response = $this->actingAs($unauthorized_user)->get($url);

        // Assert: アクセス拒否 (Controller の canExport -> abort(403) を想定)
        $response->assertStatus(403);
    }

    // ===============================================
    // Feature Tests - Export Data/Settings Variation Cases
    // ===============================================

    /**
     * 設定依存: オプションカラム設定が無効な場合、基本ヘッダーとデータのみが出力されるテスト
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-export
     */
    public function exportReturnsOnlyBaseHeadersAndDataWhenOptionalSettingsDisabled(): void
    {
        // Arrange
        $page = Page::factory()->create();
        $learningtask_parent = Learningtasks::factory()->create();
        $post = LearningtasksPosts::factory()->create([
            'learningtasks_id' => $learningtask_parent->id,
            'post_title' => '基本カラムテスト',
            'student_join_flag' => 3,
            'teacher_join_flag' => 3
        ]);
        $exporter_user = User::factory()->create();
        UsersRoles::factory()->create([
            'users_id' => $exporter_user->id,
            'target' => 'base',
            'role_name' => 'role_guest', // ゲスト
        ]);
        UsersRoles::factory()->create([
            'users_id' => $exporter_user->id,
            'target' => 'base',
            'role_name' => 'role_guest', // ゲスト
        ]);
        LearningtasksUsers::factory()->create(['post_id' => $post->id, 'user_id' => $exporter_user->id, 'role_name' => RoleName::teacher]);

        // オプション設定を明示的に作成しない

        $student1 = User::factory()->create(['userid' => 's001', 'name' => '学生 甲']);
        LearningtasksUsers::factory()->create(['post_id' => $post->id, 'user_id' => $student1->id, 'role_name' => RoleName::student]);
        $submission1_created_at = now()->subDays(2)->toDateTimeString();
        LearningtasksUsersStatuses::factory()->create([
            'post_id' => $post->id, 'user_id' => $student1->id, 'task_status' => 1,
            'comment' => 'これは提出本文だがエクスポートされないはず', // データはあっても設定オフなら出ない
            'grade' => 'A', // データはあっても設定オフなら出ない
            'created_at' => $submission1_created_at
        ]);

        // 期待されるヘッダーは基本カラムのみ
        $expected_headers = ['ログインID', 'ユーザ名', '提出日時', '提出回数'];

        $frame_id = 1;
        $url_params = ['export_type' => LearningtaskExportType::report, 'character_code' => CsvCharacterCode::utf_8];
        $url = "/download/plugin/learningtasks/exportCsv/{$page->id}/{$frame_id}/{$post->id}" . '?' . http_build_query($url_params);

        // Act
        $response = $this->actingAs($exporter_user)->get($url);

        // Assert
        $response->assertStatus(200);
        ob_start();
        $response->sendContent(); // ストリームコールバックを実行し、内容を出力バッファへ
        $csv_content = ob_get_clean();
        $parsed_data = $this->parseCsvString($csv_content);
        $this->assertCount(2, $parsed_data, 'ヘッダー行 + 学生1名分のデータ行であるべき');
        $this->assertEquals($expected_headers, $parsed_data[0], 'ヘッダーが基本カラムのみであること');
        // データ行の確認 (基本カラムのみ)
        $this->assertEquals($student1->userid, $parsed_data[1][0]);
        $this->assertEquals($student1->name, $parsed_data[1][1]);
        $this->assertEquals($submission1_created_at, (string)$parsed_data[1][2]);
        $this->assertEquals('1', $parsed_data[1][3]); // 提出回数
        $this->assertCount(4, $parsed_data[1], 'データ行のカラム数が4であること'); // オプションカラムがないことを確認
    }

    /**
     * データなし: 対象学生がいない場合にヘッダー行のみのCSVが出力されるテスト
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-export
     */
    public function exportReturnsOnlyHeadersWhenNoStudents(): void
    {
        // Arrange
        $page = Page::factory()->create();
        $learningtask_parent = Learningtasks::factory()->create();
        $post = LearningtasksPosts::factory()->create(['learningtasks_id' => $learningtask_parent->id, 'student_join_flag' => 3, 'teacher_join_flag' => 3]);
        $exporter_user = User::factory()->create();
        LearningtasksUsers::factory()->create([
            'post_id' => $post->id,
            'user_id' => $exporter_user->id,
            'role_name' => RoleName::teacher
        ]);
        UsersRoles::factory()->create([
            'users_id' => $exporter_user->id,
            'target' => 'base',
            'role_name' => 'role_guest', // ゲスト
        ]);
        UsersRoles::factory()->create([
            'users_id' => $exporter_user->id,
            'target' => 'original_role',
            'role_name' => 'teacher', // 教員
        ]);
        // 評価設定は有効にしておく（ヘッダーが正しく定義されるように）
        LearningtasksUseSettings::factory()->create(['post_id' => $post->id,'learningtasks_id' => $learningtask_parent->id,'use_function' => LearningtaskUseFunction::use_report_evaluate,'value' => 'on']);
        LearningtasksUseSettings::factory()->create(['post_id' => $post->id,'learningtasks_id' => $learningtask_parent->id,'use_function' => LearningtaskUseFunction::use_report_evaluate_comment,'value' => 'on']);
        // 学生は登録しない

        $expected_headers = ['ログインID', 'ユーザ名', '提出日時', '提出回数', '評価', '評価コメント'];

        $frame_id = 1;
        $url_params = ['export_type' => LearningtaskExportType::report, 'character_code' => CsvCharacterCode::utf_8];
        $url = "/download/plugin/learningtasks/exportCsv/{$page->id}/{$frame_id}/{$post->id}" . '?' . http_build_query($url_params);

        // Act
        $response = $this->actingAs($exporter_user)->get($url);

        // Assert
        $response->assertStatus(200);
        ob_start();
        $response->sendContent(); // ストリームコールバックを実行し、内容を出力バッファへ
        $csv_content = ob_get_clean();
        $parsed_data = $this->parseCsvString($csv_content);

        // ヘッダー行のみで、データ行がないことを確認
        $this->assertCount(1, $parsed_data, 'ヘッダー行のみであるべき');
        $this->assertEquals($expected_headers, $parsed_data[0], 'ヘッダー行の内容が正しいこと');
    }
}
