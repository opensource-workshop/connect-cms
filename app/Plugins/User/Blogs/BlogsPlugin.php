<?php

namespace App\Plugins\User\Blogs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
     *  編集画面の最初のタブ
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "editBlog";
    }

    /**
     *  紐づくブログID とフレームデータの取得
     */
    public function getBlogFrame($frame_id)
    {
        // Frame データ
        $frame = DB::table('frames')
                 ->select('frames.*', 'blogs.id as blogs_id', 'blogs.view_count')
                 ->leftJoin('blogs', 'blogs.bucket_id', '=', 'frames.bucket_id')
                 ->where('frames.id', $frame_id)
                 ->first();
        return $frame;
    }

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id)
    {
        // ブログ＆フレームデータ
        $blog_frame = $this->getBlogFrame($frame_id);
        if (empty($blog_frame)) {
            return;
        }

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // データ取得（1ページの表示件数指定）
        $blogs_posts = BlogsPosts::orderBy('created_at', 'desc')
                       ->where('blogs_id', $blog_frame->blogs_id)
                       ->paginate($blog_frame->view_count);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'blogs', [
            'blogs_posts' => $blogs_posts,
        ]);
    }

    /**
     * ブログ設定変更画面の表示
     */
    public function editBlog($request, $page_id, $frame_id, $blogs_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // ブログ＆フレームデータ
        $blog_frame = $this->getBlogFrame($frame_id);

        // ブログデータ
        $blog = new Blogs();

        // blogs_id が渡ってくればblogs_id が対象
        if (!empty($blogs_id)) {
            $blog = Blogs::where('id', $blogs_id)->first();
        }
        // Frame のbucket_id があれば、bucket_id からブログデータ取得、なければ、新規作成か選択へ誘導
        else if (!empty($blog_frame->bucket_id) && $create_flag == false) {
            $blog = Blogs::where('bucket_id', $blog_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'blogs_edit_blog', [
            'blog_frame'  => $blog_frame,
            'blog'        => $blog,
            'create_flag' => $create_flag,
            'message'     => $message,
            'errors'      => $errors,
        ])->withInput($request->all);
    }

    /**
     * ブログ新規作成画面
     */
    public function createBlog($request, $page_id, $frame_id, $blogs_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けてブログ設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBlog($request, $page_id, $frame_id, $blogs_id, $create_flag, $message, $errors);
    }

    /**
     *  ブログ登録処理
     */
    public function saveBlogs($request, $page_id, $frame_id, $blogs_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'blog_name'  => ['required'],
            'view_count' => ['required'],
        ]);
        $validator->setAttributeNames([
            'blog_name'  => 'ブログ名',
            'view_count' => '表示件数',
        ]);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {

            if (empty($blogs_id)) {
                $create_flag = true;
                return $this->createBlog($request, $page_id, $frame_id, $blogs_id, $create_flag, $message, $validator->errors());
            }
            else  {
                $create_flag = false;
                return $this->editBlog($request, $page_id, $frame_id, $blogs_id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

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
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆ブログ作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆ブログ更新
            // （表示ブログ選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {

                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);
            }

            $message = 'ブログ設定を追加しました。';
        }
        // blogs_id があれば、ブログを更新
        else {

            // ブログデータ取得
            $blogs = Blogs::where('id', $request->blogs_id)->first();

            $message = 'ブログ設定を変更しました。';
        }

        // ブログ設定
        $blogs->blog_name  = $request->blog_name;
        $blogs->view_count = $request->view_count;

        // データ保存
        $blogs->save();

        // 新規作成フラグを付けてブログ設定変更画面を呼ぶ
        $create_flag = false;
        return $this->editBlog($request, $page_id, $frame_id, $blogs_id, $create_flag, $message);
    }

    /**
     *  削除処理
     */
    public function blogsDestroy($request, $page_id, $frame_id, $blogs_id)
    {
        // blogs_id がある場合、データを削除
        if ( $blogs_id ) {

            // 記事データを削除する。
            BlogsPosts::where('blogs_id', $blogs_id)->delete();

            // ブログ設定を削除する。
            Blogs::destroy($blogs_id);

            // バケツIDの取得のためにFrame を取得(Frame を更新する前に取得しておく)
            $frame = Frame::where('id', $frame_id)->first();

            // FrameのバケツIDの更新
            Frame::where('id', $frame_id)->update(['bucket_id' => null]);

            // backetsの削除
            Buckets::where('id', $frame->bucket_id)->delete();
        }
        // 削除処理はredirect 付のルートで呼ばれて、処理後はページの再表示が行われるため、ここでは何もしない。
    }

    /**
     *  新規記事画面
     */
    public function create($request, $page_id, $frame_id, $blogs_posts_id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // ブログ＆フレームデータ
        $blog_frame = $this->getBlogFrame($frame_id);

        // 空のデータ(画面で初期値設定で使用するため)
        $blogs_posts = new BlogsPosts();
        $blogs_posts->posted_at = date('Y-m-d H:i:s');

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'blogs_input', [
            'blog_frame'  => $blog_frame,
            'blogs_posts' => $blogs_posts,
            'errors'      => $errors,
        ])->withInput($request->all);
    }

    /**
     * 記事編集画面
     */
    public function edit($request, $page_id, $frame_id, $blogs_posts_id = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Frame データ
        $blog_frame = $this->getBlogFrame($frame_id);

        // 記事取得
        $blogs_post = BlogsPosts::where('id', $blogs_posts_id)->first();

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'blogs_input', [
            'blog_frame'  => $blog_frame,
            'blogs_posts' => $blogs_post,
            'errors'      => $errors,
        ])->withInput($request->all);
    }

    /**
     *  ブログ記事登録処理
     */
    public function save($request, $page_id, $frame_id, $blogs_posts_id = null)
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
            return ( $this->create($request, $page_id, $frame_id, $blogs_posts_id, $validator->errors()) );
        }

        // id があれば更新、なければ登録
        if (empty($blogs_posts_id)) {
            $blogs_post = new BlogsPosts();
        }
        else {
            $blogs_post = BlogsPosts::where('id', $blogs_posts_id)->first();
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
    public function destroy($request, $page_id, $frame_id, $blogs_posts_id)
    {
        // id がある場合、データを削除
        if ( $blogs_posts_id ) {

            // データを削除する。
            BlogsPosts::destroy($blogs_posts_id);
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
        $blog_frame = DB::table('frames')
                      ->select('frames.*', 'blogs.id as blogs_id', 'blogs.view_count')
                      ->leftJoin('blogs', 'blogs.bucket_id', '=', 'frames.bucket_id')
                      ->where('frames.id', $frame_id)->first();

        // データ取得（1ページの表示件数指定）
        $blogs = Blogs::orderBy('created_at', 'desc')
                       ->paginate(10);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'blogs_edit_datalist', [
            'blog_frame' => $blog_frame,
            'blogs'      => $blogs,
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

        // 表示ブログ選択画面を呼ぶ
        return $this->datalist($request, $page_id, $frame_id, $id);
    }
}
