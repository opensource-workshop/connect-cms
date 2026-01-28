<?php

namespace App\Plugins\Manage\SpamManage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Common\Frame;
use App\Models\Common\SpamBlockHistory;
use App\Models\Common\SpamList;
use App\Models\User\Forms\Forms;

use App\Enums\SpamBlockType;

use App\Plugins\Manage\ManagePluginBase;

/**
 * スパム管理クラス
 *
 * @author 井上 雅人 <inoue@opensource-workshop.jp / masamasamasato0216@gmail.com>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category スパム管理
 * @package Controller
 * @plugin_title スパム管理
 * @plugin_desc スパムリストに関する機能が集まった管理機能です。
 */
class SpamManage extends ManagePluginBase
{
    /**
     *  権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_check_table = array();
        $role_check_table["index"]       = array('admin_site');
        $role_check_table["store"]       = array('admin_site');
        $role_check_table["edit"]        = array('admin_site');
        $role_check_table["update"]      = array('admin_site');
        $role_check_table["destroy"]     = array('admin_site');
        $role_check_table["downloadCsv"]             = array('admin_site');
        $role_check_table["blockHistory"]             = array('admin_site');
        $role_check_table["downloadBlockHistoryCsv"] = array('admin_site');
        return $role_check_table;
    }

    /**
     *  スパムリスト一覧表示
     *
     * @return view
     * @method_title スパムリスト一覧
     * @method_desc スパムリストを一覧で確認できます。
     * @method_detail メールアドレス、ドメイン、IPアドレスを登録してスパムをブロックできます。
     */
    public function index($request)
    {
        // ページネートの表示ページを取得
        $page = $this->getPaginatePageFromRequestOrSession($request, 'spam_list_page', 'page');

        // 検索条件を取得
        $search_block_type  = $request->input('search_block_type', '');
        $search_block_value = $request->input('search_block_value', '');
        $search_scope_type  = $request->input('search_scope_type', '');
        $search_memo        = $request->input('search_memo', '');

        // スパムリストを取得（検索条件適用）
        $query = SpamList::query();
        $query = $this->applySearchConditions($query, $request);

        $spam_lists = $query->orderBy('block_type')
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'page', $page)
            ->appends($request->except('page'));

        // フォーム一覧を取得（ID連想配列）
        $forms = Forms::orderBy('forms_name')->get()->keyBy('id');

        // フォームが配置されているページのURLマップを作成
        $form_page_urls = $this->getFormPageUrls($forms);

        // 画面の呼び出し
        return view('plugins.manage.spam.index', [
            "function"           => __FUNCTION__,
            "plugin_name"        => "spam",
            "spam_lists"         => $spam_lists,
            "forms"              => $forms,
            "form_page_urls"     => $form_page_urls,
            "search_block_type"  => $search_block_type,
            "search_block_value" => $search_block_value,
            "search_scope_type"  => $search_scope_type,
            "search_memo"        => $search_memo,
        ]);
    }

    /**
     *  スパムリスト追加処理
     */
    public function store($request)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'block_type'      => ['required', 'in:' . implode(',', SpamBlockType::getMemberKeys())],
            'block_value'     => ['required', 'max:255'],
            'target_forms_id' => ['required_if:scope_type,form'],
        ], [
            'target_forms_id.required_if' => '適用範囲で特定フォームを選択した場合、フォームを選択してください。',
        ]);
        $validator->setAttributeNames([
            'block_type'      => '種別',
            'block_value'     => '値',
            'target_forms_id' => 'フォーム',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect('manage/spam')
                       ->withErrors($validator)
                       ->withInput();
        }

        // 適用範囲の処理
        $target_id = null;
        if ($request->scope_type === 'form' && $request->filled('target_forms_id')) {
            $target_id = $request->target_forms_id;
        }

        // スパムリストの追加
        SpamList::create([
            'target_plugin_name' => 'forms',
            'target_id'          => $target_id,
            'block_type'         => $request->block_type,
            'block_value'        => $request->block_value,
            'memo'               => $request->memo,
        ]);

        // スパムリスト一覧画面に戻る
        return redirect("/manage/spam")->with('flash_message', 'スパムリストに追加しました。');
    }

    /**
     *  スパムリスト編集画面
     *
     * @return view
     * @method_title スパムリスト編集
     * @method_desc スパムリストを編集できます。
     * @method_detail
     */
    public function edit($request, $id)
    {
        // スパムリストデータの呼び出し
        $spam = SpamList::findOrFail($id);

        // フォーム一覧を取得（ID連想配列）
        $forms = Forms::orderBy('forms_name')->get()->keyBy('id');

        // 画面の呼び出し
        return view('plugins.manage.spam.edit', [
            "function"    => __FUNCTION__,
            "plugin_name" => "spam",
            "spam"        => $spam,
            "forms"       => $forms,
        ]);
    }

    /**
     *  スパムリスト更新処理
     */
    public function update($request, $id)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'block_value'     => ['required', 'max:255'],
            'target_forms_id' => ['required_if:scope_type,form'],
        ], [
            'target_forms_id.required_if' => '適用範囲で特定フォームを選択した場合、フォームを選択してください。',
        ]);
        $validator->setAttributeNames([
            'block_value'     => '値',
            'target_forms_id' => 'フォーム',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect('manage/spam/edit/' . $id)
                       ->withErrors($validator)
                       ->withInput();
        }

        // スパムリストデータの呼び出し
        $spam = SpamList::findOrFail($id);

        // 適用範囲の処理
        $target_id = null;
        if ($request->scope_type === 'form' && $request->filled('target_forms_id')) {
            $target_id = $request->target_forms_id;
        }

        // 更新
        $spam->target_id   = $target_id;
        $spam->block_value = $request->block_value;
        $spam->memo        = $request->memo;
        $spam->save();

        // スパムリスト一覧画面に戻る
        return redirect("/manage/spam")->with('flash_message', 'スパムリストを更新しました。');
    }

    /**
     *  スパムリスト削除処理
     */
    public function destroy($request, $id)
    {
        // httpメソッド確認
        if (!$request->isMethod('post')) {
            abort(403, '権限がありません。');
        }

        // 削除
        SpamList::where('id', $id)->delete();

        // スパムリスト一覧画面に戻る
        return redirect("/manage/spam")->with('flash_message', 'スパムリストから削除しました。');
    }

    /**
     *  CSVダウンロード
     */
    public function downloadCsv($request)
    {
        // スパムリストを取得（検索条件適用）
        $query = SpamList::query();
        $query = $this->applySearchConditions($query, $request);

        $spam_lists = $query->orderBy('block_type')
            ->orderBy('created_at', 'desc')
            ->get();

        // フォーム一覧を取得
        $forms = Forms::pluck('forms_name', 'id');

        // CSVデータの作成
        $csv_data = '';

        // ヘッダー行
        $csv_data .= '"種別","値","適用範囲","メモ","登録日時"' . "\n";

        // データ行
        foreach ($spam_lists as $spam) {
            $scope_name = is_null($spam->target_id) ? '全体' : ($forms[$spam->target_id] ?? '不明');
            $csv_data .= '"' . SpamBlockType::getDescription($spam->block_type) . '",';
            $csv_data .= '"' . str_replace('"', '""', $spam->block_value) . '",';
            $csv_data .= '"' . $scope_name . '",';
            $csv_data .= '"' . str_replace('"', '""', $spam->memo ?? '') . '",';
            $csv_data .= '"' . $spam->created_at . '"' . "\n";
        }

        // 文字コード変換（UTF-8 BOM付き）
        $csv_data = "\xEF\xBB\xBF" . $csv_data;

        // ファイル名
        $filename = 'spam_list_' . date('Ymd_His') . '.csv';

        // レスポンス
        return response($csv_data)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     *  ブロック履歴一覧表示
     *
     * @return view
     * @method_title ブロック履歴
     * @method_desc スパムブロックの履歴を確認できます。
     * @method_detail スパムフィルタリングでブロックされた履歴を一覧で確認できます。
     */
    public function blockHistory($request)
    {
        // ページネートの表示ページを取得
        $page = $this->getPaginatePageFromRequestOrSession($request, 'spam_block_history_page', 'page');

        // 検索条件を取得
        $search_block_type  = $request->input('search_block_type', '');
        $search_block_value = $request->input('search_block_value', '');
        $search_client_ip   = $request->input('search_client_ip', '');
        $search_forms_name  = $request->input('search_forms_name', '');
        $search_date_from   = $request->input('search_date_from', '');
        $search_date_to     = $request->input('search_date_to', '');

        // ブロック履歴を取得（検索条件適用）
        $query = SpamBlockHistory::query();
        $query = $this->applyBlockHistorySearchConditions($query, $request);

        $block_histories = $query->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'page', $page)
            ->appends($request->except('page'));

        // フォーム一覧を取得（ID連想配列）
        $forms = Forms::orderBy('forms_name')->get()->keyBy('id');

        // フォームが配置されているページのURLマップを作成
        $form_page_urls = $this->getFormPageUrls($forms);

        // 画面の呼び出し
        return view('plugins.manage.spam.block_history', [
            "function"           => __FUNCTION__,
            "plugin_name"        => "spam",
            "block_histories"    => $block_histories,
            "forms"              => $forms,
            "form_page_urls"     => $form_page_urls,
            "search_block_type"  => $search_block_type,
            "search_block_value" => $search_block_value,
            "search_client_ip"   => $search_client_ip,
            "search_forms_name"  => $search_forms_name,
            "search_date_from"   => $search_date_from,
            "search_date_to"     => $search_date_to,
        ]);
    }

    /**
     *  ブロック履歴CSVダウンロード
     */
    public function downloadBlockHistoryCsv($request)
    {
        // ブロック履歴を取得（検索条件適用）
        $query = SpamBlockHistory::query();
        $query = $this->applyBlockHistorySearchConditions($query, $request);

        $block_histories = $query->orderBy('created_at', 'desc')->get();

        // フォーム一覧を取得
        $forms = Forms::pluck('forms_name', 'id');

        // CSVデータの作成
        $csv_data = '';

        // ヘッダー行
        $csv_data .= '"ブロック日時","種別","マッチした値","フォーム名","IPアドレス","送信メールアドレス"' . "\n";

        // データ行
        foreach ($block_histories as $history) {
            $form_name = $history->forms_id ? ($forms[$history->forms_id] ?? '不明') : '';
            $csv_data .= '"' . $history->created_at . '",';
            $csv_data .= '"' . SpamBlockType::getDescription($history->block_type) . '",';
            $csv_data .= '"' . str_replace('"', '""', $history->block_value) . '",';
            $csv_data .= '"' . str_replace('"', '""', $form_name) . '",';
            $csv_data .= '"' . ($history->client_ip ?? '') . '",';
            $csv_data .= '"' . str_replace('"', '""', $history->submitted_email ?? '') . '"' . "\n";
        }

        // 文字コード変換（UTF-8 BOM付き）
        $csv_data = "\xEF\xBB\xBF" . $csv_data;

        // ファイル名
        $filename = 'spam_block_history_' . date('Ymd_His') . '.csv';

        // レスポンス
        return response($csv_data)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * ブロック履歴の検索条件をクエリに適用
     *
     * @param \Illuminate\Database\Eloquent\Builder $query クエリビルダー
     * @param \Illuminate\Http\Request $request リクエスト
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyBlockHistorySearchConditions($query, $request)
    {
        // 種別
        $search_block_type = $request->input('search_block_type', '');
        if (!empty($search_block_type)) {
            $query->where('block_type', $search_block_type);
        }

        // 値（部分一致）
        $search_block_value = $request->input('search_block_value', '');
        if (!empty($search_block_value)) {
            $query->where('block_value', 'like', '%' . $search_block_value . '%');
        }

        // IPアドレス（部分一致）
        $search_client_ip = $request->input('search_client_ip', '');
        if (!empty($search_client_ip)) {
            $query->where('client_ip', 'like', '%' . $search_client_ip . '%');
        }

        // フォーム名（フォームIDで検索）
        $search_forms_name = $request->input('search_forms_name', '');
        if (!empty($search_forms_name)) {
            $form_ids = Forms::where('forms_name', 'like', '%' . $search_forms_name . '%')->pluck('id');
            $query->whereIn('forms_id', $form_ids);
        }

        // 日付範囲（開始）
        $search_date_from = $request->input('search_date_from', '');
        if (!empty($search_date_from)) {
            $query->where('created_at', '>=', $search_date_from . ' 00:00:00');
        }

        // 日付範囲（終了）
        $search_date_to = $request->input('search_date_to', '');
        if (!empty($search_date_to)) {
            $query->where('created_at', '<=', $search_date_to . ' 23:59:59');
        }

        return $query;
    }

    /**
     * 検索条件をクエリに適用
     *
     * @param \Illuminate\Database\Eloquent\Builder $query クエリビルダー
     * @param \Illuminate\Http\Request $request リクエスト
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applySearchConditions($query, $request)
    {
        // 種別
        $search_block_type = $request->input('search_block_type', '');
        if (!empty($search_block_type)) {
            $query->where('block_type', $search_block_type);
        }

        // 値（部分一致）
        $search_block_value = $request->input('search_block_value', '');
        if (!empty($search_block_value)) {
            $query->where('block_value', 'like', '%' . $search_block_value . '%');
        }

        // 適用範囲
        $search_scope_type = $request->input('search_scope_type', '');
        if ($search_scope_type === 'global') {
            $query->whereNull('target_id');
        } elseif ($search_scope_type === 'form') {
            $query->whereNotNull('target_id');
        }

        // メモ（部分一致）
        $search_memo = $request->input('search_memo', '');
        if (!empty($search_memo)) {
            $query->where('memo', 'like', '%' . $search_memo . '%');
        }

        return $query;
    }

    /**
     * フォームが配置されているページのURLマップを作成
     *
     * @param \Illuminate\Support\Collection $forms フォーム一覧（ID連想配列）
     * @return array forms_id => ページURL の連想配列
     */
    private function getFormPageUrls($forms)
    {
        $form_page_urls = [];

        // フォームのbucket_idからFrameを取得し、ページURLを特定
        $frames = Frame::whereIn('bucket_id', $forms->pluck('bucket_id'))
            ->where('plugin_name', 'forms')
            ->with('page')
            ->get();

        foreach ($frames as $frame) {
            $form = $forms->firstWhere('bucket_id', $frame->bucket_id);
            if ($form && $frame->page && !isset($form_page_urls[$form->id])) {
                $form_page_urls[$form->id] = url($frame->page->permanent_link);
            }
        }

        return $form_page_urls;
    }
}
