<?php

namespace Tests\Unit\Plugins\User\Learningtasks\Factories;

use App\Enums\LearningtaskExportType;
use App\Plugins\User\Learningtasks\Contracts\CsvDataProviderInterface;
use App\Plugins\User\Learningtasks\Factories\CsvDataProviderFactory;
use App\Plugins\User\Learningtasks\DataProviders\ReportCsvDataProvider;
use App\Plugins\User\Learningtasks\Repositories\LearningtaskUserRepository;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * CsvDataProviderFactory のユニットテストクラス
 * @coversDefaultClass \App\Plugins\User\Learningtasks\Factories\CsvDataProviderFactory
 */
class CsvDataProviderFactoryTest extends TestCase
{
    /** @var CsvDataProviderFactory */
    private $factory;

    /** @var LearningtaskUserRepository|MockInterface */
    private $mock_user_repository; // ReportCsvDataProvider が依存するため

    /**
     * 各テスト前に Factory と必要なモックを準備
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new CsvDataProviderFactory();

        // ReportCsvDataProvider が LearningtaskUserRepository をコンストラクタで必要とするため、
        // そのモックを準備し、コンテナにインスタンスとして登録しておく。
        // これにより、Factory 内の app(ReportCsvDataProvider::class) が解決可能になる。
        $this->mock_user_repository = Mockery::mock(LearningtaskUserRepository::class);
        $this->app->instance(LearningtaskUserRepository::class, $this->mock_user_repository);

        // ReportCsvDataProvider 自体も、依存性解決を含めてコンテナに bind しておくと確実。
        // (Laravel が自動解決できるなら $this->app->bind(ReportCsvDataProvider::class); だけでも可)
        $this->app->bind(ReportCsvDataProvider::class, function ($app) {
            // コンテナ経由で UserRepository を取得して注入
            return new ReportCsvDataProvider($app->make(LearningtaskUserRepository::class));
        });

        // 将来、ExamCsvDataProvider を追加する場合も同様に準備
        // $this->app->bind(ExamCsvDataProvider::class, function ($app) { ... });
    }

    /**
     * make メソッドが "report" タイプに対して ReportCsvDataProvider を返すことをテスト
     * @test
     * @covers ::make
     * @group learningtasks
     * @group learningtasks-factory
     */
    public function makeReturnsReportDataProviderForReportType(): void
    {
        // Arrange
        $export_type = LearningtaskExportType::report;

        // Act: Factory の make メソッドを実行 (内部で app() が呼ばれる)
        $data_provider = $this->factory->make($export_type);

        // Assert: 正しいインスタンスタイプとインターフェース実装を確認
        $this->assertInstanceOf(ReportCsvDataProvider::class, $data_provider);
        $this->assertInstanceOf(CsvDataProviderInterface::class, $data_provider);
    }

    /**
     * make メソッドが未知のタイプに対して InvalidArgumentException をスローすることをテスト
     * @test
     * @covers ::make
     * @group learningtasks
     * @group learningtasks-factory
     */
    public function makeThrowsExceptionForUnknownType(): void
    {
        // Arrange
        $unknown_type = 'some_unknown_export_type';

        // Assert: 例外のスローを期待
        $this->expectException(InvalidArgumentException::class);
        // 例外メッセージも検証 (Factory 内のメッセージと一致させる)
        $this->expectExceptionMessageMatches("/未知のエクスポートタイプに対応するデータプロバイダが見つかりません.*{$unknown_type}/");

        // Act: Factory の make メソッドを実行
        $this->factory->make($unknown_type);
    }

    // 将来 'exam' タイプを追加した場合のテスト例 (今はコメントアウト)
    /**
     * make メソッドが "exam" タイプに対して ExamCsvDataProvider を返すテスト (将来用)
     * @test
     * @group learningtasks
     * @group learningtasks-factory
     * @group learningtasks-future
     */
    // public function makeReturnsExamDataProviderForExamType(): void
    // {
    //     $this->markTestSkipped('ExamCsvDataProvider is not yet implemented.');
    //
    //     // Arrange
    //     $export_type = 'exam'; // または LearningtaskExportType::exam
    //     // ExamCsvDataProvider とその依存性を setUp またはここで bind
    //     // $this->app->bind(ExamCsvDataProvider::class, function($app){...});
    //
    //     // Act
    //     $data_provider = $this->factory->make($export_type);
    //
    //     // Assert
    //     $this->assertInstanceOf(ExamCsvDataProvider::class, $data_provider);
    //     $this->assertInstanceOf(CsvDataProviderInterface::class, $data_provider);
    // }
}
