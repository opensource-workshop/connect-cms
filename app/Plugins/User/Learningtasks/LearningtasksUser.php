<?php

namespace App\Plugins\User\Learningtasks;

use App\Enums\DayOfWeek;

/**
 * 課題管理のユーザ情報保持クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
 * @package Contoroller
 */
class LearningtasksUser
{
    /**
     * レポート履歴
     */
    public $report_statuses = array();

    /**
     * 試験履歴
     */
    public $examination_statuses = array();

    /**
     *  試験の申込を行えるか？判定のみ
     */
    public function canExamination()
    {
        list($can_examination, $reason) = $this->canExaminationImpl();
        return $can_examination;
    }

    /**
     *  試験の申込を行えるか？理由のみ
     */
    public function reasonExamination()
    {
        list($can_examination, $reason) = $this->canExaminationImpl();
        return $reason;
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
     *  試験の申込を行えるか？
     */
    public function canExaminationImpl()
    {
        // 以下の条件を満たせば、試験に申込できる。
        // ・レポートに合格していること。（判定が A, B, C のどれか）
        // レポートは一度合格すれば、再提出できない想定のため、順番は意識しない。
        $ok_report = $this->report_statuses->whereInStrict('grade', ['A', 'B', 'C'])->first();
        if (empty($ok_report)) {
            return array(false, 'レポートに合格していません。');
        }

        // 申し込み中の試験がある
        if (!empty($this->getApplyingExamination())) {
            return array(false, '申し込み済み試験があります。');
        }

        // すでに試験に合格している


        // 試験の申込OK
        return array(true, '');
    }

    /**
     *  試験時間内であること
     */
    public function isNowExamination()
    {
        foreach ($this->examination_statuses as $examination_status) {
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
    public function isApplyingExamination()
    {
        if ($this->getBeforeExamination()) {
            return true;
        }
        return false;
    }

    /**
     *  申し込み中の試験
     */
    public function getApplyingExaminationDate()
    {	
        // 日本語での開始 - 終了表記で返す。
        return $this->getViewDate($this->getApplyingExamination());
    }

    /**
     *  申し込み中の試験
     */
    public function getApplyingExamination()
    {
        $applying_examination_ts = null;
        $applying_examination = null;

        // 履歴から、終了日が到達していない、一番早い日時の試験を抜き出す
        foreach ($this->examination_statuses as $examination_status) {
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
    public function getBeforeExamination()
    {
        $applying_examination_ts = null;
        $applying_examination = null;

        // 開始日が到達していない、一番早い日時の試験を抜き出す
        foreach ($this->examination_statuses as $examination_status) {
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
    public function isPassExamination()
    {
        // 履歴をループして、試験で評価がA, B, C のいずれかがあれば合格
        foreach ($this->examination_statuses as $examination_status) {
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
