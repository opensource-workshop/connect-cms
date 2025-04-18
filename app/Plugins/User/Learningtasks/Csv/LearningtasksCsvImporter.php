<?php

namespace App\Plugins\User\Learningtasks\Csv;

use App\Enums\CsvCharacterCode;
use App\Models\Common\Page;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Plugins\User\Learningtasks\Contracts\ColumnDefinitionInterface;
use App\Plugins\User\Learningtasks\Contracts\RowProcessorExceptionHandlerInterface;
use App\Plugins\User\Learningtasks\Contracts\RowProcessorInterface;
use App\Plugins\User\Learningtasks\Exceptions\AlreadyEvaluatedException;
use App\Plugins\User\Learningtasks\Exceptions\CsvFileOpenException;
use App\Plugins\User\Learningtasks\Exceptions\CsvHeaderReadException;
use App\Plugins\User\Learningtasks\Exceptions\CsvInvalidHeaderException;
use App\Plugins\User\Learningtasks\Exceptions\InvalidStudentException;
use App\Plugins\User\Learningtasks\Exceptions\SubmissionNotFoundException;
use App\Plugins\User\Learningtasks\Repositories\LearningtaskUserRepository;
use App\User;
use App\Utilities\Csv\CsvUtils;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * ★ 課題管理の汎用CSVインポートクラス
 *
 * ColumnDefinitionInterface と RowProcessorInterface を利用して、
 * 様々な種類のCSVインポート処理をオーケストレーションする。
 */
class LearningtasksCsvImporter
{
    // --- 依存性 ---
    private LearningtasksPosts $learningtask_post;
    private Page $page;
    private ColumnDefinitionInterface $column_definition;
    private RowProcessorInterface $row_processor;
    private LearningtaskUserRepository $user_repository;
    private RowProcessorExceptionHandlerInterface $exception_handler;

    /** インポート結果の集計 */
    private array $results = [
        'success' => 0,
        'errors' => 0,
        'skipped' => 0,
        'error_details' => [],
        'skip_details' => [],
    ];

    /**
     * コンストラクタ
     *
     * @param LearningtasksPosts $learningtask_post
     * @param Page $page
     * @param ColumnDefinitionInterface $column_definition インターフェースの実装を受け取る
     * @param RowProcessorInterface $row_processor インターフェースの実装を受け取る
     * @param LearningtaskUserRepository $user_repository
     * @param RowProcessorExceptionHandlerInterface $exception_handler インターフェースの実装を受け取る
     */
    public function __construct(
        LearningtasksPosts $learningtask_post,
        Page $page,
        ColumnDefinitionInterface $column_definition,
        RowProcessorInterface $row_processor,
        LearningtaskUserRepository $user_repository,
        RowProcessorExceptionHandlerInterface $exception_handler
    ) {
        $this->learningtask_post = $learningtask_post;
        $this->page = $page;
        $this->column_definition = $column_definition;
        $this->row_processor = $row_processor;
        $this->user_repository = $user_repository;
        $this->exception_handler = $exception_handler;
    }

    /**
     * CSVインポート処理を実行する (メインメソッド)
     *
     * @param UploadedFile $file アップロードされたCSVファイル
     * @param string $character_code 文字コード
     * @param User $importer インポート操作を行うユーザー
     * @return array インポート結果の集計配列
     */
    public function import(UploadedFile $file, User $importer): array
    {
        // 結果を初期化
        $this->results = ['success' => 0, 'errors' => 0, 'skipped' => 0, 'error_details' => [], 'skip_details' => []];
        // 注入された $column_definition からヘッダーを取得
        $expected_headers = $this->column_definition->getHeaders();
        $file_path = $file->getPathname();
        $fp = null; // ファイルポインタ

        try {
            DB::transaction(function () use ($file_path, $expected_headers, $importer, &$fp) {
                $fp = $this->openCsvFile($file_path);
                $header_index_map = $this->processHeader($fp, $expected_headers);
                // データ行を処理 (結果は $this->results に蓄積される)
                $this->processRows($fp, $header_index_map, $expected_headers, $importer);
                if ($this->results['errors'] > 0) {
                    throw new Exception("インポート処理中にエラーが発生したため、データベースへの変更は適用されませんでした。");
                }
            });
        } catch (CsvFileOpenException $e) {
            Log::error("CSV Import failed: " . $e->getMessage());
            $this->results['errors']++;
            $this->addErrorDetail(0, 'ファイルオープンエラー: ' . $e->getMessage(), 'file_open_error');
        } catch (CsvHeaderReadException $e) {
            Log::error("CSV Import failed: " . $e->getMessage());
            $this->results['errors']++;
            $this->addErrorDetail(1, 'ヘッダー読み取りエラー: ' . $e->getMessage(), 'header_read_error');
        } catch (CsvInvalidHeaderException $e) {
            Log::error("CSV Import failed: " . $e->getMessage());
            $this->results['errors']++;
            $this->addErrorDetail(1, 'ヘッダー形式エラー: ' . $e->getMessage(), 'header_error');
        } catch (Exception $e) { // DBエラーやロールバックトリガーなど、その他の例外
            Log::error("CSV Import failed and potentially rolled back: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            if ($this->results['errors'] == 0 && $this->results['skipped'] == 0) {
                // processRows でエラーが記録されずにここにきた場合 (DB接続エラーなど)
                 $this->results['errors']++;
                 $this->addErrorDetail(0, '予期せぬエラーにより処理が中断されました。変更は適用されていません。: ' . $e->getMessage(), 'fatal_error');
            } else {
                 // processRows でエラーがありロールバックした場合
                 $this->addErrorDetail(0, 'エラー発生のため処理が中断され、変更は適用されませんでした。詳細は各行のエラー/スキップを確認してください。', 'fatal_error_rollback');
            }
        } finally {
            // ファイルを確実にクローズ
            if ($fp !== null && is_resource($fp)) {
                fclose($fp);
            }
        }

        return $this->results; // 集計結果を返す
    }

    // ===============================================
    // Private Helper Methods for import()
    // ===============================================

    /**
     * CSVファイルを開き、文字コード設定を行う
     *
     * @param string $file_path ファイルパス
     * @param string $character_code 文字コード
     * @return resource|false ファイルポインタ (PHP 8.0未満では resource|false)
     * @throws Exception ファイルオープン失敗時
     */
    private function openCsvFile(string $file_path): mixed
    {
        // 文字コード自動判定
        $detected_code = CsvUtils::getCharacterCodeAuto($file_path);

        // 判定失敗時の処理
        if ($detected_code === false) {
            // ユーザーに分かりやすいエラーメッセージを投げる
            throw new Exception("文字コードを自動判定できませんでした。ファイル形式がUTF-8またはShift-JIS（Windows）であることを確認してください。");
        }

        // --- ファイルオープンとフィルタ適用 ---
        CsvUtils::setLocale(); // fgetcsv のためのロケール設定
        $fp = @fopen($file_path, 'r'); // エラー制御演算子を使用
        if ($fp === false) {
            throw new CsvFileOpenException("CSVファイルを開けませんでした: " . $file_path);
        }

        // 判定結果に基づいてストリームフィルタを適用
        if ($detected_code === CsvCharacterCode::sjis_win) {
            // Shift-JIS なら UTF-8 変換フィルタを適用
            $fp = CsvUtils::setStreamFilterRegisterSjisToUtf8($fp);
        }
        // UTF-8 の場合は BOM の有無に関わらずフィルタは不要 (BOMは processHeader で除去)

        return $fp;
    }

    /**
     * CSVヘッダー行を読み込み、検証し、ヘッダーインデックスマップを返す
     *
     * @param resource $fp ファイルポインタ
     * @param array $expected_headers 期待されるヘッダーの配列
     * @return array ヘッダー名 => 列インデックス のマップ
     * @throws Exception ヘッダー読み込み失敗または検証失敗時
     */
    private function processHeader($fp, array $expected_headers): array
    {
        $actual_header_raw = fgetcsv($fp, 0, ',');
        if ($actual_header_raw === false || $actual_header_raw === null) {
            throw new CsvHeaderReadException("CSVヘッダー行の読み取りに失敗しました。");
        }

        $actual_header = CsvUtils::removeUtf8Bom($actual_header_raw);

        if (!$this->validateHeader($actual_header, $expected_headers)) {
            throw new CsvInvalidHeaderException('CSVヘッダーが不正です。期待される形式と異なります。');
        }

        // ヘッダー名から列インデックスへのマップを作成して返す
        return array_flip($actual_header);
    }

    /**
     * CSVデータ行をループ処理し、検証とデータ処理を行う
     *
     * @param resource $fp ファイルポインタ
     * @param array $header_index_map ヘッダー名 => 列インデックス のマップ
     * @param array $expected_headers 期待されるヘッダーの配列
     * @param User $importer インポート実行ユーザー
     * @return void (結果は $this->results プロパティに格納)
     */
    private function processRows($fp, array $header_index_map, array $expected_headers, User $importer): void
    {
        $line_number = 2; // データは2行目から
        while (($csv_columns = fgetcsv($fp, 0, ',')) !== false) {
            // 空行の可能性をチェックしてスキップ
            if (count($csv_columns) === 1 && ($csv_columns[0] === null || $csv_columns[0] === '')) {
                continue;
            }

            try {
                $validated_data = $this->validateRow($csv_columns, $header_index_map, $line_number, $expected_headers);
                $this->row_processor->process($validated_data, $this->learningtask_post, $this->page, $importer);
                $this->results['success']++;
            } catch (Throwable $e) {
                $this->handleRowProcessingException($e, $csv_columns, $header_index_map, $line_number);
            }
            $line_number++;
        } // end while
    }

    /**
     * エラー詳細情報を結果配列に追加するヘルパーメソッド
     *
     * @param int $line_number 行番号
     * @param string $message エラーメッセージ
     * @param string $type エラー種別
     * @param string|null $user_id 関連ユーザーID (取得できれば)
     * @return void
     */
    private function addErrorDetail(int $line_number, string $message, string $type, ?string $user_id = 'N/A'): void
    {
        $this->results['error_details'][] = [
            'line' => $line_number,
            'userid' => $user_id ?? 'N/A',
            'message' => $message,
            'type' => $type,
        ];
    }

    /**
     * スキップ詳細情報を結果配列に追加するヘルパーメソッド
     *
     * @param int $line_number 行番号
     * @param string $message スキップメッセージ
     * @param string $type スキップ種別
     * @param string|null $user_id 関連ユーザーID (取得できれば)
     * @return void
     */
    private function addSkipDetail(int $line_number, string $message, string $type, ?string $user_id = 'N/A'): void
    {
        $this->results['skip_details'][] = [
            'line' => $line_number,
            'userid' => $user_id ?? 'N/A',
            'message' => $message,
            'type' => $type,
        ];
    }

    /**
     * 行処理中の例外をハンドリングするヘルパーメソッド
     *
     * @param Throwable $e 捕捉した例外/エラー
     * @param array $csv_columns エラーが発生した行の元データ
     * @param array $header_index_map ヘッダーインデックスマップ
     * @param int $line_number エラーが発生した行番号
     * @return void
     */
    private function handleRowProcessingException(Throwable $e, array $csv_columns, array $header_index_map, int $line_number): void
    {
        $user_id = $this->getUserIdFromRow($csv_columns, $header_index_map);
        $message = $e->getMessage();

        // 注入されたハンドラに例外処理を委譲
        $handling_config = $this->exception_handler->handle($e);

        if ($handling_config) {
            // --- ハンドラが処理方法を決定できた場合 ---
            $outcome = $handling_config[RowProcessorExceptionHandlerInterface::KEY_OUTCOME]; // 'error' or 'skip'
            $type = $handling_config[RowProcessorExceptionHandlerInterface::KEY_TYPE];
            $log_level = $handling_config[RowProcessorExceptionHandlerInterface::KEY_LOG_LEVEL];

            // ValidationException の場合はメッセージを整形する
            if ($e instanceof ValidationException) {
                $message = $this->exception_handler->formatValidationMessage($e);
            }

            if ($outcome === RowProcessorExceptionHandlerInterface::OUTCOME_SKIP) {
                $this->results['skipped']++;
                $this->addSkipDetail($line_number, $message, $type, $user_id);
                Log::$log_level("CSV Import Skipped (Line: {$line_number}): Type={$type} - " . $message);
            } else { // outcome === 'error'
                $this->results['errors']++;
                $this->addErrorDetail($line_number, $message, $type, $user_id);
                Log::$log_level("CSV Import Error (Line: {$line_number}): Type={$type} - " . $message);
            }

        } else {
            // --- ハンドラが処理できなかった予期せぬ例外 ---
            $this->results['errors']++;
            $this->addErrorDetail($line_number, $message, 'unexpected_error', $user_id);
            Log::error("CSV Import Unexpected Error (Line: {$line_number}): " . $message . "\n" . $e->getTraceAsString());
        }
    }

    // ===============================================
    // Core Validation/Permission Methods
    // ===============================================

    /**
     * CSVヘッダー行を検証する
     * (期待されるヘッダーが全て含まれているかを確認する)
     *
     * @param array $actual_header CSVファイルから読み込んだヘッダー
     * @param array $expected_headers 動的に決定された期待されるヘ Dダー
     * @return bool 検証OKなら true
     */
    protected function validateHeader(array $actual_header, array $expected_headers): bool
    {
        // 期待されるヘッダーが実際のヘッダーに全て含まれているか (順不同)
        return count(array_diff($expected_headers, $actual_header)) === 0;
    }

    /**
     * CSVデータ1行分を検証する (ColumnDefinition からルール/メッセージを取得)
     *
     * @param array $csv_columns fgetcsv からの数値インデックス配列
     * @param array $header_index_map ヘッダー名 => 列インデックス のマップ
     * @param int $line_number 現在の行番号
     * @param array $expected_headers この課題で期待されるヘッダーリスト
     * @return array 検証済みデータ (内部キー名 => 値)
     * @throws ValidationException バリデーション失敗時
     */
    protected function validateRow(array $csv_columns, array $header_index_map, int $line_number, array $expected_headers): array
    {
        $mapped_data = [];
        $rules = $this->column_definition->getValidationRulesBase();
        // メッセージを $column_definition から取得 (存在すれば)
        $messages = method_exists($this->column_definition, 'getValidationMessages')
                    ? $this->column_definition->getValidationMessages()
                    : []; // 存在しない or 不要な場合は空配列

        // カラムマップも $column_definition から取得
        $column_map = $this->column_definition->getColumnMap();

        // 期待ヘッダーに基づいてデータをマップし、適用するルールを決定
        $active_rules = []; // この行で実際に適用するルール
        foreach ($expected_headers as $header_name) {
            $internal_key = $column_map[$header_name] ?? null;
            if ($internal_key === null) {
                // ヘッダー名がマップに存在しない場合はスキップ
                continue;
            }
            // 値を取得
            $column_index = $header_index_map[$header_name] ?? null;
            $value = ($column_index !== null && array_key_exists($column_index, $csv_columns))
                    ? $csv_columns[$column_index]
                    : null;
            $mapped_data[$internal_key] = $value;

            // この内部キーに対応するルールが存在すれば、適用対象に追加
            if (array_key_exists($internal_key, $rules)) {
                $active_rules[$internal_key] = $rules[$internal_key];
            }
        }

        // 実際に適用するルール ($active_rules) で Validator を作成
        $validator = Validator::make($mapped_data, $active_rules, $messages);
        $validator->validate(); // 失敗時は ValidationException

        return $validator->validated(); // 内部キー名 => 値 の配列
    }

    /**
     * エラー報告用に、数値インデックスの行データからユーザーIDを取得する試み
     *
     * @param array $csv_columns fgetcsv からの数値インデックス配列
     * @param array $header_index_map ヘッダー名 => 列インデックス のマップ
     * @return string ユーザーID または 'N/A'
     */
    private function getUserIdFromRow(array $csv_columns, array $header_index_map): string
    {
        $login_id_header = 'ログインID'; // ログインIDのヘッダー名
        if (isset($header_index_map[$login_id_header])) {
            $index = $header_index_map[$login_id_header];
            if (isset($csv_columns[$index])) {
                return (string) $csv_columns[$index]; // 文字列として返す
            }
        }
        return 'N/A'; // 見つからない場合
    }

    /**
     * 指定されたユーザーがインポートを実行する権限を持っているかチェックする (管理者 or 担当教員)
     *
     * @param User $user インポートを実行しようとしているユーザー
     * @return bool 許可されていれば true
     */
    public function canImport(User $user): bool
    {
        // 管理者権限チェック
        if ($user->can('role_article_admin')) {
            return true;
        }

        // 担当教員かチェック
        $teachers = $this->user_repository->getTeachers($this->learningtask_post, $this->page);
        // コレクション内にユーザーIDが存在するかどうかで判定
        if ($teachers->contains('id', $user->id)) {
            return true;
        }

        // どちらでもなければ拒否
        return false;
    }

}
