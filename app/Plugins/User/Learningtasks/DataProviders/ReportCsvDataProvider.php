<?php

namespace App\Plugins\User\Learningtasks\DataProviders;

use App\Models\Common\Page;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Models\User\Learningtasks\LearningtasksUsersStatuses;
use App\User;
use App\Plugins\User\Learningtasks\Contracts\ColumnDefinitionInterface;
use App\Plugins\User\Learningtasks\Contracts\CsvDataProviderInterface;
use App\Plugins\User\Learningtasks\Repositories\LearningtaskUserRepository;
use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * レポート課題のCSVエクスポート用データを提供するクラス
 * CsvDataProviderInterface を実装する。
 */
class ReportCsvDataProvider implements CsvDataProviderInterface
{
    /**
     * ユーザーリポジトリ
     * @var LearningtaskUserRepository
     */
    private LearningtaskUserRepository $user_repository;

    /**
     * コンストラクタ
     * @param LearningtaskUserRepository $user_repository
     */
    public function __construct(LearningtaskUserRepository $user_repository)
    {
        $this->user_repository = $user_repository;
    }

    /**
     * レポート課題のCSVデータ行を生成して yield する
     * (CsvDataProviderInterface の実装)
     *
     * @param ColumnDefinitionInterface $column_definition カラム定義
     * @param LearningtasksPosts $post 課題投稿コンテキスト
     * @param Page $page ページコンテキスト
     * @param string $site_url サイトURL
     * @return Generator<int, array<int, string|null>> Generator オブジェクトを返す
     */
    public function getRows(
        ColumnDefinitionInterface $column_definition,
        LearningtasksPosts $post,
        Page $page,
        string $site_url
    ): Generator
    {
        // 1. ヘッダーを取得 (順序の参照用に内部で使う)
        $header_columns = $column_definition->getHeaders();
        if (empty($header_columns)) {
            // ヘッダーがなければ何も yield しない
            return;
        }

        // 2. 対象学生を取得
        $students = $this->user_repository->getStudents($post, $page);
        if ($students->isEmpty()){
            // 対象がいなければ何も yield しない
            return;
        }

        // 3. 関連ステータスを一括取得・グループ化
        $statuses_by_user = $this->fetchAllStatusesGroupedByUser($students, $post);

        // 4. 学生ごとにループして行データを yield
        foreach ($students as $student) {
            $student_statuses = $statuses_by_user->get($student->id, collect());
            $submission_eval_pair = $this->findLastSubmissionAndEvaluation($student_statuses);
            $submission_count = $student_statuses->where('task_status', 1)->count();

            // 一行分のデータを生成 (ヘルパーメソッド利用)
            $row_values = $this->generateRowForStudent(
                $student,
                $submission_eval_pair['last_submission'],
                $submission_eval_pair['last_evaluation'],
                $submission_count,
                $header_columns, // 要求ヘッダーリスト
                $site_url
            );
            // 配列に追加する代わりに yield で返す
            yield $row_values;
        }
    }

    /**
     * 対象学生全員の関連ステータス（提出・評価）を一括取得し、ユーザーIDでグループ化する
     *
     * @param Collection $students 対象学生のコレクション
     * @param LearningtasksPosts $post 課題投稿コンテキスト
     * @return Collection グループ化されたステータスのコレクション
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    private function fetchAllStatusesGroupedByUser(Collection $students, LearningtasksPosts $post): Collection
    {
        return LearningtasksUsersStatuses::where('post_id', $post->id)
            ->whereIn('task_status', [1, 2])
            ->whereIn('user_id', $students->pluck('id'))
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('user_id');
    }

    /**
     * 学生一人のステータスコレクションから、最新の提出と対応する評価を見つける
     *
     * @param Collection $student_statuses 学生のステータスコレクション
     * @return array 最新の提出と評価を含む配列
     */
    private function findLastSubmissionAndEvaluation(Collection $student_statuses): array
    {
        $student_submissions = $student_statuses->where('task_status', 1);
        $student_evaluations = $student_statuses->where('task_status', 2);
        $last_submission = $student_submissions->first();
        $last_evaluation = null;
        if ($last_submission && $student_submissions->count() === $student_evaluations->count()) {
             $last_evaluation = $student_evaluations->first();
        }
        return ['last_submission' => $last_submission, 'last_evaluation' => $last_evaluation];
    }

     /**
      * 学生一人分のCSV行データを生成 (データ生成マップ利用)
      */
     private function generateRowForStudent(
        User $student,
        ?LearningtasksUsersStatuses $last_submission,
        ?LearningtasksUsersStatuses $last_evaluation,
        int $submit_count,
        array $required_headers,
        string $site_url
     ): array {
        $row_values = [];
        $data_generators = $this->getColumnDataGenerators(
            $student, $last_submission, $last_evaluation, $submit_count, $site_url
        );
        foreach ($required_headers as $header) {
            if (isset($data_generators[$header])) {
                $row_values[] = $data_generators[$header]();
            } else {
                Log::warning("ReportCsvDataProvider: Unknown header '{$header}' requested.");
                $row_values[] = null;
            }
        }
        return $row_values; // 値のみの配列
     }

    /**
     * 各カラムのデータ生成ロジック（クロージャ）を連想配列で返すヘルパー
     */
    private function getColumnDataGenerators(
        User $student,
        ?LearningtasksUsersStatuses $last_submission,
        ?LearningtasksUsersStatuses $last_evaluation,
        int $submit_count,
        string $site_url
    ): array {
        return [
            'ログインID' => fn() => $student->userid,
            'ユーザ名' => fn() => $student->name,
            '提出日時' => fn() => optional($last_submission)->created_at,
            '提出回数' => fn() => $submit_count,
            '本文' => fn() => optional($last_submission)->comment,
            'ファイルURL' => function() use ($last_submission, $site_url) {
                $upload_id = optional($last_submission)->upload_id;
                return $upload_id ? $site_url . '/file/' . $upload_id : null;
            },
            '評価' => fn() => optional($last_evaluation)->grade,
            '評価コメント' => fn() => optional($last_evaluation)->comment,
        ];
    }
}
