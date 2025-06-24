<?php

namespace App\Plugins\User\Learningtasks\Factories;

use App\Enums\LearningtaskExportType;
use App\Plugins\User\Learningtasks\Contracts\CsvDataProviderInterface;
use App\Plugins\User\Learningtasks\DataProviders\ReportCsvDataProvider;
use InvalidArgumentException;

/**
 * エクスポートタイプに基づいて CsvDataProvider インスタンスを生成する Factory クラス
 */
class CsvDataProviderFactory
{
    /**
     * 指定されたエクスポートタイプに対応する CsvDataProvider オブジェクトを生成（解決）する。
     *
     * @param string $export_type エクスポート種別を示す文字列 ('report', 'exam' など)
     * @return CsvDataProviderInterface 生成されたデータプロバイダ実装インスタンス
     * @throws InvalidArgumentException 未知のエクスポートタイプが指定された場合
     */
    public function make(string $export_type): CsvDataProviderInterface
    {
        // サービスコンテナ経由で実装クラスを解決する。
        // これにより、ReportCsvDataProvider が必要とする UserRepository なども自動的に注入される。
        // （ServiceProvider で ReportCsvDataProvider と UserRepository が bind されているか、
        //   Laravel が自動解決できる必要がある）
        switch ($export_type) {
            case LearningtaskExportType::report:
                return app(ReportCsvDataProvider::class);

            // case LearningtaskExportType::exam: // 将来の試験エクスポート用
            //     return app(ExamCsvDataProvider::class);

            default:
                throw new InvalidArgumentException("未知のエクスポートタイプに対応するデータプロバイダが見つかりません: {$export_type}");
        }
    }
}
