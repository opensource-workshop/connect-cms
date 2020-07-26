<?php

namespace App\Plugins\User\Learningtasks;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Enums\DayOfWeek;
use App\Models\User\Learningtasks\LearningtasksUsersStatuses;

/**
 * 課題管理のユーザ情報保持クラス
 *
 * メソッド一覧(public のもの)
 * ・レポートの履歴取得                       getReportStatuses($post_id)
 * ・レポートの状況取得                       getReportStatus($post_id)
 * ・レポートの提出を行えるか？               canReportUpload($post_id)
 * ・試験の履歴取得                           getExaminationStatuses($post_id)
 * ・試験の状況取得                           getExaminationStatuse($post_id)
 * ・試験の申込を行えるか？判定のみ           canExamination($post_id)
 * ・試験の申込を行えるか？理由のみ           reasonExamination($post_id)
 * ・試験日の画面表記を取得                   getViewDate($obj)
 * ・試験時間内が判定                         isNowExamination($post_id)
 * ・申し込み中の試験があり、時間前であること isApplyingExamination($post_id)
 * ・申し込み中の試験（日本語表記）           getApplyingExaminationDate($post_id)
 * ・申し込み中の試験                         getApplyingExamination($post_id)
 * ・開始待ちの試験                           getBeforeExamination($post_id)
 * ・試験に合格済みか                         isPassExamination($post_id)
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
 * @package Contoroller
 */
class LearningtasksUser
{
    /**
     * ユーザ情報
     */
    public $user = null;

    /**
     * レポート履歴
     */
    public $report_statuses = array();

    /**
     * 試験履歴
     */
    public $examination_statuses = array();

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        // ユーザ
        $this->user = Auth::user();

        // ユーザーstatusテーブル
        if (!empty($this->user)) {
            // レポートの履歴
            $this->report_statuses = LearningtasksUsersStatuses::where(
                'user_id', '=', $this->user->id
            )->whereIn('task_status', [1, 2, 3])
             ->orderBy('post_id', 'asc')
             ->orderBy('id', 'asc')
             ->get();

            // 試験の履歴
            $this->examination_statuses = LearningtasksUsersStatuses::select(
                'learningtasks_users_statuses.*',
                'learningtasks_examinations.start_at',
                'learningtasks_examinations.end_at'
            )->leftJoin('learningtasks_examinations', 'learningtasks_examinations.id', '=', 'learningtasks_users_statuses.examination_id')
             ->where('learningtasks_users_statuses.user_id', '=', $this->user->id)
             ->whereIn('learningtasks_users_statuses.task_status', [4, 5, 6, 7])
             ->orderBy('learningtasks_users_statuses.post_id', 'asc')
             ->orderBy('learningtasks_users_statuses.id', 'asc')
             ->get();
        }
    }

    /**
     *  レポートの履歴取得
     */
    public function getReportStatuses($post_id)
    {
        if (empty($post_id)) {
            return $this->report_statuses;
        }
        return $this->report_statuses->where('post_id', $post_id);
    }

    /**
     *  レポートの状況取得
     */
    public function getReportStatus($post_id)
    {
        if (empty($post_id)) {
            return $this->report_statuses;
        }
        $report_statuses = $this->report_statuses->where('post_id', $post_id);
        $report_status = $report_statuses->whereIn('task_status', [1, 2])->last();
        if (empty($report_status) || $report_status->count() == 0) {
            return "未提出";
        } elseif ($report_status->task_status == 1) {
            return "提出済み";
        } elseif ($report_status->task_status == 2) {
            return $report_status->grade;
        }
    }

    /**
     *  レポートの状況取得
     */
    public function canReportUpload($post_id)
    {
        list($can_ret, $not_message) = $this->canReportUploadImpl($post_id);
        return $can_ret;
    }

    /**
     *  レポートの状況の文言取得
     */
    public function getReportUploadMessage($post_id)
    {
        list($can_ret, $message) = $this->canReportUploadImpl($post_id);
        return $message;
    }

    /**
     *  レポートの状況取得
     */
    private function canReportUploadImpl($post_id)
    {
        if (empty($post_id)) {
            return array(false, 'データがありません。');
        }

        // 初めはOK。提出済みならNO、再提出があればOK。合格ならその時点でNO
        $can_report_upload = array(true, '未提出');

        $report_statuses = $this->report_statuses->where('post_id', $post_id)->whereIn('task_status', [1, 2]);
        foreach ($report_statuses as $report_status) {
            // レポートで合格のため、提出不可
            if ($report_status->task_status == 2 && ($report_status->grade == 'A' || $report_status->grade == 'B' || $report_status->grade == 'C')) {
                return array(false, 'すでに合格しているため、提出不要です。');
            }
            // 提出済みがくればfalse、D 評価がくれば再提出でtrue
            if ($report_status->task_status == 1) {
                $can_report_upload = array(false, '提出済みのため、現在は提出できません。');
            } elseif ($report_status->task_status == 2 && $report_status->grade == 'D') {
                $can_report_upload = array(true, '再提出が必要');
            }
        }
        return $can_report_upload;
    }

    /**
     *  試験の履歴取得
     */
    public function getExaminationStatuses($post_id)
    {
        if (empty($post_id)) {
            return $this->examination_statuses;
        }
        return $this->examination_statuses->where('post_id', $post_id);
    }

    /**
     *  試験の状況取得
     */
    public function getExaminationStatus($post_id)
    {
        if (empty($post_id)) {
            return $this->examination_statuses;
        }
        $examination_statuses = $this->examination_statuses->where('post_id', $post_id);
        $examination_status = $examination_statuses->whereIn('task_status', [5, 6])->last();
        if (empty($examination_status) || $examination_status->count() == 0) {
            return "未受験";
        } elseif ($examination_status->task_status == 5) {
            return "評価待ち";
        } elseif ($examination_status->task_status == 6) {
            return $examination_status->grade;
        }
    }

    /**
     *  試験の申込を行えるか？判定のみ
     */
    public function canExamination($post_id)
    {
        if (empty($post_id)) {
            return false;
        }

        list($can_examination, $reason) = $this->canExaminationImpl($post_id);
        return $can_examination;
    }

    /**
     *  試験の申込を行えるか？理由のみ
     */
    public function reasonExamination($post_id)
    {
        if (empty($post_id)) {
            return false;
        }

        list($can_examination, $reason) = $this->canExaminationImpl($post_id);
        return $reason;
    }

    /**
     *  試験の申込を行えるか？
     */
    private function canExaminationImpl($post_id)
    {
        if (empty($post_id)) {
            return false;
        }

        // post_id で絞る。
        $report_statuses = $this->report_statuses->where('post_id', $post_id);

        if (empty($report_statuses) || $report_statuses->count() == 0) {
            return array(false, '');
        }

        // 以下の条件を満たせば、試験に申込できる。
        // ・レポートに合格していること。（判定が A, B, C のどれか）
        // レポートは一度合格すれば、再提出できない想定のため、順番は意識しない。
        $ok_report = $report_statuses->whereInStrict('grade', ['A', 'B', 'C'])->first();
        if (empty($ok_report)) {
            return array(false, 'レポートに合格していません。');
        }

        // 申し込み中の試験がある
        if (!empty($this->getApplyingExamination($post_id))) {
            return array(false, '申し込み済み試験があります。');
        }

        // すでに試験に合格している


        // 試験の申込OK
        return array(true, '');
    }

    /**
     * 試験日の画面表記を取得
     */
    public function getViewDate($obj)
    {
        if (empty($obj)) {
            return "";
        }

        // 判定に必要な値の準備
        $start_ts      = strtotime($obj->start_at);
        $start_ym_jp   = date('Y年m月d日', $start_ts);
        $start_week_no = date('w', $start_ts);
        $start_week_jp = DayOfWeek::getDescription($start_week_no);
        $start_hs      = date('H時i分', $start_ts);

        $end_ts        = strtotime($obj->end_at);
        $end_ym_jp     = date('Y年m月d日', $end_ts);
        $end_week_no   = date('w', $end_ts);
        $end_week_jp   = DayOfWeek::getDescription($end_week_no);
        $end_hs        = date('H時i分', $end_ts);

        // 開始日時
        $start = $start_ym_jp . '(' . $start_week_jp . ') ' . $start_hs;

        // 開始日と終了日が同じか判定
        $end = '';
        if ($start_ym_jp != $end_ym_jp) {
            $end .= $end_ym_jp . '(' . $end_week_jp . ') ';
        }
        $end .= $end_hs;

        return $start . " - " . $end;
    }

    /**
     *  試験時間内が判定
     */
    public function isNowExamination($post_id)
    {
        if (empty($post_id)) {
            return false;
        }

        foreach ($this->getExaminationStatuses($post_id) as $examination_status) {
            // 対象は試験申し込み履歴
            if ($examination_status->task_status == 4) {
                // 申し込み日時以降で終了日時が到達していない判定
                if (strtotime($examination_status->start_at) <= time() && strtotime($examination_status->end_at) >= time()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     *  申し込み中の試験があり、時間前であること
     */
    public function isApplyingExamination($post_id)
    {
        if (empty($post_id)) {
            return false;
        }

        if ($this->getBeforeExamination($post_id)) {
            return true;
        }
        return false;
    }

    /**
     *  申し込み中の試験（日本語表記）
     */
    public function getApplyingExaminationDate($post_id)
    {
        if (empty($post_id)) {
            return false;
        }

        // 日本語での開始 - 終了表記で返す。
        return $this->getViewDate($this->getApplyingExamination($post_id));
    }

    /**
     *  申し込み中の試験
     */
    public function getApplyingExamination($post_id)
    {
        if (empty($post_id)) {
            return false;
        }

        $applying_examination_ts = null;
        $applying_examination = null;

        // 履歴から、終了日が到達していない、一番早い日時の試験を抜き出す
        foreach ($this->getExaminationStatuses($post_id) as $examination_status) {
            // 対象は試験申し込み履歴
            if ($examination_status->task_status == 4) {
                // 終了日が到達していない判定
                if (strtotime($examination_status->end_at) > time()) {
                    // 一番早い日時の試験を抜き出す
                    if (empty($applying_examination_ts) || strtotime($examination_status->end_at) < $applying_examination_ts) {
                        $applying_examination_ts = strtotime($examination_status->end_at);
                        $applying_examination = $examination_status;
                    }
                }
            }
        }
        return $applying_examination;
    }

    /**
     *  開始待ちの試験
     */
    public function getBeforeExamination($post_id)
    {
        if (empty($post_id)) {
            return false;
        }

        $applying_examination_ts = null;
        $applying_examination = null;

        // 開始日が到達していない、一番早い日時の試験を抜き出す
        foreach ($this->getExaminationStatuses($post_id) as $examination_status) {
            // 対象は試験申し込み履歴
            if ($examination_status->task_status == 4) {
                // 開始日が到達していない判定
                if (strtotime($examination_status->start_at) > time()) {
                    // 一番早い日時の試験を抜き出す
                    if (empty($applying_examination_ts) || strtotime($examination_status->start_at) < $applying_examination_ts) {
                        $applying_examination_ts = strtotime($examination_status->start_at);
                        $applying_examination = $examination_status;
                    }
                }
            }
        }
        return $applying_examination;
    }

    /**
     *  試験に合格済みか
     */
    public function isPassExamination($post_id)
    {
        if (empty($post_id)) {
            return false;
        }

        // 履歴をループして、試験で評価がA, B, C のいずれかがあれば合格
        foreach ($this->getExaminationStatuses($post_id) as $examination_status) {
            // 対象は試験の評価
            if ($examination_status->task_status == 6) {
                if ($examination_status->grade == 'A' || $examination_status->grade == 'B' || $examination_status->grade == 'C') {
                    return true;
                }
            }
        }
        return false;
    }
}
