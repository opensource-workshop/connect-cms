<?php

namespace App\Plugins\User\Learningtasks\Factories;

use App\Enums\LearningtaskImportType;
use App\Plugins\User\Learningtasks\Contracts\RowProcessorInterface;
use App\Plugins\User\Learningtasks\Services\LearningtaskEvaluationRowProcessor;
use InvalidArgumentException;

/**
 * インポートタイプに基づいて RowProcessor インスタンスを生成する Factory クラス (PHP 7.x 互換)
 */
class RowProcessorFactory
{
    /**
     * 指定されたインポートタイプに対応する RowProcessor オブジェクトを生成する。
     *
     * @param string $import_type インポート種別を示す文字列 ('report', 'exam' など)
     * @return RowProcessorInterface 生成された RowProcessor 実装インスタンス
     * @throws InvalidArgumentException 未知のインポートタイプが指定された場合
     */
    public function make(string $import_type): RowProcessorInterface
    {
        // $import_type の値に応じて、適切な RowProcessor 実装を返す
        // 各 Processor 実装自体が依存性を持つ可能性も考慮し、
        // new する代わりに app() ヘルパーでコンテナから解決するのが望ましい
        // (ServiceProvider で各実装クラスが bind されているか、自動解決可能である前提)
        switch ($import_type) {
            case LearningtaskImportType::report:
                // ServiceProviderで登録済みの実装クラスをコンテナから解決
                return app(LearningtaskEvaluationRowProcessor::class);

            // case 'exam': // 将来 'exam' タイプが追加された場合
            //     return app(LearningtaskExamRowProcessor::class);

            default:
                 // どの case にも一致しない場合
                throw new InvalidArgumentException("未知の行処理タイプ（インポートタイプ）です: {$import_type}");
        }
    }
}
