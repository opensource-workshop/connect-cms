<?php

namespace App\Plugins\User\Blogs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

use DB;

use App\Buckets;
use App\Blogs;
use App\BlogsPosts;

use App\Frame;
use App\Page;

use App\Plugins\User\UserPluginBase;
/**
 * ブログプラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category コンテンツプラグイン
 * @package Contoroller
 */
class BlogsPlugin extends UserPluginBase
{

    /**
     *  紐づくブログID とフレームデータの取得
     */
    public function getFrames($frame_id)
    {
        // Frame データ
        $frame = DB::table('frames')
                 ->select('frames.*', 'blogs.id as blogs_id', 'blogs.view_count')
                 ->leftJoin('blogs', 'blogs.bucket_id', '=', 'frames.bucket_id')
                 ->where('frames.id', $frame_id)->first();
        return $frame;
    }

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // Frame データ
        $frame = $this->getFrames($frame_id);
        if (empty($frame)) {
            return;
        }

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // データ取得（1ページの表示件数指定）
        $blogs_posts = BlogsPosts::orderBy('created_at', 'desc')
                       ->where('blogs_id', $frame->blogs_id)
                       ->paginate($frame->view_count);

        // 表示テンプレートを呼び出す。
        return view(
            $this->getViewPath('blogs'), [
            'page'        => $page,
            'frame'       => $frame,
            'frame_id'    => $frame_id,
            'blogs_posts' => $blogs_posts,
        ]);
    }

    /**
     *  編集画面の最初のタブ
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "editBlog";
    }

    /**
     * ブログ設定変更画面の表示
     */
    public function editBlog($request, $page_id, $frame_id, $blogs_id = null, $create_flag = false)
    {
        // Frame データ
        $frame = $this->getFrames($frame_id);

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // ブログデータ
        $blog = new Blogs();

        // blogs_id が渡ってくればblogs_id が対象
        if (!empty($blogs_id)) {
            $blog = Blogs::where('id', $blogs_id)->first();
        }
        // Frame のbucket_id があれば、bucket_id からブログデータ取得、なければ、新規作成か選択へ誘導
        else if (!empty($frame->bucket_id) && $create_flag == false) {
            $blog = Blogs::where('bucket_id', $frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return view(
            $this->getViewPath('blogs_edit_blog'), [
            'frame_id'    => $frame_id,
            'page'        => $page,
            'frame'       => $frame,
            'blog'        => $blog,
            'create_flag' => $create_flag,
        ]);
    }

    /**
     * ブログ新規作成画面
     */
    public function createBlog($request, $page_id, $frame_id, $blogs_id = null)
    {
        // 新規作成フラグを付けてブログ設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBlog($request, $page_id, $frame_id, $blogs_id, $create_flag);
    }

    /**
     *  ブログ登録処理
     */
    public function saveBlogs($request, $page_id, $frame_id, $id = null)
    {
        // バケツIDの有無の確認、及び更新用のデータとするためにFrame の取得
        $frame = Frame::where('id', $frame_id)->first();

        // 画面から渡ってくるblogs_id が空ならバケツとブログを新規登録
        if (empty($request->blogs_id)) {

            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => '無題',
                  'plugin_name' => 'blogs'
            ]);

            // ブログデータ新規オブジェクト
            $blogs = new Blogs();
            $blogs->bucket_id = $bucket_id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            if (empty($frame->bucket_id)) {

                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);
            }
        }
        // blogs_id があれば、ブログを更新
        else {

            // ブログデータ取得
            $blogs = Blogs::where('id', $request->blogs_id)->first();
        }

        // ブログ設定
        $blogs->blog_name       = $request->blog_name;
        $blogs->view_count      = $request->view_count;

        // データ保存
        $blogs->save();
    }

    /**
     *  新規記事画面
     */
    public function create($request, $page_id, $frame_id, $id = null, $errors = null)
    {
        // Frame データ
        $frame = $this->getFrames($frame_id);

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // 空のデータ(画面で初期値設定で使用するため)
        $blogs_posts = new BlogsPosts();
        $blogs_posts->posted_at = date('Y-m-d H:i:s');

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return view(
            $this->getViewPath('blogs_input'), [
            'frame_id'    => $frame_id,
            'frame'       => $frame,
            'page'        => $page,
            'blogs_posts' => $blogs_posts,
            'errors'      => $errors,
        ])->withInput($request->all);
    }

    /**
     * 記事編集画面
     */
    public function edit($request, $page_id, $frame_id, $blogs_posts_id = null, $errors = null)
    {
        // Frame データ
        $frame = $this->getFrames($frame_id);

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // 記事取得
        $blogs_post = BlogsPosts::where('id', $blogs_posts_id)->first();

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return view(
            $this->getViewPath('blogs_input'), [
            'frame_id'    => $frame_id,
            'frame'       => $frame,
            'page'        => $page,
            'blogs_posts' => $blogs_post,
            'errors'      => $errors,
        ])->withInput($request->all);
    }

    /**
     *  ブログ記事登録処理
     */
    public function save($request, $page_id, $frame_id, $id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'post_title' => ['required'],
            'posted_at'  => ['required', 'date_format:Y-m-d H:i:s'],
            'post_text'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'post_title' => 'タイトル',
            'posted_at'  => '投稿日時',
            'post_text'  => '本文',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return ( $this->create($request, $page_id, $frame_id, $id, $validator->errors()) );
        }

        // id があれば更新、なければ登録
        if (empty($id)) {
            $blogs_post = new BlogsPosts();
        }
        else {
            $blogs_post = BlogsPosts::where('id', $id)->first();
        }

        // ブログ記事設定
        $blogs_post->blogs_id   = $request->blogs_id;
        $blogs_post->post_title = $request->post_title;
        $blogs_post->posted_at  = $request->posted_at;
        $blogs_post->post_text  = $request->post_text;

        // データ保存
        $blogs_post->save();

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  削除処理
     */
    public function destroy($request, $page_id, $frame_id, $id)
    {
        // id がある場合、データを削除
        if ( $id ) {

            // データを削除する。
            BlogsPosts::destroy($id);
        }
        // 削除後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     * データ選択表示関数
     */
    public function datalist($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $frame = DB::table('frames')
                 ->select('frames.*', 'blogs.id as blogs_id', 'blogs.view_count')
                 ->leftJoin('blogs', 'blogs.bucket_id', '=', 'frames.bucket_id')
                 ->where('frames.id', $frame_id)->first();

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // データ取得（1ページの表示件数指定）
        $blogs = Blogs::orderBy('created_at', 'desc')
                       ->paginate(10);

        // 表示テンプレートを呼び出す。
        return view(
            $this->getViewPath('blogs_edit_datalist'), [
            'frame_id' => $frame_id,
            'page'     => $page,
            'frame'    => $frame,
            'blogs'    => $blogs,
        ]);
    }

   /**
    * データ紐づけ変更関数
    */
    public function change($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
               ->update(['bucket_id' => $request->select_bucket]);

        return $this->datalist($request, $page_id, $frame_id, $id);
    }











    /**
     *  確認画面
     */
    public function confirm($request, $page_id, $frame_id, $id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'column_text' => ['required'],
        ]);
        $validator->setAttributeNames([
            'column_text' => 'テキスト',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            if (empty($id)) {
                return ( $this->create($request, $page_id, $frame_id, $validator->errors()) );
            }
            else {
                return ( $this->edit($request, $page_id, $frame_id, $id, $validator->errors()) );
            }
        }

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // データ取得
        $sampleform = Sampleforms::where('id', $id)->first();

        // アップロードファイルの定義
        $upload_files = array();

        // アップロードファイルが存在するかの確認
        if ($request->hasFile('column_file')) {

            // 確認中の一時ファイルとして保存
            $path = $request->file('column_file')->store('uploads/tmp');

            // オリジナルファイル名などのアップロードファイル情報を$upload_files 変数に保持
            $upload_files['column_file']['path'] = $path;
            $upload_files['column_file']['client_original_name'] = $request->file('column_file')->getClientOriginalName();
            $upload_files['column_file']['mimetype'] = $request->file('column_file')->getClientMimeType();
        }

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return view(
            $this->getViewPath('sampleforms_confirm'), [
            'frame_id'     => $frame_id,
            'id'           => $id,
            'page'         => $page,
            'sampleform'   => $sampleform,
            'upload_files' => $upload_files,
            'base_action'  => $request->base_action,
        ])->withInput($request->all);
    }

    /**
     *  保存画面
     */
    public function save_($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $frame = Frame::where('id', $frame_id)->first();

        // bucket の有無を確認して、なければ作成
        if (empty($frame->bucket_id)) {

            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => '無題',
                  'plugin_name' => 'sampleforms'
            ]);

            // FrameのバケツIDの更新
            Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);

        }
        else {
            $bucket_id = $frame->bucket_id;
        }

        // データ登録
        $sampleforms = new Sampleforms();
        $sampleforms->bucket_id       = $bucket_id;
        $sampleforms->form_name       = $request->form_name;
        $sampleforms->column_text     = $request->column_text;
        $sampleforms->column_password = $request->column_password;
        $sampleforms->column_radio    = $request->column_radio;
        $sampleforms->column_hidden   = $request->column_hidden;
        $sampleforms->column_textarea = $request->column_textarea;
        $sampleforms->column_select   = $request->column_select;
        $sampleforms->column_checkbox = (empty($request->column_checkbox) ? null : "|".implode("|", $request->column_checkbox)."|" );

        // アップロードファイルの取得
        if (!empty($request->upload_files)) {
            $column_file = $request->upload_files['column_file'];
            if (!empty($column_file)) {

                // Uploads テーブルに登録
                $uploads_id = DB::table('Uploads')->insertGetId([
                      'client_original_name' => $request->upload_files['column_file']['client_original_name'],
                      // Storage ファサードで拡張子が取れなかったので、File を使用
                      'extension' => File::extension($request->upload_files['column_file']['path']),
                      'mimetype'  => $request->upload_files['column_file']['mimetype'],
                      'size'      => Storage::size($request->upload_files['column_file']['path']),
                ]);
                $sampleforms->column_file = $uploads_id;

                // ファイルの移動
                Storage::move($request->upload_files['column_file']['path'], 'uploads/' . $uploads_id . '.' . File::extension($request->upload_files['column_file']['path']));
            }
        }

        // データ保存
        $sampleforms->save();
    }

    /**
     *  更新処理
     */
    public function update($request, $page_id, $frame_id, $id)
    {
        // データ取得
        $sampleforms = Sampleforms::where('id', $id)->first();

        // 旧ファイル情報
        $old_file_id = null;

        // アップロードファイルの取得
        if (!empty($request->upload_files)) {
            $column_file = $request->upload_files['column_file'];
            if (!empty($column_file)) {

                // 先のファイルがあれば、後で削除するためにidを保持しておく。
                if (!empty($sampleforms->column_file)) {
                    $old_file_id = $sampleforms->column_file;
                }

                // Uploads テーブルに登録
                $uploads_id = DB::table('Uploads')->insertGetId([
                      'client_original_name' => $request->upload_files['column_file']['client_original_name'],
                      // Storage ファサードで拡張子が取れなかったので、File を使用
                      'extension' => File::extension($request->upload_files['column_file']['path']),
                      'mimetype'  => $request->upload_files['column_file']['mimetype'],
                      'size'      => Storage::size($request->upload_files['column_file']['path']),
                ]);
                $sampleforms->column_file = $uploads_id;

                // ファイルの移動
                Storage::move($request->upload_files['column_file']['path'], 'uploads/' . $uploads_id . '.' . File::extension($request->upload_files['column_file']['path']));
            }
        }

        // 各データを詰める
        $sampleforms->column_text     = $request->column_text;
        $sampleforms->column_radio    = $request->column_radio;
        $sampleforms->column_hidden   = $request->column_hidden;
        $sampleforms->column_textarea = $request->column_textarea;
        $sampleforms->column_select   = $request->column_select;
        $sampleforms->column_checkbox = (empty($request->column_checkbox) ? null : "|".implode("|", $request->column_checkbox)."|" );
        $sampleforms->save();

        // パスワードは入力があった場合のみ、更新する。
        if (!empty($request->column_password)) {
            $sampleforms->column_password = $request->column_password;
        }

        // 先のファイルがあれば、削除
        if (!empty($old_file_id)) {

            // Uploads データ
            $upload = Uploads::where('id', $old_file_id)->first();
            if ($upload) {
                // 実ファイル(存在確認してなければスルー)
                $file_exists = Storage::exists('uploads/' . $old_file_id . '.' . $upload->extension);
                if ($file_exists) {
                    Storage::delete('uploads/' . $old_file_id . '.' . $upload->extension);
                }
            }

            // データベースから削除
            Uploads::destroy($old_file_id);
        }
    }
}
