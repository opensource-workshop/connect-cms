<?php

namespace App\Plugins\User\Learningtasks;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Enums\DayOfWeek;
use App\Models\Common\PageRole;
use App\Models\Common\GroupUser;
use App\Models\User\Learningtasks\LearningtasksUsers;
use App\Models\User\Learningtasks\LearningtasksUsersStatuses;
use App\Models\User\Learningtasks\LearningtasksUseSettings;
use App\User;

/**
 * 課題管理のユーザ情報保持クラス
 *
 * メソッド一覧(public のもの)
 * ・教員か                                   isTeacher()
 * ・学生か                                   isStudent()
 * ・ユーザIDの取得                           getUserId()
 * ・課題バケツの取得                         getLearningtask()
 * ・評価中の受講生                           getStudent()
 * ・レポートの表示を行えるか？               canReportView($post_id)
 * ・課題の表示を行えるか？                   canPostView($post_id)
 * ・試験の表示を行えるか？                   canExaminationView($post_id)
 * ・レポートの履歴有無                       hasReportStatuses($post_id)
 * ・レポートの履歴取得                       getReportStatuses($post_id)
 * ・レポートの状況取得                       getReportStatus($post_id)
 * ・レポートの提出を行えるか？               canReportUpload($post_id)
 * ・レポートの評価を行えるか？               canReportEvaluate($post_id)
 * ・レポートにコメントを行えるか？           canReportComment($post_id)
 * ・試験の履歴有無                           hasExaminationStatuses($post_id)
 * ・試験の履歴取得                           getExaminationStatuses($post_id)
 * ・試験の状況取得                           getExaminationStatus($post_id)
 * ・試験問題を表示して良いか？               canViewExaminationFile($post_id)
 * ・試験の提出を行えるか？                   canExaminationUpload($post_id)
 * ・試験の申込を行えるか？判定のみ           canExamination($post_id)
 * ・試験の申込を行えるか？理由のみ           reasonExamination($post_id)
 * ・試験の評価を行えるか？                   canExaminationEvaluate($post_id)
 * ・試験にコメントを行えるか？               canExaminationComment($post_id)
 * ・試験日の画面表記を取得                   getViewDate($obj)
 * ・試験時間内か判定                         isNowExamination($post_id)
 * ・申し込み中の試験があり、時間前であること isApplyingExamination($post_id)
 * ・申し込み中の試験（日本語表記）           getApplyingExaminationDate($post_id)
 * ・申し込み中の試験                         getApplyingExamination($post_id)
 * ・開始待ちの試験                           getBeforeExamination($post_id)
 * ・試験に合格済みか                         isPassExamination($post_id)
 * ・教員用の受講生一覧取得                   getStudents()
 * ・レポートの開閉用の属性出力               getReportCollapseAriaControls()
 * ・試験の開閉用の属性出力                   getExaminationCollapseAriaControls()
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
 * @package Contoroller
 */
class LearningtasksTool
{
    /**
     * 課題バケツ
     */
    private $learningtask = null;

    /**
     * 課題
     */
    private $post = null;

    /**
     * ログインしているユーザ情報
     */
    private $user = null;

    /**
     * 表示する情報のユーザ（学生の場合は自分、教員の場合は対象学生が入る（未選択時はnull））
     */
    private $student_id = null;

    /**
     * レポート履歴
     */
    private $report_statuses = null;

    /**
     * 科目の受講者情報
     */
    private $students = null;

    /**
     * 科目の教員情報
     */
    private $teachers = null;

    /**
     * 試験履歴
     */
    private $examination_statuses = null;

    /**
     * 使用機能(課題セット)
     */
    private $base_use_functions = null;

    /**
     * 使用機能(課題)
     */
    private $post_use_functions = null;

    /**
     * コンストラクタ
     */
    public function __construct($request, $page_id, $learningtask, $post = null)
    {
        // 変数初期化
        $this->report_statuses = new Collection();
        $this->students = new Collection();
        $this->examination_statuses = new Collection();

        $this->learningtask = $learningtask;
        $this->post = $post;

        // ログインしているユーザthis->
        $this->user = Auth::user();

        // 使用する機能
        if (!empty($this->learningtask)) {
            $this->base_use_functions = LearningtasksUseSettings::where('learningtasks_id', $this->learningtask->id)->where('post_id', 0)->get();
        }
        if (!empty($this->learningtask) && !empty($this->post)) {
            $this->post_use_functions = LearningtasksUseSettings::where('learningtasks_id', $this->learningtask->id)->where('post_id', $this->post->id)->get();
        }

        // 参照するデータのユーザ（学生の場合は自分自身、教員の場合は、選択した学生）
        if ($this->isTeacher() && session('student_id')) {
            $this->student_id = session('student_id');
        } elseif ($this->isStudent()) {
            $this->student_id = $this->user->id;
        }

        // ユーザーstatusテーブル
        if (!empty($this->student_id)) {
            // レポートの履歴
            $this->report_statuses = LearningtasksUsersStatuses::where(
                'user_id', '=', $this->student_id
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
             ->where('learningtasks_users_statuses.user_id', '=', $this->student_id)
             ->whereIn('learningtasks_users_statuses.task_status', [4, 5, 6, 7])
             ->orderBy('learningtasks_users_statuses.post_id', 'asc')
             ->orderBy('learningtasks_users_statuses.id', 'asc')
             ->get();
        }

        // 受講生一覧と教員一覧の取得
        if (!empty($this->post)) {
            // ユーザの参加方式によって、対象を取得
            if ($this->post->student_join_flag == 2) {
                // 配置ページのメンバーシップユーザ全員
                // ページから参加グループ取得
                $group_ids = PageRole::select('group_id')
                                     ->where('page_id', $page_id)
                                     ->groupBy('group_id')
                                     ->orderBy('group_id')
                                     ->get();

                $group_users = GroupUser::select('user_id')
                                        ->whereIn('group_id', $group_ids->pluck('group_id'))
                                        ->groupBy('user_id')
                                        ->orderBy('user_id')
                                        ->get();

                $this->students = User::select('users.*')
                                      ->whereIn('users.id', $group_users->pluck('user_id'))
                                      ->join('users_roles', function ($join) {
                                          $join->on('users_roles.users_id', '=', 'users.id')
                                               ->where('users_roles.target', '=', 'original_role')
                                               ->where('users_roles.role_name', '=', 'student');
                                      })
                                      ->orderBy('users.id')
                                      ->get();

                $this->teachers = User::select('users.*')
                                      ->whereIn('users.id', $group_users->pluck('user_id'))
                                      ->join('users_roles', function ($join) {
                                          $join->on('users_roles.users_id', '=', 'users.id')
                                               ->where('users_roles.target', '=', 'original_role')
                                               ->where('users_roles.role_name', '=', 'teacher');
                                      })
                                      ->orderBy('users.id')
                                      ->get();

            } elseif ($this->post->student_join_flag == 3) {
                // 配置ページのメンバーシップユーザから選ぶ
                $this->students = LearningtasksUsers::select(
                                                        'users.*'
                                                    )->join('users', 'users.id', '=', 'learningtasks_users.user_id')
                                                     ->where('learningtasks_users.post_id', $this->post->id)
                                                     ->where('learningtasks_users.role_name', 'student')
                                                     ->orderBy('users.id', 'asc')
                                                     ->get();

                $this->teachers = LearningtasksUsers::select(
                                                        'users.*'
                                                    )->join('users', 'users.id', '=', 'learningtasks_users.user_id')
                                                     ->where('learningtasks_users.post_id', $this->post->id)
                                                     ->where('learningtasks_users.role_name', 'teacher')
                                                     ->orderBy('users.id', 'asc')
                                                     ->get();
            }
        }
    }

    /**
     *  使用機能の取得
     */
    public function getFunction($function, $post_check = false)
    {
        $setting_value = null;
        if ($post_check == false) {
            $setting_obj = $this->base_use_functions->where('use_function', $function)->first();
        } else {
            $setting_obj = $this->post_use_functions->where('use_function', $function)->first();
        }
        // 該当機能の設定がないと、空なので、チェックしてから値を取得
        if (!empty($setting_obj)) {
            $setting_value = $setting_obj->value;
        }
        return $setting_value;
    }

    /**
     *  使用機能のチェック
     */
    public function checkFunction($function)
    {
        $function_parts = explode('_', $function);
        // 課題ごとの設定がある場合。
        if (!empty($this->post_use_functions)) {
            // 課題独自設定の有無
            $category_setting = $this->post_use_functions->where('use_function', 'post_' . $function_parts[1] . '_setting')->first();
            if (empty($category_setting)) {
                $category_setting_value = null;
            } else {
                $category_setting_value = $category_setting->value;
            }

            if (empty($category_setting_value)) {
                // 課題セットの方を参照するので、このまま続きへ
            } elseif ($category_setting_value == 'off') {
                // この機能を使わないため、false
                return false;
            } elseif ($category_setting_value == 'on') {
                // 機能判定
                $post_setting_value = $this->post_use_functions->where('use_function', $function)->value;
                if ($post_setting_value == 'on') {
                    return true;
                } else {
                    return false;
                }
            }
        }

        // 課題セットの設定を確認
        $category_setting = $this->base_use_functions->where('use_function', 'post_' . $function_parts[0] . '_setting')->first;
        if (empty($category_setting)) {
            $category_setting_value = null;
        } else {
            $category_setting_value = $category_setting->value;
        }

        if (empty($category_setting_value)) {
            // 設定がない＝false
            return false;
        } elseif ($category_setting_value == 'off') {
            // この機能を使わないため、false
            return false;
        } elseif ($category_setting_value == 'on') {
            // 機能判定
            $post_setting_value = $this->base_use_functions->where('use_function', $function)->value;
            if ($post_setting_value == 'on') {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     *  ユーザIDの取得
     */
    public function getUserId()
    {
        if (empty($this->user)) {
            return null;
        }
        return $this->user->id;
    }

    /**
     *  課題バケツの取得
     */
    public function getLearningtask()
    {
        return $this->learningtask;
    }

    /**
     *  評価中の受講生
     */
    public function getStudent($default = "")
    {
        if (empty($this->student_id)) {
            return $default;
        }
        $student = User::find($this->student_id);
        if (empty($student)) {
            return $default;
        }
        return $student->name;
    }

    /**
     *  教員か
     */
    public function isTeacher()
    {
        if (empty($this->user) || empty($this->user->user_roles)) {
            return false;
        }
        $user_roles = $this->user->user_roles;
        if (array_key_exists('original_role', $user_roles) && array_key_exists('teacher', $user_roles['original_role']) && $user_roles['original_role']['teacher'] == 1) {
            return true;
        }
        return false;
    }

    /**
     *  学生か
     */
    public function isStudent()
    {
        if (empty($this->user) || empty($this->user->user_roles)) {
            return false;
        }
        $user_roles = $this->user->user_roles;
        if (array_key_exists('original_role', $user_roles) && array_key_exists('student', $user_roles['original_role']) && $user_roles['original_role']['student'] == 1) {
            return true;
        }
        return false;
    }

    /**
     *  モデレータ権限を保持しているか
     */
    public function isRoleArticle()
    {
        // コンテンツ管理者とモデレータはOK とする。
        if ($this->user->can('role_article')){
            return true;
        }
        return false;
    }

    /**
     *  課題の表示を行えるか？
     */
    public function canPostView()
    {
        // ログインユーザのみ課題の閲覧を許可？
        // 0 ならゲストも閲覧OK
        if ($this->learningtask->need_auth == 0) {
            return true;
        }

        // 要ログイン
        if (empty($this->user)){
            return false;
        }

        // コンテンツ管理者とモデレータはOK とする。
        if ($this->user->can('role_article')){
            return true;
        }

        // 教員、受講者として設定されているか。（メンバーシップとしての設定も含む）
        if ($this->isTeacher()) {
            if ($this->teachers->where('id', $this->getUserId())->isNotEmpty()) {
                return true;
            }
        }
        elseif ($this->isStudent()) {
            if ($this->students->where('id', $this->getUserId())->isNotEmpty()) {
                return true;
            }
        }
        return false;
    }

    /**
     *  レポートの履歴有無
     */
    public function hasReportStatuses($post_id)
    {
        if (empty($post_id)) {
            return $this->report_statuses;
        }
        $report_statuses = $this->getReportStatuses($post_id);
        if (empty($report_statuses)) {
            return false;
        }
        if ($report_statuses->count() == 0) {
            return false;
        }
        return true;
    }

    /**
     *  レポートの表示を行えるか？
     *  
     */
    public function canReportView($post_id)
    {
        if (empty($post_id)) {
            return false;
        }
        if (empty($this->user)) {
            return false;
        }
        if ($this->isTeacher() && empty($this->student_id)) {
            return false;
        }
        return true;
    }

    /**
     *  試験の表示を行えるか？
     */
    public function canExaminationView($post_id)
    {
        if (empty($post_id)) {
            return false;
        }
        if (empty($this->user)) {
            return false;
        }
        if ($this->isTeacher() && empty($this->student_id)) {
            return false;
        }
        return true;
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
            return "";
        }
        if (empty($this->report_statuses)) {
            return "";
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
     * レポートの提出を行えるか？
     */
    public function canReportUpload($post_id)
    {
        if (!$this->isStudent()) {
            return false;
        }

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
     *  レポートの評価を行えるか？
     */
    public function canReportEvaluate($post_id)
    {
        if (empty($post_id)) {
            return false;
        }
        // レポートの最新ステータスが提出済みか評価済み
        $last_report_status = $this->report_statuses->where('post_id', $post_id)->whereIn('task_status', [1, 2])->last();

        // 提出済み or 評価済みの最後を取得して、取得したものが提出済みの場合、評価がまだということになる。
        if (!empty($last_report_status) && $last_report_status->task_status == 1) {
            return true;
        }
        return false;
    }


    /**
     *  レポートにコメントを行えるか？
     */
    public function canReportComment($post_id)
    {
        if (empty($post_id)) {
            return false;
        }
        // レポートの最新ステータスが提出済みか評価済み
        $last_report_status = $this->report_statuses->where('post_id', $post_id)->whereIn('task_status', [1, 2])->last();

        // 提出済み or 評価済みの最後を取得して、取得したものが提出済みの場合、評価がまだということになる。
        if (!empty($last_report_status) && $last_report_status->task_status == 1) {
            return true;
        }

        // 最後が評価済みで評価がD の場合、まだ完了していないので、コメント可能
        if (!empty($last_report_status) && $last_report_status->task_status == 2 && $last_report_status->grade == 'D') {
            return true;
        }
        return false;
    }

    /**
     *  試験の履歴有無
     */
    public function hasExaminationStatuses($post_id)
    {
        if (empty($post_id)) {
            return false;
        }
        $examination_statuses = $this->getExaminationStatuses($post_id);
        if (empty($examination_statuses)) {
            return false;
        }
        if ($examination_statuses->count() == 0) {
            return false;
        }
        return true;
    }

    /**
     *  試験の履歴取得
     */
    public function getExaminationStatuses($post_id)
    {
        if (empty($post_id)) {
            return $this->examination_statuses;
        }
        if (empty($this->examination_statuses)) {
            return new Collection();
        }
        return $this->examination_statuses->where('post_id', $post_id);
    }

    /**
     *  試験の状況取得
     */
    public function getExaminationStatus($post_id)
    {
        if (empty($post_id)) {
            return "";
        }
        if (empty($this->examination_statuses)) {
            return "";
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
     *  試験問題を表示して良いか？
     */
    public function canViewExaminationFile($post_id)
    {
        // 教員はいつ見ても良い。
        if ($this->isTeacher()) {
            return true;
        }
        // 学生は試験時間内のみ見ても良い。
        if ($this->isStudent() && $this->isNowExamination($post_id)) {
            return true;
        }
        return false;
    }

    /**
     *  試験の提出を行えるか？
     */
    public function canExaminationUpload($post_id)
    {
        // 学生のみ提出可能。
        if (!$this->isStudent()) {
            return false;
        }

        // すでに試験の解答を提出している状態ならアップロード不可
        $examination_statuses = $this->examination_statuses->where('post_id', $post_id);
        $examination_status = $examination_statuses->whereIn('task_status', [5, 6])->last();
        if (!empty($examination_status)) {
            if ($examination_status->count() > 0 && $examination_status->task_status == 5) {
                return false;
            }
        }

        // すでに合格済みはアップロード不可
        if (!empty($examination_status) && $examination_status->task_status == 6) {
            if ($examination_status->grade == 'A' || $examination_status->grade == 'B' || $examination_status->grade == 'C') {
                return false;
            }
        }

        // 学生は試験時間内のみアップロード可能。
        if ($this->isStudent() && $this->isNowExamination($post_id)) {
            return true;
        }
        return false;
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
        $examination_statuses = $this->examination_statuses->where('post_id', $post_id);

        $ok_examination = $examination_statuses->whereInStrict('grade', ['A', 'B', 'C'])->first();
        if (!empty($ok_examination) && $ok_examination->count() > 0) {
            return array(false, '試験に合格済みです。');
        }

        // 試験の申込OK
        return array(true, '');
    }

    /**
     *  試験の評価を行えるか？
     */
    public function canExaminationEvaluate($post_id)
    {
        if (empty($post_id)) {
            return false;
        }
        // 試験の最新ステータスが提出済みか評価済み
        $last_examination_status = $this->examination_statuses->where('post_id', $post_id)->whereIn('task_status', [5, 6])->last();

        // 提出済み or 評価済みの最後を取得して、取得したものが提出済みの場合、評価がまだということになる。
        if (!empty($last_examination_status) && $last_examination_status->task_status == 5) {
            return true;
        }
        return false;
    }

    /**
     *  試験にコメントを行えるか？
     */
    public function canExaminationComment($post_id)
    {
        if (empty($post_id)) {
            return false;
        }
        // 試験の最新ステータスが提出済みか評価済み
        $last_examination_status = $this->examination_statuses->where('post_id', $post_id)->whereIn('task_status', [5, 6])->last();

        // 提出済み or 評価済みの最後を取得して、取得したものが提出済みの場合、評価がまだということになる。
        if (!empty($last_examination_status) && $last_examination_status->task_status == 5) {
            return true;
        }

        // 最後が評価済みで評価がD の場合、まだ完了していないので、コメント可能
        if (!empty($last_examination_status) && $last_examination_status->task_status == 6 && $last_examination_status->grade == 'D') {
            return true;
        }
        return false;
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
     *  試験時間内か判定
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

    /**
     *  教員用の受講生一覧取得
     */
    public function getStudents()
    {
        return $this->students;
    }

    /**
     *  教員用の受講生ID取得
     */
    public function getStudentId()
    {
        return $this->student_id;
    }

    /**
     *  レポートの開閉用の属性出力
     */
    public function getReportCollapseAriaControls()
    {
        if (empty($this->report_statuses)) {
            return "";
        }
        $ret_str_array = array();
        for ($i = 0; $i < $this->report_statuses->count(); $i++) {
            $ret_str_array[] = "multiCollapseReport" . $i;
        }
        return implode(' ', $ret_str_array);
    }

    /**
     *  試験の開閉用の属性出力
     */
    public function getExaminationCollapseAriaControls()
    {
        if (empty($this->examination_statuses)) {
            return "";
        }
        $ret_str_array = array();
        for ($i = 0; $i < $this->examination_statuses->count(); $i++) {
            $ret_str_array[] = "multiCollapseExamination" . $i;
        }
        return implode(' ', $ret_str_array);
    }
}
