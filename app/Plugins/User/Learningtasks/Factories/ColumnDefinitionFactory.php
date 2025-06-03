<?php

namespace App\Plugins\User\Learningtasks\Factories;

use App\Enums\LearningtaskExportType;
use App\Enums\LearningtaskImportType;
use App\Enums\LearningtaskUseFunction;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Plugins\User\Learningtasks\Contracts\ColumnDefinitionInterface;
use App\Plugins\User\Learningtasks\Exceptions\FeatureDisabledException;
use App\Plugins\User\Learningtasks\Services\LearningtaskReportColumnDefinition;
use App\Plugins\User\Learningtasks\Services\LearningtaskSettingChecker;
use InvalidArgumentException;

/**
 * オペレーションタイプに基づいて ColumnDefinition インスタンスを生成する Factory クラス
 */
class ColumnDefinitionFactory
{
    /**
     * 指定されたオペレーションタイプに対応する ColumnDefinition オブジェクトを生成する。
     *
     * @param string $type オペレーション種別を示す文字列 ('report', 'exam' など)
     * @param LearningtaskSettingChecker $checker 設定有効性を判定するサービス
     * @return ColumnDefinitionInterface 生成された ColumnDefinition 実装インスタンス
     * @throws InvalidArgumentException 未知のオペレーションタイプが指定された場合
     */
    public function make(string $type, LearningtaskSettingChecker $checker): ColumnDefinitionInterface
    {
        // タイプに応じて適切な ColumnDefinition を生成
        switch ($type) {
            case LearningtaskImportType::report:
                // レポート評価設定が有効かチェック
                if (!$checker->isEnabled(LearningtaskUseFunction::use_report_evaluate)) {
                    // 無効なら専用例外をスロー
                    throw new FeatureDisabledException("この課題ではレポート評価機能が有効になっていません。");
                }
                // 有効ならインスタンスを返す (SettingChecker は渡す必要がある)
                return new LearningtaskReportColumnDefinition($checker);
            case LearningtaskExportType::report:
                return new LearningtaskReportColumnDefinition($checker);
            // case 'exam': タイプが追加された場合の例 (実装クラスはまだない)
            //     return new LearningtaskExamColumnDefinition($checker); // 将来追加
            default:
                throw new InvalidArgumentException("未知のカラム定義タイプ（インポートタイプ）です: {$type}");
        }
    }
}
