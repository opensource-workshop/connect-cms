<?php

namespace App\Plugins\User\Learningtasks\Factories;

use App\Enums\LearningtaskImportType;
use App\Plugins\User\Learningtasks\Contracts\RowProcessorExceptionHandlerInterface;
use App\Plugins\User\Learningtasks\Handlers\ReportExceptionHandler;
// use App\Plugins\User\Learningtasks\Handlers\ExamExceptionHandler; // 将来追加するなら
use InvalidArgumentException;

class ExceptionHandlerFactory
{
    /**
     * 指定されたインポートタイプに対応する例外ハンドラを生成（または解決）する。
     *
     * @param string $import_type インポート種別 ('report', 'exam' など)
     * @return RowProcessorExceptionHandlerInterface
     * @throws InvalidArgumentException 未知のタイプが指定された場合
     */
    public function make(string $import_type): RowProcessorExceptionHandlerInterface
    {
        // ハンドラ自体に依存性注入が必要になる可能性を考慮し、app() で解決するのが望ましい
        // （ServiceProvider で各ハンドラクラスを bind しておくか、Laravel が自動解決可能なら bind 不要）
        switch ($import_type) { // PHP 7.x 互換のため switch を使用
            case LearningtaskImportType::report:
                return app(ReportExceptionHandler::class);
            // case 'exam':
            //     return app(ExamExceptionHandler::class); // 将来追加
            default:
                throw new InvalidArgumentException("未知のインポートタイプに対応する例外ハンドラが見つかりません: {$import_type}");
        }
    }
}
