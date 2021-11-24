<?php

namespace App\Plugins\Manage\ReservationManage;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Models\User\Reservations\ReservationsFacility;
use App\Models\User\Reservations\ReservationsCategory;
use App\Models\User\Reservations\ReservationsColumn;
use App\Models\User\Reservations\ReservationsColumnsSelect;
use App\Models\User\Reservations\ReservationsColumnsSet;

use App\Plugins\Manage\ManagePluginBase;

use App\Enums\Required;

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
        $role_check_table = [];
        // 施設一覧
        $role_check_table["index"]              = ['admin_site'];
        $role_check_table["regist"]             = ['admin_site'];
        $role_check_table["store"]              = ['admin_site'];
        $role_check_table["edit"]               = ['admin_site'];
        $role_check_table["update"]             = ['admin_site'];
        $role_check_table["destroy"]            = ['admin_site'];
        $role_check_table["copy"]               = ['admin_site'];

        // 施設カテゴリ設定
        $role_check_table["categories"]         = ['admin_site'];
        $role_check_table["saveCategories"]     = ['admin_site'];
        $role_check_table["deleteCategories"]   = ['admin_site'];

        // 項目セット
        $role_check_table["columnSets"]         = ['admin_site'];
        $role_check_table["registColumnSet"]    = ['admin_site'];
        $role_check_table["storeColumnSet"]     = ['admin_site'];
        $role_check_table["editColumnSet"]      = ['admin_site'];
        $role_check_table["updateColumnSet"]    = ['admin_site'];
        $role_check_table["destroyColumnSet"]   = ['admin_site'];

        // 項目設定
        $role_check_table["editColumns"]        = ['admin_site'];
        $role_check_table["addColumn"]          = ['admin_site'];
        $role_check_table["updateColumn"]       = ['admin_site'];
        $role_check_table["updateColumnSequence"] = ['admin_site'];

        // 項目詳細設定
        $role_check_table["editColumnDetail"]   = ['admin_site'];
        $role_check_table["updateColumnDetail"] = ['admin_site'];
        $role_check_table["addSelect"]          = ['admin_site'];
        $role_check_table["updateSelect"]       = ['admin_site'];

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
        $columns_set = ReservationsColumnsSet::find($id);
        if (!$columns_set) {
            abort(404, '項目セットデータがありません。');
        }

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
            "function"       => __FUNCTION__,
            "plugin_name"    => "reservation",
            'columns_set'    => $columns_set,
            'columns'        => $columns,
            'title_flag'     => $title_flag,
        ]);
    }

    /**
     * 予約項目の登録
     */
    public function addColumn($request, $id)
    {
        // エラーチェック
        $validator = Validator::make($request->all(), [
            'column_name'  => ['required'],
            'column_type'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'column_name'  => '予約項目名',
            'column_type'  => '型',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 新規登録時の表示順を設定
        $max_display_sequence = ReservationsColumn::where('columns_set_id', $request->columns_set_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 施設の登録処理
        $column = new ReservationsColumn();
        // [TODO] 仮
        $column->reservations_id = 0;

        $column->columns_set_id = $request->columns_set_id;
        $column->column_name = $request->column_name;
        $column->column_type = $request->column_type;
        $column->required = $request->required ? Required::on : Required::off;
        $column->display_sequence = $max_display_sequence;
        $column->save();
        $message = '予約項目【 '. $request->column_name .' 】を追加しました。';

        // 編集画面を呼び出す
        return redirect("/manage/reservation/editColumns/" . $request->columns_set_id)->with('flash_message', $message);
    }

    /**
     * 予約項目の更新
     */
    public function updateColumn($request, $page_id, $frame_id)
    {
        // 明細行から更新対象を抽出する為のnameを取得
        $str_column_name = "column_name_"."$request->column_id";
        $str_column_type = "column_type_"."$request->column_id";
        $str_required = "required_"."$request->column_id";
        $str_hide_flag = "hide_flag_"."$request->column_id";

        // エラーチェック
        $validator = Validator::make($request->all(), [
            $str_column_name => ['required'],
            $str_column_type => ['required'],
        ]);
        $validator->setAttributeNames([
            $str_column_name => '予約項目名',
            $str_column_type => '型',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 予約項目の更新処理
        $column = ReservationsColumn::where('columns_set_id', $request->columns_set_id)->where('id', $request->column_id)->first();
        $column->column_name = $request->$str_column_name;
        $column->column_type = $request->$str_column_type;
        $column->required = $request->$str_required ? Required::on : Required::off;
        $column->hide_flag = $request->$str_hide_flag;
        $column->save();
        $message = '予約項目【 '. $request->$str_column_name .' 】を更新しました。';

        // 編集画面を呼び出す
        return redirect("/manage/reservation/editColumns/" . $request->columns_set_id)->with('flash_message', $message);
    }

    /**
     * 予約項目の表示順の更新
     */
    public function updateColumnSequence($request, $id)
    {
        // ボタンが押された行の施設データ
        $target_column = ReservationsColumn::where('columns_set_id', $request->columns_set_id)
            ->where('id', $request->column_id)
            ->first();

        // ボタンが押された前（後）の施設データ
        $query = ReservationsColumn::where('columns_set_id', $request->columns_set_id);
        $pair_column = $request->display_sequence_operation == 'up' ?
            $query->where('display_sequence', '<', $request->display_sequence)->orderby('display_sequence', 'desc')->limit(1)->first() :
            $query->where('display_sequence', '>', $request->display_sequence)->orderby('display_sequence', 'asc')->limit(1)->first();

        // それぞれの表示順を退避
        $target_column_display_sequence = $target_column->display_sequence;
        $pair_column_display_sequence = $pair_column->display_sequence;

        // 入れ替えて更新
        $target_column->display_sequence = $pair_column_display_sequence;
        $target_column->save();
        $pair_column->display_sequence = $target_column_display_sequence;
        $pair_column->save();

        $message = '予約項目【 '. $target_column->column_name .' 】の表示順を更新しました。';

        // 編集画面を呼び出す
        return redirect("/manage/reservation/editColumns/" . $request->columns_set_id)->with('flash_message', $message);
    }

    /**
     * 予約項目の設定画面の表示
     */
    public function editColumnDetail($request, $id)
    {
        // --- 画面に値を渡す準備
        $column = ReservationsColumn::where('id', $id)->first();
        if (!$column) {
            abort(404, 'カラムデータがありません。');
        }

        $columns_set = ReservationsColumnsSet::find($column->columns_set_id);
        if (!$columns_set) {
            abort(404, '項目セットデータがありません。');
        }

        $selects = ReservationsColumnsSelect::where('column_id', $column->id)->orderby('display_sequence')->get();

        return view('plugins.manage.reservation.edit_column_detail', [
            "function"       => __FUNCTION__,
            "plugin_name"    => "reservation",
            'columns_set'     => $columns_set,
            'column'          => $column,
            'selects'         => $selects,
        ]);
    }

    /**
     * 項目に紐づく詳細設定の更新
     */
    public function updateColumnDetail($request, $id)
    {
        // タイトル指定
        $title_flag = (empty($request->title_flag)) ? 0 : $request->title_flag;
        if ($title_flag) {
            // title_flagは施設予約内で１つだけ ON にする項目
            // そのため title_flag = 1 なら 施設予約内の title_flag = 1 を一度 0 に更新する。
            ReservationsColumn::where('columns_set_id', $request->columns_set_id)
                ->where('title_flag', 1)
                ->update(['title_flag' => 0]);
        }

        // 更新データは上記update後に取得しないと、title_flagが更新されない
        $column = ReservationsColumn::where('id', $request->column_id)->first();

        // タイトル指定
        $column->title_flag = $title_flag;

        // 保存
        $column->save();

        $message = '項目【 '. $column->column_name .' 】の詳細設定を更新しました。';

        return redirect("/manage/reservation/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }

    /**
     * 予約詳細項目（選択肢）の登録
     */
    public function addSelect($request, $id)
    {
        // エラーチェック
        $validator = Validator::make($request->all(), [
            'select_name'  => ['required'],
        ]);
        $validator->setAttributeNames([
            'select_name'  => '選択肢名',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 新規登録時の表示順を設定
        $max_display_sequence = ReservationsColumnsSelect::where('column_id', $request->column_id)->max('display_sequence');
        $max_display_sequence = $max_display_sequence ? $max_display_sequence + 1 : 1;

        // 施設の登録処理
        $select = new ReservationsColumnsSelect();
        // [TODO] 仮
        $select->reservations_id = 0;

        $select->columns_set_id = $request->columns_set_id;
        $select->column_id = $request->column_id;
        $select->select_name = $request->select_name;
        $select->display_sequence = $max_display_sequence;
        $select->save();
        $message = '選択肢【 '. $request->select_name .' 】を追加しました。';

        // 編集画面を呼び出す
        return redirect("/manage/reservation/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }

    /**
     * 選択肢の更新
     */
    public function updateSelect($request, $id)
    {
        // 明細行から更新対象を抽出する為のnameを取得
        $str_select_name = "select_name_"."$request->select_id";
        $str_hide_flag = "hide_flag_"."$request->select_id";

        // エラーチェック
        $validator = Validator::make($request->all(), [
            $str_select_name => ['required'],
        ]);
        $validator->setAttributeNames([
            $str_select_name => '選択肢名',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 予約項目の更新処理
        $select = ReservationsColumnsSelect::where('id', $request->select_id)->first();
        $select->select_name = $request->$str_select_name;
        $select->hide_flag = $request->$str_hide_flag;
        $select->save();
        $message = '選択肢【 '. $request->$str_select_name .' 】を更新しました。';

        // 編集画面を呼び出す
        return redirect("/manage/reservation/editColumnDetail/" . $request->column_id)->with('flash_message', $message);
    }
}
