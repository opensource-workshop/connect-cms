<?php

namespace Tests\Unit\Plugins\User\Learningtasks\Services;

use App\Plugins\User\Learningtasks\Exceptions\AlreadyEvaluatedException;
use App\Plugins\User\Learningtasks\Exceptions\InvalidStudentException;
use App\Models\Common\Page;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Models\User\Learningtasks\LearningtasksUsersStatuses;
use App\Plugins\User\Learningtasks\Repositories\LearningtaskUserRepository;
use App\Plugins\User\Learningtasks\Services\LearningtaskEvaluationRowProcessor;
use App\User;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * LearningtaskEvaluationRowProcessor のテストクラス (DB利用)
 * @coversDefaultClass \App\Plugins\User\Learningtasks\Services\LearningtaskEvaluationRowProcessor
 */
class LearningtaskEvaluationRowProcessorTest extends TestCase
{
    use RefreshDatabase;

    /** @var LearningtaskUserRepository|MockInterface */
    private $mock_user_repository; // UserRepository はモック化する

    /** @var LearningtaskEvaluationRowProcessor */
    private $processor; // テスト対象

    /**
     * 各テスト前にモック準備とテスト対象を生成
     */
    protected function setUp(): void
    {
        parent::setUp();
        // UserRepository のモックを作成
        $this->mock_user_repository = Mockery::mock(LearningtaskUserRepository::class);
        // モックを注入して Processor をインスタンス化
        $this->processor = new LearningtaskEvaluationRowProcessor($this->mock_user_repository);
    }

    /**
     * 正常系: 有効なデータで評価レコードが作成されることをテスト
     * @test
     * @covers ::process
     * @group learningtasks
     * @group learningtasks-processor
     */
    public function processCreatesEvaluationRecordSuccessfully(): void
    {
        // Arrange: データ準備
        $page = Page::factory()->create();
        $post = LearningtasksPosts::factory()->create();
        $student = User::factory()->create(['userid' => 'student01']); // ログインID指定
        $importer = User::factory()->create(); // インポート実行者
        // 最新の提出レコードを作成
        $latest_submission = LearningtasksUsersStatuses::factory()->create([
            'post_id' => $post->id,
            'user_id' => $student->id,
            'task_status' => 1, // 提出ステータス
        ]);
        // UserRepository のモック設定: この学生が有効であると返す
        $this->mock_user_repository
            ->shouldReceive('getStudents')
            ->withArgs(fn ($p, $pg) => $p->id === $post->id && $pg->id === $page->id) // 引数チェック
            ->once() // キャッシュ機能のテストも兼ねる。初回のみ呼ばれるはず。
            ->andReturn(new EloquentCollection([$student])); // 学生を含む Collection を返す

        // インポートする評価データ
        $validated_data = [
            'userid' => $student->userid, // 'student01'
            'grade' => 'A',
            'comment' => 'よくできています',
        ];

        $this->actingAs($importer);

        // Act: process メソッドを実行
        $this->processor->process($validated_data, $post, $page, $importer);

        // Assert: DB アサーション
        $this->assertDatabaseHas('learningtasks_users_statuses', [
            'post_id' => $post->id,
            'user_id' => $student->id,
            'task_status' => 2, // 評価ステータス
            'grade' => 'A',
            'comment' => 'よくできています',
            'created_id' => $importer->id, // 作成者(評価者)ID
        ]);
        // 念のため、評価レコードが1件だけ作成されたことを確認
        $this->assertDatabaseCount('learningtasks_users_statuses', 2); // 提出 + 評価
    }

    /**
     * 異常系: 既に評価済みの場合に AlreadyEvaluatedException がスローされるテスト
     * @test
     * @covers ::process
     * @group learningtasks
     * @group learningtasks-processor
     */
    public function processThrowsExceptionWhenAlreadyEvaluated(): void
    {
         // Arrange
         $page = Page::factory()->create();
         $post = LearningtasksPosts::factory()->create();
         $student = User::factory()->create(['userid' => 'student02']);
         $importer = User::factory()->create();
         $latest_submission = LearningtasksUsersStatuses::factory()->create([
             'post_id' => $post->id, 'user_id' => $student->id, 'task_status' => 1,
         ]);
         // 既存の評価レコードを作成 (ID > 提出ID になるように)
         $existing_evaluation = LearningtasksUsersStatuses::factory()->create([
             'post_id' => $post->id, 'user_id' => $student->id, 'task_status' => 2,
             'id' => $latest_submission->id + 1, // IDを調整 (Factoryでできない場合は手動更新)
             'created_id' => $importer->id,
         ]);
         $this->mock_user_repository->shouldReceive('getStudents')->once()->andReturn(new EloquentCollection([$student]));
         $validated_data = ['userid' => $student->userid, 'grade' => 'B'];

         // Assert: 例外のスローを期待
         $this->expectException(AlreadyEvaluatedException::class);

         // Act
        try {
             $this->processor->process($validated_data, $post, $page, $importer);
        } catch (Exception $e) {
            // 例外発生後、DBに追加の評価レコードがないことを確認
            $this->assertDatabaseCount('learningtasks_users_statuses', 2); // 提出 + 既存評価 のままのはず
            throw $e; // 例外を再スローして PHPUnit に検知させる
        }
    }

    /**
     * 異常系: ユーザーが受講生でない場合に InvalidStudentException がスローされるテスト
     * @test
     * @covers ::process
     * @group learningtasks
     * @group learningtasks-processor
     */
    public function processThrowsExceptionWhenUserIsNotStudent(): void
    {
         // Arrange
         $page = Page::factory()->create();
         $post = LearningtasksPosts::factory()->create();
         $not_a_student = User::factory()->create(['userid' => 'notstudent']);
         $importer = User::factory()->create();
         LearningtasksUsersStatuses::factory()->create(['post_id' => $post->id, 'user_id' => $not_a_student->id, 'task_status' => 1]);
         // UserRepository が空のコレクションを返すようにモック
         $this->mock_user_repository->shouldReceive('getStudents')->once()->andReturn(new EloquentCollection([]));
         $validated_data = ['userid' => $not_a_student->userid, 'grade' => 'C'];

         // Assert: 例外を期待
         $this->expectException(InvalidStudentException::class);

         // Act
        try {
            $this->processor->process($validated_data, $post, $page, $importer);
        } catch (Exception $e) {
            // DBに評価が追加されていないことを確認
            $this->assertDatabaseMissing('learningtasks_users_statuses', [
                'post_id' => $post->id, 'user_id' => $not_a_student->id, 'task_status' => 2
            ]);
            throw $e;
        }
    }

    /**
     * 異常系: 提出記録がない場合に Exception がスローされるテスト
     * @test
     * @covers ::process
     * @group learningtasks
     * @group learningtasks-processor
     */
    public function processThrowsExceptionWhenNoSubmissionExists(): void
    {
        // Arrange
        $page = Page::factory()->create();
        $post = LearningtasksPosts::factory()->create();
        $student = User::factory()->create(['userid' => 'student03']);
        $importer = User::factory()->create();
        // 提出記録 (task_status=1) は作成しない
        $this->mock_user_repository->shouldReceive('getStudents')->once()->andReturn(new EloquentCollection([$student]));
        $validated_data = ['userid' => $student->userid, 'grade' => 'D'];

        // Assert: 汎用 Exception を期待 (メッセージも確認)
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/提出記録がユーザー .* に見つかりません/');

        // Act
        try {
            $this->processor->process($validated_data, $post, $page, $importer);
        } catch (Exception $e) {
             // DBに評価が追加されていないことを確認
            $this->assertDatabaseMissing('learningtasks_users_statuses', [
                'post_id' => $post->id, 'user_id' => $student->id, 'task_status' => 2
            ]);
            throw $e;
        }
    }

     /**
     * 異常系: ユーザーIDが存在しない場合に ModelNotFoundException がスローされるテスト
     * @test
     * @covers ::process
     * @group learningtasks
     * @group learningtasks-processor
     */
    public function processThrowsModelNotFoundExceptionWhenUserDoesNotExist(): void
    {
        // Arrange
        $page = Page::factory()->create();
        $post = LearningtasksPosts::factory()->create();
        $importer = User::factory()->create();
        $validated_data = ['userid' => 'nonexistentuser', 'grade' => 'A'];
        // UserRepository は呼ばれる前に例外発生するのでモック不要

        // Assert: ModelNotFoundException を期待
        $this->expectException(ModelNotFoundException::class);

        // Act
        $this->processor->process($validated_data, $post, $page, $importer);
    }
}
