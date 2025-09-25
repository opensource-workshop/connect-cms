<?php

namespace App\Plugins\Manage\PageManage;

use App\Enums\PageCvsIndex;
use App\Enums\PageMetaRobots;
use App\Enums\WebsiteType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Group;
use App\Models\Common\GroupUser;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Models\User\Contents\Contents;
use App\Plugins\Manage\ManagePluginBase;
use App\Rules\CustomValiMetaRobots;
use App\Rules\CustomValiTextMax;
use App\Rules\CustomValiUrlMax;
use App\Traits\Migration\MigrationTrait;
use App\Traits\Migration\MigrationExportNc3PageTrait;
use App\Traits\Migration\MigrationExportHtmlPageTrait;
use App\User;
use App\Utilities\Csv\CsvUtils;
use App\Utilities\String\StringUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * ページ管理クラス
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category ページ管理
 * @package Controller
 * @plugin_title ページ管理
 * @plugin_desc ページの作成や設定など、ページに関する機能が集まった管理機能です。
 * @spec ページを管理できること。
         ページに必要な情報として「ページ名」、「固定リンク」、「メニューへの表示の有無」を持つこと。
         ページに必要な機能として「ページにパスワードを設定する」、「ページにデザインテーマを設定する」、「ページを閲覧可能なIPアドレスを指定できる」こと。
 */
class PageManage extends ManagePluginBase
{
    // 移行用ライブラリ
    use MigrationTrait, MigrationExportNc3PageTrait, MigrationExportHtmlPageTrait;

    // 外部ページインポート（Webスクレイピング）リクエスト間隔（秒）を指定
    protected $request_interval = 10;

    /**
     * 権限定義
     */
    public function declareRole()
    {
        // 権限チェックテーブル
        $role_ckeck_table = [];
        $role_ckeck_table["index"]               = ['admin_page'];
        $role_ckeck_table["edit"]                = ['admin_page'];
        $role_ckeck_table["store"]               = ['admin_page'];
        $role_ckeck_table["update"]              = ['admin_page'];
        $role_ckeck_table["destroy"]             = ['admin_page'];
        $role_ckeck_table["sequenceUp"]          = ['admin_page'];
        $role_ckeck_table["sequenceTop"]         = ['admin_page'];
        $role_ckeck_table["sequenceDown"]        = ['admin_page'];
        $role_ckeck_table["sequenceBottom"]      = ['admin_page'];
        $role_ckeck_table["movePage"]            = ['admin_page'];
        $role_ckeck_table["import"]              = ['admin_page'];
        $role_ckeck_table["upload"]              = ['admin_page'];
        $role_ckeck_table["downloadCsvFormat"]   = ['admin_page'];
        $role_ckeck_table["downloadCsvSample"]   = ['admin_page'];
        $role_ckeck_table["role"]                = ['admin_page'];
        $role_ckeck_table["saveRole"]            = ['admin_page'];
        $role_ckeck_table["roleList"]            = ['admin_page'];
        $role_ckeck_table["migrationOrder"]      = ['admin_page'];
        $role_ckeck_table["migrationGet"]        = ['admin_page'];
        $role_ckeck_table["migrationImort"]      = ['admin_page'];
        $role_ckeck_table["migrationFileDelete"] = ['admin_page'];
        $role_ckeck_table["toggleDisplay"]       = ['admin_page'];
        return $role_ckeck_table;
    }

    /**
     * ページ初期表示
     *
     * @return view
     * @method_title ページ一覧
     * @method_desc ページの一覧が表示されます。<br />ページに関する設定などが俯瞰できる画面です。
     * @method_detail ページの内容を編集するときは、ページ名の左にある編集ボタンをクリックしてください。
     * @spec ページの一覧が表示されること。
     */
    public function index($request, $page_id = null, $errors = array())
    {
        // ページデータの取得(laravel-nestedset 使用)
        $return_obj = 'flat';
        $pages = Page::defaultOrderWithDepth($return_obj);


        // ページ権限を取得してGroup オブジェクトに保持する。
        $page_roles = PageRole::join('groups', 'groups.id', '=', 'page_roles.group_id')
                ->whereNull('groups.deleted_at')
                ->where('page_roles.role_value', 1)
                ->get();
        // \Log::debug(var_export($page_roles, true));

        foreach ($pages as &$page) {
            $page->page_roles = $page_roles->where('page_id', $page->id);
        }

        // 移動先用にコピー
        $pages_select = $pages;

        // テーマの取得
        $themes = $this->getThemes();

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.page.page', [
            "function"     => __FUNCTION__,
            "plugin_name"  => "page",
            "page"         => new Page(),
            "pages"        => $pages,
            "pages_select" => $pages_select,
            "themes"       => $themes,
            "errors"       => $errors,
        ]);
    }

    /**
     * ページ編集画面表示
     *
     * @return view
     * @method_title ページ編集
     * @method_desc ページの登録や編集を行う画面です。
     * @method_detail <ul><li>ページに関する各項目を設定してページの作成ができます。</li>
                          <li>IPアドレス制限は、学内や社内などの特定のIPアドレスから参照されている時だけ、表示を許可したい。という使い方です。IPアドレス制限した場合は、URLを直接指定しても、制限が有効になり指定したIPアドレス以外からは参照できません。</li>
                          <li>ページを削除した場合でも、コンテンツは削除されていません。コンテンツを削除したい場合は、各コンテンツの設定画面にて削除を行ってください。</li></ul>
     * @spec ページの登録と変更が行えること。
             ページには固定リンクを設定できること。
             パスワードで保護されたページを作成できること。
             ページにはCSS等からなるデザインテーマを設定できること。
             ページは組織内や特定の環境からのみ閲覧できるようにするため、IPアドレスで閲覧制限できること。
             メニューに表示されるページの順番を変更できること。
     */
    public function edit($request, $page_id = null)
    {
        // 編集時と新規で処理を分ける
        if (empty($page_id)) {
            $page = new Page();
        } else {
            // ページID で1件取得
            $page = Page::where('id', $page_id)->first();
        }

        // ページデータの取得(laravel-nestedset 使用)
        $pages = Page::defaultOrderWithDepth();

        // テーマの取得
        $themes = $this->getThemes();

        // 画面呼び出し
        return view('plugins.manage.page.page_edit', [
            "function"    => __FUNCTION__,
            "plugin_name" => "page",
            "page"        => $page,
            "pages"       => $pages,
            "themes"      => $themes,
        ]);
    }

    /**
     * ページ登録・変更時のエラーチェック
     */
    private function pageValidator($request, $page_id = null)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'page_name'        => ['required', 'max:255'],
            'permanent_link'   => ['nullable', new CustomValiUrlMax(true), Rule::unique('pages')->ignore($page_id)],
            'password'         => ['nullable', 'max:255'],
            'background_color' => ['nullable', 'max:255'],
            'header_color'     => ['nullable', 'max:255'],
            'ip_address'       => ['nullable', new CustomValiTextMax()],
            'othersite_url'    => ['nullable', new CustomValiUrlMax()],
            'class'            => ['nullable', 'max:255'],
            'meta_robots'      => ['nullable', 'array', new CustomValiMetaRobots()],
        ]);
        $validator->setAttributeNames([
            'page_name'        => 'ページ名',
            'permanent_link'   => '固定リンク',
            'password'         => 'パスワード',
            'background_color' => '背景色',
            'header_color'     => 'ヘッダーバーの背景色',
            'ip_address'       => 'IPアドレス制限',
            'othersite_url'    => '外部サイトURL',
            'class'            => 'メニュークラス名',
            'meta_robots'      => '検索避け設定',
        ]);
        return $validator;
    }

    /**
     * meta robotsの入力値を正規化
     */
    private function normalizeMetaRobots($request): ?string
    {
        $meta_robots = $request->input('meta_robots');

        if (is_array($meta_robots)) {
            $meta_robots = array_filter($meta_robots, function ($value) {
                return $value !== null && $value !== '';
            });

            if (empty($meta_robots)) {
                return null;
            }

            $meta_robots = array_unique($meta_robots);

            $allowed = PageMetaRobots::getMemberKeys();
            $meta_robots = array_values(array_intersect($allowed, $meta_robots));

            if (empty($meta_robots)) {
                return null;
            }

            return implode(',', $meta_robots);
        }

        if (is_string($meta_robots) && $meta_robots !== '') {
            return in_array($meta_robots, PageMetaRobots::getMemberKeys(), true) ? $meta_robots : null;
        }

        return null;
    }

    /**
     * CSVインポート時のエラーチェックルール
     */
    private function pageUploadValidatorRules()
    {
        $rules = [
            PageCvsIndex::page_name         => ['required', 'max:255'],
            PageCvsIndex::permanent_link    => ['required', new CustomValiUrlMax(true), Rule::unique('pages', 'permanent_link')],
            PageCvsIndex::background_color  => ['required', 'max:255'],
            PageCvsIndex::header_color      => ['required', 'max:255'],
            PageCvsIndex::theme             => ['required'],
            PageCvsIndex::layout            => ['required'],
            PageCvsIndex::base_display_flag => ['required', Rule::in(['0', '1'])],
        ];

        return $rules;
    }

    /**
     * ページ登録処理
     */
    public function store($request)
    {
        // 固定リンクの先頭に / がない場合、追加する。
        if (strncmp($request->permanent_link ?? '', '/', 1) !== 0) {
            $request->merge([
                "permanent_link" => '/' . $request->permanent_link,
            ]);
        }

        // ページ登録・変更時のエラーチェック
        $validator = $this->pageValidator($request);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $meta_robots = $this->normalizeMetaRobots($request);

        // ページデータの登録
        $page = new Page;
        $page->page_name            = $request->page_name;
        $page->permanent_link       = $request->permanent_link;
        $page->password             = $request->password;
        $page->background_color     = $request->background_color;
        $page->header_color         = $request->header_color;
        $page->theme                = $request->theme;
        $page->layout               = $request->layout;
        $page->base_display_flag    = (isset($request->base_display_flag) ? $request->base_display_flag : 0);
        $page->membership_flag      = (isset($request->membership_flag) ? $request->membership_flag : 0);
        $page->container_flag       = (isset($request->container_flag) ? $request->container_flag : 0);
        $page->ip_address           = $request->ip_address;
        $page->othersite_url        = $request->othersite_url;
        $page->othersite_url_target = (isset($request->othersite_url_target) ? $request->othersite_url_target : 0);
        $page->transfer_lower_page_flag = $request->transfer_lower_page_flag ?? 0;
        $page->meta_robots          = $meta_robots;
        $page->class                = $request->class;
        $page->save();

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     * ページ更新処理
     */
    public function update($request, $page_id)
    {
        // 固定リンクの先頭に / がない場合、追加する。
        if (strncmp($request->permanent_link ?? '', '/', 1) !== 0) {
            $request->merge([
                "permanent_link" => '/' . $request->permanent_link,
            ]);
        }

        // ページ登録・変更時のエラーチェック
        $validator = $this->pageValidator($request, $page_id);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $meta_robots = $this->normalizeMetaRobots($request);

        // ページデータの更新
        Page::where('id', $page_id)
            ->update([
                'page_name'            => $request->page_name,
                'permanent_link'       => $request->permanent_link,
                'password'             => $request->password,
                'background_color'     => $request->background_color,
                'header_color'         => $request->header_color,
                'theme'                => $request->theme,
                'layout'               => $request->layout,
                'base_display_flag'    => (isset($request->base_display_flag) ? $request->base_display_flag : 0),
                'membership_flag'      => (isset($request->membership_flag) ? $request->membership_flag : 0),
                'container_flag'       => (isset($request->container_flag) ? $request->container_flag : 0),
                'ip_address'           => $request->ip_address,
                'othersite_url'        => $request->othersite_url,
                'othersite_url_target' => (isset($request->othersite_url_target) ? $request->othersite_url_target : 0),
                'transfer_lower_page_flag' => $request->transfer_lower_page_flag ?? 0,
                'meta_robots'          => $meta_robots,
                'class'                => $request->class,
        ]);

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     * ページ削除関数
     */
    public function destroy($request, $page_id)
    {
        // Log::debug($id);
        DB::table('pages')->where('id', '=', $page_id)->delete();

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     * ページ上移動
     */
    public function sequenceUp($request, $page_id)
    {
        // 移動元のオブジェクトを取得して、up
        $pages = Page::find($page_id);
        $pages->up();

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     * ページを一番上へ移動
     */
    public function sequenceTop($request, $page_id)
    {
        // 移動元のオブジェクトを取得
        $pages = Page::find($page_id);
        // 兄弟の一番上取得
        $sibling = $pages->siblings()->defaultOrder()->first();
        $pages->insertBeforeNode($sibling);

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     * ページ下移動
     */
    public function sequenceDown($request, $page_id)
    {
        // 移動元のオブジェクトを取得して、down
        $pages = Page::find($page_id);
        $pages->down();

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     * ページを一番下へ移動
     */
    public function sequenceBottom($request, $page_id)
    {
        // 移動元のオブジェクトを取得
        $pages = Page::find($page_id);
        // 兄弟の一番下取得
        $sibling = $pages->siblings()->reversed()->first();
        $pages->insertAfterNode($sibling);

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     * ページ階層移動
     *
     * @method_title ページ階層移動
     * @method_desc ページは移動先を指定することで、階層を変更することができます。また、上下矢印でメニューへの表示順番を変更することもできます。
     * @spec ページは階層構造を作成できること。
             ページは作成後に階層を変更できること。
     */
    public function movePage($request, $page_id)
    {
        // ルートへ移動
        if ($request->destination_id == "0") {
            // 移動元のオブジェクトを取得
            $page = Page::find($page_id);
            $page->saveAsRoot();
        } else {
            // その他の場所へ移動

            // 移動元のオブジェクトを取得
            $source_page = Page::find($page_id);

            // 移動先のオブジェクトを取得
            $destination_page = Page::find($request->destination_id);

            // 移動
            $source_page->appendToNode($destination_page)->save();
        }

        // ページ管理画面に戻る
        return redirect("/manage/page");
    }

    /**
     * ページインポート画面表示
     *
     * @return view
     */
    public function import($request, $page_id)
    {
        // 画面呼び出し
        return view('plugins.manage.page.page_import', [
            "function"     => __FUNCTION__,
            "plugin_name"  => "page",
        ]);
    }

    /**
     * CSVインポートのフォーマットダウンロード
     */
    public function downloadCsvFormat($request, $id = null)
    {
        // 返却用配列
        $csv_array = [];

        // 見出し行-頭（固定項目）
        $csv_array[0] = $this->getCsvHeader();

        // レスポンス版
        $filename = 'page.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        // データ
        $csv_data = CsvUtils::getResponseCsvData($csv_array, $request->character_code);

        return response()->make($csv_data, 200, $headers);
    }

    /**
     * CSVインポートのサンプルダウンロード
     */
    public function downloadCsvSample($request, $id = null)
    {
        // 返却用配列
        $csv_array = [];

        // 見出し行-頭（固定項目）
        $csv_array[0] = $this->getCsvHeader();

        // サンプルデータ
        $csv_array[1] = [
            PageCvsIndex::page_name => 'アップロード',
            PageCvsIndex::permanent_link => '/upload',
            PageCvsIndex::background_color => 'NULL',
            PageCvsIndex::header_color => 'NULL',
            PageCvsIndex::theme => 'NULL',
            PageCvsIndex::layout => 'NULL',
            PageCvsIndex::base_display_flag => '1',
        ];
        $csv_array[2] = [
            PageCvsIndex::page_name => 'アップロード2',
            PageCvsIndex::permanent_link => '/upload/2',
            PageCvsIndex::background_color => 'NULL',
            PageCvsIndex::header_color => 'NULL',
            PageCvsIndex::theme => 'NULL',
            PageCvsIndex::layout => 'NULL',
            PageCvsIndex::base_display_flag => '1',
        ];

        // レスポンス版
        $filename = 'page_sample.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        // データ
        $csv_data = CsvUtils::getResponseCsvData($csv_array, $request->character_code);

        return response()->make($csv_data, 200, $headers);
    }

    /**
     * CSVヘッダーチェック
     */
    private function checkHeader($header_columns)
    {
        if (empty($header_columns)) {
            return array("CSVファイルが空です。");
        }

        // ヘッダーカラム
        $header_column_format = $this->getCsvHeader();

        // 項目の不足チェック
        $shortness = array_diff($header_column_format, $header_columns);
        if (!empty($shortness)) {
            return array(implode(",", $shortness) . " が不足しています。");
        }
        // 項目の不要チェック
        $excess = array_diff($header_columns, $header_column_format);
        if (!empty($excess)) {
            return array(implode(",", $excess) . " は不要です。");
        }

        return;
    }

    /**
     * CSVヘッダー取得
     */
    private function getCsvHeader(): array
    {
        $header_column_format = [];

        foreach (PageCvsIndex::getMembers() as $csv_index => $column_name) {
            $header_column_format[$csv_index] = $column_name;
        }

        return $header_column_format;
    }

    /**
     * CSVデータ行チェック
     */
    private function checkPageline($fp)
    {
        // CSVインポート時のエラーチェックルール
        $rules = $this->pageUploadValidatorRules();

        $line_count = 1;
        $errors = [];
        $permanent_links = [];

        while (($csv_columns = fgetcsv($fp, 0, ",")) !== false) {
            // バリデーション
            $validator = Validator::make($csv_columns, $rules);

            $attribute_names = [];
            foreach (PageCvsIndex::getMembers() as $csv_index => $column_name) {
                $attribute_names[$csv_index] = "{$line_count}行目の {$column_name} ";
            }

            $validator->setAttributeNames($attribute_names);

            foreach ($csv_columns as $column_index => $csv_column) {

                if ($column_index === PageCvsIndex::permanent_link) {
                    // CSV内重複チェック
                    if (in_array($csv_column, $permanent_links)) {
                        $errors[] = "{$line_count}行目の " . PageCvsIndex::getDescription(PageCvsIndex::permanent_link) . " がCSVファイル内で重複しています。";
                    }

                    // permanent_link をCSV内重複チェックするため、貯める
                    $permanent_links[] = $csv_column;
                }
            }

            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->all());
            }

            $line_count++;
        }

        return $errors;
    }

    /**
     * 「固定記事」プラグインを新規で配置
     */
    private function createContent($page_id)
    {
        // Buckets 登録
        $bucket = Buckets::create(['bucket_name' => '無題', 'plugin_name' => 'contents']);

        // フレーム作成
        $frame = Frame::create(['page_id'          => $page_id,
                                'area_id'          => 2,
                                'frame_title'      => '[無題]',
                                'frame_design'     => 'default',
                                'plugin_name'      => 'contents',
                                'frame_col'        => 0,
                                'template'         => 'default',
                                'bucket_id'        => $bucket->id,
                                'display_sequence' => 1,
                               ]);

        // Contents 登録
        $content = Contents::create(['bucket_id'    => $bucket->id,
                                     'status'       => 0]);

        return true;
    }

    /**
     * ページインポート処理
     *
     * @return view
     * @method_title CSVインポート
     * @method_desc CSVファイルをアップロードして、ページの登録ができます。
     * @method_detail 画面のCSVフォーマットをコピーして使用してください。
     * @spec ページはCSVファイルをインポートして作成できること。
     */
    public function upload($request, $page_id)
    {
        // CSVファイルチェック
        $validator = Validator::make($request->all(), [
            'page_csv' => [
                'required',
                'file',
                'mimes:csv,txt', // mimesの都合上text/csvなのでtxtも許可が必要
            ],
        ]);
        $validator->setAttributeNames([
            'page_csv' => 'インポートCSV',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // CSVファイル一時保孫
        $path = $request->file('page_csv')->store('tmp');

        CsvUtils::setLocale();

        // 一行目（ヘッダ）読み込み
        $fp = fopen(storage_path('app/') . $path, 'r');
        // ストリームフィルタ内で、Shift-JIS -> UTF-8変換
        $fp = CsvUtils::setStreamFilterRegisterSjisToUtf8($fp);
        $header_columns = fgetcsv($fp);

        // ヘッダー項目のエラーチェック
        $error_msgs = $this->checkHeader($header_columns);
        if (!empty($error_msgs)) {
            // 一時ファイルの削除
            fclose($fp);
            Storage::delete($path);

            return redirect()->back()->withErrors(['page_csv' => $error_msgs])->withInput();
        }

        // データ項目のエラーチェック
        $error_msgs = $this->checkPageline($fp);
        if (!empty($error_msgs)) {
            // 一時ファイルの削除
            fclose($fp);
            Storage::delete($path);

            return redirect()->back()->withErrors(['page_csv' => $error_msgs])->withInput();
        }

        // ファイルポインタの位置を先頭に戻す
        rewind($fp);

        // ヘッダー
        $header_columns = fgetcsv($fp);

        DB::beginTransaction();
        try {
            // データ
            while (($csv_columns = fgetcsv($fp, 0, ",")) !== false) {
                // --- 入力値変換
                // 入力値をトリム(preg_replace(/u)で置換. /u = UTF-8 として処理)
                $csv_columns = StringUtils::trimInput($csv_columns);

                foreach ($csv_columns as $col => &$csv_column) {
                    // 空文字をnullに変換
                    $csv_column = $this->convertEmptyStringsToNull($csv_column);
                }

                // 固定リンクの先頭に / がない場合、追加する。
                if (strncmp($csv_columns[PageCvsIndex::permanent_link], '/', 1) !== 0) {
                    $csv_columns[PageCvsIndex::permanent_link] = '/' . $csv_columns[PageCvsIndex::permanent_link];
                }

                // ページ作成
                $page = Page::create([
                    'page_name'         => $csv_columns[PageCvsIndex::page_name],
                    'permanent_link'    => $csv_columns[PageCvsIndex::permanent_link],
                    'background_color'  => $csv_columns[PageCvsIndex::background_color],
                    'header_color'      => $csv_columns[PageCvsIndex::header_color],
                    'theme'             => $csv_columns[PageCvsIndex::theme],
                    'layout'            => $csv_columns[PageCvsIndex::layout],
                    'base_display_flag' => $csv_columns[PageCvsIndex::base_display_flag]
                ]);

                // 初期配置がある場合
                if ($request->has('deploy_content_plugin') && $request->deploy_content_plugin == '1') {
                    $this->createContent($page->id);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            // 一時ファイルの削除
            fclose($fp);
            Storage::delete($path);
        }

        // ページ管理画面に戻る
        return redirect("/manage/page/import")->with('flash_message', 'インポートしました。');
    }

    /**
     * 空文字をnullに変換
     *
     * @param  mixed  $value
     * @return mixed
     */
    private function convertEmptyStringsToNull($value)
    {
        $value = StringUtils::convertEmptyStringsToNull($value);
        return $value == 'NULL' ? null : $value;
    }

    /**
     * グループ権限設定画面表示
     *
     * @return view
     */
    public function role($request, $page_id, $group_id)
    {
        // ページID で1件取得
        $page = Page::find($page_id);

        // ページデータ取得
        if (empty($page)) {
            // 画面呼び出し
            return view('plugins.manage.page.error', [
                "function"     => __FUNCTION__,
                "plugin_name"  => "page",
                "message"      => "指定されたページID が存在しません。",
            ]);
        }

        // グループの取得
        $groups = Group::orderBy('display_sequence', 'asc')->get();

        // ページ権限を取得してGroup オブジェクトに保持する。
        $page_roles = PageRole::where('page_id', $page->id)
            ->where('role_value', 1)
            ->orderBy('group_id', 'asc')
            ->get();

        // 数万ユーザでメモリ不足になるため、GROUP_CONCAT()を使用して、グループ参加者のユーザ名を取得
        // GROUP_CONCAT()はmysql設定でgroup_concat_max_len(default=1024)の制限があり、それ以上の長さの文字列は消える。
        $group_users = User::
            select(
                'group_users.group_id',
                DB::raw("GROUP_CONCAT(users.name SEPARATOR ',') as group_user_names"),
                DB::raw('count(group_users.group_id) as user_count')
            )
            ->join('group_users', 'group_users.user_id', '=', 'users.id')
            ->where('group_users.deleted_at', null)
            ->groupBy('group_users.group_id')
            ->get();

        foreach ($groups as $group) {
            $group->page_roles = $page_roles->where('group_id', $group->id);
            $group->group_user_names = optional($group_users->firstWhere('group_id', $group->id))->group_user_names;
            $group->group_user_count = optional($group_users->firstWhere('group_id', $group->id))->user_count ?? 0;
        }

        // 自分のページから親を遡って取得
        $page_tree = $page->getPageTreeByGoingBackParent(null);

        // 自分及び先祖ページにグループ権限が設定されていなければ戻る
        $page_parent = new Page();
        foreach ($page_tree as $page_tmp) {
            if (! $page_tmp->page_roles->isEmpty()) {
                $page_parent = $page_tmp;
                break;
            }
        }

        // 画面呼び出し
        return view('plugins.manage.page.role', [
            "function"     => __FUNCTION__,
            "plugin_name"  => "page",
            "page"         => $page,
            "group_id"     => $group_id,
            "groups"       => $groups,
            "page_roles"   => $page_roles,
            "page_parent"  => $page_parent,
        ]);
    }

    /**
     * page_roles テーブルの更新
     */
    private function updatePageRoles($page_id, $group_id, $role_name, $role_value)
    {
        if (empty($role_value)) {
            // role_value が空の場合は、チェックされていないということなので、delete
            // この時、もともとレコードがない場合は 0件削除されるだけなので、delete 処理する。
            PageRole::where('page_id', $page_id)->where('group_id', $group_id)->where('role_name', $role_name)->delete();
        } else {
            // 更新もしくは追加
            PageRole::updateOrCreate(
                ['page_id' => $page_id, 'group_id' => $group_id, 'target' => 'base', 'role_name' => $role_name,],
                ['page_id' => $page_id, 'group_id' => $group_id, 'target' => 'base', 'role_name' => $role_name, 'role_value' => 1]
            );
        }
        return;
    }

    /**
     * グループ権限保存
     *
     * @return view
     */
    public function saveRole($request, $page_id)
    {
        // Role をループ
        foreach (config('cc_role.CC_ROLE_LIST') as $role_name => $cc_role_name) {
            // 管理権限は対象外
            if (stripos($role_name, 'admin_') === 0) {
                continue;
            }
            // page_roles 更新
            $this->updatePageRoles($page_id, $request->group_id, $role_name, $request->input($role_name));
        }

        // 更新後は一覧画面へ
        return redirect('manage/page/role/' . $page_id . '/' . $request->group_id);
    }

    /**
     * 外部ページ取り込み指示画面
     *
     * @return view
     */
    public function migrationOrder($request, $page_id)
    {
        // ページID で1件取得
        $current_page = Page::find($page_id);

        // ページデータ取得
        if (empty($current_page)) {
            // 画面呼び出し
            return view('plugins.manage.page.error', [
                "function"     => __FUNCTION__,
                "plugin_name"  => "page",
                "message"      => "指定されたページID が存在しません。",
            ]);
        }

        // 移行先ページ用ページデータの取得(laravel-nestedset 使用)
        $return_obj = 'flat';
        $pages = Page::defaultOrderWithDepth($return_obj);

        // 移行用に取り込んだページ単位ディレクトリの取得
        $migration_directories = Storage::directories('migration/import/pages');

        // 移行用に取り込んだページ単位ディレクトリのページ情報
        $page_in = [];
        $migration_directories_page_ids = [];
        foreach ($migration_directories as $migration_directory) {
            $page_id_dir = str_replace('migration/import/pages/', '', $migration_directory);
            $page_id = (int) $page_id_dir;
            $page_in[] = $page_id;
            $migration_directories_page_ids[$page_id] = [
                'migration_directory' => $migration_directory,
                'page_id_dir'         => $page_id_dir,
            ];
        }

        // ページ一覧の取得
        $migration_pages = Page::whereIn('id', $page_in)->get();
        foreach ($migration_pages as $page) {
            // ページ毎のディレクトリ更新日時
            $page->migration_directory_timestamp = Carbon::createFromTimestamp(Storage::lastModified($migration_directories_page_ids[$page_id]['migration_directory']))->format('Y/m/d H:i:s');
            $page->delete_file_page_id_dir = $migration_directories_page_ids[$page_id]['page_id_dir'];
        }

        // 画面呼び出し
        return view('plugins.manage.page.migration_order', [
            "function"        => __FUNCTION__,
            "plugin_name"     => "page",
            "current_page"    => $current_page,
            "page"            => $current_page,  // bugfix: サブメニュー表示するのにpage変数必要
            "pages"           => $pages,
            "migration_pages" => $migration_pages,
            "request_interval" => $this->request_interval,
        ]);
    }

    /**
     * 取り込み済み移行データ削除
     *
     * @return view
     */
    public function migrationFileDelete($request, $page_id)
    {
        // 削除対象のディレクトリが指定されていること。
        if ($request->delete_file_page_id_dir) {
            // 指定されたディレクトリを削除
            Storage::deleteDirectory("migration/import/pages/{$request->delete_file_page_id_dir}");

            session()->flash('flash_message', '指定した取り込み済み移行データを削除しました。');
        }

        // 指示された画面に戻る。
        return redirect("manage/page/migrationOrder/{$page_id}");
    }

    /**
     * 移行データ取り込み実行
     *
     * @return view
     */
    public function migrationGet($request, $page_id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'source_system'       => 'required',
            'url'                 => 'required',
            'destination_page_id' => 'required',
        ]);
        $validator->setAttributeNames([
            'source_system'       => '移行元システム',
            'url'                 => '移行元URL',
            'destination_page_id' => '移行先ページ',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect('manage/page/migrationOrder/' . $page_id)
                       ->withErrors($validator)
                       ->withInput();
        }

        /**
         * リクエスト間隔チェック
         */
        // 最後にリクエストを送信した時間を記録するファイルのパスを指定
        $last_request_time_file = 'migration/import/migration_last_request_time.txt';

        // 最後にリクエストを送信した時間をファイルから読み込む
        $last_request_time = Storage::exists($last_request_time_file) ? intval(Storage::get($last_request_time_file)) : null;

        // 前回のリクエストから一定時間経過していない場合は、エラーメッセージを追加
        if ($last_request_time !== null && (time() - $last_request_time) < $this->request_interval) {
            $validator->errors()->add('request_interval', 'リクエスト間隔が短すぎます。しばらく時間を置いてから再度ボタンを押下してください。');
            return redirect('manage/page/migrationOrder/' . $page_id)
                       ->withErrors($validator)
                       ->withInput();
        }

        // 移行元システムによって処理を分岐
        if ($request->source_system == WebsiteType::netcommons2) {
            // TODO: netcommons2 からの移行
        } elseif ($request->source_system == WebsiteType::netcommons3) {
            // netcommons3 からの移行
            $this->migrationNC3Page($request->url, $request->destination_page_id);
        } elseif ($request->source_system == WebsiteType::html) {
            // html からの移行
            $this->migrationHtmlPage($request->url, $request->destination_page_id);
        }

        // リクエストを送信した時間をファイルに書き込む
        Storage::put($last_request_time_file, time());

        // 指示された画面に戻る。
        return $this->migrationOrder($request, $page_id);
    }

    /**
     * 移行データインポート実行
     *
     * @return view
     */
    public function migrationImort($request, $page_id)
    {
        // 項目のエラーチェック
        $validator = Validator::make($request->all(), [
            'migration_page_id' => 'required',
        ]);
        $validator->setAttributeNames([
            'migration_page_id' => '取り込み済み移行データ',
        ]);

        // エラーがあった場合は入力画面に戻る。
        if ($validator->fails()) {
            return redirect('manage/page/migrationOrder/' . $page_id)
                       ->withErrors($validator)
                       ->withInput();
        }

        // migration_config を生成
        $this->migration_config['frames'] = ['import_frame_plugins'];
        $this->migration_config['frames']['import_frame_plugins'] = ['contents'];

        // Connect-CMS 移行形式のHTML をインポートする
        $this->importHtml($request->migration_page_id, storage_path() . '/app/migration/import/pages/' . $page_id);

        // 指示された画面に戻る。
        return $this->migrationOrder($request, $page_id);
    }

    /**
     * 表示フラグを切り替える
     */
    public function toggleDisplay($request, $page_id)
    {
        // ページID で1件取得
        $page = Page::find($page_id);

        // 指定ページがなければエラー
        if (empty($page)) {
            return view('plugins.manage.page.error', [
                "function"     => __FUNCTION__,
                "plugin_name"  => "page",
                "message"      => "指定されたページID が存在しません。",
            ]);
        }

        // 表示フラグを切り替える
        if ($page->base_display_flag) {
            $page->base_display_flag = 0;
        } else {
            $page->base_display_flag = 1;
        }
        $page->save();

        // ページ管理画面に戻る
        return redirect("/manage/page#$page_id");
    }

    /**
     * ページ権限一覧
     *
     * @return view
     * @method_title ページ権限一覧
     * @method_desc ページ権限の一覧が表示されます。<br />ページ権限に関する設定などが俯瞰できる画面です。
     * @method_detail ページ権限を編集するときは、編集ボタンをクリックしてください。
     * @spec 管理作業の分散のため、ページ毎に編集できるユーザを設定できること。
     */
    public function roleList($request, $id)
    {
        // ページデータの取得(laravel-nestedset 使用)
        $return_obj = 'flat';
        $pages = Page::defaultOrderWithDepth($return_obj);

        // ページ権限を取得してGroup オブジェクトに保持する。
        $page_roles = PageRole::join('groups', 'groups.id', '=', 'page_roles.group_id')
                ->whereNull('groups.deleted_at')
                ->where('page_roles.role_value', 1)
                ->get();

        foreach ($pages as &$page) {
            $page->page_roles = $page_roles->where('page_id', $page->id);
        }

        // グループの取得
        // ※ with('group_user')は、数万ユーザの場合、1Gでもメモリ不足になる。
        $groups = Group::orderBy('display_sequence', 'asc')->get();

        // 数万ユーザでメモリ不足になるため、GROUP_CONCAT()を使用して、グループ参加者のユーザ名を取得
        // GROUP_CONCAT()はmysql設定でgroup_concat_max_len(default=1024)の制限があり、それ以上の長さの文字列は消える。
        $group_users = User::
            select(
                'group_users.group_id',
                DB::raw("GROUP_CONCAT(users.name SEPARATOR '<br>') as group_user_names"),
                DB::raw('count(group_users.group_id) as user_count')
            )
            ->join('group_users', 'group_users.user_id', '=', 'users.id')
            ->where('group_users.deleted_at', null)
            ->groupBy('group_users.group_id')
            ->get();

        foreach ($groups as $group) {
            $group->group_user_names = optional($group_users->firstWhere('group_id', $group->id))->group_user_names;
            $group->group_user_count = optional($group_users->firstWhere('group_id', $group->id))->user_count ?? 0;
        }

        // 管理画面プラグインの戻り値の返し方
        // view 関数の第一引数に画面ファイルのパス、第二引数に画面に渡したいデータを名前付き配列で渡し、その結果のHTML。
        return view('plugins.manage.page.role_list', [
            "function"     => __FUNCTION__,
            "plugin_name"  => "page",
            "pages"        => $pages,
            "groups"       => $groups,
        ]);
    }
}
