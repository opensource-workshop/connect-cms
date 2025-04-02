<?php

namespace Tests\Unit\Plugins\User\Learningtasks;

use Tests\TestCase;
use App\Enums\LearningtaskUseFunction;
use App\Models\Common\Page;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Models\User\Learningtasks\LearningtasksUsersStatuses;
use App\Plugins\User\Learningtasks\LearningtasksReportCsvExporter;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

/**
 * LearningtasksReportCsvExporter のテストクラス
 */
class LearningtasksReportCsvExporterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * getHeaderColumns メソッドのテスト
     *
     * 設定が無効の場合に正しいヘッダーが返されることを確認します。
     */
    public function testGetHeaderColumnsWithSettingsDisabled()
    {
        // Mock dependencies
        $learningtask_post = LearningtasksPosts::factory()->create();
        $page = Page::factory()->create();

        // 設定がすべて無効の場合
        $exporter = Mockery::mock(LearningtasksReportCsvExporter::class, [$learningtask_post->id, $page->id])->makePartial();
        $exporter->shouldReceive('isSettingEnabled')->andReturn(false);
        $exporter = new LearningtasksReportCsvExporter($learningtask_post->id, $page->id);

        $this->assertEquals(['ログインID', 'ユーザ名', '提出日時', '提出回数'], $exporter->getHeaderColumns());
    }

    /**
     * getHeaderColumns メソッドのテスト
     *
     * 設定が有効の場合に正しいヘッダーが返されることを確認します。
     */
    public function testGetHeaderColumnsWithSettingsEnabled()
    {
        // Mock dependencies
        $learningtask_post = LearningtasksPosts::factory()->create();
        $page = Page::factory()->create();

        // 設定が有効の場合
        $exporter = Mockery::mock(LearningtasksReportCsvExporter::class, [$learningtask_post->id, $page->id])->makePartial();
        $exporter->shouldReceive('isSettingEnabled')->andReturn(true);
        $this->assertEquals(
            ['ログインID', 'ユーザ名', '提出日時', '提出回数', '本文', 'ファイルURL', '評価', '評価コメント'],
            $exporter->getHeaderColumns()
        );
    }

    /**
     * getHeaderColumns メソッドのテスト
     *
     * 部分的に設定が有効な場合に正しいヘッダーが返されることを確認します。
     */
    public function testGetHeaderColumnsWithPartialSettingsEnabled()
    {
        // Mock dependencies
        $learningtask_post = LearningtasksPosts::factory()->create();
        $page = Page::factory()->create();

        // 設定が部分的に有効な場合
        $exporter = Mockery::mock(LearningtasksReportCsvExporter::class, [$learningtask_post->id, $page->id])->makePartial();
        $exporter->shouldReceive('isSettingEnabled')
            ->with(LearningtaskUseFunction::use_report_comment)->andReturn(true);
        $exporter->shouldReceive('isSettingEnabled')
            ->with(LearningtaskUseFunction::use_report_file)->andReturn(false);
        $exporter->shouldReceive('isSettingEnabled')
            ->with(LearningtaskUseFunction::use_report_evaluate)->andReturn(true);
        $exporter->shouldReceive('isSettingEnabled')
            ->with(LearningtaskUseFunction::use_report_evaluate_comment)->andReturn(false);

        $this->assertEquals(
            ['ログインID', 'ユーザ名', '提出日時', '提出回数', '本文', '評価'],
            $exporter->getHeaderColumns()
        );
    }

    /**
     * getRows メソッドのテスト
     *
     * 学生、提出、評価データの組み合わせに応じて正しい行データが返されることを確認します。
     */
    public function testGetRows()
    {
        // Mock now()
        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        // Mock dependencies
        $learningtask_post = LearningtasksPosts::factory()->create();
        $page = Page::factory()->create();

        // Create test data
        $student1 = User::factory()->create(['userid' => 'student1', 'name' => 'Student One']);
        $student2 = User::factory()->create(['userid' => 'student2', 'name' => 'Student Two']);
        $student3 = User::factory()->create(['userid' => 'student3', 'name' => 'Student Three']);

        // student1の提出と評価
        $submit1_student1 = LearningtasksUsersStatuses::factory()->create([
            'post_id' => $learningtask_post->id,
            'user_id' => $student1->id,
            'task_status' => 1, // Submitted
            'created_at' => $now,
            'comment' => 'Test comment1',
            'upload_id' => 1,
        ]);
        $evaluation1_student1 = LearningtasksUsersStatuses::factory()->create([
            'post_id' => $learningtask_post->id,
            'user_id' => $student1->id,
            'task_status' => 2, // Evaluated
            'grade' => 'A',
            'comment' => 'Good work',
        ]);

        // student2の提出と評価
        // 再提出後、再提出の評価はない
        $submit1_student2 = LearningtasksUsersStatuses::factory()->create([
            'post_id' => $learningtask_post->id,
            'user_id' => $student2->id,
            'task_status' => 1, // Submitted
            'created_at' => $now->addDay(),
            'comment' => null,
            'upload_id' => 2,
        ]);
        $evaluation1_student2 = LearningtasksUsersStatuses::factory()->create([
            'post_id' => $learningtask_post->id,
            'user_id' => $student2->id,
            'task_status' => 2, // Evaluated
            'grade' => 'D',
            'comment' => 'Needs improvement',
        ]);
        $submit2_student2 = LearningtasksUsersStatuses::factory()->create([
            'post_id' => $learningtask_post->id,
            'user_id' => $student2->id,
            'task_status' => 1, // Submitted
            'created_at' => $now->addDays(2),
            'comment' => 'submit again',
            'upload_id' => 3,
        ]);

        // student3の提出と評価はなし

        // Mock fetchStudentUsers to return test students
        $exporter = Mockery::mock(LearningtasksReportCsvExporter::class, [$learningtask_post->id, $page->id])->makePartial();
        $exporter->shouldReceive('isSettingEnabled')->andReturn(true);
        $exporter->shouldReceive('fetchStudentUsers')->andReturn(new EloquentCollection([$student1, $student2, $student3]));

        // Test getRows
        $site_url = 'http://example.com';
        $rows = $exporter->getRows($site_url);

        $this->assertCount(3, $rows);

        // student1の行を検証
        // 提出と評価の組み合わせを出力する
        $this->assertEquals('student1', $rows[0]['ログインID']);
        $this->assertEquals('Student One', $rows[0]['ユーザ名']);
        $this->assertEquals($submit1_student1->created_at, $rows[0]['提出日時']);
        $this->assertEquals(1, $rows[0]['提出回数']);
        $this->assertEquals('Test comment1', $rows[0]['本文']);
        $this->assertEquals($site_url . '/file/1', $rows[0]['ファイルURL']);
        $this->assertEquals('A', $rows[0]['評価']);
        $this->assertEquals('Good work', $rows[0]['評価コメント']);

        // student2の行を検証
        // 再提出の評価はないので、評価に関する項目はnull
        $this->assertEquals('student2', $rows[1]['ログインID']);
        $this->assertEquals('Student Two', $rows[1]['ユーザ名']);
        $this->assertEquals($submit2_student2->created_at, $rows[1]['提出日時']);
        $this->assertEquals(2, $rows[1]['提出回数']);
        $this->assertEquals('submit again', $rows[1]['本文']);
        $this->assertEquals($site_url . '/file/3', $rows[1]['ファイルURL']);
        $this->assertEquals(null, $rows[1]['評価']);
        $this->assertEquals(null, $rows[1]['評価コメント']);

        // student3の行を検証
        // 提出や評価がないので、すべてnull
        $this->assertEquals('student3', $rows[2]['ログインID']);
        $this->assertEquals('Student Three', $rows[2]['ユーザ名']);
        $this->assertEquals(null, $rows[2]['提出日時']);
        $this->assertEquals(0, $rows[2]['提出回数']);
        $this->assertEquals(null, $rows[2]['本文']);
        $this->assertEquals(null, $rows[2]['ファイルURL']);
        $this->assertEquals(null, $rows[2]['評価']);
        $this->assertEquals(null, $rows[2]['評価コメント']);
    }

    /**
     * getRows メソッドのテスト
     *
     * 設定がすべて無効な場合に正しい行データが返されることを確認します。
     */
    public function testGetRowsWithSettingsDisabled()
    {
        // Mock now()
        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        // Mock dependencies
        $learningtask_post = LearningtasksPosts::factory()->create();
        $page = Page::factory()->create();

        // Create test data
        $student1 = User::factory()->create(['userid' => 'student1', 'name' => 'Student One']);
        $submit1_student1 = LearningtasksUsersStatuses::factory()->create([
            'post_id' => $learningtask_post->id,
            'user_id' => $student1->id,
            'task_status' => 1, // Submitted
            'created_at' => $now,
            'comment' => 'Test comment1',
            'upload_id' => 1,
        ]);

        // Mock fetchStudentUsers to return test students
        $exporter = Mockery::mock(LearningtasksReportCsvExporter::class, [$learningtask_post->id, $page->id])->makePartial();
        $exporter->shouldReceive('isSettingEnabled')->andReturn(false); // 設定を無効にする
        $exporter->shouldReceive('fetchStudentUsers')->andReturn(new EloquentCollection([$student1]));

        // Test getRows
        $site_url = 'http://example.com';
        $rows = $exporter->getRows($site_url);

        $this->assertCount(1, $rows);

        // Verify student's row
        $this->assertEquals('student1', $rows[0]['ログインID']);
        $this->assertEquals('Student One', $rows[0]['ユーザ名']);
        $this->assertEquals($submit1_student1->created_at, $rows[0]['提出日時']);
        $this->assertEquals(1, $rows[0]['提出回数']);
        $this->assertArrayNotHasKey('本文', $rows[0]); // 本文は設定が無効なので含まれない
        $this->assertArrayNotHasKey('ファイルURL', $rows[0]); // ファイルURLは設定が無効なので含まれない
        $this->assertArrayNotHasKey('評価', $rows[0]); // 評価は設定が無効なので含まれない
        $this->assertArrayNotHasKey('評価コメント', $rows[0]); // 評価コメントは設定が無効なので含まれない
    }

    /**
     * getRows メソッドのテスト
     *
     * 部分的に設定が有効な場合に正しい行データが返されることを確認します。
     */
    public function testGetRowsWithPartialSettingsEnabled()
    {
        // Mock now()
        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        // Mock dependencies
        $learningtask_post = LearningtasksPosts::factory()->create();
        $page = Page::factory()->create();

        // Create test data
        $student1 = User::factory()->create(['userid' => 'student1', 'name' => 'Student One']);
        $submit1_student1 = LearningtasksUsersStatuses::factory()->create([
            'post_id' => $learningtask_post->id,
            'user_id' => $student1->id,
            'task_status' => 1, // Submitted
            'created_at' => $now,
            'comment' => 'Test comment1',
            'upload_id' => 1,
        ]);

        // Mock fetchStudentUsers to return test students
        $exporter = Mockery::mock(LearningtasksReportCsvExporter::class, [$learningtask_post->id, $page->id])->makePartial();
        $exporter->shouldReceive('isSettingEnabled')
            ->with(LearningtaskUseFunction::use_report_comment)->andReturn(true); // 本文は有効
        $exporter->shouldReceive('isSettingEnabled')
            ->with(LearningtaskUseFunction::use_report_file)->andReturn(false); // ファイルURLは無効
        $exporter->shouldReceive('isSettingEnabled')
            ->with(LearningtaskUseFunction::use_report_evaluate)->andReturn(true); // 評価は有効
        $exporter->shouldReceive('isSettingEnabled')
            ->with(LearningtaskUseFunction::use_report_evaluate_comment)->andReturn(false); // 評価コメントは無効
        $exporter->shouldReceive('fetchStudentUsers')->andReturn(new EloquentCollection([$student1]));

        // Test getRows
        $site_url = 'http://example.com';
        $rows = $exporter->getRows($site_url);

        $this->assertCount(1, $rows);

        // Verify student's row
        $this->assertEquals('student1', $rows[0]['ログインID']);
        $this->assertEquals('Student One', $rows[0]['ユーザ名']);
        $this->assertEquals($submit1_student1->created_at, $rows[0]['提出日時']);
        $this->assertEquals(1, $rows[0]['提出回数']);
        $this->assertEquals('Test comment1', $rows[0]['本文']); // 本文は有効なので含まれる
        $this->assertArrayNotHasKey('ファイルURL', $rows[0]); // ファイルURLは無効なので含まれない
        $this->assertArrayHasKey('評価', $rows[0]); // 評価は有効なので含まれる
        $this->assertArrayNotHasKey('評価コメント', $rows[0]); // 評価コメントは無効なので含まれない
    }

    /**
     * canExport メソッドのテスト
     *
     * 管理者ユーザーがエクスポート可能であることを確認します。
     */
    public function testCanExportAsAdmin()
    {
        // Mock dependencies
        $learningtask_post = LearningtasksPosts::factory()->create();
        $page = Page::factory()->create();
        // Mock admin user
        $admin_user = Mockery::mock(User::class);
        $admin_user->shouldReceive('can')->with('role_article_admin')->andReturn(true);

        $exporter = new LearningtasksReportCsvExporter($learningtask_post->id, $page->id);

        $this->assertTrue($exporter->canExport($admin_user));
    }

    /**
     * canExport メソッドのテスト
     *
     * 教員ユーザーがエクスポート可能であることを確認します。
     */
    public function testCanExportAsTeacher()
    {
        // Mock dependencies
        $learningtask_post = LearningtasksPosts::factory()->create();
        $page = Page::factory()->create();
        $teacher_user = User::factory()->create();

        // Mock fetchTeacherUsers to include the teacher user
        $exporter = Mockery::mock(LearningtasksReportCsvExporter::class, [$learningtask_post->id, $page->id])->makePartial();
        $exporter->shouldReceive('fetchTeacherUsers')->andReturn(new EloquentCollection([$teacher_user]));

        $this->assertTrue($exporter->canExport($teacher_user));
    }

    /**
     * canExport メソッドのテスト
     *
     * 一般ユーザーがエクスポートできないことを確認します。
     */
    public function testCannotExportAsGuestUser()
    {
        // Mock dependencies
        $learningtask_post = LearningtasksPosts::factory()->create();
        $page = Page::factory()->create();
        $guest_user = User::factory()->create();

        // Mock fetchTeacherUsers to exclude the guest user
        $exporter = Mockery::mock(LearningtasksReportCsvExporter::class, [$learningtask_post->id, $page->id])->makePartial();
        $exporter->shouldReceive('fetchTeacherUsers')->andReturn(new EloquentCollection([]));

        $this->assertFalse($exporter->canExport($guest_user));
    }

    /**
     * export メソッドのテスト
     *
     * 正しいCSVレスポンスが返されることを確認します。
     */
    public function testExportWithValidData()
    {
        // Mock dependencies
        $learningtask_post = LearningtasksPosts::factory()->create(['post_title' => 'Test/Task']);
        $page = Page::factory()->create();

        $exporter = Mockery::mock(LearningtasksReportCsvExporter::class, [$learningtask_post->id, $page->id])->makePartial();

        // Mock methods
        $exporter->shouldReceive('getHeaderColumns')->andReturn(['ログインID', 'ユーザ名', '提出日時']);
        $exporter->shouldReceive('getRows')->with('http://example.com')->andReturn([
            ['ログインID' => 'user1', 'ユーザ名' => 'User One', '提出日時' => '2023-01-01 12:00:00'],
            ['ログインID' => 'user2', 'ユーザ名' => 'User Two', '提出日時' => '2023-01-02 12:00:00'],
        ]);

        // Call export
        $response = $exporter->export('http://example.com', 'UTF-8');

        // Verify response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="Test／Task_レポート.csv"', $response->headers->get('Content-Disposition'));
        $this->assertEquals("\xEF\xBB\xBF\"ログインID\",\"ユーザ名\",\"提出日時\"\n\"user1\",\"User One\",\"2023-01-01 12:00:00\"\n\"user2\",\"User Two\",\"2023-01-02 12:00:00\"\n", $response->getContent());
    }

    /**
     * export メソッドのテスト
     *
     * データが空の場合に正しいCSVレスポンスが返されることを確認します。
     */
    public function testExportWithEmptyData()
    {
        // Mock dependencies
        $learningtask_post = LearningtasksPosts::factory()->create(['post_title' => 'Empty Task']);
        $page = Page::factory()->create();

        $exporter = Mockery::mock(LearningtasksReportCsvExporter::class, [$learningtask_post->id, $page->id])->makePartial();

        // Mock methods
        $exporter->shouldReceive('getHeaderColumns')->andReturn(['ログインID', 'ユーザ名', '提出日時']);
        $exporter->shouldReceive('getRows')->with('http://example.com')->andReturn([]);

        // Call export
        $response = $exporter->export('http://example.com', 'UTF-8');

        // Verify response
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="Empty Task_レポート.csv"', $response->headers->get('Content-Disposition'));
        $this->assertEquals("\xEF\xBB\xBF\"ログインID\",\"ユーザ名\",\"提出日時\"\n", $response->getContent());
    }
}
