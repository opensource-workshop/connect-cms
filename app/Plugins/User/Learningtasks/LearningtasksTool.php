<?php

namespace App\Plugins\User\Learningtasks;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Enums\DayOfWeek;
use App\Models\Common\PageRole;
use App\Models\Common\GroupUser;
use App\Models\Core\UsersRoles;
use App\Models\User\Learningtasks\LearningtasksConfigs;
use App\Models\User\Learningtasks\LearningtasksUsers;
use App\Models\User\Learningtasks\LearningtasksUsersStatuses;
use App\Models\User\Learningtasks\LearningtasksUseSettings;
use App\User;

use Carbon\Carbon;

/**
 * 課題管理のユーザ情報保持クラス
 *
 * メソッド一覧(public のもの)
 * ・教員か                                               isTeacher()
 * ・学生か                                               isStudent()
 * ・課題管理者か                                          isLearningtaskAdmin()
 * ・教員の一覧取得                                       getTeachers()
 * ・教員名の取得                                         getTeachersName()
 * ・ユーザIDの取得                                       getUserId()
 * ・課題バケツの取得                                     getLearningtask()
 * ・評価中の受講生                                       getStudent()
 * ・レポートの表示を行えるか？                           canReportView($post_id)
 * ・課題の表示を行えるか？                               canPostView($post_id)
 * ・試験の表示を行えるか？                               canExaminationView($post_id)
 * ・総合評価を行えるか？                                 canEvaluate($post_id)
 * ・総合評価の表示を行えるか？                           canEvaluateView($post_id)
 * ・レポートの履歴有無                                   hasReportStatuses($post_id)
 * ・レポートの件数                                       countReportStatuses()
 * ・レポートの履歴取得                                   getReportStatuses($post_id)
 * ・レポートの状況取得                                   getReportStatus($post_id)
 * ・レポートの提出を行えるか？                           canReportUpload($post_id)
 * ・レポートの状況の文言取得                             getReportUploadMessage()
 * ・レポートの評価を行えるか？                           canReportEvaluate($post_id)
 * ・レポートにコメントを行えるか？                       canReportComment($post_id)
 * ・試験の履歴有無                                       hasExaminationStatuses($post_id)
 * ・試験の履歴取得                                       getExaminationStatuses($post_id)
 * ・試験の状況取得                                       getExaminationStatus($post_id)
 * ・試験問題を表示して良いか？                           canViewExaminationFile($post_id)
 * ・試験の提出を行えるか？                               canExaminationUpload($post)
 * ・試験の申込を行えるか？判定のみ                       canExamination($post)
 * ・試験の申込を行えるか？理由のみ                       reasonExamination($post)
 * ・試験の評価を行えるか？                               canExaminationEvaluate($post)
 * ・試験にコメントを行えるか？                           canExaminationComment($post)
 * ・試験日の画面表記を取得                               getViewDate($obj)
 * ・試験時間内か判定                                     isNowExamination($post_id)
 * ・試験の件数                                           countExaminationStatuses()
 * ・申し込み中の試験があり、時間前であること             isApplyingExamination($post_id)
 * ・申し込み中の試験（日本語表記）                       getApplyingExaminationDate($post_id)
 * ・申し込み中の試験                                     getApplyingExamination($post_id)
 * ・開始待ちの試験                                       getBeforeExamination($post_id)
 * ・試験に合格済みか                                     isPassExamination($post_id)
 * ・教員用の受講生一覧取得                               getStudents()
 * ・教員用の受講生ID取得                                 getStudentId()
 * ・レポートの開閉用の属性出力                           getReportCollapseAriaControls()
 * ・試験の開閉用の属性出力                               getExaminationCollapseAriaControls()
 * ・総合評価の状況取得                                   getEvaluateStatus()
 * ・使用機能の取得                                       getFunction()
 * ・使用機能のチェック                                   checkFunction()
 * ・メール設定取得                                       getMailConfig()
 * ・指定されたステータスを機能名に変換                   changeStatus2FunctionName()
 * ・指定されたステータスで指定した機能が使用できるか。   isUseFunction()
 * ・指定されたステータスでファイルアップロードが必要か。 isRequreUploadFile()
 * ・課題ごとの使用機能設定のCollapse CSS を返す          getSettingShowstr()
 * ・履歴削除（評価の取り消し）は行えるか？                canDeletetableUserStatus($users_statuses_id)
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
     * 総合評価履歴
     */
    private $evaluate_statuses = null;

    /**
     * 使用機能(課題セット)
     */
    private $base_use_functions = null;

    /**
     * 使用機能(課題)
     */
    private $post_use_functions = null;

    /**
     * 履歴削除（評価の取り消し）できるユーザステータスID (最後のUsersStatusesのid)
     */
    private $deletetable_users_statuses_id = null;

    /**
     * 課題設定
     */
    private $configs = null;

    /**
     * コンストラクタ
     */
    public function __construct($request, $page_id, $learningtask, $post = null, $frame_id = null)
    {
        // 変数初期化
        $this->report_statuses = new Collection();
        $this->students = new Collection();
        $this->examination_statuses = new Collection();
        $this->evaluate_statuses = new Collection();

        $this->learningtask = $learningtask;
        $this->post = $post;

        // ログインしているユーザ
        $this->user = Auth::user();

        // 使用する機能
        if (!empty($this->learningtask)) {
            $this->base_use_functions = LearningtasksUseSettings::where('learningtasks_id', $this->learningtask->id)->where('post_id', 0)->get();
        }
        // bugfix: 一覧画面でレポートの評価等、表示できてないバグ修正
        // if (!empty($this->learningtask) && !empty($this->post)) {
        if (!empty($this->learningtask)) {
            if (empty($this->post)) {
                // 一覧画面
                $this->post_use_functions = LearningtasksUseSettings::where('learningtasks_id', $this->learningtask->id)->get();
            } else {
                // 詳細画面
                $this->post_use_functions = LearningtasksUseSettings::where('learningtasks_id', $this->learningtask->id)->where('post_id', $this->post->id)->get();
            }
        }

        // メール設定
        if (!empty($this->learningtask)) {
            $this->configs = LearningtasksConfigs::where('learningtasks_id', $this->learningtask->id)->where('post_id', 0)->get();
        }

        // 参照するデータのユーザ（学生の場合は自分自身、教員の場合は、選択した学生）
        if ($this->isTeacher() && session('student_id' . $frame_id) || $this->isLearningtaskAdmin() && session('student_id' . $frame_id)) {
            $this->student_id = session('student_id' . $frame_id);
        } elseif ($this->isStudent()) {
            $this->student_id = $this->user->id;
        }

        // ユーザーstatusテーブル
        if (!empty($this->student_id)) {
            // レポートの履歴
            $this->report_statuses = LearningtasksUsersStatuses::where('user_id', '=', $this->student_id)
                    ->whereIn('task_status', [1, 2, 3])
                    ->orderBy('post_id', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

            // 試験の履歴
            $this->examination_statuses = LearningtasksUsersStatuses::
                    select(
                        'learningtasks_users_statuses.*',
                        'learningtasks_examinations.start_at',
                        'learningtasks_examinations.end_at',
                        'learningtasks_examinations.entry_end_at'
                    )
                    // bugfix: 論理削除を考慮
                    // ->leftJoin('learningtasks_examinations', 'learningtasks_examinations.id', '=', 'learningtasks_users_statuses.examination_id')
                    ->leftJoin('learningtasks_examinations', function ($join) {
                        $join->on('learningtasks_examinations.id', '=', 'learningtasks_users_statuses.examination_id')
                                ->whereNull('learningtasks_examinations.deleted_at');
                    })
                    ->where('learningtasks_users_statuses.user_id', '=', $this->student_id)
                    ->whereIn('learningtasks_users_statuses.task_status', [4, 5, 6, 7])
                    ->orderBy('learningtasks_users_statuses.post_id', 'asc')
                    ->orderBy('learningtasks_users_statuses.id', 'asc')
                    ->get();

            // 総合評価の履歴
            // POST のWHERE が抜けていたので、追加（2020-12-21）これがないと、他の科目の総合評価を引っ張ってきて、評価できない。
            if ($this->post) {
                $this->evaluate_statuses = LearningtasksUsersStatuses::where('user_id', '=', $this->student_id)
                        ->whereIn('task_status', [8])
                        ->where('post_id', $this->post->id)
                        ->orderBy('post_id', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();

                // 履歴削除（評価の取り消し）できるユーザステータスID (最後のUsersStatusesのid)
                $this->deletetable_users_statuses_id = LearningtasksUsersStatuses::where('user_id', '=', $this->student_id)
                        ->where('post_id', $this->post->id)
                        ->max('id');
            } else {
                $this->evaluate_statuses = LearningtasksUsersStatuses::where('user_id', '=', $this->student_id)
                        ->whereIn('task_status', [8])
                        ->orderBy('post_id', 'asc')
                        ->orderBy('id', 'asc')
                        ->get();
            }
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
                                               ->where('users_roles.role_name', '=', \RoleName::student);
                                      })
                                      ->orderBy('users.id')
                                      ->get();

                $this->teachers = User::select('users.*')
                                      ->whereIn('users.id', $group_users->pluck('user_id'))
                                      ->join('users_roles', function ($join) {
                                          $join->on('users_roles.users_id', '=', 'users.id')
                                               ->where('users_roles.target', '=', 'original_role')
                                               ->where('users_roles.role_name', '=', \RoleName::teacher);
                                      })
                                      ->orderBy('users.id')
                                      ->get();
            } elseif ($this->post->student_join_flag == 3) {
                // 配置ページのメンバーシップユーザから選ぶ
                $this->students = LearningtasksUsers::select('users.*')
                                                    ->join('users', 'users.id', '=', 'learningtasks_users.user_id')
                                                    ->where('learningtasks_users.post_id', $this->post->id)
                                                    ->where('learningtasks_users.role_name', \RoleName::student)
                                                    ->orderBy('users.id', 'asc')
                                                    ->get();

                $this->teachers = LearningtasksUsers::select('users.*')
                                                    ->join('users', 'users.id', '=', 'learningtasks_users.user_id')
                                                    ->where('learningtasks_users.post_id', $this->post->id)
                                                    ->where('learningtasks_users.role_name', \RoleName::teacher)
                                                    ->orderBy('users.id', 'asc')
                                                    ->get();
            }
        }
    }

    /**
     *  メール設定取得
     */
    public function getMailConfig($type, $task_status, $post_id = 0, $default = "")
    {
        if (empty($this->configs)) {
            return $default;
        }

        $mail_config = $this->configs->where("post_id", $post_id)->where("type", $type)->where("task_status", $task_status)->first();

        if (empty($mail_config)) {
            return $default;
        }

        // bugfix: メール設定を空で登録した場合、valueがnullになるため対応
        if (empty($mail_config->value)) {
            return $default;
        }

        return $mail_config->value;
    }

    /**
     *  教員の一覧取得
     */
    public function getTeachers()
    {
        return $this->teachers;
    }

    /**
     *  教員名の取得
     */
    public function getTeachersName($ommit_role = null)
    {
        // ommit_role が指定されていれば、ommit_role の関連権限を取得
        $cc_role_hierarchys = config('cc_role.CC_ROLE_HIERARCHY');
        $omit_role_hierarchy = null;
        if (!empty($ommit_role)) {
            $omit_role_hierarchy = $cc_role_hierarchys[$ommit_role];
        }

        $teacher_names = array();
        // [bug] Invalid argument supplied for foreach(). 参加教員 未選択時
        foreach ($this->teachers as $teacher) {
            // ommit_role の指定に合致すれば対象外（システムの管理者などを除外するのが目的）
            if (!empty($omit_role_hierarchy)) {
                $users_roles = UsersRoles::where('users_id', $teacher->id)->get();
                foreach ($users_roles as $users_role) {
                    if (in_array($users_role, $omit_role_hierarchy)) {
                        continue 2;
                    }
                }
            }
            $teacher_names[] = $teacher->name;
        }
        return implode('，', $teacher_names);
    }

    /**
     * 使用機能の取得
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
            if (LearningtasksUseSettings::isDatetimeUseFunction($function)) {
                // 日時を使う機能
                $setting_value = $setting_obj->datetime_value;
            } else {
                // 通常
                $setting_value = $setting_obj->value;
            }
        }
        return $setting_value;
    }

    /**
     * post, baseの両方から順で、レポートの使用機能の値取得
     */
    public function getFunctionBothReport(string $function)
    {
        return $this->getFunctionBoth($function, \LearningtaskUseFunction::report);
    }

    /**
     * post, baseの両方から順で使用機能の取得
     */
    private function getFunctionBoth(string $function, string $function_parts)
    {
        // 使用機能の課題独自設定 取得
        $post_function_setting_value = $this->getPostFunctionSetting($function_parts[1], null);

        if (empty($post_function_setting_value)) {
            // 課題管理設定に従う = null
            // ここではなにもせず、課題セットの設定へいく
        } elseif ($post_function_setting_value == 'on') {
            // この課題独自に設定する = on

            // postから取得
            $setting_value = $this->getFunction($function, true);
            if ($setting_value) {
                // 値があったら返却
                return $setting_value;
            }
        } elseif ($post_function_setting_value == 'off') {
            // 課題独自で使用しない = off
            return null;
        }

        // baseから取得
        $setting_value = $this->getFunction($function, false);
        return $setting_value;
    }

    /**
     * 使用機能のチェック
     */
    public function checkFunction($function, int $post_id = null)
    {
        $function_parts = explode('_', $function);

        // bugfix: 一覧画面でレポートの評価等、表示できてないバグ修正
        // 使用機能の課題独自設定 取得
        $post_function_setting_value = $this->getPostFunctionSetting($function_parts[1], $post_id);

        if (empty($post_function_setting_value)) {
            // 課題管理設定に従う = null
            // ここではなにもせず、課題セットの設定へいく
        } elseif ($post_function_setting_value == 'on') {
            // この課題独自に設定する = on

            // 機能判定
            // bugfix: post側に設定ない事をも考慮する。 例）レポート提出 use_report のチェックが付いてない時
            // $post_setting_value = $this->post_use_functions->where('use_function', $function)->value;
            // $post_setting = $this->post_use_functions->where('use_function', $function)->first();
            if ($post_id) {
                // 一覧画面でpost_id指定
                $post_setting = $this->post_use_functions->where('use_function', $function)->where('post_id', $post_id)->first();
            } else {
                // 詳細画面
                $post_setting = $this->post_use_functions->where('use_function', $function)->first();
            }

            if (empty($post_setting)) {
                $post_setting_value = null;
            } else {
                $post_setting_value = $post_setting->value;
            }

            if (empty($post_setting_value)) {
                // 設定がない＝false
                return false;
            } elseif ($post_setting_value == 'off') {
                // この機能を使わないため、false
                return false;
            } elseif ($post_setting_value == 'on') {
                // 機能を使う
                return true;
            }
        } elseif ($post_function_setting_value == 'off') {
            // 課題独自で使用しない = off
            return false;
        }

        // 課題セットの設定を確認
        $category_setting = $this->base_use_functions->where('use_function', $function)->first();
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
            // 機能を使う
            return true;
        }
        return false;
    }

    /**
     * 使用機能の課題独自設定 取得
     */
    private function getPostFunctionSetting(string $function_parts, int $post_id = null)
    {
        $post_function_setting_value = null;

        // 課題ごとの設定がある場合。
        if (!empty($this->post_use_functions)) {
            // 課題独自設定の有無
            if ($post_id) {
                // 一覧画面でpost_id指定
                $post_setting = $this->post_use_functions->where('use_function', 'post_' . $function_parts . '_setting')->where('post_id', $post_id)->first();
            } else {
                // 詳細画面
                $post_setting = $this->post_use_functions->where('use_function', 'post_' . $function_parts . '_setting')->first();
            }

            if ($post_setting) {
                $post_function_setting_value = $post_setting->value;
            }
        }

        return $post_function_setting_value;
    }

    /**
     *  課題ごとの使用機能設定のCollapse CSS を返す
     */
    public function getSettingShowstr($use_function)
    {
        // 課題ごとの設定がない場合は空を返す
        if (empty($this->post_use_functions)) {
            return "";
        }

        // 設定を取得し、課題セットに従う（空）or 使用しない（off）の場合は空文字を返す。on の場合は、上書き設定を表示するための show を返す。
        $use_function_obj = $this->post_use_functions->where('use_function', $use_function)->first();
        if (empty($use_function_obj) || empty($use_function_obj->value)) {
            return "";
        } elseif ($use_function_obj->value == 'on') {
            return "show";
        }
        return "";
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
     * 教員か
     */
    public function isTeacher()
    {
        if (empty($this->user) || empty($this->user->user_roles)) {
            return false;
        }
        $user_roles = $this->user->user_roles;
        if (array_key_exists('original_role', $user_roles)
                && array_key_exists(\RoleName::teacher, $user_roles['original_role'])
                && $user_roles['original_role'][\RoleName::teacher] == 1) {
            return true;
        }
        return false;
    }

    /**
     * 学生か
     */
    public function isStudent()
    {
        if (empty($this->user) || empty($this->user->user_roles)) {
            return false;
        }
        $user_roles = $this->user->user_roles;
        if (array_key_exists('original_role', $user_roles)
                && array_key_exists(\RoleName::student, $user_roles['original_role'])
                && $user_roles['original_role'][\RoleName::student] == 1) {
            return true;
        }
        return false;
    }

    /**
     * 課題管理者か
     */
    public function isLearningtaskAdmin()
    {
        if (empty($this->user)) {
            return false;
        }

        // コンテンツ管理者はOKとする。
        if ($this->user->can('role_article_admin')) {
            return true;
        }
        return false;
    }

    // delete: 使われてない
    // /**
    //  * モデレータ権限を保持しているか
    //  */
    // public function isRoleArticle()
    // {
    //     // コンテンツ管理者とモデレータはOK とする。
    //     if ($this->user->can('role_article')) {
    //         return true;
    //     }
    //     return false;
    // }

    /**
     * 課題の表示を行えるか？
     */
    public function canPostView()
    {
        // ログインユーザのみ課題の閲覧を許可？
        // 0 ならゲストも閲覧OK
        if (!$this->checkFunction('use_need_auth')) {
            return true;
        }

        // 要ログイン
        if (empty($this->user)) {
            return false;
        }

        // 課題管理者はOK とする。
        // if ($this->user->can('role_article')) {
        if ($this->isLearningtaskAdmin()) {
                return true;
        }

        // 教員、受講者として設定されているか。（メンバーシップとしての設定も含む）
        if ($this->isTeacher()) {
            if ($this->teachers->where('id', $this->getUserId())->isNotEmpty()) {
                return true;
            }
        } elseif ($this->isStudent()) {
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
     *  レポートの件数
     */
    public function countReportStatuses($post_id)
    {
        if (empty($post_id)) {
            return 0;
        }
        $report_statuses = $this->getReportStatuses($post_id);
        if (empty($report_statuses)) {
            return 0;
        }
        return $report_statuses->count();
    }

    /**
     *  レポートの表示を行えるか？
     */
    public function canReportView($post_id)
    {
        // 課題を選んでいない
        if (empty($post_id)) {
            return false;
        }
        // ログインしていない
        if (empty($this->user)) {
            return false;
        }
        // 教員の場合に、受講者を選んでいない
        if ($this->isTeacher() && empty($this->student_id)) {
            return false;
        }
        return true;
    }

    /**
     * 指定されたステータスを機能名に変換
     */
    public function changeStatus2FunctionName($task_status, $detail_function = null)
    {
        $function_name = '';

        // 各ステータスのファイル提出がON か判定
        if ($task_status == 1) {
            // レポートのファイル提出
            $function_name = 'use_report';
        } elseif ($task_status == 2) {
            // レポート評価のファイル提出
            $function_name = 'use_report_evaluate';
        } elseif ($task_status == 3) {
            // レポートコメントのファイル提出
            $function_name = 'use_report_reference';
        } elseif ($task_status == 5) {
            // 試験のファイル提出
            $function_name = 'use_examination';
        } elseif ($task_status == 6) {
            // 試験評価のファイル提出
            $function_name = 'use_examination_evaluate';
        } elseif ($task_status == 7) {
            // 試験コメントのファイル提出
            $function_name = 'use_examination_reference';
        } elseif ($task_status == 8) {
            // 試験コメントのファイル提出
            $function_name = 'use_evaluate';
        }

        // 機能詳細
        if (!empty($detail_function)) {
            $function_name .= '_' . $detail_function;
        }
        return $function_name;
    }

    /**
     *  指定されたステータスで指定した機能が使用できるか。
     */
    public function isUseFunction($task_status, $detail_function)
    {
        // 指定されたステータスを機能名に変換して、設定に保持しているか確認
        return $this->checkFunction($this->changeStatus2FunctionName($task_status, $detail_function));
    }

    /**
     *  指定されたステータスでファイルアップロードが必要か。
     */
    public function isRequreUploadFile($task_status)
    {
        // 指定されたステータスを機能名に変換して、設定に保持しているか確認
        return $this->checkFunction($this->changeStatus2FunctionName($task_status, 'file'));
    }

    /**
     *  試験の表示を行えるか？
     */
    public function canExaminationView($post)
    {
        if (empty($post->id)) {
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
     *  総合評価の登録を行えるか？
     */
    public function canEvaluateView($post)
    {
        if (empty($post->id)) {
            return false;
        }
        // 総合評価済みなら、もう登録できない。
        if ($this->evaluate_statuses->isNotEmpty()) {
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
        if ($this->isStudent() || $this->isLearningtaskAdmin()) {
            // 処理続行
        } else {
            // 提出できない
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
     * レポートの状況取得
     */
    private function canReportUploadImpl($post_id)
    {
        if (empty($post_id)) {
            return array(false, 'データがありません。');
        }

        // 初めはOK。提出済みならNO、再提出があればOK。合格ならその時点でNO
        $can_report_upload = array(true, '未提出');

        // レポート提出期限チェック
        $can_report_upload = $this->checkReportUploadDeadline($can_report_upload);

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

                // レポート提出期限チェック
                $can_report_upload = $this->checkReportUploadDeadline($can_report_upload);
            }
        }
        return $can_report_upload;
    }

    /**
     * レポート提出期限チェック
     */
    private function checkReportUploadDeadline($can_report_upload)
    {
        // レポート提出期限オーバーか
        if ($this->isOutOfDeadlineReportUpload()) {
            $can_report_upload = array(false, '提出期限を過ぎたのため、現在は提出できません。');
        }
        return $can_report_upload;
    }

    /**
     * レポート提出期限オーバーか
     */
    public function isOutOfDeadlineReportUpload()
    {
        // 提出終了日時の制御ON
        if ($this->checkFunction(\LearningtaskUseFunction::use_report_end)) {
            // 今より提出期限[以上(gte)]なら、提出できない. use_report_end=on なら report_end_at は必須のため、値がある想定
            if (Carbon::now()->gte($this->getFunctionBothReport(\LearningtaskUseFunction::report_end_at))) {
                return true;
            }
        }
        return false;
    }

    /**
     *  レポートの評価を行えるか？
     */
    public function canReportEvaluate($post)
    {
        if (empty($post->id)) {
            return false;
        }
        // レポートの最新ステータスが提出済みか評価済み
        $last_report_status = $this->report_statuses->where('post_id', $post->id)->whereIn('task_status', [1, 2])->last();

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
     *  試験の件数
     */
    public function countExaminationStatuses($post_id)
    {
        if (empty($post_id)) {
            return 0;
        }
        $examination_statuses = $this->getExaminationStatuses($post_id);
        if (empty($examination_statuses)) {
            return 0;
        }
        return $examination_statuses->count();
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
    public function canExamination($post)
    {
        if (empty($post->id)) {
            return false;
        }

        list($can_examination, $reason) = $this->canExaminationImpl($post);
        return $can_examination;
    }

    /**
     *  試験の申込を行えるか？理由のみ
     */
    public function reasonExamination($post)
    {
        if (empty($post->id)) {
            return false;
        }

        list($can_examination, $reason) = $this->canExaminationImpl($post);
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
    public function canExaminationUpload($post)
    {
        // 学生のみ提出可能。
        if (!$this->isStudent()) {
            return false;
        }

        // すでに試験の解答を提出している状態ならアップロード不可
        $examination_statuses = $this->examination_statuses->where('post_id', $post->id);
        // 2020-12-22 whereIn('task_status', [5, 6]) は不要。再提出＆再申し込みなら、最後に4（試験申し込み）があり、その前に5 がある。
        //$examination_status = $examination_statuses->whereIn('task_status', [5, 6])->last();
        $examination_status = $examination_statuses->last();
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
        if ($this->isStudent() && $this->isNowExamination($post->id)) {
            return true;
        }
        return false;
    }

    /**
     *  試験の申込を行えるか？
     */
    private function canExaminationImpl($post)
    {
        $base_message = "試験に申し込む条件が不足しています。";

        // 受講生 or 課題管理者でない場合は試験の申し込みはできない。
        if ($this->isStudent() || $this->isLearningtaskAdmin()) {
            // 処理続行
        } else {
            return array(false, $base_message);
        }

        if (empty($post->id)) {
            return array(false, $base_message);
        }

        // post_id で絞る。
        $report_statuses = $this->report_statuses->where('post_id', $post->id);

        if (empty($report_statuses) || $report_statuses->count() == 0) {
            return array(false, $base_message);
        }

        // 申し込み中の試験がある
        if (!empty($this->getApplyingExamination($post->id))) {
            return array(false, $base_message . '<br />申し込み済み試験があります。');
        }

        // すでに試験を回答して、評価待ちである。（最新の履歴が task_status 5 試験の解答提出（再提出も同じ）である。）
        $examination_statuses = $this->examination_statuses->where('post_id', $post->id);
        $examination_last_status = $examination_statuses->last();
        if (!empty($examination_last_status)) {
            if ($examination_last_status->task_status == 5) {
                return array(false, $base_message . '<br />試験解答済みで評価待ちです。');
                return false;
            }
        }

        // すでに試験に合格している
        $examination_statuses = $this->examination_statuses->where('post_id', $post->id);

        $ok_examination = $examination_statuses->whereInStrict('grade', ['A', 'B', 'C'])->first();
        if (!empty($ok_examination) && $ok_examination->count() > 0) {
            return array(false, $base_message . '<br />試験に合格済みです。');
        }

        // 申し込み可能判定でのチェック
        $post_examination_timing = LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
                                                           ->where('post_id', $post->id)
                                                           ->where('use_function', 'post_examination_timing')
                                                           ->first();
        if (empty($post_examination_timing)) {
            // レポートに合格していること。（判定が A, B, C のどれか）
            // レポートは一度合格すれば、再提出できない想定のため、順番は意識しない。
            $ok_report = $report_statuses->whereInStrict('grade', ['A', 'B', 'C'])->first();
            if (empty($ok_report)) {
                return array(false, $base_message . '<br />レポートに合格していません。');
            }
        } elseif ($post_examination_timing->value == 'one') {
            // レポートが1回でも提出済みなら（合否のチェックはしない）
            $upload_report = $report_statuses->where('task_status', 1)->first();
            if (empty($upload_report)) {
                return array(false, $base_message . '<br />レポートが提出されていません。');
            }
        } elseif ($post_examination_timing->value == 'no_fail') {
            $upload_report = $report_statuses->where('task_status', 1)->last();
            $evaluate_report = $report_statuses->where('task_status', 2)->last();
            if (empty($upload_report)) {
                return array(false, $base_message . '<br />レポートが提出されていません。');
            }
            if (!empty($evaluate_report) && $evaluate_report->grade == 'D') {
                return array(false, $base_message . '<br />最新のレポートが不合格です。');
            }
        }

        // 試験の申込OK
        return array(true, '');
    }

    /**
     *  試験の評価を行えるか？
     */
    public function canExaminationEvaluate($post)
    {
        if (empty($post->id)) {
            return false;
        }
        // 試験の最新ステータスが提出済みか評価済み
        $last_examination_status = $this->examination_statuses->where('post_id', $post->id)->whereIn('task_status', [5, 6])->last();

        // 提出済み or 評価済みの最後を取得して、取得したものが提出済みの場合、評価がまだということになる。
        if (!empty($last_examination_status) && $last_examination_status->task_status == 5) {
            return true;
        }
        return false;
    }

    /**
     *  試験にコメントを行えるか？
     */
    public function canExaminationComment($post)
    {
        if (empty($post->id)) {
            return false;
        }
        // 試験の最新ステータスが提出済みか評価済み
        $last_examination_status = $this->examination_statuses->where('post_id', $post->id)->whereIn('task_status', [5, 6])->last();

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


    /**
     *  総合評価の状況取得
     */
    public function getEvaluateStatus($post_id)
    {
        $base_message = "総合評価は登録されていません。";

        if (empty($post_id)) {
            return $base_message;
        }
        if (empty($this->evaluate_statuses)) {
            return $base_message;
        }
        $evaluate_statuses = $this->evaluate_statuses->where('post_id', $post_id);
        $evaluate_status = $evaluate_statuses->where('task_status', 8)->last();
        if (!empty($evaluate_status)) {
            return $evaluate_status->grade;
        }
        return $base_message;
    }

    /**
     *  総合評価の履歴取得
     */
    public function getEvaluateStatuses($post_id)
    {
        if (empty($post_id)) {
            return $this->evaluate_statuses;
        }
        if (empty($this->evaluate_statuses)) {
            return new Collection();
        }
        return $this->evaluate_statuses->where('post_id', $post_id);
    }

    /**
     * 総合評価を行えるか？
     */
    public function canEvaluate($post_id)
    {
        if (empty($post_id)) {
            return false;
        }
        // レポートの最新ステータスが評価済みで合格
        $last_report_status = $this->report_statuses->where('post_id', $post_id)->whereIn('task_status', [2])->last();
        if (empty($last_report_status)) {
            return false;
        }
        if ($last_report_status->grade == 'D') {
            return false;
        }

        // 試験の最新ステータスが評価済みで合格
        $last_examination_status = $this->examination_statuses->where('post_id', $post_id)->whereIn('task_status', [6])->last();
        if (empty($last_examination_status)) {
            return false;
        }
        if ($last_examination_status->grade == 'D') {
            return false;
        }

        return true;
    }

    /**
     * 履歴削除は行えるか？
     */
    public function canDeletetableUserStatus($users_statuses_id)
    {
        // 課題管理者でない
        if (!$this->isLearningtaskAdmin()) {
            return false;
        }

        // 削除可能なユーザステータスID (最後のUsersStatusesのid)
        if ($users_statuses_id == $this->deletetable_users_statuses_id) {
            return true;
        }
        return false;
    }
}
