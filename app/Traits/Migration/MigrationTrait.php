<?php

namespace App\Traits\Migration;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

use DB;
use File;
use Session;
use Storage;

use App\Models\Common\Buckets;
use App\Models\Common\Categories;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\Core\Configs;
use App\Models\Core\UsersRoles;
use App\Models\User\Blogs\Blogs;
use App\Models\User\Blogs\BlogsPosts;
use App\Models\User\Contents\Contents;
use App\Models\User\Databases\Databases;
use App\Models\User\Databases\DatabasesColumns;
use App\Models\User\Databases\DatabasesColumnsSelects;
use App\Models\User\Databases\DatabasesFrames;
use App\Models\User\Databases\DatabasesInputCols;
use App\Models\User\Databases\DatabasesInputs;
use App\Models\User\Menus\Menu;
use App\User;

use App\Models\Migration\MigrationMapping;
use App\Models\Migration\Nc2\Nc2Announcement;
use App\Models\Migration\Nc2\Nc2Block;
use App\Models\Migration\Nc2\Nc2Item;
use App\Models\Migration\Nc2\Nc2Journal;
use App\Models\Migration\Nc2\Nc2JournalBlock;
use App\Models\Migration\Nc2\Nc2JournalCategory;
use App\Models\Migration\Nc2\Nc2JournalPost;
use App\Models\Migration\Nc2\Nc2Multidatabase;
use App\Models\Migration\Nc2\Nc2MultidatabaseBlock;
use App\Models\Migration\Nc2\Nc2MultidatabaseMetadata;
use App\Models\Migration\Nc2\Nc2MultidatabaseMetadataContent;
use App\Models\Migration\Nc2\Nc2Page;
use App\Models\Migration\Nc2\Nc2Upload;
use App\Models\Migration\Nc2\Nc2User;

use App\Traits\ConnectCommonTrait;

/**
 * 移行プログラム
 * データは storage/app/migration に保持し、そこからインポートする。
 * 数値のフォルダはインポート先のページ指定で実行したもの。
 * _付のフォルダは新規ページを作るもの。
 * @付のフォルダは共通データ（uploadsなど）
 *
 * [MigrationMapping]テーブルの target_source_table の説明
 * --- エクスポート
 * nc2_pages    : source_key にNC2 のpage_id、destination_key にエクスポート用ページフォルダID（連番）：ページの階層調査用
 * --- インポート
 * connect_page : source_key にインポート用ディレクトリ、destination_key に新ページID。ページ移行時、親を探すのに使用。
 * uploads      : source_key に共通部分は新たに採番したキー、オリジナル部分はNC2 のjournal_category のcategory_id
 * categories   : source_key にNC2 のuploads_id、destination_key に新Upload のid。WYSIWYG 移行時に使用。
 * users        : source_key にNC2 のuserid、destination_key にも同じuserid。インポートの判断はUsers テーブルで行うので、これは履歴のみ。
 * blogs        : source_key にNC2 のblogs_id、destination_key に新Blog のid。新旧のつなぎ＆2回目の実行用。
 * databases    : source_key にNC2 のdatabases_id、destination_key に新Database のid。新旧のつなぎ＆2回目の実行用。
 *
 */
trait MigrationTrait
{
    use ConnectCommonTrait;

    /**
     * ファイル取得時のHTTPヘッダーからcontent_disposition
     * CURL でクロージャで取得するため、値の移送用いインスタンス変数を用意
     * ここから日本語ファイル名を取得する。
     */
    private $content_disposition = "";

    /**
     * ページ、フレームのCSV出力
     */
    private $frame_tree = "page_id,ページタイトル,固定リンク,モジュール,block_id,ブロックタイトル\n";

    /**
     * ログのパス
     * ログ自体はプログラムが途中でコケても残るように、append する。
     * ここは、ログファイルの名前を時分秒を付けて保存したいため、シングルトンでファイル名を保持するためのもの。
     */
    private $log_path = array();

    /**
     * uploads.ini
     */
    private $uploads_ini = null;

    /**
     * 移行の設定ファイル
     */
    private $migration_config = array();

    /**
     * NC2 action_name -> Connect-CMS plugin_name 変換用テーブル
     * 開発中 or 開発予定のものは 'Development' にする。
     * 廃止のものは 'Abolition' にする。
     */
    protected $plugin_name = [
        'announcement'  => 'contents',     // お知らせ
        'assignment'    => 'Development',  // レポート
        'bbs'           => 'Development',  // 掲示板
        'cabinet'       => 'Development',  // キャビネット
        'calendar'      => 'Development',  // カレンダー
        'chat'          => 'Development',  // チャット
        'circular'      => 'Development',  // 回覧板
        'counter'       => 'Development',  // カウンター
        'iframe'        => 'Development',  // iFrame
        'imagine'       => 'Abolition',    // imagine
        'journal'       => 'blogs',        // ブログ
        'language'      => 'Development',  // 言語選択
        'linklist'      => 'Development',  // リンクリスト
        'login'         => 'Development',  // ログイン
        'menu'          => 'menus',        // メニュー
        'multidatabase' => 'databases',    // データベース
        'online'        => 'Development',  // オンライン状況
        'photoalbum'    => 'Development',  // フォトアルバム
        'pm'            => 'Abolition',    // プライベートメッセージ
        'questionnaire' => 'Development',  // アンケート
        'quiz'          => 'Development',  // 小テスト
        'registration'  => 'forms',        // フォーム
        'reservation'   => 'reservations', // 施設予約
        'rss'           => 'Development',  // RSS
        'search'        => 'searchs',      // 検索
        'todo'          => 'Development',  // ToDo
        'whatsnew'      => 'whatsnews',    // 新着情報
    ];

    /**
     * NC2 日誌のデフォルトカテゴリー
     */
    protected $nc2_default_categories = [
        0   => '今日の出来事',
        1   => '連絡事項',
        2   => '報告事項',
        3   => 'ミーティング',
        4   => '本・雑誌',
        5   => 'ニュース',
        6   => '映画・テレビ',
        7   => '音楽',
        8   => 'スポーツ',
        9   => 'パソコン・インターネット',
        10  => 'ペット',
        11  => '総合学習',
        12  => 'アニメ・コミック',
    ];

    /**
     * バッチの対象（処理する対象 all or uploads など）
     */
    private $target = null;

    /**
     * バッチの対象プラグイン
     */
    private $target_plugin = null;

    /**
     * テストメソッド
     */
    private function getTestStr()
    {
        return "This is MigrationTrait test.";
    }

    /**
     * 処理対象の判定
     */
    private function isTarget($command, $target, $target_plugin = null)
    {
        // 変数説明
        // $this->target = コマンドで指定された対象
        // $target       = プログラム中で順次、処理対象か確認している、対象

        // 対象外の条件をチェックして、対象外ならfalse を返す。最後まで到達すれば対象。
        if ($this->target == 'all') {
            // 全てなので、続き
        } elseif ($this->target == $target) {
            // 対象の処理なので、続き
        } else {
            // 対象外
            return false;
        }

        // プラグインの場合は、プラグイン指定をチェック
        if ($this->target == 'plugins') {
            if ($this->target_plugin == 'all') {
                // 全てなので、続き
            } elseif ($this->target_plugin == $target_plugin) {
                // 対象の処理なので、続き
            } else {
                // 対象外
                return false;
            }
        }

        // migration_config のチェック
        if ($target == 'plugins') {
            if ($this->hasMigrationConfig($target, $command . '_plugins', $target_plugin)) {
                // 対象の処理が実行するように指定されているので、続き
            } else {
                return false;
            }
        } else {
            if ($this->getMigrationConfig($target, $command . '_' . $target)) {
                // 対象の処理が実行するように指定されているので、続き
            } else {
                return false;
            }
        }

        // 対象
        return true;
    }

    /**
     * モニターログ出力
     */
    private function putError($destination, $message, $detail = null, $nc2_block = null)
    {
        $this->putLog($destination, $message, $detail, $nc2_block, 'error');
    }

    /**
     * モニターログ出力
     */
    private function putMonitor($destination, $message, $detail = null, $nc2_block = null)
    {
        $this->putLog($destination, $message, $detail, $nc2_block, 'monitor');
    }

    /**
     * ログ出力
     * destination = 0 : 出力なし、1 : ログ、2 : 標準出力、3 : ログ＆標準出力
     */
    private function putLog($destination, $message, $detail, $nc2_block = null, $filename = 'migration')
    {
        // 最初のみ。
        // ログのファイル名の設定
        if (!array_key_exists($filename, $this->log_path)) {
            $this->log_path[$filename] = "migration/" . $filename . "_" . date('His') . '.log';

            // ログにヘッダー出力
            if (config('migration.MIGRATION_JOB_LOG')) {
                Storage::append($this->log_path[$filename], "page_id,block_id,category,message");
            }

            // 標準出力にヘッダー出力
            if (config('migration.MIGRATION_JOB_MONITOR')) {
                echo "page_id,block_id,category,message" . "\n";
            }
        }

        // メッセージ組み立て
        $log_str = "";
        if (empty($nc2_block)) {
            $log_str .= ",,";
        } else {
            $log_str .= $nc2_block->page_id . ",";
            $log_str .= $nc2_block->block_id . ",";
        }
        $log_str .= $message . ",";
        $log_str .= $detail;


        // ログ出力
        if (config('migration.MIGRATION_JOB_LOG') && ($destination == 1 || $destination == 3)) {
            Storage::append($this->log_path[$filename], $log_str);
        }

        // 標準出力
        if (config('migration.MIGRATION_JOB_MONITOR') && ($destination == 2 || $destination == 3)) {
            echo $log_str . "\n";
        }
    }

    /**
     * インポートの初期処理
     */
    private function migrationInit()
    {
        // 環境ごとの移行設定の読み込み
        if (Storage::exists('migration_config/migration_config.ini')) {
            $this->migration_config = parse_ini_file(storage_path() . '/app/migration_config/migration_config.ini', true);
        }

        // uploads のini ファイルの読み込み
        if (Storage::exists('migration/@uploads/uploads.ini')) {
            $this->uploads_ini = parse_ini_file(storage_path() . '/app/migration/@uploads/uploads.ini', true);
        }
    }

    /**
     * 移行設定の取得
     */
    private function getMigrationConfig($section, $key, $default = false)
    {
        // 指定されたセクション、キーで設定ファイルを確認して値を返す。
        if (array_key_exists($section, $this->migration_config) && array_key_exists($key, $this->migration_config[$section])) {
            return $this->migration_config[$section][$key];
        }
        return $default;
    }

    /**
     * 移行設定の取得
     */
    private function hasMigrationConfig($section, $key, $value)
    {
        // 設定の取得
        $config_value = $this->getMigrationConfig($section, $key);

        // 設定がなければ、false
        if (!$config_value) {
            return false;
        }

        // 設定が配列の場合、値があるか確認、配列ではない場合は単純な比較
        if (is_array($config_value)) {
            if (in_array($value, $config_value)) {
                return true;
            }
        } else {
            if ($config_value == $value) {  // === すると、ini のtrue が判断できない。
                return true;
            }
        }

        return false;
    }

    /**
     * インポートする際の参照コンテンツ（画像、ファイル）の追加ディレクトリ取得
     */
    private function getImportSrcDir($default = '/file/')
    {
        $cc_import_add_src_dir = $this->getMigrationConfig('pages', 'cc_import_add_src_dir', '');
        return $cc_import_add_src_dir . $default ;
    }

    /**
     * 日時の関数
     */
    private function getCCDatetime($gmt_datetime)
    {
        $gmt_datetime_ts = mktime(substr($gmt_datetime, 8, 2), substr($gmt_datetime, 10, 2), substr($gmt_datetime, 12, 2), substr($gmt_datetime, 4, 2), substr($gmt_datetime, 6, 2), substr($gmt_datetime, 0, 4));
        // 9時間足す
        $gmt_datetime_ts = $gmt_datetime_ts + (60 * 60 * 9);
        // Connect-CMS の形式で返す
        return date('Y-m-d H:i:s', $gmt_datetime_ts);
    }

    /**
     * Connect-CMS 移行形式のHTML をインポート
     */
    private function importSite($target, $target_plugin, $redo = null)
    {
        if (empty(trim($target))) {
            echo "\n";
            echo "---------------------------------------------\n";
            echo "処理の対象を指定してください。\n";
            echo "すべて処理する場合は all を指定してください。\n";
            echo "---------------------------------------------\n";
            return;
        }

        $this->target        = $target;
        $this->target_plugin = $target_plugin;

        $this->putMonitor(3, "importSite() Start.");

        // 移行の初期処理
        $this->migrationInit();

        // アップロード・ファイルの取り込み
        if ($this->isTarget('cc_import', 'uploads')) {
            $this->importUploads($redo);
        }

        // 共通カテゴリの取り込み
        if ($this->isTarget('cc_import', 'categories')) {
            $this->importCommonCategories($redo);
        }

        // ユーザデータの取り込み
        if ($this->isTarget('cc_import', 'users')) {
            $this->importUsers($redo);
        }

        // ブログの取り込み
        if ($this->isTarget('cc_import', 'plugins', 'blogs')) {
            $this->importBlogs($redo);
        }

        // データベースの取り込み
        if ($this->isTarget('cc_import', 'plugins', 'databases')) {
            $this->importDatabases($redo);
        }

        // 新ページの取り込み
        if ($this->isTarget('cc_import', 'pages')) {
            // データクリア
            if ($redo === true) {
                // トップページ以外の削除
                Page::where('permanent_link', '<>', '/')->delete();
                Frame::truncate();
                Contents::truncate();
                Buckets::where('plugin_name', 'contents')->delete();
                MigrationMapping::where('target_source_table', 'connect_page')->delete();
            }

            $paths = File::glob(storage_path() . '/app/migration/@pages/*');

            // ルームの指定（あれば後で使う）
            $cc_import_page_room_ids = $this->getMigrationConfig('pages', 'cc_import_page_room_ids');

            // 新ページのループ
            foreach ($paths as $path) {
                // ページ指定の有無
                $cc_import_where_page_dirs = $this->getMigrationConfig('pages', 'cc_import_where_page_dirs');
                if (!empty($cc_import_where_page_dirs)) {
                    if (!in_array(basename($path), $cc_import_where_page_dirs)) {
                        continue;
                    }
                }

                $this->putMonitor(3, "Page data loop.", "dir = " . basename($path));

                // ページの設定取得
                $page_ini = parse_ini_file($path. '/page.ini', true);
                //print_r($page_ini);

                // ルーム指定を探しておく。
                $room_id = null;
                if (array_key_exists('page_base', $page_ini) && array_key_exists('nc2_room_id', $page_ini['page_base'])) {
                    $room_id = $page_ini['page_base']['nc2_room_id'];
                }

                // ルーム指定があれば、指定されたルームのみ処理する。
                if (empty($cc_import_page_room_ids)) {
                    // ルーム指定なし。全データの移行
                } elseif (!empty($room_id) && !empty($cc_import_page_room_ids) && in_array($room_id, $cc_import_page_room_ids)) {
                    // ルーム指定あり。指定ルームに合致する。
                } else {
                    // ルーム指定あり。条件に合致せず。移行しない。
                    continue;
                }

                // インポートする際のURL変更（前方一致）"変更前|変更後"
                $cc_import_page_url_changes = $this->getMigrationConfig('pages', 'cc_import_page_url_changes');
                if (!empty($cc_import_page_url_changes)) {
                    foreach ($cc_import_page_url_changes as $cc_import_page_url_change) {
                        $url_change_parts = explode('|', $cc_import_page_url_change);

                        // 指定のURLの判定は、完全一致 or 後ろに'/'をつけた状態で前方一致（/test を対象にした際、/test000 は対象にせず、/test/000 は対象にしたいため）
                        if (array_key_exists('permanent_link', $page_ini['page_base']) &&
                            !empty($page_ini['page_base']['permanent_link'])) {
                            // 完全一致の場合は、/ に変換
                            if ($page_ini['page_base']['permanent_link'] == $url_change_parts[0]) {
                                $page_ini['page_base']['permanent_link'] = '/';
                            }
                            // 前方一致の場合は、指定部分を削除
                            if (strpos($page_ini['page_base']['permanent_link'], $url_change_parts[0] . '/') === 0) {
                                $page_ini['page_base']['permanent_link'] = str_replace($url_change_parts[0], '', $page_ini['page_base']['permanent_link']);
                            }
                        }
                    }
                }

                // 固定リンクでページの存在確認
                // 同じ固定リンクのページが存在した場合は、そのページを使用する。
                $page = Page::where('permanent_link', $page_ini['page_base']['permanent_link'])->first();
                // var_dump($page);

                // 対象のURL がなかった場合はページの作成
                if (empty($page)) {
                    $this->putMonitor(3, "Page create.");

                    // ページの作成
                    $page = Page::create(['page_name'         => $page_ini['page_base']['page_name'],
                                          'permanent_link'    => $page_ini['page_base']['permanent_link'],
                                          'base_display_flag' => $page_ini['page_base']['base_display_flag'],
                                        ]);

                    // 親ページの指定があるか
                    if (array_key_exists('page_base', $page_ini) && array_key_exists('parent_page_dir', $page_ini['page_base'])) {
                        // マッピングテーブルから、親ページのページIDを取得
                        $parent_mapping = MigrationMapping::where('target_source_table', 'connect_page')->where('source_key', $page_ini['page_base']['parent_page_dir'])->first();

                        // 親ページの取得
                        if (!empty($parent_mapping)) {
                            $parent_page = Page::find($parent_mapping->destination_key);
                            $parent_page->appendNode($page);
                        }
                    }

                    // マッピングテーブルの追加
                    $mapping = MigrationMapping::updateOrCreate(
                        ['target_source_table' => 'connect_page',
                        'source_key' => ltrim(basename($path), '_')],
                        ['target_source_table'  => 'connect_page',
                        'source_key'           => ltrim(basename($path), '_'),
                        'destination_key'      => $page->id]
                    );
                } else {
                    $this->putMonitor(3, "Page found. Use existing page.");
                }

                // ページの中身の作成
                $this->importHtmlImpl($page, $path);
            }
        }

        // シーダーの呼び出し
        $this->putMonitor(3, "seeder import Start.");
        $this->importSeeder();
    }

    /**
     * Connect-CMS 移行形式のアップロード・ファイルをインポート
     */
    private function importUploads($redo)
    {
        $this->putMonitor(3, "uploads import Start.");

        // データクリア
        if ($redo === true) {
            // アップロードテーブルのtruncate とmigration_mappings のuploads の削除、アップロードファイルの削除
            Uploads::truncate();
            MigrationMapping::where('target_source_table', 'uploads')->delete();
            Storage::deleteDirectory(config('connect.directory_base'));
        }

        // アップロード・ファイル定義の取り込み
        $uploads_ini = parse_ini_file(storage_path() . '/app/migration/@uploads/uploads.ini', true);

        // ルームの指定（あれば後で使う）
        $cc_import_uploads_room_ids = $this->getMigrationConfig('uploads', 'cc_import_uploads_room_ids');

        // アップロード・ファイルのループ
        if (array_key_exists('uploads', $uploads_ini) && array_key_exists('upload', $uploads_ini['uploads'])) {
            foreach ($uploads_ini['uploads']['upload'] as $upload_key => $upload_item) {
                // ルーム指定を探しておく。
                $room_id = null;
                if (array_key_exists('nc2_room_id', $uploads_ini[$upload_key])) {
                    $room_id = $uploads_ini[$upload_key]['nc2_room_id'];
                }

                // ルーム指定があれば、指定されたルームのみ処理する。
                if (empty($cc_import_uploads_room_ids)) {
                    // ルーム指定なし。全データの移行
                } elseif (!empty($room_id) && !empty($cc_import_uploads_room_ids) && in_array($room_id, $cc_import_uploads_room_ids)) {
                    // ルーム指定あり。指定ルームに合致する。
                } else {
                    // ルーム指定あり。条件に合致せず。移行しない。
                    continue;
                }

                // マッピングテーブルの取得
                $mapping = MigrationMapping::where('target_source_table', 'uploads')->where('source_key', $upload_key)->first();

                // マッピングテーブルの確認
                if (empty($mapping)) {
                    // マッピングテーブルがなければ、Uploads テーブルとマッピングテーブルを追加
                    $upload = Uploads::create([
                        'client_original_name' => $uploads_ini[$upload_key]['client_original_name'],
                        'mimetype'             => $uploads_ini[$upload_key]['mimetype'],
                        'extension'            => $uploads_ini[$upload_key]['extension'],
                        'size'                 => $uploads_ini[$upload_key]['size'],
                        'plugin_name'          => $uploads_ini[$upload_key]['plugin_name'],
                        'page_id'              => $uploads_ini[$upload_key]['page_id'],
                        'temporary_flag'       => 0,
                    ]);

                    // マッピングテーブルの追加
                    $mapping = MigrationMapping::create([
                        'target_source_table'  => 'uploads',
                        'source_key'           => $upload_key,
                        'destination_key'      => $upload->id,
                    ]);
                } else {
                    // マッピングテーブルがあれば、Uploads テーブルを更新
                    $upload = Uploads::find($mapping->destination_key);
                    if (empty($upload)) {
                        $this->putMonitor(1, "No Mapping target = uploads", "destination_key = " . $mapping->destination_key);
                    } else {
                        $upload->client_original_name = $uploads_ini[$upload_key]['client_original_name'];
                        $upload->mimetype             = $uploads_ini[$upload_key]['mimetype'];
                        $upload->extension            = $uploads_ini[$upload_key]['extension'];
                        $upload->size                 = $uploads_ini[$upload_key]['size'];
                        $upload->plugin_name          = $uploads_ini[$upload_key]['plugin_name'];
                        $upload->page_id              = $uploads_ini[$upload_key]['page_id'];
                        $upload->temporary_flag       = 0;
                        $upload->save();
                    }
                }

                // ファイルのコピー
                $source_file_path = 'migration/@uploads/' . $upload_item;
                $destination_file_path = $this->getDirectory($upload->id) . '/' . $upload->id . '.' . $uploads_ini[$upload_key]['extension'];
                if (Storage::exists($source_file_path)) {
                    if (Storage::exists($destination_file_path)) {
                        Storage::delete($destination_file_path);
                    }
                    Storage::copy($source_file_path, $destination_file_path);
                }
            }
        }
    }

    /**
     * Connect-CMS 移行形式のカテゴリをインポート
     */
    private function importCommonCategories($redo)
    {
        $this->putMonitor(3, "Categories import Start.");

        // データクリア
        if ($redo === true) {
            // カテゴリテーブルのtruncate とmigration_mappings のcategories の削除
            Categories::truncate();
            MigrationMapping::where('target_source_table', 'categories')->delete();
        }

        // 共通カテゴリのファイル読み込み
        $source_file_path = 'migration/@categories/categories.ini';
        if (Storage::exists($source_file_path)) {
            $categories_ini = parse_ini_file(storage_path() . '/app/' . $source_file_path, true);
            if (array_key_exists('categories', $categories_ini) && array_key_exists('categories', $categories_ini['categories'])) {
                $this->importCategories($categories_ini['categories']['categories']);
            }
        }
    }

    /**
     * Connect-CMS 移行形式のカテゴリをインポート
     */
    private function importCategories($categories, $target = null, $plugin_id = null)
    {
        $display_sequence = 0;
        foreach ($categories as $category_id => $category_name) {
            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'categories')->where('source_key', $category_id)->first();

            // マッピングテーブルの確認
            if (empty($mapping)) {
                // マッピングテーブルがなければ、Categories テーブルとマッピングテーブルを追加

                $display_sequence++;
                $category = Categories::create([
                    'classname'        => 'category_default',
                    'category'         => $category_name,
                    'color'            => '#ffffff',
                    'background_color' => '#606060',
                    'target'           => $target,
                    'plugin_id'        => $plugin_id,
                    'display_sequence' => $display_sequence
                ]);

                // マッピングテーブルの追加
                $mapping = MigrationMapping::create([
                    'target_source_table'  => 'categories',
                    'source_key'           => $category_id,
                    'destination_key'      => $category->id,
                ]);
            } else {
                // マッピングテーブルがあれば、Categories テーブルを更新
                $category = Categories::find($mapping->destination_key);
                if (empty($category)) {
                    $this->putMonitor(1, "No Mapping target = category", "destination_key = " . $mapping->destination_key);
                } else {
                    $category->classname        = 'category_default';
                    $category->category         = $category_name;
                    $category->color            = '#ffffff';
                    $category->background_color = '#606060';
                    $category->target           = $target;
                    $category->plugin_id        = $plugin_id;
                    $category->display_sequence = $display_sequence;
                    $category->save();
                }
            }
        }

        // 登録したカテゴリをCollection で返す
        $categories = Categories::where('target', $target)->where('plugin_id', $plugin_id)->orderBy('id', 'asc')->get();
        return $categories;
    }

    /**
     * Connect-CMS 移行形式のユーザをインポート
     */
    private function importUsers($redo)
    {
        $this->putMonitor(3, "Users import Start.");

        // データクリア
        if ($redo === true) {
            // 最初のユーザ以外の削除、migration_mappings のusers の削除
            $first_user = User::orderBy('id', 'asc')->first();
            UsersRoles::where('users_id', '<>', $first_user->id)->delete();
            User::where('id', '<>', $first_user->id)->delete();
            MigrationMapping::where('target_source_table', 'users')->delete();
        }

        // ユーザ定義・ファイル定義の取り込み
        $users_ini = parse_ini_file(storage_path() . '/app/migration/@users/users.ini', true);

        // ユーザの指定（あれば後で使う）
        $cc_import_login_users = $this->getMigrationConfig('users', 'cc_import_login_users');

        // ユーザ定義のループ
        if (array_key_exists('users', $users_ini) && array_key_exists('user', $users_ini['users'])) {
            foreach ($users_ini['users']['user'] as $user_key => $username) {
                // ユーザ指定を探しておく。
                $userid = null;
                if (array_key_exists('userid', $users_ini[$user_key])) {
                    $userid = $users_ini[$user_key]['userid'];
                }

                // ユーザ指定があれば、指定されたユーザのみ処理する。
                if (empty($cc_import_login_users)) {
                    // ユーザ指定なし。全ユーザの移行
                } elseif (!empty($userid) && !empty($cc_import_login_users) && in_array($userid, $cc_import_login_users)) {
                    // ユーザ指定あり。指定ユーザに合致する。
                } else {
                    // ユーザ指定あり。条件に合致せず。移行しない。
                    continue;
                }

                // ユーザ情報
                $user_item = null;
                if (array_key_exists($user_key, $users_ini)) {
                    $user_item = $users_ini[$user_key];
                } else {
                    $this->putError(3, 'ユーザデータの詳細なし', "user_key = " . $user_key . " name = " . $username);
                    continue;
                }

                // ユーザテーブルの取得
                $user = User::where('userid', $user_item['userid'])->first();

                // 移行のテスト用（メールアドレスに半角@が含まれていたら、全角＠に変更する。（テスト中の誤送信防止用））
                $email = $user_item['email'];
                if ($this->getMigrationConfig('users', 'cc_import_user_test_mail')) {
                    $email = str_replace('@', '＠', $user_item['email']);
                }
                // Duplicate entry 制約があるので、空文字ならnull に変換
                if ($email == "") {
                    $email = null;
                }

                // パスワードのチェック（id とパスワードが同じなら警告）
                if (md5($user_item['userid']) == $user_item['password']) {
                    $this->putError(3, 'ログインIDとパスワードが同じ。', "userid = " . $user_item['userid'] . " name = " . $user_item['name']);
                }

                // ユーザがあるかの確認
                if (empty($user)) {
                    // ユーザテーブルがなければ、追加
                    $user = User::create([
                        'name'     => $user_item['name'],
                        'email'    => $email,
                        'userid'   => $user_item['userid'],
                        'password' => Hash::make($user_item['password']),
                    ]);

                    // マッピングテーブルの追加
                    $mapping = MigrationMapping::create([
                        'target_source_table'  => 'users',
                        'source_key'           => $user_item['userid'],
                        'destination_key'      => $user_item['userid'],
                    ]);
                } else {
                    // ユーザテーブルがあれば、Users テーブルを更新
                    $user->name      = $user_item['name'];
                    $user->email     = $email;
                    $user->userid    = $user_item['userid'];
                    $user->password  = Hash::make($user_item['password']);
                    $user->save();
                }
                // ユーザー権限をインポートする。
                $this->importUsersRoles($user, 'base', $user_item);
                $this->importUsersRoles($user, 'manage', $user_item);
            }
        }
    }

    /**
     * Connect-CMS 移行形式のユーザ権限をインポート
     */
    private function importUsersRoles($user, $target, $user_item)
    {

        // 権限の比較と更新
        $users_roles_records = UsersRoles::select('role_name')->where('users_id', $user->id)->where('target', $target)->get();
        $users_roles_beings = $users_roles_records->pluck('role_name')->toArray();

        // 対象のターゲットの存在確認
        if (array_key_exists('users_roles_'. $target, $user_item)) {
            $users_roles_news = explode('|', $user_item['users_roles_'. $target]);
        } else {
            $users_roles_news = array();
        }

        // 既存にしかないものは削除
        foreach (array_diff($users_roles_beings, $users_roles_news) as $delete_role) {
            UsersRoles::where('users_id', $user->id)->where('target', $target)->where('role_name', $delete_role)->delete();
        }

        // インポートファイルにしかないものは追加
        foreach (array_diff($users_roles_news, $users_roles_beings) as $insert_role) {
            UsersRoles::create([
                'users_id'   => $user->id,
                'target'     => $target,
                'role_name'  => $insert_role,
                'role_value' => 1,
            ]);
        }
        return;
    }

    /**
     * Connect-CMS 移行形式のブログをインポート
     */
    private function importBlogs($redo)
    {
        $this->putMonitor(3, "Blogs import Start.");

        // データクリア
        if ($redo === true) {
            // blogs、blogs_posts のtruncate
            Blogs::truncate();
            BlogsPosts::truncate();
            MigrationMapping::where('target_source_table', 'blogs')->delete();
        }

        // 共通カテゴリの取得
        $common_categories = Categories::whereNull('target')->whereNull('plugin_id')->orderBy('id', 'asc')->get();

        // ブログ定義の取り込み
        $blogs_ini_paths = File::glob(storage_path() . '/app/migration/@blogs/blog_*.ini');

        // ルームの指定（あれば後で使う）
        $cc_import_blogs_room_ids = $this->getMigrationConfig('blogs', 'cc_import_blogs_room_ids');

        // ブログ定義のループ
        foreach ($blogs_ini_paths as $blogs_ini_path) {
            // ini_file の解析
            $blog_ini = parse_ini_file($blogs_ini_path, true);

            // ルーム指定を探しておく。
            $room_id = null;
            if (array_key_exists('nc2_info', $blog_ini) && array_key_exists('room_id', $blog_ini['nc2_info'])) {
                $room_id = $blog_ini['nc2_info']['room_id'];
            }

            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($cc_import_blogs_room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_id) && !empty($cc_import_blogs_room_ids) && in_array($room_id, $cc_import_blogs_room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // nc2 の journal_id
            $nc2_journal_id = 0;
            if (array_key_exists('nc2_info', $blog_ini) && array_key_exists('journal_id', $blog_ini['nc2_info'])) {
                $nc2_journal_id = $blog_ini['nc2_info']['journal_id'];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'blogs')->where('source_key', $nc2_journal_id)->first();

            // マッピングテーブルを確認して、追加か更新の処理を分岐
            if (empty($mapping)) {
                // マッピングテーブルがなければ、Buckets テーブルと Blogs テーブル、マッピングテーブルを追加
                $blog_name = '無題';
                if (array_key_exists('blog_base', $blog_ini) && array_key_exists('blog_name', $blog_ini['blog_base'])) {
                    $blog_name = $blog_ini['blog_base']['blog_name'];
                }
                $bucket = Buckets::create(['bucket_name' => $blog_name, 'plugin_name' => 'blogs']);

                $view_count = 10;
                if (array_key_exists('blog_base', $blog_ini) && array_key_exists('view_count', $blog_ini['blog_base'])) {
                    $view_count = $blog_ini['blog_base']['view_count'];
                    // view_count が 0 を含む空の場合は、初期値にする。（NC2 で0 で全件表示されているものがあるので、その対応）
                    if (empty($view_count)) {
                        $view_count = 10;
                    }
                }
                $blog = Blogs::create(['bucket_id' => $bucket->id, 'blog_name' => $blog_name, 'view_count' => $view_count]);

                // マッピングテーブルの追加
                $mapping = MigrationMapping::create([
                    'target_source_table'  => 'blogs',
                    'source_key'           => $nc2_journal_id,
                    'destination_key'      => $blog->id,
                ]);

                // ブログ固有カテゴリ追加
                $blog_categories = collect();
                if (array_key_exists('categories', $blog_ini) && array_key_exists('original_categories', $blog_ini['categories'])) {
                    $blog_categories = $this->importCategories($blog_ini['categories']['original_categories'], 'blogs', $blog->id);
                }

                // Blogs の記事を取得（TSV）
                $blog_tsv_filename = str_replace('ini', 'tsv', basename($blogs_ini_path));
                if (Storage::exists('migration/@blogs/' . $blog_tsv_filename)) {
                    // TSV ファイル取得（1つのTSV で1つのブログ丸ごと）
                    $blog_tsv = Storage::get('migration/@blogs/' . $blog_tsv_filename);
                    // POST が無いものは対象外
                    if (empty($blog_tsv)) {
                        continue;
                    }
                    // 改行で記事毎に分割
                    $blog_tsv_lines = explode("\n", $blog_tsv);
                    foreach ($blog_tsv_lines as $blog_tsv_line) {
                        // タブで項目に分割
                        $blog_tsv_cols = explode("\t", $blog_tsv_line);

                        // 投稿日時の変換(NC2 の投稿日時はGMT のため、9時間プラスする) NC2=20151020122600
                        $posted_at_ts = mktime(substr($blog_tsv_cols[0], 8, 2), substr($blog_tsv_cols[0], 10, 2), substr($blog_tsv_cols[0], 12, 2), substr($blog_tsv_cols[0], 4, 2), substr($blog_tsv_cols[0], 6, 2), substr($blog_tsv_cols[0], 0, 4));
                        $posted_at = date('Y-m-d H:i:s', $posted_at_ts + (60 * 60 * 9));

                        // 記事のカテゴリID
                        // 共通カテゴリに同じ文言があれば、共通カテゴリを使用。
                        // 記事のカテゴリID = original_categories にキーがあれば、original_categories の文言でブログ単位のカテゴリを探してID 特定。
                        $categories_id = null;
                        if ($common_categories->firstWhere('category', $blog_tsv_cols[1])) {
                            $categories_id = $common_categories->firstWhere('category', $blog_tsv_cols[1])->id;
                        }
                        if (empty($categories_id) && $blog_categories->firstWhere('category', $blog_tsv_cols[1])) {
                            $categories_id = $blog_categories->firstWhere('category', $blog_tsv_cols[1])->id;
                        }

                        // 本文
                        $post_text = $this->changeWYSIWYG($blog_tsv_cols[5]);
                        // 本文2
                        $post_text2 = $this->changeWYSIWYG($blog_tsv_cols[6]);

                        // ブログ記事テーブル追加
                        $blogs_posts = BlogsPosts::create(['blogs_id' => $blog->id, 'post_title' => $blog_tsv_cols[4], 'post_text' => $post_text, 'post_text2' => $post_text2, 'categories_id' => $categories_id, 'important' => null, 'status' => 0, 'posted_at' => $posted_at]);

                        // contents_id を初回はid と同じものを入れて、更新
                        $blogs_posts->contents_id = $blogs_posts->id;
                        $blogs_posts->save();
                    }
                }
            } else {
                // マッピングテーブルがあれば、Blogs テーブル更新
                // *** あとで。
            }
        }
    }

    /**
     * Connect-CMS 移行形式のデータベースをインポート
     */
    private function importDatabases($redo)
    {
        $this->putMonitor(3, "Databases import Start.");

        // データクリア
        if ($redo === true) {
            // databases、databases_columns、databases_columns_selects、databases_inputs、databases_input_cols のtruncate
            Databases::truncate();
            DatabasesColumns::truncate();
            DatabasesColumnsSelects::truncate();
            DatabasesInputs::truncate();
            DatabasesInputCols::truncate();
            MigrationMapping::where('target_source_table', 'databases')->delete();
        }

        // データベース定義の取り込み
        $databases_ini_paths = File::glob(storage_path() . '/app/migration/@databases/database_*.ini');

        // ルームの指定（あれば後で使う）
        $cc_import_databases_room_ids = $this->getMigrationConfig('databases', 'cc_import_databases_room_ids');

        // データベース定義のループ
        foreach ($databases_ini_paths as $databases_ini_path) {
            // ini_file の解析
            $databases_ini = parse_ini_file($databases_ini_path, true);

            // ルーム指定を探しておく。
            $room_id = null;
            if (array_key_exists('nc2_info', $databases_ini) && array_key_exists('room_id', $databases_ini['nc2_info'])) {
                $room_id = $databases_ini['nc2_info']['room_id'];
            }

            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($cc_import_databases_room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_id) && !empty($cc_import_databases_room_ids) && in_array($room_id, $cc_import_databases_room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // データベース指定の有無
            $cc_import_where_database_ids = $this->getMigrationConfig('databases', 'cc_import_where_database_ids');
            if (!empty($cc_import_where_database_ids)) {
                if (!in_array($databases_ini['nc2_info']['multidatabase_id'], $cc_import_where_database_ids)) {
                    continue;
                }
            }

            // nc2 の multidatabase_id
            $nc2_multidatabase_id = 0;
            if (array_key_exists('nc2_info', $databases_ini) && array_key_exists('multidatabase_id', $databases_ini['nc2_info'])) {
                $nc2_multidatabase_id = $databases_ini['nc2_info']['multidatabase_id'];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'databases')->where('source_key', $nc2_multidatabase_id)->first();

            // マッピングテーブルを確認して、追加か更新の処理を分岐
            if (empty($mapping)) {
                // マッピングテーブルがなければ、Buckets テーブルと Database テーブル、マッピングテーブルを追加
                $database_name = '無題';
                if (array_key_exists('database_base', $databases_ini) && array_key_exists('database_name', $databases_ini['database_base'])) {
                    $database_name = $databases_ini['database_base']['database_name'];
                }
                $bucket = Buckets::create(['bucket_name' => $database_name, 'plugin_name' => 'databases']);

                $database = Databases::create(['bucket_id' => $bucket->id, 'databases_name' => $database_name, 'data_save_flag' => 1]);

                // マッピングテーブルの追加
                $mapping = MigrationMapping::create([
                    'target_source_table'  => 'databases',
                    'source_key'           => $nc2_multidatabase_id,
                    'destination_key'      => $database->id,
                ]);
            } else {
                // マッピングテーブルがあれば、一度該当のDatabase 関係データを削除する。
                // bucket, databases はそのまま使う。databases_columns, databases_inputs, databases_input_cols, databases_columns_selects は削除
                $database = Databases::find($mapping->destination_key);

                // DatabasesInputCols, DatabasesColumnsSelects 削除。カラムデータを呼び出して、カラムのID で削除
                $databases_columns = DatabasesColumns::where('databases_id', $database->id)->get();
                foreach ($databases_columns as $databases_column) {
                    DatabasesColumnsSelects::where('databases_columns_id', $databases_column->id)->delete();
                    DatabasesInputCols::where('databases_columns_id', $databases_column->id)->delete();
                }

                // DatabasesColumns と DatabasesInputs はデータベースのID で削除
                DatabasesColumns::where('databases_id', $database->id)->delete();
                DatabasesInputs::where('databases_id', $database->id)->delete();
            }

            // columns のid を配列に保持。後で入力データを移行する際の column_id に使うため。
            $column_ids = array();
            $create_columns = array();

            if (array_key_exists('databases_columns', $databases_ini) && array_key_exists('databases_column', $databases_ini['databases_columns'])) {
                foreach ($databases_ini['databases_columns']['databases_column'] as $column_id => $column_name) {
                    $databases_column = DatabasesColumns::create([
                        'databases_id'     => $database->id,
                        'column_type'      => $databases_ini[$column_id]['column_type'],
                        'column_name'      => $databases_ini[$column_id]['column_name'],
                        'required'         => $databases_ini[$column_id]['required'],
                        'frame_col'        => 0,
                        'list_hide_flag'   => $databases_ini[$column_id]['list_hide_flag'],
                        'detail_hide_flag' => $databases_ini[$column_id]['detail_hide_flag'],
                        'sort_flag'        => $databases_ini[$column_id]['sort_flag'],
                        'search_flag'      => $databases_ini[$column_id]['search_flag'],
                        'select_flag'      => $databases_ini[$column_id]['select_flag'],
                        'display_sequence' => $databases_ini[$column_id]['display_sequence'],
                        'row_group'        => empty($databases_ini[$column_id]['row_group']) ? null : $databases_ini[$column_id]['row_group'],
                        'column_group'     => empty($databases_ini[$column_id]['column_group']) ? null : $databases_ini[$column_id]['column_group'],
                    ]);
                    $column_ids[] = $databases_column->id;
                    $create_columns[] = $databases_column;

                    // 選択型項目で選択肢がある場合、DatabasesColumnsSelects テーブルを追加
                    $columns_selects = $databases_ini[$column_id]['columns_selects'];
                    if (!empty($columns_selects)) {
                        $columns_selects = explode('|', $columns_selects);
                        $display_sequence = 1;
                        foreach ($columns_selects as $columns_select) {
                            DatabasesColumnsSelects::create([
                                'databases_columns_id' => $databases_column->id,
                                'value'                => $columns_select,
                                'display_sequence'     => $display_sequence,
                            ]);
                            $display_sequence++;
                        }
                    }
                }
            }

            // データベースの情報取得

            // Database のデータを取得（TSV）
            $database_tsv_filename = str_replace('ini', 'tsv', basename($databases_ini_path));

            if (Storage::exists('migration/@databases/' . $database_tsv_filename)) {
                // TSV ファイル取得（1つのTSV で1つのデータベース丸ごと）
                $database_tsv = Storage::get('migration/@databases/' . $database_tsv_filename);
                // POST が無いものは対象外
                if (empty($database_tsv)) {
                    continue;
                }

                // 行ループで使用する各種変数
                $header_skip = true;  // ヘッダースキップフラグ（1行目はカラム名の行）
                $created_at_idx = 0;  // created_at のカラムインデックス（0 の場合は無効）
                $created_at = '';     // created_at の内容（日時）
                $updated_at_idx = 0;  // updated_at のカラムインデックス（0 の場合は無効）
                $updated_at = '';     // updated_at の内容（日時）

                // 改行で記事毎に分割（行の処理）
                $database_tsv_lines = explode("\n", $database_tsv);
                foreach ($database_tsv_lines as $database_tsv_line) {
                    // 1行目はカラム名の行のため、対象外
                    if ($header_skip) {
                        $header_skip = false;

                        // created_atを探す。タブで項目に分割
                        $loop_idx = 0;
                        $database_tsv_cols = explode("\t", trim($database_tsv_line, "\n\r"));

                        foreach ($database_tsv_cols as $database_tsv_col) {
                            if ($database_tsv_col == 'created_at') {
                                $created_at_idx = $loop_idx;
                            } elseif ($database_tsv_col == 'updated_at') {
                                $updated_at_idx = $loop_idx;
                            }
                            $loop_idx++;
                        }
                        continue;
                    }

                    // 行データをタブで項目に分割
                    $database_tsv_cols = explode("\t", trim($database_tsv_line, "\n\r"));

                    // created_at、updated_at の設定
                    // created_at、updated_at のカラムがない or データが空の場合は、処理時間を入れる。
                    if ($created_at_idx != 0 && array_key_exists($created_at_idx, $database_tsv_cols) && !empty($database_tsv_cols[$created_at_idx])) {
                        $created_at = $database_tsv_cols[$created_at_idx];
if (!\DateTime::createFromFormat('Y-m-d H:i:s', $created_at)) {
    $this->putError(1, '日付エラー', "created_at = " . $created_at);
    $created_at = date('Y-m-d H:i:s');
}
                    } else {
                        $created_at = date('Y-m-d H:i:s');
                    }
                    if ($updated_at_idx != 0 && array_key_exists($updated_at_idx, $database_tsv_cols) && !empty($database_tsv_cols[$updated_at_idx])) {
                        $updated_at = $database_tsv_cols[$updated_at_idx];
if (!\DateTime::createFromFormat('Y-m-d H:i:s', $updated_at)) {
    $this->putError(1, '日付エラー', "updated_at = " . $updated_at);
    $updated_at = date('Y-m-d H:i:s');
}
                    } else {
                        $updated_at = date('Y-m-d H:i:s');
                    }

                    // 行データの追加
                    $databases_input = DatabasesInputs::create(['databases_id' => $database->id, 'created_at' => $created_at, 'updated_at' => $updated_at]);

                    $databases_columns_id_idx = 0; // 処理カラムのloop index

                    // データベースのバルクINSERT対応
                    $bulks = array();

                    foreach ($database_tsv_cols as $database_tsv_col) {
                        // created_at、updated_at はカラムとしては読み飛ばす
                        if ($databases_columns_id_idx == $created_at_idx || $databases_columns_id_idx == $updated_at_idx) {
                            continue;
                        }

                        // エラーの内容は再度、チェックすること。
                        if (array_key_exists($databases_columns_id_idx, $column_ids)) {
                            // 項目の型により変換するもの
                            if ($create_columns[$databases_columns_id_idx]->column_type == 'textarea') {
                                // 複数行テキスト
                                $database_tsv_col = str_replace('<br />', "\n", $database_tsv_col);
                            } elseif ($create_columns[$databases_columns_id_idx]->column_type == 'wysiwyg') {
                                // WYSIWYG
                                $database_tsv_col = $this->changeWYSIWYG($database_tsv_col);
                            }

                            // セルデータの追加
                            $bulks[] = ['databases_inputs_id'  => $databases_input->id,
                                'databases_columns_id' => $column_ids[$databases_columns_id_idx],
                                'value'                => $database_tsv_col,
                                'created_at'           => $created_at,
                                'updated_at'           => $updated_at];
                            /*
                            $databases_input_cols = DatabasesInputCols::create([
                                'databases_inputs_id'  => $databases_input->id,
                                'databases_columns_id' => $column_ids[$databases_columns_id_idx],
                                'value'                => $database_tsv_col,
                                'created_at'           => $created_at,
                                'updated_at'           => $updated_at,
                            ]);
                            */
                        } else {
                            $this->putError(3, 'データベース詳細インポートエラー', "databases_columns_id_idx = " . $databases_columns_id_idx);
                        }
                        $databases_columns_id_idx++;
                    }
                    // バルクINSERT
                    DB::table('databases_input_cols')->insert($bulks);
                }
            }
        }
    }

    /**
     * シーダーの呼び出し
     */
    private function importSeeder()
    {
        // とりあえずテスト用
        $top_page = Page::where('permanent_link', '/')->first();
        if (!empty($top_page)) {
            $frame = Frame::create([
                'page_id'          => $top_page->id,
                'area_id'          => 1,
                'frame_title'      => null,
                'frame_design'     => 'none',
                'plugin_name'      => 'menus',
                'frame_col'        => 0,
                'template'         => 'opencurrenttree',
                'display_sequence' => 0,
            ]);
            Menu::create([
                'frame_id'          => $frame->id,
                'select_flag'       => 0,
                'page_ids'          => '',
                'folder_close_font' => 0,
                'folder_open_font'  => 0,
                'indent_font'       => 0,
            ]);
        }
    }

    /**
     * WYSIWYG 内の画像パスをエクスポート形式からConnect-CMS コンテンツへ変換
     */
    private function changeWYSIWYG($content)
    {
        // 画像を探す
        $images = $this->getContentImage($content);

        // 添付ファイルを探す
        $anchors = $this->getContentAnchor($content);

        // 画像、添付ファイルをマージ（変換が必要なパスしてマージ）
        $change_list = array();
        if (is_array($images)) {
            $change_list = $change_list + $images;
        }
        if (is_array($anchors)) {
            $change_list = $change_list + $anchors;
        }

        // 対象がなければ戻る
        if (empty($change_list)) {
            return $content;
        }

        // アップロードファイルのパスへ変換
        foreach ($change_list as $image_path) {
            if (strpos($image_path, '../../@uploads') === 0) {
                $img_filename = str_replace('../../@uploads/', '', $image_path);
                $nc2_upload_id = array_search($img_filename, $this->uploads_ini['uploads']['upload']);
                $upload_mapping = MigrationMapping::where('target_source_table', 'uploads')->where('source_key', $nc2_upload_id)->first();
                if (!empty($upload_mapping)) {
                    $content = str_replace($image_path, $this->getImportSrcDir() . $upload_mapping->destination_key, $content);
                } else {
                    // $this->putError(1, 'image path not found mapping', "コンテンツ中のアップロード画像のパスがマッピングテーブルに見つからない。nc2_upload_id = " . $nc2_upload_id);
                }
            }
        }
        return $content;
    }

    /**
     * Connect-CMS 移行形式のHTML をインポート
     */
    private function importHtml($page_id, $dir = null)
    {
        $page = Page::find($page_id);
        $this->importHtmlImpl($page);
    }

    /**
     * Connect-CMS 移行形式のHTML をインポート
     */
    private function importHtmlImpl($page, $dir = null)
    {
        /*
        HTML からインポート（ページ指定）

        "frame_*.ini" でFile::glob する。
        Buckets 登録
        Frames 登録
        [image_names] の画像を登録（連番ディレクトリ注意）
            upload_id でhtml の内容を編集
        Contents 登録
        */

        // インポート元のディレクトリが指定されていない場合は、ページid と同じ名前のディレクトリがあるとする
        if (empty($dir)) {
            $dir = storage_path() . '/app/migration/' . $page->id;
        }

        // フレーム単位のini ファイルの取得
        $frame_ini_paths = File::glob($dir . '/frame_*.ini');

        // フレームのループ
        $display_sequence = 0;
        foreach ($frame_ini_paths as $frame_ini_path) {
            // echo $frame_ini_path . "\n";

            $display_sequence++;

            // フレーム毎のini_file の解析
            $frame_ini = parse_ini_file($frame_ini_path, true);
            //print_r($ini_array);

            // プラグイン毎の登録処理へ
            $this->importPlugin($page, dirname($frame_ini_path), $frame_ini, $display_sequence);
        }
        // echo $page_id . ' の移行が完了';
    }

    /**
     * プラグイン毎の登録処理
     */
    private function importPlugin($page, $page_dir, $frame_ini, $display_sequence)
    {
        // プラグインが指定されていない場合は戻る
        if (!array_key_exists('frame_base', $frame_ini) || !array_key_exists('plugin_name', $frame_ini['frame_base'])) {
            return;
        }

        // プラグイン名
        $plugin_name = $frame_ini['frame_base']['plugin_name'];

        // インポートするプラグインに指定されているか確認して、対象とする。
        if (!$this->hasMigrationConfig('frames', 'import_frame_plugins', $plugin_name)) {
            return;
        }

        // プラグイン振り分け
        if ($plugin_name == 'contents') {
            // 固定記事（お知らせ）
            $this->importPluginContents($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'menus') {
            // メニュー
            $this->importPluginMenus($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'blogs') {
            // ブログ
            $this->importPluginBlogs($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'databases') {
            // データベース
            $this->importPluginDatabases($page, $page_dir, $frame_ini, $display_sequence);
        }
    }

    /**
     * メニュープラグインの登録処理
     */
    private function importPluginMenus($page, $page_dir, $frame_ini, $display_sequence)
    {
        // メニューオプション（エリア）の確認
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('area_id', $frame_ini['frame_base'])) {
            // フレームの指定と＆オプションの位置指定が両方OKなら、インポートする。
            if (($frame_ini['frame_base']['area_id'] == 0 && $this->hasMigrationConfig('menus', 'import_menu_area', 'header')) ||
                ($frame_ini['frame_base']['area_id'] == 1 && $this->hasMigrationConfig('menus', 'import_menu_area', 'left')) ||
                ($frame_ini['frame_base']['area_id'] == 2 && $this->hasMigrationConfig('menus', 'import_menu_area', 'main')) ||
                ($frame_ini['frame_base']['area_id'] == 3 && $this->hasMigrationConfig('menus', 'import_menu_area', 'right')) ||
                ($frame_ini['frame_base']['area_id'] == 4 && $this->hasMigrationConfig('menus', 'import_menu_area', 'footer'))) {
            } else {
                return;
            }
        }

        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence);

        // Menus 登録
        $menus = Menu::create(['frame_id' => $frame->id, 'page_ids' => '']);
    }

    /**
     * ブログプラグインの登録処理
     */
    private function importPluginBlogs($page, $page_dir, $frame_ini, $display_sequence)
    {
        // 変数定義
        $blog_id = null;
        $blog_ini = null;
        $journal_id = null;
        $migration_mappings = null;
        $blogs = null;
        $bucket = null;

        // エクスポートファイルの blog_id 取得（エクスポート時の連番）
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('blog_id', $frame_ini['frame_base'])) {
            $blog_id = $frame_ini['frame_base']['blog_id'];
        }
        // ブログの情報取得
        if (!empty($blog_id) && Storage::exists('migration/@blogs/blog_' . $blog_id . '.ini')) {
            $blog_ini = parse_ini_file(storage_path() . '/app/migration/@blogs/blog_' . $blog_id . '.ini', true);
        }
        // NC2 のjournal_id
        if (!empty($blog_ini) && array_key_exists('nc2_info', $blog_ini) && array_key_exists('journal_id', $blog_ini['nc2_info'])) {
            $journal_id = $blog_ini['nc2_info']['journal_id'];
        }
        // NC2 のjournal_id でマップ確認
        if (!empty($blog_ini) && array_key_exists('nc2_info', $blog_ini) && array_key_exists('journal_id', $blog_ini['nc2_info'])) {
            $migration_mappings = MigrationMapping::where('target_source_table', 'blogs')->where('source_key', $journal_id)->first();
        }
        // マップから新Blog を取得
        if (!empty($migration_mappings)) {
            $blogs = Blogs::find($migration_mappings->destination_key);
        }
        // 新Blog からBucket ID を取得
        if (!empty($blogs)) {
            $bucket = Buckets::find($blogs->bucket_id);
        }
        // bucket がない場合は、フレームは作るけど、エラーログを出しておく。
        if (!empty($bucket)) {
            $this->putError(1, 'Blog フレームのみで実体なし', "page_dir = " . $page_dir);
        }

        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);
    }

    /**
     * データベースプラグインの登録処理
     */
    private function importPluginDatabases($page, $page_dir, $frame_ini, $display_sequence)
    {
        // 変数定義
        $database_id = null;
        $database_ini = null;
        $multidatabase_id = null;
        $migration_mappings = null;
        $databases = null;
        $bucket = null;

        // エクスポートファイルの database_id 取得（エクスポート時の連番）
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('database_id', $frame_ini['frame_base'])) {
            $database_id = $frame_ini['frame_base']['database_id'];
        }
        // データベースの情報取得
        if (!empty($database_id) && Storage::exists('migration/@databases/database_' . $database_id . '.ini')) {
            $database_ini = parse_ini_file(storage_path() . '/app/migration/@databases/database_' . $database_id . '.ini', true);
        }
        // NC2 のmultidatabase_id
        if (!empty($database_ini) && array_key_exists('nc2_info', $database_ini) && array_key_exists('multidatabase_id', $database_ini['nc2_info'])) {
            $multidatabase_id = $database_ini['nc2_info']['multidatabase_id'];
        }
        // NC2 のmultidatabase_id でマップ確認
        if (!empty($database_ini) && array_key_exists('nc2_info', $database_ini) && array_key_exists('multidatabase_id', $database_ini['nc2_info'])) {
            $migration_mappings = MigrationMapping::where('target_source_table', 'databases')->where('source_key', $multidatabase_id)->first();
        }
        // マップから新Database を取得
        if (!empty($migration_mappings)) {
            $databases = Databases::find($migration_mappings->destination_key);
        }
        // 新データベース からBucket ID を取得
        if (!empty($databases)) {
            $bucket = Buckets::find($databases->bucket_id);
        }
        // bucket がない場合は、フレームは作るけど、エラーログを出しておく。
        if (!empty($bucket)) {
            $this->putError(1, 'Database フレームのみで実体なし', "page_dir = " . $page_dir);
        }

        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);

        // NC2 のview_count
        $view_count = 10; // 初期値
        if (!empty($database_ini) && array_key_exists('database_base', $database_ini) && array_key_exists('view_count', $database_ini['database_base'])) {
            $view_count = $database_ini['database_base']['view_count'];
        }

        // databases_frames 登録
        if (!empty($databases)) {
            DatabasesFrames::create([
                'databases_id'      => $databases->id,
                'frames_id'         => $frame->id,
                'use_search_flag'   => 1,
                'use_select_flag'   => 1,
                'use_sort_flag'     => null,
                'default_sort_flag' => null,
                'view_count'        => $view_count,
                'default_hide'      => 0,
            ]);
        }
    }

    /**
     * HTML からGoogle Analytics タグ部分を削除
     */
    private function deleteGATag($content)
    {
        preg_match_all('/<script(.*?)script>/is', $content, $matches);

        foreach ($matches[0] as $matche) {
            if (stripos($matche, 'www.google-analytics.com/analytics.js')) {
                $content = str_replace($matche, '', $content);
            }
            if (stripos($matche, 'GoogleAnalyticsObject')) {
                $content = str_replace($matche, '', $content);
            }
        }
        return $content;
    }

    /**
     * 固定記事プラグインの登録処理
     */
    private function importPluginContents($page, $page_dir, $frame_ini, $display_sequence)
    {
        // コンテンツが指定されていない場合は戻る
        if (!array_key_exists('contents', $frame_ini) || !array_key_exists('contents_file', $frame_ini['contents'])) {
            return;
        }

        // HTML コンテンツの取得（画像処理をループしながら、タグを編集するので、ここで読みこんでおく）
        $html_file_path = $page_dir . '/' . $frame_ini['contents']['contents_file'];
        $content_html = File::get($html_file_path);

        // Google Analytics タグ部分を削除
        $content_html = $this->deleteGATag($content_html);

        // 対象外の条件を確認
        $import_ommit_keywords = $this->getMigrationConfig('contents', 'import_ommit_keyword', array());
        foreach ($import_ommit_keywords as $import_ommit_keyword) {
            if (stripos($content_html, $import_ommit_keyword) !== false) {
                return;
            }
        }

        // Buckets 登録
        // echo "Buckets 登録\n";
        $bucket = Buckets::create(['bucket_name' => '無題', 'plugin_name' => 'contents']);

        // Frames 登録
        $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);

        // NC2 から移行した場合：[upload_images] の画像を登録
        //
        // --- uploads.ini
        // upload[1] = "upload_00001.jpg"
        //
        // --- mapping テーブル
        //
        // source:
        // distination:
        //
        // --- frame_0001.ini
        // [upload_images]
        // 2 = "upload_00002.jpg"
        //
        // --- frame_0001.html
        // img src="../../@uploads/upload_00002.jpg"
        //
        if (array_key_exists('upload_images', $frame_ini)) {
            // アップロードファイル定義のループ

            foreach ($frame_ini['upload_images'] as $nc2_upload_id => $image_path) {
                // 画像のパスの修正
                // ini ファイルのID はNC2 のアップロードID が入っている。
                // マッピングテーブルから新ID を取得して、変換する。
                $migration_mapping = MigrationMapping::where('target_source_table', 'uploads')->where('source_key', $nc2_upload_id)->first();

                // コンテンツ中のアップロード画像のパスの修正
                if (!empty($migration_mapping)) {
                    $content_html = str_replace($image_path, $this->getImportSrcDir() . $migration_mapping->destination_key, $content_html);
                } else {
                    // $this->putError(1, 'image path not found mapping', "コンテンツ中のアップロード画像のパスがマッピングテーブルに見つからない。nc2_upload_id = " . $nc2_upload_id);
                }
            }
        }

        // NC2 から移行した場合：[upload_files] のファイルを登録
        if (array_key_exists('upload_files', $frame_ini)) {
            foreach ($frame_ini['upload_files'] as $nc2_upload_id => $file_path) {
                // アップロードファイルのパスの修正
                // ini ファイルのID はNC2 のアップロードID が入っている。
                // マッピングテーブルから新ID を取得して、変換する。
                $migration_mapping = MigrationMapping::where('target_source_table', 'uploads')->where('source_key', $nc2_upload_id)->first();

                // コンテンツ中のアップロードファイルのパスの修正
                if (!empty($migration_mapping)) {
                    $content_html = str_replace($file_path, $this->getImportSrcDir() . $migration_mapping->destination_key, $content_html);
                } else {
                    // $this->putError(1, 'file path not found mapping', "コンテンツ中のアップロードファイルのパスがマッピングテーブルに見つからない。nc2_upload_id = " . $nc2_upload_id);
                }
            }
        }

        // NC3 のHTTP から移行した場合：[image_names] の画像を登録
        if (array_key_exists('image_names', $frame_ini)) {
            foreach ($frame_ini['image_names'] as $filename => $client_original_name) {
                // ファイルサイズ
                if (File::exists($page_dir . "/" . $filename)) {
                    $file_size = File::size($page_dir . "/" . $filename);
                } else {
                    $file_size = 0;
                }
                //echo "ファイルサイズ = " . $file_size . "\n";

                // Uploads テーブル
                $upload = Uploads::create([
                              'client_original_name' => $client_original_name,
                              'mimetype'             => $this->getMimetypeFromFilename($filename),
                              'extension'            => $this->getExtension($filename),
                              'size'                 => $file_size,
                              'plugin_name'          => 'contents',
                              'page_id'              => $page->id,
                              'temporary_flag'       => 0,
                          ]);

                // ファイルのコピー
                $source_file_path = $page_dir. "/" . $filename;
                $destination_file_dir = storage_path() . "/app/" . $this->getDirectory($upload->id);
                $destination_file_path = $destination_file_dir . '/' . $upload->id . '.' . $this->getExtension($filename);

                if (!File::isDirectory($destination_file_dir)) {
                    File::makeDirectory($destination_file_dir, 0775, true);
                }
                File::copy($source_file_path, $destination_file_path);

                // 画像のパスの修正
                $content_html = str_replace($filename, $this->getImportSrcDir() . $upload->id, $content_html);
            }
        }

        // NC3 のHTTP から移行した場合：[file_names] の画像を登録
        if (array_key_exists('file_names', $frame_ini)) {
            foreach ($frame_ini['file_names'] as $filename => $client_original_name) {
                // ファイルサイズ
                if (File::exists($page_dir . "/" . $filename)) {
                    $file_size = File::size($page_dir . "/" . $filename);
                } else {
                    $file_size = 0;
                }
                //echo "ファイルサイズ = " . $file_size . "\n";

                // Uploads テーブル
                $upload = Uploads::create([
                              'client_original_name' => $client_original_name,
                              'mimetype'             => $this->getMimetypeFromFilename($filename),
                              'extension'            => $this->getExtension($filename),
                              'size'                 => $file_size,
                              'plugin_name'          => 'contents',
                              'page_id'              => $page->id,
                              'temporary_flag'       => 0,
                          ]);

                // ファイルのコピー
                $source_file_path = $page_dir. "/" . $filename;
                $destination_file_dir = storage_path() . "/app/" . $this->getDirectory($upload->id);
                $destination_file_path = $destination_file_dir . '/' . $upload->id . '.' . $this->getExtension($filename);
                if (!File::isDirectory($destination_file_dir)) {
                    File::makeDirectory($destination_file_dir, 0775, true);
                }
                File::copy($source_file_path, $destination_file_path);

                // ファイルのパスの修正
                $content_html = str_replace($filename, $this->getImportSrcDir() . $upload->id, $content_html);
            }
        }

        //Log::debug($content_html);

        // Contents 登録
        // echo "Contents 登録\n";
        $content = Contents::create(['bucket_id' => $bucket->id,
                                     'content_text' => $content_html,
                                     'status' => 0]);
    }

    /**
     * フレームの登録処理
     */
    private function importPluginFrame($page, $frame_ini, $display_sequence, $bucket = null)
    {
        // Frames 登録
        // echo "Frames 登録\n";

        // Frame タイトル
        $frame_title = '[無題]';
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('frame_title', $frame_ini['frame_base'])) {
            $frame_title = $frame_ini['frame_base']['frame_title'];
        }

        // Frame デザイン
        $frame_design = 'default';
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('frame_design', $frame_ini['frame_base'])) {
            $frame_design = $frame_ini['frame_base']['frame_design'];
        }

        // Frame エリアID
        $frame_area_id = 2; // メイン
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('area_id', $frame_ini['frame_base'])) {
            $frame_area_id = $frame_ini['frame_base']['area_id'];
        }

        // Frame col
        $frame_col = 0;
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('frame_col', $frame_ini['frame_base'])) {
            $frame_col = $frame_ini['frame_base']['frame_col'];
        }

        // テンプレート
        $template = 'default';
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('template', $frame_ini['frame_base'])) {
            $template = $frame_ini['frame_base']['template'];
        }

        // plugin_name
        $plugin_name = '';
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('plugin_name', $frame_ini['frame_base'])) {
            $plugin_name = $frame_ini['frame_base']['plugin_name'];
        }

        // bucket_id
        $bucket_id = null;
        if ($bucket) {
            $bucket_id = $bucket->id;
        }

        $frame = Frame::create(['page_id'          => $page->id,
                                'area_id'          => $frame_area_id,
                                'frame_title'      => $frame_title,
                                'frame_design'     => $frame_design,
                                'plugin_name'      => $plugin_name,
                                'frame_col'        => $frame_col,
                                'template'         => $template,
                                'bucket_id'        => $bucket_id,
                                'display_sequence' => $display_sequence,
                               ]);
        return $frame;
    }

    /**
     * 拡張子からMIMETYPE 取得
     */
    private function getMimetypeFromExtension($extension)
    {
        // 拡張子の確認
        if ($extension == 'jpg') {            // jpeg の場合
            return IMAGETYPE_JPEG;
        } elseif ($extension == 'png') {      // png の場合
            return IMAGETYPE_PNG;
        } elseif ($extension == 'gif') {      // gif の場合
            return IMAGETYPE_GIF;
        }
        return "";
    }

    /**
     * ファイル名から拡張子を取得
     */
    private function getExtension($filename)
    {
        $filepath = pathinfo($filename);
        return $filepath['extension'];
    }

    /**
     * ファイル名からMIMETYPE 取得
     */
    private function getMimetypeFromFilename($filename)
    {
        $extension = $this->getExtension($filename);

        // 拡張子の確認
        if ($extension == 'jpg') {    // jpg
            return IMAGETYPE_JPEG;
        }
        if ($extension == 'png') {    // png
            return IMAGETYPE_PNG;
        }
        if ($extension == 'gif') {    // gif
            return IMAGETYPE_GIF;
        }
        if ($extension == 'pdf') {    // pdf
            return 'application/pdf';
        }
        if ($extension == 'xls') {    // excel
            return 'application/vnd.ms-excel';
        }
        if ($extension == 'xlsx') {   // excel
            return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }
        if ($extension == 'doc') {    // word
            return 'application/msword';
        }
        if ($extension == 'docx') {   // word
            return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        }
        if ($extension == 'ppt') {    // power point
            return 'application/vnd.ms-powerpoint';
        }
        if ($extension == 'pptx') {   // power point
            return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
        }
        if ($extension == 'mp3') {    // mp3
            return 'audio/mpeg';
        }
        if ($extension == 'mp4') {    // mp4
            return 'video/mp4';
        }

        return "application/octet-stream";
    }

    /**
     * ページのHTML取得
     */
    private function migrationNC3Page($url, $page_id)
    {
        /*
        ページ移行関数呼び出し(URL, 移行先のページid)

        ページ移行関数
            HTML 取得
            タイトルバーの文字列の取得
            本文をHTML形式で取得
            本文から画像、添付ファイルのタグの抜き出し
            画像、添付ファイルの取得
            ---
            バケツ作成
            フレーム作成
            固定記事作成
            ファイル登録
        */

        // 画像ファイルや添付ファイルを取得する場合のテンポラリ・ディレクトリ
        //$uniq_tmp_dir = uniqid('migration_');
        Storage::makeDirectory('migration/' . $page_id);

        // 指定されたページのHTML を取得
        $html = $this->getHTMLPage($url);
        //var_dump($url);

        // HTMLドキュメントの解析準備
        $dom = new \DOMDocument;

        // DOMDocument が返ってくる。
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        // NC3 のメイン部分を抜き出します。
        $expression = '//*[@id="container-main"]';

        // DOMElement が返ってくる。
        $container_main = $xpath->query($expression)->item(0);
        //var_dump($container_main);

        // NC3 のフレーム部分として section を抜き出します。
        $expression = './/section';
        $frame_index = 0; // フレームの連番
        foreach ($xpath->query($expression, $container_main) as $section) {
            $frame_index++;
            $frame_index_str = sprintf("%'.04d", $frame_index);

            // フレームタイトル(panel-heading)を抜き出します。
            $expression = './/div[contains(@class, "panel-heading")]/span';
            $frame_title = $xpath->query($expression, $section)->item(0);
            //var_dump($this->getInnerHtml($frame_title));
            //Log::debug($this->getInnerHtml($frame_title));

            // フレーム設定の保存用変数
            $frame_ini = "[frame_base]\n";
            $frame_ini .= "area_id = 2\n";
            $frame_ini .= "frame_title = \"" . $this->getInnerHtml($frame_title) . "\"\n";

            // フレームデザイン
            $expression = './/@class';
            $frame_design = $xpath->query($expression, $section)->item(0);
            $frame_ini .= "frame_design = \"" . $this->getFrameDesign($frame_design->value) . "\"\n";

            // プラグイン情報
            $frame_ini .= "plugin_name = \"contents\"\n";
            $frame_ini .= "nc2_module_name = \"announcement\"\n";

            // 本文を抜き出します。
            $expression = './/div[contains(@class, "panel-body")]/article';
            $content = $xpath->query($expression, $section)->item(0);
            //var_dump($this->getInnerHtml($content));
            //Log::debug($this->getInnerHtml($content));

            // HTML の保存用変数
            $content_html = $this->getInnerHtml($content);

            // 本文から画像(img src)を抜き出す
            $images = $this->getContentImage($content_html);
            //var_dump($images);

            // 画像の取得と保存
            // ・取得して連番で保存（拡張子ナシ）
            // ・mime_type から拡張子決定
            if ($images) {
                // HTML 中の画像ファイルをループで処理
                $frame_ini .= "\n[image_names]\n";
                $image_index = 0;
                foreach ($images as $image_url) {
                    // 保存する画像のパス
                    $image_index++;
                    $downloadPath = $image_url;

                    $file_name = "frame_" . $frame_index_str . '_' . $image_index;
                    $savePath = 'migration/' . $page_id . "/" . $file_name;
                    $saveStragePath = storage_path() . '/app/' . $savePath;

                    // CURL 設定、ファイル取得
                    $ch = curl_init($downloadPath);
                    $fp = fopen($saveStragePath, 'w');
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this,'callbackHeader'));
                    $result = curl_exec($ch);
                    curl_close($ch);
                    fclose($fp);

                    //echo $this->content_disposition;

                    //getimagesize関数で画像情報を取得する
                    list($img_width, $img_height, $mime_type, $attr) = getimagesize($saveStragePath);

                    //list関数の第3引数にはgetimagesize関数で取得した画像のMIMEタイプが格納されているので条件分岐で拡張子を決定する
                    switch ($mime_type) {
                        case IMAGETYPE_JPEG:    // jpegの場合
                            //拡張子の設定
                            $img_extension = "jpg";
                            break;
                        case IMAGETYPE_PNG:     // pngの場合
                        //拡張子の設定
                            $img_extension = "png";
                            break;
                        case IMAGETYPE_GIF:     // gifの場合
                            //拡張子の設定
                            $img_extension = "gif";
                            break;
                    }

                    // 拡張子の変更
                    Storage::delete($savePath . '.' . $img_extension);
                    Storage::move($savePath, $savePath . '.' . $img_extension);

                    // 画像の設定情報の記載
                    $frame_ini .= $file_name . '.' . $img_extension . ' = "' . $this->searchFileName($this->content_disposition) . "\"\n";

                    // content 内の保存した画像のパスを修正
                    $content_html = str_replace($image_url, $file_name . '.' . $img_extension, $content_html);

                    //拡張子の出力
                    //echo $img_extension;
                    //echo "\n";
                }
            }

            // 本文からアンカー(a href)を抜き出す
            $anchors = $this->getContentAnchor($content_html);
            //var_dump($anchors);

            // 添付ファイルの取得と保存
            // ・取得して連番で保存（拡張子ナシ）
            // ・mime_type から拡張子決定
            if ($anchors) {
                // HTML 中のアップロードファイルをループで処理
                $frame_ini .= "\n[file_names]\n";
                $file_index = 0;
                foreach ($anchors as $anchor_href) {
                    // アップロードファイルの場合
                    if (stripos($anchor_href, 'cabinet_files/download') !== false || stripos($anchor_href, 'wysiwyg/file/download') !== false) {
                        // 保存するファイルのパス
                        $file_index++;
                        $downloadPath = $anchor_href;

                        $file_name = "frame_" . $frame_index_str . '_file_' . $file_index;
                        $savePath = 'migration/' . $page_id . "/" . $file_name;
                        $saveStragePath = storage_path() . '/app/' . $savePath;

                        // CURL 設定、ファイル取得
                        $ch = curl_init($downloadPath);
                        $fp = fopen($saveStragePath, 'w');
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_HEADER, false);
                        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this,'callbackHeader'));
                        $result = curl_exec($ch);
                        curl_close($ch);
                        fclose($fp);

                        //echo $this->content_disposition;

                        // ファイルの拡張子の取得
                        $file_extension = $this->getExtension($this->searchFileName($this->content_disposition));

                        // 拡張子の変更
                        Storage::delete($savePath . '.' . $file_extension);
                        Storage::move($savePath, $savePath . '.' . $file_extension);

                        // ファイルの設定情報の記載
                        $frame_ini .= $file_name . '.' . $file_extension . ' = "' . $this->searchFileName($this->content_disposition) . "\"\n";

                        // content 内の保存したファイルのパスを修正
                        $content_html = str_replace($anchor_href, $file_name . '.' . $file_extension, $content_html);

                        //拡張子の出力
                        //echo $file_extension;
                        //echo "\n";
                    }
                }
            }

            // フレーム設定ファイルに [contents] 追加
            $frame_ini .= "\n";
            $frame_ini .= "[contents]\n";
            $frame_ini .= "contents_file = \"frame_" . $frame_index_str . ".html\"\n";

            // フレーム設定ファイルの出力
            Storage::put('migration/' . $page_id . "/frame_" . $frame_index_str . '.ini', $frame_ini);

            // Contents 変換
            $content_html = $this->migrationHtml($content_html);

            // HTML content の保存
            Storage::put('migration/' . $page_id . "/frame_" . $frame_index_str . '.html', trim($content_html));
        }
    }

    /**
     * NC3 からConnect-CMS へタグ変換
     */
    private function migrationHtml($content_html)
    {
        // 画像のレスポンスCSS
        $content_html = $this->replaceCss('img-responsive', 'img-fluid', $content_html);

        // NC3 用画像CSS（削除）
        $content_html = $this->replaceCss('nc3-img-block', '', $content_html);
        $content_html = $this->replaceCss('nc3-img', '', $content_html);
        $content_html = $this->replaceCss('thumbnail', 'img-thumbnail', $content_html);

        return $content_html;
    }

    /**
     * CSS 中のクラス名の変換
     */
    private function replaceCss($search, $replace, $subject)
    {
        $pattern = '/class=((?:\s|")?)(.*?)((?:\s|")+)' . $search . '((?:\s|")+)(.*?)((?:\s|")?)/';
        $replacement = 'class=$1$2$3' . $replace . '$4$5$6';
        $content_html = preg_replace($pattern, $replacement, $subject);
        return $content_html;
    }

    /**
     * content_disposition からファイル名の抜き出し
     */
    private function searchFileName($content_disposition)
    {
        // attachment ＆ filename*=UTF-8 形式
        if (stripos($content_disposition, "Content-Disposition: attachment;filename*=UTF-8''") !== false) {
            return trim(str_replace("Content-Disposition: attachment;filename*=UTF-8''", '', $content_disposition));
        }

        // inline ＆ filename= ＆ filename*=UTF-8 併用形式
        if (stripos($content_disposition, "Content-Disposition: inline; filename=") !== false &&
            stripos($content_disposition, "filename*=UTF-8''") !== false) {
            return trim(mb_substr($content_disposition, stripos($content_disposition, "filename*=UTF-8''") + 17, mb_strlen($content_disposition) - 1));
        }

        return "";
    }

    /**
     * CURL のhttp ヘッダー処理コールバック関数
     */
    private function callbackHeader($ch, $header_line)
    {
        // Content-Disposition の場合に処理する。
        // （この関数はhttp ヘッダーの行数分、呼び出される）
        if (strpos($header_line, "Content-Disposition") !== false) {
            //Log::debug($header_line);
            //echo urldecode($header_line);
            $this->content_disposition = urldecode($header_line);
        }

        return strlen($header_line);
    }

    /**
     * HTML からimg タグの src 属性を取得
     */
    private function getContentImage($content)
    {
        $pattern = '/<img.*?src\s*=\s*[\"|\'](.*?)[\"|\'].*?>/i';

        if (preg_match_all($pattern, $content, $images)) {
            if (is_array($images) && isset($images[1])) {
                return $images[1];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * HTML からimg タグ全体を取得
     */
    private function getContentImageTag($content)
    {
        $pattern = '/<img.*?src\s*=\s*[\"|\'](.*?)[\"|\'].*?>/i';

        if (preg_match_all($pattern, $content, $images)) {
            if (is_array($images) && isset($images[0])) {
                return $images[0];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * HTML からimg タグの style 属性を取得
     */
    private function getImageStyle($content)
    {
        $pattern = '/<img.*?style\s*=\s*[\"|\'](.*?)[\"|\'].*?>/i';

        if (preg_match_all($pattern, $content, $images)) {
            if (is_array($images) && isset($images[1])) {
                return $images[1];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * HTML からa タグの href 属性を取得
     */
    private function getContentAnchor($content)
    {

        $pattern = "|<a.*?href=\"(.*?)\".*?>(.*?)</a>|mis";
        if (preg_match_all($pattern, $content, $anchors)) {
            if (is_array($anchors) && isset($anchors[1])) {
                return $anchors[1];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * ページのHTML取得
     */
    private function getHTMLPage($url)
    {

        // curl Open
        $ch = curl_init();

        //オプション
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $html =  curl_exec($ch);

        // curl Close
        curl_close($ch);

        return $html;
    }

    /**
     * nodeをHTMLとして取り出す
     */
    private function getInnerHtml($node)
    {

        // node が空の場合
        if (empty($node)) {
            return "";
        }

        $children = $node->childNodes;
        $html = '';
        foreach ($children as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }
        return $html;
    }

    /**
     * フレームデザインの取得
     */
    private function getFrameDesign($classes)
    {
        // none
        if (stripos($classes, 'panel-none') !== false) {
            return 'none';
        }
        // primary
        if (stripos($classes, 'panel-primary') !== false) {
            return 'primary';
        }
        // info
        if (stripos($classes, 'panel-info') !== false) {
            return 'info';
        }
        // success
        if (stripos($classes, 'panel-success') !== false) {
            return 'success';
        }
        // warning
        if (stripos($classes, 'panel-warning') !== false) {
            return 'warning';
        }
        // danger
        if (stripos($classes, 'panel-danger') !== false) {
            return 'danger';
        }

        // default
        return 'default';
    }

    /**
     * ID のゼロ埋め
     */
    private function zeroSuppress($id, $size = 4)
    {
        // ページID がとりあえず、1万ページ未満で想定。
        // ここの桁数を上げれば、さらに大きなページ数でも処理可能
        $size_str = sprintf("%'.02d", $size);

        return sprintf("%'." . $size_str . "d", $id);
    }

    /**
     * 経路探索キーの取得（作成）
     */
    private function getRouteStr($nc2_page, $nc2_sort_pages, $get_display_sequence = false)
    {
        // 経路探索パス(配列の route_path )
        // r{root_id}_{parent_id}_{parent_id}_{...}_{page_id}

        // ソート用の配列のキー(root_id を最初に持ってきて、display_sequence でつなぐ)
        // r{root_id}_{display_sequence}_{display_sequence}_{...}_{display_sequence}

        // 前提として、最低限のソートとして、同一階層でのソートができている。
        // ページデータを経路探索をキーに設定済みの配列から、親を探して、自分の経路探索キーを生成する。
        // 経路探索キーは 0021_0026 のように、{第1階層ページID}_{第2階層ページID}_{...} のように生成する。
        foreach ($nc2_sort_pages as $nc2_sort_page_key => $nc2_sort_page) {
            if ($nc2_sort_page->page_id == $nc2_page->parent_id) {
                if ($get_display_sequence) {
                    // ソート用の配列のキーを取得
                    return $nc2_sort_page_key . '_' . $this->zeroSuppress($nc2_page->display_sequence);
                } else {
                    // 経路探索パス
                    return $nc2_sort_page->route_path . '_' . $this->zeroSuppress($nc2_page->page_id);
                }
            }
        }

        // まだ配列になかった場合（各スペースのルートページ）
        if ($get_display_sequence) {
            return 'r' . $this->zeroSuppress($nc2_page->root_id) . '_' . $this->zeroSuppress($nc2_page->display_sequence);
        } else {
            return 'r' . $this->zeroSuppress($nc2_page->root_id) . '_' . $this->zeroSuppress($nc2_page->page_id);
        }
    }

    /**
     * 経路探索キーの取得（Block）
     */
    private function getRouteBlockStr($nc2_block, $nc2_sort_blocks, $get_display_sequence = false)
    {
        foreach ($nc2_sort_blocks as $nc2_sort_block_key => $nc2_sort_block) {
            if ($nc2_sort_block->block_id == $nc2_block->parent_id) {
                if ($get_display_sequence) {
                    // ソート用の配列のキーを取得
                    return $nc2_sort_block_key . '_' . $this->zeroSuppress($nc2_block->row_num . $nc2_block->col_num . $nc2_block->thread_num) . '_' . $nc2_block->block_id;
                } else {
                    // 経路探索パス
                    return $nc2_sort_block->route_path . '_' . $this->zeroSuppress($nc2_block->block_id);
                }
            }
        }

        // まだ配列になかった場合（各スペースのルートページ）
        if ($get_display_sequence) {
            return 'r' . $this->zeroSuppress($nc2_block->root_id) . '_' . $this->zeroSuppress($nc2_block->row_num . $nc2_block->col_num . $nc2_block->thread_num) . '_' . $nc2_block->block_id;
        } else {
            return 'r' . $this->zeroSuppress($nc2_block->root_id) . '_' . $this->zeroSuppress($nc2_block->block_id);
        }
    }

    /**
     * NC2 からデータをエクスポート
     *
     * 動かし方
     *
     * 【.env で以下のNC2 用の定義を設定】
     *
     * NC2_DB_CONNECTION=mysql
     * NC2_DB_HOST=127.0.0.1
     * NC2_DB_PORT=3306
     * NC2_DB_DATABASE=xxxxxx
     * NC2_DB_USERNAME=xxxxxx
     * NC2_DB_PASSWORD=xxxxxx
     * NC2_DB_PREFIX=netcommons2_ (例)
     *
     * 【実行コマンド】
     * php artisan command:ExportNc2
     *
     * 【ブロック・ツリーのCSV】
     * exportNc2() 関数の最後で echo $this->frame_tree; しています。
     * これをコマンドでファイルに出力すればCSV になります。
     *
     * 【移行データ】
     * storage\app\migration にNC2 をエクスポートしたデータが入ります。
     *
     * 【ログ】
     * migration/exportNc2{His}.log
     *
     * 【画像】
     * src にhttp 指定などで、移行しなかった画像はログに出力
     *
     *
     */
    private function exportNc2($target, $target_plugin)
    {
        if (empty(trim($target))) {
            echo "\n";
            echo "---------------------------------------------\n";
            echo "処理の対象を指定してください。\n";
            echo "すべて処理する場合は all を指定してください。\n";
            echo "---------------------------------------------\n";
            return;
        }

        $this->target        = $target;
        $this->target_plugin = $target_plugin;

        // NetCommons2 のページデータの取得

        // 【対象】
        // パブリックのみ（グループルームは今後、要考慮）
        // root_id = 0 は 'グループスペース' などの「くくり」なので不要
        // display_sequence = 0 はヘッダーカラムなどの共通部分

        // 【ソート】
        // space_type でソートする。（パブリック、グループ）
        // thread_num, display_sequence でソートする。

        $this->putMonitor(3, "Start exportNc2.");

        // 移行の初期処理
        $this->migrationInit();

        // uploads_path の最後に / がなければ追加
        $uploads_path = $this->getMigrationConfig('uploads', 'nc2_export_uploads_path');
        if (!empty($uploads_path) && mb_substr($uploads_path, -1) != '/') {
            $uploads_path = $uploads_path . '/';
        }

        // アップロード・データとファイルのエクスポート
        if ($this->isTarget('nc2_export', 'uploads')) {
            $this->nc2ExportUploads($uploads_path);
        }

        // 共通カテゴリデータのエクスポート
        if ($this->isTarget('nc2_export', 'categories')) {
            $this->nc2ExportCategories();
        }

        // ユーザデータのエクスポート
        if ($this->isTarget('nc2_export', 'users')) {
            $this->nc2ExportUsers();
        }

        // NC2 日誌（journal）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'blogs')) {
            $this->nc2ExportJournal();
        }

        // NC2 汎用データベース（multidatabase）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'databases')) {
            $this->nc2ExportMultidatabase();
        }

        // pages データとファイルのエクスポート
        if ($this->isTarget('nc2_export', 'pages')) {
            // NC2 のページデータ
            $nc2_pages_query = Nc2Page::where('private_flag', 0)
                                      ->where('root_id', '<>', 0)
                                      ->where('display_sequence', '<>', 0);

            // ページ指定の有無
            if ($this->getMigrationConfig('pages', 'nc2_export_where_page_ids')) {
                $nc2_pages_query->whereIn('page_id', $this->getMigrationConfig('pages', 'nc2_export_where_page_ids'));
            }

            $nc2_pages = $nc2_pages_query->orderBy('space_type')
                                         ->orderBy('thread_num')
                                         ->orderBy('display_sequence')
                                         ->get();

            // NC2 のページデータは隣接モデルのため、ページ一覧を一発でソートできない。
            // そのため、取得したページデータを一度、経路探索モデルに変換する。
            $nc2_sort_pages = array();

            // 経路探索の文字列をキーにしたページ配列の作成
            foreach ($nc2_pages as $nc2_page) {
                $nc2_page->route_path = $this->getRouteStr($nc2_page, $nc2_sort_pages);
                $nc2_sort_pages[$this->getRouteStr($nc2_page, $nc2_sort_pages, true)] = $nc2_page;
            }

            // 経路探索の文字列（キー）でソート
            ksort($nc2_sort_pages);
            //Log::debug($nc2_sort_pages);

            // NC2 のページID を使うことにした。
            //// 新規ページ用のインデックス
            //// 新規ページは _99 のように _ 付でページを作っておく。（_ 付はデータ作成時に既存page_id の続きで採番する）

            // エクスポートしたページフォルダは連番にした。
            // NC2 のページID を使うと、順番がおかしくなるため。
            $new_page_index = 0;

            // ページのループ
            $this->putMonitor(1, "Page loop.");
            foreach ($nc2_sort_pages as $nc2_sort_page_key => $nc2_sort_page) {
                $this->putMonitor(3, "Page", "page_id = " . $nc2_sort_page->page_id);

                // ページ設定の保存用変数
                $page_ini = "[page_base]\n";
                $page_ini .= "page_name = \"" . $nc2_sort_page->page_name . "\"\n";
                $page_ini .= "permanent_link = \"/" . $nc2_sort_page->permalink . "\"\n";
                $page_ini .= "base_display_flag = 1\n";
                $page_ini .= "nc2_room_id = \"" . $nc2_sort_page->room_id . "\"\n";

                // 親ページの検索（parent_id = 1 はパブリックのトップレベルなので、1 より大きいものを探す）
                if ($nc2_sort_page->parent_id > 1) {
                    // マッピングテーブルから親のページのディレクトリを探す
                    $parent_page_mapping = MigrationMapping::where('target_source_table', 'nc2_pages')->where('source_key', $nc2_sort_page->parent_id)->first();
                    if (!empty($parent_page_mapping)) {
                        $page_ini .= "parent_page_dir = \"" . $parent_page_mapping->destination_key . "\"\n";
                    }
                }

                // ページディレクトリの作成
                //$new_page_index = $nc2_sort_page->page_id;
                $new_page_index++;
                Storage::makeDirectory('migration/@pages/' . $this->zeroSuppress($new_page_index));

                // ページ設定ファイルの出力
                Storage::put('migration/@pages/' . $this->zeroSuppress($new_page_index) . '/' . "/page.ini", $page_ini);

                // マッピングテーブルの追加
                $mapping = MigrationMapping::updateOrCreate(
                    ['target_source_table' => 'nc2_pages', 'source_key' => $nc2_sort_page->page_id],
                    ['target_source_table' => 'nc2_pages',
                     'source_key'          => $nc2_sort_page->page_id,
                     'destination_key'     => $this->zeroSuppress($new_page_index)]
                );

                // echo $nc2_sort_page_key . ':' . $nc2_sort_page->page_name . "\n";

                // ブロック処理
                $this->nc2Block($nc2_sort_page, $new_page_index);
            }
        }

        // ページ、ブロックの関係をCSV 形式で出力。ファイルにしたい場合はコマンドラインでファイルに出力
        // echo $this->frame_tree;
    }

    /**
     *  プラグインの変換
     */
    public function nc2GetPluginName($module_name)
    {
        // uploads の file_path にも対応するため、/ をトル。
        $module_name = trim($module_name, '/');

        // NC2 テンプレート変換配列にあれば、その値。
        // 定義のないものは 'NotFound' にする。
        if (array_key_exists($module_name, $this->plugin_name)) {
            return $this->plugin_name[$module_name];
        }
        return 'NotFound';
    }

    /**
     * NC2：アップロードファイルの移行
     *
     * uploads_ini の形式
     *
     * [uploads]
     * upload[upload_00001] = upload_00001.jpg
     * upload[upload_00002] = upload_00002.png
     * upload[upload_00003] = upload_00003.pdf
     *
     * [upload_00001]
     * file_name =
     * mimetype =
     * extension =
     * plugin_name =
     * page_id = 0
     *
     * [upload_00002]
     * ・・・
     */
    private function nc2ExportUploads($uploads_path)
    {
        $this->putMonitor(3, "Start this->nc2ExportUploads.");

        // NC2 アップロードテーブルを移行する。
        $nc2_uploads = Nc2Upload::orderBy('upload_id')->get();

        // uploads,ini ファイル
        Storage::put('migration/@uploads/uploads.ini', "[uploads]");

        // uploads,ini ファイルの詳細（変数に保持、後でappend。[uploads] セクションが切れないため。）
        $uploads_ini = "";
        $uploads_ini_detail = "";

        // アップロード・ファイルのループ
        foreach ($nc2_uploads as $nc2_upload) {
            // NC2 バックアップは対象外
            if ($nc2_upload->file_path == 'backup/') {
                continue;
            }

            // ファイルのコピー
            $source_file_path = $uploads_path . $nc2_upload->file_path . $nc2_upload->physical_file_name;
            $destination_file_dir = storage_path() . "/app/migration/@uploads";
            $destination_file_name = "upload_" . $this->zeroSuppress($nc2_upload->upload_id, 5);
            $destination_file_path = $destination_file_dir . '/' . $destination_file_name . '.' . $nc2_upload->extension;

            if (File::exists($source_file_path)) {
                if (!File::isDirectory($destination_file_dir)) {
                    File::makeDirectory($destination_file_dir, 0775, true);
                }
                File::copy($source_file_path, $destination_file_path);
            }

            $uploads_ini .= "upload[" . $nc2_upload->upload_id . "] = \"" . $destination_file_name . '.' . $nc2_upload->extension . "\"\n";

            $uploads_ini_detail .= "\n";
            $uploads_ini_detail .= "[" . $nc2_upload->upload_id . "]\n";
            $uploads_ini_detail .= "client_original_name = \"" . $nc2_upload->file_name . "\"\n";
            $uploads_ini_detail .= "temp_file_name = \"" . $destination_file_name . '.' . $nc2_upload->extension . "\"\n";
            $uploads_ini_detail .= "size = \"" . $nc2_upload->file_size . "\"\n";
            $uploads_ini_detail .= "mimetype = \"" . $nc2_upload->mimetype . "\"\n";
            $uploads_ini_detail .= "extension = \"" . $nc2_upload->extension . "\"\n";
            $uploads_ini_detail .= "plugin_name = \"" . $this->nc2GetPluginName($nc2_upload->file_path) . "\"\n";
            $uploads_ini_detail .= "page_id = \"0\"\n";
            $uploads_ini_detail .= "nc2_room_id = \"" . $nc2_upload->room_id . "\"\n";
        }

        // アップロード一覧の出力
        Storage::append('migration/@uploads/uploads.ini', $uploads_ini . $uploads_ini_detail);

        // uploads のini ファイルの再読み込み
        if (Storage::exists('migration/@uploads/uploads.ini')) {
            $this->uploads_ini = parse_ini_file(storage_path() . '/app/migration/@uploads/uploads.ini', true);
        }
    }

    /**
     * NC2：カテゴリの移行
     */
    private function nc2ExportCategories()
    {
        $this->putMonitor(3, "Start nc2ExportCategories.");

        // categories,ini ファイル
        $uploads_ini = "[categories]";
        foreach ($this->nc2_default_categories as $nc2_default_category_key => $nc2_default_category) {
            $uploads_ini .= "\n" . "categories[" . $nc2_default_category_key . "] = \"" . $nc2_default_category . "\"";
        }
        Storage::put('migration/@categories/categories.ini', $uploads_ini);
    }

    /**
     * 半角 @ を全角 ＠ に変換する。
     */
    private function replaceFullwidthAt($str)
    {
        return str_replace('@', '＠', $str);
    }

    /**
     * NC2：ユーザの移行
     */
    private function nc2ExportUsers()
    {
        $this->putMonitor(3, "Start nc2ExportUsers.");

        /*
            移行項目：login_id、password、handle、role_authority_id、system_flag
                      role_authority_id：1 => 管理権限は全てON、コンテンツ権限はコンテンツ管理者
                                       ：2 => 主担権限。管理権限は全てOFF、コンテンツ権限はコンテンツ管理者
                                       ：3 => モデレータ権限。管理権限は全てOFF、コンテンツ権限はモデレータ＆編集者
                                       ：4 => 一般権限。管理権限は全てOFF、コンテンツ権限は編集者
                                       ：5 => ゲスト権限。管理権限は全てOFF、コンテンツ権限はなし
                      system_flag：
            Connect-CMS に機能追加するもの：active_flag
        */

        /*
        [users]
        user[0] = "admin"
        user[1] = "test_user"

        [admin]
        name        = "システム管理者"
        email       = "system@example.com"
        userid      = systemAdmin6152
        password    = $2y$10$Zy7krF.Kcq43qMWC2ZKjqFXLt44urVgh3argR41E6qhmQAZ5e6WKi
        users_roles = "role_article_admin|admin_system"
        */

        // NC2 ユーザの最初のメアド項目取得
        $nc2_mail_item = Nc2Item::where('type', 'email')
                                ->orderBy('col_num')
                                ->orderBy('row_num')
                                ->first();

        // NC2 ユーザデータ取得
        $nc2_users_query = Nc2User::select('users.*', 'users_items_link.content AS email')
                                  ->where('active_flag', 1);
        if (!empty($nc2_mail_item)) {
            $nc2_users_query->leftJoin('users_items_link', function ($join) use ($nc2_mail_item) {
                $join->on('users_items_link.user_id', '=', 'users.user_id')
                     ->where('users_items_link.item_id', '=', $nc2_mail_item->item_id);
            });
        }
        $nc2_users = $nc2_users_query->orderBy('insert_time')
                                     ->get();

        // 空なら戻る
        if ($nc2_users->isEmpty()) {
            return;
        }

        // ini ファイル用変数
        $users_ini = "[users]\n";

        // NC2ユーザ（User）のループ（ユーザインデックス用）
        foreach ($nc2_users as $nc2_user) {
            $users_ini .= "user[\"" . $nc2_user->user_id . "\"] = \"" . $nc2_user->handle . "\"\n";
        }

        // NC2ユーザ（User）のループ（ユーザデータ用）
        foreach ($nc2_users as $nc2_user) {
            // テスト用データ変換
            if ($this->hasMigrationConfig('user', 'nc2_export_test_mail', true)) {
                $nc2_user->email = $this->replaceFullwidthAt($nc2_user->email);
                $nc2_user->login_id = $this->replaceFullwidthAt($nc2_user->login_id);
            }
            $users_ini .= "\n";
            $users_ini .= "[\"" . $nc2_user->user_id . "\"]\n";
            $users_ini .= "name               = \"" . $nc2_user->handle . "\"\n";
            $users_ini .= "email              = \"" . $nc2_user->email . "\"\n";
            $users_ini .= "userid             = \"" . $nc2_user->login_id . "\"\n";
            $users_ini .= "password           = \"" . $nc2_user->password . "\"\n";
            if ($nc2_user->role_authority_id == 1) {
                $users_ini .= "users_roles_manage = \"admin_system\"\n";
                $users_ini .= "users_roles_base   = \"role_article_admin\"\n";
            } elseif ($nc2_user->role_authority_id == 2) {
                $users_ini .= "users_roles_base   = \"role_article_admin\"\n";
            } elseif ($nc2_user->role_authority_id == 3) {
                $users_ini .= "users_roles_base   = \"role_article\"\n";
            } elseif ($nc2_user->role_authority_id == 4) {
                $users_ini .= "users_roles_base   = \"role_reporter\"\n";
            }
        }

        // Userデータの出力
        Storage::put('migration/@users/users.ini', $users_ini);
    }

    /**
     * NC2：日誌（Journal）の移行
     */
    private function nc2ExportJournal()
    {
        $this->putMonitor(3, "Start nc2ExportJournal.");

        // NC2日誌（Journal）を移行する。
        $nc2_journals = Nc2Journal::orderBy('journal_id')->get();

        // 空なら戻る
        if ($nc2_journals->isEmpty()) {
            return;
        }

        // NC2日誌（Journal）のループ
        foreach ($nc2_journals as $nc2_journal) {
            $journals_ini = "";
            $journals_ini .= "[blog_base]\n";
            $journals_ini .= "blog_name = \"" . $nc2_journal->journal_name . "\"\n";
            $journals_ini .= "view_count = 10\n";

            // NC2 情報
            $journals_ini .= "\n";
            $journals_ini .= "[nc2_info]\n";
            $journals_ini .= "journal_id = " . $nc2_journal->journal_id . "\n";
            $journals_ini .= "room_id = " . $nc2_journal->room_id . "\n";

            // NC2日誌のカテゴリ（journal_category）を移行する。
            $journals_ini .= "\n";
            $journals_ini .= "[categories]\n";
            $nc2_journal_categories = Nc2JournalCategory::where('journal_id', $nc2_journal->journal_id)->orderBy('display_sequence')->get();
            //Log::debug($nc2_journal_categories);
            $journals_ini_commons = "";
            $journals_ini_originals = "";

            foreach ($nc2_journal_categories as $nc2_journal_category) {
                if (in_array($nc2_journal_category->category_name, $this->nc2_default_categories)) {
                    // 共通カテゴリにあるものは個別に作成しない。
                    $journals_ini_commons .= "common_categories[" . array_search($nc2_journal_category->category_name, $this->nc2_default_categories) . "] = \"" . $nc2_journal_category->category_name . "\"\n";
                } else {
                    $journals_ini_originals .= "original_categories[" . $nc2_journal_category->category_id . "] = \"" . $nc2_journal_category->category_name . "\"\n";
                }
            }
            if (!empty($journals_ini_commons)) {
                $journals_ini .= $journals_ini_commons;
            }
            if (!empty($journals_ini_originals)) {
                $journals_ini .= $journals_ini_originals;
            }

            // NC2日誌の記事（journal_post）を移行する。
            $nc2_journal_posts = Nc2JournalPost::where('journal_id', $nc2_journal->journal_id)->orderBy('post_id')->get();

            // journals_ini ファイルの詳細（変数に保持、後でappend。[blog_post] セクションを切れないため。）
            // $blog_post_ini_detail = "";

            // 日誌の記事はTSV でエクスポート
            // 日付{\t}status{\t}承認フラグ{\t}タイトル{\t}本文1{\t}本文2{\t}続き表示文言{\t}続き隠し文言
            $journals_tsv = "";

            // NC2日誌の記事をループ
            $journals_ini .= "\n";
            $journals_ini .= "[blog_post]\n";
            foreach ($nc2_journal_posts as $nc2_journal_post) {
                // TSV 形式でエクスポート
                if (!empty($journals_tsv)) {
                    $journals_tsv .= "\n";
                }

                $content       = $this->nc2Wysiwyg(null, null, null, null, $nc2_journal_post->content);
                $more_content  = $this->nc2Wysiwyg(null, null, null, null, $nc2_journal_post->more_content);

                $category_obj  = $nc2_journal_categories->firstWhere('category_id', $nc2_journal_post->category_id);
                $category      = "";
                if (!empty($category_obj)) {
                    $category  = $category_obj->category_name;
                }

                $journals_tsv .= $nc2_journal_post->journal_date    . "\t";
                // $journals_tsv .= $nc2_journal_post->category_id     . "\t";
                $journals_tsv .= $category                          . "\t";
                $journals_tsv .= $nc2_journal_post->status          . "\t";
                $journals_tsv .= $nc2_journal_post->agree_flag      . "\t";
                $journals_tsv .= $nc2_journal_post->title           . "\t";
                $journals_tsv .= $content                           . "\t";
                $journals_tsv .= $more_content                      . "\t";
                $journals_tsv .= $nc2_journal_post->more_title      . "\t";
                $journals_tsv .= $nc2_journal_post->hide_more_title . "\t";

                // 記事のタイトルの一覧
                // タイトルに " あり
                if (strpos($nc2_journal_post->title, '"')) {
                    // ログ出力
                    $this->putError(1, 'Blog title in double-quotation', "タイトル = " . $nc2_journal_post->title);
                }
                $journals_ini .= "post_title[" . $nc2_journal_post->post_id . "] = \"" . str_replace('"', '', $nc2_journal_post->title) . "\"\n";

                // 1記事：1HTML の移行のロジック
                //
                // // タイトルに " あり
                // if (strpos($nc2_journal_post->title, '"')) {
                //     // ログ出力
                //     $this->putError(1, 'Blog title in double-quotation', "タイトル = " . $nc2_journal_post->title);
                // }
                // $journals_ini .= "post_title[" . $nc2_journal_post->post_id . "] = \"" . str_replace('"', '', $nc2_journal_post->title) . "\"\n";
                //
                // // 記事をエクスポート
                // $nc2_block = null;
                // $save_folder = '@blogs';
                // $content_filename = $this->zeroSuppress($nc2_journal->journal_id) . '_' . $this->zeroSuppress($nc2_journal_post->post_id) . ".html";
                // $ini_filename = null;
                // $content = $nc2_journal_post->content . $nc2_journal_post->more_content;
                // $this->nc2Wysiwyg($nc2_block, $save_folder, $content_filename, $ini_filename, $content);

                // $blog_post_ini_detail .= "\n";
                // $blog_post_ini_detail .= "[" . $nc2_journal_post->post_id . "]\n";
                // $blog_post_ini_detail .= "post_html = \"" . $content_filename . "\"\n";
            }

            // blog の記事毎設定
            // $journals_ini .= $blog_post_ini_detail;

            // blog の設定
            Storage::put('migration/@blogs/blog_' . $this->zeroSuppress($nc2_journal->journal_id) . '.ini', $journals_ini);

            // blog の記事
            Storage::put('migration/@blogs/blog_' . $this->zeroSuppress($nc2_journal->journal_id) . '.tsv', $journals_tsv);
        }
    }

    /**
     * NC2：汎用データベース（Databases）の移行
     */
    private function nc2ExportMultidatabase()
    {
        $this->putMonitor(3, "Start nc2ExportMultidatabase.");

        // NC2汎用データベース（Multidatabase）を移行する。
        $nc2_export_where_multidatabase_ids = $this->getMigrationConfig('databases', 'nc2_export_where_multidatabase_ids');

        if (empty($nc2_export_where_multidatabase_ids)) {
            $nc2_multidatabases = Nc2Multidatabase::orderBy('multidatabase_id')->get();
        } else {
            $nc2_multidatabases = Nc2Multidatabase::whereIn('multidatabase_id', $nc2_export_where_multidatabase_ids)->orderBy('multidatabase_id')->get();
        }

        // 空なら戻る
        if ($nc2_multidatabases->isEmpty()) {
            return;
        }

        // NC2汎用データベース（Multidatabase）のループ
        foreach ($nc2_multidatabases as $nc2_multidatabase) {
            $multidatabase_id = $nc2_multidatabase->multidatabase_id;

            // データベース設定
            $multidatabase_ini = "";
            $multidatabase_ini .= "[database_base]\n";
            $multidatabase_ini .= "database_name = \"" . $nc2_multidatabase->multidatabase_name . "\"\n";

            // multidatabase_block の取得
            // 1DB で複数ブロックがあるので、Join せずに、個別に読む
            $nc2_multidatabase_block = Nc2MultidatabaseBlock::where('multidatabase_id', $nc2_multidatabase->multidatabase_id)->orderBy('block_id', 'asc')->first();
            if (empty($nc2_multidatabase_block)) {
                $multidatabase_ini .= "view_count = 10\n";  // 初期値
            } else {
                $multidatabase_ini .= "view_count = " . $nc2_multidatabase_block->visible_item . "\n";
            }

            // NC2 情報
            $multidatabase_ini .= "\n";
            $multidatabase_ini .= "[nc2_info]\n";
            $multidatabase_ini .= "multidatabase_id = " . $nc2_multidatabase->multidatabase_id . "\n";
            $multidatabase_ini .= "room_id = " . $nc2_multidatabase->room_id . "\n";

            // 汎用データベースのカラム情報
            $multidatabase_metadatas = Nc2MultidatabaseMetadata::where('multidatabase_id', $multidatabase_id)
                                                               ->orderBy('display_pos', 'asc')
                                                               ->orderBy('display_sequence', 'asc')
                                                               ->get();
            if (empty($multidatabase_metadatas)) {
                continue;
            }

            // カラム情報
            $multidatabase_cols_rows = array();

            foreach ($multidatabase_metadatas as $multidatabase_metadata) {
                // type
                if ($multidatabase_metadata->type == 1) {
                    $column_type = "text";
                } elseif ($multidatabase_metadata->type == 2) {
                    $column_type = "textarea";
                } elseif ($multidatabase_metadata->type == 3) {
                    $column_type = "link";
                } elseif ($multidatabase_metadata->type == 4) {
                    $column_type = "select";
                } elseif ($multidatabase_metadata->type == 12) {
                    $column_type = "checkbox";
                } elseif ($multidatabase_metadata->type == 5) {
                    $column_type = "file";
                } elseif ($multidatabase_metadata->type == 0) {
                    $column_type = "image";
                } elseif ($multidatabase_metadata->type == 6) {
                    $column_type = "wysiwyg";
                } elseif ($multidatabase_metadata->type == 7) {
                    $column_type = "text";                       // あとで連番型の実装すること。
                } elseif ($multidatabase_metadata->type == 8) {
                    $column_type = "mail";
                } elseif ($multidatabase_metadata->type == 9) {
                    $column_type = "date";
                } elseif ($multidatabase_metadata->type == 10) {
                    $column_type = "created";
                } elseif ($multidatabase_metadata->type == 11) {
                    $column_type = "updated";
                }
                $metadata_id = $multidatabase_metadata->metadata_id;
                $multidatabase_cols_rows[$metadata_id]["column_type"]      = $column_type;
                $multidatabase_cols_rows[$metadata_id]["column_name"]      = $multidatabase_metadata->name;
                $multidatabase_cols_rows[$metadata_id]["required"]         = $multidatabase_metadata->require_flag;
                $multidatabase_cols_rows[$metadata_id]["frame_col"]        = null;
                $multidatabase_cols_rows[$metadata_id]["list_hide_flag"]   = ($multidatabase_metadata->list_flag == 0) ? 1 : 0;
                $multidatabase_cols_rows[$metadata_id]["detail_hide_flag"] = ($multidatabase_metadata->detail_flag == 0) ? 1 : 0;
                $multidatabase_cols_rows[$metadata_id]["sort_flag"]        = $multidatabase_metadata->sort_flag;
                $multidatabase_cols_rows[$metadata_id]["search_flag"]      = $multidatabase_metadata->search_flag;
                $multidatabase_cols_rows[$metadata_id]["select_flag"]      = ($multidatabase_metadata->type == 4 || $multidatabase_metadata->type == 12) ? 1 : 0;
                $multidatabase_cols_rows[$metadata_id]["display_sequence"] = $multidatabase_metadata->display_sequence;
                $multidatabase_cols_rows[$metadata_id]["row_group"]        = null;
                $multidatabase_cols_rows[$metadata_id]["column_group"]     = null;
                if ($multidatabase_metadata->display_pos == 2) {
                    $multidatabase_cols_rows[$metadata_id]["row_group"]    = 1;
                    $multidatabase_cols_rows[$metadata_id]["column_group"] = 1;
                }
                if ($multidatabase_metadata->display_pos == 3) {
                    $multidatabase_cols_rows[$metadata_id]["row_group"]    = 1;
                    $multidatabase_cols_rows[$metadata_id]["column_group"] = 2;
                }
                $multidatabase_cols_rows[$metadata_id]["columns_selects"]  = $multidatabase_metadata->select_content;
            }

            // カラム情報出力
            $multidatabase_ini .= "\n";
            $multidatabase_ini .= "[databases_columns]\n";

            // カラムのサマリ
            foreach ($multidatabase_cols_rows as $metadata_id => $multidatabase_cols) {
                $multidatabase_ini .= "databases_column[" . $metadata_id . "] = \"" . $multidatabase_cols["column_name"] . "\"\n";
            }

            // カラムの詳細
            $display_sequence = 0;  // 順番は振りなおす。（NC2 は4つのエリアごとの順番のため）
            foreach ($multidatabase_cols_rows as $metadata_id => $multidatabase_cols) {
                $display_sequence++;
                $multidatabase_ini .= "\n";
                $multidatabase_ini .= "[" . $metadata_id . "]\n";
                $multidatabase_ini .= "column_type      = \"" . $multidatabase_cols["column_type"]      . "\"\n";
                $multidatabase_ini .= "column_name      = \"" . $multidatabase_cols["column_name"]      . "\"\n";
                $multidatabase_ini .= "required         = "   . $multidatabase_cols["required"]         . "\n";
                $multidatabase_ini .= "frame_col        = "   . $multidatabase_cols["frame_col"]        . "\n";
                $multidatabase_ini .= "list_hide_flag   = "   . $multidatabase_cols["list_hide_flag"]   . "\n";
                $multidatabase_ini .= "detail_hide_flag = "   . $multidatabase_cols["detail_hide_flag"] . "\n";
                $multidatabase_ini .= "sort_flag        = "   . $multidatabase_cols["sort_flag"]        . "\n";
                $multidatabase_ini .= "search_flag      = "   . $multidatabase_cols["search_flag"]      . "\n";
                $multidatabase_ini .= "select_flag      = "   . $multidatabase_cols["select_flag"]      . "\n";
                $multidatabase_ini .= "display_sequence = "   . $display_sequence                       . "\n";
                $multidatabase_ini .= "row_group        = "   . $multidatabase_cols["row_group"]        . "\n";
                $multidatabase_ini .= "column_group     = "   . $multidatabase_cols["column_group"]     . "\n";
                $multidatabase_ini .= "columns_selects  = \"" . $multidatabase_cols["columns_selects"]  . "\"\n";
            }

            // カラムのヘッダー及びTSV 行毎の枠準備（カラム詳細データを枠に入れる。データは抜けがあり得るため、単純に結合すると、カラムがおかしくなる）
            $tsv_header = '';
            $tsv_cols = array();
            foreach ($multidatabase_cols_rows as $metadata_id => $multidatabase_cols) {
                $tsv_header .= $multidatabase_cols["column_name"] . "\t";
                $tsv_cols[$metadata_id] = "";
            }

            $tsv_header .= "created_at\tupdated_at";
            $tsv_cols['insert_time'] = "";
            $tsv_cols['update_time'] = "";

            // データベースの記事
            $multidatabase_metadata_contents = Nc2MultidatabaseMetadataContent::select(
                'multidatabase_metadata_content.*',
                'multidatabase_metadata.type',
                'multidatabase_content.insert_time as multidatabase_content_insert_time',
                'multidatabase_content.update_time as multidatabase_content_update_time'
            )->join('multidatabase_metadata', 'multidatabase_metadata.metadata_id', '=', 'multidatabase_metadata_content.metadata_id')
             ->join('multidatabase_content', 'multidatabase_content.content_id', '=', 'multidatabase_metadata_content.content_id')
             ->join('multidatabase', 'multidatabase.multidatabase_id', '=', 'multidatabase_metadata.multidatabase_id')
             ->where('multidatabase.multidatabase_id', $multidatabase_id)
             ->orderBy('multidatabase_metadata_content.content_id', 'asc')
             ->orderBy('multidatabase_metadata.display_pos', 'asc')
             ->orderBy('multidatabase_metadata.display_sequence', 'asc')
             ->get();

            // カラムデータのループ
            $content_id = 0;
            $tsv_record = $tsv_cols;
            Storage::delete('migration/@databases/database_' . $this->zeroSuppress($multidatabase_id) . '.tsv');
            $tsv = '';
            foreach ($multidatabase_metadata_contents as $multidatabase_metadata_content) {
                // レコードのID が変わった＝コントロールブレイク
                if ($content_id != $multidatabase_metadata_content->content_id) {
                    if ($content_id == 0) {
                        //Storage::append('migration/@databases/database_' . $this->zeroSuppress($multidatabase_id) . '.tsv', $tsv_header);
                        $tsv .= $tsv_header . "\n";
                    } else {
                        // 登録日時、更新日時
                        $tsv_record['insert_time'] = $this->getCCDatetime($multidatabase_metadata_content->multidatabase_content_insert_time);
                        $tsv_record['update_time'] = $this->getCCDatetime($multidatabase_metadata_content->multidatabase_content_update_time);
                        // データ行の書き出し
                        //Storage::append('migration/@databases/database_' . $this->zeroSuppress($multidatabase_id) . '.tsv', implode("\t", $tsv_record));
                        $tsv .= implode("\t", $tsv_record) . "\n";
                    }
                    $content_id = $multidatabase_metadata_content->content_id;
                    $tsv_record = $tsv_cols;
                }
                $content = str_replace("\n", "<br />", $multidatabase_metadata_content->content);

                // メタデータの型による変換
                if ($multidatabase_metadata_content->type === 0) {
                    // 画像型
                    if (strpos($content, '?action=multidatabase_action_main_filedownload&upload_id=') !== false) {
                        // NC2 のアップロードID 抜き出し
                        $nc2_uploads_id = str_replace('?action=multidatabase_action_main_filedownload&upload_id=', '', $content);
                        // マッピングテーブルから新ID を探す
                        $migration_mappings = MigrationMapping::where('target_source_table', 'uploads')->where('source_key', $nc2_uploads_id)->first();
                        // マップから新ファイルID を取得
                        if (!empty($migration_mappings)) {
                            // アップロードファイル情報
                            $migration_mappings = MigrationMapping::where('target_source_table', 'uploads')->where('source_key', $nc2_uploads_id)->first();
                            $content = $migration_mappings->destination_key;
                        }
                    }
                } elseif ($multidatabase_metadata_content->type === 6) {
                    // WYSIWYG
                    $content = $this->nc2Wysiwyg(null, null, null, null, $content);
                } elseif ($multidatabase_metadata_content->type === 9) {
                    // 日付型
                    if (!empty($content) && strlen($content) == 14) {
                        $content = $this->getCCDatetime($content);
                    }
                }

                $tsv_record[$multidatabase_metadata_content->metadata_id] = $content;
            }
            // 登録日時、更新日時
            $tsv_record['insert_time'] = $this->getCCDatetime($multidatabase_metadata_content->multidatabase_content_insert_time);
            $tsv_record['update_time'] = $this->getCCDatetime($multidatabase_metadata_content->multidatabase_content_update_time);

            // データ行の書き出し
            //Storage::append('migration/@databases/database_' . $this->zeroSuppress($multidatabase_id) . '.tsv', implode("\t", $tsv_record));
            Storage::append('migration/@databases/database_' . $this->zeroSuppress($multidatabase_id) . '.tsv', $tsv);

            // detabase の設定
            Storage::put('migration/@databases/database_' . $this->zeroSuppress($multidatabase_id) . '.ini', $multidatabase_ini);
        }
    }

    /**
     * NC2：ページ内のブロックをループ
     */
    private function nc2Block($nc2_page, $new_page_index)
    {
        // 指定されたページ内のブロックを取得
        $nc2_blocks_query = Nc2Block::where('page_id', $nc2_page->page_id);

        // 対象外のブロックがあれば加味する。
        $export_ommit_blocks = $this->getMigrationConfig('frames', 'export_ommit_blocks');
        if (!empty($export_ommit_blocks)) {
            $nc2_blocks_query->whereNotIn('block_id', $export_ommit_blocks);
        }

        // メニューが対象外なら除外する。
        $export_ommit_menu = $this->getMigrationConfig('menus', 'export_ommit_menu');
        if ($export_ommit_menu) {
            $nc2_blocks_query->where('action_name', '<>', 'menu_view_main_init');
        }

        $nc2_blocks = $nc2_blocks_query->orderBy('thread_num')
                                       ->orderBy('row_num')
                                       ->orderBy('col_num')
                                       ->get();

        // ブロックをループ
        $frame_index = 0; // フレームの連番

        // トップページの場合のみ、ヘッダ、左、右のブロックを取得して、トップページに設置する。
        // NC2 では、ヘッダ、左、右が一つずつで共通のため、ここで処理する。
        if ($nc2_page->permalink == '' && $nc2_page->display_sequence == 1 && $nc2_page->space_type == 1 && $nc2_page->private_flag == 0) {
            // 指定されたページ内のブロックを取得
            $nc2_common_blocks = Nc2Block::select('blocks.*', 'pages.page_name')
                                         ->join('pages', 'pages.page_id', '=', 'blocks.page_id')
                                         ->whereIn('pages.page_name', ['Header Column', 'Left Column', 'Right Column'])
                                         ->orderBy('page_id', 'desc')
                                         ->orderBy('col_num', 'desc')
                                         ->get();

            // 共通部分をBlock 設定に追加する。
            foreach ($nc2_common_blocks as $nc2_common_block) {
                // ヘッダーは無条件にフレームデザインをnone にしておく
                if ($nc2_common_block->page_name == 'Header Column') {
                    $nc2_common_block->theme_name = 'noneframe';
                }

                // Block 設定に追加
                $nc2_blocks->prepend($nc2_common_block);
            }
            // Log::debug($nc2_blocks);
        }

        // 経路探索の文字列をキーにしたページ配列の作成
        $nc2_sort_blocks = array();
        foreach ($nc2_blocks as $nc2_block) {
            $nc2_block->route_path = $this->getRouteBlockStr($nc2_block, $nc2_sort_blocks);
            $nc2_sort_blocks[$this->getRouteBlockStr($nc2_block, $nc2_sort_blocks, true)] = $nc2_block;
        }
        // Log::debug($nc2_sort_blocks);

        // 経路探索の文字列（キー）でソート
        ksort($nc2_sort_blocks);
        // Log::debug($nc2_sort_blocks);

        // ソート結果でCollection 詰めなおし
        $nc2_blocks = collect();
        foreach ($nc2_sort_blocks as $nc2_sort_block) {
            $nc2_blocks[] = $nc2_sort_block;
        }

        // ページ内のブロック
        foreach ($nc2_blocks as $nc2_block) {
            $this->putMonitor(1, "Block", "block_id = " . $nc2_block->block_id);

            // グループは対象外（frame_col で対応）
            if ($nc2_block->action_name == 'pages_view_grouping') {
                // ページ、ブロック構成を最後に出力するために保持
                $this->nc2BlockTree($nc2_page, $nc2_block);

                continue;
            }

            $frame_index++;
            $frame_index_str = sprintf("%'.04d", $frame_index);

            // フレーム設定の保存用変数
            $frame_ini = "[frame_base]\n";
            $frame_ini .= "area_id = " . $this->nc2BlockArea($nc2_block) . "\n";
            $frame_ini .= "frame_title = \"" . $nc2_block->block_name . "\"\n";
            $frame_ini .= "frame_design = \"" . $nc2_block->getFrameDesign() . "\"\n";
            $frame_ini .= "plugin_name = \"" . $nc2_block->getPluginName() . "\"\n";

            // グルーピングされているブロックの考慮
            // 同じ親で同じ行（row_num）に配置されているブロックの数を12で計算する。
            // 親（parent_id）= 0 でcol_num があるデータがある。NC2 の場合は、親にグループが居るはず。
            $row_block_count = $nc2_blocks->where('parent_id', $nc2_block->parent_id)->where('row_num', $nc2_block->row_num)->count();
            $row_block_parent = $nc2_blocks->where('block_id', $nc2_block->parent_id)->first();

            if ($row_block_count > 1 && $row_block_count <= 12 && $row_block_parent && $row_block_parent->action_name == 'pages_view_grouping') {
                $frame_ini .= "frame_col = " . floor(12 / $row_block_count) . "\n";
            }
            $frame_ini .= "template = \"" . $this->nc2BlockTemp($nc2_block) . "\"\n";

            // モジュールに紐づくメインのデータのID
            $frame_ini .= $this->nc2BlockMainDataId($nc2_block);

            // NC2 情報
            $frame_nc2 = "\n";
            $frame_nc2 .= "[nc2_info]\n";
            $frame_nc2 .= "nc2_block_id = \"" . $nc2_block->block_id . "\"\n";
            $frame_nc2 .= "nc2_module_name = \"" . $nc2_block->getModuleName() . "\"\n";
            $frame_ini .= $frame_nc2;

            // フレーム設定ファイルの出力
            Storage::put('migration/@pages/' . $this->zeroSuppress($new_page_index) . "/frame_" . $frame_index_str . '.ini', $frame_ini);

            //echo $nc2_block->block_name . "\n";

            // ブロックのモジュールデータをエクスポート
            $this->nc2BlockExport($nc2_page, $nc2_block, $new_page_index, $frame_index_str);

            // ページ、ブロック構成を最後に出力するために保持
            $this->nc2BlockTree($nc2_page, $nc2_block);

            // Connect-CMS のプラグイン名の取得
            $plugin_name = $nc2_block->getPluginName();
            if ($plugin_name == 'Development' || $plugin_name == 'Abolition' || $plugin_name == 'forms' || $plugin_name == 'reservations' || $plugin_name == 'searchs' || $plugin_name == 'whatsnews') {
                // 移行できなかったモジュール
                $this->putError(3, "no migrate module", "モジュール = " . $nc2_block->getModuleName(), $nc2_block);
            }
        }
    }

    /**
     * NC2：ブロックに紐づくモジュールのメインデータのID 取得
     */
    private function nc2BlockMainDataId($nc2_block)
    {
        $ret = "";
        $module_name = $nc2_block->getModuleName();
        if ($module_name == 'journal') {
            $nc2_journal_block = Nc2JournalBlock::where('block_id', $nc2_block->block_id)->first();
            $ret = "blog_id = \"" . $this->zeroSuppress($nc2_journal_block->journal_id) . "\"\n";
        } elseif ($module_name == 'multidatabase') {
            $nc2_multidatabase_block = Nc2MultidatabaseBlock::where('block_id', $nc2_block->block_id)->first();
            if (empty($nc2_multidatabase_block)) {
                $this->putError(3, "Nc2MultidatabaseBlock not found.", "block_id = " . $nc2_block->block_id, $nc2_block);
            } else {
                $ret = "database_id = \"" . $this->zeroSuppress($nc2_multidatabase_block->multidatabase_id) . "\"\n";
            }
        }
        return $ret;
    }

    /**
     * NC2：ブロックのテンプレート
     */
    private function nc2BlockTemp($nc2_block)
    {
        $module_name = $nc2_block->getModuleName();
        if ($module_name == 'menu') {
            // メニューのテンプレートの判定
            if ($nc2_block->temp_name == 'default') {
                // メニューのdefault テンプレートの場合、Connect-CMS では「ディレクトリ展開式」に変更する。
                return 'opencurrenttree';
            } elseif (strpos($nc2_block->temp_name, 'side') !== false) {
                // メニューの横長系のテンプレートの場合、Connect-CMS では「ドロップダウン」に変更する。
                return 'opencurrenttree';
            } elseif (strpos($nc2_block->temp_name, 'header') !== false) {
                // メニューの横長系のテンプレートの場合、Connect-CMS では「ドロップダウン」に変更する。
                return 'dropdown';
            } elseif (strpos($nc2_block->temp_name, 'jq_gnavi') !== false) {
                // メニューの横長系のテンプレートの場合、Connect-CMS では「ドロップダウン」に変更する。
                return 'dropdown';
            } elseif ($nc2_block->temp_name == 'topic_path') {
                // パンくず。
                return 'breadcrumbs';
            }
        }
        return 'default';
    }

    /**
     * NC2：ブロックのエリア
     */
    private function nc2BlockArea($nc2_block)
    {
        if ($nc2_block->page_name == 'Header Column') {
            return '0';
        } elseif ($nc2_block->page_name == 'Left Column') {
            return '1';
        } elseif ($nc2_block->page_name == 'Right Column') {
            return '3';
        }
        return '2';
    }

    /**
     * NC2：ページ内のブロックに配置されているモジュールのエクスポート。
     * モジュールごとのエクスポート処理に振り分け。
     */
    private function nc2BlockExport($nc2_page, $nc2_block, $new_page_index, $frame_index_str)
    {
        // Connect-CMS のプラグイン名の取得
        $plugin_name = $nc2_block->getPluginName();

        // モジュールごとに振り分け

        // プラグインで振り分け
        if ($plugin_name == 'contents') {
            // 固定記事（お知らせ）
            $this->nc2ExportContents($nc2_page, $nc2_block, $new_page_index, $frame_index_str);
        } elseif ($plugin_name == 'menus') {
            // メニュー
            // 今のところ、メニューの追加設定はなし。
        }
    }

    /**
     * NC2：固定記事（お知らせ）のエクスポート
     */
    private function nc2ExportContents($nc2_page, $nc2_block, $new_page_index, $frame_index_str)
    {
        // お知らせモジュールのデータの取得
        // 続きを読むはとりあえず、1つに統合。固定記事の方、対応すること。
        $announcement = Nc2Announcement::where('block_id', $nc2_block->block_id)->first();

        // 記事

        // 「お知らせモジュール」のデータがなかった場合は、データの不整合としてエラーログを出力
        $content = "";
        if (!empty($announcement)) {
            $content = trim($announcement->content);
            $content .= trim($announcement->more_content);
        } else {
            $this->putError(1, "no announcement record", "block_id = " . $nc2_block->block_id);
        }

        // WYSIWYG 記事のエクスポート
        $save_folder = '@pages/' . $this->zeroSuppress($new_page_index);
        $content_filename = "frame_" . $frame_index_str . '.html';
        $ini_filename = "frame_" . $frame_index_str . '.ini';

        $this->nc2Wysiwyg($nc2_block, $save_folder, $content_filename, $ini_filename, $content);

        //echo "nc2ExportContents";
    }

    /**
     * NC2：WYSIWYG の記事の保持
     *
     * 保存するディレクトリ：migration の下を指定
     * コンテンツファイル名
     * iniファイル名
     */
    private function nc2Wysiwyg($nc2_block, $save_folder, $content_filename, $ini_filename, $content)
    {
        // 画像を探す
        $img_srcs = $this->getContentImage($content);
        // var_dump($img_srcs);

        // 画像の中のcommon_download_main をエクスポートしたパスに変換する。
        $content = $this->nc2MigrationCommonDownloadMain($nc2_block, $save_folder, $ini_filename, $content, $img_srcs, '[upload_images]');

        // 画像全体にレスポンシブCSS を適用する。
        $img_srcs = $this->getContentImageTag($content);
        if (!empty($img_srcs)) {
            $img_srcs = array_unique($img_srcs);
            foreach ($img_srcs as $img_src) {
                if (stripos($img_src, '../../@uploads') !== false && stripos($img_src, 'class=') === false) {
                    $new_img_src = str_replace('<img ', '<img class="img-fluid" ', $img_src);
                    $content = str_replace($img_src, $new_img_src, $content);
                }
            }
        }

        // 画像のstyle設定を探し、height をmax-height に変換する。
        $img_styles = $this->getImageStyle($content);
        if (!empty($img_styles)) {
            $img_styles = array_unique($img_styles);
            //Log::debug($img_styles);
            foreach ($img_styles as $img_style) {
                $new_img_style = str_replace('height', 'max-height', $img_style);
                $new_img_style = str_replace('max-max-height', 'max-height', $new_img_style);
                $content = str_replace($img_style, $new_img_style, $content);
            }
        }

        // 添付ファイルを探す
        $anchors = $this->getContentAnchor($content);
        //var_dump($anchors);

        // 添付ファイルの中のcommon_download_main をエクスポートしたパスに変換する。
        $content = $this->nc2MigrationCommonDownloadMain($nc2_block, $save_folder, $ini_filename, $content, $anchors, '[upload_files]');

        // HTML content の保存
        if ($save_folder) {
            Storage::put('migration/' . $save_folder . "/" . $content_filename, $content);
        }

        // フレーム設定ファイルの追記
        if ($ini_filename) {
            $contents_ini = "[contents]\n";
            $contents_ini .= "contents_file = \"" . $content_filename . "\"\n";
            Storage::append('migration/' . $save_folder . "/" . $ini_filename, $contents_ini);
        }

        return $content;
    }

    /**
     * NC2：common_download_main をエクスポート形式に変換
     */
    private function nc2MigrationCommonDownloadMain($nc2_block, $save_folder, $ini_filename, $content, $paths, $section_name)
    {
        if (empty($paths)) {
            return $content;
        }

        // 変換処理
        list($content, $export_paths) = $this->nc2MigrationCommonDownloadMainImple($content, $paths, $section_name, $nc2_block = null);

        // フレーム設定ファイルの追記
        $ini_text = $section_name . "\n";
        foreach ($export_paths as $export_key => $export_path) {
            $ini_text .= $export_key . " = \"" . $export_path . "\"\n";
        }

        // 記事ごとにini ファイルが必要な場合のみ出力する。
        if ($ini_filename) {
            Storage::append('migration/' . $save_folder . "/" . $ini_filename, $ini_text);
        }


//        // フレーム設定ファイルの追記
//        $ini_text = $section_name . "\n";
//
//        foreach ($paths as $path) {
//            // common_download_main があれば、NC2 の画像として移行する。
//            if (stripos($path, 'common_download_main') !== false) {
//                // &amp; があれば、& に変換
//                $path_tmp = str_replace('&amp;', '&', $path);
//                // &で分割
//                $src_params = explode('&', $path_tmp);
//                foreach ($src_params as $src_param) {
//                    $param_split = explode('=', $src_param);
//                    if ($param_split[0] == 'upload_id') {
//                        // フレーム設定ファイルの追記
//                        // 移行したアップロードファイルをini ファイルから探す
//                        if ($this->uploads_ini && array_key_exists('uploads', $this->uploads_ini) && array_key_exists('upload', $this->uploads_ini['uploads']) && array_key_exists($param_split[1], $this->uploads_ini['uploads']['upload'])) {
//                            // コンテンツ及び[upload_images] or [upload_files]セクション内のimg src or a href を作る。
//                            $export_path = '../../@uploads/' . $this->uploads_ini[$param_split[1]]['temp_file_name'];
//
//                            // [upload_images] or [upload_files] 内の画像情報の追記
//                            $ini_text .= $param_split[1] . " = \"" . $export_path . "\"\n";
//
//                            // ファイルのパスの修正
//                            $content = str_replace($path, $export_path, $content);
//                        } else {
//                            // 移行しなかったファイルのimg or a タグとしてログに記録
//                            $this->putError(1, "no migrate img", "src = " . $path, $nc2_block);
//                        }
//                    }
//                }
//            }
//        }
//
//        // 記事ごとにini ファイルが必要な場合のみ出力する。
//        if ($ini_filename) {
//            Storage::append('migration/' . $save_folder . "/" . $ini_filename, $ini_text);
//        }

        // パスを変更した記事を返す。
        return $content;
    }

    /**
     * NC2：common_download_main をエクスポート形式に変換
     */
    private function nc2MigrationCommonDownloadMainImple($content, $paths, $section_name, $nc2_block = null)
    {
        if (empty($paths)) {
            return $content;
        }

        // 修正したパスの配列
        $export_paths = array();

        foreach ($paths as $path) {
            // common_download_main があれば、NC2 の画像として移行する。
            if (stripos($path, 'common_download_main') !== false) {
                // &amp; があれば、& に変換
                $path_tmp = str_replace('&amp;', '&', $path);
                // &で分割
                $src_params = explode('&', $path_tmp);
                foreach ($src_params as $src_param) {
                    $param_split = explode('=', $src_param);
                    if ($param_split[0] == 'upload_id') {
                        // フレーム設定ファイルの追記
                        // 移行したアップロードファイルをini ファイルから探す
                        if ($this->uploads_ini && array_key_exists('uploads', $this->uploads_ini) && array_key_exists('upload', $this->uploads_ini['uploads']) && array_key_exists($param_split[1], $this->uploads_ini['uploads']['upload'])) {
                            // コンテンツ及び[upload_images] or [upload_files]セクション内のimg src or a href を作る。
                            $export_path = '../../@uploads/' . $this->uploads_ini[$param_split[1]]['temp_file_name'];

                            // [upload_images] or [upload_files] 内の画像情報の追記
                            $export_paths[$param_split[1]] = $export_path;

                            // ファイルのパスの修正
                            $content = str_replace($path, $export_path, $content);
                        } else {
                            // 移行しなかったファイルのimg or a タグとしてログに記録
                            $this->putError(1, "no migrate img", "src = " . $path, $nc2_block);
                        }
                    }
                }
            }
        }

        // パスを変更した記事を返す。
        return array($content, $export_paths);
    }

    /**
     * NC2：ページ内のブロックをCSV用に溜める
     */
    private function nc2BlockTree($nc2_page, $nc2_block)
    {
        // ページ、ブロック構成を最後に出力するために保持
        $this->frame_tree .= $nc2_page->page_id . ',' . $nc2_page->page_name . ',' . $nc2_page->permalink . ',' . $nc2_block->action_name . ',' . $nc2_block->block_id . ',' . $nc2_block->block_name . "\n";
    }
}
