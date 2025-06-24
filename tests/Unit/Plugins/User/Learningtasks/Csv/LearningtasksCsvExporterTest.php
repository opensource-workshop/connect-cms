<?php

namespace Tests\Unit\Plugins\User\Learningtasks\Csv; // ★ Exporter のテスト用名前空間

use App\Enums\CsvCharacterCode;
use App\Models\Common\Page;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Plugins\User\Learningtasks\Contracts\ColumnDefinitionInterface;
use App\Plugins\User\Learningtasks\Contracts\CsvDataProviderInterface;
use App\Plugins\User\Learningtasks\Csv\LearningtasksCsvExporter;
use App\Plugins\User\Learningtasks\Repositories\LearningtaskUserRepository;
use App\User;
use App\Utilities\Csv\CsvUtils;
use App\Utilities\File\FileUtils;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

/**
 * LearningtasksCsvExporter のテストクラス
 * @coversDefaultClass \App\Plugins\User\Learningtasks\Csv\LearningtasksCsvExporter
 */
class LearningtasksCsvExporterTest extends TestCase
{
    use RefreshDatabase;

    /** @var ColumnDefinitionInterface|MockInterface */
    private $mock_column_definition;
    /** @var CsvDataProviderInterface|MockInterface */
    private $mock_data_provider;
    /** @var LearningtaskUserRepository|MockInterface */
    private $mock_user_repository;
    /** @var LearningtasksCsvExporter */
    private $exporter;

    // テスト用共通データ
    private $page;
    private $post;
    private $exporter_user;

    protected function setUp(): void
    {
        parent::setUp();
        // モックを作成
        $this->mock_column_definition = Mockery::mock(ColumnDefinitionInterface::class);
        $this->mock_data_provider = Mockery::mock(CsvDataProviderInterface::class);
        $this->mock_user_repository = Mockery::mock(LearningtaskUserRepository::class);

        // 基本的なテストデータ
        $this->page = Page::factory()->create();
        $this->post = LearningtasksPosts::factory()->create(['post_title' => 'エクスポートテスト課題']);
        $this->exporter_user = User::factory()->create(); // エクスポート実行者

        // テスト対象クラスを生成 (モックを注入)
        $this->exporter = new LearningtasksCsvExporter(
            $this->post,
            $this->page,
            $this->mock_column_definition,
            $this->mock_data_provider,
            $this->mock_user_repository
        );
    }

    /** CSV文字列をパースして配列に変換するヘルパー (PluginTestからコピーまたは共通化) */
    private function parseCsvString(string $csv_string): array
    {
        if (strpos($csv_string, CsvUtils::bom) === 0) {
            $csv_string = substr($csv_string, strlen(CsvUtils::bom));
        }
        $lines = explode("\n", trim($csv_string));
        $data = [];
        foreach ($lines as $line) {
            if (!empty($line)) {
                $data[] = str_getcsv($line);
            }
        }
        return $data;
    }

    /**
     * 正常系: export メソッドが正しい StreamedResponse を返すことをテスト
     * @test
     * @covers ::export
     * @group learningtasks
     * @group learningtasks-exporter
     */
    public function exportReturnsCorrectCsvStreamResponse(): void
    {
        // Arrange
        $site_url = 'http://localhost';
        $character_code = CsvCharacterCode::utf_8;
        $expected_headers = ['ログインID', 'ユーザ名', '評価'];
        $expected_rows = [
            ['student1', '学生 一郎', 'A'],
            ['student2', '学生 次郎', 'B'],
        ];
        $expected_filename = FileUtils::toValidFilename($this->post->post_title . '_Export.csv');

        // モックの振る舞いを設定
        $this->mock_column_definition->shouldReceive('getHeaders')
            ->once()
            ->andReturn($expected_headers);

        $this->mock_data_provider->shouldReceive('getRows')
            ->once()
            ->with(
                $this->mock_column_definition, // ColumnDefinition が渡されること
                $this->post,
                $this->page,
                $site_url
            )
            ->andReturn(new \ArrayIterator($expected_rows)); // iterable を返す (配列でも可)

        // Act
        $response = $this->exporter->export($site_url, $character_code);

        // Assert: レスポンスの検証
        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));

        // Content-Disposition ヘッダー (ファイル名) の検証
        // FileUtils::toValidFilename が返すのは、エンコードや "" で囲まれる前の純粋なファイル名文字列のはず
        $raw_expected_filename = FileUtils::toValidFilename($this->post->post_title . '_Export.csv');
        $content_disposition_header = $response->headers->get('Content-Disposition');
        $this->assertNotNull($content_disposition_header);

        // 'attachment' が含まれることを確認
        $this->assertStringContainsString('attachment', $content_disposition_header, 'Content-Disposition should indicate attachment.');

        // filename*=utf-8''... (RFC 6266 形式) が正しいエンコードされたファイル名を含むことを確認
        // PHP の rawurlencode() はスペースを %20 にエンコードする
        $expected_filename_star_part = "filename*=utf-8''" . rawurlencode($raw_expected_filename);
        $this->assertStringContainsString($expected_filename_star_part, $content_disposition_header, 'Content-Disposition filename* part is incorrect.');

        // ストリーム内容の検証
        ob_start();
        $response->sendContent();
        $csv_content = ob_get_clean();

        $this->assertTrue(strpos($csv_content, CsvUtils::bom) === 0, 'UTF-8 CSV should have BOM');
        $parsed_data = $this->parseCsvString($csv_content);

        $this->assertCount(3, $parsed_data); // ヘッダー + 2行
        $this->assertEquals($expected_headers, $parsed_data[0]); // ヘッダー確認
        $this->assertEquals($expected_rows[0], $parsed_data[1]); // データ1行目
        $this->assertEquals($expected_rows[1], $parsed_data[2]); // データ2行目
    }

    /**
     * canExport が管理者ユーザーに対して true を返すことをテスト
     * @test
     * @covers ::canExport
     * @group learningtasks
     * @group learningtasks-exporter
     * @group learningtasks-permission
     */
    public function canExportReturnsTrueForAdminUser(): void
    {
        // Arrange: 管理者ユーザーのモックを作成
        /** @var User|MockInterface $admin_user */
        $admin_user = Mockery::mock(User::class);
        // can('role_article_admin') が true を返すように設定
        $admin_user->shouldReceive('can')
                   ->with('role_article_admin') // この権限名を確認
                   ->once() // can は1回呼ばれるはず
                   ->andReturn(true);

        // UserRepository::getTeachers はこのパスでは呼ばれないはず
        $this->mock_user_repository->shouldNotReceive('getTeachers');

        // Act: canExport を実行
        $result = $this->exporter->canExport($admin_user);

        // Assert: 結果が true であることを確認
        $this->assertTrue($result, '管理者はエクスポートできるべき');
    }

    /**
     * canExport が担当教員ユーザーに対して true を返すことをテスト
     * @test
     * @covers ::canExport
     * @group learningtasks
     * @group learningtasks-exporter
     * @group learningtasks-permission
     */
    public function canExportReturnsTrueForTeacherUser(): void
    {
        // Arrange: 教員ユーザーのモックを作成
        $teacher_user = User::factory()->create();

        // UserRepository がこの教員を含むコレクションを返すように設定
        $this->mock_user_repository
            ->shouldReceive('getTeachers')
            ->with($this->post, $this->page) // 正しい引数で呼ばれるか確認
            ->once() // getTeachers は1回呼ばれるはず
            ->andReturn(new EloquentCollection([$teacher_user]));
        // can メソッドの振る舞いのみ上書き (管理者ではないとする)
        $partial_mock_teacher = Mockery::mock($teacher_user)->makePartial();
        $partial_mock_teacher->shouldReceive('can')
                             ->with('role_article_admin')
                             ->once()
                             ->andReturn(false);

        // Act: canExport を実行
        $result = $this->exporter->canExport($partial_mock_teacher);

        // Assert: 結果が true であることを確認
        $this->assertTrue($result, '担当教員はエクスポートできるべき');
    }

    /**
     * canExport が管理者でも担当教員でもないユーザーに対して false を返すことをテスト
     * @test
     * @covers ::canExport
     * @group learningtasks
     * @group learningtasks-exporter
     * @group learningtasks-permission
     */
    public function canExportReturnsFalseForOtherUser(): void
    {
         // Arrange: その他のユーザーのモックを作成
        /** @var User|MockInterface $other_user */
        $other_user = Mockery::mock(User::class);
        // 'id' 属性へのアクセスをモック
        $other_user->shouldReceive('getAttribute')->with('id')->andReturn(100);

        // can('role_article_admin') は false を返す
        $other_user->shouldReceive('can')
                     ->with('role_article_admin')
                     ->once()
                     ->andReturn(false);

        // UserRepository が空のコレクションを返すように設定 (または他のユーザーリスト)
        $this->mock_user_repository
            ->shouldReceive('getTeachers')
            ->with($this->post, $this->page)
            ->once()
            ->andReturn(new EloquentCollection([])); // 空のコレクション (ユーザーを含まない)

        // Act: canExport を実行
        $result = $this->exporter->canExport($other_user);

        // Assert: 結果が false であることを確認
        $this->assertFalse($result, '管理者でも担当教員でもないユーザーはエクスポートできないべき');
    }
}
