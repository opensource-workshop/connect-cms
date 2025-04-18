<?php

namespace App\Plugins\User\Learningtasks\Contracts;

/**
 * 課題管理CSVインポートにおけるカラム定義を提供するクラスのためのインターフェース
 *
 * 実装クラスは、特定のインポートタイプ（例：レポート評価、試験評価）に応じた
 * ヘッダーリスト、カラムマッピング、基本的なバリデーションルールを提供する責務を持つ。
 */
interface ColumnDefinitionInterface
{
    /**
     * 現在のコンテキスト（例: 課題投稿の設定など）に基づいて、
     * CSVで期待される（または処理対象となる）ヘッダーカラム名のリストを取得する。
     *
     * @return array ヘッダー文字列の配列。例: ['ログインID', '評価', '評価コメント']
     */
    public function getHeaders(): array;

    /**
     * この定義クラスが扱う可能性のある全てのCSVヘッダー名と、
     * それに対応する内部キー名の完全なマッピング配列を取得する。
     * Importer が列インデックスと内部キーを紐付けるために利用する。
     *
     * @return array ['CSVヘッダー名' => '内部キー名', ...]
     */
    public function getColumnMap(): array;

    /**
     * 指定されたCSVヘッダー名に対応する内部キー名を取得する。
     * マッピングが存在しない場合は null を返す。
     *
     * @param string $header_name CSVヘッダー名。例: '評価コメント'
     * @return string|null 内部キー名。例: 'comment'。見つからない場合は null。
     */
    public function getInternalKey(string $header_name): ?string;

    /**
     * このカラム定義（インポートタイプ）に対応する基本的なバリデーションルールを取得する。
     * ルールは内部キー名に対して定義された連想配列として返す。
     * Importer はこの基本ルールを利用してバリデーションを行う。
     *
     * @return array ['内部キー名' => ['ルール1', 'ルール2', ...], ...]
     * 例: ['userid' => ['required', 'exists:users,userid'], 'grade' => ['nullable']]
     */
    public function getValidationRulesBase(): array;

    /**
     * (任意) このカラム定義に対応するカスタムバリデーションメッセージを取得する。
     * メッセージは '内部キー名.ルール名' をキーとする連想配列で返す。
     * 実装クラスで不要な場合は空配列を返せば良い。
     *
     * @return array ['内部キー名.ルール名' => 'メッセージ文字列', ...]
     * 例: ['userid.required' => 'ログインIDは必須です。']
     */
    public function getValidationMessages(): array;
}
