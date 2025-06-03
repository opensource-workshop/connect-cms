<?php

namespace Tests\Unit\Plugins\User\Learningtasks\DataProviders;

use App\Models\Common\Page;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Models\User\Learningtasks\LearningtasksUsersStatuses;
use App\Plugins\User\Learningtasks\Contracts\ColumnDefinitionInterface;
use App\Plugins\User\Learningtasks\DataProviders\ReportCsvDataProvider;
use App\Plugins\User\Learningtasks\Repositories\LearningtaskUserRepository;
use App\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ReportCsvDataProviderTest extends TestCase
{
    use RefreshDatabase; // ★ DBテスト

    /** @var LearningtaskUserRepository|MockInterface */
    private $mock_user_repository;

    /** @var ColumnDefinitionInterface|MockInterface */
    private $mock_column_definition;

    /** @var ReportCsvDataProvider */
    private $data_provider; // テスト対象

    // テストで共通して使うコンテキストデータ
    private $page;
    private $post;
    private $site_url = 'http://localhost.test'; // ファイルURL生成用

    protected function setUp(): void
    {
        parent::setUp();

        // モックを作成
        $this->mock_user_repository = Mockery::mock(LearningtaskUserRepository::class);
        $this->mock_column_definition = Mockery::mock(ColumnDefinitionInterface::class);

        // テスト対象クラスを生成 (モックを注入)
        $this->data_provider = new ReportCsvDataProvider($this->mock_user_repository);

        // 基本的なコンテキストデータを作成
        $this->page = Page::factory()->create();
        $this->post = LearningtasksPosts::factory()->create();
    }

    /**
     * 正常系: 全てのオプションカラムが有効な場合に正しいデータ行が yield されるテスト
     * @test
     * @covers ::getRows
     * @group learningtasks
     * @group learningtasks-dataprovider
     */
    public function getRowsYieldsCorrectDataWhenAllColumnsEnabled(): void
    {
        // Arrange
        // 1. 学生ユーザーと、その提出・評価データを作成
        $student1 = User::factory()->create(['userid' => 's001', 'name' => '学生 甲']);
        $student2 = User::factory()->create(['userid' => 's002', 'name' => '学生 乙']);

        $submission1_created_at_str = now()->subDays(2)->toDateTimeString();
        $submission1 = LearningtasksUsersStatuses::factory()->create([
            'post_id' => $this->post->id, 'user_id' => $student1->id, 'task_status' => 1,
            'comment' => '学生 甲の提出本文', 'upload_id' => 777, 'created_at' => $submission1_created_at_str,
        ]);
        // student1 の評価 (grade と comment がある)
        LearningtasksUsersStatuses::factory()->create([
            'post_id' => $this->post->id, 'user_id' => $student1->id, 'task_status' => 2,
            'grade' => '秀', 'comment' => '甲の評価コメント', 'created_at' => now()->subDay(),
        ]);

        $submission2_created_at_str = now()->subHours(5)->toDateTimeString();
        // student2 の提出 (ファイルなし、評価もなし)
        LearningtasksUsersStatuses::factory()->create([
            'post_id' => $this->post->id, 'user_id' => $student2->id, 'task_status' => 1,
            'comment' => '学生 乙の本文のみ', 'upload_id' => null, 'created_at' => $submission2_created_at_str,
        ]);

        // 2. ColumnDefinition のモック設定: 全てのヘッダーを返す
        $expected_headers = ['ログインID', 'ユーザ名', '提出日時', '提出回数', '本文', 'ファイルURL', '評価', '評価コメント'];
        $this->mock_column_definition->shouldReceive('getHeaders')->once()->andReturn($expected_headers);

        // 3. UserRepository のモック設定: 作成した学生を返す
        $this->mock_user_repository->shouldReceive('getStudents')
            ->with($this->post, $this->page) // 正しい引数で呼ばれるか
            ->once()
            ->andReturn(new EloquentCollection([$student1, $student2])); // 学生のコレクション

        // Act: getRows を呼び出し、結果を配列に変換して検証しやすくする
        $result_iterable = $this->data_provider->getRows(
            $this->mock_column_definition,
            $this->post,
            $this->page,
            $this->site_url
        );
        // Generator の結果を配列に変換
        $result_rows_array = iterator_to_array($result_iterable);

        // Assert
        $this->assertCount(2, $result_rows_array, '学生2名分のデータが yield されるべき');

        // --- 1人目のデータ検証 ---
        $expected_row_student1 = [
            $student1->userid,                              // ログインID
            $student1->name,                                // ユーザ名
            $submission1_created_at_str,                    // 提出日時
            '1',                                            // 提出回数 (このテストでは1回と仮定)
            '学生 甲の提出本文',                            // 本文
            $this->site_url . '/file/' . $submission1->upload_id, // ファイルURL
            '秀',                                           // 評価
            '甲の評価コメント',                             // 評価コメント
        ];
        $this->assertEquals($expected_row_student1, $result_rows_array[0], '学生1のデータが期待通りであること');

        // --- 2人目のデータ検証 ---
        $expected_row_student2 = [
            $student2->userid,
            $student2->name,
            $submission2_created_at_str,
            '1',
            '学生 乙の本文のみ',
            null, // ファイルURLなし (optional(null)->upload_id は null)
            null, // 評価なし (optional(null)->grade は null)
            null, // 評価コメントなし (optional(null)->comment は null)
        ];
        $this->assertEquals($expected_row_student2, $result_rows_array[1], '学生2のデータが期待通りであること');
    }

    /**
     * オプションカラム設定が無効な場合、基本データのみが yield されるテスト
     * @test
     * @covers ::getRows
     * @group learningtasks
     * @group learningtasks-dataprovider
     */
    public function getRowsYieldsOnlyBaseDataWhenOptionalColumnsDisabled(): void
    {
        // Arrange
        // 1. 学生と関連データを作成 (オプションカラム用のデータも含む)
        $student1 = User::factory()->create(['userid' => 's001', 'name' => '学生A']);
        $submission1_created_at_str = now()->subDays(2)->toDateTimeString();
        // 提出データには本文やファイルIDも設定しておく
        LearningtasksUsersStatuses::factory()->create([
            'post_id' => $this->post->id, 'user_id' => $student1->id, 'task_status' => 1,
            'comment' => 'S1提出本文データ', 'upload_id' => 777, 'created_at' => $submission1_created_at_str,
        ]);
        // 評価データにも評価コメントを設定しておく
        LearningtasksUsersStatuses::factory()->create([
            'post_id' => $this->post->id, 'user_id' => $student1->id, 'task_status' => 2,
            'grade' => '秀', 'comment' => 'S1評価コメントデータ'
        ]);

        // 2. ColumnDefinition のモック設定: ★ 基本ヘッダーのみを返すようにする ★
        $base_headers = ['ログインID', 'ユーザ名', '提出日時', '提出回数'];
        $this->mock_column_definition->shouldReceive('getHeaders')->once()->andReturn($base_headers);

        // 3. UserRepository のモック設定: 学生リストを返す
        $this->mock_user_repository->shouldReceive('getStudents')
            ->with($this->post, $this->page)
            ->once()
            ->andReturn(new EloquentCollection([$student1]));

        // Act: getRows を呼び出し、結果を配列に変換
        $result_iterable = $this->data_provider->getRows(
            $this->mock_column_definition,
            $this->post,
            $this->page,
            $this->site_url
        );
        $result_rows_array = iterator_to_array($result_iterable);

        // Assert
        $this->assertCount(1, $result_rows_array, '学生1名分のデータが yield されるべき');

        // 1人目のデータ検証 (★ 基本カラムのみであること、およびその内容を確認 ★)
        $expected_row_student1 = [
            $student1->userid,
            $student1->name,
            $submission1_created_at_str,
            '1', // 提出回数 (このテストでは1回と仮定)
        ];
        // 返された行のデータが期待通りか
        $this->assertEquals($expected_row_student1, $result_rows_array[0], '学生1のデータが基本カラムのみで期待通りであること');
        // 返された行のカラム数が基本ヘッダーの数と一致するか
        $this->assertCount(count($base_headers), $result_rows_array[0], '返された行のカラム数が基本ヘッダー数と一致すること');
    }

    /**
     * 対象学生がいない場合に空の Generator (結果として空配列) が返るテスト
     * @test
     * @covers ::getRows
     * @group learningtasks
     * @group learningtasks-dataprovider
     */
    public function getRowsYieldsEmptyWhenNoStudents(): void
    {
        // Arrange
        // 1. ColumnDefinition のモック設定 (何らかのヘッダーを返す)
        //    getRows 内で header_columns が空でないかのチェックがあるため設定する
        $expected_headers = ['ログインID', 'ユーザ名']; // 例: 基本ヘッダー
        $this->mock_column_definition->shouldReceive('getHeaders')->once()->andReturn($expected_headers);

        // 2. UserRepository のモック設定: ★ 空のコレクションを返す ★
        $this->mock_user_repository->shouldReceive('getStudents')
            ->with($this->post, $this->page) // 正しい引数で呼ばれるか
            ->once()
            ->andReturn(new EloquentCollection([])); // ★ 空の学生リストを返す

        // Act: getRows を呼び出し、結果を配列に変換
        $result_iterable = $this->data_provider->getRows(
            $this->mock_column_definition,
            $this->post,
            $this->page,
            $this->site_url
        );
        // Generator の結果を配列に変換して検証しやすくする
        $result_rows_array = iterator_to_array($result_iterable);

        // Assert
        // ★ データ行が0件であることを確認
        $this->assertCount(0, $result_rows_array, '対象学生がいない場合、データ行は0件であるべき');
    }

    /**
     * 学生は存在するが提出/評価記録がない場合、関連フィールドが空で yield されるテスト
     * @test
     * @covers ::getRows
     * @group learningtasks
     * @group learningtasks-dataprovider
     */
    public function getRowsYieldsDataWithEmptyFieldsWhenNoStatuses(): void
    {
        // Arrange
        // 1. 学生ユーザーを作成
        $student1 = User::factory()->create(['userid' => 's001', 'name' => '学生A']);
        // ★ このテストでは、この学生に対する LearningtasksUsersStatuses レコードは作成しない

        // 2. ColumnDefinition のモック設定 (提出・評価関連ヘッダーも含むフルヘッダーを想定)
        $expected_headers = ['ログインID', 'ユーザ名', '提出日時', '提出回数', '本文', 'ファイルURL', '評価', '評価コメント'];
        $this->mock_column_definition->shouldReceive('getHeaders')->once()->andReturn($expected_headers);

        // 3. UserRepository のモック設定: 作成した学生を返す
        $this->mock_user_repository->shouldReceive('getStudents')
            ->with($this->post, $this->page)
            ->once()
            ->andReturn(new EloquentCollection([$student1]));

        // Act: getRows を呼び出し、結果を配列に変換
        $result_iterable = $this->data_provider->getRows(
            $this->mock_column_definition,
            $this->post,
            $this->page,
            $this->site_url
        );
        // Generator の結果を配列に変換して検証しやすくする
        $result_rows_array = iterator_to_array($result_iterable);

        // Assert
        $this->assertCount(1, $result_rows_array, '学生1名分のデータが yield されるべき');

        // データ検証: 提出・評価関連のフィールドが空またはデフォルト値(0)であることを確認
        // ReportCsvDataProvider 内の getColumnDataGenerators メソッドの実装 (optional() の使用) に依存する
        $expected_row_data = [
            $student1->userid, // ログインID
            $student1->name,   // ユーザ名
            null,             // 提出日時 (optional(null)->created_at は null)
            '0',              // 提出回数 (statuses->where(...)->count() は 0)
            null,             // 本文 (optional(null)->comment は null)
            null,             // ファイルURL (optional(null)->upload_id は null で、その結果 URL も null)
            null,             // 評価 (optional(null)->grade は null)
            null,             // 評価コメント (optional(null)->comment は null)
        ];
        $this->assertEquals($expected_row_data, $result_rows_array[0], '提出/評価がない学生のデータが期待通りであること');
    }

    /**
     * 単語数・字数カラムが有効な場合に正しい値が出力されるテスト（カスタムアクセサ対応）
     * @test
     * @covers ::getRows
     * @group learningtasks
     * @group learningtasks-dataprovider
     */
    public function getRowsYieldsWordAndCharCountColumns(): void
    {
        // Arrange
        $student = User::factory()->create(['userid' => 's003', 'name' => '学生 丙']);
        $submission_comment = 'word テスト 123'; // 半角2単語+全角1単語+数字1単語 → str_word_count=3, mb_strlen=11
        $submission_created_at_str = now()->subHour()->toDateTimeString();
        $submission = LearningtasksUsersStatuses::factory()->create([
            'post_id' => $this->post->id, 'user_id' => $student->id, 'task_status' => 1,
            'comment' => $submission_comment, 'upload_id' => 888, 'created_at' => $submission_created_at_str,
        ]);
        // 評価データも追加（値は使わない）
        LearningtasksUsersStatuses::factory()->create([
            'post_id' => $this->post->id, 'user_id' => $student->id, 'task_status' => 2,
            'grade' => '優', 'comment' => '評価コメント',
        ]);

        // ヘッダーに単語数・字数を含める
        $headers = ['ログインID', 'ユーザ名', '提出日時', '提出回数', '単語数', '字数'];
        $this->mock_column_definition->shouldReceive('getHeaders')->once()->andReturn($headers);
        $this->mock_user_repository->shouldReceive('getStudents')
            ->with($this->post, $this->page)
            ->once()
            ->andReturn(new EloquentCollection([$student]));

        // Act
        $result_iterable = $this->data_provider->getRows(
            $this->mock_column_definition,
            $this->post,
            $this->page,
            $this->site_url
        );
        $result_rows_array = iterator_to_array($result_iterable);

        // Assert
        $this->assertCount(1, $result_rows_array, '学生1名分のデータが yield されるべき');
        $expected_row = [
            $student->userid,
            $student->name,
            $submission_created_at_str,
            '1', // 提出回数
            str_word_count($submission_comment), // 単語数（PHPのstr_word_count仕様）
            mb_strlen($submission_comment),      // 字数（全角・半角問わず）
        ];
        $this->assertEquals($expected_row, $result_rows_array[0], '単語数・字数カラムの値が正しく出力されること');
    }
}
