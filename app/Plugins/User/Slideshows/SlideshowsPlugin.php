<?php

namespace App\Plugins\User\Slideshows;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Uploads;
use App\Models\User\Slideshows\Slideshows;
use App\Models\User\Slideshows\SlideshowsItems;

use App\Plugins\User\UserPluginBase;

/**
 * スライドショー・プラグイン
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スライドショー・プラグイン
 * @package Contoroller
 */
class SlideshowsPlugin extends UserPluginBase
{
    /**
     *  関数定義（コアから呼び出す）
     */
    public function getPublicFunctions()
    {
        // 標準関数以外で画面などから呼ばれる関数の定義
        $functions = array();
        $functions['get']  = [
            'index',
        ];
        $functions['post'] = [
            'index',
            'updateItem',
            'updateItemSequence',
        ];
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
        $role_ckeck_table = [];

        $role_ckeck_table["updateItem"]         = ['buckets.editItem'];
        $role_ckeck_table["updateItemSequence"] = ['buckets.editItem'];
        return $role_ckeck_table;
    }

    /**
     *  編集画面の最初のタブ ※スーパークラスをオーバーライド
     */
    public function getFirstFrameEditAction()
    {
        // スライドショー未作成の場合、スライドショーの新規作成に遷移
        $slideshow = $this->getSlideshows($this->frame->id);
        return $slideshow ? 'editBuckets' : 'createBuckets';
    }

    /* private関数 */

    /**
     *  フレームIDに紐づくプラグインデータ取得
     */
    private function getSlideshows($frame_id)
    {
        $slideshow = Slideshows::query()
            ->select('slideshows.*')
            ->join('frames', 'frames.bucket_id', '=', 'slideshows.bucket_id')
            ->where('frames.id', '=', $frame_id)
            ->first();

        return $slideshow;
    }

    /**
     *  フレームに紐づくスライドショーID とフレームデータの取得
     */
    private function getSlideshowFrame($frame_id)
    {
        // Frame データ
        $frame = Frame::query()
            ->select(
                'frames.*', 
                'slideshows.id as slideshows_id'
            )
            ->leftJoin('slideshows', 'slideshows.bucket_id', '=', 'frames.bucket_id')
            ->where('frames.id', $frame_id)
            ->first();
        return $frame;
    }

    /* 画面アクション関数 */

    /**
     *  データ初期表示関数
     *  コアがページ表示の際に呼び出す関数
     */
    public function index($request, $page_id, $frame_id, $errors = null)
    {

        // セッション初期化などのLaravel 処理。
        $request->flash();

        // Slideshows、Frame データ
        $slideshow = $this->getSlideshows($frame_id);

        $setting_error_messages = null;
        $slideshows_items = null;
        if ($slideshow) {

        } else {
            // フレームに紐づくスライドショー親データがない場合
            $setting_error_messages[] = 'フレームの設定画面から、使用するスライドショーを選択するか、作成してください。';
        }

        if (empty($setting_error_messages)) {
            // 表示テンプレートを呼び出す。
            return $this->view('slideshows', [
                'request' => $request,
                'frame_id' => $frame_id,
                'slideshow' => $slideshow,
                'slideshows_items' => $slideshows_items,
                'errors' => $errors,
            ]);
        } else {
            // エラーあり
            return $this->view('slideshows_error_messages', [
                'error_messages' => $setting_error_messages,
            ]);
        }
    }

    /**
     * スライドショー新規作成画面
     */
    public function createBuckets($request, $page_id, $frame_id, $slideshows_id = null, $is_create = false, $message = null, $errors = null)
    {
        // 新規作成フラグを付けてスライドショー設定変更画面を呼ぶ
        $is_create = true;
        return $this->editBuckets($request, $page_id, $frame_id, $slideshows_id, $is_create, $message, $errors);
    }

    /**
     * スライドショー設定変更画面の表示
     */
    public function editBuckets($request, $page_id, $frame_id, $slideshows_id = null, $is_create = false, $message = null, $errors = null)
    {
        // セッション初期化などのLaravel 処理。
        $request->flash();

        // スライドショー＆フレームデータ
        $slideshow_frame = $this->getSlideshowFrame($frame_id);

        // スライドショーデータ
        $slideshow = new Slideshows();

        if (!empty($slideshows_id)) {
            // slideshows_id が渡ってくればslideshows_id が対象
            $slideshow = Slideshows::where('id', $slideshows_id)->first();
        } elseif (!empty($slideshow_frame->bucket_id) && $is_create == false) {
            // Frame のbucket_id があれば、bucket_id からスライドショーデータ取得、なければ、新規作成か選択へ誘導
            $slideshow = Slideshows::where('bucket_id', $slideshow_frame->bucket_id)->first();
        }

        // 表示テンプレートを呼び出す。
        return $this->view('slideshows_edit_slideshow',
            [
                'slideshow_frame' => $slideshow_frame,
                'slideshow' => $slideshow,
                'is_create' => $is_create,
                'message' => $message,
                'errors' => $errors,
            ]
        )->withInput($request->all);
    }

    /**
     *  スライドショー登録処理
     */
    public function saveBuckets($request, $page_id, $frame_id, $slideshows_id = null)
    {
        // デフォルトで必須
        $validator_values['slideshows_name'] = ['required'];
        $validator_attributes['slideshows_name'] = 'スライドショー名';

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $validator_values);
        $validator->setAttributeNames($validator_attributes);

        // エラーがあった場合は入力画面に戻る。
        $message = null;
        if ($validator->fails()) {
            $is_create = $slideshows_id ? false : true;
            return $this->editBuckets($request, $page_id, $frame_id, $slideshows_id, $is_create, $message, $validator->errors());
        }

        // 更新後のメッセージ
        $message = null;

        // 画面から渡ってくるslideshows_id が空ならバケツとスライドショーを新規登録
        if (empty($slideshows_id)) {
            /**
             * 新規登録用の処理
             */
            $bucket = new Buckets();
            $bucket->bucket_name = '無題';
            $bucket->plugin_name = 'slideshows';
            $bucket->save();

            // スライドショーデータ新規オブジェクト
            $slideshows = new Slideshows();
            $slideshows->bucket_id = $bucket->id;

            // Frame のBuckets を見て、Buckets が設定されていなければ、作成したものに紐づける。
            // Frame にBuckets が設定されていない ＞ 新規のフレーム＆スライドショー作成
            // Frame にBuckets が設定されている ＞ 既存のフレーム＆スライドショー更新
            // （表示スライドショー選択から遷移してきて、内容だけ更新して、フレームに紐づけないケースもあるため）
            $frame = Frame::where('id', $frame_id)->first();
            if (empty($frame->bucket_id)) {
                // FrameのバケツIDの更新
                $frame = Frame::where('id', $frame_id)->update(['bucket_id' => $bucket->id]);
            }

            $message = 'スライドショー設定を追加しました。<br />' .
                        '　 [ <a href="' . url('/') . '/plugin/slideshows/listBuckets/' . $page_id . '/' . $frame_id . '/#frame-' . $frame_id . '">スライドショー選択</a> ]から作成したスライドショーを選択後、［ 項目設定 ］で使用する項目を設定してください。';
        } else {
            /**
             * 更新用の処理
             */

            // スライドショーデータ取得
            $slideshows = Slideshows::where('id', $slideshows_id)->first();

            $message = 'スライドショー設定を変更しました。';
        }

        /**
         * 登録処理 ※新規、更新共通
         */
        $slideshows->slideshows_name = $request->slideshows_name;
        $slideshows->control_display_flag = $request->control_display_flag;
        $slideshows->indicators_display_flag = $request->indicators_display_flag;
        $slideshows->fade_use_flag = $request->fade_use_flag;
        $slideshows->save();

        // 新規作成フラグを更新モードにセットして設定変更画面へ遷移
        $is_create = false;

        // リダイレクト設定はフォーム側で設定している為、return処理は省略
    }

    /**
     * スライドショー削除処理
     */
    public function destroyBuckets($request, $page_id, $frame_id, $slideshows_id)
    {
        // slideshows_id がある場合、データを削除
        if ($slideshows_id) {
            $slideshows_items = SlideshowsItems::where('slideshows_id', $slideshows_id)->get();

            // ////
            // //// 添付ファイルの削除
            // ////
            // $file_item_type_ids = [];
            // foreach ($slideshows_items as $slideshows_item) {
            //     // ファイルタイプ
            //     if (SlideshowsItems::isFileItemType($slideshows_item->item_type)) {
            //         $file_item_type_ids[] = $slideshows_item->id;
            //     }
            // }

            // // 削除するファイル情報が入っている詳細データの特定
            // $del_file_ids = SlideshowsInputCols::whereIn('slideshows_items_id', $file_item_type_ids)
            //                                     ->whereNotNull('value')
            //                                     ->pluck('value')
            //                                     ->all();

            // 削除するファイルデータ (もし重複IDあったとしても、in検索によって排除される)
            // $delete_uploads = Uploads::whereIn('id', $del_file_ids)->get();
            // foreach ($delete_uploads as $delete_upload) {
            //     // ファイルの削除
            //     $directory = $this->getDirectory($delete_upload->id);
            //     Storage::delete($directory . '/' . $delete_upload->id . '.' .$delete_upload->extension);

            //     // uploadの削除
            //     $delete_upload->delete();
            // }


            foreach ($slideshows_items as $slideshows_item) {
                // 詳細データ値を削除する。
                SlideshowsItems::where('slideshows_items_id', $slideshows_item->id)->delete();
            }

            $slideshows = Slideshows::find($slideshows_id);

            // backetsの削除
            Buckets::where('id', $slideshows->bucket_id)->delete();

            // バケツIDの取得のためにFrame を取得(Frame を更新する前に取得しておく)
            $frame = Frame::where('id', $frame_id)->first();
            // フレームのbucket_idと削除するスライドショーのbucket_idが同じなら、FrameのバケツIDの更新する
            if ($frame->bucket_id == $slideshows->bucket_id) {
                // FrameのバケツIDの更新
                Frame::where('bucket_id', $frame->bucket_id)->update(['bucket_id' => null]);
            }

            // スライドショー設定を削除する。
            Slideshows::destroy($slideshows_id);
        }

        // リダイレクト設定はフォーム側で設定している為、return処理は省略
    }

    /**
     * データ紐づけ変更関数
     */
    public function changeBuckets($request, $page_id = null, $frame_id = null, $id = null)
    {
        // FrameのバケツIDの更新
        Frame::where('id', $frame_id)
               ->update(['bucket_id' => $request->select_bucket]);

        // 関連するセッションクリア
        $request->session()->forget('slideshows');

        // 表示スライドショー選択画面を呼ぶ
        return $this->listBuckets($request, $page_id, $frame_id, $id);
    }

    /**
     * 項目の追加
     */
    public function addItem($request, $page_id, $frame_id, $id = null)
    {
        // エラーチェック
        $validator = Validator::make($request->all(), [
            'item_name'  => ['required'],
            'item_type'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'item_name'  => '項目名',
            'item_type'  => '型',
        ]);

        $errors = null;
        if ($validator->fails()) {
            // エラーと共に編集画面を呼び出す
            $errors = $validator->errors();
            return $this->editItem($request, $page_id, $frame_id, $request->slideshows_id, null, $errors);
        }

        // 新規登録時の表示順を設定
        $max_display_sequence = SlideshowsItems::query()->where('slideshows_id', $request->slideshows_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 項目の登録処理
        $item = new SlideshowsItems();
        $item->slideshows_id = $request->slideshows_id;
        $item->item_name = $request->item_name;
        $item->item_type = $request->item_type;
        $item->required = $request->required ? \Required::on : \Required::off;
        $item->display_sequence = $max_display_sequence;
        $item->caption_color = \Bs4TextColor::dark;
        $item->save();
        $message = '項目【 '. $request->item_name .' 】を追加しました。';

        // 編集画面へ戻る。
        return $this->editItem($request, $page_id, $frame_id, $request->slideshows_id, $message, $errors);
    }

    /**
     * カラム編集画面の表示
     */
    public function editItem($request, $page_id, $frame_id, $id = null, $message = null, $errors = null)
    {
        if ($errors) {
            // エラーあり：入力値をフラッシュデータとしてセッションへ保存
            $request->flash();
        } else {
            // エラーなし：セッションから入力値を消去
            $request->flush();
        }

        // フレームに紐づくスライドショーID を探して取得
        $form_db = $this->getSlideshows($frame_id);

        // スライドショーのID。まだスライドショーがない場合は0
        $slideshows_id = 0;
        $use_temporary_regist_mail_flag = null;
        if (!empty($form_db)) {
            $slideshows_id = $form_db->id;
            $use_temporary_regist_mail_flag = $form_db->use_temporary_regist_mail_flag;
        }

        // 項目データ取得
        // 予約項目データ
        $items = SlideshowsItems::query()
            ->select(
                'slideshows_items.id',
                'slideshows_items.slideshows_id',
                'slideshows_items.item_type',
                'slideshows_items.item_name',
                'slideshows_items.required',
                'slideshows_items.frame_col',
                'slideshows_items.caption',
                'slideshows_items.caption_color',
                'slideshows_items.place_holder',
                'slideshows_items.display_sequence',
                DB::raw('count(slideshows_items_selects.id) as select_count'),
                DB::raw('GROUP_CONCAT(slideshows_items_selects.value order by slideshows_items_selects.display_sequence SEPARATOR \',\') as select_names')
            )
            ->where('slideshows_items.slideshows_id', $slideshows_id)
            // 予約項目の子データ（選択肢）
            ->leftjoin('slideshows_items_selects', function ($join) {
                $join->on('slideshows_items.id', '=', 'slideshows_items_selects.slideshows_items_id');
            })
            ->groupby(
                'slideshows_items.id',
                'slideshows_items.slideshows_id',
                'slideshows_items.item_type',
                'slideshows_items.item_name',
                'slideshows_items.required',
                'slideshows_items.frame_col',
                'slideshows_items.caption',
                'slideshows_items.caption_color',
                'slideshows_items.place_holder',
                'slideshows_items.display_sequence'
            )
            ->orderby('slideshows_items.display_sequence')
            ->get();

        // 仮登録設定時のワーニングメッセージ
        $warning_message = null;
        if ($use_temporary_regist_mail_flag) {
            $is_exist = false;
            foreach ($items as $item) {
                if ($item->required && $item->item_type == \FormItemType::mail) {
                    $is_exist = true;
                    break;
                }
            }
            if (! $is_exist) {
                $warning_message = "仮登録メールが設定されています。必須のメールアドレス型の項目を設定してください。";
            }
        }

        // 編集画面テンプレートを呼び出す。
        return $this->view(
            'slideshows_edit',
            [
                'slideshows_id'   => $slideshows_id,
                'items'    => $items,
                'message'    => $message,
                'warning_message' => $warning_message,
                'errors'     => $errors,
            ]
        );
    }

    /**
     * 項目の削除
     */
    public function deleteItem($request, $page_id, $frame_id)
    {
        // 明細行から削除対象の項目名を抽出
        $str_item_name = "item_name_"."$request->item_id";

        // 項目の削除
        SlideshowsItems::query()->where('id', $request->item_id)->delete();
        // 項目に紐づく選択肢の削除
        $this->deleteItemsSelects($request->item_id);
        $message = '項目【 '. $request->$str_item_name .' 】を削除しました。';

        // 編集画面へ戻る。
        return $this->editItem($request, $page_id, $frame_id, $request->slideshows_id, $message, null);
    }

    /**
     * 項目の更新
     */
    public function updateItem($request, $page_id, $frame_id)
    {
        // 明細行から更新対象を抽出する為のnameを取得
        $str_item_name = "item_name_".$request->item_id;
        $str_item_type = "item_type_".$request->item_id;
        $str_required = "required_".$request->item_id;

        $validate_value = [
            'item_name_'.$request->item_id => ['required'],
            'item_type_'.$request->item_id => ['required'],
        ];

        $validate_attribute = [
            'item_name_'.$request->item_id  => '項目名',
            'item_type_'.$request->item_id  => '型',
        ];

        // エラーチェック
        $validator = Validator::make($request->all(), $validate_value);
        $validator->setAttributeNames($validate_attribute);

        $errors = null;
        if ($validator->fails()) {
            // エラーと共に編集画面を呼び出す
            $errors = $validator->errors();
            return $this->editItem($request, $page_id, $frame_id, $request->slideshows_id, null, $errors);
        }

        // 項目の更新処理
        $item = SlideshowsItems::query()->where('id', $request->item_id)->first();
        $item->item_name = $request->$str_item_name;
        $item->item_type = $request->$str_item_type;
        $item->required = $request->$str_required ? \Required::on : \Required::off;
        $item->save();
        $message = '項目【 '. $request->$str_item_name .' 】を更新しました。';

        // 編集画面を呼び出す
        return $this->editItem($request, $page_id, $frame_id, $request->slideshows_id, $message, $errors);
    }

    /**
     * 項目の表示順の更新
     */
    public function updateItemSequence($request, $page_id, $frame_id)
    {
        // ボタンが押された行の施設データ
        $target_item = SlideshowsItems::query()
            ->where('slideshows_id', $request->slideshows_id)
            ->where('id', $request->item_id)
            ->first();

        // ボタンが押された前（後）の施設データ
        $query = SlideshowsItems::query()
            ->where('slideshows_id', $request->slideshows_id);
        $pair_item = $request->display_sequence_operation == 'up' ?
            $query->where('display_sequence', '<', $request->display_sequence)->orderby('display_sequence', 'desc')->limit(1)->first() :
            $query->where('display_sequence', '>', $request->display_sequence)->orderby('display_sequence', 'asc')->limit(1)->first();

        // それぞれの表示順を退避
        $target_item_display_sequence = $target_item->display_sequence;
        $pair_item_display_sequence = $pair_item->display_sequence;

        // 入れ替えて更新
        $target_item->display_sequence = $pair_item_display_sequence;
        $target_item->save();
        $pair_item->display_sequence = $target_item_display_sequence;
        $pair_item->save();

        $message = '項目【 '. $target_item->item_name .' 】の表示順を更新しました。';

        // 編集画面を呼び出す
        return $this->editItem($request, $page_id, $frame_id, $request->slideshows_id, $message, null);
    }
}