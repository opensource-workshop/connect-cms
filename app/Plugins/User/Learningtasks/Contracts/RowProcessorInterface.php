<?php

namespace App\Plugins\User\Learningtasks\Contracts;

use App\Models\Common\Page;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\User;
use Exception; // エラー発生時に基底例外を投げることを想定

/**
 * インポート時に検証済みのCSVデータ1行分を処理するためのインターフェース
 */
interface RowProcessorInterface
{
    /**
     * 検証済みのデータ1行分を処理する。
     *
     * このメソッドの実装クラスが、CSVの1行に対応する
     * データベースレコードの検索、更新、または作成といった
     * コアなビジネスロジックを担当します。
     *
     * @param array $validated_data 検証済みデータ（内部キー名 => 値 の連想配列）
     * 例: ['userid' => '123', 'grade' => 'A', 'comment' => 'Good']
     * @param LearningtasksPosts $post インポート対象の課題投稿コンテキスト
     * @param User $importer インポート操作を実行しているユーザー (評価者ID等に利用)
     * @return void
     * @throws Exception 処理中にエラーが発生した場合 (例: 関連データが見つからない、DBエラー)。
     * より具体的なカスタム例外を定義して投げるのが望ましい。
     */
    public function process(array $validated_data, LearningtasksPosts $post, Page $page, User $importer): void;
}
