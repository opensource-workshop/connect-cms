<?php

namespace Tests\Feature\Plugins\User\Learningtasks;

use App\Enums\CsvCharacterCode;
use App\Enums\LearningtaskImportType;
use App\Enums\LearningtaskUseFunction;
use App\Enums\RoleName;
use App\Models\Common\Group;
use App\Models\Common\GroupUser;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Models\Core\UsersRoles;
use App\Models\User\Learningtasks\Learningtasks;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Models\User\Learningtasks\LearningtasksUsers;
use App\Models\User\Learningtasks\LearningtasksUsersStatuses;
use App\Models\User\Learningtasks\LearningtasksUseSettings;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Utilities\Csv\CsvUtils;

class LearningtasksPluginImportCsvTest extends TestCase
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

    /**
     * 正常系: 権限のあるユーザーが有効なCSVでレポート評価をインポートできるテスト
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-import
     */
    public function authorizedUserCanImportValidReportCsv(): void
    {
        // Arrange: テストデータの準備
        // 1. ページ、課題投稿、インポート実行ユーザー(管理者or教員)を作成
        $page = Page::factory()->create();
        // Learningtasks レコードが必要な場合がある
        $learningtask = Learningtasks::factory()->create();
        $post = LearningtasksPosts::factory()->create([
            'learningtasks_id' => $learningtask->id,
            'student_join_flag' => 3,
            'teacher_join_flag' => 3,
        ]);
        // 課題投稿に関連する設定を作成
        // (レポート評価機能を有効にするための設定)
        LearningtasksUseSettings::factory()->create([
            'post_id' => $post->id,
            'use_function' => LearningtaskUseFunction::use_report_evaluate,
            'value' => 'on',
        ]);
        LearningtasksUseSettings::factory()->create([
            'post_id' => $post->id,
            'use_function' => LearningtaskUseFunction::use_report_evaluate_comment,
            'value' => 'on',
        ]);
        $importer_user = User::factory()->create();
        UsersRoles::factory()->create([
            'users_id' => $importer_user->id,
            'target' => 'base',
            'role_name' => 'role_guest', // ゲスト
        ]);
        UsersRoles::factory()->create([
            'users_id' => $importer_user->id,
            'target' => 'original_role',
            'role_name' => 'teacher', // 教員
        ]);
        // 権限設定: このユーザーが教員としてインポートを実行できるようにする
        LearningtasksUsers::factory()->create([
            'post_id' => $post->id, 'user_id' => $importer_user->id, 'role_name' => RoleName::teacher
        ]);

        // 2. インポート対象の学生ユーザーと提出記録を作成
        $student1 = User::factory()->create(['userid' => 'student001']);
        $student2 = User::factory()->create(['userid' => 'student002']);
        // 受講生として登録 (Processor のチェックをパスするため)
        LearningtasksUsers::factory()->create(['post_id' => $post->id, 'user_id' => $student1->id, 'role_name' => RoleName::student]);
        LearningtasksUsers::factory()->create(['post_id' => $post->id, 'user_id' => $student2->id, 'role_name' => RoleName::student]);
        // 最新の提出記録を作成
        $submission_student1 =LearningtasksUsersStatuses::factory()->create(['post_id' => $post->id, 'user_id' => $student1->id, 'task_status' => 1]);
        $submission_student2 = LearningtasksUsersStatuses::factory()->create(['post_id' => $post->id, 'user_id' => $student2->id, 'task_status' => 1]);

        // 3. アップロードするCSVファイルの内容と偽ファイルを作成
        //    (レポート評価インポート用のヘッダーとデータ)
        $headers = ['ログインID', 'ユーザ名', '提出日時', '提出回数', '評価', '評価コメント'];
        $rows = [
            [$student1->userid, $student1->name, $submission_student1->created_at, '1', 'A', 'Comment Good'],
            [$student2->userid, $student2->name, $submission_student2->created_at, '1', 'B', 'Comment OK'],
        ];
        $csv_content = $this->createCsvContent($headers, $rows);
        $fake_file = UploadedFile::fake()->createWithContent('report_import.csv', $csv_content);

        // 4. リクエストURLとパラメータを準備
        $frame_id = 1; // 仮のフレームID
        // ★ URL は実際の Connect-CMS のルーティングに合わせる
        $url = "/redirect/plugin/learningtasks/importCsv/{$page->id}/{$frame_id}/{$post->id}";
        $expected_redirect_url = "/plugin/learningtasks/show/{$page->id}/{$frame_id}/{$post->id}";
        $post_data = [
            'csv_file' => $fake_file,
            'import_type' => LearningtaskImportType::report,
            'redirect_path' => $expected_redirect_url,
        ];

        // Act: 認証済みユーザーとしてPOSTリクエストをシミュレート
        $response = $this->actingAs($importer_user)
                         ->post($url, $post_data);

        // Assert: レスポンスとDBの状態を検証
        // 1. 正しいレポート表示画面にリダイレクトされること
        $response->assertStatus(302); // リダイレクトステータス
        $response->assertRedirect($expected_redirect_url); // リダイレクト先URL

        // 2. フラッシュメッセージのキーと内容を確認
        // キー 'flash_message' に期待する成功メッセージ文字列が含まれているか
        $expected_message = "CSVインポート処理完了：成功 2件。";
        $response->assertSessionHas('flash_message', $expected_message);

        // 3. エラー関連のセッションがないこと
        $response->assertSessionHasNoErrors(); // Laravel標準バリデーションエラーがないこと
        $this->assertNull(session('csv_import_errors'), '詳細エラーがフラッシュされていないこと');

        // 4. データベースに評価レコードが正しく作成されていること
        $this->assertDatabaseHas('learningtasks_users_statuses', [
            'post_id' => $post->id, 'user_id' => $student1->id, 'task_status' => 2, 'grade' => 'A', 'comment' => 'Comment Good', 'created_id' => $importer_user->id
        ]);
        $this->assertDatabaseHas('learningtasks_users_statuses', [
            'post_id' => $post->id, 'user_id' => $student2->id, 'task_status' => 2, 'grade' => 'B', 'comment' => 'Comment OK', 'created_id' => $importer_user->id
        ]);
        // 提出2件 + 評価2件 = 4件になっているはず
        $this->assertDatabaseCount('learningtasks_users_statuses', 4);
    }

    // ===============================================
    // Feature Tests - File Validation Cases for Import
    // ===============================================

    /**
     * ファイルバリデーション(必須): ファイル未選択時にエラーになるテスト
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-import-validation
     */
    public function importFailsWithMissingFile(): void
    {
        // Arrange: ユーザー、ページ、投稿を準備 (権限のあるユーザーが必要)
        $page = Page::factory()->create();
        $post = LearningtasksPosts::factory()->create();
        $user = User::factory()->create();
        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'base',
            'role_name' => 'role_article_admin', // 管理者
        ]);

        $frame_id = 1; // 仮
        $url = "/redirect/plugin/learningtasks/importCsv/{$page->id}/{$frame_id}/{$post->id}"; // ★ 実際のURLに合わせる
        // ★ csv_file を含めないリクエストデータ
        $post_data = [
            'import_type' => LearningtaskImportType::report,
            'redirect_path' => "/plugin/learningtasks/show/{$page->id}/{$frame_id}/{$post->id}", // リダイレクト先指定(任意)
        ];

        // Act: 認証済みユーザーとして POST リクエストを実行
        $response = $this->actingAs($user)->post($url, $post_data);

        // Assert: バリデーションエラーでリダイレクトバックされることを確認
        $response->assertStatus(302); // リダイレクト
        $response->assertSessionHasErrors(['csv_file' => 'CSVファイルは必須です。']);
    }

    /**
     * ファイルバリデーション(MIME): 不正なファイルタイプ時にエラーになるテスト
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-import-validation
     */
    public function importFailsWithInvalidMimeType(): void
    {
        // Arrange: ユーザー、ページ、投稿を準備
        $page = Page::factory()->create();
        $post = LearningtasksPosts::factory()->create();
        $user = User::factory()->create();
        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'base',
            'role_name' => 'role_article_admin', // 管理者
        ]);

        $frame_id = 1;
        $url = "/redirect/plugin/learningtasks/importCsv/{$page->id}/{$frame_id}/{$post->id}";
        // ★ 不正なMIMEタイプ(例: pdf)の偽ファイルを作成
        $fake_file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'); // 100KBのPDFファイル
        $post_data = [
            'csv_file' => $fake_file, // 不正なファイルを添付
            'import_type' => LearningtaskImportType::report,
            'redirect_path' => "/plugin/learningtasks/show/{$page->id}/{$frame_id}/{$post->id}",
         ];

        // Act: 認証済みユーザーとして POST
        $response = $this->actingAs($user)->post($url, $post_data);

        // Assert: バリデーションエラーでリダイレクトバックされること
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['csv_file' => 'CSVファイルにはcsv, txt形式のファイルを指定してください。']);
    }

    /**
     * インポートタイプバリデーション: 空でエラー
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-import-validation
     */
    public function importFailsWithoutImportType(): void
    {
        // Arrange: ユーザー、ページ、投稿を準備
        $page = Page::factory()->create();
        $post = LearningtasksPosts::factory()->create();
        $user = User::factory()->create();
        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'base',
            'role_name' => 'role_article_admin', // 管理者
        ]);

        $frame_id = 1;
        $url = "/redirect/plugin/learningtasks/importCsv/{$page->id}/{$frame_id}/{$post->id}";
        $fake_file = UploadedFile::fake()->create('document.csv', 100, 'text/csv');
        $post_data = [
            'csv_file' => $fake_file,
            'redirect_path' => "/plugin/learningtasks/show/{$page->id}/{$frame_id}/{$post->id}",
         ];

        // Act: 認証済みユーザーとして POST
        $response = $this->actingAs($user)->post($url, $post_data);

        // Assert: バリデーションエラーでリダイレクトバックされること
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['import_type' => 'インポート形式は必須です。']);
    }

    /**
     * インポートタイプバリデーション: 形式違いでエラー
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-import-validation
     */
    public function importFailsWithInvalidImportType(): void
    {
        // Arrange: ユーザー、ページ、投稿を準備
        $page = Page::factory()->create();
        $post = LearningtasksPosts::factory()->create();
        $user = User::factory()->create();
        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'base',
            'role_name' => 'role_article_admin', // 管理者
        ]);

        $frame_id = 1;
        $url = "/redirect/plugin/learningtasks/importCsv/{$page->id}/{$frame_id}/{$post->id}";
        $fake_file = UploadedFile::fake()->create('document.csv', 100, 'text/csv');
        $post_data = [
            'csv_file' => $fake_file,
            'import_type' => 'new type',
            'redirect_path' => "/plugin/learningtasks/show/{$page->id}/{$frame_id}/{$post->id}",
         ];

        // Act: 認証済みユーザーとして POST
        $response = $this->actingAs($user)->post($url, $post_data);

        // Assert: バリデーションエラーでリダイレクトバックされること
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['import_type' => 'インポート形式には「report」のうちいずれかの値を指定してください。']);
    }

    // ===============================================
    // Feature Tests - Authorization Cases
    // ===============================================

    /**
     * 権限なし(未ログイン): CSVインポート(POST)が403権限エラーになるテスト
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-auth
     */
    public function guestCannotImportCsv(): void
    {
        // Arrange
        $page = Page::factory()->create();
        $post = LearningtasksPosts::factory()->create();
        $frame_id = 1;
        $url = "/redirect/plugin/learningtasks/importCsv/{$page->id}/{$frame_id}/{$post->id}";
        // POSTデータは最小限で良い（どうせ認証で弾かれるはず）
        $fake_file = UploadedFile::fake()->create('dummy.csv', 1);
        $post_data = [
            'csv_file' => $fake_file,
            'import_type' => LearningtaskImportType::report,
            'redirect_path' => "/plugin/learningtasks/show/{$page->id}/{$frame_id}/{$post->id}"
        ];

        // Act: ログインせずに POST
        $response = $this->post($url, $post_data);

        // Assert: 403レスポンス
        $response->assertStatus(200); // canメソッドチェック:Connect-CMSの仕様で200レスポンスとなる
        $response->assertSeeText('403 Forbidden');
    }

    /**
     * 権限なし(ログイン済 & 管理者でも担当教員でもない一般ユーザー): canImport()でCSVインポート(POST)が拒否されるテスト
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-auth
     */
    public function unauthorizedUserCannotImportCsv(): void
    {
        // Arrange: ページ、投稿、権限のないユーザー、偽ファイル
        $page = Page::factory()->create();
        $post = LearningtasksPosts::factory()->create();
        $unauthorized_user = User::factory()->create();
        UsersRoles::factory()->create([
            'users_id' => $unauthorized_user->id,
            'target' => 'base',
            'role_name' => 'role_guest', // ゲスト
        ]);
        // 課題投稿に関連する設定を作成
        // (レポート評価機能を有効にするための設定)
        LearningtasksUseSettings::factory()->create([
            'post_id' => $post->id,
            'use_function' => LearningtaskUseFunction::use_report_evaluate,
            'value' => 'on',
        ]);
        // フレーム、ページ、ファイル
        $frame_id = 1;
        $url = "/redirect/plugin/learningtasks/importCsv/{$page->id}/{$frame_id}/{$post->id}";
        $fake_file = UploadedFile::fake()->create('dummy.csv', 1);
        $post_data = [
            'csv_file' => $fake_file,
            'import_type' => LearningtaskImportType::report,
            'redirect_path' => "/plugin/learningtasks/show/{$page->id}/{$frame_id}/{$post->id}"
        ];

        // Act: 権限のないユーザーとして POST
        $response = $this->actingAs($unauthorized_user)->post($url, $post_data);

        // Assert: アクセス拒否 (Controller の canImport チェック -> abort(403)を想定)
        $response->assertStatus(403);
    }

    /**
     * ヘッダーエラー: 不正なヘッダーのCSVでエラーメッセージ付きリダイレクトになるテスト
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-import
     */
    public function importShowsErrorOnInvalidHeader(): void
    {
        // Arrange: データ準備
        // 1. ページ、課題投稿、実行ユーザー
        $page = Page::factory()->create();
        $learningtask = Learningtasks::factory()->create();
        $post = LearningtasksPosts::factory()->create(
            ['learningtasks_id' => $learningtask->id, 'student_join_flag' => 3, 'teacher_join_flag' => 3]
        );
        // この課題で評価インポートを有効にする設定を作成
        LearningtasksUseSettings::factory()->create([
            'post_id' => $post->id,
            'learningtasks_id' => $learningtask->id,
            'use_function' => LearningtaskUseFunction::use_report_evaluate,
            'value' => 'on',
        ]);
        // ★ ユーザーにインポート権限を付与 (例: 教員として登録)
        $user = User::factory()->create();
        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'base',
            'role_name' => 'role_guest', // ゲスト
        ]);
        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'original_role',
            'role_name' => 'teacher', // 教員
        ]);
        LearningtasksUsers::factory()->create(['post_id' => $post->id, 'user_id' => $user->id, 'role_name' => RoleName::teacher]);

        // 2. CSVファイル準備 (★ 不正なヘッダー)
        //    LearningtaskReportColumnDefinition が期待するのは ['ログインID', '評価', '評価コメント'] など
        $invalid_headers = ['ユーザーID', '評定', '感想']; // 期待と異なるヘッダー
        $rows = [
            ['student99', 'A', 'Good'], // データ行の内容はこのテストではあまり重要ではない
        ];
        $csv_content = $this->createCsvContent($invalid_headers, $rows);
        $fake_file = UploadedFile::fake()->createWithContent('invalid_header.csv', $csv_content);

        // 3. URL と POST データ
        $frame_id = 1; // 仮
        $url = "/redirect/plugin/learningtasks/importCsv/{$page->id}/{$frame_id}/{$post->id}";
        $expected_redirect_url = "/plugin/learningtasks/show/{$page->id}/{$frame_id}/{$post->id}";
        $post_data = [
            'csv_file' => $fake_file,
            'import_type' => LearningtaskImportType::report,
            'redirect_path' => $expected_redirect_url,
        ];

        // Act: 認証済みユーザーとしてPOST
        $response = $this->actingAs($user)->post($url, $post_data);

        // Assert: レスポンスの検証
        // 1. リダイレクトされること
        $response->assertStatus(302);
        $response->assertRedirect($expected_redirect_url);

        // 2a. 要約メッセージ ('flash_message') の確認
        $response->assertSessionHas('flash_message'); // キーの存在
        // 内容は「エラーがあったこと」を示すものか？
        $this->assertStringContainsString('エラー 1件', session('flash_message'));
        $this->assertStringContainsString('エラー内容を確認してください', session('flash_message'));
        // 具体的なエラー「ヘッダーが不正」はここには含まれないはず

        // 2b. 詳細エラー ('csv_import_errors') の確認
        $response->assertSessionHas('csv_import_errors');
        $error_details = session('csv_import_errors');
        $this->assertIsArray($error_details);
        $this->assertCount(1, $error_details, 'エラー詳細が1件であること');
        // 最初の (唯一の) エラー詳細の内容を確認
        $first_error = $error_details[0];
        $this->assertEquals(1, $first_error['line'], 'エラー詳細の行番号が1であること');
        $this->assertEquals('header_error', $first_error['type'], 'エラー詳細のタイプが header_error であること');
        $this->assertStringContainsString('ヘッダーが不正', $first_error['message'], 'エラー詳細のメッセージに原因が含まれること');

        // 3. DBに変更がないこと
        $this->assertDatabaseCount('learningtasks_users_statuses', 0);
    }

    /**
     * スキップ発生: スキップされる行がある場合に情報メッセージが表示され、成功行はコミットされるテスト
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-import
     */
    public function importShowsInfoMessageOnSkips(): void
    {
        // Arrange
        // 1. 基本データと権限設定
        $page = Page::factory()->create();
        $learningtask = Learningtasks::factory()->create();
        $post = LearningtasksPosts::factory()->create([
            'learningtasks_id' => $learningtask->id,
            'student_join_flag' => 3,
            'teacher_join_flag' => 3
        ]);
        LearningtasksUseSettings::factory()->create([
            'post_id' => $post->id,
            'learningtasks_id' => $learningtask->id,
            'use_function' => LearningtaskUseFunction::use_report_evaluate,
            'value' => 'on'
        ]); // 評価を有効に
        $user = User::factory()->create(); // インポート実行者
        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'base',
            'role_name' => 'role_article_admin', // 管理者
        ]);

        // 2. 学生と提出・評価データ (★一部評価済みデータを作成)
        $student1 = User::factory()->create(['userid' => 'student001']); // スキップ対象 (評価済み)
        $student2 = User::factory()->create(['userid' => 'student002']); // 成功対象
        // 受講生として登録
        LearningtasksUsers::factory()->create(['post_id' => $post->id, 'user_id' => $student1->id, 'role_name' => RoleName::student]);
        LearningtasksUsers::factory()->create(['post_id' => $post->id, 'user_id' => $student2->id, 'role_name' => RoleName::student]);
        // 提出記録
        $submission1 = LearningtasksUsersStatuses::factory()->create(['post_id' => $post->id, 'user_id' => $student1->id, 'task_status' => 1]);
        $submission2 = LearningtasksUsersStatuses::factory()->create(['post_id' => $post->id, 'user_id' => $student2->id, 'task_status' => 1]);
        // ★ student1 は既に評価済みレコードが存在する (IDは提出より後になるように)
        $existing_eval1 = LearningtasksUsersStatuses::factory()->create([
            'post_id' => $post->id, 'user_id' => $student1->id, 'task_status' => 2, 'grade' => 'C'
        ]);

        // 3. CSV ファイル (student1 は評価済みなのでスキップ、student2 は成功するはず)
        $headers = ['ログインID', 'ユーザ名', '提出日時', '提出回数', '評価'];
        $rows = [
            [$student1->userid, $student1->name, $submission1->created_at, '1', 'A'], // この行は評価済みのためスキップされる
            [$student2->userid, $student2->name, $submission2->created_at, '1', 'B'], // この行は成功するはず
        ];
        $csv_content = $this->createCsvContent($headers, $rows);
        $fake_file = UploadedFile::fake()->createWithContent('import_with_skips.csv', $csv_content);

        // 4. URL と POST データ
        $frame_id = 1;
        $url = "/redirect/plugin/learningtasks/importCsv/{$page->id}/{$frame_id}/{$post->id}";
        $expected_redirect_url = "/plugin/learningtasks/show/{$page->id}/{$frame_id}/{$post->id}";
        $post_data = [
            'csv_file' => $fake_file,
            'import_type' => LearningtaskImportType::report,
            'redirect_path' => $expected_redirect_url
        ];

        // Act
        $response = $this->actingAs($user)->post($url, $post_data);

        // Assert
        // 1. リダイレクト確認
        $response->assertStatus(302);
        $response->assertRedirect($expected_redirect_url);

        // 2. フラッシュメッセージ ('flash_message') の確認 (★情報メッセージ)
        $response->assertSessionHas('flash_message');
        // メッセージに成功件数とスキップ件数が含まれることを確認
        $this->assertStringContainsString('成功 1件', session('flash_message'));
        $this->assertStringContainsString('スキップ 1件', session('flash_message'));
        // メッセージにスキップがあったことを示す文言が含まれるか確認 (formatImportResultFeedback の実装による)
        $this->assertStringContainsString('一部スキップされた行があります', session('flash_message'));

        // 3. エラー関連セッションがないこと
        $response->assertSessionHasNoErrors(); // Laravel 標準バリデーションエラーなし
        $this->assertNull(session('csv_import_errors'), '詳細エラーがフラッシュされていないこと');
        // スキップ詳細がフラッシュされているはず
        $response->assertSessionHas('csv_import_skipped_details');
        // スキップ詳細の内容を確認
        $skipped_details = session('csv_import_skipped_details');
        $this->assertIsArray($skipped_details);
        $this->assertCount(1, $skipped_details); // スキップは1件
        $first_skip = $skipped_details[0];
        $this->assertEquals(2, $first_skip['line'], 'スキップ詳細の行番号が2であること');
        $this->assertEquals('already_evaluated', $first_skip['type'], 'スキップ詳細のタイプが already_evaluated であること');
        $this->assertStringContainsString('提出は既に評価済み', $first_skip['message'], 'スキップ詳細のメッセージに原因が含まれること');

        // 4. データベースの状態確認 (★コミットされているはず)
        //    - student2 の評価 (grade=B) は作成されている
        $this->assertDatabaseHas('learningtasks_users_statuses', [
            'post_id' => $post->id, 'user_id' => $student2->id, 'task_status' => 2, 'grade' => 'B', 'created_id' => $user->id
        ]);
        //    - student1 の評価は元のまま (grade=C)
        $this->assertDatabaseHas('learningtasks_users_statuses', [
            'post_id' => $post->id, 'user_id' => $student1->id, 'task_status' => 2, 'grade' => 'C'
        ]);
        //    - student1 の評価が意図せず A で追加/更新されていないことを確認
        $this->assertDatabaseMissing('learningtasks_users_statuses', [
             'post_id' => $post->id, 'user_id' => $student1->id, 'task_status' => 2, 'grade' => 'A'
        ]);
         // 合計件数: 提出2 + 既存評価1 + 新規評価1 = 4件
         $this->assertDatabaseCount('learningtasks_users_statuses', 4);
    }

    /**
     * エラー発生とロールバック: 処理中エラーで行が失敗し、全体がロールバックされるテスト
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-import
     */
    public function importRollsBackAndShowsErrorOnProcessingFailure(): void
    {
        // Arrange
        // 1. 基本データ、権限設定、課題設定
        $page = Page::factory()->create();
        $learningtask = Learningtasks::factory()->create();
        $post = LearningtasksPosts::factory()->create([
            'learningtasks_id' => $learningtask->id,
            'student_join_flag' => 3,
            'teacher_join_flag' => 3
        ]);
        LearningtasksUseSettings::factory()->create([
            'post_id' => $post->id,
            'learningtasks_id' => $learningtask->id,
            'use_function' => LearningtaskUseFunction::use_report_evaluate,
            'value' => 'on'
        ]); // 評価を有効に
        $user = User::factory()->create(); // インポート実行者
        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'base',
            'role_name' => 'role_article_admin', // 管理者
        ]);

        // 2. 学生データと提出状況 (★ student2 は提出記録なし)
        $student1 = User::factory()->create(['userid' => 'student1']); // この行の処理は Processor 内では成功するはず
        $student2 = User::factory()->create(['userid' => 'student2']); // この行の処理で Exception が発生するはず
        // 受講生登録
        LearningtasksUsers::factory()->create(['post_id' => $post->id, 'user_id' => $student1->id, 'role_name' => RoleName::student]);
        LearningtasksUsers::factory()->create(['post_id' => $post->id, 'user_id' => $student2->id, 'role_name' => RoleName::student]);
        // 提出記録 (student1 のみ作成)
        $submission1 = LearningtasksUsersStatuses::factory()->create(['post_id' => $post->id, 'user_id' => $student1->id, 'task_status' => 1]);
        // student2 の提出記録は作成しない

        // 3. CSV ファイル
        $headers = ['ログインID', 'ユーザ名', '提出日時', '提出回数', '評価'];
        $rows = [
            [$student1->userid, $student1->name, $submission1->created_at, '1', 'A'], // この行は成功するはず
            [$student2->userid, $student2->name, '', '1', 'B'], // 提出なし Exception が発生するはず
        ];
        $csv_content = $this->createCsvContent($headers, $rows);
        $fake_file = UploadedFile::fake()->createWithContent('import_proc_error.csv', $csv_content);

        // 4. URL と POST データ
        $frame_id = 1;
        $url = "/redirect/plugin/learningtasks/importCsv/{$page->id}/{$frame_id}/{$post->id}";
        $expected_redirect_url = "/plugin/learningtasks/showReport/{$page->id}/{$frame_id}/{$post->id}";
        $post_data = [
            'csv_file' => $fake_file,
            'import_type' => LearningtaskImportType::report,
            'redirect_path' => $expected_redirect_url
        ];

        // Act: インポート実行
        $response = $this->actingAs($user)->post($url, $post_data);

        // Assert
        // 1. リダイレクト確認
        $response->assertStatus(302);
        $response->assertRedirect($expected_redirect_url);

        // 2. 要約フラッシュメッセージ ('flash_message') の確認 (エラー表示)
        $response->assertSessionHas('flash_message');
        // ★ 成功 1件、エラー 1件 となっているはず
        $this->assertStringContainsString('成功 1件', session('flash_message')); // ロールバックされた
        $this->assertStringNotContainsString('スキップ', session('flash_message'));
        $this->assertStringContainsString('エラー 1件', session('flash_message'));
        $this->assertStringContainsString('エラー内容を確認してください', session('flash_message'));

        // 3. ★★★ エラー詳細 ('csv_import_errors') の個別アサートに変更 ★★★
        $response->assertSessionHas('csv_import_errors');
        $error_details = session('csv_import_errors');
        $this->assertIsArray($error_details);
        $this->assertCount(2, $error_details, 'エラー詳細が2件であること');

        // 3a. 処理エラー詳細 (Line 3) の内容を個別確認
        //     タイプ 'submission_not_found' で記録される想定
        $processing_error = collect($error_details)->firstWhere('line', 3);
        $this->assertNotNull($processing_error, 'Line 3 の処理エラー詳細が見つかりません');
        $this->assertEquals($student2->userid, $processing_error['userid'], '処理エラー詳細: userid');
        $this->assertEquals('submission_not_found', $processing_error['type'], '処理エラーのタイプ');
        $this->assertStringContainsString("評価対象の提出記録がユーザー ({$student2->userid}) に見つかりません。", $processing_error['message'], '処理エラー詳細: message');

        // 3b. ロールバックエラー詳細 (Line 0) の内容を個別確認
        $rollback_error = collect($error_details)->firstWhere('line', 0);
        $this->assertNotNull($rollback_error, 'Line 0 のロールバックエラー詳細が見つかりません');
        $this->assertEquals('fatal_error_rollback', $rollback_error['type'], 'ロールバックエラー詳細: type');
        $this->assertStringContainsString('処理が中断され', $rollback_error['message'], 'ロールバックエラー詳細: message prefix');
        $this->assertEquals('N/A', $rollback_error['userid'], 'ロールバックエラー詳細: userid'); // 行全体のエラーなので N/A

        // 4. スキップ詳細がないこと
        $this->assertNull(session('csv_import_skipped_details'));

        // 5. データベースの状態確認 (ロールバック)
        // 最初に処理されたはずの student1 の評価レコードも存在しないことを確認
        $this->assertDatabaseMissing('learningtasks_users_statuses', [
            'post_id' => $post->id, 'user_id' => $student1->id, 'task_status' => 2
        ]);
        // エラーになった student2 の評価レコードも存在しないことを確認
        $this->assertDatabaseMissing('learningtasks_users_statuses', [
            'post_id' => $post->id, 'user_id' => $student2->id, 'task_status' => 2
        ]);
        // 存在するレコードは student1 の提出記録のみのはず
        $this->assertDatabaseCount('learningtasks_users_statuses', 1);
    }

    /**
     * 設定無効: 課題設定で評価インポートが無効な場合に処理が中断されるテスト
     * @test
     * @group learningtasks
     * @group learningtasks-feature
     * @group learningtasks-import
     */
    public function importIsRejectedWhenSettingDisabled(): void
    {
        // Arrange:
        // 1. ページ、投稿、ユーザーを準備
        $page = Page::factory()->create();
        $learningtask = Learningtasks::factory()->create();
        $post = LearningtasksPosts::factory()->create(['learningtasks_id' => $learningtask->id]);
        $user = User::factory()->create();
        UsersRoles::factory()->create([
            'users_id' => $user->id,
            'target' => 'base',
            'role_name' => 'role_article_admin', // 管理者
        ]);

        // 2. 課題設定で評価インポートを「無効」にする
        //    (LearningtasksUseSettings レコードを作成しないか、'off' で作成する)
        //    ここではレコードを作成しないことで無効とする例
        LearningtasksUseSettings::factory()->create([
            'post_id' => $post->id,
            'use_function' => LearningtaskUseFunction::use_report_evaluate,
            'value' => 'off', // 明示的に off
        ]);

        // 3. ダミーの CSV ファイルとリクエストデータ
        $fake_file = UploadedFile::fake()->create('dummy.csv', 1);
        $frame_id = 1;
        $url = "/redirect/plugin/learningtasks/importCsv/{$page->id}/{$frame_id}/{$post->id}"; // ★ importCsv ルート
        $expected_redirect_url = "/plugin/learningtasks/show/{$page->id}/{$frame_id}/{$post->id}"; // 仮のリダイレクト先
        $post_data = [
            'csv_file' => $fake_file,
            'import_type' => LearningtaskImportType::report, // report を指定
            'redirect_path' => $expected_redirect_url,
        ];

        // Act: 認証済みユーザーとして POST
        $response = $this->actingAs($user)->post($url, $post_data);

        // Assert:
        // 1. リダイレクトバックされることを期待 (withErrors で返るため 302)
        //    (FeatureDisabledException を catch して back()->with('error', ...) で返す実装)
        $response->assertStatus(302);
        // redirect()->back() なのでリダイレクト先は厳密には特定できないが、リダイレクトされること自体を確認
        $response->assertRedirect();

        // 2. ★ セッションに 'error' キーで特定のエラーメッセージが含まれることを確認
        //    (Controller が FeatureDisabledException のメッセージを使う場合)
        $response->assertSessionHas('error', 'この課題ではレポート評価機能が有効になっていません。');

        // 3. DB に変更がないことを確認 (インポート処理は実行されないため)
        $this->assertDatabaseCount('learningtasks_users_statuses', 0); // 何も登録されない
    }
}
