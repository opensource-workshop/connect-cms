<?php

namespace Tests\Unit\Plugins\User\Learningtasks\Csv;

use App\Models\Common\Page;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Plugins\User\Learningtasks\Contracts\ColumnDefinitionInterface;
use App\Plugins\User\Learningtasks\Contracts\RowProcessorExceptionHandlerInterface;
use App\Plugins\User\Learningtasks\Contracts\RowProcessorInterface;
use App\Plugins\User\Learningtasks\Csv\LearningtasksCsvImporter;
use App\Plugins\User\Learningtasks\Exceptions\AlreadyEvaluatedException;
use App\Plugins\User\Learningtasks\Exceptions\InvalidStudentException;
use App\Plugins\User\Learningtasks\Exceptions\SubmissionNotFoundException;
use App\Plugins\User\Learningtasks\Repositories\LearningtaskUserRepository;
use App\User;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * LearningtasksCsvImporter のテストクラス (インテグレーション寄り)
 * @coversDefaultClass \App\Plugins\User\Learningtasks\Csv\LearningtasksCsvImporter
 */
class LearningtasksCsvImporterTest extends TestCase
{
    use RefreshDatabase;

    /** @var ColumnDefinitionInterface|MockInterface */
    private $mock_column_definition;

    /** @var RowProcessorInterface|MockInterface */
    private $mock_row_processor;

    /** @var LearningtaskUserRepository|MockInterface */
    private $mock_user_repository;

    /** @var RowProcessorExceptionHandlerInterface|MockInterface */
    private $mock_exception_handler;

    /** @var LearningtasksCsvImporter */
    private $importer;

    // --- テスト対象の基本データ ---
    private $page;
    private $post;
    private $importer_user;

    /**
     * 各テスト前に共通の準備
     */
    protected function setUp(): void
    {
        parent::setUp();
        // モックを作成
        $this->mock_column_definition = Mockery::mock(ColumnDefinitionInterface::class);
        $this->mock_row_processor = Mockery::mock(RowProcessorInterface::class);
        $this->mock_user_repository = Mockery::mock(LearningtaskUserRepository::class);
        $this->mock_exception_handler =  Mockery::mock(RowProcessorExceptionHandlerInterface::class);

        // 基本的なテストデータを作成
        $this->page = Page::factory()->create();
        $this->post = LearningtasksPosts::factory()->create();
        $this->importer_user = User::factory()->create(); // インポート実行者

        // テスト対象クラスを生成 (モックを注入)
        $this->importer = new LearningtasksCsvImporter(
            $this->post,
            $this->page,
            $this->mock_column_definition,
            $this->mock_row_processor,
            $this->mock_user_repository,
            $this->mock_exception_handler
        );

        // Fake storage (任意だがファイルを実際に置かない場合に便利)
        Storage::fake('local');
    }

    /**
     * CSVファイルの内容を生成するヘルパー
     */
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
     * 正常系: 有効なCSVファイルでインポートが成功することをテスト
     * @test
     * @covers ::import
     * @group learningtasks
     * @group learningtasks-importer
     */
    public function importProcessesValidCsvSuccessfully(): void
    {
        // Arrange: 準備
        // 1. テスト用ユーザーデータ
        $student1 = User::factory()->create(['userid' => 'student1']);
        $student2 = User::factory()->create(['userid' => 'student2']);

        // 2. ColumnDefinitionInterface のモック設定
        $expected_headers = ['ログインID', '評価', '評価コメント'];
        $column_map = ['ログインID' => 'userid', '評価' => 'grade', '評価コメント' => 'comment'];
        // バリデーションルールはテストシナリオに応じて簡略化または具体的に設定
        $validation_rules = ['userid' => ['required'], 'grade' => [], 'comment' => []];
        $this->mock_column_definition->shouldReceive('getHeaders')->once()->andReturn($expected_headers);
        $this->mock_column_definition->shouldReceive('getColumnMap')->atLeast()->once()->andReturn($column_map); // validateRow で呼ばれる
        $this->mock_column_definition->shouldReceive('getValidationRulesBase')->atLeast()->once()->andReturn($validation_rules); // validateRow で呼ばれる
        $this->mock_column_definition->shouldReceive('getValidationMessages')->andReturn([]); // メッセージも取得される可能性がある

        // 3. RowProcessorInterface のモック設定
        //    process メソッドが期待する引数で、期待する回数呼ばれることを設定
        $this->mock_row_processor->shouldReceive('process')
            ->once()
            ->withArgs(fn($data, $post, $page, $importer) =>
                isset($data['userid'], $data['grade']) && // キーの存在確認を追加(より安全)
                $data['userid'] === $student1->userid && $data['grade'] === 'A'
            )
            ->andReturnNull(); // 成功時は void (null)

        $this->mock_row_processor->shouldReceive('process')
            ->once()
            ->withArgs(fn($data, $post, $page, $importer) =>
                 isset($data['userid'], $data['grade']) &&
                 $data['userid'] === $student2->userid && $data['grade'] === 'B'
             )
            ->andReturnNull();

        // 4. CSVファイルの内容と偽のアップロードファイルを作成
        $csv_rows = [
            [$student1->userid, 'A', 'Comment 1'],
            [$student2->userid, 'B', 'Comment 2'],
        ];
        $csv_content = $this->createCsvContent($expected_headers, $csv_rows);
        $fake_file = UploadedFile::fake()->createWithContent('import.csv', $csv_content);

        // Act: インポート処理を実行
        $results = $this->importer->import($fake_file, $this->importer_user);

        // Assert: 結果の検証
        $this->assertEquals(2, $results['success'], '成功件数が2であること');
        $this->assertEquals(0, $results['errors'], 'エラー件数が0であること');
        $this->assertEquals(0, $results['skipped'], 'スキップ件数が0であること');
        $this->assertCount(0, $results['error_details'], 'エラー詳細が空であること');
        // RowProcessor が期待通り呼ばれたかは Mockery が内部で検証している
    }

    /**
     * ヘッダーエラー: CSVヘッダーが不正な場合にエラー終了し、正しいエラー詳細が記録されるテスト
     * @test
     * @covers ::import
     * @covers ::processHeader
     * @covers ::validateHeader
     * @group learningtasks
     * @group learningtasks-importer
     */
    public function importFailsOnInvalidHeader(): void
    {
        // Arrange
        $expected_headers = ['ログインID', '評価'];
        $invalid_headers = ['ユーザーID', '評定'];
        $this->mock_column_definition->shouldReceive('getHeaders')->once()->andReturn($expected_headers);
        $this->mock_row_processor->shouldNotReceive('process');
        $csv_content = $this->createCsvContent($invalid_headers, [['userA', 'A']]);
        $fake_file = UploadedFile::fake()->createWithContent('invalid_header.csv', $csv_content);

        // Act
        $results = $this->importer->import($fake_file, $this->importer_user);

        // Assert
        $this->assertEquals(0, $results['success']);
        $this->assertEquals(0, $results['skipped']);
        // ★ エラー件数は、外側の catch で捕捉されるため 1 になるはず
        //    (ただし、addErrorDetail の重複追加防止ロジックが完璧なら1)
        $this->assertEquals(1, $results['errors']);
        $this->assertCount(1, $results['error_details']); // ★ ヘッダーエラー1件のみのはず

        $error_detail = $results['error_details'][0];
        $this->assertEquals(1, $error_detail['line']); // ヘッダーエラーは1行目

        // Importer の catch(CsvInvalidHeaderException $e) ブロックで
        // 'header_error' タイプで addErrorDetail が呼ばれることを期待
        $this->assertEquals('header_error', $error_detail['type'], 'エラータイプが header_error であること');

        $this->assertStringContainsString('ヘッダーが不正', $error_detail['message']); // メッセージ確認

        // ★ DBに変更がないことを確認
        $this->assertDatabaseCount('learningtasks_users_statuses', 0);
    }

    // ===============================================
    // Exception Handling Tests
    // ===============================================
    /**
     * Skip Outcome: ハンドラが 'skip' を返した場合、スキップとして記録されコミットされるテスト
     * @test
     * @covers ::import
     * @covers ::handleRowProcessingException
     * @group learningtasks
     * @group learningtasks-importer
     */
    public function importCommitsAndLogsSkipWhenHandlerReturnsSkip(): void
    {
        // Arrange
        $student1 = User::factory()->create(['userid' => 'student1']); // Success row
        $student2 = User::factory()->create(['userid' => 'student2']); // Row that causes skip
        $expected_headers = ['ログインID', '評価'];
        $exception_to_throw = new AlreadyEvaluatedException("Already done"); // 代表的なスキップ例外
        $skip_type_string = 'test_skip_type_from_handler'; // ハンドラが返すタイプ文字列
        $skip_log_level = 'info';

        // ColumnDefinition Mock (valid for both rows)...
        $this->mock_column_definition->shouldReceive('getHeaders')->andReturn($expected_headers);
        $this->mock_column_definition->shouldReceive('getColumnMap')->andReturn(['ログインID' => 'userid', '評価' => 'grade']);
        $this->mock_column_definition->shouldReceive('getValidationRulesBase')->andReturn(['userid'=>[],'grade'=>[]]);
        $this->mock_column_definition->shouldReceive('getValidationMessages')->andReturn([]);

        // RowProcessor Mock: 1回目成功、2回目例外
        $this->mock_row_processor->shouldReceive('process')->once()->ordered()->andReturnNull();
        $this->mock_row_processor->shouldReceive('process')->once()->ordered()->andThrow($exception_to_throw);

        // Exception Handler Mock: スキップ設定を返す
        $this->mock_exception_handler->shouldReceive('handle')
             ->once()
             ->with(Mockery::on(fn($e) => $e === $exception_to_throw))
             ->andReturn(['outcome' => 'skip', 'type' => $skip_type_string, 'log_level' => $skip_log_level]);

        // CSV File...
        $csv_rows = [ [$student1->userid, 'A'], [$student2->userid, 'B'] ];
        $csv_content = $this->createCsvContent($expected_headers, $csv_rows);
        $fake_file = UploadedFile::fake()->createWithContent('import_skip.csv', $csv_content);

        // Act
        $results = $this->importer->import($fake_file, $this->importer_user);

        // Assert
        $this->assertEquals(1, $results['success'], '成功は1件');
        $this->assertEquals(0, $results['errors'], 'エラーは0件');
        $this->assertEquals(1, $results['skipped'], 'スキップは1件');
        $this->assertCount(0, $results['error_details'], 'エラー詳細はない'); // エラーはない
        $this->assertCount(1, $results['skip_details'], 'スキップ詳細は1件'); // スキップ詳細がある
        $this->assertEquals($skip_type_string, $results['skip_details'][0]['type'], 'スキップ詳細のタイプがハンドラ指定通り');
        $this->assertEquals(3, $results['skip_details'][0]['line']); // 行番号
    }

    /**
     * ★ Error Outcome: ハンドラが 'error' を返した場合、エラーとして記録されロールバックされるテスト
     * @test
     * @covers ::import
     * @covers ::handleRowProcessingException
     * @group learningtasks
     * @group learningtasks-importer
     */
    public function importRollsBackAndLogsErrorWhenHandlerReturnsError(): void
    {
        // Arrange
        $student1 = User::factory()->create(['userid' => 'student1']); // Success attempt -> Rollback
        $student2 = User::factory()->create(['userid' => 'student2']); // Row that causes error
        $expected_headers = ['ログインID', '評価'];
        $exception_to_throw = new SubmissionNotFoundException("No submission"); // 代表的なエラー例外
        $error_type_string = 'test_error_type_from_handler'; // ハンドラが返すタイプ文字列
        $error_log_level = 'error';

        // ColumnDefinition Mock (valid for both rows)...
        $this->mock_column_definition->shouldReceive('getHeaders')->andReturn($expected_headers);
        $this->mock_column_definition->shouldReceive('getColumnMap')->andReturn(['ログインID' => 'userid', '評価' => 'grade']);
        $this->mock_column_definition->shouldReceive('getValidationRulesBase')->andReturn(['userid'=>[],'grade'=>[]]);
        $this->mock_column_definition->shouldReceive('getValidationMessages')->andReturn([]);

        // RowProcessor Mock: 1回目成功、2回目例外
        $this->mock_row_processor->shouldReceive('process')->once()->ordered()->andReturnNull();
        $this->mock_row_processor->shouldReceive('process')->once()->ordered()->andThrow($exception_to_throw);

        // Exception Handler Mock: エラー設定を返す
        $this->mock_exception_handler->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(fn($e) => $e === $exception_to_throw))
            ->andReturn(['outcome' => 'error', 'type' => $error_type_string, 'log_level' => $error_log_level]);

        // CSV File...
        $csv_rows = [ [$student1->userid, 'A'], [$student2->userid, 'B'] ];
        $csv_content = $this->createCsvContent($expected_headers, $csv_rows);
        $fake_file = UploadedFile::fake()->createWithContent('import_error.csv', $csv_content);

        // Act
        $results = $this->importer->import($fake_file, $this->importer_user);

        // Assert
        $this->assertEquals(1, $results['success'], '成功は1件');
        $this->assertEquals(1, $results['errors'], 'エラーは1件');
        $this->assertEquals(0, $results['skipped'], 'スキップは0件');
        $this->assertCount(2, $results['error_details'], 'エラー詳細は2件 (処理エラー + ロールバック)'); // ★ Error + Rollback details
        $this->assertCount(0, $results['skip_details'], 'スキップ詳細はない');

        // エラー詳細のタイプがハンドラ指定通りか確認
        $processing_error = collect($results['error_details'])->firstWhere('line', 3);
        $this->assertNotNull($processing_error);
        $this->assertEquals($error_type_string, $processing_error['type'], 'エラー詳細のタイプがハンドラ指定通り');
        // ロールバックエラー詳細の存在確認
        $rollback_error = collect($results['error_details'])->firstWhere('type', 'fatal_error_rollback');
        $this->assertNotNull($rollback_error);
    }

    /**
     * Unhandled Outcome: ハンドラが null を返した場合、予期せぬエラーとして記録されロールバックされるテスト
     * @test
     * @covers ::import
     * @covers ::handleRowProcessingException
     * @group learningtasks
     * @group learningtasks-importer
     */
    public function importRollsBackAndLogsErrorWhenHandlerReturnsNull(): void
    {
        // Arrange
        $student1 = User::factory()->create(['userid' => 'student1']);
        $student2 = User::factory()->create(['userid' => 'student2']);
        $expected_headers = ['ログインID', '評価'];
        $exception_to_throw = new Exception("Unhandled error"); // ハンドラが処理しない例外

        // ColumnDefinition Mock...
        $this->mock_column_definition->shouldReceive('getHeaders')->andReturn($expected_headers);
        // ... etc ...
        $this->mock_column_definition->shouldReceive('getColumnMap')->andReturn(['ログインID' => 'userid', '評価' => 'grade']);
        $this->mock_column_definition->shouldReceive('getValidationRulesBase')->andReturn(['userid'=>[],'grade'=>[]]);
        $this->mock_column_definition->shouldReceive('getValidationMessages')->andReturn([]);

        // RowProcessor Mock: 1回目成功、2回目例外
        $this->mock_row_processor->shouldReceive('process')->once()->ordered()->andReturnNull();
        $this->mock_row_processor->shouldReceive('process')->once()->ordered()->andThrow($exception_to_throw);

        // Exception Handler Mock: null を返す
        $this->mock_exception_handler->shouldReceive('handle')
            ->once()
            ->with(Mockery::on(fn($e) => $e === $exception_to_throw))
            ->andReturn(null); // ★ null を返す -> Importer が unexpected_error で処理

        // CSV File...
        $csv_rows = [ [$student1->userid, 'A'], [$student2->userid, 'B'] ];
        $csv_content = $this->createCsvContent($expected_headers, $csv_rows);
        $fake_file = UploadedFile::fake()->createWithContent('import_unhandled.csv', $csv_content);

        // Act
        $results = $this->importer->import($fake_file, $this->importer_user);

        // Assert
        $this->assertEquals(1, $results['success']);
        $this->assertEquals(1, $results['errors']);
        $this->assertEquals(0, $results['skipped']);
        $this->assertCount(2, $results['error_details']); // Unexpected Error + Rollback Error
        $this->assertCount(0, $results['skip_details']);

        // 予期せぬエラーの詳細を確認
        $processing_error = collect($results['error_details'])->firstWhere('line', 3);
        $this->assertNotNull($processing_error);
        $this->assertEquals('unexpected_error', $processing_error['type']); // ★ Importer の fallback type
        $this->assertEquals($exception_to_throw->getMessage(), $processing_error['message']);

        // ロールバックエラー詳細の存在確認 ...
        $rollback_error = collect($results['error_details'])->firstWhere('line', 0);
        $this->assertNotNull($rollback_error);
        $this->assertEquals('fatal_error_rollback', $rollback_error['type']);
    }

    /**
     * canImport が管理者ユーザーに対して true を返すことをテスト
     * @test
     * @covers ::canImport
     * @group learningtasks
     * @group learningtasks-importer
     * @group learningtasks-permission
     */
    public function canImportReturnsTrueForAdminUser(): void
    {
        // Arrange: ユーザーを作成
        $admin_user = User::factory()->create();
        $partial_mock_admin = Mockery::mock($admin_user)->makePartial();
        // can メソッドの振る舞いのみ上書き
        $partial_mock_admin->shouldReceive('can')
                           ->with('role_article_admin')
                           ->once()
                           ->andReturn(true); // 管理者権限ありとみなす

        // UserRepository::getTeachers は呼ばれないはず

        // Act: canImport を実行
        $result = $this->importer->canImport($partial_mock_admin);

        // Assert: 結果が true であることを確認
        $this->assertTrue($result, '管理者はインポートできるべき');
    }

    /**
     * canImport が担当教員ユーザーに対して true を返すことをテスト
     * @test
     * @covers ::canImport
     * @group learningtasks
     * @group learningtasks-importer
     * @group learningtasks-permission
     */
    public function canImportReturnsTrueForTeacherUser(): void
    {
        // Arrange: 教員ユーザーのモックを作成
        $teacher_user = User::factory()->create();

        // UserRepository がこの教員（実際のオブジェクト）を含むコレクションを返すように設定
        $this->mock_user_repository
            ->shouldReceive('getTeachers')
            ->with($this->post, $this->page)
            ->once()
            ->andReturn(new EloquentCollection([$teacher_user]));

        // can メソッドの振る舞いのみ上書き (管理者ではないとする)
        $partial_mock_teacher = Mockery::mock($teacher_user)->makePartial();
        $partial_mock_teacher->shouldReceive('can')
                             ->with('role_article_admin')
                             ->once()
                             ->andReturn(false);

        // Act: canImport を実行
        $result = $this->importer->canImport($partial_mock_teacher);

        // Assert: 結果が true であることを確認
        $this->assertTrue($result, '担当教員はインポートできるべき');
    }

    /**
     * canImport が管理者でも担当教員でもないユーザーに対して false を返すことをテスト
     * @test
     * @covers ::canImport
     * @group learningtasks
     * @group learningtasks-importer
     * @group learningtasks-permission
     */
    public function canImportReturnsFalseForOtherUser(): void
    {
        // Arrange その他のユーザーを作成
        $other_user = User::factory()->create();

        // UserRepository が空のコレクションを返すように設定
        $this->mock_user_repository
            ->shouldReceive('getTeachers')
            ->with($this->post, $this->page)
            ->once()
            ->andReturn(new EloquentCollection([])); // 空のコレクション

        // can メソッドの振る舞いのみ上書き (管理者ではないとする)
        $partial_mock_other = Mockery::mock($other_user)->makePartial();
        $partial_mock_other->shouldReceive('can')
                        ->with('role_article_admin')
                        ->once()
                        ->andReturn(false);

        // Act: canImport
        $result = $this->importer->canImport($partial_mock_other);

        // Assert
        $this->assertFalse($result, '管理者でも担当教員でもないユーザーはインポートできないべき');
    }
}
