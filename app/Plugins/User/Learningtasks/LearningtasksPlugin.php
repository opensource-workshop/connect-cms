<?php

namespace App\Plugins\User\Learningtasks;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Plugins\User\Learningtasks\LearningtasksTool;

use App\Models\Core\UsersRoles;
use App\Models\Common\Buckets;
use App\Models\Common\Categories;
use App\Models\Common\PluginCategory;
use App\Models\Common\Frame;
use App\Models\Common\GroupUser;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Models\Common\Uploads;
use App\Models\User\Learningtasks\Learningtasks;
use App\Models\User\Learningtasks\LearningtasksConfigs;
use App\Models\User\Learningtasks\LearningtasksExaminations;
use App\Models\User\Learningtasks\LearningtasksPosts;
use App\Models\User\Learningtasks\LearningtasksPostsFiles;
use App\Models\User\Learningtasks\LearningtasksUsers;
use App\Models\User\Learningtasks\LearningtasksUsersStatuses;
use App\Models\User\Learningtasks\LearningtasksUseSettings;
use App\User;

use App\Mail\ConnectMail;
use App\Plugins\User\UserPluginBase;
use App\Utilities\Csv\CsvUtils;
use App\Utilities\String\StringUtils;
use App\Enums\CsvCharacterCode;
use App\Enums\LearningtasksExaminationColumn;
use App\Enums\LearningtaskUseFunction;
use App\Enums\RoleName;

use App\Rules\CustomValiWysiwygMax;

/**
 * 課題管理プラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 課題管理プラグイン
 * @package Controller
 */
class LearningtasksPlugin extends UserPluginBase
{
    /*
        task_status : 科目＆ユーザに対するアクションの履歴
        0 : 何もしていない状態。（レコードなしと同じ？）
        1 : レポートの課題提出（再提出も同じ。提出アクションが2つ目以降は再提出となるだけ）
        2 : レポートの評価（再評価も同じ）
        3 : レポートのコメント
        4 : 試験申し込み
        5 : 試験の解答提出（再提出も同じ。提出アクションが2つ目以降は再提出となるだけ）
        6 : 試験の評価（再評価も同じ）
        7 : 試験のコメント
        8 : 総合評価

        task_status の変更メソッドは 本体を private の changeStatusImpl() とする。
        各ステータス毎に public の入り口メソッドを持ち、権限チェックを行う。
        メソッド内では、科目に対するユーザの権限など、さらにチェックを行う。

        取り消し : 取り消しは各ステータス毎の public の入り口メソッドに Cancel をつけたメソッドを用意する。
                   権限チェックとログにアクションを残すため。
    */

    /* オブジェクト変数 */

    /**
     * 変更時のPOSTデータ
     */
    public $post = null;

    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = [
            'editMail',
            'editUsers',
            'editReport',
            'editExaminations',
            'editEvaluate',
            'listGrade',
            'switchUserUrl',
            'importExaminations',
        ];
        $functions['post'] = [
            'saveMail',
            'saveUsers',
            'saveReport',
            'saveExaminations',
            'saveEvaluate',
            'downloadGrade',
            'switchUser',
            'changeStatus1',
            'changeStatus2',
            'changeStatus3',
            'changeStatus4',
            'changeStatus5',
            'changeStatus6',
            'changeStatus7',
            'changeStatus8',
            'deleteStatus',
            'uploadCsvExaminations',
            'downloadCsvFormatExaminations',
            'downloadCsvExaminations',
        ];
        return $functions;
    }

    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_check_table = array();

        // プラグイン管理者
        $role_check_table["editMail"]         = array('role_arrangement');
        $role_check_table["saveMail"]         = array('role_arrangement');

        // コンテンツ管理者（科目の編集から飛べる処理）
        $role_check_table["edit"]             = array('role_article_admin');
        $role_check_table["editUsers"]        = array('role_article_admin');
        $role_check_table["editReport"]       = array('role_article_admin');
        $role_check_table["editExaminations"] = array('role_article_admin');
        $role_check_table["editEvaluate"]     = array('role_article_admin');
        $role_check_table["listGrade"]        = array('role_article_admin');

        $role_check_table["save"]             = array('role_article_admin');
        $role_check_table["saveUsers"]        = array('role_article_admin');
        $role_check_table["saveReport"]       = array('role_article_admin');
        $role_check_table["saveExaminations"] = array('role_article_admin');
        $role_check_table["importExaminations"] = array('role_article_admin');
        $role_check_table["uploadCsvExaminations"] = array('role_article_admin');
        $role_check_table["downloadCsvFormatExaminations"] = array('role_article_admin');
        $role_check_table["downloadCsvExaminations"] = array('role_article_admin');

        $role_check_table["saveEvaluate"]     = array('role_article_admin');
        $role_check_table["downloadGrade"]    = array('role_article_admin');
        $role_check_table["delete"]           = array('role_article_admin');
        $role_check_table["deleteStatus"]     = array('role_article_admin');

        $role_check_table["switchUser"]       = array('role_guest');
        $role_check_table["switchUserUrl"]    = array('role_guest');
        $role_check_table["changeStatus1"]    = array('role_guest');
        $role_check_table["changeStatus2"]    = array('role_guest');
        $role_check_table["changeStatus3"]    = array('role_guest');
        $role_check_table["changeStatus4"]    = array('role_guest');
        $role_check_table["changeStatus5"]    = array('role_guest');
        $role_check_table["changeStatus6"]    = array('role_guest');
        $role_check_table["changeStatus7"]    = array('role_guest');
        $role_check_table["changeStatus8"]    = array('role_guest');
        return $role_check_table;
    }

    /**
     *  編集画面の最初のタブ（コアから呼び出す）
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "editBuckets";
    }

    /**
     * POST取得関数（コアから呼び出す）
     * コアがPOSTチェックの際に呼び出す関数
     */
    public function getPost($id, $action = null)
    {
        // データ存在チェックのために getPost を利用

        // id がない場合は処理しない。
        if (empty($id)) {
            return null;
        }

        if (is_null($action)) {
            // プラグイン内からの呼び出しを想定。処理を通す。
        } elseif (in_array($action, ['edit', 'save', 'delete'])) {
            // コアから呼び出し。posts.update|posts.deleteの権限チェックを指定したアクションは、処理を通す。
        } else {
            // それ以外のアクションは null で返す。
            return null;
        }

        // 一度読んでいれば、そのPOSTを再利用する。
        if (!empty($this->post)) {
            return $this->post;
        }

        // データのグループ（contents_id）が欲しいため、指定されたID のPOST を読む
        // 履歴の廃止
        //$arg_post = LearningtasksPosts::where('id', $id)->first();

        // POST取得
        $learningtasks_query = LearningtasksPosts::
            select(
                'learningtasks_posts.*',
                'learningtasks.bucket_id',
                'categories.color as category_color',
                'categories.background_color as category_background_color',
                'categories.category as category',
                'plugin_categories.view_flag as category_view_flag'
            )
            // 論理削除を考慮
            ->join('learningtasks', function ($join) {
                $join->on('learningtasks.id', '=', 'learningtasks_posts.learningtasks_id')
                    ->whereNull('learningtasks.deleted_at');
            });
            // 履歴の廃止
            //->where('contents_id', $arg_post->contents_id)
            //->where(function ($query) {
            //      $query = $this->appendAuthWhere($query);
            //})
            // ->orderBy('id', 'desc')
            // ->first();

        // カテゴリのleftJoin
        $learningtasks_query = Categories::appendCategoriesLeftJoin($learningtasks_query, $this->frame->plugin_name, 'learningtasks_posts.categories_id', 'learningtasks.id');

        // 履歴最新を取得するために、idをdesc指定（履歴を廃止しても過去データのため必要かも）
        $this->post = $learningtasks_query->orderBy('id', 'desc')->firstOrNew(['learningtasks_posts.id' => $id]);

        return $this->post;
    }

    /* private関数 */

    /**
     *  紐づく課題管理ID とフレームデータの取得
     */
    private function getLearningTask($frame_id)
    {
        $learningtask = Learningtasks::select('learningtasks.*')
                                    ->join('frames', 'frames.bucket_id', '=', 'learningtasks.bucket_id')
                                    ->where('frames.id', $frame_id)
                                    ->first();

        // bugfix: 空の場合newする。課題管理未選択 & カテゴリ設定画面でエラーになるため。
        if (is_null($learningtask)) {
            $learningtask = new Learningtasks();
        }

        return $learningtask;

        // Frame データ
        //$frame = DB::table('frames')
        //         ->select('frames.*', 'learningtasks.id as learningtask_id', 'learningtasks.learningtasks_name', 'learningtasks.view_count', 'learningtasks.rss', 'learningtasks.rss_count', 'learningtasks.sequence_conditions')
        //         ->leftJoin('learningtasks', 'learningtasks.bucket_id', '=', 'frames.bucket_id')
        //         ->where('frames.id', $frame_id)
        //         ->first();
        //return $frame;
    }

    /**
     *  記事の取得権限に対する条件追加
     *  履歴の廃止
     */
    //private function appendAuthWhere($query)
    //{
    //    if ($this->isCan('role_article') || $this->isCan('role_article_admin')) {
    //        // 記事修正権限、コンテンツ管理者の場合、全件取得のため、追加条件なしで戻る。
    //    } elseif ($this->isCan('role_approval')) {
    //        // 承認権限の場合、Active ＋ 承認待ちの取得
    //        $query->Where('status', '=', 0)
    //              ->orWhere('status', '=', 2);
    //    } elseif ($this->isCan('role_reporter')) {
    //        // 編集者権限の場合、Active ＋ 自分の全ステータス記事の取得
    //        $query->Where('status', '=', 0)
    //              ->orWhere('learningtasks_posts.created_id', '=', Auth::user()->id);
    //    } else {
    //        // その他（ゲスト）
    //        $query->where('status', 0);
    //        $query->where('learningtasks_posts.posted_at', '<=', Carbon::now());
    //    }
    //
    //    return $query;
    //}

    /**
     * 課題管理記事一覧取得
     */
    private function getPosts($learningtasks_frame, $tool, $option_count = null)
    {
        $user = Auth::user();
        if (empty($user)) {
            $user_id = null;
        } else {
            $user_id = $user->id;
        }

        // 課題セットから、全課題（POST）を抽出
        // use_need_auth でログインの条件を加味、student_join_flag で受講の有無を加味して、
        // 抜き出したID で再度、詳細項目のデータ取得（ページングなども行うため）
        $learningtasks_posts = LearningtasksPosts::
            select(
                'learningtasks_posts.*',
                'parent_use_need_auth.value as parent_use_need_auth',
                'post_use_need_auth.value as post_use_need_auth',
                'student.role_name as student_role_name',
                'teacher.role_name as teacher_role_name'
            )
            ->leftJoin('learningtasks_use_settings as parent_use_need_auth', function ($join) {
                $join->on('parent_use_need_auth.learningtasks_id', '=', 'learningtasks_posts.learningtasks_id')
                     ->where('parent_use_need_auth.use_function', '=', 'use_need_auth')
                     ->where('parent_use_need_auth.post_id', '=', '0')
                     ->whereNull('parent_use_need_auth.deleted_at');
            })
            ->leftJoin('learningtasks_use_settings as post_use_need_auth', function ($join) {
                $join->on('post_use_need_auth.learningtasks_id', '=', 'learningtasks_posts.learningtasks_id')
                     ->on('post_use_need_auth.post_id', '=', 'learningtasks_posts.id')
                     ->where('post_use_need_auth.use_function', '=', 'use_need_auth')
                     ->whereNull('post_use_need_auth.deleted_at');
            })
            ->leftJoin('learningtasks_users as student', function ($join) use ($user_id) {
                $join->on('student.post_id', '=', 'learningtasks_posts.id')
                     ->where('student.user_id', '=', $user_id)
                     ->where('student.role_name', '=', RoleName::student)
                     ->whereNull('student.deleted_at');
            })
            ->leftJoin('learningtasks_users as teacher', function ($join) use ($user_id) {
                $join->on('teacher.post_id', '=', 'learningtasks_posts.id')
                     ->where('teacher.user_id', '=', $user_id)
                     ->where('teacher.role_name', '=', RoleName::teacher)
                     ->whereNull('teacher.deleted_at');
            })
            ->where('learningtasks_posts.learningtasks_id', $learningtasks_frame->id)
            ->get();

        // use_need_auth = on ：閲覧にはログインが必要
        // use_need_auth = off：非ログインでも閲覧可能

        // student_join_flag_2：配置ページのメンバーシップ受講者全員
        // student_join_flag_3：配置ページのメンバーシップ受講者から選ぶ
        $target_post_ids = array();

        foreach ($learningtasks_posts as $learningtasks_post) {
            // 課題管理者の場合は、全部、対象
            // if ($this->isCan('role_article')) {
            if ($tool->isLearningtaskAdmin()) {
                $target_post_ids[] = $learningtasks_post->id;
                continue;
            }

            if (empty($learningtasks_post->post_use_need_auth)) {
                // 親の設定に従う＆親では閲覧にはログインが必要＆ログインしていない ＝ 閲覧できない
                if ($learningtasks_post->parent_use_need_auth == 'on' && empty($user)) {
                    continue;
                }
            } else {
                // 閲覧にはログインが必要＆ログインしていない ＝ 閲覧できない
                if ($learningtasks_post->post_use_need_auth == 'on' && empty($user)) {
                    continue;
                }
            }

            // 「配置ページのメンバーシップ受講者から選ぶ」場合、自分の role が課題に設定されているか確認する。
            $student_flag = true;
            if ($learningtasks_post->student_join_flag == 3) {
                if ($tool->isStudent() && $learningtasks_post->student_role_name == RoleName::student) {
                    // OK
                } else {
                    // 閲覧対象外
                    $student_flag = false;
                }
            }
            $teacher_flag = true;
            if ($learningtasks_post->teacher_join_flag == 3) {
                if ($tool->isTeacher() && $learningtasks_post->teacher_role_name == RoleName::teacher) {
                    // OK
                } else {
                    // 閲覧対象外
                    $teacher_flag = false;
                }
            }
            if (!$student_flag && !$teacher_flag) {
                continue;
            }
            // 対象のPOST
            $target_post_ids[] = $learningtasks_post->id;
        }

        //$learningtasks_posts = null;

        // 件数
        $count = $learningtasks_frame->view_count;
        if ($option_count != null) {
            $count = $option_count;
        }

        // 削除されていないデータでグルーピングして、最新のIDで全件
        $learningtasks_posts = LearningtasksPosts::
            select(
                'learningtasks_posts.*',
                'categories.id as category_id',
                'categories.color as category_color',
                'categories.background_color as category_background_color',
                'categories.category as category',
                'plugin_categories.view_flag as category_view_flag'
            )
            // 履歴の廃止
            //                         ->whereIn('learningtasks_posts.id', function ($query) use ($learningtasks_frame) {
            //                             $query->select(DB::raw('MAX(id) As id'))
            //                                   ->from('learningtasks_posts')
            //                                   ->where('learningtasks_id', $learningtasks_frame->learningtasks_id)
            //                                   ->where('deleted_at', null)
            //                                   // 権限を見てWhere を付与する。
            //                                   ->where(function ($query_auth) {
            //                                       $query_auth = $this->appendAuthWhere($query_auth);
            //                                   })
            //                                   ->groupBy('categories.display_sequence')
            //                                   ->groupBy('contents_id');
            //                         });
            // 表示している課題セット
            ->where('learningtasks_id', $learningtasks_frame->id)

            // ユーザなど加味した対象のPOST
            ->whereIn('learningtasks_posts.id', $target_post_ids)

            // 有効なレコードのみ
            ->where('status', 0);

        // カテゴリのleftJoin
        $learningtasks_posts = Categories::appendCategoriesLeftJoin($learningtasks_posts, $this->frame->plugin_name, 'learningtasks_posts.categories_id', 'learningtasks_posts.learningtasks_id');

        // カテゴリソート条件追加
        $learningtasks_posts->orderBy('plugin_categories.display_sequence', 'asc');

        // 表示条件に対するソート条件追加

        if ($learningtasks_frame->sequence_conditions == 0) {
            // 最新順
            $learningtasks_posts->orderBy('posted_at', 'desc');
        } elseif ($learningtasks_frame->sequence_conditions == 1) {
            // 投稿順
            $learningtasks_posts->orderBy('posted_at', 'asc');
        } elseif ($learningtasks_frame->sequence_conditions == 2) {
            // 指定順
            $learningtasks_posts->orderBy('display_sequence', 'asc');
        }

       // 取得
        $learningtasks_posts_recored = $learningtasks_posts->orderBy('posted_at', 'desc')
                ->paginate($count, ["*"], "frame_{$learningtasks_frame->id}_page");

        return $learningtasks_posts_recored;
    }

    // /**
    //  *  タグの保存
    //  */
    // private function saveTag($request, $learningtasks_post, $old_learningtasks_post)
    // {
    //     // タグの保存
    //     if ($request->tags) {
    //         $tags = explode(',', $request->tags);
    //         foreach ($tags as $tag) {
    //             // 新規オブジェクト生成
    //             $learningtasks_posts_tags = new LearningtasksPostsTags();

    //             // タグ登録
    //             $learningtasks_posts_tags->created_id     = $learningtasks_post->created_id;
    //             $learningtasks_posts_tags->learningtasks_posts_id = $learningtasks_post->id;
    //             $learningtasks_posts_tags->tags           = $tag;
    //             $learningtasks_posts_tags->save();
    //         }
    //     }
    //     return;
    // }

    // /**
    //  *  タグのコピー
    //  */
    // private function copyTag($from_post, $to_post)
    // {
    //     // タグの保存
    //     $learningtasks_posts_tags = LearningtasksPostsTags::where('learningtasks_posts_id', $from_post->id)->orderBy('id', 'asc')->get();
    //     foreach ($learningtasks_posts_tags as $learningtasks_posts_tag) {
    //         $new_tag = $learningtasks_posts_tag->replicate();
    //         $new_tag->learningtasks_posts_id = $to_post->id;
    //         $new_tag->save();
    //     }

    //     return;
    // }

    /**
     * 課題ファイルの保存
     */
    private function saveTaskFile($request, $page_id, $post_id, $task_flag)
    {
        // 旧データがある場合は、履歴のためにコピーする。
        //if (!empty($old_learningtasks_post) && !empty($old_learningtasks_post->id)) {
        //    $this->copyTaskFile($request, $old_learningtasks_post, $learningtasks_post);
        //}

        // 課題ファイルがアップロードされた。
        if ($request->hasFile('add_task_file')) {
            // move: validatorは、各saveメソッドの手前のvalidatorでそれぞれチェックする
            // // Scratchを許可
            // $extension = $request->file('add_task_file')->getClientOriginalExtension();
            // if ($extension == 'sb2' || $extension == 'sb3') {
            //     // OK
            // } else {
            //     // ファイルチェック
            //     $validator = Validator::make($request->all(), [
            //         'add_task_file' => 'required|mimes:pdf,doc,docx',
            //     ]);
            //     $validator->setAttributeNames([
            //         'add_task_file' => '課題ファイル',
            //     ]);
            //     if ($validator->fails()) {
            //         // エラー時はエラー内容を引き継いで入力画面に戻る
            //         return redirect()->back()->withErrors($validator)->withInput();
            //     }
            // }

            // uploads テーブルに情報追加、ファイルのid を取得する
            $upload = Uploads::create([
                'client_original_name' => $request->file('add_task_file')->getClientOriginalName(),
                'mimetype'             => $request->file('add_task_file')->getClientMimeType(),
                'extension'            => $request->file('add_task_file')->getClientOriginalExtension(),
                'size'                 => $request->file('add_task_file')->getClientSize(),
                'plugin_name'          => 'learningtasks',
                'check_method'         => 'checkUploadPost',
                'page_id'              => $page_id,
            ]);

            // learningtasks_posts_files テーブルに情報追加
            $learningtasks_posts_files = LearningtasksPostsFiles::create([
                'post_id'   => $post_id,
                'task_flag' => $task_flag,
                'upload_id' => $upload->id,
            ]);

            // 課題ファイル保存
            $directory = $this->getDirectory($upload->id);
            $upload_path = $request->file('add_task_file')->storeAs($directory, $upload->id . '.' . $request->file('add_task_file')->getClientOriginalExtension());
        }
        return;
    }

    /**
     *  課題ファイルの削除
     */
    private function deleteTaskFile($request)
    {
        // 課題ファイルの削除が指示された。
        if ($request->filled('del_task_file')) {
            foreach ($request->del_task_file as $task_file_uploads_id => $value) {
                // 削除する課題ファイルのレコード取得
                $learningtasks_posts_file = LearningtasksPostsFiles::find($task_file_uploads_id);

                // アップロードテーブルの取得
                $upload = Uploads::find($learningtasks_posts_file->upload_id);

                // アップロードファイルの削除
                $directory = $this->getDirectory($upload->id);
                Storage::delete($directory . '/' . $upload->id . "." . $upload->extension);

                // アップロードテーブルの削除
                $upload->delete();

                // 課題ファイルテーブルの削除
                $learningtasks_posts_file->delete();
            }
        }
        return;
    }

    /**
     *  課題ファイル情報のコピー
     */
    //private function copyTaskFile($request, $from_post, $to_post)
    //{
    //    // 課題ファイル情報の保存
    //    $learningtasks_posts_files = LearningtasksPostsFiles::where('learningtasks_posts_id', $from_post->id)->orderBy('id', 'asc')->get();
    //    foreach ($learningtasks_posts_files as $learningtasks_posts_file) {
    //        // 削除対象のファイルはデータをコピーしない
    //        if ($request->del_task_file) {
    //            if (array_key_exists($learningtasks_posts_file->id, $request->del_task_file)) {
    //                continue;
    //            }
    //        }

    //        // レコードコピー
    //        $new_file = $learningtasks_posts_file->replicate();
    //        $new_file->learningtasks_posts_id = $to_post->id;
    //        $new_file->save();
    //    }

    //    return;
    //}

    /**
     *  紐づく課題ファイルの取得
     */
    private function getTaskFile($post_ids, $task_flag = 0)
    {
        // 課題ファイルテーブル
        $posts_files_db = LearningtasksPostsFiles::
                select(
                    'learningtasks_posts_files.*',
                    'uploads.id as uploads_id', 'uploads.client_original_name', 'uploads.download_count'
                )
                ->leftJoin('uploads', 'uploads.id', '=', 'learningtasks_posts_files.upload_id')
                ->whereIn('learningtasks_posts_files.post_id', $post_ids)
                ->where('learningtasks_posts_files.task_flag', $task_flag)
                ->get();

        // 課題ファイル詰めなおし（課題管理データの一覧にあてるための外配列）
        $learningtasks_posts_files = array();
        foreach ($posts_files_db as $record) {
            $learningtasks_posts_files[$record->post_id][] = $record;
        }

        return $learningtasks_posts_files;
    }

    /**
     *  紐づくユーザーstatusの取得
     */
    //private function getUserStatus($contents_ids)
    //{
    //    // ユーザ
    //    $user = Auth::user();
    //    if (empty($user)) {
    //        return null;
    //    }

    //    // ユーザーstatusテーブル
    //    $users_statuses
    //        = LearningtasksUsersStatuses::whereIn('learningtasks_users_statuses.contents_id', $contents_ids)
    //                                    ->where('user_id', '=', $user->id)
    //                                    ->get();

    //    // ユーザーstatusテーブル詰めなおし（課題管理データの一覧にあてるための配列）
    //    $learningtasks_users_statuses = array();
    //    foreach ($users_statuses as $record) {
    //        $learningtasks_users_statuses[$record->contents_id] = $record;
    //    }

    //    return $learningtasks_users_statuses;
    //}

    /* スタティック関数 */

    /**
     *  新着情報用メソッド
     */
    public static function getWhatsnewArgs()
    {

        // 戻り値('sql_method'、'link_pattern'、'link_base')

        $return[] = DB::table('learningtasks_posts')
                      ->select(
                          'frames.page_id              as page_id',
                          'frames.id                   as frame_id',
                          'learningtasks_posts.id              as post_id',
                          'learningtasks_posts.post_title      as post_title',
                          'learningtasks_posts.important       as important',
                          'learningtasks_posts.posted_at       as posted_at',
                          'learningtasks_posts.created_name    as posted_name',
                          'categories.classname        as classname',
                          'categories.category         as category',
                          DB::raw('"learningtasks" as plugin_name')
                      )
                      ->join('learningtasks', 'learningtasks.id', '=', 'learningtasks_posts.learningtasks_id')
                      ->join('frames', 'frames.bucket_id', '=', 'learningtasks.bucket_id')
                      ->leftJoin('categories', 'categories.id', '=', 'learningtasks_posts.categories_id')
                      ->where('status', 0)
                      ->where('disable_whatsnews', 0)
                      ->whereNull('learningtasks_posts.deleted_at');

        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/learningtasks/show';

        return $return;
    }

    /**
     *  検索用メソッド
     */
    public static function getSearchArgs($search_keyword)
    {
        $return[] = DB::table('learningtasks_posts')
                      ->select(
                          'learningtasks_posts.id              as post_id',
                          'frames.id                   as frame_id',
                          'frames.page_id              as page_id',
                          'pages.permanent_link        as permanent_link',
                          'learningtasks_posts.post_title      as post_title',
                          'learningtasks_posts.important       as important',
                          'learningtasks_posts.posted_at       as posted_at',
                          'learningtasks_posts.created_name    as posted_name',
                          'categories.classname        as classname',
                          'learningtasks_posts.categories_id   as categories_id',
                          'categories.category         as category',
                          DB::raw('"learningtasks" as plugin_name')
                      )
                      ->join('learningtasks', 'learningtasks.id', '=', 'learningtasks_posts.learningtasks_id')
                      ->join('frames', 'frames.bucket_id', '=', 'learningtasks.bucket_id')
                      ->leftJoin('categories', 'categories.id', '=', 'learningtasks_posts.categories_id')
                      ->leftjoin('pages', 'pages.id', '=', 'frames.page_id')
                      ->where('status', '?')
                      ->where(function ($plugin_query) use ($search_keyword) {
                          $plugin_query->where('learningtasks_posts.post_title', 'like', '?')
                                       ->orWhere('learningtasks_posts.post_text', 'like', '?');
                      })
                      ->whereNull('learningtasks_posts.deleted_at');


        $bind = array(0, '%'.$search_keyword.'%', '%'.$search_keyword.'%');
        $return[] = $bind;
        $return[] = 'show_page_frame_post';
        $return[] = '/plugin/learningtasks/show';

        return $return;
    }

    /**
     * 教員のタスク一覧
     */
    private function getTeacherTasks($tool, $posts)
    {
        if (!$tool->isTeacher()) {
            return null;
        }

        // tool の課題データは、課題が確定してからのものなので、一覧で表示する内容は独自にDB を見る。
        // posts はログインしている教員が見るべき課題に絞られているため、使用する。
        $teacher_tasks = array();
        foreach ($posts as $post) {
            $users_statuses_tmp = LearningtasksUsersStatuses::
                    select(
                        'learningtasks_users_statuses.*',
                        'users.id as user_id',
                        'learningtasks_posts.post_title',
                        'learningtasks_posts.id as post_id',
                        'users.name as user_name'
                    )
                    ->join('users', 'users.id', '=', 'learningtasks_users_statuses.user_id')
                    // bugfix: 論理削除を考慮
                    // ->join('learningtasks_posts', 'learningtasks_posts.id', '=', 'learningtasks_users_statuses.post_id')
                    ->join('learningtasks_posts', function ($join) {
                        $join->on('learningtasks_posts.id', '=', 'learningtasks_users_statuses.post_id')
                                ->whereNull('learningtasks_posts.deleted_at');
                    })
                    ->where('post_id', $post->id)
                    ->orderBy('id', 'asc')
                    ->get();

            // Collection の機能でユーザ毎に分割する。
            $users_statuses = $users_statuses_tmp->groupBy('user_id');

            foreach ($users_statuses as $users_status) {
                // レポート
                if ($tool->checkFunction('use_report', $post->id)) {
                    // レポートの評価が必要か。(レポートの提出と評価の最後を見る)
                    $last_report_task = $users_status->whereIn('task_status', [1, 2])->last();
                    // 最後が 1 なら、レポートの評価が必要
                    if (!empty($last_report_task) && $last_report_task->task_status == 1) {
                        $teacher_tasks[] = $last_report_task;
                    }
                }

                // 試験
                if ($tool->checkFunction('use_examination', $post->id)) {
                    // 試験の評価が必要か。(試験の提出と評価の最後を見る)
                    $last_examination_task = $users_status->whereIn('task_status', [5, 6])->last();
                    // 最後が 5 なら、試験の評価が必要
                    if (!empty($last_examination_task) && $last_examination_task->task_status == 5) {
                        $teacher_tasks[] = $last_examination_task;
                    }
                }

                // 総合評価
                if ($tool->checkFunction('use_evaluate', $post->id)) {
                    // 総合評価が必要か。(レポートが合格、試験が合格、総合評価なしの場合)
                    $last_evaluate_task = $users_status->whereIn('task_status', [8])->last();

                    // 上で取得したレポートのステータスが合格＆上で取得した試験のステータスが合格＆総合評価がまだない場合
                    if (!empty($last_report_task) && $last_report_task->task_status == 2 &&
                        ($last_report_task->grade == 'A' || $last_report_task->grade == 'B' || $last_report_task->grade == 'C') &&
                        !empty($last_examination_task) && $last_examination_task->task_status == 6 &&
                        ($last_examination_task->grade == 'A' || $last_examination_task->grade == 'B' || $last_examination_task->grade == 'C') &&
                        (empty($last_evaluate_task))) {
                        //(empty($last_evaluate_task) || $last_evaluate_task->isEmpty())) {

                        // 総合評価の条件に合致。ただし、この条件では、総合評価のデータはまだない。
                        // データがないと画面表示に際に判定できないため、試験結果をオブジェクトコピーし、ステータスを 8 にしておく。
                        $last_evaluate_task = clone $last_examination_task;
                        $last_evaluate_task->task_status = 8;
                        $teacher_tasks[] = $last_evaluate_task;
                    }
                }

            }
        }
        return $teacher_tasks;
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // 課題管理データ
        $learningtask = $this->getLearningTask($frame_id);
        if (empty($learningtask)) {
            return;
        }

        // （教員用）詳細から戻った時に student_id がセッションに残って、一覧表示がそのstudent_idの内容に変わるので消す。
        // if ($tool->isTeacher())で判定しようかと思ったが、LearningtasksToolのコンストラクタで、sessionのstudent_idがあると、
        // その受講者の内容でとってきてしまって表示が変わったため、使えなかった。
        session()->forget('student_id'. $frame_id);
        session()->forget('learningtask_post_id' . $frame_id);

        // ユーザー関連情報のまとめ
        $tool = new LearningtasksTool($request, $page_id, $learningtask, null, $frame_id);

        // 課題管理データ一覧の取得
        $posts = $this->getPosts($learningtask, $tool);

        // タグ：画面表示するデータのlearningtasks_posts_id を集める
        //$posts_ids = array();
        //foreach ($posts as $learningtasks_post) {
        //    $posts_ids[] = $learningtasks_post->id;
        //}

        // タグ：タグデータ取得
        //$learningtasks_posts_tags_row = LearningtasksPostsTags::whereIn('learningtasks_posts_id', $posts_ids)->get();

        // タグ：タグデータ詰めなおし（課題管理データの一覧にあてるための外配列）
        //$learningtasks_posts_tags = array();
        //foreach ($learningtasks_posts_tags_row as $record) {
        //    $learningtasks_posts_tags[$record->learningtasks_posts_id][] = $record->tags;
        //}

        // タグ：タグデータをポストデータに紐づけ
        //foreach ($learningtasks_posts as &$learningtasks_post) {
        //    if (array_key_exists($learningtasks_post->id, $learningtasks_posts_tags)) {
        //        $learningtasks_post->tags = $learningtasks_posts_tags[$learningtasks_post->id];
        //    }
        //}

        // 課題ファイルを取得
        $posts_files = $this->getTaskFile($posts->pluck('id'));

        // 課題ファイルをポストデータに紐づけ
        foreach ($posts as &$post) {
            if (array_key_exists($post->id, $posts_files)) {
                $post->task_files = $posts_files[$post->id];
            }
        }

        // ユーザーstatus：画面表示するデータのcontents_id を集める
        //$contents_ids = array();
        //foreach ($posts as $post) {
        //    $contents_ids[] = $post->contents_id;
        //}

        // 認証されているユーザの取得
        //$user = Auth::user();

        // ユーザーstatusテーブルを取得
        //$users_statuses = $this->getUserStatus($posts->pluck('id'));

        //// ユーザーstatusテーブルをポストデータに紐づけ
        //foreach ($posts as &$post) {
        //    if ($learningtasks_users_statuses && array_key_exists($post->contents_id, $learningtasks_users_statuses)) {
        //        $post->user_task_status = $learningtasks_users_statuses[$post->contents_id]->task_status;
        //        $post->upload_id = $learningtasks_users_statuses[$post->contents_id]->upload_id;
        //    }
        //}

        // カテゴリごとにまとめる＆カテゴリの配列も作る
        $categories_and_posts = array();
        $categories = array();
        foreach ($posts as $post) {
            // 表示しないカテゴリは、カテゴリIDを空にする
            if (! $post->category_view_flag) {
                $post->categories_id = '';
            }

            $categories_and_posts[$post->categories_id][] = $post;
            $categories[$post->categories_id] = $post;
        }

        // 教員のタスク一覧
        $teacher_tasks = $this->getTeacherTasks($tool, $posts);

        // 表示テンプレートを呼び出す。
        return $this->view('learningtasks', [
            'learningtask'         => $learningtask,
            'posts'                => $posts,
            'teacher_tasks'        => $teacher_tasks,
            'tool'                 => $tool,
            'categories_and_posts' => $categories_and_posts,
            'categories'           => $categories,
        ]);
    }

    /**
     * 新規記事画面
     */
    public function create($request, $page_id, $frame_id, $learningtasks_posts_id = null)
    {
        // セッション初期化などのLaravel 処理。
        //$request->flash();

        // 課題管理＆フレームデータ
        $learningtask = $this->getLearningTask($frame_id);

        // 空のデータ(画面で初期値設定で使用するため)
        $learningtasks_posts = new LearningtasksPosts();
        $learningtasks_posts->posted_at = date('Y-m-d H:i:00');

        // カテゴリ
        $learningtasks_categories = Categories::getInputCategories($this->frame->plugin_name, $learningtask->id);

        // タグ
        // $learningtasks_posts_tags = "";

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'learningtasks_input', [
            'learningtask'             => $learningtask,
            'learningtasks_posts'      => $learningtasks_posts,
            'learningtasks_categories' => $learningtasks_categories,
            // 'learningtasks_posts_tags' => $learningtasks_posts_tags,
            //'errors'           => $errors,
            ]
        )->withInput($request->all);
    }

    /**
     * 教員機能、ユーザ切り替え
     */
    public function switchUser($request, $page_id, $frame_id, $post_id)
    {
        // 課題管理＆フレームデータ
        $learningtask = $this->getLearningTask($frame_id);

        // ユーザー関連情報のまとめ
        $tool = new LearningtasksTool($request, $page_id, $learningtask, $this->getPost($post_id), $frame_id);

        // 教員 or 課題管理者
        if ($tool->isTeacher() || $tool->isLearningtaskAdmin()) {
            // 処理を進める
        } else {
            // bugfix: returnしてなかったため、ここに入っても処理が継続されていた。
            // $this->index($request, $page_id, $frame_id);

            // リダイレクトで詳細画面へ
            return;
        }

        // 受講生のID
        if (empty($request->student_id)) {
            session()->forget('student_id' . $frame_id);
        } else {
            session(['student_id' . $frame_id => $request->student_id]);
        }

        // 課題のIDもセッションに保持する。
        // 課題のIDが変わったら、受講生を選びなおす。
        session(['learningtask_post_id' . $frame_id => $post_id]);

        // リダイレクトで詳細画面へ
        return;
    }

    /**
     * 教員機能、URLでユーザ切り替え
     * URL例）http://localhost/redirect/plugin/learningtasks/switchUserUrl/3/16/5?student_id=2#frame-16
     */
    public function switchUserUrl($request, $page_id, $frame_id, $post_id)
    {
        // [debug]
        // dd($post_id, $request->student_id);

        // $page_id = (int) $page_id;  // 基本不要。この処理に到達前にページ存在チェックがコアで実行されるため。
        $frame_id = (int) $frame_id;
        $post_id = (int) $post_id;
        $student_id = (int) $request->student_id;

        if (empty($frame_id) || empty($post_id) || empty($student_id)) {
            abort(403, 'URLが正しくありません。');
        }

        // 課題管理＆フレームデータ
        $learningtask = $this->getLearningTask($frame_id);
        if (empty($learningtask->id)) {
            // idがなければ該当データなし
            abort(403, '対象のフレーム又は課題管理がありません。');
        }

        // ユーザー関連情報のまとめ
        $tool = new LearningtasksTool($request, $page_id, $learningtask, $this->getPost($post_id), $frame_id);
        // if (empty($tool->post)) {
        //     // postがなければ該当データなし
        //     abort(403, '対象の課題がありません。');
        // }

        // redirect_path。詳細ページ
        $redirect_path_array = [
            'redirect_path' => url('/') . "/plugin/" . $this->frame->plugin_name . "/show/" . $page_id . "/" . $frame_id . "/" . $post_id . "#frame-" . $frame_id
        ];

        // 教員 or 課題管理者
        if ($tool->isTeacher() || $tool->isLearningtaskAdmin()) {
            // 処理を進める
        } else {
            // 教員 or 課題管理者でない, 未ログイン（課題管理は基本メンバーシップぺージに配置するため、ログインしないと基本ページに到達しないのでほぼない想定）
            // リダイレクト
            return collect($redirect_path_array);
        }

        // 受講生のID
        session(['student_id' . $frame_id => $student_id]);

        // 課題のIDもセッションに保持する。
        // 課題のIDが変わったら、受講生を選びなおす。
        session(['learningtask_post_id' . $frame_id => $post_id]);

        // リダイレクト
        return collect($redirect_path_array);
    }

    /**
     * 詳細表示関数
     */
    public function show($request, $page_id, $frame_id, $post_id)
    {
        // 課題のIDが変わったら、受講生を選びなおす。
        if (session('learningtask_post_id' . $frame_id) != $post_id) {
            session()->forget('student_id' . $frame_id);
            session()->forget('learningtask_post_id' . $frame_id);
        }

        // 課題管理
        $learningtask = $this->getLearningTask($frame_id);

        // 記事取得
        $post = $this->getPost($post_id);
        if (empty($post->id)) {
            return $this->viewError("403_inframe", null, 'showのユーザー権限に応じたPOST ID チェック');
        }

        // 課題の添付ファイル（学習指導書など）を取得
        $post_files = LearningtasksPostsFiles::
                select(
                    'learningtasks_posts_files.*',
                    'uploads.id as uploads_id', 'uploads.client_original_name'
                )
                ->leftJoin('uploads', 'uploads.id', '=', 'learningtasks_posts_files.upload_id')
                ->where('post_id', $post->id)
                ->where('task_flag', 0)
                ->get();

        // 試験の添付ファイル（試験問題、解答用ファイルなど）を取得
        $examination_files = LearningtasksPostsFiles::
                select(
                    'learningtasks_posts_files.*',
                    'uploads.id as uploads_id', 'uploads.client_original_name'
                )
                ->leftJoin('uploads', 'uploads.id', '=', 'learningtasks_posts_files.upload_id')
                ->where('post_id', $post->id)
                ->where('task_flag', 1)
                ->get();

        // 試験情報(申し込み可能な分 = 終了日時が現在より後のもの＋申込終了日時がないもの or 申込終了日時が現在より後のもの)
        $examinations = LearningtasksExaminations::where('post_id', $post->id)
                ->where('entry_end_at', '>', date('Y-m-d H:i:s'))
                ->orWhere(function ($query) use ($post) {
                    $query->where('post_id', $post->id)
                            ->where('end_at', '>', date('Y-m-d H:i:s'))
                            ->whereNull('entry_end_at');
                })
                ->orderBy('start_at', 'asc')
                ->get();

        // ユーザー関連情報のまとめ
        $tool = new LearningtasksTool($request, $page_id, $learningtask, $post, $frame_id);

        // 詳細画面を呼び出す。
        return $this->view('learningtasks_show', [
            'learningtask' => $learningtask,
            'post' => $post,
            'post_files' => $post_files,
            'examination_files' => $examination_files,
            'examinations' => $examinations,
            'tool' => $tool,
        ]);
    }

    /**
     * 記事編集画面
     */
    public function edit($request, $page_id, $frame_id, $learningtasks_posts_id = null)
    {
        // セッション初期化などのLaravel 処理。
        //$request->flash();

        // 課題管理データ
        $learningtask = $this->getLearningTask($frame_id);

        // 記事取得
        $learningtasks_post = $this->getPost($learningtasks_posts_id);
        if (empty($learningtasks_post->id)) {
            return $this->viewError("403_inframe", null, 'editのユーザー権限に応じたPOST ID チェック');
        }

        // カテゴリ
        $learningtasks_categories = Categories::getInputCategories($this->frame->plugin_name, $learningtask->id);

        // タグ取得
        // $learningtasks_posts_tags_array = LearningtasksPostsTags::where('learningtasks_posts_id', $learningtasks_post->id)->get();
        // $learningtasks_posts_tags = "";
        // foreach ($learningtasks_posts_tags_array as $learningtasks_posts_tags_item) {
        //     $learningtasks_posts_tags .= ',' . $learningtasks_posts_tags_item->tags;
        // }
        // $learningtasks_posts_tags = trim($learningtasks_posts_tags, ',');

        // 課題管理データを取得
        $learningtasks_posts_files = $this->getTaskFile([$learningtasks_post->id]);

        // 変更画面を呼び出す。
        return $this->view('learningtasks_input', [
            'learningtask' => $learningtask,
            'learningtasks_posts' => $learningtasks_post,
            'learningtasks_categories' => $learningtasks_categories,
            // 'learningtasks_posts_tags' => $learningtasks_posts_tags,
            'learningtasks_posts_files' => (array_key_exists($learningtasks_post->id, $learningtasks_posts_files)) ? $learningtasks_posts_files[$learningtasks_post->id] : null,
        ]);
    }

    /**
     * レポート関係編集画面
     */
    public function editReport($request, $page_id, $frame_id, $post_id = null)
    {
        // 課題管理データ
        $learningtask = $this->getLearningTask($frame_id);

        // 記事取得
        $learningtasks_posts = $this->getPost($post_id);
        if (empty($learningtasks_posts->id)) {
            return $this->viewError("403_inframe", null, 'editのユーザー権限に応じたPOST ID チェック');
        }

        // ツールクラス
        $tool = new LearningtasksTool($request, $page_id, $learningtask, $learningtasks_posts, $frame_id);

        // 編集画面
        return $this->view(
            'learningtasks_edit_report', [
            'learningtask'        => $learningtask,
            'learningtasks_posts' => $learningtasks_posts,
            'tool'                => $tool,
            ]
        );
    }

    /**
     * レポート関係保存処理
     */
    public function saveReport($request, $page_id, $frame_id, $post_id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'post_settings.report_end_at' => ['nullable', 'date_format:"Y-m-d H:i"', Rule::requiredIf($request->input('post_settings.use_report_end') == 'on')],
        ]);
        $validator->setAttributeNames([
            'post_settings.use_report_end' => '以下の提出終了日時で制御する',
            'post_settings.report_end_at' => '提出終了日時',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 対象の課題特定
        $post = LearningtasksPosts::find($post_id);
        if (empty($post)) {
            return $this->editReport($request, $page_id, $frame_id, $post_id);
        }

        // 設定内容を保存（一旦削除して新たに保存）
        // change: deleted_id, deleted_nameを自動セットするため、複数件削除する時は collectionのpluck('id')でid配列を取得して destroy()で消す。
        // LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
        //         ->where('post_id', $post->id)
        //         ->where('use_function', 'post_report_setting')
        //         ->delete();
        // LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
        //         ->where('post_id', $post->id)
        //         ->where('use_function', 'like', 'use_report%')
        //         ->delete();
        // LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
        //         ->where('post_id', $post->id)
        //         ->where('use_function', LearningtaskUseFunction::report_end_at)
        //         ->delete();
        $learningtasks_use_settings_ids = LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
                ->where('post_id', $post->id)
                ->where('use_function', 'post_report_setting')
                ->pluck('id');
        $learningtasks_use_settings_ids2 = LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
                ->where('post_id', $post->id)
                ->where('use_function', 'like', 'use_report%')
                ->pluck('id');
        $learningtasks_use_settings_ids3 = LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
                ->where('post_id', $post->id)
                ->where('use_function', LearningtaskUseFunction::report_end_at)
                ->pluck('id');

        // Collectionマージ
        $del_learningtasks_use_settings_ids = $learningtasks_use_settings_ids->merge($learningtasks_use_settings_ids2)->merge($learningtasks_use_settings_ids3);
        LearningtasksUseSettings::destroy($del_learningtasks_use_settings_ids);

        if ($request->filled('post_report_setting')) {
            LearningtasksUseSettings::create([
                'learningtasks_id' => $post->learningtasks_id,
                'post_id'          => $post->id,
                'use_function'     => 'post_report_setting',
                'value'            => $request->post_report_setting,
            ]);
        }
        if ($request->filled('post_settings')) {
            $post_settings = $request->post_settings;
            foreach ($post_settings as $post_setting_key => $post_setting_value) {
                if (strpos($post_setting_key, 'use_report') === 0) {
                    if ($post_setting_value == "on" || $post_setting_value == "off") {
                        LearningtasksUseSettings::create([
                            'learningtasks_id' => $post->learningtasks_id,
                            'post_id' => $post->id,
                            'use_function' => $post_setting_key,
                            'value' => $post_setting_value,
                        ]);
                    }
                } elseif (LearningtasksUseSettings::isDatetimeUseFunction($post_setting_key) && $post_setting_value) {
                    // 日付を使う機能 + 値あり
                    LearningtasksUseSettings::create([
                        'learningtasks_id' => $post->learningtasks_id,
                        'post_id' => $post->id,
                        'use_function' => $post_setting_key,
                        'datetime_value' => $post_setting_value . ':00',
                    ]);
                }
            }
        }

        // delete: redirect_path指定で画面戻るため、下記不要
        // return $this->editReport($request, $page_id, $frame_id, $post_id);
    }

    /**
     * 総合評価関係編集画面
     */
    public function editEvaluate($request, $page_id, $frame_id, $post_id = null)
    {
        // 課題管理データ
        $learningtask = $this->getLearningTask($frame_id);

        // 記事取得
        $learningtasks_posts = $this->getPost($post_id);
        if (empty($learningtasks_posts->id)) {
            return $this->viewError("403_inframe", null, 'editのユーザー権限に応じたPOST ID チェック');
        }

        // ツールクラス
        $tool = new LearningtasksTool($request, $page_id, $learningtask, $learningtasks_posts, $frame_id);

        // 編集画面
        return $this->view(
            'learningtasks_edit_evaluate', [
            'learningtask'        => $learningtask,
            'learningtasks_posts' => $learningtasks_posts,
            'tool'                => $tool,
            ]
        );
    }

    /**
     * 総合評価関係保存処理
     */
    public function saveEvaluate($request, $page_id, $frame_id, $post_id)
    {
        // 対象の課題特定
        $post = LearningtasksPosts::find($post_id);
        if (empty($post)) {
            return $this->editEvaluate($request, $page_id, $frame_id, $post_id);
        }

        // 設定内容を保存（一旦削除して新たに保存）
        // change: deleted_id, deleted_nameを自動セットするため、複数件削除する時は collectionのpluck('id')でid配列を取得して destroy()で消す。
        // LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
        //                         ->where('post_id', $post->id)
        //                         ->where('use_function', 'post_evaluate_setting')
        //                         ->delete();
        // LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
        //                         ->where('post_id', $post->id)
        //                         ->where('use_function', 'like', 'use_evaluate%')
        //                         ->delete();
        $learningtasks_use_settings_ids = LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
                ->where('post_id', $post->id)
                ->where('use_function', 'post_evaluate_setting')
                ->pluck('id');
        $learningtasks_use_settings_ids2 = LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
                ->where('post_id', $post->id)
                ->where('use_function', 'like', 'use_evaluate%')
                ->pluck('id');

        // Collectionマージ
        $del_learningtasks_use_settings_ids = $learningtasks_use_settings_ids->merge($learningtasks_use_settings_ids2);
        LearningtasksUseSettings::destroy($del_learningtasks_use_settings_ids);

        if ($request->filled('post_evaluate_setting')) {
            LearningtasksUseSettings::create([
                'learningtasks_id' => $post->learningtasks_id,
                'post_id'          => $post->id,
                'use_function'     => 'post_evaluate_setting',
                'value'            => $request->post_evaluate_setting,
            ]);
        }
        if ($request->filled('post_settings')) {
            $post_settings = $request->post_settings;
            foreach ($post_settings as $post_setting_key => $post_setting_value) {
                if (strpos($post_setting_key, 'use_evaluate') === 0) {
                    if ($post_setting_value == "on" || $post_setting_value == "off") {
                        LearningtasksUseSettings::create([
                            'learningtasks_id' => $post->learningtasks_id,
                            'post_id'          => $post->id,
                            'use_function'     => $post_setting_key,
                            'value'            => $post_setting_value,
                        ]);
                    }
                }
            }
        }

        return $this->editEvaluate($request, $page_id, $frame_id, $post_id);
    }

    /**
     * 試験関係編集画面
     */
    public function editExaminations($request, $page_id, $frame_id, $post_id = null)
    {
        // セッション初期化などのLaravel 処理。
        //$request->flash();

        // 課題管理データ
        $learningtask = $this->getLearningTask($frame_id);

        // 記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
        $learningtasks_post = $this->getPost($post_id);
        if (empty($learningtasks_post->id)) {
            return $this->viewError("403_inframe", null, 'editのユーザー権限に応じたPOST ID チェック');
        }

        // タグ取得
        //$learningtasks_posts_tags_array = LearningtasksPostsTags::where('post_id', $learningtasks_post->id)->get();
        //$learningtasks_posts_tags = "";
        //foreach ($learningtasks_posts_tags_array as $learningtasks_posts_tags_item) {
        //    $learningtasks_posts_tags .= ',' . $learningtasks_posts_tags_item->tags;
        //}
        //$learningtasks_posts_tags = trim($learningtasks_posts_tags, ',');

        // 課題管理データを取得
        $post_files = $this->getTaskFile([$learningtasks_post->id], 1);

        // 試験設定データを取得
        $examinations = LearningtasksExaminations::where('post_id', $learningtasks_post->id)
                ->orderBy('start_at', 'asc')
                ->get();

        // ツールクラス
        $tool = new LearningtasksTool($request, $page_id, $learningtask, $learningtasks_post, $frame_id);

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'learningtasks_edit_examinations', [
            'learningtask'              => $learningtask,
            'learningtasks_posts'       => $learningtasks_post,
            //'learningtasks_posts_tags'  => $learningtasks_posts_tags,
            'post_files'                => (array_key_exists($learningtasks_post->id, $post_files)) ? $post_files[$learningtasks_post->id] : null,
            'examinations'              => $examinations,
            'tool'                      => $tool,
            ]
        );
    }

    /**
     * 成績表示（管理者用）
     */
    public function listGrade($request, $page_id, $frame_id, $post_id)
    {
        // 課題管理データ
        $learningtask = $this->getLearningTask($frame_id);

        // 課題
        $learningtasks_post = $this->getPost($post_id);

        // 成績の配列取得
        $statuses = $this->downloadGradeImpl($request, $page_id, $frame_id, $post_id);

        // 画面を呼び出す。
        return $this->view(
            'learningtasks_list_grade', [
            'learningtask'        => $learningtask,
            'learningtasks_posts' => $learningtasks_post,
            'statuses'            => $statuses,
            ]
        );
    }

    /**
     * 成績ダウンロード（管理者用）
     */
    public function downloadGrade($request, $page_id, $frame_id, $post_id)
    {
        // 課題管理データ
        $learningtask = $this->getLearningTask($frame_id);

        // 課題
        $learningtasks_post = $this->getPost($post_id);

        // 成績の配列取得
        $statuses = $this->downloadGradeImpl($request, $page_id, $frame_id, $post_id);

        // CSV で出力
        $stream = fopen('php://temp', 'r+b');
        foreach ($statuses as $user) {
            fputcsv($stream, $user);
        }
        rewind($stream);
        $csv = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $clear_str = ["\r\n", "\r", "\n", "\t"];
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . str_replace($clear_str, '', strip_tags($learningtasks_post->post_title)) . '（成績）.csv"',
        );
        return Response::make($csv, 200, $headers);
    }

    /**
     * 成績ダウンロード（管理者用）
     */
    private function downloadGradeImpl($request, $page_id, $frame_id, $post_id)
    {
        // 成績
        $users_statuses = LearningtasksUsersStatuses::
                select(
                    'learningtasks_users_statuses.*',
                    'learningtasks_posts.post_title',
                    'users.name'
                )
                // bugfix: 論理削除を考慮
                // ->join('learningtasks_posts', 'learningtasks_posts.id', '=', 'learningtasks_users_statuses.post_id')
                ->join('learningtasks_posts', function ($join) {
                    $join->on('learningtasks_posts.id', '=', 'learningtasks_users_statuses.post_id')
                            ->whereNull('learningtasks_posts.deleted_at');
                })
                ->leftJoin('users', 'users.id', '=', 'learningtasks_users_statuses.user_id')
                ->where('learningtasks_users_statuses.post_id', $post_id)
                ->orderBy('learningtasks_users_statuses.id', 'asc')
                ->get();

        // 成績ステータス毎に、最終のものを抜き出す。（task_status で上書きすることで最後が残る）
        $statuses_ojb = array();
        foreach ($users_statuses as $users_status) {
            $statuses_ojb[$users_status->user_id][$users_status->task_status] = $users_status;
        }

        // 表（含むCSV）のフォーマットに詰めなおす
        $statuses = array();
        foreach ($statuses_ojb as $user_id => $status_ojbs) {
            $statuses[$user_id][0] = array_key_exists(1, $status_ojbs) ? $status_ojbs[1]->post_title : '－';
            $statuses[$user_id][1] = array_key_exists(1, $status_ojbs) ? $status_ojbs[1]->name       : '－';
            $statuses[$user_id][2] = array_key_exists(1, $status_ojbs) ? $status_ojbs[1]->created_at : '－';
            $statuses[$user_id][3] = array_key_exists(2, $status_ojbs) ? $status_ojbs[2]->grade      : '－';
            $statuses[$user_id][4] = array_key_exists(5, $status_ojbs) ? $status_ojbs[5]->created_at : '－';
            $statuses[$user_id][5] = array_key_exists(6, $status_ojbs) ? $status_ojbs[6]->grade      : '－';
            $statuses[$user_id][6] = array_key_exists(8, $status_ojbs) ? $status_ojbs[8]->grade      : '－';
        }
        $csvHeader = ['課題名', '受講者名', 'レポート提出最終日時', 'レポート評価', '試験提出最終日時', '試験評価', '総合評価'];
        array_unshift($statuses, $csvHeader);

        return $statuses;
    }

    /**
     * 課題管理記事登録処理
     */
    public function save($request, $page_id, $frame_id, $post_id = null)
    {
        // 項目のエラーチェック
        $validate_value = [
            'post_title' => ['required', 'max:255'],
            'posted_at' => ['required', 'date_format:Y-m-d H:i'],
            'post_text' => ['required', new CustomValiWysiwygMax()],
        ];

        $validate_attribute = [
            'post_title' => 'タイトル',
            'posted_at' => '投稿日時',
            'post_text' => '本文',
        ];

        // 課題ファイルがアップロードされた。
        if ($request->hasFile('add_task_file')) {
            // Scratchを許可. アップロードされないと拡張子判定できない。
            $extension = $request->file('add_task_file')->getClientOriginalExtension();
            if ($extension == 'sb2' || $extension == 'sb3') {
                // OK
            } else {
                // ファイルチェック
                $validate_value['add_task_file'] = ['required', 'mimes:pdf,doc,docx'];
                $validate_attribute['add_task_file'] = '課題ファイル';
            }
        }

        $request->merge([
            // 表示順:  全角→半角変換
            "display_sequence" => StringUtils::convertNumericAndMinusZenkakuToHankaku($request->display_sequence),
        ]);

        // エラーチェック
        $validator = Validator::make($request->all(), $validate_value);
        $validator->setAttributeNames($validate_attribute);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
            //if ($post_id) {
            //    return ( $this->edit($request, $page_id, $frame_id, $post_id, $validator->errors()) );
            //} else {
            //    return ( $this->create($request, $page_id, $frame_id, $post_id, $validator->errors()) );
            //}
        }

        // id があれば旧データを取得＆権限を加味して更新可能データかどうかのチェック
        //$old_learningtasks_post = null;
        //if (!empty($learningtasks_posts_id)) {
        //    // 指定されたID のデータ
        //    $old_learningtasks_post = LearningtasksPosts::where('id', $learningtasks_posts_id)->first();

        //    // 記事取得（指定されたPOST ID そのままではなく、権限に応じたPOST を取得する。）
        //    $check_learningtasks_post = $this->getPost($learningtasks_posts_id);

        //    // 指定されたID と権限に応じたPOST のID が異なる場合は、キーを捏造したPOST と考えられるため、エラー
        //    if (empty($check_learningtasks_post) || $check_learningtasks_post->id != $old_learningtasks_post->id) {
        //        return $this->viewError("403_inframe", null, 'saveのユーザー権限に応じたPOST ID チェック');
        //    }
        //}

        // オブジェクト取得 or 生成
        if (empty($post_id)) {
            $post = new LearningtasksPosts();
        } else {
            $post = LearningtasksPosts::firstOrNew(['id' => $post_id]);
        }

        // 表示順が空なら、自分を省いた最後の番号+1 をセット
        $display_sequence = $this->getSaveDisplaySequence($request->display_sequence, $request->learningtask_id, $post_id);

        // 課題管理記事設定
        $post->learningtasks_id = $request->learningtask_id;
        $post->post_title       = $request->post_title;
        $post->categories_id    = $request->categories_id;
        $post->important        = $request->important;
        $post->posted_at        = $request->posted_at . ':00';
        $post->post_text        = $this->clean($request->post_text);   // wysiwygのXSS対応のJavaScript等の制限
        $post->display_sequence = $display_sequence;
        $post->save();

        if (empty($post_id)) {
            // 登録
            $request->flash_message = '課題を追加しました。<br />' .
                '　 [ <a href="' . url('/') . '/plugin/learningtasks/editUsers/' . $page_id . '/' . $frame_id . '/' . $post->id . '/#frame-' . $frame_id . '">参加設定</a> ]から参加する受講生と教員を設定してください。';
        } else {
            // 更新
            $request->flash_message = '課題を変更しました。';
        }

        //if (empty($learningtasks_posts_id)) {
        //    // 新規
        //    // 登録ユーザ
        //    $learningtasks_post->created_id  = Auth::user()->id;

        //    // データ保存
        //    $learningtasks_post->save();

        //    // 新規登録の場合、contents_id を最初のレコードのid と同じにする。
        //    LearningtasksPosts::where('id', $learningtasks_post->id)->update(['contents_id' => $learningtasks_post->id]);
        //} else {
        //    // 更新
        //    // 変更処理の場合、contents_id を旧レコードのcontents_id と同じにする。
        //    $learningtasks_post->contents_id = $old_learningtasks_post->contents_id;

        //    // 登録ユーザ
        //    $learningtasks_post->created_id  = $old_learningtasks_post->created_id;

        //    // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)ただし、承認待ちレコード作成時は対象外
        //    if ($learningtasks_post->status != 2) {
        //        LearningtasksPosts::where('contents_id', $old_learningtasks_post->contents_id)->where('status', 0)->update(['status' => 9]);
        //    }

        //    // データ保存
        //    $learningtasks_post->save();
        //}

        //// タグの保存
        //$this->saveTag($request, $learningtasks_post, $old_learningtasks_post);

        // 課題ファイルの保存
        $this->saveTaskFile($request, $page_id, $post->id, 0);

        // 課題ファイルの削除
        $this->deleteTaskFile($request);

        // 登録後はリダイレクト処理を呼ぶため、ここでは、view は呼ばない。
        // 新規登録後は、登録したデータの edit 画面を開きたいため、フォームで指定したリクエストの redirect_path を置き換える。
        $request->merge(['redirect_path' => url('/') . '/plugin/learningtasks/edit/' . $page_id . '/' . $frame_id . '/' . $post->id . '#frame-' . $frame_id]);

        // 登録後は表示用の初期処理を呼ぶ。
        //return $this->index($request, $page_id, $frame_id);
    }

    /**
     * 登録する表示順を取得
     */
    private function getSaveDisplaySequence($display_sequence, $learningtask_id, $id)
    {
        // 表示順が空なら、自分を省いた最後の番号+1 をセット
        if (!is_null($display_sequence)) {
            $display_sequence = intval($display_sequence);
        } else {
            $max_display_sequence = LearningtasksPosts::where('learningtasks_id', $learningtask_id)->where('id', '<>', $id)->max('display_sequence');
            $display_sequence = empty($max_display_sequence) ? 1 : $max_display_sequence + 1;
        }
        return $display_sequence;
    }

    /**
     * 削除処理
     */
    public function delete($request, $page_id, $frame_id, $post_id)
    {
        // 課題データ取得
        $learningtasks_posts = LearningtasksPosts::find($post_id);
        if (empty($learningtasks_posts)) {
            // 表示用の初期処理を呼ぶ。
            return $this->index($request, $page_id, $frame_id);
        }

        ////
        //// 添付ファイル及びデータ削除
        ////
        //   learningtasks_posts_files 課題の添付ファイル（学習指導書など）task_flag=0
        //                               試験の添付ファイル（試験問題、解答用ファイルなど）task_flag=1 取得
        $learningtasks_posts_files = LearningtasksPostsFiles::where('post_id', $post_id);
        $learningtasks_posts_files_upload_ids = $learningtasks_posts_files->pluck('upload_id');

        // learningtasks_users_statuses 成績 取得
        $learningtasks_users_statuses = LearningtasksUsersStatuses::where('post_id', $post_id);
        $learningtasks_users_statuses_upload_ids = $learningtasks_users_statuses->pluck('upload_id');

        // dd($learningtasks_posts_files_upload_ids, $learningtasks_users_statuses_upload_ids);
        // Collectionマージ
        $del_upload_ids = $learningtasks_posts_files_upload_ids->merge($learningtasks_users_statuses_upload_ids);
        // dd($del_upload_ids);

        // uploads 削除
        // 削除するファイルデータ (もし重複IDあったとしても、in検索によって排除される)
        $delete_uploads = Uploads::whereIn('id', $del_upload_ids)->get();
        foreach ($delete_uploads as $delete_upload) {
            // ファイルの削除
            $directory = $this->getDirectory($delete_upload->id);
            Storage::delete($directory . '/' . $delete_upload->id . '.' .$delete_upload->extension);

            // uploadの削除
            $delete_upload->delete();
        }

        ////
        //// 課題データ削除
        ////
        // learningtasks_examinations 試験日データ削除
        $learningtasks_examinations_ids = LearningtasksExaminations::where('post_id', $post_id)->pluck('id');
        LearningtasksExaminations::destroy($learningtasks_examinations_ids);

        // learningtasks_users_statuses 成績 削除
        $learningtasks_users_statuses_ids = $learningtasks_users_statuses->pluck('id');
        LearningtasksUsersStatuses::destroy($learningtasks_users_statuses_ids);

        // learningtasks_users 参加設定（受講者・教員）削除
        $learningtasks_users_ids = LearningtasksUsers::where('post_id', $post_id)->pluck('id');
        LearningtasksUsers::destroy($learningtasks_users_ids);

        // 課題の添付ファイルのテーブルデータ 削除
        $learningtasks_posts_files_ids = $learningtasks_posts_files->pluck('id');
        LearningtasksPostsFiles::destroy($learningtasks_posts_files_ids);

        // 課題データを削除する。
        $learningtasks_posts->delete();


        // 削除後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     * データ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $learningtasks_frame = Frame::select('frames.*', 'learningtasks.id as learningtasks_id', 'learningtasks.view_count')
                // bugfix: 論理削除を考慮
                // ->leftJoin('learningtasks', 'learningtasks.bucket_id', '=', 'frames.bucket_id')
                ->leftJoin('learningtasks', function ($join) {
                    $join->on('learningtasks.bucket_id', '=', 'frames.bucket_id')
                            ->whereNull('learningtasks.deleted_at');
                })
                ->where('frames.id', $frame_id)->first();

        // データ取得（1ページの表示件数指定）
        $learningtasks = Learningtasks::orderBy('created_at', 'desc')
                       ->paginate(10, ["*"], "frame_{$frame_id}_page");

        // 表示テンプレートを呼び出す。
        return $this->view('learningtasks_list_buckets', [
            'learningtasks_frame' => $learningtasks_frame,
            'learningtasks' => $learningtasks,
        ]);
    }

    /**
     * 課題管理新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $learningtask_id = null)
    {
        // 新規作成フラグを付けて課題管理設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $learningtask_id, $create_flag);
    }

    /**
     * 課題管理設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $learningtask_id = null, $create_flag = false)
    {
        // セッション初期化などのLaravel 処理。
        //$request->flash();

        // Frame
        $frame = Frame::find($frame_id);

        // 課題管理データ
        $learningtask = new Learningtasks();

        if (!empty($learningtask_id)) {
            // learningtask_id が渡ってくればlearningtask_id が対象
            $learningtask = Learningtasks::where('id', $learningtask_id)->first();
        } elseif (!empty($frame->bucket_id) && $create_flag == false) {
            // Frame のbucket_id があれば、bucket_id から課題管理データ取得、なければ、新規作成か選択へ誘導
            $learningtask = Learningtasks::where('bucket_id', $frame->bucket_id)->first();
        }

        // 課題設定
        //$base_settings = LearningtasksUseSettings::where('learningtasks_id', $learningtask->id)->where('post_id', 0)->get();

        // ユーザー関連情報のまとめ
        $tool = new LearningtasksTool($request, $page_id, $learningtask, null, $frame_id);

        // 表示テンプレートを呼び出す。
        return $this->view('learningtasks_edit_learningtasks', [
            'learningtask'  => $learningtask,
            //'base_settings' => $base_settings,
            'tool'          => $tool,
            'create_flag'   => $create_flag,
        ]);
    }

    /**
     *  課題管理登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $learningtask_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'learningtasks_name' => ['required'],
            'view_count' => ['required', 'numeric'],
            'sequence_conditions' => ['nullable', 'numeric'],
            'base_settings.report_end_at' => ['nullable', 'date_format:"Y-m-d H:i"', Rule::requiredIf($request->input('base_settings.use_report_end') == 'on')],
        ]);
        $validator->setAttributeNames([
            'learningtasks_name' => '課題管理名',
            'view_count' => '表示件数',
            'sequence_conditions' => '順序条件',
            'base_settings.use_report_end' => '以下の提出終了日時で制御する',
            'base_settings.report_end_at' => '提出終了日時',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if (empty($request->learningtask_id)) {
            // 画面から渡ってくるlearningtask_id が空ならバケツと課題管理を新規登録
            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                'bucket_name' => $request->learningtasks_name,
                'plugin_name' => 'learningtasks'
            ]);

            if (empty($request->copy_learningtask_id)) {
                // 登録

                // 課題管理データ新規オブジェクト
                $learningtask = new Learningtasks();
            } else {
                // コピー

                // コピー元IDで、課題管理データ取得
                $copy_learningtask = Learningtasks::find($request->copy_learningtask_id);
                // ID消して複製
                $learningtask = $copy_learningtask->replicate();
            }
            $learningtask->bucket_id = $bucket_id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆課題管理作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆課題管理更新
            // （表示課題管理選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {
                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);
            }

            $request->flash_message = '課題管理設定を追加しました。';
        } else {
            // learningtask_id があれば、課題管理を更新
            // 課題管理データ取得
            $learningtask = Learningtasks::where('id', $request->learningtask_id)->first();

            $request->flash_message = '課題管理設定を変更しました。';
        }

        // 課題管理設定
        $learningtask->learningtasks_name  = $request->learningtasks_name;

        // delete: learningtasks.use_report, learningtasks.use_examination は使われれていないため、
        //         useReport(), useExamination(), strUseReport()を削除
        //         use_report, use_examination 設定は、learningtasks_use_settings.use_function = 'use_report' or 'use_examination' に移行済み。
        // $learningtask->use_report          = $request->use_report;
        // $learningtask->use_examination     = $request->use_examination;

        //$learningtask->use_evaluate        = $request->use_evaluate;
        //$learningtask->need_auth           = $request->need_auth;
        $learningtask->view_count          = $request->view_count;
        // 課題管理にRSS が必要か、再考する。
        //$learningtask->rss                 = $request->rss;
        //$learningtask->rss_count           = $request->rss_count;
        $learningtask->rss                 = 0;
        $learningtask->rss_count           = 0;
        $learningtask->sequence_conditions = intval($request->sequence_conditions);

        // データ保存
        $learningtask->save();

        // 課題管理名で、Buckets名も更新する
        Buckets::where('id', $learningtask->bucket_id)->update(['bucket_name' => $request->learningtasks_name]);

        // 課題管理名で、Buckets名も更新する
        //Log::debug($learningtask->bucket_id);
        //Log::debug($request->learningtasks_name);

        // 設定内容を保存（一旦削除して新たに保存）
        // change: deleted_id, deleted_nameを自動セットするため、複数件削除する時は collectionのpluck('id')で id のCollectionを取得して destroy()で消す。
        // LearningtasksUseSettings::where('learningtasks_id', $learningtask->id)->where('post_id', 0)->delete();
        $learningtasks_use_settings_ids = LearningtasksUseSettings::where('learningtasks_id', $learningtask->id)->where('post_id', 0)->pluck('id');
        LearningtasksUseSettings::destroy($learningtasks_use_settings_ids);

        if ($request->filled('base_settings')) {
            $base_settings = $request->base_settings;
            foreach ($base_settings as $base_setting_key => $base_setting_value) {
                if ($base_setting_value == "on" || $base_setting_value == "off") {
                    LearningtasksUseSettings::create([
                        'learningtasks_id' => $learningtask->id,
                        'post_id' => 0,
                        'use_function' => $base_setting_key,
                        'value' => $base_setting_value,
                    ]);
                } elseif (LearningtasksUseSettings::isDatetimeUseFunction($base_setting_key) && $base_setting_value) {
                    // 日付を使う機能 + 値あり
                    LearningtasksUseSettings::create([
                        'learningtasks_id' => $learningtask->id,
                        'post_id' => 0,
                        'use_function' => $base_setting_key,
                        'datetime_value' => $base_setting_value . ':00',
                    ]);
                }
            }
        }

        // 登録
        if (empty($request->learningtask_id)) {
            // コピーIDあり
            if ($request->copy_learningtask_id) {
                // [コピーする]
                // ・(LearningtasksPosts). 各課題コピー
                // ・learningtasks_use_settings (LearningtasksUseSettings). 課題の各設定コピー
                // ・learningtasks_configs (LearningtasksConfigs). メール設定
                // ・個別カテゴリコピー
                // ・PluginCategory 課題カテゴリの表示する/しない・表示順データコピー
                //   ※ PluginCategoryの共通カテゴリの表示する/しない、表示順はコピーしない。もし必要になったら実装する。
                //
                // [コピーしない]
                // ・ファイル
                //   ・learningtasks_posts_files 課題の添付ファイル（学習指導書など）task_flag=0
                //                               試験の添付ファイル（試験問題、解答用ファイルなど）task_flag=1
                //   ・learningtasks_users_statuses.upload_id (成績) レポート提出ファイル等
                //   ・uploads (ファイル)
                // ・learningtasks_examinations 試験日
                // ・learningtasks_users 参加設定（受講者・教員）
                // ・learningtasks_users_statuses 成績

                // 課題データ取得
                $copy_learningtasks_posts = LearningtasksPosts::where('learningtasks_id', $request->copy_learningtask_id)->get();
                foreach ($copy_learningtasks_posts as $copy_learningtasks_post) {
                    // コピー元のpost_id
                    $origin_post_id = $copy_learningtasks_post->id;

                    // 課題データ. ID消して複製
                    $learningtasks_post = $copy_learningtasks_post->replicate();
                    $learningtasks_post->learningtasks_id = $learningtask->id;
                    $learningtasks_post->save();

                    // learningtasks_use_settings (LearningtasksUseSettings). 課題の各設定コピー
                    $copy_user_learningtasks_use_settings = LearningtasksUseSettings::where('learningtasks_id', $request->copy_learningtask_id)
                            ->where('post_id', $origin_post_id)
                            ->get();
                    foreach ($copy_user_learningtasks_use_settings as $copy_user_learningtasks_use_setting) {
                        // ID消して複製
                        $user_learningtasks_use_setting = $copy_user_learningtasks_use_setting->replicate();
                        $user_learningtasks_use_setting->learningtasks_id = $learningtask->id;
                        $user_learningtasks_use_setting->post_id = $learningtasks_post->id;
                        $user_learningtasks_use_setting->save();
                    }
                }

                // learningtasks_configs (LearningtasksConfigs). メール設定コピー
                $copy_learningtasks_configs = LearningtasksConfigs::where('learningtasks_id', $request->copy_learningtask_id)
                        ->where('post_id', 0)
                        ->get();
                foreach ($copy_learningtasks_configs as $copy_learningtasks_config) {
                    // ID消して複製
                    $learningtasks_config = $copy_learningtasks_config->replicate();
                    $learningtasks_config->learningtasks_id = $learningtask->id;
                    $learningtasks_config->save();
                }

                // カテゴリコピー. （カテゴリは課題管理毎に別々に存在してるため）
                // where('plugin_id', $learningtask->id)
                $copy_categories = Categories::where('plugin_id', $request->copy_learningtask_id)->where('target', 'learningtasks')->get();
                foreach ($copy_categories as $copy_category) {
                    // コピー元のpost_id
                    $origin_categories_id = $copy_category->id;

                    // カテゴリ. ID消して複製
                    $category = $copy_category->replicate();
                    $category->plugin_id = $learningtask->id;
                    $category->save();

                    // 課題カテゴリデータ取得
                    // $copy_learningtasks_categories = LearningtasksCategories::where('learningtasks_id', $request->copy_learningtask_id)
                    //         ->where('categories_id', $origin_categories_id)
                    //         ->get();
                    $copy_learningtasks_categories = PluginCategory::where('target_id', $request->copy_learningtask_id)
                        ->where('target', $this->frame->plugin_name)
                        ->where('categories_id', $origin_categories_id)
                        ->get();

                    foreach ($copy_learningtasks_categories as $copy_learningtasks_category) {
                        // ID消して複製
                        $learningtasks_category = $copy_learningtasks_category->replicate();
                        $learningtasks_category->target = $this->frame->plugin_name;
                        $learningtasks_category->target_id = $learningtask->id;
                        $learningtasks_category->categories_id = $category->id;
                        $learningtasks_category->save();
                    }
                }

            }
        }

        // 登録後はリダイレクトして編集ページを開く。
        return collect(['redirect_path' => url('/') . "/plugin/learningtasks/editBuckets/" . $page_id . "/" . $frame_id . "/" . $learningtask->id . "#frame-" . $frame_id]);
    }

    /**
     * 削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $learningtask_id)
    {
        // change: backetsは $frame->bucket_id で消さない。選択したLearningtasksのbucket_idで消す
        $learningtasks = Learningtasks::find($learningtask_id);
        if (empty($learningtasks)) {
            return;
        }

        // 課題データ取得
        // change: deleted_id, deleted_nameを自動セットするため、複数件削除する時は collectionのpluck('id')でid配列を取得して destroy()で消す。
        // LearningtasksPosts::where('learningtasks_id', $learningtask_id)->delete();
        $learningtasks_posts_ids = LearningtasksPosts::where('learningtasks_id', $learningtask_id)->pluck('id');

        ////
        //// 添付ファイル及びデータ削除
        ////
        //   learningtasks_posts_files 課題の添付ファイル（学習指導書など）task_flag=0
        //                               試験の添付ファイル（試験問題、解答用ファイルなど）task_flag=1 取得
        $learningtasks_posts_files = LearningtasksPostsFiles::whereIn('post_id', $learningtasks_posts_ids);
        $learningtasks_posts_files_upload_ids = $learningtasks_posts_files->pluck('upload_id');

        // learningtasks_users_statuses 成績 取得
        $learningtasks_users_statuses = LearningtasksUsersStatuses::whereIn('post_id', $learningtasks_posts_ids);
        $learningtasks_users_statuses_upload_ids = $learningtasks_users_statuses->pluck('upload_id');

        // Collectionマージ
        $del_upload_ids = $learningtasks_posts_files_upload_ids->merge($learningtasks_users_statuses_upload_ids);
        // dd($del_upload_ids);

        // uploads 削除
        // 削除するファイルデータ (もし重複IDあったとしても、in検索によって排除される)
        $delete_uploads = Uploads::whereIn('id', $del_upload_ids)->get();
        foreach ($delete_uploads as $delete_upload) {
            // ファイルの削除
            $directory = $this->getDirectory($delete_upload->id);
            Storage::delete($directory . '/' . $delete_upload->id . '.' .$delete_upload->extension);

            // uploadの削除
            $delete_upload->delete();
        }

        // 課題の添付ファイルのテーブルデータ 削除
        $learningtasks_posts_files_ids = $learningtasks_posts_files->pluck('id');
        LearningtasksPostsFiles::destroy($learningtasks_posts_files_ids);

        ////
        //// 課題データ削除
        ////
        // learningtasks_examinations 試験日データ削除
        $learningtasks_examinations_ids = LearningtasksExaminations::whereIn('post_id', $learningtasks_posts_ids)->pluck('id');
        LearningtasksExaminations::destroy($learningtasks_examinations_ids);

        // learningtasks_users_statuses 成績 削除
        $learningtasks_users_statuses_ids = $learningtasks_users_statuses->pluck('id');
        LearningtasksUsersStatuses::destroy($learningtasks_users_statuses_ids);

        // learningtasks_users 参加設定（受講者・教員）削除
        $learningtasks_users_ids = LearningtasksUsers::whereIn('post_id', $learningtasks_posts_ids)->pluck('id');
        LearningtasksUsers::destroy($learningtasks_users_ids);

        // 課題データを削除する。
        LearningtasksPosts::destroy($learningtasks_posts_ids);

        ////
        //// カテゴリ削除
        ////
        Categories::destroyBucketsCategories($this->frame->plugin_name, $learningtask_id);

        ////
        //// 課題管理の設定削除
        ////
        // learningtasks_configs (LearningtasksConfigs). メール設定
        $learningtasks_configs_ids = LearningtasksConfigs::where('learningtasks_id', $learningtask_id)->pluck('id');
        LearningtasksConfigs::destroy($learningtasks_configs_ids);

        // 課題管理の設定、課題毎の各設定を削除
        $learningtasks_use_settings_ids = LearningtasksUseSettings::where('learningtasks_id', $learningtask_id)->pluck('id');
        LearningtasksUseSettings::destroy($learningtasks_use_settings_ids);

// Frame に紐づくLearningTask を削除した場合のみ、Frame の更新。（Frame に紐づかないLearningTask の削除もあるので、その場合はFrame は更新しない。）
// 実装は後で。

        // バケツIDの取得のためにFrame を取得(Frame を更新する前に取得しておく)
        // $frame = Frame::where('id', $frame_id)->first();

        // FrameのバケツIDの更新. このバケツを表示している全ページのフレームのバケツIDを消す（もし、このフレームでこのバケツを表示していたとしても、$learningtasks->bucket_idで消えるため問題なし）
        // Frame::where('id', $frame_id)->update(['bucket_id' => null]);
        Frame::where('bucket_id', $learningtasks->bucket_id)->update(['bucket_id' => null]);

        // backetsの削除
        // Buckets::where('id', $frame->bucket_id)->delete();
        Buckets::where('id', $learningtasks->bucket_id)->delete();

        // 課題管理設定を削除する。
        // Learningtasks::destroy($learningtask_id);
        $learningtasks->delete();

        // 削除処理はredirect 付のルートで呼ばれて、処理後はページの再表示が行われるため、ここでは何もしない。
    }

    /**
     * データ紐づけ変更関数
     */
    public function changeBuckets($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
               ->update(['bucket_id' => $request->select_bucket]);

        // changeBuckets は redirect 付のルートで呼ばれて、処理後はページの再表示が行われるため、ここでは何もしない。
    }

    /**
     * 試験申し込み者一覧ダウンロード
     */
    public function downloadCsv($request, $page_id, $frame_id, $id)
    {
        // id で対象のデータの取得

        // データベースの取得
        $learningtask = Learningtasks::where('id', $id)->first();

        /*
        SELECT p.post_title, us.examination_id, u.name, e.start_at, us.created_at
        FROM learningtasks_users_statuses AS us
            LEFT JOIN users AS u ON u.id = us.user_id
            LEFT JOIN learningtasks_posts AS p ON p.id = us.post_id
            LEFT JOIN learningtasks_examinations AS e ON e.id = us.examination_id
        WHERE `task_status` = 4
        ORDER BY e.start_at, p.post_title
        */
        $learningtasks_users_statuses = LearningtasksUsersStatuses::
                select(
                    'learningtasks_posts.post_title',
                    'learningtasks_users_statuses.examination_id',
                    'users.name',
                    'learningtasks_examinations.start_at',
                    'learningtasks_users_statuses.created_at'
                )
                ->leftJoin('users', 'users.id', '=', 'learningtasks_users_statuses.user_id')
                // bugfix: 論理削除を考慮
                // ->leftJoin('learningtasks_posts', 'learningtasks_posts.id', '=', 'learningtasks_users_statuses.post_id')
                // ->leftJoin('learningtasks_examinations', 'learningtasks_examinations.id', '=', 'learningtasks_users_statuses.examination_id')
                ->leftJoin('learningtasks_posts', function ($join) {
                    $join->on('learningtasks_posts.id', '=', 'learningtasks_users_statuses.post_id')
                            ->whereNull('learningtasks_posts.deleted_at');
                })
                ->leftJoin('learningtasks_examinations', function ($join) {
                    $join->on('learningtasks_examinations.id', '=', 'learningtasks_users_statuses.examination_id')
                            ->whereNull('learningtasks_examinations.deleted_at');
                })
                ->where('learningtasks_users_statuses.task_status', 4)
                ->where('learningtasks_posts.learningtasks_id', $id)
                ->orderBy('learningtasks_examinations.start_at', 'asc')
                ->orderBy('learningtasks_posts.post_title', 'asc')
                ->get();


        // 返却用配列
        $csv_array = array();

        // 見出し行
        $csv_array[0]['post_title'] = '科目名';
        $csv_array[0]['examination_id'] = '試験ID';
        $csv_array[0]['name'] = '氏名';
        $csv_array[0]['start_at'] = '試験開始日時';
        $csv_array[0]['created_at'] = '登録日時';

        // データ
        foreach ($learningtasks_users_statuses as $learningtasks_users_status) {
            $csv_line['post_title'] = $learningtasks_users_status->post_title;
            $csv_line['examination_id'] = $learningtasks_users_status->examination_id;
            $csv_line['name'] = $learningtasks_users_status->name;
            $csv_line['start_at'] = $learningtasks_users_status->start_at;
            $csv_line['created_at'] = $learningtasks_users_status->created_at;
            $csv_array[] = $csv_line;
        }

        // レスポンス版
        $filename = $learningtask->learningtasks_name . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        // データ
        $csv_data = '';
        foreach ($csv_array as $csv_line) {
            foreach ($csv_line as $csv_col) {
                $csv_data .= '"' . $csv_col . '",';
            }
            // 末尾カンマを削除
            $csv_data = substr($csv_data, 0, -1);
            $csv_data .= "\n";
        }

        // Log::debug(var_export($request->character_code, true));

        // 文字コード変換
        if ($request->character_code == CsvCharacterCode::utf_8) {
            $csv_data = mb_convert_encoding($csv_data, CsvCharacterCode::utf_8);
            // UTF-8のBOMコードを追加する(UTF-8 BOM付きにするとExcelで文字化けしない)
            $csv_data = CsvUtils::addUtf8Bom($csv_data);
        } else {
            $csv_data = mb_convert_encoding($csv_data, CsvCharacterCode::sjis_win);
        }

        return response()->make($csv_data, 200, $headers);
    }

    /**
     * メール設定表示関数
     */
    public function editMail($request, $page_id, $frame_id, $post_id = null)
    {
        // 課題管理
        $learningtask = $this->getLearningTask($frame_id);

        // 課題取得
        $post = $this->getPost($post_id);

        // 課題管理ツール
        $tool = new LearningtasksTool($request, $page_id, $learningtask, $post, $frame_id);

        // 表示テンプレートを呼び出す。
        return $this->view('learningtasks_edit_mail', [
            'learningtask'        => $learningtask,
            // 'learningtasks_posts' => $post,
            'tool'                => $tool,
        ]);
    }

    /**
     * メール設定表示関数
     */
    public function saveMail($request, $page_id, $frame_id, $post_id = null)
    {
        // 課題管理
        $learningtask = $this->getLearningTask($frame_id);
        if (empty($learningtask)) {
            return $this->editMail($request, $page_id, $frame_id, $post_id);
        }

        // 課題取得
        // $post = $this->getPost($post_id);

        // 課題管理ツール
        // $tool = new LearningtasksTool($request, $page_id, $learningtask, $post, $frame_id);

        // 設定内容を保存（一旦削除して新たに保存）
        LearningtasksConfigs::where('learningtasks_id', $learningtask->id)
                            ->where('post_id', 0)
                            ->delete();

        // 件名保存
        if ($request->filled('subjects')) {
            $subjects = $request->subjects;
            foreach ($subjects as $task_status => $subject) {
                LearningtasksConfigs::create([
                    'learningtasks_id' => $learningtask->id,
                    'post_id'          => 0,
                    'type'             => "subject",
                    'task_status'      => $task_status,
                    'value'            => $subject,
                ]);
            }
        }
        // 本文保存
        if ($request->filled('bodys')) {
            $bodys = $request->bodys;
            foreach ($bodys as $task_status => $body) {
                LearningtasksConfigs::create([
                    'learningtasks_id' => $learningtask->id,
                    'post_id'          => 0,
                    'type'             => "body",
                    'task_status'      => $task_status,
                    'value'            => $body,
                ]);
            }
        }
        // フッター保存
        if ($request->filled('footer')) {
            LearningtasksConfigs::create([
                'learningtasks_id' => $learningtask->id,
                'post_id'          => 0,
                'type'             => "footer",
                'task_status'      => 0,
                'value'            => $request->footer,
            ]);
        }

        return $this->editMail($request, $page_id, $frame_id, $post_id);
    }

    /**
     * カテゴリ表示関数
     */
    public function listCategories($request, $page_id, $frame_id, $id = null)
    {
        // セッション初期化などのLaravel 処理。
        // $request->flash();

        // 課題管理
        $learningtask = $this->getLearningTask($frame_id);

        // 共通カテゴリ
        $general_categories = Categories::getGeneralCategories($this->frame->plugin_name, $learningtask->id);

        // 個別カテゴリ（プラグイン）
        $plugin_categories = Categories::getPluginCategories($this->frame->plugin_name, $learningtask->id);


        // 表示テンプレートを呼び出す。
        return $this->view('learningtasks_list_categories', [
            'general_categories' => $general_categories,
            'plugin_categories' => $plugin_categories,
            'learningtask' => $learningtask,
        ]);
    }

    /**
     * カテゴリ登録処理
     */
    public function saveCategories($request, $page_id, $frame_id, $id = null)
    {
        /* エラーチェック
        ------------------------------------ */

        $validator = Categories::validatePluginCategories($request);

        if ($validator->fails()) {
            // return $this->listCategories($request, $page_id, $frame_id, $id, $validator->errors());
            return redirect()->back()->withErrors($validator)->withInput();
        }

        /* カテゴリ追加
        ------------------------------------ */

        // 課題管理
        $learningtask = $this->getLearningTask($frame_id);

        Categories::savePluginCategories($request, $this->frame->plugin_name, $learningtask->id);

        // return $this->listCategories($request, $page_id, $frame_id, $id, null, true);
        // saveCategoriesはredirect 付のルートで呼ばれて、処理後はページの再表示が行われるため、ここでは何もしない。
    }

    /**
     *  カテゴリ削除処理
     */
    public function deleteCategories($request, $page_id, $frame_id, $id = null)
    {
        Categories::deleteCategories($this->frame->plugin_name, $id);

        // return $this->listCategories($request, $page_id, $frame_id, $id, null, true);
        // deleteCategoriesはredirect 付のルートで呼ばれて、処理後はページの再表示が行われるため、ここでは何もしない。
    }

    /**
     * 試験登録処理
     */
    public function saveExaminations($request, $page_id, $frame_id, $post_id)
    {
        // 登録する試験
        $validate_value = [
            'start_at' => ['nullable', 'date_format:"Y-m-d H:i"', 'required_with:end_at,entry_end_at', 'before_or_equal:end_at'],
            'end_at' => ['nullable', 'date_format:"Y-m-d H:i"', 'required_with:start_at,entry_end_at'],
            'entry_end_at' => ['nullable', 'date_format:"Y-m-d H:i"', 'before_or_equal:start_at'],
        ];

        $validate_attribute = [
            'start_at' => '追加の開始日時',
            'end_at' => '追加の終了日時',
            'entry_end_at' => '追加の申込終了日時',
        ];

        // 既存の試験
        if ($request->filled('edit_examination_id')) {
            foreach ($request->edit_examination_id as $examination_id) {
                // 項目のエラーチェック
                $validate_value['edit_start_at.'.$examination_id] = [
                    'required',
                    'date_format:"Y-m-d H:i"',
                    'required_with:edit_end_at.'.$examination_id.',edit_entry_end_at.'.$examination_id,
                    'before_or_equal:edit_end_at.'.$examination_id
                ];
                $validate_value['edit_end_at.'.$examination_id] = [
                    'required',
                    'date_format:"Y-m-d H:i"',
                    'required_with:edit_start_at.'.$examination_id.',edit_entry_end_at.'.$examination_id
                ];
                $validate_value['edit_entry_end_at.'.$examination_id] = [
                    'nullable',
                    'date_format:"Y-m-d H:i"',
                    'before_or_equal:edit_start_at.'.$examination_id
                ];

                $validate_attribute['edit_start_at.'.$examination_id] = '開始日時';
                $validate_attribute['edit_end_at.'.$examination_id] = '終了日時';
                $validate_attribute['edit_entry_end_at.'.$examination_id] = '申込終了日時';
            }
        }

        // 課題ファイルがアップロードされた。
        if ($request->hasFile('add_task_file')) {
            // Scratchを許可. アップロードされないと拡張子判定できない。
            $extension = $request->file('add_task_file')->getClientOriginalExtension();
            if ($extension == 'sb2' || $extension == 'sb3') {
                // OK
            } else {
                // ファイルチェック
                $validate_value['add_task_file'] = ['required', 'mimes:pdf,doc,docx'];
                $validate_attribute['add_task_file'] = '課題ファイル';
            }
        }

        // エラーチェック
        $validator = Validator::make($request->all(), $validate_value);
        $validator->setAttributeNames($validate_attribute);

        if ($validator->fails()) {
            // エラー時はエラー内容を引き継いで入力画面に戻る
            return redirect()->back()->withErrors($validator)->withInput();
            //return $this->editExaminations($request, $page_id, $frame_id, $post_id)->withErrors($validator);
        }

        // 既存の試験
        $edit_examination_ids = [];
        if ($request->filled('edit_examination_id')) {
            $edit_examination_ids = $request->edit_examination_id;
        }

        // 削除対象の試験を削除する。
        if ($request->filled('del_examinations')) {
            foreach ($request->del_examinations as $examination_id => $examination_value) {
                if ($examination_value) {
                    LearningtasksExaminations::find($examination_id)->delete();

                    // edit_examination_ids は必ず del_examinations と同じkeyで作成されます。
                    // そのため、削除した試験日時IDは edit_examination_ids から取り除きます。
                    unset($edit_examination_ids[$examination_id]);
                }
            }
        }

        // 試験関係ファイルの保存
        $this->saveTaskFile($request, $page_id, $post_id, 1);

        // 登録する試験
        if ($request->filled('start_at') && $request->filled('end_at')) {
            // LearningtasksExaminations::create(['post_id' => $post_id, 'start_at' => $request->start_at . ':00', 'end_at' => $request->end_at . ':00']);
            $learningtasks_examinations = new LearningtasksExaminations();
            $learningtasks_examinations->post_id = $post_id;
            $learningtasks_examinations->start_at = $request->start_at . ':00';
            $learningtasks_examinations->end_at = $request->end_at . ':00';
            if ($request->filled('entry_end_at')) {
                $learningtasks_examinations->entry_end_at = $request->entry_end_at . ':00';
            }
            // 保存
            $learningtasks_examinations->save();
        }

        // 既存の試験
        foreach ($edit_examination_ids as $edit_examination_id) {
            // モデルオブジェクト取得
            $learningtasks_examinations = LearningtasksExaminations::where('id', $edit_examination_id)->first();

            // データのセット
            $learningtasks_examinations->post_id = $post_id;
            $learningtasks_examinations->start_at = $request->input('edit_start_at.'.$edit_examination_id) . ':00';
            $learningtasks_examinations->end_at = $request->input('edit_end_at.'.$edit_examination_id) . ':00';
            if ($request->filled('edit_entry_end_at.'.$edit_examination_id)) {
                $learningtasks_examinations->entry_end_at = $request->input('edit_entry_end_at.'.$edit_examination_id) . ':00';
            } else {
                // bugfix: 申込終了日が消せないバグ修正
                $learningtasks_examinations->entry_end_at = null;
            }

            // 保存
            $learningtasks_examinations->save();
        }

        // 課題ファイルの削除
        $this->deleteTaskFile($request);

        // 対象の課題特定
        $post = LearningtasksPosts::find($post_id);
        if (empty($post)) {
            // return $this->editExaminations($request, $page_id, $frame_id, $post_id);
            return;
        }

        // 設定内容を保存（一旦削除して新たに保存）
        // change: deleted_id, deleted_nameを自動セットするため、複数件削除する時は collectionのpluck('id')でid配列を取得して destroy()で消す。
        // LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
        //                         ->where('post_id', $post->id)
        //                         ->where('use_function', 'post_examination_setting')
        //                         ->delete();
        // LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
        //                         ->where('post_id', $post->id)
        //                         ->where('use_function', 'post_examination_timing')
        //                         ->delete();
        // LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
        //                         ->where('post_id', $post->id)
        //                         ->where('use_function', 'like', 'use_examination%')
        //                         ->delete();
        $learningtasks_use_settings_ids = LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
                ->where('post_id', $post->id)
                ->where('use_function', 'post_examination_setting')
                ->pluck('id');
        $learningtasks_use_settings_ids2 = LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
                ->where('post_id', $post->id)
                ->where('use_function', 'like', 'post_examination_timing')
                ->pluck('id');
        $learningtasks_use_settings_ids3 = LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
                ->where('post_id', $post->id)
                ->where('use_function', 'like', 'use_examination%')
                ->pluck('id');

        // Collectionマージ
        $del_learningtasks_use_settings_ids = $learningtasks_use_settings_ids->merge($learningtasks_use_settings_ids2)->merge($learningtasks_use_settings_ids3);
        LearningtasksUseSettings::destroy($del_learningtasks_use_settings_ids);

        if ($request->filled('post_examination_setting')) {
            LearningtasksUseSettings::create([
                'learningtasks_id' => $post->learningtasks_id,
                'post_id'          => $post->id,
                'use_function'     => 'post_examination_setting',
                'value'            => $request->post_examination_setting,
            ]);
        }
        if ($request->filled('post_examination_timing')) {
            LearningtasksUseSettings::create([
                'learningtasks_id' => $post->learningtasks_id,
                'post_id'          => $post->id,
                'use_function'     => 'post_examination_timing',
                'value'            => $request->post_examination_timing,
            ]);
        }
        if ($request->filled('post_settings')) {
            $post_settings = $request->post_settings;
            foreach ($post_settings as $post_setting_key => $post_setting_value) {
                if (strpos($post_setting_key, 'use_examination') === 0) {
                    if ($post_setting_value == "on" || $post_setting_value == "off") {
                        LearningtasksUseSettings::create([
                            'learningtasks_id' => $post->learningtasks_id,
                            'post_id'          => $post->id,
                            'use_function'     => $post_setting_key,
                            'value'            => $post_setting_value,
                        ]);
                    }
                }
            }
        }

        // 編集画面を開く
        // return $this->editExaminations($request, $page_id, $frame_id, $post_id);
    }

    /**
     * 試験日時インポート画面表示
     */
    public function importExaminations($request, $page_id, $frame_id, $learningtasks_posts_id = null)
    {
        // // 課題管理データ
        // $learningtask = $this->getLearningTask($frame_id);
        // if (empty($learningtask)) {
        //     return;
        // }

        // 記事取得
        $learningtasks_post = $this->getPost($learningtasks_posts_id);
        if (empty($learningtasks_post->id)) {
            return $this->viewError("403_inframe", null, 'editのユーザー権限に応じたPOST ID チェック');
        }

        // 表示テンプレートを呼び出す。
        return $this->view('learningtasks_import_examinations', [
            'learningtasks_posts' => $learningtasks_post,
        ]);
    }

    /**
     * 試験日時インポートインポート処理
     */
    public function uploadCsvExaminations($request, $page_id, $frame_id, $post_id)
    {
        // csv
        $rules = [
            'examinations_csv'  => [
                'required',
                'file',
                'mimes:csv,txt', // mimesの都合上text/csvなのでtxtも許可が必要
                'mimetypes:application/csv,text/plain',
            ],
        ];

        // 画面エラーチェック
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames([
            'examinations_csv' => 'CSVファイル',
        ]);

        if ($validator->fails()) {
            // Log::debug(var_export($validator->errors(), true));
            // エラーと共に編集画面を呼び出す
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // CSVファイル一時保存
        $path = $request->file('examinations_csv')->store('tmp');
        // Log::debug(var_export(storage_path('app/') . $path, true));
        $csv_full_path = storage_path('app/') . $path;

        // ファイル拡張子取得
        $file_extension = $request->file('examinations_csv')->getClientOriginalExtension();
        // 小文字に変換
        $file_extension = strtolower($file_extension);
        // Log::debug(var_export($file_extension, true));

        // 文字コード
        $character_code = $request->character_code;

        // 文字コード自動検出
        if ($character_code == CsvCharacterCode::auto) {
            // 文字コードの自動検出(文字エンコーディングをsjis-win, UTF-8の順番で自動検出. 対象文字コード外の場合、false戻る)
            $character_code = CsvUtils::getCharacterCodeAuto($csv_full_path);
            if (!$character_code) {
                // 一時ファイルの削除
                Storage::delete($path);

                $error_msgs = "文字コードを自動検出できませんでした。CSVファイルの文字コードを " . CsvCharacterCode::getSelectMembersDescription(CsvCharacterCode::sjis_win) .
                            ", " . CsvCharacterCode::getSelectMembersDescription(CsvCharacterCode::utf_8) . " のいずれかに変更してください。";

                return redirect()->back()->withErrors(['examinations_csv' => $error_msgs])->withInput();
            }
        }

        // 読み込み
        $fp = fopen($csv_full_path, 'r');
        // CSVファイル：Shift-JIS -> UTF-8変換時のみ
        if ($character_code == CsvCharacterCode::sjis_win) {
            // ストリームフィルタ内で、Shift-JIS -> UTF-8変換
            $fp = CsvUtils::setStreamFilterRegisterSjisToUtf8($fp);
        }

        // bugfix: fgetcsv() は ロケール設定の影響を受け、xampp環境＋日本語文字列で誤動作したため、ロケール設定する。
        setlocale(LC_ALL, 'ja_JP.UTF-8');

        // 一行目（ヘッダ）
        $header_columns = fgetcsv($fp, 0, ',');
        // CSVファイル：UTF-8のみ
        if ($character_code == CsvCharacterCode::utf_8) {
            // UTF-8のみBOMコードを取り除く
            $header_columns = CsvUtils::removeUtf8Bom($header_columns);
        }
        // dd($csv_full_path);
        // \Log::debug('$header_columns:'. var_export($header_columns, true));

        // カラムの取得
        $examination_columns = LearningtasksExaminationColumn::getImportColumn();

        // ヘッダー項目のエラーチェック
        $error_msgs = CsvUtils::checkCsvHeader($header_columns, $examination_columns);
        if (!empty($error_msgs)) {
            // 一時ファイルの削除
            fclose($fp);
            Storage::delete($path);

            return redirect()->back()->withErrors(['learningtasks_examinations_id' => $error_msgs])->withInput();
        }

        $cvs_rules = [
            0 => [
                'nullable',
                'numeric',
                'exists:learningtasks_examinations,id,post_id,'.$post_id.',deleted_at,NULL'
            ],  // id
            // 日付のインポートは画面とは違い /(スラッシュ) 区切りで 月は一桁 (例) 2021/4/26 10:00形式で取り込む ※ Excel日付形式での取込
            // 1 => ['required', 'date_format:"Y-m-d H:i"', 'required_with:2,3'],      // 試験開始日時
            // 2 => ['required', 'date_format:"Y-m-d H:i"', 'required_with:1,3'],      // 試験終了日時
            // 3 => ['nullable', 'date_format:"Y-m-d H:i"', 'before_or_equal:1'],      // 申込終了日時
            1 => ['required', 'date_format:"Y/n/j H:i"', 'required_with:2,3', 'before_or_equal:2'], // 試験開始日時
            2 => ['required', 'date_format:"Y/n/j H:i"', 'required_with:1,3'],          // 試験終了日時
            3 => ['nullable', 'date_format:"Y/n/j H:i"', 'before_or_equal:1'],          // 申込終了日時
        ];

        // データ項目のエラーチェック
        $error_msgs = CsvUtils::checkCvslines($fp, $examination_columns, $cvs_rules);
        if (!empty($error_msgs)) {
            // 一時ファイルの削除
            fclose($fp);
            Storage::delete($path);

            return redirect()->back()->withErrors(['examinations_csv' => $error_msgs])->withInput();
        }

        // [debug]
        // // 一時ファイルの削除
        // fclose($fp);
        // Storage::delete($path);
        // dd('ここまで');

        // ファイルポインタの位置を先頭に戻す
        rewind($fp);

        // ヘッダー
        $header_columns = fgetcsv($fp, 0, ',');
        // CSVファイル：UTF-8のみ
        if ($character_code == CsvCharacterCode::utf_8) {
            // UTF-8のみBOMコードを取り除く
            $header_columns = CsvUtils::removeUtf8Bom($header_columns);
        }

        // データ
        while (($csv_columns = fgetcsv($fp, 0, ',')) !== false) {
            // --- 入力値変換
            // Log::debug(var_export($csv_columns, true));

            // 入力値をトリム(preg_replace(/u)で置換. /u = UTF-8 として処理)
            $csv_columns = StringUtils::trimInput($csv_columns);

            // 配列の頭から要素(id)を取り除いて取得
            // CSVのデータ行の頭は、必ず固定項目のidの想定
            $learningtasks_examinations_id = array_shift($csv_columns);
            // 空文字をnullに変換
            $learningtasks_examinations_id = StringUtils::convertEmptyStringsToNull($learningtasks_examinations_id);

            foreach ($csv_columns as $col => &$csv_column) {
                // 空文字をnullに変換
                $csv_column = StringUtils::convertEmptyStringsToNull($csv_column);
            }
            // Log::debug('$csv_columns:'. var_export($csv_columns, true));

            // [debug]
            //// 一時ファイルの削除
            // fclose($fp);
            // Storage::delete($path);
            // dd('ここまで' . $posted_at);

            if (empty($learningtasks_examinations_id)) {
                // 登録
                $learningtasks_examinations = new LearningtasksExaminations();
            } else {
                // 更新
                // learningtasks_examinations_idはバリデートでLearningtasksExaminations存在チェック済みなので、必ずデータある想定
                $learningtasks_examinations = LearningtasksExaminations::where('id', $learningtasks_examinations_id)->first();
            }

            $learningtasks_examinations->post_id = $post_id;

            // $learningtasks_examinations->start_at = $csv_columns[0] . ':00';
            // $learningtasks_examinations->end_at = $csv_columns[1] . ':00';
            // if ($csv_columns[2]) {
            //     $learningtasks_examinations->entry_end_at = $csv_columns[2] . ':00';
            // }
            $learningtasks_examinations->start_at = new Carbon($csv_columns[0]);
            $learningtasks_examinations->end_at = new Carbon($csv_columns[1]);
            if ($csv_columns[2]) {
                $learningtasks_examinations->entry_end_at = new Carbon($csv_columns[2]);
            } else {
                // bugfix: 申込終了日が消せないバグ修正
                $learningtasks_examinations->entry_end_at = null;
            }

            $learningtasks_examinations->save();
        }

        // 一時ファイルの削除
        fclose($fp);
        Storage::delete($path);

        $request->flash_message = 'インポートしました。';

        // redirect_path指定して自動遷移するため、returnで表示viewの指定不要。
    }

    /**
     * CSVインポートのフォーマットダウンロード
     */
    public function downloadCsvFormatExaminations($request, $page_id, $frame_id, $post_id)
    {
        // データ出力しない（フォーマットのみ出力）
        $data_output_flag = false;
        return $this->downloadCsvExaminations($request, $page_id, $frame_id, $post_id, $data_output_flag);
    }

    /**
     * データベースデータダウンロード
     */
    public function downloadCsvExaminations($request, $page_id, $frame_id, $post_id, $data_output_flag = true)
    {
        // カラムの取得
        $columns = LearningtasksExaminationColumn::getImportColumn();

        // 返却用配列
        $csv_array = array();

        // 見出し行
        foreach ($columns as $columnKey => $column) {
            $csv_array[0][$columnKey] = $column;
        }

        // $data_output_flag = falseは、CSVフォーマットダウンロード処理
        if ($data_output_flag) {
            // 試験設定データを取得
            $examinations = LearningtasksExaminations::where('post_id', $post_id)
                    ->orderBy('start_at', 'asc')
                    ->get();

            // 行数
            $csv_line_no = 1;

            // データ
            foreach ($examinations as $examination) {
                $csv_line = [];
                foreach ($columns as $columnKey => $column) {
                    // 日付カラムか
                    if (LearningtasksExaminations::isDateColumn($columnKey)) {
                        $csv_line[$columnKey] = $examination->$columnKey ? $examination->$columnKey->format('Y/n/j H:i') : null;
                    } else {
                        $csv_line[$columnKey] = $examination->$columnKey;
                    }
                }

                $csv_array[$csv_line_no] = $csv_line;
                $csv_line_no++;
            }
        }

        // レスポンス版
        $filename = 'examinations.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        // データ
        $csv_data = '';
        foreach ($csv_array as $csv_line) {
            foreach ($csv_line as $csv_col) {
                $csv_data .= '"' . $csv_col . '",';
            }
            // 末尾カンマを削除
            $csv_data = substr($csv_data, 0, -1);
            $csv_data .= "\n";
        }

        // Log::debug(var_export($request->character_code, true));

        // 文字コード変換
        if ($request->character_code == CsvCharacterCode::utf_8) {
            $csv_data = mb_convert_encoding($csv_data, CsvCharacterCode::utf_8);
            // UTF-8のBOMコードを追加する(UTF-8 BOM付きにするとExcelで文字化けしない)
            $csv_data = CsvUtils::addUtf8Bom($csv_data);
        } else {
            $csv_data = mb_convert_encoding($csv_data, CsvCharacterCode::sjis_win);
        }

        return response()->make($csv_data, 200, $headers);
    }

    /**
     * レポートの課題提出
     */
    public function changeStatus1($request, $page_id, $frame_id, $post_id)
    {
        return $this->changeStatus($request, $page_id, $frame_id, $post_id, 1);
    }

    /**
     * レポートの課題評価
     */
    public function changeStatus2($request, $page_id, $frame_id, $post_id)
    {
        return $this->changeStatus($request, $page_id, $frame_id, $post_id, 2);
    }

    /**
     * レポートのコメント
     */
    public function changeStatus3($request, $page_id, $frame_id, $post_id)
    {
        return $this->changeStatus($request, $page_id, $frame_id, $post_id, 3);
    }

    /**
     * 試験申し込み
     */
    public function changeStatus4($request, $page_id, $frame_id, $post_id)
    {
        return $this->changeStatus($request, $page_id, $frame_id, $post_id, 4);
    }

    /**
     * 試験の解答提出
     */
    public function changeStatus5($request, $page_id, $frame_id, $post_id)
    {
        return $this->changeStatus($request, $page_id, $frame_id, $post_id, 5);
    }

    /**
     * 試験の評価
     */
    public function changeStatus6($request, $page_id, $frame_id, $post_id)
    {
        return $this->changeStatus($request, $page_id, $frame_id, $post_id, 6);
    }

    /**
     * 試験のコメント
     */
    public function changeStatus7($request, $page_id, $frame_id, $post_id)
    {
        return $this->changeStatus($request, $page_id, $frame_id, $post_id, 7);
    }

    /**
     * 総合評価のコメント
     */
    public function changeStatus8($request, $page_id, $frame_id, $post_id)
    {
        return $this->changeStatus($request, $page_id, $frame_id, $post_id, 8);
    }

    /**
     * 進捗ステータス更新
     */
    private function changeStatus($request, $page_id, $frame_id, $post_id, $task_status)
    {
        // 権限チェック（deleteCategories 関数は標準チェックにないので、独自チェック）
        $user = Auth::user();
        if (empty($user)) {
            return $this->viewError("403_inframe", null, "ログインしないとできない処理です。");
        }

        // 課題管理＆フレームデータ
        $learningtask = $this->getLearningTask($frame_id);

        // 課題取得
        $post = $this->getPost($post_id);

        // 課題管理ツール
        $tool = new LearningtasksTool($request, $page_id, $learningtask, $post, $frame_id);

        // レポートの課題提出
        if ($task_status == 1) {
            // レポート提出期限オーバーか
            if ($tool->isOutOfDeadlineReportUpload()) {
                session()->flash('plugin_errors', '提出期限を過ぎたのため、現在は提出できません。');
                // redirect_path指定済みのため、returnのみで元画面へ遷移
                return;
            }
        }

        // 登録時のチェック
        $validator_values = array();
        $validator_attributes = array();
        // $validator_messages = array();

        // 試験申し込み
        if ($task_status == 4) {
            $learningtasksExaminations = LearningtasksExaminations::find($request->examination_id);

            // 試験申込期限の設定ないなら、チェックしない
            if ($learningtasksExaminations->report_end_at) {
                // 今より試験申込期限[以上(gte)]なら、申込できない
                if (Carbon::now()->gte($learningtasksExaminations->report_end_at)) {
                    session()->flash('plugin_errors', '申込期限を過ぎたのため、該当の試験は申込できません。');
                    // redirect_path指定済みのため、returnのみで元画面へ遷移
                    return;
                }
            }
        }

        // 試験の解答の場合、該当の試験時間内かチェック
        if ($task_status == 5) {
            // tools クラスのcanExaminationUpload（試験の提出を行えるか？）でチェックする。
            // この関数でチェックすることで、試験時間もチェックできる。
            // required を使用しているが、メッセージはview で独自に記載。
            if (!$tool->canExaminationUpload($post)) {
                $validator_values['examination_time'] = ['required'];
                $validator_attributes['examination_time'] = '試験時間';
            }
        }

        // アップロードファイルの指定があれば、必須チェック（必須は受講生側のレポート提出、試験提出のみ）
        if ($task_status == 1 || $task_status == 5) {
            if ($tool->isRequreUploadFile($task_status)) {
                $validator_values['upload_file'] = ['required'];
                $validator_attributes['upload_file'] = 'ファイル';
            }
        }

        // 評価の場合、評価を必須チェック
        if ($task_status == 2 || $task_status == 6 || $task_status == 8) {
            $validator_values['grade'] = ['required'];
            $validator_attributes['grade'] = '評価';
        }

        // 項目のエラーチェック
        if (!empty($validator_values)) {
            $validator = Validator::make($request->all(), $validator_values);
            $validator->setAttributeNames($validator_attributes);
            if ($validator->fails()) {
                // エラー時はエラー内容を引き継いで入力画面に戻る
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        // upload 用変数
        $upload = null;

        // アップロードファイルがあれば保存する。
        if ($request->hasFile('upload_file')) {
            // uploads テーブルに情報追加、ファイルのid を取得する
            $upload = Uploads::create([
                'client_original_name' => $request->file('upload_file')->getClientOriginalName(),
                'mimetype'             => $request->file('upload_file')->getClientMimeType(),
                'extension'            => $request->file('upload_file')->getClientOriginalExtension(),
                'size'                 => $request->file('upload_file')->getClientSize(),
                'plugin_name'          => 'learningtasks',
                'check_method'         => 'checkUploadUsersStatus',
                'page_id'              => $page_id,
                'private'              => 1,
            ]);

            // 課題ファイル保存
            $directory = $this->getDirectory($upload->id);
            $request->file('upload_file')->storeAs($directory, $upload->id . '.' . $request->file('upload_file')->getClientOriginalExtension());
        }

        // 科目を取得
        //$learningtask_post = $this->getPost($post_id);

        // 進捗ステータスのユーザID（受講生のID）
        // レポートの評価(2)、レポートのコメント(3)、試験の評価(6)、試験のコメント(7)、総合評価(8)の場合は、教員によるログイン操作のため、セッションから
        $student_user_id = $user->id;

        // 課題管理者のみ、代理のレポート提出(1), 試験申し込み(4), 試験提出(5)させる
        if ($tool->isLearningtaskAdmin() && ($task_status == 1 || $task_status == 4 || $task_status == 5)) {
            $student_user_id = $tool->getStudentId();
        }

        if ($task_status == 2 || $task_status == 3 || $task_status == 6 || $task_status == 7 || $task_status == 8) {
            // bugfix: 管理者等で、教員＆受講生とありえない設定をして、評価する受講生を１度も切替ず評価した場合、student_user_id が nullでSQLエラーになるため修正
            // $student_user_id = session('student_id' . $frame_id);
            $student_user_id = $tool->getStudentId();
            // dd($student_user_id, $user->id, session('student_id' . $frame_id));
        }

        // メール送信：機能設定でメール送信あり＆対象ユーザにメールアドレスの設定がある場合
        if ($task_status == 1 || $task_status == 2 || $task_status == 3 || $task_status == 5 || $task_status == 6 || $task_status == 7 || $task_status == 8) {
            $this->sendMailLocal($post, $task_status, $tool, $student_user_id);
        }

        // ユーザーの進捗ステータス保存
        LearningtasksUsersStatuses::create([
            'post_id'        => $post_id,
            'user_id'        => $student_user_id,
            'task_status'    => $task_status,
            'comment'        => $request->filled('comment') ? $request->comment : null,
            'upload_id'      => empty($upload) ? null : $upload->id,
            'examination_id' => $request->filled('examination_id') ? $request->examination_id : null,
            'grade'          => $request->filled('grade') ? $request->grade : null,
        ]);

        // リダイレクトで詳細画面へ
        return;
    }

    /**
     * 差し込み文章変換
     */
    private function replaceMailText($subject, $tool, $post, $student_user_id)
    {
        $mail_text = str_replace('[[student_name]]', $tool->getStudent(), $subject);
        $mail_text = str_replace('[[teacher_name]]', $tool->getTeachersName('role_article_admin'), $mail_text);
        $mail_text = str_replace('[[post_title]]', strip_tags($post->post_title), $mail_text);

        // 課題URL
        $task_url = url('/').'/plugin/learningtasks/show/'.$this->page->id.'/'.$this->frame->id.'/'.$post->id.'#frame-'.$this->frame->id;
        $mail_text = str_replace('[[task_url]]', $task_url, $mail_text);

        // 評価する受講者を指定した課題URL
        $teacher_task_url = url('/').'/redirect/plugin/learningtasks/switchUserUrl/'.$this->page->id.'/'.$this->frame->id.'/'.$post->id.'?student_id='.$student_user_id.'#frame-'.$this->frame->id;
        $mail_text = str_replace('[[teacher_task_url]]', $teacher_task_url, $mail_text);
        return $mail_text;
    }

    /**
     * メール文面
     */
    private function getMailFormat($task_status, $tool)
    {
        // 初期値
        $mail_subjects = array(
            1 => $tool->getMailConfig('subject', $task_status, 0, 'レポートが提出されました。'),
            2 => $tool->getMailConfig('subject', $task_status, 0, 'レポートの評価が登録されました。'),
            3 => $tool->getMailConfig('subject', $task_status, 0, 'レポートにコメントが登録されました。'),
            5 => $tool->getMailConfig('subject', $task_status, 0, '試験の解答が提出されました。'),
            6 => $tool->getMailConfig('subject', $task_status, 0, '試験の評価が登録されました。'),
            7 => $tool->getMailConfig('subject', $task_status, 0, '試験のコメントが登録されました。'),
            8 => $tool->getMailConfig('subject', $task_status, 0, '総合評価が登録されました。'),
        );
        $mail_bodys = array(
            1 => $tool->getMailConfig('body', $task_status, 0, "「[[post_title]]」のレポートが提出されました。\n評価をお願いします。\n[[teacher_task_url]]\n"),
            2 => $tool->getMailConfig('body', $task_status, 0, "「[[post_title]]」のレポートの評価が登録されました。\n確認をお願いします。\n[[task_url]]\n"),
            3 => $tool->getMailConfig('body', $task_status, 0, "「[[post_title]]」にコメントが登録されました。\n確認をお願いします。\n[[task_url]]\n"),
            5 => $tool->getMailConfig('body', $task_status, 0, "「[[post_title]]」に試験の解答が提出されました。\n評価をお願いします。\n[[teacher_task_url]]\n"),
            6 => $tool->getMailConfig('body', $task_status, 0, "「[[post_title]]」の試験の評価が登録されました。\n確認をお願いします。\n[[task_url]]\n"),
            7 => $tool->getMailConfig('body', $task_status, 0, "「[[post_title]]」に試験のコメントが登録されました。\n確認をお願いします。\n[[task_url]]\n"),
            8 => $tool->getMailConfig('body', $task_status, 0, "「[[post_title]]」の総合評価が登録されました。\n確認をお願いします。\n[[task_url]]\n"),
        );
        return array($mail_subjects, $mail_bodys);
    }

    /**
     *  メール送信
     */
    private function sendMailLocal($post, $task_status, $tool, $student_user_id)
    {
        if ($task_status == 1) {
            // レポートの提出(1). メール送信（教員宛）off ならメール送信しない
            if (! $tool->checkFunction(LearningtaskUseFunction::use_report_mail)) {
                return;
            }
        } elseif ($task_status == 2) {
            // レポートの評価(2). メール送信（受講者宛）off ならメール送信しない
            if (! $tool->checkFunction(LearningtaskUseFunction::use_report_evaluate_mail)) {
                return;
            }
        } elseif ($task_status == 3) {
            // レポートのコメント(3). メール送信（受講者宛）off ならメール送信しない
            if (! $tool->checkFunction(LearningtaskUseFunction::use_report_reference_mail)) {
                return;
            }
        } elseif ($task_status == 5) {
            // 試験の提出(5). メール送信（教員宛）off ならメール送信しない
            if (! $tool->checkFunction(LearningtaskUseFunction::use_examination_mail)) {
                return;
            }
        } elseif ($task_status == 6) {
            // 試験の評価(6). メール送信（受講者宛）off ならメール送信しない
            if (! $tool->checkFunction(LearningtaskUseFunction::use_examination_evaluate_mail)) {
                return;
            }
        } elseif ($task_status == 7) {
            // 試験のコメント(7). メール送信（受講者宛）off ならメール送信しない
            if (! $tool->checkFunction(LearningtaskUseFunction::use_examination_reference_mail)) {
                return;
            }
        } elseif ($task_status == 8) {
            // 総合評価(8). メール送信（受講者宛）off ならメール送信しない
            if (! $tool->checkFunction(LearningtaskUseFunction::use_evaluate_mail)) {
                return;
            }
        }

        // 送信するユーザオブジェクト
        $send_user = null;

        // メールの定型文取得
        list($mail_subjects, $mail_bodys) = $this->getMailFormat($task_status, $tool);
        $mail_footer = $tool->getMailConfig('footer', 0);

        // 教員へメールを送信。レポートの提出(1)、試験の提出(5)
        if ($task_status == 1 || $task_status == 5) {
            $send_users = $tool->getTeachers();
        }
        // 受講者へメールを送信。レポートの評価(2)、レポートのコメント(3)、試験の評価(6)、試験のコメント(7)、総合評価(8)
        if ($task_status == 2 || $task_status == 3 || $task_status == 6 || $task_status == 7 || $task_status == 8) {
            $send_users = User::where('id', $student_user_id)->get();
        }

        // 件名、本文、フッターの変換
        $mail_body = $this->replaceMailText($mail_bodys[$task_status], $tool, $post, $student_user_id);
        $mail_body = $mail_body . "\n" . $this->replaceMailText($mail_footer, $tool, $post, $student_user_id);
        $mail_subject = $this->replaceMailText($mail_subjects[$task_status], $tool, $post, $student_user_id);

        foreach ($send_users as $send_user) {
            // メールアドレスがなければ終了
            if (empty($send_user->email)) {
                continue;
            }
            try {
                Mail::to(trim($send_user->email))->send(new ConnectMail(['subject' => $mail_subject, 'template' => 'mail.send'], ['content' => $mail_body]));
                session()->flash('plugin_errors', 'メール送信OK');
            } catch (\Exception $e) {
                session()->flash('plugin_errors', 'メール送信に失敗しました。<br />運営組織に連絡をお願いいたします。');
            }
        }
        return;
    }

    /**
     * 進捗ステータス削除
     */
    public function deleteStatus($request, $page_id, $frame_id, $id)
    {
        // 進捗ステータスを削除する。
        LearningtasksUsersStatuses::find($id)->delete();
    }

    /**
     *  進捗ステータス更新
     */
    //public function changeStatus___($request, $page_id, $frame_id, $id = null)
    //{
    //    // 権限チェック（deleteCategories 関数は標準チェックにないので、独自チェック）
    //    $user = Auth::user();
    //    if (empty($user)) {
    //        return $this->viewError("403_inframe", null, "ログインしないとできない処理です。");
    //    }

    //    // upload 用変数
    //    $upload = null;

    //    // 「修了」の場合に手書き画像があれば保存する。
    //    if ($request->task_status == "1" && $request->has('handwriting') && !empty($request->get('handwriting'))) {
    //        $imagedata = base64_decode($request->get('handwriting'));

    //        // uploads テーブルに情報追加、ファイルのid を取得する
    //        $upload = Uploads::create([
    //            'client_original_name' => '手書き回答.png',
    //            'mimetype'             => 'image/png',
    //            'extension'            => 'png',
    //            'size'                 => strlen($imagedata),
    //            'plugin_name'          => 'learningtasks',
    //            'page_id'              => $page_id,
    //            'private'              => 1,
    //         ]);

    //        // 課題ファイル保存
    //        $directory = $this->getDirectory($upload->id);
    //        Storage::put($directory . '/' . $upload->id . ".png", $imagedata);
    //    }

    //    // 「取り消し」の場合に手書き画像があれば削除する。
    //    if ($request->task_status == "0") {
    //        $learningtasks_users_status = LearningtasksUsersStatuses::where('contents_id', $id)->where('user_id', $user->id)->first();
    //        // 手書き画像ファイルの確認
    //        if ($learningtasks_users_status->upload_id) {
    //            // アップロードテーブルの削除
    //            Uploads::destroy($learningtasks_users_status->upload_id);
    //            // アップロードファイルの削除
    //            $directory = $this->getDirectory($learningtasks_users_status->upload_id);
    //            Storage::delete($directory . '/' . $learningtasks_users_status->upload_id . ".png");
    //        }
    //    }

    //    // ユーザーの進捗ステータス
    //    LearningtasksUsersStatuses::updateOrCreate(
    //        ['contents_id' => $id, 'user_id' => $user->id],
    //        [
    //         'contents_id' => $id,
    //         'user_id'     => $user->id,
    //         'task_status' => $request->task_status,
    //         'upload_id'   => empty($upload) ? null : $upload->id,
    //        ]
    //    );

    //    return $this->index($request, $page_id, $frame_id);
    //}

    /**
     *  RSS配信
     */
    //public function rss($request, $page_id, $frame_id, $id = null)
    //{
    //    // 課題管理＆フレームデータ
    //    $learningtask = $this->getLearningTask($frame_id);
    //    if (empty($learningtask)) {
    //        return;
    //    }
    //
    //    // サイト名
    //    $base_site_name = Configs::where('name', 'base_site_name')->first();
    //
    //    // URL
    //    $url = url("/redirect/plugin/learningtasks/rss/" . $page_id . "/" . $frame_id);
    //
    //    // HTTPヘッダー出力
    //    header('Content-Type: text/xml; charset=UTF-8');
    //
    //    echo <<<EOD
    //<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" version="2.0">
    //<channel>
    //<title>[{$base_site_name->value}]{$learningtask->learningtasks_name}</title>
    //<description></description>
    //<link>
    //{$url}
    //</link>
    //EOD;
    //
    //    $learningtasks_posts = $this->getPosts($learningtask, $learningtask->rss_count);
    //    foreach ($learningtasks_posts as $learningtasks_post) {
    //        $title = $learningtasks_post->post_title;
    //        $link = url("/plugin/learningtasks/show/" . $page_id . "/" . $frame_id . "/" . $learningtasks_post->id);
    //        if (mb_strlen(strip_tags($learningtasks_post->post_text)) > 100) {
    //            $description = mb_substr(strip_tags($learningtasks_post->post_text), 0, 100) . "...";
    //            $replaceTarget = array('<br>', '&nbsp;', '&emsp;', '&ensp;');
    //            $description = str_replace($replaceTarget, '', $description);
    //        } else {
    //            $description = strip_tags($learningtasks_post->post_text);
    //            $replaceTarget = array('<br>', '&nbsp;', '&emsp;', '&ensp;');
    //            $description = str_replace($replaceTarget, '', $description);
    //        }
    //        $pub_date = date(DATE_RSS, strtotime($learningtasks_post->posted_at));
    //        $content = strip_tags(html_entity_decode($learningtasks_post->post_text));
    //        echo <<<EOD
    //
    //<item>
    //<title>{$title}</title>
    //<link>{$link}</link>
    //<description>{$description}</description>
    //<pubDate>{$pub_date}</pubDate>
    //<content:encoded>{$content}</content:encoded>
    //</item>
    //EOD;
    //    }

/*
<title>{$title}</title>
<link>{$link}</link>
<description>{$description}</description>
<pubDate>{$pub_date}</pubDate>
<content:encoded>{$content}</content:encoded>
*/
//echo $rss_text;

    //    echo <<<EOD
    //</channel>
    //</rss>
    //EOD;
    //
    //    exit;
    //}

    /**
     * ユーザ関係編集画面
     */
    public function editUsers($request, $page_id, $frame_id, $post_id)
    {
        // 課題取得
        $post = $this->getPost($post_id);

        // 配置されているページのメンバーシップの対象ユーザ取得
        // 複数のページにプラグインは配置されている可能性を考慮
        $pages = Page::select('pages.*')
                     ->join('frames', function ($join) use ($post) {
                         $join->on('frames.page_id', '=', 'pages.id')
                              ->where('frames.bucket_id', '=', $post->bucket_id);
                     })
                     ->where('pages.membership_flag', 1)
                     ->orderBy('pages._lft')
                     ->get();

        // グループID 取得のために、配置されているページRoleを取得
        $page_roles = PageRole::select('group_id')->whereIn('page_id', $pages->pluck('id'))->groupBy('group_id')->get();

        // グループのユーザを取得
        $group_users = GroupUser::select('user_id')->whereIn('group_id', $page_roles->pluck('group_id'))->groupBy('user_id')->get();

        // 学生のみ取得
        $students = UsersRoles::whereIn('users_id', $group_users->pluck('user_id'))
                              ->where('target', 'original_role')
                              ->where('role_name', RoleName::student)
                              ->where('role_value', 1)
                              ->get();

        // メンバーシップのユーザ情報を取得
        // この時、すでに権限付与済みのユーザも紐づける。
        $membership_users = User::select('users.*', 'learningtasks_users.user_id AS join_user_id')
                                ->leftJoin('learningtasks_users', function ($join) use ($post) {
                                    $join->on('learningtasks_users.user_id', '=', 'users.id')
                                         ->where('learningtasks_users.post_id', '=', $post->id)
                                         ->where('learningtasks_users.role_name', RoleName::student)
                                         ->whereNull('learningtasks_users.deleted_at');
                                })
                                ->whereIn('users.id', $students->pluck('users_id'))
                                ->orderBy('id', 'asc')
                                ->get();


        // 教員のみ取得
        $teachers = UsersRoles::whereIn('users_id', $group_users->pluck('user_id'))
                              ->where('target', 'original_role')
                              ->where('role_name', RoleName::teacher)
                              ->where('role_value', 1)
                              ->get();

        // メンバーシップのユーザ情報を取得
        // この時、すでに権限付与済みのユーザも紐づける。
        $membership_teacher_users = User::select('users.*', 'learningtasks_users.user_id AS join_user_id')
                                ->leftJoin('learningtasks_users', function ($join) use ($post) {
                                    $join->on('learningtasks_users.user_id', '=', 'users.id')
                                         ->where('learningtasks_users.post_id', '=', $post->id)
                                         ->where('learningtasks_users.role_name', RoleName::teacher)
                                         ->whereNull('learningtasks_users.deleted_at');
                                })
                                ->whereIn('users.id', $teachers->pluck('users_id'))
                                ->orderBy('id', 'asc')
                                ->get();

        // 課題管理＆フレームデータ
        $learningtask = $this->getLearningTask($frame_id);

        // 課題管理ツール
        $tool = new LearningtasksTool($request, $page_id, $learningtask, $post, $frame_id);

        // 画面を呼び出す。
        return $this->view(
            'learningtasks_edit_users', [
            'learningtasks_posts'      => $post,
            'membership_users'         => $membership_users,
            'membership_teacher_users' => $membership_teacher_users,
            'tool'                     => $tool,
            ]
        );
    }

    /**
     * ユーザ関係保存画面
     */
    public function saveUsers($request, $page_id, $frame_id, $post_id)
    {
        // 参加方式の更新
        $post = LearningtasksPosts::find($post_id);
        if (empty($post)) {
            return $this->editUsers($request, $page_id, $frame_id, $post_id);
        }

        $post->student_join_flag = $request->student_join_flag;
        $post->teacher_join_flag = $request->teacher_join_flag;
        $post->save();

        // 受講者

        // 画面のチェックボックスのユーザIDを一度ローカル変数にしておく。
        // 1件もチェックされていないと、null になり、処理中で毎回、配列化を聞くことになるため、
        // ここで、nullなら、空の配列にしておく。
        $join_users = $request->join_users;
        if (empty($join_users)) {
            $join_users = array();
        }

        // ページ中に1件でもユーザがいる場合はループして処理する。
        if ($request->filled('page_users')) {
            foreach ($request->page_users as $page_user_id) {
                $learningtasks_users = LearningtasksUsers::where('post_id', $post_id)
                                                         ->where('user_id', $page_user_id)
                                                         ->where('role_name', RoleName::student)
                                                         ->whereNull('deleted_at')
                                                         ->first();
                // 参加データの追加・削除
                if (!empty($learningtasks_users) && !in_array($page_user_id, $join_users)) {
                    // 削除（参加データはあり、画面のチェックはない）
                    $learningtasks_users->delete();
                } elseif (empty($learningtasks_users) && in_array($page_user_id, $join_users)) {
                    // 追加（参加データはなし、画面のチェックはあり）
                    LearningtasksUsers::create(['post_id' => $post_id, 'user_id' => $page_user_id, 'role_name' => RoleName::student]);
                }
            }
        }

        // 教員

        // 画面のチェックボックスのユーザIDを一度ローカル変数にしておく。
        // 1件もチェックされていないと、null になり、処理中で毎回、配列化を聞くことになるため、
        // ここで、nullなら、空の配列にしておく。
        $join_users = $request->join_teacher_users;
        if (empty($join_users)) {
            $join_users = array();
        }

        // ページ中に1件でもユーザがいる場合はループして処理する。
        if ($request->filled('page_teacher_users')) {
            foreach ($request->page_teacher_users as $page_user_id) {
                $learningtasks_users = LearningtasksUsers::where('post_id', $post_id)
                                                         ->where('user_id', $page_user_id)
                                                         ->where('role_name', RoleName::teacher)
                                                         ->whereNull('deleted_at')
                                                         ->first();
                // 参加データの追加・削除
                if (!empty($learningtasks_users) && !in_array($page_user_id, $join_users)) {
                    // 削除（参加データはあり、画面のチェックはない）
                    $learningtasks_users->delete();
                } elseif (empty($learningtasks_users) && in_array($page_user_id, $join_users)) {
                    // 追加（参加データはなし、画面のチェックはあり）
                    LearningtasksUsers::create(['post_id' => $post_id, 'user_id' => $page_user_id, 'role_name' => RoleName::teacher]);
                }
            }
        }

        // 設定内容を保存（一旦削除して新たに保存）
        LearningtasksUseSettings::where('learningtasks_id', $post->learningtasks_id)
                                ->where('post_id', $post->id)
                                ->where('use_function', 'use_need_auth')
                                ->delete();
        if ($request->filled('use_need_auth')) {
            LearningtasksUseSettings::create([
                'learningtasks_id' => $post->learningtasks_id,
                'post_id'          => $post->id,
                'use_function'     => 'use_need_auth',
                'value'            => $request->use_need_auth,
            ]);
        }

        return $this->editUsers($request, $page_id, $frame_id, $post_id);
    }

    /**
     * 課題系ファイル閲覧チェック（ファイルダウンロード処理から呼ばれる）
     */
    public static function checkUploadPost($request, $upload)
    {
        // ファイルを閲覧する権限、条件があるかチェックする。

        // 課題ファイル、試験問題ファイルなど、課題に紐づくファイルの閲覧権限のチェック
        // upload_id でファイルを探せば、あれば 1つだけのはず。
        $learningtasks_posts_file = LearningtasksPostsFiles::where('upload_id', $upload->id)->first();

        // ない場合は、閲覧させない。
        // もとは課題やレポートのアップロードだったけれど、課題やレポートが消されている。などを考慮。
        if (empty($learningtasks_posts_file)) {
            return [false, '対象ファイルなし'];
        }

        // 課題と課題セットを取得
        $post = LearningtasksPosts::find($learningtasks_posts_file->post_id);
        if (empty($post)) {
            return [false, '課題（POST）なし'];
        }
        $learningtask = Learningtasks::find($post->learningtasks_id);
        if (empty($learningtask)) {
            return [false, '課題セットなし'];
        }

        // Bucket からFrame, Page とつないで、配置しているページを確認
        // 複数ページに配置も可能なため、ページ単位のチェックが必要。
        $frames = Frame::where('bucket_id', $learningtask->bucket_id)->get();

        // LearningtasksTool クラスの各種メソッドを利用する。
        foreach ($frames as $frame) {
            // 課題管理ツールを利用してチェックする。
            $tool = new LearningtasksTool($request, $frame->page_id, $learningtask, $post, $frame->id);

            // 課題に対する権限はあるか。
            // この結果がNG でも、複数ページの場合に次のページをチェックするため、return false はしない。
            if ($tool->canPostView()) {
                return [true, 'OK'];
            }
        }
        return [false, '課題関係のファイルに対する権限なし'];
    }

    /**
     * ファイル閲覧チェック（ファイルダウンロード処理から呼ばれる）
     */
    public static function checkUploadUsersStatus($request, $upload)
    {
        // ファイルを閲覧する権限、条件があるかチェックする。

        // 提出ファイル、添削問題ファイルなど、提出に紐づくファイルの閲覧権限のチェック
        // upload_id でファイルを探せば、あれば 1つだけのはず。
        $learningtasks_users_status = LearningtasksUsersStatuses::where('upload_id', $upload->id)->first();

        // ない場合は、閲覧させない。
        // もとは課題やレポートのアップロードだったけれど、課題やレポートが消されている。などを考慮。
        if (empty($learningtasks_users_status)) {
            return [false, '対象ファイルなし'];
        }

        // 課題と課題セットを取得
        $post = LearningtasksPosts::find($learningtasks_users_status->post_id);
        if (empty($post)) {
            return [false, '課題（POST）なし'];
        }
        $learningtask = Learningtasks::find($post->learningtasks_id);
        if (empty($learningtask)) {
            return [false, '課題セットなし'];
        }

        // Bucket からFrame, Page とつないで、配置しているページを確認
        // 複数ページに配置も可能なため、ページ単位のチェックが必要。
        $frames = Frame::where('bucket_id', $learningtask->bucket_id)->get();

        // LearningtasksTool クラスの各種メソッドを利用する。
        foreach ($frames as $frame) {
            // 課題管理ツールを利用してチェックする。
            $tool = new LearningtasksTool($request, $frame->page_id, $learningtask, $post, $frame->id);

            // 提出に対する権限はあるか。
            // この結果がNG でも、複数ページの場合に次のページをチェックするため、return false はしない。
            if ($tool->canPostView()) {
                return [true, 'OK'];
            }
        }
        return [false, '提出関係のファイルに対する権限なし'];
    }

    // delete: 権限設定廃止のためコメントアウト
    // /**
    //  * 権限設定 変更画面
    //  */
    // public function editBucketsRoles($request, $page_id, $frame_id, $id = null, $use_approval = true)
    // {
    //     // 承認は使用しない
    //     $use_approval = false;
    //     return parent::editBucketsRoles($request, $page_id, $frame_id, $id, $use_approval);
    // }

    /**
     * 機能選択編集画面
     */
/*
    public function selectFunction($request, $page_id, $frame_id, $post_id)
    {
        // 課題管理
        $learningtask = $this->getLearningTask($frame_id);

        // 課題取得
        $post = $this->getPost($post_id);

        // 画面を呼び出す。
        return $this->view(
            'learningtasks_select_function', [
            'learningtask'      => $learningtask,
            'learningtasks_posts'      => $post,
            ]
        );
    }
*/
}
