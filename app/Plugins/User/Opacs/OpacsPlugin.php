<?php

namespace App\Plugins\User\Opacs;

use SimpleXMLElement;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use DB;

use App\Buckets;
use App\Opacs;
use App\OpacsBooks;
use App\OpacsBooksLents;

use App\Frame;
use App\Page;

use App\Plugins\User\UserPluginBase;

/**
 * Opacプラグイン
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category Opacプラグイン
 * @package Contoroller
 */
class OpacsPlugin extends UserPluginBase
{

   /**
    * lent_flag        = 9:貸出終了(貸し出し可能)、1:貸し出し中、2:貸し出しリクエスト受付中
    * scheduled_return = 返却予定日(日付)
    * lent_at          = 貸し出し日時(日時)
    */

    /**
     *  書誌データ取得関数
     *
     * @return view
     */
    public function index($request, $page_id, $frame_id)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // ブログ＆フレームデータ
        $opac_frame = $this->getOpacFrame($frame_id);
        if (empty($opac_frame)) {
            return;
        }

        // Page データ
        $page = Page::where('id', $page_id)->first();

        // 検索キーワード
        $keyword = $request->session()->get('search_keyword');

        // データ取得（1ページの表示件数指定）
        if (empty($keyword)) {
            $opacs_books = DB::table('opacs_books')
                          ->select('opacs_books.*', 'opacs_books_lents.lent_flag', 'opacs_books_lents.student_no', 'opacs_books_lents.return_scheduled', 'opacs_books_lents.lent_at')
                          ->leftJoin('opacs_books_lents', function ($join) {
                              $join->on('opacs_books_lents.opacs_books_id', '=', 'opacs_books.id')
                                  ->wherein('opacs_books_lents.lent_flag', [1, 2]);
                          })
                          ->where('opacs_id', $opac_frame->opacs_id)
                          ->orderBy('accept_date', 'desc')
                          ->paginate($opac_frame->view_count);
        }
        else {
            $opacs_books = DB::table('opacs_books')
                          ->select('opacs_books.*', 'opacs_books_lents.lent_flag', 'opacs_books_lents.student_no', 'opacs_books_lents.return_scheduled', 'opacs_books_lents.lent_at')
                          ->leftJoin('opacs_books_lents', function ($join) {
                              $join->on('opacs_books_lents.opacs_books_id', '=', 'opacs_books.id')
                                  ->wherein('opacs_books_lents.lent_flag', [1, 2]);
                          })
                          ->where('opacs_id', $opac_frame->opacs_id)
                          ->where(function($query) use ($keyword) {
                              $query->Where('isbn',      'like', '%' . $keyword . '%')
                                  ->orWhere('title',     'like', '%' . $keyword . '%')
                                  ->orWhere('ndc',       'like', '%' . $keyword . '%')
                                  ->orWhere('creator',   'like', '%' . $keyword . '%')
                                  ->orWhere('publisher', 'like', '%' . $keyword . '%')
                                  ->orWhere('barcode',   'like', '%' . $keyword . '%');
                          })
                          ->orderBy('accept_date', 'desc')
                          ->paginate($opac_frame->view_count);
        }
/*
                      ->where([
['opacs_id', $opac_frame->opacs_id],
['title', 'like', '%高校%'],
])
*/

        // 表示テンプレートを呼び出す。
        return $this->view(
            'opacs', [
            'opac_frame'  => $opac_frame,
            'opacs_books' => $opacs_books,
        ]);
    }

    /**
     *  編集画面の最初のタブ
     *
     *  スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        return "editBuckets";
    }

    /**
     *  紐づくOPAC ID とフレームデータの取得
     */
    public function getOpacFrame($frame_id)
    {
        // Frame データ
        $frame = DB::table('frames')
                 ->select('frames.*', 'opacs.id as opacs_id', 'opacs.opac_name', 'opacs.view_count')
                 ->leftJoin('opacs', 'opacs.bucket_id', '=', 'frames.bucket_id')
                 ->where('frames.id', $frame_id)
                 ->first();
        return $frame;
    }

    /**
     * OPAC設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $opacs_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // OPAC＆フレームデータ
        $opac_frame = $this->getOpacFrame($frame_id);

        // OPACデータ
        $opac = new Opacs();

        // opacs_id が渡ってくればopacs_id が対象
        if (!empty($opacs_id)) {
            $opac = Opacs::where('id', $opacs_id)->first();
        }
        // Frame のbucket_id があれば、bucket_id からOPACデータ取得、なければ、新規作成か選択へ誘導
        else if (!empty($opac_frame->bucket_id) && $create_flag == false) {
            $opac = Opacs::where('bucket_id', $opac_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view(
            'opacs_edit_opac', [
            'opac_frame'  => $opac_frame,
            'opac'        => $opac,
            'create_flag' => $create_flag,
            'message'     => $message,
            'errors'      => $errors,
        ])->withInput($request->all);
    }

    /**
     * OPAC新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $opacs_id = null, $create_flag = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けてOPAC設定変更画面を呼ぶ
        $create_flag = true;
        return $this->editBuckets($request, $page_id, $frame_id, $opacs_id, $create_flag, $message, $errors);
    }

    /**
     *  OPAC登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $opacs_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'opac_name'  => ['required'],
            'view_count' => ['required'],
        ]);
        $validator->setAttributeNames([
            'opac_name'  => 'OPAC名',
            'view_count' => '表示件数',
        ]);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {

            if (empty($opacs_id)) {
                $create_flag = true;
                return $this->createBuckets($request, $page_id, $frame_id, $opacs_id, $create_flag, $message, $validator->errors());
            }
            else  {
                $create_flag = false;
                return $this->editBuckets($request, $page_id, $frame_id, $opacs_id, $create_flag, $message, $validator->errors());
            }
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるopacs_id が空ならバケツとOPACを新規登録
        if (empty($request->opacs_id)) {

            // バケツの登録
            $bucket_id = DB::table('buckets')->insertGetId([
                  'bucket_name' => '無題',
                  'plugin_name' => 'opacs'
            ]);

            // OPACデータ新規オブジェクト
            $opacs = new Opacs();
            $opacs->bucket_id = $bucket_id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆OPAC作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆OPAC更新
            // （表示OPAC選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {

                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket_id]);
            }

            $message = 'OPAC設定を追加しました。';
        }
        // opacs_id があれば、OPACを更新
        else {

            // OPACデータ取得
            $opacs = Opacs::where('id', $request->opacs_id)->first();

            $message = 'OPAC設定を変更しました。';
        }

        // OPAC設定
        $opacs->opac_name  = $request->opac_name;
        $opacs->view_count = $request->view_count;

        // データ保存
        $opacs->save();

        // 新規作成フラグを付けてブログ設定変更画面を呼ぶ
        $create_flag = false;
        return $this->editBuckets($request, $page_id, $frame_id, $opacs_id, $create_flag, $message);
    }

    /**
     *  削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $opacs_id)
    {
        // opacs_id がある場合、データを削除
        if ( $opacs_id ) {

            // 書誌データを削除する。
            OpacsBooks::where('opacs_id', $opacs_id)->delete();

            // OPAC設定を削除する。
            Opacs::destroy($opacs_id);

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
     * データ選択表示関数
     */
    public function listBuckets($request, $page_id, $frame_id, $id = null)
    {
        // Frame データ
        $opac_frame = DB::table('frames')
                      ->select('frames.*', 'opacs.id as opacs_id', 'opacs.view_count')
                      ->leftJoin('opacs', 'opacs.bucket_id', '=', 'frames.bucket_id')
                      ->where('frames.id', $frame_id)->first();

        // データ取得（1ページの表示件数指定）
        $opacs = Opacs::orderBy('created_at', 'desc')
                       ->paginate(10);

        // 表示テンプレートを呼び出す。
        return $this->view(
            'opacs_list_buckets', [
            'opac_frame' => $opac_frame,
            'opacs'      => $opacs,
        ]);
    }

   /**
    * データ紐づけ変更関数
    */
    public function changeBuckets($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
               ->update(['bucket_id' => $request->select_bucket]);

        // 表示ブログ選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }

    /**
     *  書誌データ取得
     */
    public function getBook($request, $opacs_books)
    {
        if (empty($request->isbn)) {
            return;
        }

        // 国会図書館API
        $request_url = 'http://iss.ndl.go.jp/api/opensearch?isbn=' . $request->isbn;

        // $context = stream_context_create(array(
        //     'http' => array('ignore_errors' => true, 'timeout' => 10)
        // ));

        // NDL OpenSearch 呼び出しと結果のXML 取得
        $xml = null;
        try {
//              $xml_string = file_get_contents($request_url, false, $context);
//              $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOERROR|LIBXML_ERR_NONE|LIBXML_ERR_FATAL);
//            $xml = simplexml_load_file($request_url);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $xml_string = curl_exec($ch);
            $xml = simplexml_load_string($xml_string, 'SimpleXMLElement', LIBXML_NOERROR|LIBXML_ERR_NONE|LIBXML_ERR_FATAL);
            //var_dump($xml);

        } catch (Exception $e) {
            // Log::debug($e);
            return array($opacs_books, "書誌データ取得でエラーが発生しました。");
        }



        // http://iss.ndl.go.jp/api/opensearch?isbn=9784063655407
        // echo $xml->channel->item[1]->children('dc', true)->publisher;
        // echo $xml->channel->item->count();
        // var_dump($xml->channel->item[1]);
        // print_r($xml);

        // 結果が取得できた場合
        //var_dump($xml);
        $totalResults = $xml->channel->children('openSearch', true)->totalResults;
        if ($totalResults == 0) {
            return array($opacs_books, "書誌データが見つかりませんでした。");
        }
        if (!$xml) {
            return array($opacs_books, "取得した書誌データでエラーが発生しました。");
        }
        else {
            $target_item = null;
            $channel = get_object_vars($xml->channel);

            if (is_array($channel["item"])) {
                $target_item = end($channel["item"]);
            }
            else {
                $target_item = $channel["item"];
            }

            $opacs_books->title   = $target_item->title;
            $opacs_books->creator = $target_item->author;
            $opacs_books->publisher = $target_item->children('dc', true)->publisher;
        }

        return array($opacs_books, "");
    }

    /**
     *  新規書誌データ画面
     */
    public function create($request, $page_id, $frame_id, $opacs_books_id = null, $errors = null)
    {
        // 権限チェック
        // 特別処理。role_article（記事修正）でチェック。
        if ($this->can('role_article')) {
            return $this->view_error(403);
        }

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // OPAC＆フレームデータ
        $opac_frame = $this->getOpacFrame($frame_id);

        // 空のデータ(画面で初期値設定で使用するため)
        $opacs_books = new OpacsBooks();

        // 書誌データ取得の場合
        $search_error_message = '';
        if ($request->book_search == '1') {
            list($tmp_opacs_books, $search_error_message) = $this->getBook($request, $opacs_books);
            if (empty($tmp_opacs_books)) {
                $search_error_message = '書誌データが検索できませんでした。';
            }
            else {
                $opacs_books = $tmp_opacs_books;
            }
            //echo $opacs_books->title;
        }

        // 表示テンプレートを呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'opacs_input', [
            'opac_frame'  => $opac_frame,
            'opacs_books' => $opacs_books,
            'book_search' => $request->book_search,
            'errors'      => $errors,
            'search_error_message' => $search_error_message,
        ])->withInput($request->all);
    }

    /**
     * 書誌データ編集画面
     */
    public function edit($request, $page_id, $frame_id, $opacs_books_id = null, $errors = null)
    {
        // 権限チェック
        // 特別処理。role_article（記事修正）でチェック。
        if ($this->can('role_article')) {
            return $this->view_error(403);
        }

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Frame データ
        $opac_frame = $this->getOpacFrame($frame_id);

        // 書誌データ取得
        $opacs_book = OpacsBooks::where('id', $opacs_books_id)->first();

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'opacs_input', [
            'opac_frame'  => $opac_frame,
            'opacs_books' => $opacs_book,
            'errors'      => $errors,
        ])->withInput($request->all);
    }

    /**
     * 書誌データ詳細画面
     */
    public function show($request, $page_id, $frame_id, $opacs_books_id, $message = null, $message_class = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Frame データ
        $opac_frame = $this->getOpacFrame($frame_id);

        // 書誌データ取得
        $opacs_book = DB::table('opacs_books')
                      ->select('opacs_books.*', 'opacs_books_lents.lent_flag', 'opacs_books_lents.student_no', 'opacs_books_lents.return_scheduled', 'opacs_books_lents.lent_at')
                      ->leftJoin('opacs_books_lents', function ($join) {
                          $join->on('opacs_books_lents.opacs_books_id', '=', 'opacs_books.id')
                              ->wherein('opacs_books_lents.lent_flag', [1, 2]);
                      })
                      ->where('opacs_books.id', $opacs_books_id)->first();

        // 変更画面を呼び出す。(blade でold を使用するため、withInput 使用)
        return $this->view(
            'opacs_show', [
            'opac_frame'     => $opac_frame,
            'opacs_books'    => $opacs_book,
            'opacs_books_id' => $opacs_books_id,
            'message'        => $message,
            'message_class'  => $message_class,
            'errors'         => $errors,
        ]);
    }

    /**
     *  書誌データ登録処理
     */
    public function save($request, $page_id, $frame_id, $opacs_books_id = null)
    {
        // 権限チェック
        // 特別処理。role_article（記事修正）でチェック。
        if ($this->can('role_article')) {
            return $this->view_error(403);
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'title' => ['required'],
        ]);
        $validator->setAttributeNames([
            'title' => 'タイトル',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return ( $this->create($request, $page_id, $frame_id, $opacs_books_id, $validator->errors()) );
        }

        // 書誌データ取得の場合、入力画面に戻る
        if ($request->book_search == '1') {
            return ( $this->create($request, $page_id, $frame_id, $opacs_books_id, $validator->errors()) );
        }

        // id があれば更新、なければ登録
        if (empty($opacs_books_id)) {
            $opacs_book = new OpacsBooks();
        }
        else {
            $opacs_book = OpacsBooks::where('id', $opacs_books_id)->first();
        }

        // 書誌データ設定
        $opacs_book->opacs_id          = $request->opacs_id;
        $opacs_book->isbn              = $request->isbn;
        $opacs_book->title             = $request->title;
        $opacs_book->ndc               = $request->ndc;
        $opacs_book->creator           = $request->creator;
        $opacs_book->publisher         = $request->publisher;
        $opacs_book->barcode           = $request->barcode;
        $opacs_book->title_read        = $request->title_read;
        $opacs_book->subtitle          = $request->subtitle;
        $opacs_book->series            = $request->series;
        $opacs_book->publication_year  = $request->publication_year;
        $opacs_book->class             = $request->class;
        $opacs_book->size              = $request->size;
        $opacs_book->page_number       = $request->page_number;
        $opacs_book->marc              = $request->marc;
        $opacs_book->type              = $request->type;
        $opacs_book->shelf             = $request->shelf;
        $opacs_book->lend_flag         = $request->lend_flag;
        $opacs_book->accept_flag       = $request->accept_flag;
        $opacs_book->accept_date       = date('Y-m-d', strtotime($request->accept_date));
        $opacs_book->accept_price      = $request->accept_price;
        $opacs_book->storage_life      = date('Y-m-d', strtotime($request->storage_life));
        $opacs_book->remove_flag       = $request->remove_flag;
        $opacs_book->remove_date       = date('Y-m-d', strtotime($request->remove_date));
        $opacs_book->possession        = $request->possession;
        $opacs_book->library           = $request->library;
        $opacs_book->last_lending_date = date('Y-m-d', strtotime($request->last_lending_date));
        $opacs_book->total_lends       = $request->total_lends;

        // データ保存
        $opacs_book->save();

        // 登録後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  削除処理
     */
    public function destroy($request, $page_id, $frame_id, $opacs_books_id)
    {
        // 権限チェック
        // 特別処理。role_article（記事修正）でチェック。
        if ($this->can('role_article')) {
            return $this->view_error(403);
        }

        // id がある場合、データを削除
        if ( $opacs_books_id ) {

            // データを削除する。
            OpacsBooks::destroy($opacs_books_id);
        }
        // 削除後は表示用の初期処理を呼ぶ。
        return $this->index($request, $page_id, $frame_id);
    }

    /**
     *  貸し出しチェック
     */
    public function lentCheck($opacs_books_id)
    {
        // 貸出中でないかのチェック
        $books_lents = OpacsBooksLents::where('opacs_books_id', $opacs_books_id)->whereIn('lent_flag', [1, 2])->get();
        if (count($books_lents) == 0) {
            return true;
        }
        return false;
    }

    /**
     *  貸し出し登録
     */
    public function lent($request, $page_id, $frame_id, $opacs_books_id)
    {
        // 権限チェック
        // 特別処理。role_article（記事修正）でチェック。
//        if ($this->can('role_article')) {
//            return $this->view_error(403);
//        }

        // 貸出中でないかのチェック
        if ( !$this->lentCheck($opacs_books_id) ) {
            return $this->show($request, $page_id, $frame_id, $opacs_books_id, 'この書籍は貸出中です。', 'danger');
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'student_no'       => ['required'],
            'return_scheduled' => ['required'],
        ]);
        $validator->setAttributeNames([
            'student_no'       => '学籍番号',
            'return_scheduled' => '返却予定日',
        ]);

        // エラーがあった場合は詳細画面に戻る。
        $message = null;
        if ($validator->fails()) {
            return $this->show($request, $page_id, $frame_id, $opacs_books_id, $message, null, $validator->errors());
        }

        // 書籍貸し出しデータ新規オブジェクト
        $opacs_books_lents                   = new OpacsBooksLents();
        $opacs_books_lents->opacs_books_id   = $opacs_books_id;
        $opacs_books_lents->lent_flag        = 1;
        $opacs_books_lents->student_no       = $request->student_no;
        $opacs_books_lents->return_scheduled = date('Y-m-d 00:00:00', strtotime($request->return_scheduled));

        // データ保存
        $opacs_books_lents->save();

        $message = '貸し出し登録しました。';

        // 郵送貸し出しリクエスト処理後は詳細表示処理を呼ぶ。(更新成功時もエラー時も同じ)
        return $this->show($request, $page_id, $frame_id, $opacs_books_id, $message, null, $validator->errors());
    }

    /**
     *  郵送貸し出しリクエスト
     */
    public function requestLent($request, $page_id, $frame_id, $opacs_books_id)
    {
        // 貸出中でないかのチェック
        if ( !$this->lentCheck($opacs_books_id) ) {
            return $this->show($request, $page_id, $frame_id, $opacs_books_id, 'この書籍は貸出中です。', 'danger');
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'req_student_no'       => ['required'],
            'req_return_scheduled' => ['required'],
        ]);
        $validator->setAttributeNames([
            'req_student_no'       => '学籍番号',
            'req_return_scheduled' => '返却予定日',
        ]);

        // エラーがあった場合は詳細画面に戻る。
        $message = null;
        if ($validator->fails()) {
            return $this->show($request, $page_id, $frame_id, $opacs_books_id, $message, null, $validator->errors());
        }

        // 書籍貸し出しデータ新規オブジェクト
        $opacs_books_lents                   = new OpacsBooksLents();
        $opacs_books_lents->opacs_books_id   = $opacs_books_id;
        $opacs_books_lents->lent_flag        = 2;
        $opacs_books_lents->student_no       = $request->req_student_no;
        $opacs_books_lents->return_scheduled = date('Y-m-d 00:00:00', strtotime($request->req_return_scheduled));
        $opacs_books_lents->phone_no         = $request->req_phone_no;
        $opacs_books_lents->email            = $request->req_email;

        // データ保存
        $opacs_books_lents->save();

        $message = '郵送貸し出しリクエストを受け付けました。';

        // 郵送貸し出しリクエスト処理後は詳細表示処理を呼ぶ。(更新成功時もエラー時も同じ)
        return $this->show($request, $page_id, $frame_id, $opacs_books_id, $message, null, $validator->errors());
    }

    /**
     *  貸し出し返却
     */
    public function returnLent($request, $page_id, $frame_id, $opacs_books_id)
    {

        // 貸出中でないかのチェック
        if ( $this->lentCheck($opacs_books_id) ) {
            return $this->show($request, $page_id, $frame_id, $opacs_books_id, 'この書籍は貸出中ではありません。', 'danger');
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'return_student_no'  => ['required'],
            'return_date'        => ['required'],
        ]);
        $validator->setAttributeNames([
            'return_student_no'  => '学籍番号',
            'return_date'        => '返却日',
        ]);

        // エラーがあった場合は詳細画面に戻る。
        $message = null;
        if ($validator->fails()) {
            return $this->show($request, $page_id, $frame_id, $opacs_books_id, $message, null, $validator->errors());
        }

        // 学籍番号チェック
        $books_lents = OpacsBooksLents::where('opacs_books_id', $opacs_books_id)->whereIn('lent_flag', [1, 2])->first();
        if ($books_lents->student_no != $request->return_student_no) {
            $message = '学籍番号が一致しません。';
            $message_class = 'danger';
            return $this->show($request, $page_id, $frame_id, $opacs_books_id, $message, $message_class, $validator->errors());
        }

        // 書籍貸し出しデータ
        $books_lents = OpacsBooksLents::where('opacs_books_id', $opacs_books_id)->whereIn('lent_flag', [1, 2])->first();

        $books_lents->lent_flag   = 9;
        $books_lents->student_no  = null;
        $books_lents->return_date = date('Y-m-d 00:00:00', strtotime($request->return_date));
        $books_lents->phone_no    = null;
        $books_lents->email       = null;

        // データ保存
        $books_lents->save();

        $message = '返却しました。';

        // 郵送貸し出しリクエスト処理後は詳細表示処理を呼ぶ。(更新成功時もエラー時も同じ)
        return $this->show($request, $page_id, $frame_id, $opacs_books_id, $message, null, $validator->errors());
    }

    /**
     *  検索
     */
    public function search($request, $page_id, $frame_id)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // キーワードをセッションに保存しておく。
        $request->session()->put('search_keyword', $request->keyword);

        return $this->index($request, $page_id, $frame_id);
    }
}
