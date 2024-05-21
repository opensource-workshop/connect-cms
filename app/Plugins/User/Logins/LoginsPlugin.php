<?php

namespace App\Plugins\User\Logins;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\User\Logins\Login;

use App\Plugins\User\UserPluginBase;

/**
 * ログイン・プラグイン
 * ログインのためのプラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ログイン・プラグイン
 * @package Plugin
 * @plugin_title タブ
 * @plugin_desc 任意の場所にログイン画面を表示するプラグインです。
 */
class LoginsPlugin extends UserPluginBase
{
    /* オブジェクト変数 */

    /**
     * POST チェックに使用する getPost() 関数を使うか
     */
    public $use_getpost = false;

    /* コアから呼び出す関数 */

    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        return $functions;
    }

    /**
     * 追加の権限定義（コアから呼び出す）
     */
    public function declareRole()
    {
        // 標準権限以外で設定画面などから呼ばれる権限の定義
        // 標準権限は右記で定義 config/cc_role.php
        //
        // 権限チェックテーブル
        $role_check_table = [];

        return $role_check_table;
    }

    /**
     * プラグインのバケツ取得関数
     */
    private function getPluginBucket($bucket_id)
    {
        // プラグインのメインデータを取得する。
        return Login::firstOrNew(['bucket_id' => $bucket_id]);
    }

    /* 画面アクション関数 */

    /**
     *  初期表示取得関数
     *
     * @return view
     * @method_title タブ表示
     * @method_desc 画面をリロードすることなく、タブでフレームを切り替えることができます。
     * @method_detail 固定記事以外にも新着など、異なるプラグインを組み合わせることもできます。表示するのは各プラグインの初期画面です。
     */
    public function index($request, $page_id, $frame_id)
    {
        // ログイン後の指定画面があれば、指定画面へ
        if (!empty($this->buckets)) {
            $login = $this->getPluginBucket($this->buckets->id);
            if (!empty($login->redirect_page)) {
                session()->flash('url.intended', $login->redirect_page);
            }
        }

        return $this->view('logins', [
            'page_id'   => $page_id,
        ]);
    }

    /**
     * プラグインのバケツ選択表示関数
     *
     * @method_title 選択
     * @method_desc このフレームに表示するログインを選択します。
     * @method_detail
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // 表示テンプレートを呼び出す。
        return $this->view('login_buckets', [
            'plugin_buckets' => Login::orderBy('created_at', 'desc')->paginate(10),
        ]);
    }

    /**
     * バケツ新規作成画面
     *
     * @method_title 作成
     * @method_desc ログインを新しく作成します。
     * @method_detail ログイン名を入力してログインを作成できます。
     */
    public function createBuckets($request, $page_id, $frame_id)
    {
        // 処理的には編集画面を呼ぶ
        return $this->editBuckets($request, $page_id, $frame_id);
    }

    /**
     * バケツ設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id)
    {
        // コアがbucket_id なしで呼び出してくるため、bucket_id は frame_id から探す。
        if ($this->action == 'createBuckets') {
            $bucket_id = null;
        } else {
            $bucket_id = $this->getBucketId();
        }

        // ページデータの取得(laravel-nestedset 使用)
        $return_obj = 'flat';
        $pages_select = Page::defaultOrderWithDepth($return_obj);

        // 表示テンプレートを呼び出す。
        return $this->view('bucket', [
            // 表示中のバケツデータ
            'login' => $this->getPluginBucket($bucket_id),
            'pages_select' => $pages_select,
        ]);
    }

    /**
     *  バケツ登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $bucket_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
        ]);
        $validator->setAttributeNames([
            'name' => 'ログイン名',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // バケツの取得。なければ登録。
        $bucket = Buckets::updateOrCreate(
            ['id' => $bucket_id],
            ['bucket_name' => $request->name, 'plugin_name' => 'logins'],
        );

        // フレームにバケツの紐づけ
        $frame = Frame::find($frame_id)->update(['bucket_id' => $bucket->id]);

        // プラグインバケツを取得(なければ新規オブジェクト)
        // プラグインバケツにデータを設定して保存
        $login = $this->getPluginBucket($bucket->id);
        $login->name = $request->name;
        $login->redirect_page = $request->redirect_page;
        $login->save();

        // 登録後はリダイレクトして編集ページを開く。
        return new Collection(['redirect_path' => url('/') . "/plugin/logins/editBuckets/" . $page_id . "/" . $frame_id . "/" . $bucket->id . "#frame-" . $frame_id]);
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $id)
    {
        // deleted_id, deleted_nameを自動セットするため、複数件削除する時はdestroy()を利用する。

        // プラグインバケツの取得
        $login = Login::find($id);
        if (empty($login)) {
            return;
        }

        // FrameのバケツIDの更新
        Frame::where('bucket_id', $login->bucket_id)->update(['bucket_id' => null]);

        // バケツ削除
        Buckets::destroy($login->bucket_id);

        // プラグインデータ削除
        $login->delete();

        return;
    }

    /**
     * データ紐づけ変更関数
     */
    public function changeBuckets($request, $page_id, $frame_id)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)->update(['bucket_id' => $request->select_bucket]);

        return;
    }
}
