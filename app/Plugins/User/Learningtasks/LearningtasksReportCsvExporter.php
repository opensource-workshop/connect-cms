<?php

namespace App\Plugins\User\Learningtasks;

use App\Enums\LearningtaskUseFunction;
use App\Models\Common\Page;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Models\User\Learningtasks\LearningtasksUsersStatuses;
use App\Traits\Learningtasks\LearningtaskPostTrait;
use App\User;
use App\Utilities\Csv\CsvUtils;
use App\Utilities\File\FileUtils;

/**
 * 課題管理のレポート機能におけるCSVエクスポーター
 */
class LearningtasksReportCsvExporter
{
    use LearningtaskPostTrait;

    public function __construct(int $learningtask_post_id, int $page_id)
    {
        $this->learningtask_post = LearningtasksPosts::findOrFail($learningtask_post_id);
        $this->page = Page::findOrFail($page_id);
    }

    /**
     * CSVエクスポート
     * @param string $site_url サイトURL
     * @param string $character_code 文字コード
     * @return \Illuminate\Http\Response CSVレスポンス
     */
    public function export($site_url, $character_code): \Illuminate\Http\Response
    {
        $header = $this->getHeaderColumns();
        $rows = $this->getRows($site_url);
        $csv_data = array_merge([$header], $rows);

        $filename = FileUtils::toValidFilename($this->learningtask_post->post_title . '_レポート.csv');
        $response_headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        // データ
        $csv_data = CsvUtils::getResponseCsvData($csv_data, $character_code);

        return response()->make($csv_data, 200, $response_headers);
    }

    /**
     * ヘッダーの項目を取得する
     * @return array ヘッダーの項目
     */
    public function getHeaderColumns(): array
    {
        $header_columns = ['ログインID', 'ユーザ名', '提出日時'];

        // 本文
        if ($this->isSettingEnabled(LearningtaskUseFunction::use_report_comment)) {
            $header_columns[] = '本文';
        }

        // 提出ファイルのURL
        if ($this->isSettingEnabled(LearningtaskUseFunction::use_report_file)) {
            $header_columns[] = 'ファイルURL';
        }

        // 評価
        if ($this->isSettingEnabled(LearningtaskUseFunction::use_report_evaluate)) {
            $header_columns[] = '評価';
        }

        // 評価コメント
        if ($this->isSettingEnabled(LearningtaskUseFunction::use_report_evaluate_comment)) {
            $header_columns[] = '評価コメント';
        }

        return $header_columns;
    }

    /**
     * 行データを取得する
     * @param string $site_url サイトURL
     * @return array 行データ
     */
    public function getRows(string $site_url): array
    {
        $rows = [];

        $students = $this->fetchStudentUsers();
        $submits = LearningtasksUsersStatuses::where('post_id', $this->learningtask_post->id)
            ->where('task_status', 1) // 提出
            ->get();
        $evaluations = LearningtasksUsersStatuses::where('post_id', $this->learningtask_post->id)
            ->where('task_status', 2) // 評価
            ->get();

        foreach ($students as $student) {
            $row = [
                'ログインID' => $student->userid,
                'ユーザ名' => $student->name,
            ];

            $student_submits = $submits->where('user_id', $student->id)->sortByDesc('id');
            $student_evaluations = $evaluations->where('user_id', $student->id)->sortByDesc('id');

            // 最後の提出と評価の組み合わせを出力する
            $last_submit = $student_submits->first();
            // 提出と評価で数が合わないということは、最後の提出の評価がまだされていないということ
            $last_evaluation = null;
            if ($student_submits->count() === $student_evaluations->count()) {
                $last_evaluation = $student_evaluations->first();
            }
            $row['提出日時'] = optional($last_submit)->created_at;

            if ($this->isSettingEnabled(LearningtaskUseFunction::use_report_comment)) {
                $row['本文'] = optional($last_submit)->comment;
            }

            if ($this->isSettingEnabled(LearningtaskUseFunction::use_report_file)) {
                $row['ファイルURL'] = optional($last_submit)->upload_id ? $site_url . '/file/' . optional($last_submit)->upload_id : null;
            }

            if ($this->isSettingEnabled(LearningtaskUseFunction::use_report_evaluate)) {
                $row['評価'] = optional($last_evaluation)->grade;
            }

            if ($this->isSettingEnabled(LearningtaskUseFunction::use_report_evaluate_comment)) {
                $row['評価コメント'] = optional($last_evaluation)->comment;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * ユーザーがCSVエクスポート可能かどうかを判定する
     * @param User $user ユーザ
     * @return bool エクスポート可能な場合はtrue、そうでない場合はfalse
     */
    public function canExport(User $user): bool
    {
        // 管理者であればエクスポート可能
        if ($user->can('role_article_admin')) {
            return true;
        }

        // 教員であればエクスポート可能
        if ($this->fetchTeacherUsers()->contains($user)) {
            return true;
        }

        return false;
    }
}
