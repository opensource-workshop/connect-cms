<?php

namespace App\Plugins\User\Learningtasks\Csv;

use App\Enums\CsvCharacterCode;
use App\Models\Common\Page;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\User;
use App\Plugins\User\Learningtasks\Contracts\ColumnDefinitionInterface;
use App\Plugins\User\Learningtasks\Contracts\CsvDataProviderInterface;
use App\Plugins\User\Learningtasks\Repositories\LearningtaskUserRepository;
use App\Utilities\Csv\CsvUtils;
use App\Utilities\File\FileUtils;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 汎用的なCSVエクスポート処理を行うクラス
 *
 * データ取得は CsvDataProviderInterface に、
 * カラム定義は ColumnDefinitionInterface に委譲する。
 */
class LearningtasksCsvExporter
{
    // --- 依存性 ---
    private LearningtasksPosts $learningtask_post;
    private Page $page;
    private ColumnDefinitionInterface $column_definition;
    private CsvDataProviderInterface $data_provider;
    private LearningtaskUserRepository $user_repository;

    /**
     * コンストラクタ
     *
     * @param LearningtasksPosts $learningtask_post
     * @param Page $page
     * @param ColumnDefinitionInterface $column_definition
     * @param CsvDataProviderInterface $data_provider
     * @param LearningtaskUserRepository $user_repository
     */
    public function __construct(
        LearningtasksPosts $learningtask_post,
        Page $page,
        ColumnDefinitionInterface $column_definition,
        CsvDataProviderInterface $data_provider,
        LearningtaskUserRepository $user_repository
    ) {
        $this->learningtask_post = $learningtask_post;
        $this->page = $page;
        $this->column_definition = $column_definition;
        $this->data_provider = $data_provider;
        $this->user_repository = $user_repository;
    }

    /**
     * CSVエクスポートを実行し、HTTPレスポンスを返す
     */
    public function export(string $site_url, string $character_code): StreamedResponse
    {
        // 1. ヘッダー行を取得
        $header_row = $this->column_definition->getHeaders();

        // 2. ファイル名生成
        $filename = FileUtils::toValidFilename($this->learningtask_post->post_title . '_Export.csv');

        // 3. レスポンスヘッダー (streamDownload が Content-Disposition を主に設定)
        //    Content-Type は必要に応じて明示的に指定する
        $headers = [
            'Content-Type' => 'text/csv; charset='. $character_code,
        ];

        // 4. ストリーミング処理のコールバックを定義
        $callback = function () use ($site_url, $character_code, $header_row) {
            // 出力ストリーム 'php://output' を書き込みモードで開く
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                Log::error("CSV Export Streaming: Failed to open php://output.");
                // ここで処理を中断する（例外を投げるなど）
                return;
            }

            // ロケール設定 (fputcsv の挙動のため念のため)
            CsvUtils::setLocale();

            // 文字コードに応じた処理: BOM 追加 (UTF-8の場合)
            if ($character_code === CsvCharacterCode::utf_8) {
                // Excel での互換性のため UTF-8 BOM を先頭に書き込む
                fwrite($handle, CsvUtils::bom);
            }

            // 文字コードに応じた処理: ヘッダー行のエンコーディング変換
            if ($character_code === CsvCharacterCode::sjis_win) {
                // ヘッダー行を Shift-JIS に変換
                $header_row = array_map(
                    fn($value) => mb_convert_encoding((string)$value, CsvCharacterCode::sjis_win, 'UTF-8'),
                    $header_row
                );
            }
            // ヘッダー行を CSV として書き込み
            fputcsv($handle, $header_row);
            // データ行を取得 (DataProvider から iterable で)
            $data_rows_iterable = $this->data_provider->getRows(
                $this->column_definition,
                $this->learningtask_post,
                $this->page,
                $site_url
            );

            // データ行を一件ずつ処理して出力ストリームに書き込み
            foreach ($data_rows_iterable as $row_array) {
                // Shift-JIS で出力する場合の変換
                if ($character_code === CsvCharacterCode::sjis_win) {
                    $row_array = array_map(fn($value) => mb_convert_encoding((string)$value, 'SJIS-win', 'UTF-8'), $row_array);
                }

                // RFC4180 準拠: fputcsv は基本的なダブルクォートのエスケープは行うが、
                // 改行コード等の扱いでより厳密な処理が必要な場合は、自前でエスケープ処理を追加検討。
                // (CsvUtils::getResponseCsvData にあった str_replace('"', '""', ...) の処理は fputcsv が行う)
                fputcsv($handle, $row_array);
            }
            // php://output は fclose 不要
        };

        // 5. ストリーミングダウンロードレスポンスを生成して返す
        return response()->streamDownload($callback, $filename, $headers);
    }

    /**
     * ユーザーがCSVエクスポート可能か判定
     *
     * @param User $user
     * @return bool
     */
    public function canExport(User $user): bool
    {
        if ($user->can('role_article_admin')) {
            return true;
        }
        $teachers = $this->user_repository->getTeachers($this->learningtask_post, $this->page);
        if ($teachers->contains('id', $user->id)) {
            return true;
        }
        return false;
    }
}
