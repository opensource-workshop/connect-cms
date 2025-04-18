<?php

namespace App\Plugins\User\Learningtasks\Factories;

use App\Enums\LearningtaskImportType;
use App\Enums\LearningtaskUseFunction;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Plugins\User\Learningtasks\Contracts\ColumnDefinitionInterface;
use App\Plugins\User\Learningtasks\Exceptions\FeatureDisabledException;
use App\Plugins\User\Learningtasks\Services\LearningtaskReportColumnDefinition;
use App\Plugins\User\Learningtasks\Services\LearningtaskSettingChecker;
use InvalidArgumentException;

/**
 * インポートタイプに基づいて ColumnDefinition インスタンスを生成する Factory クラス
 */
class ColumnDefinitionFactory
{
    /**
     * 指定されたインポートタイプに対応する ColumnDefinition オブジェクトを生成する。
     *
     * @param string $import_type インポート種別を示す文字列 ('report', 'exam' など)
     * @param LearningtasksPosts $post カラム定義のコンテキストとなる課題投稿オブジェクト
     * @return ColumnDefinitionInterface 生成された ColumnDefinition 実装インスタンス
     * @throws InvalidArgumentException 未知のインポートタイプが指定された場合
     */
    public function make(string $import_type, LearningtasksPosts $post): ColumnDefinitionInterface
    {
        // ColumnDefinition (例: Report) は SettingChecker を必要とし、
        // SettingChecker は $post を必要とするため、ここでまず SettingChecker を生成する。
        $setting_checker = new LearningtaskSettingChecker($post);

        // インポートタイプに応じて適切な ColumnDefinition を生成
        switch ($import_type) {
            case LearningtaskImportType::report:
                // レポート評価設定が有効かチェック
                if (!$setting_checker->isEnabled(LearningtaskUseFunction::use_report_evaluate)) {
                    // 無効なら専用例外をスロー
                    throw new FeatureDisabledException("この課題ではレポート評価機能が有効になっていません。");
                }
                // 有効ならインスタンスを返す (SettingChecker は渡す必要がある)
                return new LearningtaskReportColumnDefinition($setting_checker);
            // case 'exam': タイプが追加された場合の例 (実装クラスはまだない)
            //     return new LearningtaskExamColumnDefinition($setting_checker); // 将来追加
            default:
                throw new InvalidArgumentException("未知のカラム定義タイプ（インポートタイプ）です: {$import_type}");
        }
    }
}
