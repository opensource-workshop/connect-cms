<?php

namespace App\Plugins\User\Covids;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;
use Session;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\User\Covids\Covid;
use App\Models\User\Covids\CovidDailyReport;

use App\Plugins\User\UserPluginBase;

/**
 * 感染症数値集計プラグイン
 *
 * 感染症数値を集計してグラフで表示するプラグイン。
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 感染症数値集計プラグイン(covid)
 * @package Contoroller
 */
class CovidsPlugin extends UserPluginBase
{

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = [
            'getData',
        ];
        $functions['post'] = [
            'change',
        ];
        return $functions;
    }

    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = array();
        $role_ckeck_table["getData"]   = array('role_arrangement');
        $role_ckeck_table["change"]   = array('role_arrangement');
        return $role_ckeck_table;
    }

    /* オブジェクト変数 */

    /**
     * POSTデータ
     */
    public $post = null;

    /**
     *  Covid データ取得
     */
    private function getCovidFrame($frame_id)
    {
        $covid = Covid::select('covids.*')
                      ->join('frames', function ($join) use ($frame_id) {
                          $join->on('frames.bucket_id', '=', 'covids.bucket_id')
                               ->where('frames.id', '=', $frame_id);
                      })
                      ->first();
        return $covid;
    }

    /**
     *  データ取得
     */
    private function getDailyReports($frame_id)
    {
        // buckets_id
        $buckets_id = null;
        if (!empty($this->buckets)) {
            $buckets_id = $this->buckets->id;
        }

        // Bucketsに応じたデータを返す。

        $covid_daily_reports = 
            CovidDailyReport::select(
                DB::raw("country_region, sum(confirmed) as sum_confirmed, sum(deaths) as sum_deaths")
            )
            ->groupBy("country_region")
            ->get();

        return $covid_daily_reports;
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // データ取得
        $covid_daily_reports = $this->getDailyReports($frame_id);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'covids', [
            'covid_daily_reports' => $covid_daily_reports,
            ]
        );
    }

    /**
     * データセット新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id)
    {
        // 新規作成フラグを付けてデータセット設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $create_flag);
    }

    /**
     * データセット設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $create_flag = false)
    {
        // データセット定義
        // 新規作成の場合は、空。変更の場合は配置されているフレームから引っ張ってくる。
        if ($create_flag) {
            $covid = new Covid();
        }
        else {
            $covid = $this->getCovidFrame($frame_id);
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'edit_covid', [
            'covid'=> $covid,
            ]
        )->withInput($request->all);
    }

    /**
     *  データセット登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id)
    {
        // デフォルトでチェック
        $validator_values['covids_name'] = ['required'];
        $validator_values['source_base_url'] = ['required'];

        $validator_attributes['covids_name'] = 'データセット名';
        $validator_attributes['source_base_url'] = 'データの基本URL';

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_values);
        $validator->setAttributeNames($validator_attributes);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            if ($request->filled('covid_id')) {
                return $this->editBuckets($request, $page_id, $frame_id, true)->withErrors($validator);
            } else {
                return $this->createBuckets($request, $page_id, $frame_id)->withErrors($validator);
            }
        }

        // Covid データの確認
        $covid = Covid::find($request->covid_id);

        // バケツデータ更新 or 追加
        $buckets = Buckets::updateOrCreate(
            ['id' => $covid->bucket_id],
            [
             'bucket_name' => $request->covids_name,
             'plugin_name' => 'covid',
            ]
        );

        // Covid データ更新 or 追加
        $covid = Covid::updateOrCreate(
            ['id' => $request->covid_id],
            ['bucket_id' => $buckets->id,
             'covids_name' => $request->covids_name,
             'source_base_url' => $request->source_base_url]
        );

        $this->cc_massage = 'Covid 設定を保存しました。';

        // Covid 変更画面を開く
        return $this->editBuckets($request, $page_id, $frame_id);
    }

    /**
     * データ編集用表示関数
     * コアが編集画面表示の際に呼び出す関数
     */
    public function edit($request, $page_id, $frame_id, $id = null)
    {
        // データ取得
        $contents = $this->getFrameContents($frame_id);

        // データがない場合は、新規登録用画面
        if (empty($contents)) {
            // 新規登録画面を呼び出す
            return $this->view(
                'contents_create', [
                ]
            );
        } else {
            // 編集画面テンプレートを呼び出す。
            return $this->view(
                'contents_edit', [
                'contents' => $contents,
                ]
            );
        }
    }

    /**
     *  URL からデータのインポート
     */
    public function getData($request, $page_id, $frame_id)
    {
        // 表示テンプレートを呼び出す。
        return $this->view(
            'get_data', [
//            'contents' => $contents,
            ]
        );
    }

    /**
     *  データ詳細表示関数
     *  コアがデータ削除の確認用に呼び出す関数
     */
    public function show($request, $page_id, $frame_id, $id = null)
    {
        // 権限チェック
        // 固定記事プラグインの特別処理。削除のための表示であり、フレーム画面のため、個別に権限チェックする。
        if ($this->can('frames.delete')) {
            return $this->view_error(403);
        }

        // データ取得
        $contents = $this->getFrameContents($frame_id);

        // データの存在確認をして、画面を切り替える
        if (empty($contents)) {
            // データなしの表示テンプレートを呼び出す。
            return $this->view(
                'contents_edit_nodata', [
                'contents' => null,
                ]
            );
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'contents_show', [
            'contents' => $contents,
            ]
        );
    }

   /**
    * データ新規登録関数
    */
    public function store($request, $page_id = null, $frame_id = null, $id = null, $status = 0)
    {
        // バケツがまだ登録されていなかったら登録する。
        if (empty($this->buckets)) {
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => '無題',
                  'plugin_name' => 'contents'
            ]);
        } else {
            $bucket_id = $this->buckets['id'];
        }

        // コンテンツデータの登録
        $contents = new Contents;
        $contents->created_id   = Auth::user()->id;
        $contents->bucket_id    = $bucket_id;
        $contents->content_text = $request->contents;

        // 一時保存(status が 1 になる。)
        if ($status == 1) {
            $contents->status = 1;
        } elseif ($this->isApproval($frame_id)) {
            // 承認フラグ(要承認の場合はstatus が 2 になる。)
            $contents->status = 2;
        } else {
            $contents->status = 0;
        }

        $contents->save();

        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
                  ->update(['bucket_id' => $bucket_id]);

        return;
    }

   /**
    * データ更新（確定）関数
    */
    public function update($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
        $oldrow = Contents::find($id);

        // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
        $newrow = $oldrow->replicate();
        $newrow->content_text = $request->contents;

        // 承認フラグ(要承認の場合はstatus が2 になる。)
        if ($this->isApproval($frame_id)) {
            $newrow->status = 2;
        } else {
            $newrow->status = 0;
        }

        // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)ただし、承認待ちレコード作成時は対象外
        if ($newrow->status != 2) {
            Contents::where('bucket_id', $oldrow->bucket_id)->where('status', 0)->update(['status' => 9]);
        }
        //Contents::where('id', $oldrow->id)->update(['status' => 9]);

        // 変更のデータ保存
        $newrow->save();

        return;
    }

   /**
    * データ一時保存関数
    */
    public function temporarysave($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 新規で一時保存しようとしたときは id、レコードがまだない。
        if (empty($id)) {
            $status = 1;
            $this->store($request, $page_id, $frame_id, $id, $status);
        } else {
            // 旧データ取得
            $oldrow = Contents::find($id);

            // 旧レコードが表示でなければ、履歴に更新（表示を履歴に更新すると、画面に表示されなくなる）
// 過去のステータスも残す方式にする。
//            if ($oldrow->status != 0) {
//                Contents::where('id', $id)->update(['status' => 9]);
//            }

            // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
            $newrow = $oldrow->replicate();
            $newrow->content_text = $request->contents;
            $newrow->status = 1; //（一時保存）
            $newrow->save();
        }
        return;
    }

   /**
    * 承認
    */
    public function approval($request, $page_id = null, $frame_id = null, $id = null)
    {
        // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
        $oldrow = Contents::find($id);

        // 旧レコードのstatus 更新(Activeなもの(status:0)は、status:9 に更新。他はそのまま。)
        Contents::where('bucket_id', $oldrow->bucket_id)->where('status', 0)->update(['status' => 9]);

        // 新しいレコードの登録（旧レコードのコピー＆内容の入れ替え）
        $newrow = $oldrow->replicate();
        $newrow->status = 0;
        $newrow->save();

        return;
    }

   /**
    * データ削除関数
    */
    public function delete($request, $page_id = null, $frame_id = null, $id = null)
    {
        // id がある場合、コンテンツを削除
        if ($id) {
            // Contents データ
            $content = Contents::where('id', $id)->first();

            // フレームも同時に削除するがチェックされていたらフレームを削除する。
            if ($request->frame_delete_flag == "1") {
                Frame::destroy($frame_id);
            }

            // 論理削除のため、コンテンツデータを status:9 に変更する。バケツデータは削除しない。
// 過去のステータスも残す方式にする。
//            Contents::where('id', $id)->update(['status' => 9]);

            // 削除ユーザの更新
            Contents::where('bucket_id', $content->bucket_id)->update(['deleted_id' => Auth::user()->id, 'deleted_name' => Auth::user()->name]);

            // 同じbucket_id のものを削除
            Contents::where('bucket_id', $content->bucket_id)->delete();
        }
        return;
    }

   /**
    * データ紐づけ変更関数
    */
    public function change($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
               ->update(['bucket_id' => $request->select_bucket]);
        return;
    }
}
