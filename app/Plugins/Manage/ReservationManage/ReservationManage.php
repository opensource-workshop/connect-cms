<?php

namespace App\Plugins\Manage\ReservationManage;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\User\Reservations\ReservationsFacility;
use App\Models\User\Reservations\ReservationsCategory;
use App\Models\User\Reservations\ReservationsColumn;
use App\Models\User\Reservations\ReservationsColumnsSet;

use App\Plugins\Manage\ManagePluginBase;

/**
 * 施設管理
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 施設管理
 * @package Controller
 */
class ReservationManage extends ManagePluginBase
{
    /**
     * 権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_check_table = array();
        // 施設一覧
        $role_check_table["index"]              = array('admin_site');
        $role_check_table["regist"]             = array('admin_site');
        $role_check_table["store"]              = array('admin_site');
        $role_check_table["edit"]               = array('admin_site');
        $role_check_table["update"]             = array('admin_site');
        $role_check_table["destroy"]            = array('admin_site');
        $role_check_table["copy"]               = array('admin_site');

        // 施設カテゴリ設定
        $role_check_table["categories"]         = array('admin_site');
        $role_check_table["saveCategories"]     = array('admin_site');
        $role_check_table["deleteCategories"]   = array('admin_site');

        // 項目セット
        $role_check_table["columnSets"]         = array('admin_site');
        $role_check_table["registColumnSet"]    = array('admin_site');
        $role_check_table["storeColumnSet"]     = array('admin_site');
        $role_check_table["editColumnSet"]      = array('admin_site');
        $role_check_table["updateColumnSet"]    = array('admin_site');
        $role_check_table["destroyColumnSet"]   = array('admin_site');

        // 項目設定
        $role_check_table["editColumns"]        = array('admin_site');

        return $role_check_table;
    }

    /**
     * 初期表示
     *
     * @return view
     */
    public function index($request, $id = null)
    {
        /* ページの処理（セッション）
        ----------------------------------------------*/

        // 表示ページ数。詳細で更新して戻ってきたら、元と同じページを表示したい。
        // セッションにあればページの指定があれば使用。
        // ただし、リクエストでページ指定があればそれが優先。(ページング操作)
        $page = 1;
        if ($request->session()->has('reservation_page_condition.page')) {
            $page = $request->session()->get('reservation_page_condition.page');
        }
        if ($request->filled('page')) {
            $page = $request->page;
        }

        // ページがリクエストで指定されている場合は、セッションの検索条件配列のページ番号を更新しておく。
        // 詳細画面や更新処理から戻ってきた時用
        if ($request->filled('page')) {
            session(["reservation_page_condition.page" => $request->page]);
        }

        /* データの取得
        ----------------------------------------------*/

        // 施設
        $facilities = ReservationsFacility::
            select(
                'reservations_facilities.*',
                'reservations_categories.category as category',
                'reservations_columns_sets.name as columns_set_name'
            )
            ->leftJoin('reservations_categories', function ($join) {
                $join->on('reservations_facilities.reservations_categories_id', '=', 'reservations_categories.id')
                    ->whereNull('reservations_categories.deleted_at');
            })
            ->leftJoin('reservations_columns_sets', function ($join) {
                $join->on('reservations_facilities.columns_set_id', '=', 'reservations_columns_sets.id')
                    ->whereNull('reservations_columns_sets.deleted_at');
            })
            ->orderBy('reservations_categories.display_sequence')
            ->orderBy('reservations_facilities.display_sequence')
            ->paginate(50, null, 'page', $page);

        return view('plugins.manage.reservation.index', [
            "function" => __FUNCTION__,
            "plugin_name" => "reservation",
            "facilities" => $facilities,
        ]);
    }

    /**
     * 施設登録画面表示
     *
     * @return view
     */
    public function regist($request, $id = null, $errors = array())
    {
        return $this->edit($request, $id, 'regist', $errors);
    }

    /**
     * 施設登録処理
     *
     * @return view
     */
    public function store($request)
    {
        return $this->update($request, null);
    }

    /**
     * 施設変更画面表示
     *
     * @return view
     */
    public function edit($request, $id = null, $function = null)
    {
        $facility = ReservationsFacility::firstOrNew(['id' => $id]);

        $function = $function ?? 'edit';

        // カテゴリデータの取得
        $categories = ReservationsCategory::orderBy('display_sequence', 'asc')->get();

        // 項目セットの取得
        $columns_sets = ReservationsColumnsSet::orderBy('display_sequence', 'asc')->get();

        return view('plugins.manage.reservation.edit', [
            "function" => $function,
            "plugin_name" => "reservation",
            "facility" => $facility,
            "categories" => $categories,
            "columns_sets" => $columns_sets,
        ]);
    }

    /**
     * 施設更新処理
     */
    public function update($request, $id)
    {
        // エラーチェック
        $validator = Validator::make($request->all(), [
            'hide_flag'  => ['required'],
            'facility_name'  => ['required'],
            'reservations_categories_id'  => ['required'],
            'columns_set_id'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'hide_flag'  => '表示',
            'facility_name'  => '施設名',
            'reservations_categories_id'  => '施設カテゴリ',
            'columns_set_id'  => '項目セット',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 表示順が空なら、自分を省いた最後の番号+1 をセット
        $display_sequence = $this->getSaveDisplaySequence(ReservationsFacility::query(), $request->display_sequence, $id);

        // 施設の登録処理
        $facility = ReservationsFacility::firstOrNew(['id' => $id]);
        // [TODO] 仮
        // $facility->reservations_id = $request->reservations_id;
        $facility->reservations_id = $facility->reservations_id ?: 0;

        $facility->facility_name                = $request->facility_name;
        $facility->hide_flag                    = $request->hide_flag;
        $facility->reservations_categories_id   = $request->reservations_categories_id;
        $facility->columns_set_id               = $request->columns_set_id;
        $facility->display_sequence             = $display_sequence;
        $facility->save();

        if ($id) {
            $message = '施設【 '. $request->facility_name .' 】を変更しました。';
        } else {
            $message = '施設【 '. $request->facility_name .' 】を登録しました。';
        }

        // 一覧画面に戻る
        return redirect("/manage/reservation")->with('flash_message', $message);
    }

    /**
     * 登録する表示順を取得
     */
    private function getSaveDisplaySequence($query, $display_sequence, $id)
    {
        // 表示順が空なら、自分を省いた最後の番号+1 をセット
        if (!is_null($display_sequence)) {
            $display_sequence = intval($display_sequence);
        } else {
            $max_display_sequence = $query->where('id', '<>', $id)->max('display_sequence');
            $display_sequence = empty($max_display_sequence) ? 1 : $max_display_sequence + 1;
        }
        return $display_sequence;
    }

    /**
     * 施設削除処理
     */
    public function destroy($request, $id)
    {
        $facility = ReservationsFacility::find($id);
        $facility_name = $facility->facility_name;
        $facility->delete();

        // 一覧画面に戻る
        return redirect("/manage/reservation")->with('flash_message', '施設【 '. $facility_name .' 】を削除しました。');
    }

    /**
     * 施設をコピーして登録画面へ処理
     */
    public function copy($request, $id = null)
    {
        return redirect("/manage/reservation/regist")->withInput();
    }

    /**
     * 施設カテゴリ表示画面
     *
     * @return view
     */
    public function categories($request, $id = null)
    {
        // カテゴリデータの取得
        $categories = ReservationsCategory::orderBy('display_sequence', 'asc')->get();

        return view('plugins.manage.reservation.categories', [
            "function"    => __FUNCTION__,
            "plugin_name" => "reservation",
            "categories"  => $categories,
        ]);
    }

    /**
     * 施設カテゴリ保存処理
     */
    public function saveCategories($request, $id)
    {
        /* エラーチェック
        ------------------------------------ */
        $rules = [];

        // エラーチェックの項目名
        $setAttributeNames = [];

        // 追加項目のどれかに値が入っていたら、行の他の項目も必須
        if (!empty($request->add_display_sequence) || !empty($request->add_category)) {
            // 項目のエラーチェック
            $rules['add_display_sequence'] = ['required'];
            $rules['add_category'] = ['required'];

            $setAttributeNames['add_display_sequence'] = '追加行の表示順';
            $setAttributeNames['add_category'] = '追加行のカテゴリ';
        }

        // 既存項目のidに値が入っていたら、行の他の項目も必須
        if (!empty($request->categories_id)) {
            foreach ($request->categories_id as $category_id) {
                // 項目のエラーチェック
                $rules['display_sequence.'.$category_id] = ['required'];
                $rules['category.'.$category_id] = ['required'];

                $setAttributeNames['display_sequence.'.$category_id] = '表示順';
                $setAttributeNames['category.'.$category_id] = 'カテゴリ';
            }
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($setAttributeNames);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 追加項目アリ
        if (!empty($request->add_display_sequence)) {
            ReservationsCategory::create([
                'display_sequence' => intval($request->add_display_sequence),
                'category'         => $request->add_category,
            ]);
        }

        // 既存項目アリ
        if (!empty($request->categories_id)) {
            foreach ($request->categories_id as $category_id) {
                // モデルオブジェクト取得
                $categories = ReservationsCategory::where('id', $category_id)->first();

                // データのセット
                $categories->category         = $request->category[$category_id];
                $categories->display_sequence = $request->display_sequence[$category_id];

                // 保存
                $categories->save();
            }
        }

        return redirect()->back()->with('flash_message', '変更しました。');
    }

    /**
     *  カテゴリ削除処理
     */
    public function deleteCategories($request, $id)
    {
        // カテゴリ削除
        $category = ReservationsCategory::find($id);
        $category_name = $category->category;
        $category->delete();

        return redirect()->back()->with('flash_message', '【 '. $category_name .' 】を削除しました。');
    }

    /**
     * 項目セット一覧 初期表示
     *
     * @return view
     */
    public function columnSets($request, $id = null)
    {
        /* ページの処理（セッション）
        ----------------------------------------------*/

        // 表示ページ数。詳細で更新して戻ってきたら、元と同じページを表示したい。
        // セッションにあればページの指定があれば使用。
        // ただし、リクエストでページ指定があればそれが優先。(ページング操作)
        $page = 1;
        if ($request->session()->has('reservation_columns_set_page_condition.page')) {
            $page = $request->session()->get('reservation_columns_set_page_condition.page');
        }
        if ($request->filled('page')) {
            $page = $request->page;
        }

        // ページがリクエストで指定されている場合は、セッションの検索条件配列のページ番号を更新しておく。
        // 詳細画面や更新処理から戻ってきた時用
        if ($request->filled('page')) {
            session(["reservation_columns_set_page_condition.page" => $request->page]);
        }

        /* データの取得
        ----------------------------------------------*/

        // 施設項目セット取得
        $columns_sets = ReservationsColumnsSet::orderBy('display_sequence')->paginate(10, '*', 'page', $page);

        return view('plugins.manage.reservation.column_sets', [
            "function"      => __FUNCTION__,
            "plugin_name"   => "reservation",
            "columns_sets"  => $columns_sets,
        ]);
    }

    /**
     * 項目セット 登録画面表示
     *
     * @return view
     */
    public function registColumnSet($request)
    {
        return $this->editColumnSet($request, null, 'registColumnSet');
    }

    /**
     * 項目セット 登録処理
     */
    public function storeColumnSet($request)
    {
        return $this->updateColumnSet($request, null);
    }

    /**
     * 項目セット 変更画面表示
     *
     * @return view
     */
    public function editColumnSet($request, $id = null, $function = null)
    {
        $columns_set = ReservationsColumnsSet::firstOrNew(['id' => $id]);

        $function = $function ?? 'editColumnSet';

        return view('plugins.manage.reservation.edit_column_set', [
            "function" => $function,
            "plugin_name" => "reservation",
            "columns_set" => $columns_set,
        ]);
    }

    /**
     * 項目セット 更新処理
     */
    public function updateColumnSet($request, $id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
        ]);
        $validator->setAttributeNames([
            'name' => '項目セット名',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 表示順が空なら、自分を省いた最後の番号+1 をセット
        $display_sequence = $this->getSaveDisplaySequence(ReservationsColumnsSet::query(), $request->display_sequence, $id);

        $columns_set = ReservationsColumnsSet::firstOrNew(['id' => $id]);
        $columns_set->name             = $request->name;
        $columns_set->display_sequence = $display_sequence;
        $columns_set->save();

        if ($id) {
            $message = '【 '. $request->name .' 】を変更しました。';
        } else {
            $message = '【 '. $request->name .' 】を登録しました。';
        }

        // 一覧画面に戻る
        return redirect("/manage/reservation/columnSets")->with('flash_message', $message);
    }

    /**
     * 項目セット 削除処理
     */
    public function destroyColumnSet($request, $id)
    {
        $columns_set = ReservationsColumnsSet::find($id);
        $columns_set_name = $columns_set->name;
        $columns_set->delete();

        // 施設一覧画面に戻る
        return redirect("/manage/reservation/columnSets")->with('flash_message', '【 '. $columns_set_name .' 】を削除しました。');
    }

    /**
     * 項目設定 初期表示
     *
     * @return view
     */
    public function editColumns($request, $id)
    {
        // 施設項目セット取得
        // $columns_sets = ReservationsColumnsSet::orderBy('display_sequence')->paginate(10, '*', 'page', $page);

        // --- 基本データの取得
        // 施設予約＆フレームデータ
        // $reservation_frame = $this->getFrame($frame_id);

        // 施設データ
        // $reservation = new Reservation();

        // if (!empty($reservations_id)) {
        //     // id が渡ってくればid が対象
        //     $reservation = Reservation::where('id', $reservations_id)->first();
        // } elseif (!empty($reservation_frame->bucket_id)) {
        //     // Frame のbucket_id があれば、bucket_id から施設データ取得
        //     $reservation = Reservation::where('bucket_id', $reservation_frame->bucket_id)->first();
        // }

        // 施設予約データがない場合は0をセット
        // $reservations_id = empty($reservation) ? null : $reservation->id;

        // 予約項目データ
        $columns = ReservationsColumn::
            select(
                'reservations_columns.id',
                'reservations_columns.columns_set_id',
                'reservations_columns.column_type',
                'reservations_columns.column_name',
                'reservations_columns.required',
                'reservations_columns.hide_flag',
                'reservations_columns.title_flag',
                'reservations_columns.display_sequence',
                DB::raw('count(reservations_columns_selects.id) as select_count'),
                DB::raw('GROUP_CONCAT(reservations_columns_selects.select_name order by reservations_columns_selects.display_sequence SEPARATOR \',\') as select_names'),
            )
            // ->where('reservations_columns.reservations_id', $reservations_id)
            ->where('reservations_columns.columns_set_id', $id)
            // 予約項目の子データ（選択肢）
            ->leftJoin('reservations_columns_selects', function ($join) {
                $join->on('reservations_columns.id', '=', 'reservations_columns_selects.column_id');
            })
            ->groupBy(
                'reservations_columns.id',
                'reservations_columns.columns_set_id',
                'reservations_columns.column_type',
                'reservations_columns.column_name',
                'reservations_columns.required',
                'reservations_columns.hide_flag',
                'reservations_columns.title_flag',
                'reservations_columns.display_sequence',
            )
            ->orderBy('reservations_columns.display_sequence')
            ->get();

        // 新着等のタイトル指定 が設定されているか（施設予約毎に１つ設定）
        $title_flag = 0;
        foreach ($columns as $column) {
            if ($column->title_flag) {
                $title_flag = 1;
                break;
            }
        }

        return view('plugins.manage.reservation.edit_columns', [
            "function"      => __FUNCTION__,
            "plugin_name"   => "reservation",
            // "columns_sets"  => $columns_sets,
            // 'reservations_id' => $reservations_id,
            // 'reservation'   => $reservation,
            'columns'       => $columns,
            'title_flag'    => $title_flag,
        ]);
    }
}
