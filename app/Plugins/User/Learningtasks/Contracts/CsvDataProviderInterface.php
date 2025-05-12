<?php

namespace App\Plugins\User\Learningtasks\Contracts;

use App\Models\Common\Page;
use App\Models\User\Learningtasks\LearningtasksPosts;
// iterable は PHP 7.1 以降で使用可能
// Generator を返す場合は Generator を use してもよい
// use Generator;

/**
 * CSV エクスポート用のデータ行を提供するクラスのためのインターフェース
 */
interface CsvDataProviderInterface
{
    /**
     * 指定されたコンテキストとカラム定義に基づき、CSVに出力するデータ行を取得する。
     *
     * 大量データを効率的に扱うため、iterable (配列または Generator) を返すことを推奨。
     * 返される配列の各要素（1行分のデータ）は、
     * ColumnDefinitionInterface->getHeaders() で返されるヘッダーの
     * 順序に対応した「値の配列」であること。
     *
     * @param ColumnDefinitionInterface $column_definition カラム定義 (ヘッダー順序等の参照用)
     * @param LearningtasksPosts $post 課題投稿コンテキスト
     * @param Page $page ページコンテキスト
     * @param string $site_url サイトURL (ファイルURL等生成用)
     * @return iterable<array<int, string|null>> データ行の iterable (各行は値の配列)
     */
    public function getRows(
        ColumnDefinitionInterface $column_definition,
        LearningtasksPosts $post,
        Page $page,
        string $site_url
    ): iterable;
}
