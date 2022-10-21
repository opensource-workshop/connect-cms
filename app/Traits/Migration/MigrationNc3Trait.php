<?php

namespace App\Traits\Migration;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use Symfony\Component\Yaml\Yaml;

use App\Models\Migration\MigrationMapping;

use App\Models\Migration\Nc3\Nc3AccessCounter;
use App\Models\Migration\Nc3\Nc3Announcement;
use App\Models\Migration\Nc3\Nc3Box;
use App\Models\Migration\Nc3\Nc3Bbs;
use App\Models\Migration\Nc3\Nc3Block;
use App\Models\Migration\Nc3\Nc3Blog;
use App\Models\Migration\Nc3\Nc3Cabinet;
use App\Models\Migration\Nc3\Nc3CalendarFrameSetting;
use App\Models\Migration\Nc3\Nc3Faq;
use App\Models\Migration\Nc3\Nc3Frame;
// use App\Models\Migration\Nc3\Nc3Link;
use App\Models\Migration\Nc3\Nc3MenuFramePage;
use App\Models\Migration\Nc3\Nc3MenuFrameSetting;
use App\Models\Migration\Nc3\Nc3Multidatabase;
// use App\Models\Migration\Nc3\Nc3Topic;
use App\Models\Migration\Nc3\Nc3TopicFrameSetting;
use App\Models\Migration\Nc3\Nc3Page;
use App\Models\Migration\Nc3\Nc3PageContainer;
use App\Models\Migration\Nc3\Nc3Registration;
use App\Models\Migration\Nc3\Nc3ReservationFrameSetting;
// use App\Models\Migration\Nc3\Nc3Room;
use App\Models\Migration\Nc3\Nc3PhotoAlbum;
use App\Models\Migration\Nc3\Nc3PhotoAlbumFrameSetting;
use App\Models\Migration\Nc3\Nc3SiteSetting;
use App\Models\Migration\Nc3\Nc3Space;
use App\Models\Migration\Nc3\Nc3UploadFile;
use App\Models\Migration\Nc3\Nc3User;
use App\Models\Migration\Nc3\Nc3UserAttribute;
use App\Models\Migration\Nc3\Nc3UserAttributeChoice;
use App\Models\Migration\Nc3\Nc3UsersLanguage;

use App\Traits\ConnectCommonTrait;
use App\Utilities\Migration\MigrationUtils;

use App\Enums\AreaType;
use App\Enums\CounterDesignType;
use App\Enums\DayOfWeek;
use App\Enums\LinklistType;
use App\Enums\NoticeEmbeddedTag;
use App\Enums\ReservationLimitedByRole;
use App\Enums\ReservationNoticeEmbeddedTag;
use App\Enums\StatusType;
use App\Enums\UserColumnType;
use App\Enums\UserStatus;

/**
 * NC3移行プログラム
 */
trait MigrationNc3Trait
{
    use ConnectCommonTrait, MigrationLogTrait;

    /**
     * ログのヘッダー出力
     * use する側で定義する
     * @see \App\Traits\Migration\MigrationLogTrait
     */
    private $log_header = "frame_id,,category,message";

    /**
     * uploads.ini
     */
    private $uploads_ini = null;

    /**
     * 移行の設定ファイル
     */
    private $migration_config = array();

    /**
     * エクスポート済みトップページのbox_id保持
     */
    private $exported_common_top_page_box_ids = [
        Nc3Box::container_type_header => [],
        Nc3Box::container_type_left   => [],
        Nc3Box::container_type_main   => [],
        Nc3Box::container_type_right  => [],
        Nc3Box::container_type_footer => []
    ];

    /**
     * エクスポート済みframe_id保持
     */
    private $exported_frame_ids = [];

    /**
     * NC3 plugin_key -> Connect-CMS plugin_name 変換用テーブル
     * 開発中 or 開発予定のものは 'Development' にする。
     * 廃止のものは 'Abolition' にする。
     */
    protected $plugin_name = [
        // 'access_counters'  => 'counters',     // カウンター
        // 'announcements'    => 'contents',     // お知らせ
        // 'bbses'            => 'bbses',        // 掲示板
        // 'blogs'            => 'blogs',        // ブログ
        // 'cabinets'         => 'cabinets',     // キャビネット
        // 'calendars'        => 'calendars',    // カレンダー
        // 'circular_notices' => 'Development',  // 回覧板
        // 'faqs'             => 'faqs',         // FAQ
        // 'iframes'          => 'Development',  // iFrame
        // 'links'            => 'linklists',    // リンクリスト
        // 'menus'            => 'menus',        // メニュー
        // 'multidatabases'   => 'databases',    // データベース
        // 'photo_albums'     => 'photoalbums',  // フォトアルバム
        // 'questionnaires'   => 'Development',  // アンケート
        // 'quizzes'          => 'Development',  // 小テスト
        // 'registrations'    => 'forms',        // フォーム
        // 'reservations'     => 'reservations', // 施設予約
        // 'rss_readers'      => 'Development',  // RSS
        // 'searches'         => 'searchs',      // 検索
        // 'tasks'            => 'Development',  // ToDo
        // 'topics'           => 'whatsnews',    // 新着情報
        // 'videos'           => 'Development',  // 動画
        'access_counters'  => 'Development',  // カウンター
        'announcements'    => 'contents',     // お知らせ
        'bbses'            => 'Development',  // 掲示板
        'blogs'            => 'Development',  // ブログ
        'cabinets'         => 'Development',  // キャビネット
        'calendars'        => 'Development',  // カレンダー
        'circular_notices' => 'Development',  // 回覧板
        'faqs'             => 'Development',  // FAQ
        'iframes'          => 'Development',  // iFrame
        'links'            => 'Development',  // リンクリスト
        'menus'            => 'menus',        // メニュー
        'multidatabases'   => 'Development',  // データベース
        'photo_albums'     => 'Development',  // フォトアルバム
        'questionnaires'   => 'Development',  // アンケート
        'quizzes'          => 'Development',  // 小テスト
        'registrations'    => 'Development',  // フォーム
        'reservations'     => 'Development',  // 施設予約
        'rss_readers'      => 'Development',  // RSS
        'searches'         => 'Development',  // 検索
        'tasks'            => 'Development',  // ToDo
        'topics'           => 'Development',  // 新着情報
        'videos'           => 'Development',  // 動画
        'wysiwyg'          => 'Development',  // wysiwyg(upload用)
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
     * クリア
     */
    private $redo = null;

    /**
     * 追加処理
     */
    private $added = false;

    /**
     * migration_base
     */
    private $migration_base = 'migration/';

    /**
     * import_base
     */
    private $import_base = 'import/';

    /**
     * migration 各データのパス取得
     */
    private function getImportPath($target, $import_base = null)
    {
        if (empty($import_base)) {
            $import_base = $this->import_base;
        }

        $import_dir = $this->migration_base . $import_base;

        return $import_dir . $target;
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
                $this->putMonitor(2, 'migration_config.ini(nc3)未設定', "migration_config.iniの [{$target}] " . $command . '_' . $target . " を設定してください。");
                return false;
            }
        }

        // 対象
        return true;
    }

    /**
     * NC3のリンク切れチェック
     */
    private function checkDeadLinkNc2($url, $nc3_module_name = null, $nc3_block = null)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (in_array($scheme, ['http', 'https'])) {

            $nc3_base_url = $this->getMigrationConfig('basic', 'check_deadlink_nc3_base_url', '');
            if (empty($nc3_base_url)) {
                $this->putLinkCheck(3, 'check_deadlink_nc3_base_url未設定', 'migration_config.iniの [basic] check_deadlink_nc3_base_url を設定してください');
            }

            $domain = str_replace("https://", "", $nc3_base_url);
            $domain = str_replace("http://", "", $domain);

            // 先頭がNC3のベースURL
            if (preg_match("/^http:\/\/{$domain}|^https:\/\/{$domain}/", $url)) {
                // 内部リンク
                $this->checkDeadLinkInsideNc2($url, $nc3_module_name, $nc3_block);
            } else {
                // 外部リンク
                $this->checkDeadLinkOutside($url, $nc3_module_name, $nc3_block);
            }

        } elseif (is_null($scheme)) {
            // "{{CORE_BASE_URL}}/images/comp/textarea/titleicon/icon-weather9.gif" 等はここで処理

            // 内部リンク
            $this->checkDeadLinkInsideNc2($url, $nc3_module_name, $nc3_block);
        } else {
            // 対象外
            $this->putLinkCheck(3, $nc3_module_name . '|リンク切れチェック対象外', $url, $nc3_block);
        }
    }

    /**
     * 外部URLのリンク切れチェック
     */
    private function checkDeadLinkOutside($url, $nc3_module_name, $nc3_block): bool
    {
        // タイムアウト(秒)を変更
        ini_set('default_socket_timeout', 3);

        stream_context_set_default([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        // 独自でエラーをcatchする
        set_error_handler(function ($severity, $message) {
            throw new \Exception($message);
        });

        try {
            $headers = get_headers($url, true);
        } catch (\Exception $e) {
            // NG
            $this->putLinkCheck(3, $nc3_module_name . '|外部リンク|リンク切れ|' . $e->getMessage(), $url, $nc3_block);
            return false;

        } finally {
            // エラーハンドリングを元に戻す
            restore_error_handler();

            // タイムアウト(秒)を元に戻す
            ini_restore('default_socket_timeout');
        }


        $i = 0;
        while (!isset($headers[$i])) {
            if (stripos($headers[$i], "200") !== false) {
                // OK
                return true;
            } elseif (stripos($headers[$i], "301") !== false) {
                // 301リダイレクトのため、次要素の $header でチェック
            } elseif (stripos($headers[$i], "302") !== false) {
                // OK
                return true;
            } else {
                // NG
                $this->putLinkCheck(3, $nc3_module_name . '|外部リンク|リンク切れ|' . $headers[$i], $url, $nc3_block);
                return false;
            }
            $i++;
        }

        // NG. 基本ここには到達しない想定
        $this->putLinkCheck(3, $nc3_module_name . '|外部リンク|リンク切れ', $url, $nc3_block);
        return false;
    }

    /**
     * 内部URL(nc3)のリンク切れチェック
     */
    private function checkDeadLinkInsideNc2($url, $nc3_module_name = null, $nc3_block = null)
    {

        // >>> parse_url("http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=42&page_id=0&display_type=2#_active_center_42")
        // => [
        //      "scheme" => "http",
        //      "host" => "localhost",
        //      "port" => 8080,
        //      "path" => "/index.php",
        //      "query" => "action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=42&page_id=0&display_type=2",
        //      "fragment" => "_active_center_42",
        //    ]
        //
        // >>> parse_url("http://localhost:8080/新規カテゴリ2/新規カテゴリ2-1/新規カテゴリ2-1-1/")
        // => [
        //      "scheme" => "http",
        //      "host" => "localhost",
        //      "port" => 8080,
        //      "path" => "/新規カテゴリ2/新規カテゴリ2-1/新規カテゴリ2-1-1/",
        //    ]

        $nc3_base_url = $this->getMigrationConfig('basic', 'check_deadlink_nc3_base_url', '');

        // &amp; => & 等のデコード
        $check_url = htmlspecialchars_decode($url);
        // {{CORE_BASE_URL}} 置換
        $check_url = str_replace("{{CORE_BASE_URL}}", $nc3_base_url, $check_url);

        $check_url_path = parse_url($check_url, PHP_URL_PATH);
        $check_url_query = parse_url($check_url, PHP_URL_QUERY);

        // トップページ
        // ---------------------------------
        // http://localhost:8080/
        // http://localhost:8080
        // /
        // ./
        // /index.php
        // ./index.php
        // http://localhost:8080/index.php
        // ---------------------------------
        // (pathがトップページに該当するもの＋queryなし)は、OK扱いにする
        if (in_array($check_url_path, [null, '/', './', '/index.php', './index.php']) && is_null($check_url_query)) {
            return;
        }
        // ---------------------------------
        // http://localhost:8080/?lang=japanese
        // http://localhost:8080/?lang=english
        // http://localhost:8080/?lang=chinese
        // ---------------------------------
        // queryあり＋pathがトップページに該当するもの＋queryはlang１つだけ、はOK扱いにする
        parse_str($check_url_query, $check_url_query_array);
        if ($check_url_query_array) {
            $lang = MigrationUtils::getArrayValue($check_url_query_array, 'lang', null, null);
            if (in_array($check_url_path, ['/', './', '/index.php', './index.php']) && count($check_url_query_array) === 1 && $lang) {
                if (in_array($lang, ['japanese', 'english', 'chinese'])) {
                    // OK
                } else {
                    // NG
                    $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|lang値の間違い', $url, $nc3_block);
                }
                return;
            }
        }
        // 以下 check_url_path は値が存在する

        $check_url_array = explode('/', $check_url_path);
        // array_filter()でarrayの空要素削除. array_values()で添え字振り直し
        $check_url_array = array_values(array_filter($check_url_array));

        // NC3固定URLチェック. 例）mu4bpil7b-1312  explodeで0要素は必ずある.
        // ---------------------------------
        // (掲示板) http://localhost:8080/bb7flt02n-57/#_57
        // (汎用DB) http://localhost:8080/muwoibbvq-51/#_51
        // (日誌)   http://localhost:8080/jojo6xnz5-34/#_34
        // (日誌)   http://localhost:8080/index.php?key=jojo6xnz5-34#_34
        // ---------------------------------
        if (!isset($check_url_array[0])) {
            return;
        }
        $short_url_array = explode('-', $check_url_array[0]);
        $key = MigrationUtils::getArrayValue($check_url_query_array, 'key', null, null);
        $key_array = explode('-', $key);

        $nc3_abbreviate_url = Nc2AbbreviateUrl::
            where(function ($query) use ($short_url_array, $key_array) {
                $query->where('short_url', $short_url_array[0])
                    ->orWhere('short_url', $key_array[0]);
            })->first();

        if ($nc3_abbreviate_url) {
            if ($nc3_abbreviate_url->dir_name == 'multidatabase') {
                // 汎用DB

                // [Abbreviateurl]
                // block_sql = "SELECT {blocks}.block_id FROM {blocks},{multidatabase_block},{multidatabase_content},{abbreviate_url} WHERE {blocks}.block_id={multidatabase_block}.block_id AND {multidatabase_block}.multidatabase_id={multidatabase_content}.multidatabase_id AND {multidatabase_content}.multidatabase_id={abbreviate_url}.contents_id AND {multidatabase_content}.content_id={abbreviate_url}.unique_id"
                $abbreviate_block = Nc2Block::join('multidatabase_block', 'multidatabase_block.block_id', '=', 'blocks.block_id')
                    ->join('multidatabase_content', 'multidatabase_content.multidatabase_id', '=', 'multidatabase_block.multidatabase_id')
                    ->join('abbreviate_url', function ($join) {
                        $join->on('abbreviate_url.contents_id', '=', 'multidatabase_content.multidatabase_id')
                            ->whereColumn('abbreviate_url.unique_id', 'multidatabase_content.content_id');
                    })
                    ->first();

                // var_dump($abbreviate_block);
                if (!$abbreviate_block) {
                    // NG
                    $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|DBデータなし', $url, $nc3_block);
                }

            } elseif ($nc3_abbreviate_url->dir_name == 'bbs') {
                // 掲示板

                // [Abbreviateurl]
                // block_sql = "SELECT {blocks}.block_id FROM {blocks},{bbs_block},{bbs_post},{abbreviate_url} WHERE {blocks}.block_id={bbs_block}.block_id AND {bbs_block}.bbs_id={bbs_post}.bbs_id AND {bbs_post}.bbs_id={abbreviate_url}.contents_id AND {bbs_post}.post_id={abbreviate_url}.unique_id"
                $abbreviate_block = Nc2Block::join('bbs_block', 'bbs_block.block_id', '=', 'blocks.block_id')
                    ->join('bbs_post', 'bbs_post.bbs_id', '=', 'bbs_block.bbs_id')
                    ->join('abbreviate_url', function ($join) {
                        $join->on('abbreviate_url.contents_id', '=', 'bbs_post.bbs_id')
                            ->whereColumn('abbreviate_url.unique_id', 'bbs_post.post_id');
                    })
                    ->first();

                // var_dump($abbreviate_block);
                if (!$abbreviate_block) {
                    // NG
                    $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|DBデータなし', $url, $nc3_block);
                }

            } elseif ($nc3_abbreviate_url->dir_name == 'journal') {
                // 日誌

                // [Abbreviateurl]
                // block_sql = "SELECT {blocks}.block_id FROM {blocks},{journal_block},{journal_post},{abbreviate_url} WHERE {blocks}.block_id={journal_block}.block_id AND {journal_block}.journal_id={journal_post}.journal_id AND {journal_post}.journal_id={abbreviate_url}.contents_id AND {journal_post}.post_id={abbreviate_url}.unique_id"
                $abbreviate_block = Nc2Block::join('journal_block', 'journal_block.block_id', '=', 'blocks.block_id')
                    ->join('journal_post', 'journal_post.journal_id', '=', 'journal_block.journal_id')
                    ->join('abbreviate_url', function ($join) {
                        $join->on('abbreviate_url.contents_id', '=', 'journal_post.journal_id')
                            ->whereColumn('abbreviate_url.unique_id', 'journal_post.post_id');
                    })
                    ->first();

                // var_dump($abbreviate_block);
                if (!$abbreviate_block) {
                    // NG
                    $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|DBデータなし', $url, $nc3_block);
                }

            } else {
                $this->putError(3, '固定URLの未対応モジュール', "nc3_abbreviate_url->dir_name = " . $nc3_abbreviate_url->dir_name);
            }
            return;
        }

        // ページ＋mod_rewrite
        // ---------------------------------
        // http://localhost:8080/カウンター/
        // http://localhost:8080/group/グループ１/
        // http://localhost:8080/group/グループ１/サブグループ１/
        // http://localhost:8080/新規カテゴリ2/新規カテゴリ2-1/新規カテゴリ2-1-1/
        // ---------------------------------
        $check_page_permalink = trim($check_url_path, '/');
        if ($check_page_permalink) {
            // 頭とお尻の/を取り除いたpath + 空以外 の permalink でページの存在チェック
            $nc3_page = Nc2Page::where('permalink', trim($check_url_path, '/'))->where('permalink', '!=', '')->first();
            if ($nc3_page) {
                // ページデータあり. チェックOK
                return;
            }
        }

        // ページ（mod_rewriteなし. page_id指定）
        // ---------------------------------
        // http://localhost:8080/?page_id=16
        // ---------------------------------
        if ($check_url_query_array) {
            // >>> parse_str("page_id=16", $result)
            // >>> $result
            // => [
            //      "page_id" => "16",
            //    ]
            //
            // >>> parse_str("action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=42&page_id=0&display_type=2", $result)
            // >>> $result
            // => [
            //      "action" => "pages_view_main",
            //      "active_center" => "reservation_view_main_init",
            //      "reserve_details_id" => "19",
            //      "active_block_id" => "42",
            //      "page_id" => "0",
            //      "display_type" => "2",
            //    ]
            $page_id = MigrationUtils::getArrayValue($check_url_query_array, 'page_id', null, null);
            if ($page_id) {
                $nc3_page = Nc2Page::where('page_id', $page_id)->first();
                if ($nc3_page) {
                    // ページデータあり. チェックOK
                } else {
                    // NG
                    $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|ページデータなし', $url, $nc3_block);
                }
                return;
            }
        }

        // ダウンロードURL（ファイル・画像）
        // ---------------------------------
        // (画像) ./?action=common_download_main&upload_id=10
        // (添付) ./?action=common_download_main&upload_id=11
        // ---------------------------------
        if ($check_url_query_array) {
            $action = MigrationUtils::getArrayValue($check_url_query_array, 'action', null, null);
            if ($action == 'common_download_main') {
                $upload_id = MigrationUtils::getArrayValue($check_url_query_array, 'upload_id', null, null);
                if ($upload_id) {
                    $nc3_upload = Nc2Upload::where('upload_id', $upload_id)->first();
                    if ($nc3_upload) {
                        // アップロードデータあり. チェックOK
                    } else {
                        // NG
                        $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|アップロードデータなし', $url, $nc3_block);
                    }
                } else {
                    // NG
                    $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|common_download_mainでアップロードIDなし', $url, $nc3_block);
                }
                return;
            }
        }

        // ---------------------------------
        // 新着表示の各リンク
        // ---------------------------------
        // (中央エリアに表示)
        //   (action)active_center & active_block_id(任意)
        //
        //   (施設予約)       http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=42&page_id=0&display_type=2#_active_center_42
        //   (カレンダー)     http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42&active_block_id=11&page_id=1&display_type=5#_active_center_11
        //   active_block_idなしでも表示できる
        //   (施設予約)       http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&page_id=0&display_type=2#_active_center_42
        //   (カレンダー)     http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42&page_id=1&display_type=5#_active_center_11
        //   (検索)          ./index.php?action=pages_view_main&active_center=search_view_main_center
        //
        //   (手動で active_center 入れてリンク作成)
        //   (キャビネット)   http://localhost:8080/index.php?action=pages_view_main&active_center=cabinet_view_main_init&cabinet_id=2&folder_id=&block_id=69#_69
        // active_center でblock_id無でもトップページとして表示できる
        //   (キャビネット)   http://localhost:8080/index.php?action=pages_view_main&active_center=cabinet_view_main_init&cabinet_id=2&folder_id=#_69
        // active_center で存在しないblock_idは「データの取得失敗」エラーで表示できない
        //   (キャビネット)   http://localhost:8080/index.php?action=pages_view_main&active_center=cabinet_view_main_init&cabinet_id=2&folder_id=&block_id=699999#_69
        // ---------------------------------
        // (通常モジュール)
        //   (action)active_action & block_id(必須)
        //
        //   (キャビネット)   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&cabinet_id=2&folder_id=&block_id=69#_69
        //   (掲示板)        http://localhost:8080/index.php?action=pages_view_main&active_action=bbs_view_main_post&post_id=9&block_id=56#_56
        //   (フォトアルバム) http://localhost:8080/index.php?action=pages_view_main&active_action=photoalbum_view_main_init&album_id=1&block_id=68#photoalbum_album_68_1
        //
        //   (手動で block_id など修正してリンク作成)
        // active_action でblock_id無は「キャビネット削除された可能性」エラーで表示できない
        //   (キャビネット)   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&cabinet_id=2&folder_id=#_69
        // active_action で存在しないblock_idは「キャビネット削除された可能性」エラーで表示できない
        //   (キャビネット)   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&cabinet_id=2&folder_id=&block_id=699999#_69
        //
        //   (pages_view_main 内での active_action処理の調査)
        //   $blocks[$count]['full_path'] = BASE_URL.INDEX_FILE_NAME."?action=".$block['action_name']."&block_id=".$block['block_id']."&page_id=".$block['page_id'];    //絶対座標に変換
        //   (掲示板ページ表示)       http://localhost:8080/index.php?action=pages_view_main&active_action=bbs_view_main_post&post_id=9&block_id=56#_56
        //                           ↓
        //   (掲示板ブロックだけ表示)  http://localhost:8080/index.php?action=bbs_view_main_post&post_id=9&block_id=56&page_id=33#_56
        //   (掲示板ブロックだけ表示)  http://localhost:8080/index.php?action=bbs_view_main_post&post_id=9&block_id=56#_56    // page_idなくても表示できた
        //
        // ---------------------------------
        // 検索結果の各リンク
        // ---------------------------------
        // (お知らせ)   http://localhost:8080/index.php?action=pages_view_main&block_id=72&page_id=50&active_action=announcement_view_main_init#_72
        // (掲示板)     http://localhost:8080/index.php?action=pages_view_main&block_id=56&active_action=bbs_view_main_post&bbs_id=3&post_id=9#_56
        // (カレンダー) http://localhost:8080/index.php?action=pages_view_main&page_id=13&active_center=calendar_view_main_init&date=20210811&current_time=000000&display_type=5
        // ---------------------------------
        //
        // NC3モジュール名| 新着 | 検索 | リンクチェック対象
        // お知らせ         o      o     o
        // アンケート       o      -     o
        // Todo            o      o     o
        // カレンダー       o      o     o
        // 掲示板           o      o     o
        // キャビネット     o      o     o
        // レポート         o      o     o
        // 小テスト         o      -     o
        // 施設予約         o      o     o
        // 日誌             o      o     o
        // フォトアルバム    o      -     o
        // 汎用データベース  o      o     o
        // 回覧板           o      o     o
        // FAQ              -      o     o
        // 検索             -      -     o
        // ---------------------------------

        if ($check_url_query_array) {

            $action = MigrationUtils::getArrayValue($check_url_query_array, 'action', null, null);
            if ($action == 'pages_view_main') {

                // (通常モジュール)
                //   (action)active_action & block_id(必須)         例：掲示板, お知らせ, キャビネット等
                // (中央エリアに表示)
                //   (action)active_center & active_block_id(任意)  例：カレンダー, 施設予約, 検索
                $active_action = MigrationUtils::getArrayValue($check_url_query_array, 'active_action', null, null);
                $active_center = MigrationUtils::getArrayValue($check_url_query_array, 'active_center', null, null);

                if ($active_action) {
                    // block存在チェック(必須)
                    $block_id = MigrationUtils::getArrayValue($check_url_query_array, 'block_id', null, null);
                    $check_nc3_block = Nc2Block::where('block_id', $block_id)->first();
                    if ($check_nc3_block) {
                        // OK
                    } else {
                        // NG
                        $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|blockデータなし', $url, $nc3_block);
                        return;
                    }
                }

                if ($active_action || $active_center) {
                    // page_id存在チェック(任意)
                    $page_id = MigrationUtils::getArrayValue($check_url_query_array, 'page_id', null, null);
                    if ($page_id) {
                        $check_nc3_page = Nc2Page::where('page_id', $page_id)->first();
                        if ($check_nc3_page) {
                            // OK
                        } else {
                            // NG
                            $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|pageデータなし', $url, $nc3_block);
                            return;
                        }
                    }
                }

                // (通常モジュール) active_action
                // --------------------------------
                if ($active_action == 'bbs_view_main_post') {
                    // (掲示板パラメータ)
                    //   block_id 必須
                    //   post_id  必須
                    //   bbs_id   任意. あれば存在チェック
                    //
                    // (掲示板-新着)
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=bbs_view_main_post&post_id=9&block_id=56#_56
                    // block_idを存在しないIDにすると、「該当ページに配置してある掲示板が削除された可能性があります。」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=bbs_view_main_post&post_id=9&block_id=56999999999999#_56
                    // post_idを存在しないIDにすると、ページは開けて、掲示板の箇所が「入力値が不正です。不正にアクセスされた可能性があります。」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=bbs_view_main_post&post_id=99999999999999&block_id=56#_56
                    //
                    // (掲示板-検索)
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=56&active_action=bbs_view_main_post&bbs_id=3&post_id=9#_56
                    // bbs_idなくても表示できた
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=56&active_action=bbs_view_main_post&post_id=9#_56
                    // bbs_idを存在しないIDにすると、ページは開けて、掲示板の箇所が「入力値が不正です。不正にアクセスされた可能性があります。」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=56&active_action=bbs_view_main_post&bbs_id=39999999&post_id=9#_56
                    //
                    // (掲示板-active_center, 手動でリンク作成を想定)
                    //   http://localhost:8080/index.php?action=pages_view_main&active_block_id=56&active_center=bbs_view_main_post&bbs_id=3&post_id=9#_56
                    // active_block_idを存在しないID, active_block_id設定なしにしても、詳細表示部分が空白なだけで、エラーにはならない。
                    //   http://localhost:8080/index.php?action=pages_view_main&active_block_id=56999999999&active_center=bbs_view_main_post&bbs_id=3&post_id=9#_56
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=bbs_view_main_post&bbs_id=3&post_id=9#_56

                    // bbs_post存在チェック
                    $post_id = MigrationUtils::getArrayValue($check_url_query_array, 'post_id', null, null);
                    $check_nc3_bbs_post = Nc2BbsPost::where('post_id', $post_id)->first();
                    if ($check_nc3_bbs_post) {
                        // OK
                    } else {
                        // NG
                        $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|bbs_postデータなし', $url, $nc3_block);
                        return;
                    }

                    // bbs_id存在チェック(任意)
                    $bbs_id = MigrationUtils::getArrayValue($check_url_query_array, 'bbs_id', null, null);
                    if ($bbs_id) {
                        $check_nc3_bbs = Nc2Bbs::where('bbs_id', $bbs_id)->first();
                        if ($check_nc3_bbs) {
                            // OK
                        } else {
                            // NG
                            $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|bbsデータなし', $url, $nc3_block);
                            return;
                        }
                    }

                    // OK
                    return;

                } elseif ($active_action == 'announcement_view_main_init') {
                    // (お知らせパラメータ)
                    //   block_id 必須
                    //   page_id  任意. あれば存在チェック
                    //
                    // (お知らせ-新着)
                    // http://localhost:8080/index.php?action=pages_view_main&&block_id=72#_72
                    //
                    // (お知らせ-検索)
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=72&page_id=50&active_action=announcement_view_main_init#_72
                    // page_idなしでも表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=72&active_action=announcement_view_main_init#_72
                    // page_idを存在しないIDにすると「データ取得に失敗」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=72&page_id=50999999&active_action=announcement_view_main_init#_72
                    // block_idがない or 存在しないIDにすると、「該当ページに配置してある掲示板が削除された可能性があります。」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&page_id=50&active_action=announcement_view_main_init#_72
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=7299999&page_id=50&active_action=announcement_view_main_init#_72

                    // OK
                    return;

                } elseif ($active_action == 'journal_view_main_detail') {
                    // (日誌パラメータ)
                    //   block_id       必須
                    //   post_id        必須
                    //   comment_flag   任意. (チェック不要). 1:コメント入力あり 1以外:コメント入力なし
                    //
                    // (日誌-新着)
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=journal_view_main_detail&post_id=4&comment_flag=1&block_id=34#_34
                    // post_idなし or 存在しないIDにすると「記事は存在しいません」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=journal_view_main_detail&comment_flag=1&block_id=34#_34
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=journal_view_main_detail&post_id=49999&comment_flag=1&block_id=34#_34
                    // comment_flagなし or 変な値でも記事表示できる＋コメント入力なし
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=journal_view_main_detail&post_id=4&block_id=34#_34
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=journal_view_main_detail&post_id=4&comment_flag=199999&block_id=34#_34
                    //
                    // (日誌-検索)
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=34&active_action=journal_view_main_detail&post_id=4#_34

                    // journal_post存在チェック
                    $post_id = MigrationUtils::getArrayValue($check_url_query_array, 'post_id', null, null);
                    $check_nc3_journal_post = Nc2JournalPost::where('post_id', $post_id)->first();
                    if ($check_nc3_journal_post) {
                        // OK
                    } else {
                        // NG
                        $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|journal_postデータなし', $url, $nc3_block);
                        return;
                    }

                    // OK
                    return;

                } elseif ($active_action == 'multidatabase_view_main_detail') {
                    // (汎用DBパラメータ)
                    //   block_id          必須
                    //   multidatabase_id  必須
                    //   content_id        必須
                    //
                    // (汎用DB-新着)
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=multidatabase_view_main_detail&content_id=4&multidatabase_id=1&block_id=51#_51
                    // multidatabase_idなし or IDが存在しないと「入力値が不正」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=multidatabase_view_main_detail&content_id=4&block_id=51#_51
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=multidatabase_view_main_detail&content_id=4&multidatabase_id=19999&block_id=51#_51
                    // content_idなし or IDが存在しないとコンテンツが存在しません」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=multidatabase_view_main_detail&multidatabase_id=1&block_id=51#_51
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=multidatabase_view_main_detail&content_id=499999&multidatabase_id=1&block_id=51#_51
                    //
                    // (汎用DB-検索)
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=51&active_action=multidatabase_view_main_detail&content_id=4&multidatabase_id=1&block_id=51#_51

                    // multidatabase存在チェック
                    $multidatabase_id = MigrationUtils::getArrayValue($check_url_query_array, 'multidatabase_id', null, null);
                    $check_nc3_multidatabase = Nc2Multidatabase::where('multidatabase_id', $multidatabase_id)->first();
                    if ($check_nc3_multidatabase) {
                        // OK
                    } else {
                        // NG
                        $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|multidatabaseデータなし', $url, $nc3_block);
                        return;
                    }

                    // multidatabase_content存在チェック
                    $content_id = MigrationUtils::getArrayValue($check_url_query_array, 'content_id', null, null);
                    $check_nc3_multidatabase_content = Nc2MultidatabaseContent::where('content_id', $content_id)->first();
                    if ($check_nc3_multidatabase_content) {
                        // OK
                    } else {
                        // NG
                        $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|multidatabase_contentデータなし', $url, $nc3_block);
                        return;
                    }

                    // OK
                    return;

                } elseif ($active_action == 'cabinet_view_main_init') {
                    // (キャビネットパラメータ)
                    //   block_id          必須
                    //   cabinet_id        任意.
                    //   folder_id         任意.
                    //
                    // (キャビネット-新着)
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&cabinet_id=2&folder_id=&block_id=69#_69
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&block_id=69#_69
                    // cabinet_idなしは表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&folder_id=&block_id=69#_69
                    // cabinet_idのIDが存在しないと「公開されているキャビネットはありません」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&cabinet_id=2999999&folder_id=&block_id=69#_69
                    // folder_idなしは表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&cabinet_id=2&block_id=69#_69
                    // folder_idのIDが存在しないと「権限が不正」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&cabinet_id=2&folder_id=99999&block_id=69#_69
                    //
                    // (キャビネット-検索)
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=69&active_action=cabinet_view_main_init&cabinet_id=2&folder_id=0#_69

                    // cabinet_manage存在チェック(任意)
                    $cabinet_id = MigrationUtils::getArrayValue($check_url_query_array, 'cabinet_id', null, null);
                    if ($cabinet_id) {
                        $check_nc3_cabinet_manage = Nc2CabinetManage::where('cabinet_id', $cabinet_id)->first();
                        if ($check_nc3_cabinet_manage) {
                            // OK
                        } else {
                            // NG
                            $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|cabinet_manageデータなし', $url, $nc3_block);
                            return;
                        }
                    }

                    // folder_id(=file_id)のcabinet_file存在チェック(任意)
                    $folder_id = MigrationUtils::getArrayValue($check_url_query_array, 'folder_id', null, null);
                    if ($folder_id) {
                        $check_nc3_cabinet_file = Nc2CabinetFile::where('file_id', $folder_id)->first();
                        if ($check_nc3_cabinet_file) {
                            // OK
                        } else {
                            // NG
                            $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|cabinet_fileデータなし.folder_id=file_id', $url, $nc3_block);
                            return;
                        }
                    }

                    // OK
                    return;

                } elseif ($active_action == 'faq_view_main_init') {
                    // (FAQパラメータ)
                    //   block_id          必須
                    //   question_id       任意.（チェック不要）
                    //
                    // (FAQ-検索-のみ)
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=35&active_action=faq_view_main_init&question_id=4#_faq_answer_4
                    // question_idなし or 存在しないIDでも表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=35&active_action=faq_view_main_init#_faq_answer_4
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=35&active_action=faq_view_main_init&question_id=4999#_faq_answer_4

                    // OK
                    return;

                } elseif ($active_action == 'photoalbum_view_main_init') {
                    // (フォトアルバムパラメータ)
                    //   block_id          必須
                    //   album_id          任意.（チェック不要）
                    //
                    // (フォトアルバム-新着-のみ)
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=photoalbum_view_main_init&album_id=1&block_id=68#photoalbum_album_68_1
                    // album_idなし or 存在しないIDでも表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=photoalbum_view_main_init&block_id=68#photoalbum_album_68_1
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=photoalbum_view_main_init&album_id=19999999999999&block_id=68#photoalbum_album_68_1

                    // OK
                    return;

                } elseif ($active_action == 'assignment_view_main_whatsnew' || $active_action == 'assignment_view_main_init') {
                    // (レポートパラメータ)
                    //   block_id          必須
                    //   (新着：assignment_view_main_whatsnew) assignment_id     必須
                    //   (検索：assignment_view_main_init)     assignment_id     任意.
                    //
                    // (レポート-新着)
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=assignment_view_main_whatsnew&assignment_id=1&block_id=74#_74
                    // assignment_idなし or 存在しないIDだと「入力値が不正」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=assignment_view_main_whatsnew&block_id=74#_74
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=assignment_view_main_whatsnew&assignment_id=1999999&block_id=74#_74
                    //
                    // (レポート-検索)
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=74&active_action=assignment_view_main_init&assignment_id=1#_74
                    // assignment_idなしでも表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=74&active_action=assignment_view_main_init#_74
                    // assignment_idで存在しないIDだと「公開されている課題はありません」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=74&active_action=assignment_view_main_init&assignment_id=1999999#_74


                    if ($active_action == 'assignment_view_main_whatsnew') {
                        // (レポート-新着)
                        // assignment存在チェック（必須）
                        $assignment_id = MigrationUtils::getArrayValue($check_url_query_array, 'assignment_id', null, null);
                        $check_nc3_assignment = Nc2Assignment::where('assignment_id', $assignment_id)->first();
                        if ($check_nc3_assignment) {
                            // OK
                        } else {
                            // NG
                            $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|assignmentデータなし', $url, $nc3_block);
                            return;
                        }

                    } elseif ($active_action == 'assignment_view_main_init') {
                        // (レポート-検索)
                        // assignment存在チェック（任意）
                        $assignment_id = MigrationUtils::getArrayValue($check_url_query_array, 'assignment_id', null, null);
                        if ($assignment_id) {
                            $check_nc3_assignment = Nc2Assignment::where('assignment_id', $assignment_id)->first();
                            if ($check_nc3_assignment) {
                                // OK
                            } else {
                                // NG
                                $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|assignmentデータなし', $url, $nc3_block);
                                return;
                            }
                        }
                    }

                    // OK
                    return;

                } elseif ($active_action == 'questionnaire_view_main_whatsnew') {
                    // (アンケートパラメータ)
                    //   block_id          必須
                    //   questionnaire_id  必須
                    //
                    // (アンケート-新着-のみ)
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=questionnaire_view_main_whatsnew&questionnaire_id=1&block_id=75#_75
                    // questionnaire_idなし or 存在しないIDだと「入力値が不正」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=questionnaire_view_main_whatsnew&block_id=75#_75
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=questionnaire_view_main_whatsnew&questionnaire_id=19999999&block_id=75#_75

                    // questionnaire存在チェック
                    $questionnaire_id = MigrationUtils::getArrayValue($check_url_query_array, 'questionnaire_id', null, null);
                    $check_nc3_questionnaire = Nc2Questionnaire::where('questionnaire_id', $questionnaire_id)->first();
                    if ($check_nc3_questionnaire) {
                        // OK
                    } else {
                        // NG
                        $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|questionnaireデータなし', $url, $nc3_block);
                        return;
                    }

                    // OK
                    return;

                } elseif ($active_action == 'quiz_view_main_whatsnew') {
                    // (小テストパラメータ)
                    //   block_id          必須
                    //   quiz_id           必須
                    //
                    // (小テスト-新着-のみ)
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=quiz_view_main_whatsnew&quiz_id=1&block_id=77#_77
                    // quiz_idなし or 存在しないIDだと「入力値が不正」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=quiz_view_main_whatsnew&block_id=77#_77
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=quiz_view_main_whatsnew&quiz_id=1999999&block_id=77#_77

                    // quiz存在チェック
                    $quiz_id = MigrationUtils::getArrayValue($check_url_query_array, 'quiz_id', null, null);
                    $check_nc3_quiz = Nc2Quiz::where('quiz_id', $quiz_id)->first();
                    if ($check_nc3_quiz) {
                        // OK
                    } else {
                        // NG
                        $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|quizデータなし', $url, $nc3_block);
                        return;
                    }

                    // OK
                    return;

                } elseif ($active_action == 'todo_view_main_init') {
                    // (Todoパラメータ)
                    //   block_id          必須
                    //   todo_id           任意
                    //   page_id           任意
                    //
                    // (Todo-新着)
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=todo_view_main_init&todo_id=11&block_id=76#_76
                    // todo_idなし だと表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=todo_view_main_init&block_id=76#_76
                    // todo_idが存在しないID だと「公開されているTodoリストはありません」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=todo_view_main_init&todo_id=11999999&block_id=76#_76
                    //
                    // (Todo-検索)
                    //   http://localhost:8080/index.php?action=pages_view_main&block_id=76&page_id=55&active_action=todo_view_main_init#_76

                    // todo存在チェック（任意）
                    $todo_id = MigrationUtils::getArrayValue($check_url_query_array, 'todo_id', null, null);
                    if ($todo_id) {
                        $check_nc3_todo = Nc2Todo::where('todo_id', $todo_id)->first();
                        if ($check_nc3_todo) {
                            // OK
                        } else {
                            // NG
                            $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|todoデータなし', $url, $nc3_block);
                            return;
                        }
                    }

                    // OK
                    return;

                } elseif ($active_action == 'circular_view_main_detail') {
                    // (回覧板パラメータ)
                    //   block_id          必須
                    //   circular_id       必須
                    //   page_id           任意
                    //   room_id           任意（チェック不要）
                    //
                    // (回覧板-新着)
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=circular_view_main_detail&circular_id=2&page_id=53&block_id=78#_78
                    // circular_idなし or 存在しないID だと「既に削除されています」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=circular_view_main_detail&page_id=53&block_id=78#_78
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=circular_view_main_detail&circular_id=299999&page_id=53&block_id=78#_78
                    //
                    // (回覧板-検索)
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=circular_view_main_detail&block_id=78&room_id=1&circular_id=2#_78
                    // room_idなし or 存在しないID でも表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=circular_view_main_detail&block_id=78&circular_id=2#_78
                    //   http://localhost:8080/index.php?action=pages_view_main&active_action=circular_view_main_detail&block_id=78&room_id=19999&circular_id=2#_78

                    // circular存在チェック
                    $circular_id = MigrationUtils::getArrayValue($check_url_query_array, 'circular_id', null, null);
                    $check_nc3_circular = Nc2Circular::where('circular_id', $circular_id)->first();
                    if ($check_nc3_circular) {
                        // OK
                    } else {
                        // NG
                        $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|circularデータなし', $url, $nc3_block);
                        return;
                    }

                    // OK
                    return;
                }


                // (中央エリアに表示) active_center
                // --------------------------------
                if ($active_center == 'search_view_main_center') {
                    // （検索-初期インストール配置のヘッダー検索お知らせ）
                    //   ./index.php?action=pages_view_main&active_center=search_view_main_center
                    // （検索-active_action, 手動でリンク作成を想定
                    //   → 対応しない
                    //   block_idがないと、「該当ページに配置してある検索が削除された可能性があります。」エラー
                    //   ./index.php?action=pages_view_main&active_action=search_view_main_center

                    // OK
                    return;

                } elseif ($active_center == 'reservation_view_main_init') {
                    // (施設予約-新着)
                    //   active_block_id      任意. (チェック不要)
                    //   page_id              任意. あれば存在チェック
                    //   reserve_details_id   任意. (チェック不要)
                    //   display_type         任意. あれば値チェック=1|2|3
                    //   reserve_id           任意. (チェック不要)
                    //
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=42&page_id=0&display_type=2#_active_center_42
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init
                    // active_block_idなしでも, 存在しないIDでも表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&page_id=0&display_type=2#_active_center_42
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=4299999999999999&page_id=0&display_type=2#_active_center_42
                    // page_idなしでも表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&display_type=2#_active_center_42
                    // page_idを存在しないIDにすると「データ取得に失敗」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&page_id=9999999&display_type=2#_active_center_42
                    // reserve_details_idなし or 存在しないIDでも表示できる. あれば該当日の一覧を表示
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=1999999999&active_block_id=42&page_id=0&display_type=2#_active_center_42
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&active_block_id=42&page_id=0&display_type=2#_active_center_42
                    // display_typeなしでも表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=42&page_id=0#_active_center_42
                    // display_typeの「入力値が不正です」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=42&page_id=0&display_type=999#_active_center_42
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=42&page_id=0&display_type=4#_active_center_42
                    //
                    // >>> parse_str("action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=42&page_id=0&display_type=2", $result)
                    // >>> $result
                    // => [
                    //      "action" => "pages_view_main",
                    //      "active_center" => "reservation_view_main_init",
                    //      "reserve_details_id" => "19",
                    //      "active_block_id" => "42",
                    //      "page_id" => "0",
                    //      "display_type" => "2",
                    //    ]
                    //
                    // (施設予約-検索)
                    //   http://localhost:8080/index.php?action=pages_view_main&page_id=13&active_center=reservation_view_main_init&reserve_id=74
                    // reserve_id が存在しないIDでも表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&page_id=13&active_center=reservation_view_main_init&reserve_id=74999999

                    // display_typeの有効値チェック(任意)
                    $display_type = MigrationUtils::getArrayValue($check_url_query_array, 'display_type', null, null);
                    if ($display_type) {
                        if ((int)$display_type <= 3) {
                            // OK 1|2|3, ※ イレギュラーだけど0,-1,-2...でも表示可
                        } else {
                            // NG
                            $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|display_type対象外', $url, $nc3_block);
                            return;
                        }
                    }

                    // OK
                    return;

                } elseif ($active_center == 'calendar_view_main_init') {
                    // (カレンダー-新着)
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42&active_block_id=11&page_id=1&display_type=5#_active_center_11
                    // page_idなしでも表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42&active_block_id=11&display_type=5#_active_center_11
                    // page_idを存在しないIDにすると「データ取得に失敗」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42&active_block_id=11&page_id=19999999999&display_type=5#_active_center_11
                    // display_typeなしでも表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42&active_block_id=11&page_id=1#_active_center_11
                    // display_type=0|-1|-2...は表示できちゃう
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42&active_block_id=11&page_id=1&display_type=-1#_active_center_11
                    // display_typeの「入力値が不正です」エラー
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42&active_block_id=11&page_id=1&display_type=8#_active_center_11
                    // plan_id なし or ID存在しなくても表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&active_block_id=11&page_id=1&display_type=5#_active_center_11
                    //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42999&active_block_id=11&page_id=1&display_type=5#_active_center_11
                    //
                    // http://localhost:8080/index.php?
                    //   - action=pages_view_main
                    //   - active_center=calendar_view_main_init
                    //   - plan_id=42           任意. (チェック不要)
                    //   - active_block_id=11
                    //   - page_id=1            上にチェック処理あり
                    //   o display_type=5       任意. あれば値チェック=1～8
                    //   - #_active_center_11
                    //
                    // (カレンダー-検索)
                    //   http://localhost:8080/index.php?action=pages_view_main&page_id=13&active_center=calendar_view_main_init&date=20210811&current_time=000000&display_type=5
                    // date|current_time なし or 値が変な値でも表示できる
                    //   http://localhost:8080/index.php?action=pages_view_main&page_id=13&active_center=calendar_view_main_init&display_type=5
                    //   http://localhost:8080/index.php?action=pages_view_main&page_id=13&active_center=calendar_view_main_init&date=202108119999&current_time=0000009999&display_type=5
                    //   http://localhost:8080/index.php?action=pages_view_main&page_id=13&active_center=calendar_view_main_init&date=20210811&display_type=5
                    //
                    // http://localhost:8080/index.php?
                    //   - date=20210811        任意. (チェック不要)
                    //   - current_time=000000  任意. (チェック不要)
                    //   o display_type=5       任意. あれば値チェック=1～8

                    // display_typeの有効値チェック(任意)
                    $display_type = MigrationUtils::getArrayValue($check_url_query_array, 'display_type', null, null);
                    if ($display_type) {
                        if ((int)$display_type <= 8) {
                            // OK ※イレギュラーだけど0,-1,-2...でも表示可
                        } else {
                            // NG
                            $this->putLinkCheck(3, $nc3_module_name . '|内部リンク|display_type対象外', $url, $nc3_block);
                            return;
                        }
                    }

                    // OK
                    return;
                }

            }
        }

        // 外部リンク
        // 内部リンクの直ファイル指定の存在チェック。例）http://localhost:8080/htdocs/install/logo.gif
        // if ($this->checkDeadLinkOutside($check_url, $nc3_module_name, $nc3_block)) {
        //     // 外部OK=移行対象外 (link_checkログには吐かない)
        //     $this->putMonitor(3, $nc3_module_name . '|内部リンク＋外部リンクチェックOK|移行対象外URL', $url, $nc3_block);
        // } else {
        //     // 外部NG
        //     $header = get_headers($check_url, true);
        //     $this->putLinkCheck(3, $nc3_module_name . '|内部リンク＋外部リンクチェックNG|未対応URL|' . $header[0], $url, $nc3_block);
        // }

        // 移行対象外 (link_checkログには吐かない)
        $this->putMonitor(3, $nc3_module_name . '|内部リンク|移行対象外URL', $url, $nc3_block);
    }

    /**
     * エクスポート・インポートの初期処理
     */
    private function migrationInit()
    {
        if (File::exists(config('migration.MIGRATION_CONFIG_PATH'))) {
            // 手動で設置のmigration config がある場合
            $this->migration_config = parse_ini_file(config('migration.MIGRATION_CONFIG_PATH'), true);
        } else {
            $this->putError(3, 'migration configのiniが見つかりません。');
        }

        // uploads のini ファイルの読み込み
        if (Storage::exists($this->getImportPath('uploads/uploads.ini'))) {
            $this->uploads_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('uploads/uploads.ini'), true);
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
    private function hasMigrationConfig($section, $key, $value = null)
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
     * 日時の関数(NC3)
     */
    private function getCCDatetime(?Carbon $utc_datetime): ?Carbon
    {
        if (empty($utc_datetime)) {
            return null;
        }

        // 9時間足す
        return $utc_datetime->addHours(9);
    }

    /**
     * NC3ユーザIDからNC3ログインID取得
     */
    private function getNc3LoginIdFromNc3UserId(Collection $nc3_users, ?int $nc3_user_id): ?string
    {
        $nc3_user = $nc3_users->firstWhere('id', $nc3_user_id) ?? new Nc3User();
        return $nc3_user->username;
    }

    /**
     * NC3ユーザIDからNC3ハンドル取得
     */
    private function getNc3HandleFromNc3UserId(Collection $nc3_users, ?int $nc3_user_id): ?string
    {
        $nc3_user = $nc3_users->firstWhere('id', $nc3_user_id) ?? new Nc3User();
        return $nc3_user->handlename;
    }

    /**
     * ommit 設定を確認
     */
    private function isOmmit($section, $arg_name, $check_id)
    {
        // 対象外のブロックがあれば加味する。
        $ommit_settings = $this->getMigrationConfig($section, $arg_name);
        if (!empty($ommit_settings) && in_array($check_id, $ommit_settings)) {
            return true;
        }
        return false;
    }

    /**
     * ID のゼロ埋め
     */
    private function zeroSuppress($id, $size = 4)
    {
        return MigrationUtils::zeroSuppress($id, $size);
    }

    /**
     * 多言語化判定（日本語）NC3
     */
    private function checkLangDirnameJpn($language_id)
    {
        /* 日本語 */
        if ($language_id == 2) {
            return true;
        }
        return false;
    }

    /**
     * NC3 からデータをエクスポート
     *
     * 動かし方
     *
     * 【.env で以下のNC3 用の定義を設定】
     *
     * NC3_DB_CONNECTION=mysql
     * NC3_DB_HOST=127.0.0.1
     * NC3_DB_PORT=3306
     * NC3_DB_DATABASE=xxxxxx
     * NC3_DB_USERNAME=xxxxxx
     * NC3_DB_PASSWORD=xxxxxx
     * NC3_DB_PREFIX=nc3_ (例)
     *
     * 【実行コマンド】
     * php artisan command:exportNc3
     *
     * 【移行データ】
     * storage\app\migration にNC3 をエクスポートしたデータが入ります。
     *
     * 【ログ】
     * storage\app\migration\logs\*.log
     *
     * 【画像】
     * src にhttp 指定などで、移行しなかった画像はログに出力
     */
    private function exportNc3($target, $target_plugin, $redo = null)
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

        $this->putMonitor(3, "Start exportNc3.");

        // 移行の初期処理
        $this->migrationInit();

        // uploads_path の取得
        $uploads_path = config('migration.NC3_EXPORT_UPLOADS_PATH');

        // uploads_path の最後に / がなければ追加
        if (!empty($uploads_path) && mb_substr($uploads_path, -1) != '/') {
            $uploads_path = $uploads_path . '/';
        }

        // サイト基本設定のエクスポート
        if ($this->isTarget('nc3_export', 'basic')) {
            $this->nc3ExportBasic();
        }

        // アップロード・データとファイルのエクスポート
        if ($this->isTarget('nc3_export', 'uploads')) {
            $this->nc3ExportUploads($uploads_path, $redo);
        }

        // ユーザデータのエクスポート
        if ($this->isTarget('nc3_export', 'users')) {
            $this->nc3ExportUsers($redo);
        }

        //////////////////
        // [TODO] まだ
        //////////////////
        // // ルームデータのエクスポート
        // if ($this->isTarget('nc3_export', 'groups')) {
        //     $this->nc3ExportRooms($redo);
        // }

        // // NC3 日誌（journal）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'blogs')) {
        //     $this->nc3ExportJournal($redo);
        // }

        // // NC3 掲示板（bbs）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'bbses')) {
        //     $this->nc3ExportBbs($redo);
        // }

        // // NC3 汎用データベース（multidatabase）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'databases')) {
        //     $this->nc3ExportMultidatabase($redo);
        // }

        // // NC3 登録フォーム（registration）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'forms')) {
        //     $this->nc3ExportRegistration($redo);
        // }

        // // NC3 FAQ（faq）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'faqs')) {
        //     $this->nc3ExportFaq($redo);
        // }

        // // NC3 リンクリスト（linklist）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'linklists')) {
        //     $this->nc3ExportLinklist($redo);
        // }

        // // NC3 新着情報（whatsnew）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'whatsnews')) {
        //     $this->nc3ExportWhatsnew($redo);
        // }

        // // NC3 キャビネット（cabinet）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'cabinets')) {
        //     $this->nc3ExportCabinet($redo);
        // }

        // // NC3 カウンター（counter）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'counters')) {
        //     $this->nc3ExportCounter($redo);
        // }

        // // NC3 カレンダー（calendar）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'calendars')) {
        //     $this->nc3ExportCalendar($redo);
        // }

        // // NC3 スライダー（slides）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'slideshows')) {
        //     $this->nc3ExportSlides($redo);
        // }

        // // NC3 シンプル動画（simplemovie）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'simplemovie')) {
        //     $this->nc3ExportSimplemovie($redo);
        // }

        // // NC3 施設予約（reservation）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'reservations')) {
        //     $this->nc3ExportReservation($redo);
        // }

        // // NC3 フォトアルバム（photoalbum）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'photoalbums')) {
        //     $this->nc3ExportPhotoalbum($redo);
        // }

        // // NC3 固定リンク（abbreviate_url）データのエクスポート
        // if ($this->isTarget('nc3_export', 'plugins', 'blogs') ||
        //     $this->isTarget('nc3_export', 'plugins', 'databases') ||
        //     $this->isTarget('nc3_export', 'plugins', 'bbses')) {
        //     $this->nc3ExportAbbreviateUrl($redo);
        // }

        // pages データとファイルのエクスポート
        if ($this->isTarget('nc3_export', 'pages')) {
            // データクリア
            if ($redo === true) {
                MigrationMapping::where('target_source_table', 'nc3_pages')->delete();
                // 移行用ファイルの削除
                Storage::deleteDirectory($this->getImportPath('pages/'));
                // pagesエクスポート関連のnc3Frame()でmenuのエクスポートで@insert配下ディレクトリに出力しているため、同ディレクトリを削除
                // ⇒ 移行後用の新ページを作成したのを置いておき、移行後にinsertするような使い方だから削除されると微妙なため、コメントアウト
                // Storage::deleteDirectory($this->getImportPath('pages/', '@insert/'));
            }

            // NC3 トップページ
            // -- 1件目取得
            // select Page.*
            // from
            // nc3_pages Page,
            // nc3_rooms Room x
            // where Page.parent_id is not null x
            // and Page.room_id = Room.id x
            // and Room.space_id = 2 -- 2:public　x
            // order by Page.sort_key asc; x
            // see) https://github.com/NetCommons3/NetCommons/blob/d1c6871b4a00dccefe3cae278143c0015fcad9ce/Lib/Current/CurrentLibPage.php#L239-L260
            $nc3_top_page = Nc3Page::
                select('pages.id')
                ->join('rooms', function ($join) {
                    $join->on('rooms.id', '=', 'pages.room_id')
                        ->where('rooms.space_id', Nc3Space::PUBLIC_SPACE_ID);
                })
                ->whereNotNull('pages.parent_id')
                ->orderBy('pages.sort_key')
                ->first();

            // NC3 のページデータ
            $nc3_pages_query = Nc3Page::
                select('pages.*', 'rooms.space_id', 'rooms.page_id_top', 'pages_languages.name as page_name', 'pages_languages.language_id')
                ->join('rooms', function ($join) {
                    $join->on('rooms.id', '=', 'pages.room_id')
                        ->where('rooms.space_id', '!=', Nc3Space::PRIVATE_SPACE_ID); // プライベートルーム以外
                })
                ->join('pages_languages', function ($join) {
                    $join->on('pages_languages.page_id', '=', 'pages.id');
                })
                ->join('languages', function ($join) {
                    $join->on('languages.id', '=', 'pages_languages.language_id')
                        ->where('languages.is_active', 1);  // 使用言語（日本語・英語）で有効な言語を取得
                })
                ->whereNotNull('pages.root_id');

            // ページ指定の有無
            if ($this->getMigrationConfig('pages', 'nc3_export_where_page_ids')) {
                $nc3_pages_query->whereIn('id', $this->getMigrationConfig('pages', 'nc3_export_where_page_ids'));
            }

            // 対象外ページ指定の有無
            if ($this->getMigrationConfig('pages', 'nc3_export_ommit_page_ids')) {
                $nc3_pages_query->whereNotIn('id', $this->getMigrationConfig('pages', 'nc3_export_ommit_page_ids'));
            }

            $nc3_pages = $nc3_pages_query
                ->orderBy('pages_languages.language_id')
                ->orderBy('pages.sort_key')
                ->orderBy('rooms.sort_key')
                ->get();

            // NC3 のページID を使うことにした。
            //// 新規ページ用のインデックス
            //// 新規ページは _99 のように _ 付でページを作っておく。（_ 付はデータ作成時に既存page_id の続きで採番する）

            // エクスポートしたページフォルダは連番にした。
            $new_page_index = 0;

            // ページのループ
            $this->putMonitor(1, "Page loop.");
            foreach ($nc3_pages as $nc3_sort_page) {
                $this->putMonitor(3, "Page", "page_id = " . $nc3_sort_page->id);

                $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
                // ルーム指定があれば、指定されたルームのみ処理する。
                if (empty($room_ids)) {
                    // ルーム指定なし。全データの移行
                } elseif (!empty($room_ids) && in_array($nc3_sort_page->room_id, $room_ids)) {
                    // ルーム指定あり。指定ルームに合致する。
                } else {
                    // ルーム指定あり。条件に合致せず。移行しない。
                    continue;
                }

                // ページ設定の保存用変数
                $membership_flag = null;
                if ($nc3_sort_page->space_id == Nc3Space::COMMUNITY_SPACE_ID) {
                    // 「すべての会員をデフォルトで参加させる」
                    if ($nc3_sort_page->default_participation == 1) {
                        $membership_flag = 2;
                    } else {
                        // ルームで選択した会員のみ
                        if ($nc3_sort_page->id == $nc3_sort_page->page_id_top) {
                            $membership_flag = 1;
                        }
                    }
                }

                /* 多言語化対応 */
                $permanent_link = '/';
                if ($nc3_sort_page->id == $nc3_top_page->id) {
                    // トップページ
                    if ($this->checkLangDirnameJpn($nc3_sort_page->language_id)) {
                        $permanent_link = '/';
                    } else {
                        $permanent_link = '/en';
                    }
                } else {
                    if ($this->checkLangDirnameJpn($nc3_sort_page->language_id)) {
                        $permanent_link = '/' . $nc3_sort_page->permalink;;
                    } else {
                        $permanent_link = '/en/' . $nc3_sort_page->permalink;;
                    }
                }

                $page_ini = "[page_base]\n";
                $page_ini .= "page_name = \"" . $nc3_sort_page->page_name . "\"\n";
                $page_ini .= "permanent_link = \"". $permanent_link . "\"\n";
                $page_ini .= "base_display_flag = 1\n";
                $page_ini .= "membership_flag = " . $membership_flag . "\n";
                $page_ini .= "nc3_page_id = \"" . $nc3_sort_page->id . "\"\n";
                $page_ini .= "nc3_room_id = \"" . $nc3_sort_page->room_id . "\"\n";

                // 親ページの検索（parent_id = 1 はパブリックのトップレベルなので、1 より大きいものを探す）
                if ($nc3_sort_page->parent_id > 1) {
                    // マッピングテーブルから親のページのディレクトリを探す
                    $parent_page_mapping = MigrationMapping::where('target_source_table', 'nc3_pages')->where('source_key', $nc3_sort_page->parent_id)->first();
                    // 1ルームのみの移行の場合を考慮
                    $parent_room_flg = true;
                    $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
                    if (!empty($room_ids) && count($room_ids) == 1 && isset($room_ids[0])) {
                        if ($nc3_sort_page->parent_id == $room_ids[0]) {
                            $parent_room_flg = false;
                        }
                    }
                    if (!empty($parent_page_mapping) && $parent_room_flg) {
                        $page_ini .= "parent_page_dir = \"" . $parent_page_mapping->destination_key . "\"\n";
                    }
                }

                // ページディレクトリの作成
                $new_page_index++;
                Storage::makeDirectory($this->getImportPath('pages/') . $this->zeroSuppress($new_page_index));

                // ページ設定ファイルの出力
                Storage::put($this->getImportPath('pages/') . $this->zeroSuppress($new_page_index) . '/' . "/page.ini", $page_ini);

                // マッピングテーブルの追加
                $mapping = MigrationMapping::updateOrCreate(
                    ['target_source_table' => 'nc3_pages', 'source_key' => $nc3_sort_page->id],
                    ['target_source_table' => 'nc3_pages',
                     'source_key'          => $nc3_sort_page->id,
                     'destination_key'     => $this->zeroSuppress($new_page_index)]
                );

                // ブロック処理
                $this->nc3Frame($nc3_sort_page, $new_page_index, $nc3_top_page);
            }

            // ページ入れ替え
            $this->changePageSequence();
        }
    }

    /**
     * ページ入れ替え
     */
    private function changePageSequence()
    {
        // パラメータの取得とチェック
        $nc3_export_change_pages = $this->getMigrationConfig('pages', 'nc3_export_change_page');
        if (empty($nc3_export_change_pages)) {
            return;
        }

        // パラメータのループと入れ替え処理
        foreach ($nc3_export_change_pages as $source_page_id => $destination_page_id) {
            // マッピングテーブルを見て、移行後のフォルダ名を取得
            $source_page = MigrationMapping::where('target_source_table', 'nc3_pages')->where('source_key', $source_page_id)->first();
            $destination_page = MigrationMapping::where('target_source_table', 'nc3_pages')->where('source_key', $destination_page_id)->first();

            if (empty($source_page) || empty($destination_page)) {
                continue;
            }

            // 例：0005 を0007 に入れ替え。0005 -> 0005_, 0007 -> 0005, 0005_ -> 0007
            Storage::move($this->getImportPath('pages/' . $source_page->destination_key), $this->getImportPath('pages/' . $source_page->destination_key . '_'));
            Storage::move($this->getImportPath('pages/' . $destination_page->destination_key), $this->getImportPath('pages/' . $source_page->destination_key));
            Storage::move($this->getImportPath('pages/' . $source_page->destination_key . '_'), $this->getImportPath('pages/' . $destination_page->destination_key));
        }
    }

    /**
     *  ファイル出力
     */
    private function storageAppend($path, $value)
    {
        $value = $this->exportStrReplace($value);

        // ファイル出力
        Storage::append($path, $value);
    }

    /**
     *  ファイル出力
     */
    private function storagePut($path, $value)
    {
        $value = $this->exportStrReplace($value);

        // ファイル出力
        Storage::put($path, $value);
    }

    /**
     *  ファイル出力
     */
    private function exportStrReplace($value, $target = 'basic')
    {
        // 文字列変換指定を反映する。
        $nc3_export_str_replaces = $this->getMigrationConfig($target, 'nc3_export_str_replace');
        if (!empty($nc3_export_str_replaces)) {
            foreach ($nc3_export_str_replaces as $search => $replace) {
                $value = str_replace($search, $replace, $value);
            }
        }
        return $value;
    }

    /**
     *  プラグインの変換
     */
    private function nc3GetPluginName($nc3_plugin_key)
    {
        // NC3 テンプレート変換配列にあれば、その値。
        // 定義のないものは 'NotFound' にする。
        if (array_key_exists($nc3_plugin_key, $this->plugin_name)) {
            return $this->plugin_name[$nc3_plugin_key];
        }
        return 'NotFound';
    }

    /**
     *  NC3モジュール名の取得
     * 「TODO」まだ
     */
    public function nc3GetModuleNames($action_names, $connect_change = true)
    {
        $available_connect_plugin_names = ['blogs', 'bbses', 'databases'];
        $ret = array();
        foreach ($action_names as $action_name) {
            $action_name_parts = explode('_', $action_name);
            // Connect-CMS のプラグイン名に変換
            if ($connect_change == true && array_key_exists($action_name_parts[0], $this->plugin_name)) {
                $connect_plugin_name = $this->plugin_name[$action_name_parts[0]];
                if ($connect_plugin_name == 'Development') {
                    $this->putError(3, '新着：未開発プラグイン', "action_names = " . $action_name_parts[0]);
                } elseif (in_array($connect_plugin_name, $available_connect_plugin_names)) {
                    $ret[] = $connect_plugin_name;
                } else {
                    $this->putError(3, '新着：未対応プラグイン', "action_names = " . $action_name_parts[0]);
                }
            }
        }
        return implode(',', $ret);
    }

    /**
     *  NC3 の基本情報をエクスポートする。
     */
    private function nc3ExportBasic()
    {
        $this->putMonitor(3, "Start nc3ExportBasic.");

        // config テーブルの取得
        $site_settings = Nc3SiteSetting::get();

        // site,ini ファイル編集
        $basic_ini = "[basic]\n";

        // サイト名
        $sitename = $site_settings->where('key', 'App.site_name')->where('is_origin', 1)->first();
        $sitename = empty($sitename) ? '' : $sitename->value;
        $basic_ini .= "base_site_name = \"" . $sitename . "\"\n";

        // 使ってないためコメントアウト
        // 基本デザイン（パブリック）
        // $whole_site_room = Nc3Room::where('space_id', Nc3Space::WHOLE_SITE_ID)->first();   // 必ずある想定
        // $public_room = Nc3Room::where('parent_id', $whole_site_room->id)->where('space_id', Nc3Space::PUBLIC_SPACE_ID)->first();   // 必ずある想定
        // $public_room = $public_room ?? new Nc3Room();
        // $basic_ini .= "default_theme_public = \"" . $public_room->theme . "\"\n";

        // salt
        $nc3_application_yml_path = config('migration.NC3_APPLICATION_YML_PATH');
        $yaml = Yaml::parse(file_get_contents($nc3_application_yml_path));
        $salt = str_replace(array("\r\n", "\r", "\n"), "", $yaml['Security']['salt']);  // salt末尾に改行あり。改行削除
        $basic_ini .= "nc3_security_salt = \"" . $salt . "\"\n";

        // basic.ini ファイル保存
        $this->storagePut($this->getImportPath('basic/basic.ini'), $basic_ini);
    }

    /**
     * NC3：アップロードファイルの移行
     *
     * uploads_ini の形式
     *
     * [uploads]
     * upload[1] = upload_00001.jpg
     * upload[2] = upload_00002.png
     * upload[3] = upload_00003.pdf
     *
     * [1]
     * file_name =
     * mimetype =
     * extension =
     * plugin_name =
     * page_id = 0
     *
     * [2]
     * ・・・
     */
    private function nc3ExportUploads($uploads_path, $redo)
    {
        $this->putMonitor(3, "Start nc3ExportUploads.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('uploads/'));
            // アップロードファイルの削除
            Storage::deleteDirectory(config('connect.directory_base'));
        }

        // NC3 アップロードテーブルを移行する。
        $nc3_uploads = Nc3UploadFile::orderBy('id')->get();

        // uploads,ini ファイル
        $this->storagePut($this->getImportPath('uploads/uploads.ini'), "[uploads]");

        // uploads,ini ファイルの詳細（変数に保持、後でappend。[uploads] セクションが切れないため。）
        $uploads_ini = "";
        $uploads_ini_detail = "";

        // アップロード・ファイルのループ
        foreach ($nc3_uploads as $nc3_upload) {
            // アップロードファイルのルームを無視する指定があれば全部を移行、なければルーム設定を参照
            if (!$this->hasMigrationConfig('uploads', 'nc3_export_uploads_force_room', true)) {
                $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
                // ルーム指定があれば、指定されたルームのみ処理する。
                if (empty($room_ids)) {
                    // ルーム指定なし。全データの移行
                } elseif (!empty($room_ids) && in_array($nc3_upload->room_id, $room_ids)) {
                    // ルーム指定あり。指定ルームに合致する。
                } else {
                    // ルーム指定あり。条件に合致せず。移行しない。
                    continue;
                }
            }

            // ファイルのコピー
            $destination_file_dir = storage_path() . "/app/" . $this->getImportPath('uploads');
            $destination_file_name = "upload_" . $this->zeroSuppress($nc3_upload->id, 5);
            $destination_file_path = $destination_file_dir . '/' . $destination_file_name . '.' . $nc3_upload->extension;

            // 画像か？
            // nc3 Wysiwygでは、右記画像のみアップ許可。 'image/gif', 'image/png', 'image/jpg', 'image/jpeg';
            // see) https://github.com/NetCommons3/Wysiwyg/blob/master/Controller/WysiwygImageController.php#L30
            $is_image = false;
            if (in_array($nc3_upload->mimetype, ['image/gif', 'image/png', 'image/jpg', 'image/jpeg'])) {
                $is_image = true;
            }

            if ($is_image) {
                // 画像
                // 原寸からサイズの大きい順に調べて、あったらその画像で移行する
                $image_sizes = ['', 'biggest_', 'big_', 'medium_', 'small_', 'thumb_'];
                $is_image_not_exists = true;
                foreach ($image_sizes as $image_size) {
                    $image_file_path = $uploads_path . $nc3_upload->path . $nc3_upload->id . '/' . $image_size . $nc3_upload->real_file_name;
                    if (File::exists($image_file_path)) {
                        if (!File::isDirectory($destination_file_dir)) {
                            File::makeDirectory($destination_file_dir, 0775, true);
                        }
                        File::copy($image_file_path, $destination_file_path);
                        $is_image_not_exists = false;
                        break;
                    }
                }
                if ($is_image_not_exists) {
                    $this->putMonitor(3, "Image file not exists: " . $uploads_path . $nc3_upload->path . $nc3_upload->id . '/');
                }

            } else {
                // ファイル
                $source_file_path = $uploads_path . $nc3_upload->path . $nc3_upload->id . '/' . $nc3_upload->real_file_name;
                if (File::exists($source_file_path)) {
                    if (!File::isDirectory($destination_file_dir)) {
                        File::makeDirectory($destination_file_dir, 0775, true);
                    }
                    File::copy($source_file_path, $destination_file_path);
                } else {
                    $this->putMonitor(3, "File not exists: {$source_file_path}");
                }
            }

            $uploads_ini .= "upload[" . $nc3_upload->id . "] = \"" . $destination_file_name . '.' . $nc3_upload->extension . "\"\n";

            $uploads_ini_detail .= "\n";
            $uploads_ini_detail .= "[" . $nc3_upload->id . "]\n";
            $uploads_ini_detail .= "client_original_name = \"" . $nc3_upload->original_name . "\"\n";
            $uploads_ini_detail .= "temp_file_name = \"" . $destination_file_name . '.' . $nc3_upload->extension . "\"\n";
            $uploads_ini_detail .= "size = \"" . $nc3_upload->size . "\"\n";
            $uploads_ini_detail .= "mimetype = \"" . $nc3_upload->mimetype . "\"\n";
            $uploads_ini_detail .= "extension = \"" . $nc3_upload->extension . "\"\n";
            $uploads_ini_detail .= "plugin_name = \"" . $this->nc3GetPluginName($nc3_upload->plugin_key) . "\"\n";
            $uploads_ini_detail .= "page_id = \"0\"\n";
            $uploads_ini_detail .= "nc3_room_id = \"" . $nc3_upload->room_id . "\"\n";
        }

        // アップロード一覧の出力
        Storage::append($this->getImportPath('uploads/uploads.ini'), $uploads_ini . $uploads_ini_detail);

        // uploads のini ファイルの再読み込み
        if (Storage::exists($this->getImportPath('uploads/uploads.ini'))) {
            $this->uploads_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('uploads/uploads.ini'), true);
        }
    }

    /**
     * NC3：ユーザの移行
     */
    private function nc3ExportUsers($redo)
    {
        $this->putMonitor(3, "Start nc3ExportUsers.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('users/'));
        }

        // NC3 ユーザ任意項目
        $nc3_any_items = collect();
        $nc3_export_user_items = $this->getMigrationConfig('users', 'nc3_export_user_items');
        if ($nc3_export_user_items) {

            $nc3_any_items = Nc3UserAttribute::
                select('user_attributes.*', 'user_attribute_settings.data_type_key', 'user_attribute_settings.required')
                ->whereIn('user_attributes.name', $nc3_export_user_items)
                ->leftJoin('user_attribute_settings', 'user_attribute_settings.user_attribute_key', '=', 'user_attributes.key')
                ->where('user_attributes.is_origin', 1)
                ->orderBy('user_attribute_settings.col')
                ->orderBy('user_attribute_settings.row')
                ->get();
        }

        // NC3 ユーザデータ取得
        $nc3_users_query = Nc3User::where('username', '<>', '');
        $nc3_users = $nc3_users_query->orderBy('users.created')->get();

        // 空なら戻る
        if ($nc3_users->isEmpty()) {
            return;
        }

        // 氏名・プロフィール等の日本語
        $nc3_users_languages = Nc3UsersLanguage::where('language_id', 2)->whereIn('user_id', $nc3_users->pluck('id'))->get();

        // オプション項目
        $nc3_user_item_options = Nc3UserAttributeChoice::where('is_origin', 1)->get();

        // ini ファイル用変数
        $users_ini = "[users]\n";

        // NC3ユーザ（User）のループ（ユーザインデックス用）
        foreach ($nc3_users as $nc3_user) {
            $users_ini .= "user[\"" . $nc3_user->id . "\"] = \"" . $nc3_user->handlename . "\"\n";
        }

        // NC3ユーザ（User）のループ（ユーザデータ用）
        foreach ($nc3_users as $nc3_user) {
            // テスト用データ変換
            if ($this->hasMigrationConfig('user', 'nc3_export_test_mail', true)) {
                $nc3_user->email = MigrationUtils::replaceFullwidthAt($nc3_user->email);
                $nc3_user->username = MigrationUtils::replaceFullwidthAt($nc3_user->username);   // ログインID
            }
            $users_ini .= "\n";
            $users_ini .= "[\"" . $nc3_user->id . "\"]\n";
            $users_ini .= "name               = \"" . $nc3_user->handlename . "\"\n";
            $users_ini .= "email              = \"" . trim($nc3_user->email) . "\"\n";
            $users_ini .= "userid             = \"" . $nc3_user->username . "\"\n";
            $users_ini .= "password           = \"" . $nc3_user->password . "\"\n";
            if ($nc3_user->status == Nc3User::status_not_active) {
                $users_ini .= "status             = " . UserStatus::not_active . "\n";
            } else {
                $users_ini .= "status             = " . UserStatus::active . "\n";
            }
            if ($nc3_any_items->isNotEmpty()) {
                // 任意項目
                foreach ($nc3_any_items as $nc3_any_item) {
                    $item_name = "item_{$nc3_any_item->id}";
                    $item_key = $nc3_any_item->key;

                    if (in_array($item_key, ['name', 'profile', 'search_keywords'])) {
                        // name, profile等が nc3_users_languages にあるんで、そっちから取る。
                        $nc3_users_language = $nc3_users_languages->firstWhere('user_id', $nc3_user->id) ?? new Nc3UsersLanguage();
                        $item_value = $nc3_users_language->$item_key;
                    } elseif (in_array($nc3_any_item->data_type_key, ['radio', 'select', 'checkbox'])) {
                        // 選択肢はコード⇒値に変換
                        $option = $nc3_user_item_options->where('user_attribute_id', $nc3_any_item->id)->where('code', $nc3_user->$item_key)->first() ?? new Nc3UserAttributeChoice();
                        $item_value = $option->name;
                    } else {
                        $item_value = $nc3_user->$item_key;
                    }
                    $users_ini .= "{$item_name}            = \"" . $item_value . "\"\n";
                }
            }

            if ($nc3_user->role_key == Nc3User::role_system_administrator) {
                $users_ini .= "users_roles_manage = \"admin_system\"\n";
                $users_ini .= "users_roles_base   = \"role_article_admin\"\n";
            } elseif ($nc3_user->role_key == Nc3User::role_administrator) {
                $users_ini .= "users_roles_base   = \"role_article_admin\"\n";
            } elseif ($nc3_user->role_key == Nc3User::role_common_user) {
                $users_ini .= "users_roles_base   = \"role_reporter\"\n";
            }
        }

        // Userデータの出力
        $this->storagePut($this->getImportPath('users/users.ini'), $users_ini);

        // ユーザ任意項目
        foreach ($nc3_any_items as $i => $nc3_any_item) {
            // カラム型 変換
            $convert_user_column_types = [
                // nc3, cc
                'text'     => UserColumnType::text,
                'email'    => UserColumnType::mail,
                'radio'    => UserColumnType::radio,
                'textarea' => UserColumnType::textarea,
                'select'   => UserColumnType::select,
                'checkbox' => UserColumnType::checkbox,
            ];

            // 未対応
            $exclude_user_column_types = [
                'password',
                'label',
                'timezone',
                'img',
            ];

            $user_column_type = $nc3_any_item->data_type_key;
            if (in_array($user_column_type, $exclude_user_column_types)) {
                // 未対応
                $this->putError(3, 'ユーザ任意項目の項目タイプが未対応', "user_attribute_settings.data_type_key = {$user_column_type}|user_attributes.name = {$nc3_any_item->name}");
                $user_column_type = '';

            } elseif (array_key_exists($user_column_type, $convert_user_column_types)) {
                $user_column_type = $convert_user_column_types[$user_column_type];
                $users_columns_selects_ini  = "[users_columns_selects_base]\n";
                switch ($user_column_type) {
                    case 'radio':
                    case 'select':
                    case 'checkbox':
                        $options = $nc3_user_item_options->where('user_attribute_id', $nc3_any_item->id)->pluck('name')->implode('|');
                        $users_columns_selects_ini .= "value      = \"" . $options . "\"\n";
                        $users_columns_selects_ini .= "\n";
                        break;
                    default:
                        $users_columns_selects_ini = "\n";
                        break;
                }

            } else {
                // 未対応に未指定
                $this->putError(3, 'ユーザ任意項目の項目タイプが未対応（未対応に未指定の型）', "user_attribute_settings.data_type_key = {$user_column_type}|user_attributes.name = {$nc3_any_item->name}");
                $user_column_type = '';
            }

            // ini ファイル用変数
            $users_columns_ini  = "[users_columns_base]\n";
            $users_columns_ini .= "column_type      = \"" . $user_column_type . "\"\n";
            $users_columns_ini .= "column_name      = \"" . $nc3_any_item->name . "\"\n";
            $users_columns_ini .= "required         = " . $nc3_any_item->required . "\n";
            $users_columns_ini .= "caption          = \"" . $nc3_any_item->description . "\"\n";
            $users_columns_ini .= "display_sequence = " . ($i + 1) . "\n";
            $users_columns_ini .= "\n";
            $users_columns_ini .= $users_columns_selects_ini;
            $users_columns_ini .= "[source_info]\n";
            $users_columns_ini .= "item_id = " . $nc3_any_item->id . "\n";

            // Userカラムデータの出力
            $this->storagePut($this->getImportPath('users/users_columns_') . $this->zeroSuppress($nc3_any_item->id) . '.ini', $users_columns_ini);
        }
    }


    /**
     * NC3：グループの移行
     */
    private function nc3ExportRooms($redo)
    {
        $this->putMonitor(3, "Start nc3ExportRooms.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('groups/'));
        }

        /*
        [group_base]
        name = "会員専用ルーム"

        [users]
        user["admin"] = 2
        user["user"] = 4

        ※ user["ユーザID"] = "role_authority_id"
        */

        // NC3 ルームの取得
        // 「すべての会員をデフォルトで参加させる」はグループにしないので対象外。'default_entry_flag'== 0
        $nc3_rooms_query = Nc2Page::where('space_type', 2)
                            ->whereColumn('page_id', 'room_id')
                            ->whereIn('thread_num', [1, 2])
                            ->where('default_entry_flag', 0);
        // 対象外ページ指定の有無
        if ($this->getMigrationConfig('pages', 'nc3_export_ommit_page_ids')) {
            $nc3_rooms_query->whereNotIn('page_id', $this->getMigrationConfig('pages', 'nc3_export_ommit_page_ids'));
        }
        $nc3_rooms = $nc3_rooms_query->orderBy('thread_num')->orderBy('display_sequence')->get();

        // 空なら戻る
        if ($nc3_rooms->isEmpty()) {
            return;
        }

        // グループをループ
        foreach ($nc3_rooms as $nc3_room) {
            // ini ファイル用変数
            $groups_ini  = "[group_base]\n";
            $groups_ini .= "name = \"" . $nc3_room->page_name . "\"\n";
            $groups_ini .= "\n";
            $groups_ini .= "[source_info]\n";
            $groups_ini .= "room_id = " . $nc3_room->room_id . "\n";
            $groups_ini .= "\n";
            $groups_ini .= "[users]\n";

            // NC3 参加ユーザの取得
            $nc3_pages_users_links = Nc2PageUserLink::select('pages_users_link.*', 'users.login_id')
                                                    ->join('users', 'users.user_id', 'pages_users_link.user_id')
                                                    ->where('room_id', $nc3_room->room_id)
                                                    ->orderBy('room_id')
                                                    ->orderBy('users.role_authority_id')
                                                    ->orderBy('users.insert_time')
                                                    ->get();

            foreach ($nc3_pages_users_links as $nc3_pages_users_link) {
                $groups_ini .= "user[\"" . $nc3_pages_users_link->login_id . "\"] = " . $nc3_pages_users_link->role_authority_id . "\n";
            }

            // グループデータの出力
            $this->storagePut($this->getImportPath('groups/group_') . $this->zeroSuppress($nc3_room->room_id) . '.ini', $groups_ini);
        }
    }

    /**
     * NC3：日誌（Journal）の移行
     */
    private function nc3ExportJournal($redo)
    {
        $this->putMonitor(3, "Start nc3ExportJournal.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('blogs/'));
        }

        // NC3日誌（Journal）を移行する。
        // $nc3_journals = Nc2Journal::orderBy('journal_id')->get();
        $nc3_journals = Nc2Journal::select('journal.*', 'page_rooms.space_type')
            ->join('pages as page_rooms', function ($join) {
                $join->on('page_rooms.page_id', '=', 'journal.room_id')
                    ->whereColumn('page_rooms.page_id', 'page_rooms.room_id')
                    ->whereIn('page_rooms.space_type', [Nc2Page::space_type_public, Nc2Page::space_type_group])
                    ->where('page_rooms.room_id', '!=', 2);        // 2:グループスペースを除外（枠だけでグループルームじゃないので除外）
                    // ->where('page_rooms.private_flag', 0);         // 0:プライベートルーム以外
            })
            ->orderBy('journal.journal_id')
            ->get();

        // 空なら戻る
        if ($nc3_journals->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc2User::get();

        // NC3日誌（Journal）のループ
        foreach ($nc3_journals as $nc3_journal) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_journal->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // この日誌が配置されている最初のページオブジェクトを取得しておく
            // WYSIWYG で相対パスを絶対パスに変換する際に、ページの固定URL が必要になるため。
            $nc3_page = null;
            $nc3_journal_block = Nc2JournalBlock::where('journal_id', $nc3_journal->journal_id)->orderBy('block_id', 'asc')->first();
            if (!empty($nc3_journal_block)) {
                $nc3_block = Nc2Block::where('block_id', $nc3_journal_block->block_id)->first();
            }
            if (!empty($nc3_block)) {
                $nc3_page = Nc2Page::where('page_id', $nc3_block->page_id)->first();
            }

            // 権限設定
            // ----------------------------------------------------
            // post_authority
            // 2: 一般まで
            // 3: モデレータまで
            // 4: 主担のみ
            $article_post_flag = 0;
            $reporter_post_flag = 0;
            if ($nc3_journal->post_authority == 2) {
                $article_post_flag = 1;
                $reporter_post_flag = 1;

            } elseif ($nc3_journal->post_authority == 3) {
                $article_post_flag = 1;

            } elseif ($nc3_journal->post_authority == 4) {
                // 一般,モデレータ=0でccでは主担=コンテンツ管理者は投稿可のため、なにもしない
            }

            // メール設定
            // ----------------------------------------------------
            // mail_authority
            // 1: ゲストまで 　　→ パブ通知は、「全ユーザに通知」
            // 　※ 掲示板-パブリック（パブサブも同様）： ⇒ 「全ユーザに通知」
            // 　※ 掲示板-グループ：　　　　　　　　　　 ⇒ ルームグループに、グループ通知
            // 2: 一般まで 　　　→ グループは、グループ通知
            // 　※ 掲示板-パブリック（パブサブも同様）： ⇒ (手動)でグループ作って、グループ通知　⇒ 移行で警告表示
            // 　※ 掲示板-グループ：　 　　　　　　　　　⇒ ルームグループに、グループ通知
            // 3: モデレータまで → (手動)でグループ作って、グループ通知　⇒ 移行で警告表示
            // 4: 主担のみ 　　　→グループ管理者は、「管理者グループ」通知
            $notice_everyone = 0;
            $notice_admin_group = 0;
            $notice_moderator_group = 0;
            $notice_group = 0;
            $notice_public_general_group = 0;
            $notice_public_moderator_group = 0;

            if ($nc3_journal->mail_authority === 1) {
                if ($nc3_journal->space_type == Nc2Page::space_type_public) {
                    // 全ユーザ通知
                    $notice_everyone = 1;

                } elseif ($nc3_journal->space_type == Nc2Page::space_type_group) {
                    // グループ通知
                    $notice_group = 1;
                }

            } elseif ($nc3_journal->mail_authority == 2) {
                if ($nc3_journal->space_type == Nc2Page::space_type_public) {
                    // パブリック一般通知
                    $notice_public_general_group = 1;
                    $notice_admin_group = 1;

                } elseif ($nc3_journal->space_type == Nc2Page::space_type_group) {
                    // グループ通知
                    $notice_group = 1;
                }

            } elseif ($nc3_journal->mail_authority == 3) {
                if ($nc3_journal->space_type == Nc2Page::space_type_public) {
                    // パブリックモデレーター通知
                    $notice_public_moderator_group = 1;
                    $notice_admin_group = 1;
                } elseif ($nc3_journal->space_type == Nc2Page::space_type_group) {
                    // モデレータユーザ通知
                    $notice_moderator_group = 1;
                    $notice_admin_group = 1;
                }

            } elseif ($nc3_journal->mail_authority == 4) {
                // 管理者グループ通知
                $notice_admin_group = 1;
            }

            $mail_subject = $nc3_journal->mail_subject;
            $mail_body = $nc3_journal->mail_body;
            $approved_subject = $nc3_journal->agree_mail_subject;
            $approved_body = $nc3_journal->agree_mail_body;

            // --- メール配信設定
            // [{X-SITE_NAME}]日誌投稿({X-ROOM} {X-JOURNAL_NAME} {X-SUBJECT})
            //
            // 日誌に投稿されたのでお知らせします。
            // ルーム名称:{X-ROOM}
            // 日誌タイトル:{X-JOURNAL_NAME}
            // 記事タイトル:{X-SUBJECT}
            // 投稿者:{X-USER}
            // 投稿日時:{X-TO_DATE}
            //
            //
            // {X-BODY}
            //
            // この記事に返信するには、下記アドレスへ
            // {X-URL}

            // ※ {X-BODY}は「続きを読む」内容は含んでいなかった。

            // --- 日誌投稿承認完了通知設定
            // [{X-SITE_NAME}]日誌投稿承認完了通知
            //
            // {X-SITE_NAME}における日誌投稿の承認が完了しました。
            // もし{X-SITE_NAME}での日誌投稿に覚えがない場合はこのメールを破棄してください。
            //
            // 日誌の内容を確認するには下記のリンクをクリックして下さい。
            // {X-URL}

            // 変換
            $convert_embedded_tags = [
                // nc3埋込タグ, cc埋込タグ
                ['{X-SITE_NAME}', '[[' . NoticeEmbeddedTag::site_name . ']]'],
                ['{X-SUBJECT}', '[[' . NoticeEmbeddedTag::title . ']]'],
                ['{X-USER}', '[[' . NoticeEmbeddedTag::created_name . ']]'],
                ['{X-TO_DATE}', '[[' . NoticeEmbeddedTag::created_at . ']]'],
                ['{X-BODY}', '[[' . NoticeEmbeddedTag::body . ']]'],
                ['{X-URL}', '[[' . NoticeEmbeddedTag::url . ']]'],
                // 除外
                ['日誌タイトル:{X-JOURNAL_NAME}', ''],
                ['ルーム名称:{X-ROOM}', ''],
                ['{X-JOURNAL_NAME} ', ''],
                ['{X-JOURNAL_NAME}', ''],
                ['{X-ROOM} ', ''],
                ['{X-ROOM}', ''],
            ];
            foreach ($convert_embedded_tags as $convert_embedded_tag) {
                $mail_subject = str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_subject);
                $mail_body = str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_body);
                $approved_subject = str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $approved_subject);
                $approved_body = str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $approved_body);
            }

            // （NC3）承認メールは、承認あり＋メール通知ON（～ゲストまで通知）でも、メール通知フォーマットで「主担」のみに飛ぶ。
            //        ⇒ （CC）NC3メール通知フォーマットを、CC承認メールフォーマットにセット
            // （NC3）承認完了メールは、承認完了通知ONで、承認完了通知フォーマットで「投稿者」のみに飛ぶ。
            //        他ユーザには、メール通知ON（～ゲストまで通知）でメール通知フォーマットで全員にメール飛ぶ。
            //        ⇒ （CC）NC3承認完了通知フォーマットを、CC承認完了通知フォーマットにセット。通知先は、投稿者＋管理グループ。

            // ブログ設定
            $journals_ini = "";
            $journals_ini .= "[blog_base]\n";
            $journals_ini .= "blog_name = \"" . $nc3_journal->journal_name . "\"\n";
            $journals_ini .= "view_count = 10\n";
            $journals_ini .= "use_like = " . $nc3_journal->vote_flag . "\n";
            $journals_ini .= "article_post_flag = " . $article_post_flag . "\n";
            $journals_ini .= "article_approval_flag = " . $nc3_journal->agree_flag . "\n";      // agree_flag 1:承認あり 0:承認なし
            $journals_ini .= "reporter_post_flag = " . $reporter_post_flag . "\n";
            $journals_ini .= "reporter_approval_flag = " . $nc3_journal->agree_flag . "\n";     // agree_flag 1:承認あり 0:承認なし
            $journals_ini .= "notice_on = " . $nc3_journal->mail_flag . "\n";
            $journals_ini .= "notice_everyone = " . $notice_everyone . "\n";
            $journals_ini .= "notice_group = " . $notice_group . "\n";
            $journals_ini .= "notice_moderator_group = " . $notice_moderator_group . "\n";
            $journals_ini .= "notice_admin_group = " . $notice_admin_group . "\n";
            $journals_ini .= "notice_public_general_group = " . $notice_public_general_group . "\n";
            $journals_ini .= "notice_public_moderator_group = " . $notice_public_moderator_group . "\n";
            $journals_ini .= "mail_subject = \"" . $mail_subject . "\"\n";
            $journals_ini .= "mail_body = \"" . $mail_body . "\"\n";
            $journals_ini .= "approval_on = " . $nc3_journal->agree_flag . "\n";                // 承認ありなら 1: 承認通知
            $journals_ini .= "approval_admin_group = " . $nc3_journal->agree_flag . "\n";       // 1:「管理者グループ」通知
            $journals_ini .= "approval_subject = \"" . $mail_subject . "\"\n";                  // 承認通知はメール通知フォーマットと同じ
            $journals_ini .= "approval_body = \"" . $mail_body . "\"\n";
            $journals_ini .= "approved_on = " . $nc3_journal->agree_mail_flag . "\n";           // agree_mail_flag 1:承認完了通知する 0:通知しない
            $journals_ini .= "approved_author = " . $nc3_journal->agree_mail_flag . "\n";       // 1:投稿者へ通知する
            $journals_ini .= "approved_admin_group = " . $nc3_journal->agree_mail_flag . "\n";  // 1:「管理者グループ」通知
            $journals_ini .= "approved_subject = \"" . $approved_subject . "\"\n";
            $journals_ini .= "approved_body = \"" . $approved_body . "\"\n";

            // NC3 情報
            $journals_ini .= "\n";
            $journals_ini .= "[source_info]\n";
            $journals_ini .= "journal_id = " . $nc3_journal->journal_id . "\n";
            $journals_ini .= "room_id = " . $nc3_journal->room_id . "\n";
            $journals_ini .= "module_name = \"journal\"\n";
            $journals_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_journal->created) . "\"\n";
            $journals_ini .= "created_name    = \"" . $nc3_journal->insert_user_name . "\"\n";
            $journals_ini .= "insert_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_journal->created_user) . "\"\n";
            $journals_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_journal->modified) . "\"\n";
            $journals_ini .= "updated_name    = \"" . $nc3_journal->update_user_name . "\"\n";
            $journals_ini .= "update_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_journal->modified_user) . "\"\n";

            // NC3日誌で使ってるカテゴリ（journal_category）のみ移行する。
            $journals_ini .= "\n";
            $journals_ini .= "[categories]\n";
            $nc3_journal_categories = Nc2JournalCategory::
                select(
                    'journal_category.category_id',
                    'journal_category.category_name'
                )
                ->where('journal_category.journal_id', $nc3_journal->journal_id)
                ->join('journal_post', function ($join) {
                    $join->on('journal_post.category_id', '=', 'journal_category.category_id')
                         ->whereColumn('journal_post.journal_id', 'journal_category.journal_id');
                })
                ->groupBy(
                    'journal_category.category_id',
                    'journal_category.category_name',
                    'journal_category.display_sequence'
                )
                ->orderBy('journal_category.display_sequence')
                ->get();
            //Log::debug($nc3_journal_categories);
            // $journals_ini_commons = "";
            $journals_ini_originals = "";

            foreach ($nc3_journal_categories as $nc3_journal_category) {
                // if (in_array($nc3_journal_category->category_name, $this->nc3_default_categories)) {
                //     // 共通カテゴリにあるものは個別に作成しない。
                //     $journals_ini_commons .= "common_categories[" . array_search($nc3_journal_category->category_name, $this->nc3_default_categories) . "] = \"" . $nc3_journal_category->category_name . "\"\n";
                // } else {
                //     $journals_ini_originals .= "original_categories[" . $nc3_journal_category->category_id . "] = \"" . $nc3_journal_category->category_name . "\"\n";
                // }
                $journals_ini_originals .= "original_categories[" . $nc3_journal_category->category_id . "] = \"" . $nc3_journal_category->category_name . "\"\n";
            }
            // if (!empty($journals_ini_commons)) {
            //     $journals_ini .= $journals_ini_commons;
            // }
            if (!empty($journals_ini_originals)) {
                $journals_ini .= $journals_ini_originals;
            }

            // NC3日誌の記事（journal_post）を移行する。
            $nc3_journal_posts = Nc2JournalPost::where('journal_id', $nc3_journal->journal_id)->orderBy('post_id')->get();

            // journals_ini ファイルの詳細（変数に保持、後でappend。[blog_post] セクションを切れないため。）
            // $blog_post_ini_detail = "";

            // 日誌の記事はTSV でエクスポート
            // 日付{\t}status{\t}承認フラグ{\t}タイトル{\t}本文1{\t}本文2{\t}続き表示文言{\t}続き隠し文言
            $journals_tsv = "";

            // NC3日誌の記事をループ
            $journals_ini .= "\n";
            $journals_ini .= "[blog_post]\n";
            foreach ($nc3_journal_posts as $nc3_journal_post) {
                // TSV 形式でエクスポート
                if (!empty($journals_tsv)) {
                    $journals_tsv .= "\n";
                }

                $content       = $this->nc3Wysiwyg(null, null, null, null, $nc3_journal_post->content, 'journal');
                $more_content  = $this->nc3Wysiwyg(null, null, null, null, $nc3_journal_post->more_content, 'journal');

                $category_obj  = $nc3_journal_categories->firstWhere('category_id', $nc3_journal_post->category_id);
                $category      = "";
                if (!empty($category_obj)) {
                    $category  = $category_obj->category_name;
                }

                $like_count = empty($nc3_journal_post->vote) ? 0 : count(explode('|', $nc3_journal_post->vote));

                $status = StatusType::active;
                if ($nc3_journal_post->status == 1) {
                    $status = StatusType::temporary;
                }
                if ($nc3_journal_post->agree_flag == 1) {
                    $status = StatusType::approval_pending;
                }

                $journals_tsv .= $this->getCCDatetime($nc3_journal_post->journal_date) . "\t";  // [0] 投稿日時
                // $journals_tsv .= $nc3_journal_post->category_id     . "\t";
                $journals_tsv .= $category                          . "\t";
                $journals_tsv .= $status                            . "\t";     // [2] ccステータス
                $journals_tsv .= $nc3_journal_post->agree_flag      . "\t";     // [3] 使ってない
                $journals_tsv .= $nc3_journal_post->title           . "\t";
                $journals_tsv .= $content                           . "\t";
                $journals_tsv .= $more_content                      . "\t";
                $journals_tsv .= $nc3_journal_post->more_title      . "\t";
                $journals_tsv .= $nc3_journal_post->hide_more_title . "\t";
                $journals_tsv .= $like_count                        . "\t";     // [9] いいね数
                $journals_tsv .= $nc3_journal_post->vote            . "\t";     // [10]いいねのsession_id & nc3 user_id
                $journals_tsv .= $this->getCCDatetime($nc3_journal_post->created)                             . "\t";   // [11]
                $journals_tsv .= $nc3_journal_post->insert_user_name                                              . "\t";   // [12]
                $journals_tsv .= $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_journal_post->created_user) . "\t";   // [13]
                $journals_tsv .= $this->getCCDatetime($nc3_journal_post->modified)                             . "\t";   // [14]
                $journals_tsv .= $nc3_journal_post->update_user_name                                              . "\t";   // [15]
                $journals_tsv .= $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_journal_post->modified_user);          // [16]

                // 記事のタイトルの一覧
                // タイトルに " あり
                if (strpos($nc3_journal_post->title, '"')) {
                    // ログ出力
                    $this->putError(1, 'Blog title in double-quotation', "タイトル = " . $nc3_journal_post->title);
                }
                $journals_ini .= "post_title[" . $nc3_journal_post->post_id . "] = \"" . str_replace('"', '', $nc3_journal_post->title) . "\"\n";
            }

            // blog の記事毎設定
            // $journals_ini .= $blog_post_ini_detail;

            // blog の設定
            //Storage::put($this->getImportPath('blogs/blog_') . $this->zeroSuppress($nc3_journal->journal_id) . '.ini', $journals_ini);
            $this->storagePut($this->getImportPath('blogs/blog_') . $this->zeroSuppress($nc3_journal->journal_id) . '.ini', $journals_ini);

            // blog の記事
            //Storage::put($this->getImportPath('blogs/blog_') . $this->zeroSuppress($nc3_journal->journal_id) . '.tsv', $journals_tsv);
            $journals_tsv = $this->exportStrReplace($journals_tsv, 'blogs');
            $this->storagePut($this->getImportPath('blogs/blog_') . $this->zeroSuppress($nc3_journal->journal_id) . '.tsv', $journals_tsv);
        }
    }

    /**
     * NC3：掲示板（Bbs）の移行
     */
    private function nc3ExportBbs($redo)
    {
        $this->putMonitor(3, "Start nc3ExportBbs.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('bbses/'));
        }

        // NC3掲示板（Bbs）を移行する。
        // $nc3_bbses = Nc2Bbs::orderBy('bbs_id')->get();
        $nc3_bbses = Nc2Bbs::select('bbs.*', 'page_rooms.space_type')
            ->join('pages as page_rooms', function ($join) {
                $join->on('page_rooms.page_id', '=', 'bbs.room_id')
                    ->whereColumn('page_rooms.page_id', 'page_rooms.room_id')
                    ->whereIn('page_rooms.space_type', [Nc2Page::space_type_public, Nc2Page::space_type_group])
                    ->where('page_rooms.room_id', '!=', 2);        // 2:グループスペースを除外（枠だけでグループルームじゃないので除外）
                    // ->where('page_rooms.private_flag', 0);         // 0:プライベートルーム以外
            })
            ->orderBy('bbs.bbs_id')
            ->get();

        // 空なら戻る
        if ($nc3_bbses->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc2User::get();

        // NC3掲示板（Bbs）のループ
        foreach ($nc3_bbses as $nc3_bbs) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_bbs->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // この掲示板が配置されている最初のページオブジェクトを取得しておく
            // WYSIWYG で相対パスを絶対パスに変換する際に、ページの固定URL が必要になるため。
            $nc3_page = null;
            $nc3_bbs_block = Nc2BbsBlock::where('bbs_id', $nc3_bbs->bbs_id)->orderBy('block_id', 'asc')->first();
            if (!empty($nc3_bbs_block)) {
                $nc3_block = Nc2Block::where('block_id', $nc3_bbs_block->block_id)->first();
            }
            if (!empty($nc3_block)) {
                $nc3_page = Nc2Page::where('page_id', $nc3_block->page_id)->first();
            }

            // 権限設定
            // ----------------------------------------------------
            // topic_authority
            // 2: 一般まで
            // 3: モデレータまで
            // 4: 主担のみ
            $article_post_flag = 0;
            $reporter_post_flag = 0;
            if ($nc3_bbs->topic_authority == 2) {
                $article_post_flag = 1;
                $reporter_post_flag = 1;

            } elseif ($nc3_bbs->topic_authority == 3) {
                $article_post_flag = 1;

            } elseif ($nc3_bbs->topic_authority == 4) {
                // 一般,モデレータ=0でccでは主担=コンテンツ管理者は投稿可のため、なにもしない
            }

            // メール設定
            // ----------------------------------------------------
            // mail_authority
            // 1: ゲストまで 　　→ パブ通知は、「全ユーザに通知」
            // 　※ 掲示板-パブリック（パブサブも同様）： ⇒ 「全ユーザに通知」
            // 　※ 掲示板-グループ：　　　　　　　　　　 ⇒ ルームグループに、グループ通知
            // 2: 一般まで 　　　→ グループは、グループ通知
            // 　※ 掲示板-パブリック（パブサブも同様）： ⇒ (手動)でグループ作って、グループ通知　⇒ 移行で警告表示
            // 　※ 掲示板-グループ：　 　　　　　　　　　⇒ ルームグループに、グループ通知
            // 3: モデレータまで → (手動)でグループ作って、グループ通知　⇒ 移行で警告表示
            // 4: 主担のみ 　　　→グループ管理者は、「管理者グループ」通知
            $notice_everyone = 0;
            $notice_admin_group = 0;
            $notice_moderator_group = 0;
            $notice_group = 0;
            $notice_public_general_group = 0;
            $notice_public_moderator_group = 0;

            if ($nc3_bbs->mail_authority === 1) {
                if ($nc3_bbs->space_type == Nc2Page::space_type_public) {
                    // 全ユーザ通知
                    $notice_everyone = 1;

                } elseif ($nc3_bbs->space_type == Nc2Page::space_type_group) {
                    // グループ通知
                    $notice_group = 1;
                }

            } elseif ($nc3_bbs->mail_authority == 2) {
                if ($nc3_bbs->space_type == Nc2Page::space_type_public) {
                    // パブリック一般通知
                    $notice_public_general_group = 1;
                    $notice_admin_group = 1;

                } elseif ($nc3_bbs->space_type == Nc2Page::space_type_group) {
                    // グループ通知
                    $notice_group = 1;
                }

            } elseif ($nc3_bbs->mail_authority == 3) {
                if ($nc3_bbs->space_type == Nc2Page::space_type_public) {
                    // パブリックモデレーター通知
                    $notice_public_moderator_group = 1;
                    $notice_admin_group = 1;
                } elseif ($nc3_bbs->space_type == Nc2Page::space_type_group) {
                    // モデレータユーザ通知
                    $notice_moderator_group = 1;
                    $notice_admin_group = 1;
                }

            } elseif ($nc3_bbs->mail_authority == 4) {
                // 管理者グループ通知
                $notice_admin_group = 1;
            }

            $mail_subject = $nc3_bbs->mail_subject;
            $mail_body = $nc3_bbs->mail_body;

            // [{X-SITE_NAME}]掲示板投稿({X-ROOM} {X-BBS_NAME} {X-SUBJECT})
            //
            // 掲示板に投稿されたのでお知らせします。
            // ルーム名称:{X-ROOM}
            // 掲示板タイトル:{X-BBS_NAME}
            // 記事タイトル:{X-SUBJECT}
            // 投稿者:{X-USER}
            // 投稿日時:{X-TO_DATE}
            //
            //
            // {X-BODY}
            //
            // この記事に返信するには、下記アドレスへ
            // {X-URL}

            // 変換
            $convert_embedded_tags = [
                // nc3埋込タグ, cc埋込タグ
                ['{X-SITE_NAME}', '[[' . NoticeEmbeddedTag::site_name . ']]'],
                ['{X-SUBJECT}', '[[' . NoticeEmbeddedTag::title . ']]'],
                ['{X-USER}', '[[' . NoticeEmbeddedTag::created_name . ']]'],
                ['{X-TO_DATE}', '[[' . NoticeEmbeddedTag::created_at . ']]'],
                ['{X-BODY}', '[[' . NoticeEmbeddedTag::body . ']]'],
                ['{X-URL}', '[[' . NoticeEmbeddedTag::url . ']]'],
                // 除外
                ['掲示板タイトル:{X-BBS_NAME}', ''],
                ['ルーム名称:{X-ROOM}', ''],
                ['{X-BBS_NAME} ', ''],
                ['{X-BBS_NAME}', ''],
                ['{X-ROOM} ', ''],
                ['{X-ROOM}', ''],
            ];
            foreach ($convert_embedded_tags as $convert_embedded_tag) {
                $mail_subject = str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_subject);
                $mail_body = str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_body);
            }

            // 掲示板を日誌に移行する。
            // Connect-CMS に掲示板ができたら、掲示板 to 掲示板の移行機能も追加する。

            $journals_ini = "";
            $journals_ini .= "[blog_base]\n";
            $journals_ini .= "blog_name = \"" . $nc3_bbs->bbs_name . "\"\n";
            $journals_ini .= "use_like = " . $nc3_bbs->vote_flag . "\n";
            $journals_ini .= "article_post_flag = " . $article_post_flag . "\n";
            $journals_ini .= "reporter_post_flag = " . $reporter_post_flag . "\n";
            $journals_ini .= "notice_on = " . $nc3_bbs->mail_send . "\n";
            $journals_ini .= "notice_everyone = " . $notice_everyone . "\n";
            $journals_ini .= "notice_group = " . $notice_group . "\n";
            $journals_ini .= "notice_moderator_group = " . $notice_moderator_group . "\n";
            $journals_ini .= "notice_admin_group = " . $notice_admin_group . "\n";
            $journals_ini .= "notice_public_general_group = " . $notice_public_general_group . "\n";
            $journals_ini .= "notice_public_moderator_group = " . $notice_public_moderator_group . "\n";
            $journals_ini .= "mail_subject = \"" . $mail_subject . "\"\n";
            $journals_ini .= "mail_body = \"" . $mail_body . "\"\n";

            // NC3 情報
            $journals_ini .= "\n";
            $journals_ini .= "[source_info]\n";
            $journals_ini .= "journal_id = " . 'BBS_' . $nc3_bbs->bbs_id . "\n";
            $journals_ini .= "room_id = " . $nc3_bbs->room_id . "\n";
            $journals_ini .= "space_type = " . $nc3_bbs->space_type . "\n";   // スペースタイプ, 1:パブリックスペース, 2:グループスペース
            $journals_ini .= "module_name = \"bbs\"\n";
            $journals_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_bbs->created) . "\"\n";
            $journals_ini .= "created_name    = \"" . $nc3_bbs->insert_user_name . "\"\n";
            $journals_ini .= "insert_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_bbs->created_user) . "\"\n";
            $journals_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_bbs->modified) . "\"\n";
            $journals_ini .= "updated_name    = \"" . $nc3_bbs->update_user_name . "\"\n";
            $journals_ini .= "update_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_bbs->modified_user) . "\"\n";

            // NC3掲示板の記事（bbs_post、bbs_post_body）を移行する。
            $nc3_bbs_posts = Nc2BbsPost::
                select('bbs_post.*', 'bbs_post_body.body', 'bbs_topic.newest_time')
                ->join('bbs_post_body', 'bbs_post_body.post_id', '=', 'bbs_post.post_id')
                ->leftJoin('bbs_topic', 'bbs_topic.topic_id', '=', 'bbs_post.topic_id')
                ->where('bbs_id', $nc3_bbs->bbs_id)
                ->orderBy('post_id')
                ->get();

            // 記事はTSV でエクスポート
            // 日付{\t}status{\t}タイトル{\t}本文
            $journals_tsv = "";

            // NC3記事をループ
            $journals_ini .= "\n";
            $journals_ini .= "[blog_post]\n";
            foreach ($nc3_bbs_posts as $nc3_bbs_post) {
                // TSV 形式でエクスポート
                if (!empty($journals_tsv)) {
                    $journals_tsv .= "\n";
                }

                $content       = $this->nc3Wysiwyg(null, null, null, null, $nc3_bbs_post->body, 'bbs');

                $journals_tsv .= $this->getCCDatetime($nc3_bbs_post->created) . "\t"; // 0:投稿日時
                $journals_tsv .=                              "\t"; // カテゴリ
                $journals_tsv .= $nc3_bbs_post->status      . "\t";
                $journals_tsv .=                              "\t"; // 承認フラグ
                // データ中にタブ文字が存在するケースがあったため、タブ文字は消すようにした。
                $journals_tsv .= str_replace("\t", "", $nc3_bbs_post->subject) . "\t";
                $journals_tsv .= $content                   . "\t";
                $journals_tsv .=                              "\t"; // more_content
                $journals_tsv .=                              "\t"; // more_title
                $journals_tsv .=                              "\t"; // hide_more_title
                $journals_tsv .= $nc3_bbs_post->parent_id   . "\t"; // 親ID
                $journals_tsv .= $nc3_bbs_post->topic_id .    "\t"; // トピックID
                $journals_tsv .= $this->getCCDatetime($nc3_bbs_post->newest_time) . "\t"; // 11:最新投稿日時
                $journals_tsv .= $nc3_bbs_post->insert_user_name . "\t"; // 12:投稿者名
                $journals_tsv .= $nc3_bbs_post->vote_num    . "\t"; // いいね数
                $journals_tsv .=                              "\t"; // いいねのsession_id & nc3 user_id
                $journals_tsv .= $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_bbs_post->created_user) . "\t";   // 15:投稿者ID
                $journals_tsv .= $this->getCCDatetime($nc3_bbs_post->modified) . "\t";                               // 16:更新日時
                $journals_tsv .= $nc3_bbs_post->update_user_name . "\t";                                                // 17:更新者名
                $journals_tsv .= $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_bbs_post->modified_user) . "\t";   // 18:更新者ID

                // 記事のタイトルの一覧
                // タイトルに " あり
                if (strpos($nc3_bbs_post->subject, '"')) {
                    // ログ出力
                    $this->putError(1, 'BBS subject in double-quotation', "タイトル = " . $nc3_bbs_post->subject);
                }
                $journals_ini .= "post_title[" . $nc3_bbs_post->post_id . "] = \"" . str_replace('"', '', $nc3_bbs_post->subject) . "\"\n";
            }

            // bbs->blog移行の場合は、blog用のフォルダに吐き出す
            $export_path = 'bbses/bbs_';
            if ($this->plugin_name['bbs'] === 'blogs') {
                $export_path = 'blogs/blog_bbs_';
            }

            // blog の設定
            //Storage::put($this->getImportPath('blogs/blog_bbs_') . $this->zeroSuppress($nc3_bbs_post->bbs_id) . '.ini', $journals_ini);
            $this->storagePut($this->getImportPath($export_path) . $this->zeroSuppress($nc3_bbs->bbs_id) . '.ini', $journals_ini);

            // blog の記事
            //Storage::put($this->getImportPath('blogs/blog_bbs_') . $this->zeroSuppress($nc3_bbs_post->bbs_id) . '.tsv', $journals_tsv);
            $journals_tsv = $this->exportStrReplace($journals_tsv, 'bbses');
            $this->storagePut($this->getImportPath($export_path) . $this->zeroSuppress($nc3_bbs->bbs_id) . '.tsv', $journals_tsv);
        }
    }

    /**
     * NC3：FAQ（Faq）の移行
     */
    private function nc3ExportFaq($redo)
    {
        $this->putMonitor(3, "Start nc3ExportFaq.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('faqs/'));
        }

        // NC3FAQ（Faq）を移行する。
        $nc3_faqs = Nc2Faq::orderBy('faq_id')->get();

        // 空なら戻る
        if ($nc3_faqs->isEmpty()) {
            return;
        }

        // NC3FAQ（Faq）のループ
        foreach ($nc3_faqs as $nc3_faq) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_faq->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // このFAQが配置されている最初のページオブジェクトを取得しておく
            // WYSIWYG で相対パスを絶対パスに変換する際に、ページの固定URL が必要になるため。
            $nc3_page = null;
            $nc3_faq_block = Nc2FaqBlock::where('faq_id', $nc3_faq->faq_id)->orderBy('block_id', 'asc')->first();
            if (!empty($nc3_faq_block)) {
                $nc3_block = Nc2Block::where('block_id', $nc3_faq_block->block_id)->first();
            }
            if (!empty($nc3_block)) {
                $nc3_page = Nc2Page::where('page_id', $nc3_block->page_id)->first();
            }

            $faqs_ini = "";
            $faqs_ini .= "[faq_base]\n";
            $faqs_ini .= "faq_name = \"" . $nc3_faq->faq_name . "\"\n";
            $faqs_ini .= "view_count = 10\n";

            // NC3 情報
            $faqs_ini .= "\n";
            $faqs_ini .= "[source_info]\n";
            $faqs_ini .= "faq_id = " . $nc3_faq->faq_id . "\n";
            $faqs_ini .= "room_id = " . $nc3_faq->room_id . "\n";
            $faqs_ini .= "module_name = \"faq\"\n";

            // NC3FAQで使ってるカテゴリ（faq_category）のみ移行する。
            $faqs_ini .= "\n";
            $faqs_ini .= "[categories]\n";
            // $nc3_faq_categories = Nc2FaqCategory::where('faq_id', $nc3_faq->faq_id)->orderBy('display_sequence')->get();
            $nc3_faq_categories = Nc2FaqCategory::
                select(
                    'faq_category.category_id',
                    'faq_category.category_name'
                )
                ->where('faq_category.faq_id', $nc3_faq->faq_id)
                ->join('faq_question', function ($join) {
                    $join->on('faq_question.category_id', '=', 'faq_category.category_id')
                         ->whereColumn('faq_question.faq_id', 'faq_category.faq_id');
                })
                ->groupBy(
                    'faq_category.category_id',
                    'faq_category.category_name',
                    'faq_category.display_sequence'
                )
                ->orderBy('faq_category.display_sequence')
                ->get();

            $faqs_ini_originals = "";

            foreach ($nc3_faq_categories as $nc3_faq_category) {
                $faqs_ini_originals .= "original_categories[" . $nc3_faq_category->category_id . "] = \"" . $nc3_faq_category->category_name . "\"\n";
            }
            if (!empty($faqs_ini_originals)) {
                $faqs_ini .= $faqs_ini_originals;
            }

            // NC3FAQの記事（faq_question）を移行する。
            $nc3_faq_questions = Nc2FaqQuestion::where('faq_id', $nc3_faq->faq_id)->orderBy('display_sequence')->get();

            // FAQの記事はTSV でエクスポート
            // カテゴリID{\t}表示順{\t}タイトル{\t}本文
            $faqs_tsv = "";

            // NC3FAQの記事をループ
            // $faqs_ini .= "\n";
            // $faqs_ini .= "[faq_question]\n";
            foreach ($nc3_faq_questions as $nc3_faq_question) {
                // TSV 形式でエクスポート
                if (!empty($faqs_tsv)) {
                    $faqs_tsv .= "\n";
                }

                $category_obj  = $nc3_faq_categories->firstWhere('category_id', $nc3_faq_question->category_id);
                $category = "";
                if (!empty($category_obj)) {
                    $category  = $category_obj->category_name;
                }

                $question_answer = $this->nc3Wysiwyg(null, null, null, null, $nc3_faq_question->question_answer, 'faq');

                $faqs_tsv .= $category                       . "\t";
                $faqs_tsv .= $nc3_faq_question->display_sequence . "\t";
                $faqs_tsv .= $this->getCCDatetime($nc3_faq_question->created)      . "\t";
                $faqs_tsv .= $nc3_faq_question->question_name    . "\t";
                $faqs_tsv .= $question_answer                . "\t";

                // $faqs_ini .= "post_title[" . $nc3_faq_question->question_id . "] = \"" . str_replace('"', '', $nc3_faq_question->question_name) . "\"\n";
            }

            // FAQ の設定
            //Storage::put($this->getImportPath('faqs/faq_') . $this->zeroSuppress($nc3_faq->faq_id) . '.ini', $faqs_ini);
            $this->storagePut($this->getImportPath('faqs/faq_') . $this->zeroSuppress($nc3_faq->faq_id) . '.ini', $faqs_ini);

            // FAQ の記事
            //Storage::put($this->getImportPath('faqs/faq_') . $this->zeroSuppress($nc3_faq->faq_id) . '.tsv', $faqs_tsv);
            $faqs_tsv = $this->exportStrReplace($faqs_tsv, 'faqs');
            $this->storagePut($this->getImportPath('faqs/faq_') . $this->zeroSuppress($nc3_faq->faq_id) . '.tsv', $faqs_tsv);
        }
    }

    /**
     * NC3：リンクリスト（Linklist）の移行
     */
    private function nc3ExportLinklist($redo)
    {
        $this->putMonitor(3, "Start nc3ExportLinklist.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('linklists/'));
        }

        // NC3リンクリスト（Linklist）を移行する。
        $nc3_linklists = Nc2Linklist::orderBy('linklist_id')->get();

        // 空なら戻る
        if ($nc3_linklists->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc2User::get();

        // NC3リンクリスト（Linklist）のループ
        foreach ($nc3_linklists as $nc3_linklist) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_linklist->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // target 指定を取るために最初のブロックを参照（NC3 はブロック単位でtarget 指定していた。最初を移行する）
            $nc3_linklist_block = Nc2LinklistBlock::firstOrNew(
                ['linklist_id' => $nc3_linklist->linklist_id],
                ['target_blank_flag' => '0']
            );

            // (NC3)mark リストマーカー -> (Connect)type 表示形式 変換
            $convert_types = [
                'none'        => LinklistType::none,
                'disc'        => LinklistType::black_circle,
                'circle'      => LinklistType::white_circle,
                'square'      => LinklistType::black_square,
                'lower-alpha' => LinklistType::english_lowercase,
                'upper-alpha' => LinklistType::english_uppercase,
                'mark_a1.gif' => LinklistType::black_square,
                'mark_a2.gif' => LinklistType::black_square,
                'mark_a3.gif' => LinklistType::black_square,
                'mark_a4.gif' => LinklistType::black_square,
                'mark_a5.gif' => LinklistType::black_square,
                'mark_b1.gif' => LinklistType::black_square,
                'mark_b2.gif' => LinklistType::black_square,
                'mark_b3.gif' => LinklistType::black_square,
                'mark_c1.gif' => LinklistType::black_square,
                'mark_c2.gif' => LinklistType::black_square,
                'mark_c3.gif' => LinklistType::black_square,
                'mark_c4.gif' => LinklistType::black_square,
                'mark_d1.gif' => LinklistType::black_square,
                'mark_d2.gif' => LinklistType::black_square,
                'mark_d3.gif' => LinklistType::black_square,
                'mark_d4.gif' => LinklistType::black_square,
                'mark_d5.gif' => LinklistType::black_square,
                'mark_e1.gif' => LinklistType::white_circle,
                'mark_e2.gif' => LinklistType::white_circle,
                'mark_e3.gif' => LinklistType::white_circle,
                'mark_e4.gif' => LinklistType::white_circle,
                'mark_e5.gif' => LinklistType::white_circle,
            ];

            $type = $convert_types[$nc3_linklist_block->mark] ?? LinklistType::none;

            $linklists_ini = "";
            $linklists_ini .= "[linklist_base]\n";
            $linklists_ini .= "linklist_name = \"" . $nc3_linklist->linklist_name . "\"\n";
            // $linklists_ini .= "view_count = 10\n";
            $linklists_ini .= "type = " . $type . "\n";

            // NC3 情報
            $linklists_ini .= "\n";
            $linklists_ini .= "[source_info]\n";
            $linklists_ini .= "linklist_id = " . $nc3_linklist->linklist_id . "\n";
            $linklists_ini .= "room_id = " . $nc3_linklist->room_id . "\n";
            $linklists_ini .= "module_name = \"linklist\"\n";
            $linklists_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_linklist->created) . "\"\n";
            $linklists_ini .= "created_name    = \"" . $nc3_linklist->insert_user_name . "\"\n";
            $linklists_ini .= "insert_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_linklist->created_user) . "\"\n";
            $linklists_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_linklist->modified) . "\"\n";
            $linklists_ini .= "updated_name    = \"" . $nc3_linklist->update_user_name . "\"\n";
            $linklists_ini .= "update_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_linklist->modified_user) . "\"\n";

            // NC3リンクリストで使っているカテゴリ（linklist_category）のみ移行する。
            $linklists_ini .= "\n";
            $linklists_ini .= "[categories]\n";
            // NC3リンクリストは自動的に「カテゴリなし」（名前変更不可）カテゴリが作成されるため、「カテゴリなし」は移行除外する。
            // ※ また、NC3では「カテゴリなし」１個だけだと、カテゴリを表示しない仕様
            // $nc3_linklist_categories = Nc2LinklistCategory::where('linklist_id', $nc3_linklist->linklist_id)->where('category_name', '!=','カテゴリなし')->orderBy('category_sequence')->get();
            $nc3_linklist_categories = Nc2LinklistCategory::
                select(
                    'linklist_category.category_id',
                    'linklist_category.category_name'
                )
                ->where('linklist_category.linklist_id', $nc3_linklist->linklist_id)
                ->where('category_name', '!=', 'カテゴリなし')
                ->join('linklist_link', function ($join) {
                    $join->on('linklist_link.category_id', '=', 'linklist_category.category_id')
                         ->whereColumn('linklist_link.linklist_id', 'linklist_category.linklist_id');
                })
                ->groupBy(
                    'linklist_category.category_id',
                    'linklist_category.category_name',
                    'linklist_category.category_sequence'
                )
                ->orderBy('linklist_category.category_sequence')
                ->get();

            $linklists_ini_originals = "";

            foreach ($nc3_linklist_categories as $nc3_linklist_category) {
                $linklists_ini_originals .= "original_categories[" . $nc3_linklist_category->category_id . "] = \"" . $nc3_linklist_category->category_name . "\"\n";
            }
            if (!empty($linklists_ini_originals)) {
                $linklists_ini .= $linklists_ini_originals;
            }

            // NC3リンクリストの記事（linklist_link）を移行する。
            $nc3_linklist_links = Nc2LinklistLink::where('linklist_id', $nc3_linklist->linklist_id)->orderBy('link_sequence')->get();

            // リンクリストの記事はTSV でエクスポート
            // タイトル{\t}URL{\t}説明{\t}新規ウィンドウflag{\t}表示順
            $linklists_tsv = "";

            $nc3_block = Nc2Block::where('block_id', $nc3_linklist_block->block_id)->first();

            // NC3リンクリストの記事をループ
            // $linklists_ini .= "\n";
            // $linklists_ini .= "[linklist_link]\n";
            foreach ($nc3_linklist_links as $nc3_linklist_link) {
                // TSV 形式でエクスポート
                if (!empty($linklists_tsv)) {
                    $linklists_tsv .= "\n";
                }

                $category_obj  = $nc3_linklist_categories->firstWhere('category_id', $nc3_linklist_link->category_id);
                $category = "";
                if (!empty($category_obj)) {
                    $category  = $category_obj->category_name;
                }

                $linklists_tsv .= str_replace(array("\r", "\n", "\t"), "", $nc3_linklist_link->title)              . "\t";
                $linklists_tsv .= str_replace(array("\r", "\n", "\t"), "", $nc3_linklist_link->url)                . "\t";
                $linklists_tsv .= str_replace(array("\r", "\n", "\t"), " ", $nc3_linklist_link->description)       . "\t";
                $linklists_tsv .= $nc3_linklist_block->target_blank_flag                        . "\t";
                $linklists_tsv .= $nc3_linklist_link->link_sequence                             . "\t";
                $linklists_tsv .= $category;

                // NC3のリンク切れチェック
                $this->checkDeadLinkNc2($nc3_linklist_link->url, 'linklist', $nc3_block);

                // $linklists_ini .= "post_title[" . $nc3_linklist_link->link_id . "] = \"" . str_replace('"', '', $nc3_linklist_link->title) . "\"\n";
            }

            // リンクリストの設定
            //Storage::put($this->getImportPath('linklists/linklist_') . $this->zeroSuppress($nc3_linklist->linklist_id) . '.ini', $linklists_ini);
            $this->storagePut($this->getImportPath('linklists/linklist_') . $this->zeroSuppress($nc3_linklist->linklist_id) . '.ini', $linklists_ini);

            // リンクリストの記事
            //Storage::put($this->getImportPath('linklists/linklist_') . $this->zeroSuppress($nc3_linklist->linklist_id) . '.tsv', $linklists_tsv);
            $this->storagePut($this->getImportPath('linklists/linklist_') . $this->zeroSuppress($nc3_linklist->linklist_id) . '.tsv', $linklists_tsv);
        }
    }

    /**
     * NC3：汎用データベース（Multidatabase）の移行
     */
    private function nc3ExportMultidatabase($redo)
    {
        $this->putMonitor(3, "Start nc3ExportMultidatabase.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('databases/'));
        }

        // NC3汎用データベース（Multidatabase）を移行する。
        $nc3_export_where_multidatabase_ids = $this->getMigrationConfig('databases', 'nc3_export_where_multidatabase_ids');

        if (empty($nc3_export_where_multidatabase_ids)) {
            $nc3_multidatabases = Nc2Multidatabase::orderBy('multidatabase_id')->get();
        } else {
            $nc3_multidatabases = Nc2Multidatabase::whereIn('multidatabase_id', $nc3_export_where_multidatabase_ids)->orderBy('multidatabase_id')->get();
        }

        // 空なら戻る
        if ($nc3_multidatabases->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc2User::get();

        // NC3汎用データベース（Multidatabase）のループ
        foreach ($nc3_multidatabases as $nc3_multidatabase) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_multidatabase->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            $multidatabase_id = $nc3_multidatabase->multidatabase_id;

            // データベース設定
            $multidatabase_ini = "";
            $multidatabase_ini .= "[database_base]\n";
            $multidatabase_ini .= "database_name = \"" . $nc3_multidatabase->multidatabase_name . "\"\n";

            // multidatabase_block の取得
            // 1DB で複数ブロックがあるので、Join せずに、個別に読む
            $nc3_multidatabase_block = Nc2MultidatabaseBlock::where('multidatabase_id', $nc3_multidatabase->multidatabase_id)->orderBy('block_id', 'asc')->first();
            if (empty($nc3_multidatabase_block)) {
                $multidatabase_ini .= "view_count = 10\n";  // 初期値
            } else {
                $multidatabase_ini .= "view_count = " . $nc3_multidatabase_block->visible_item . "\n";
            }

            // この汎用データベースが配置されている最初のページオブジェクトを取得しておく
            // WYSIWYG で相対パスを絶対パスに変換する際に、ページの固定URL が必要になるため。
            $nc3_page = null;
            if (!empty($nc3_multidatabase_block)) {
                $nc3_block = Nc2Block::where('block_id', $nc3_multidatabase_block->block_id)->first();
            }
            if (!empty($nc3_block)) {
                $nc3_page = Nc2Page::where('page_id', $nc3_block->page_id)->first();
            }

            // NC3 情報
            $multidatabase_ini .= "\n";
            $multidatabase_ini .= "[source_info]\n";
            $multidatabase_ini .= "multidatabase_id = " . $nc3_multidatabase->multidatabase_id . "\n";
            $multidatabase_ini .= "room_id = " . $nc3_multidatabase->room_id . "\n";
            $multidatabase_ini .= "module_name = \"multidatabase\"\n";
            $multidatabase_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_multidatabase->created) . "\"\n";
            $multidatabase_ini .= "created_name    = \"" . $nc3_multidatabase->insert_user_name . "\"\n";
            $multidatabase_ini .= "insert_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_multidatabase->created_user) . "\"\n";
            $multidatabase_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_multidatabase->modified) . "\"\n";
            $multidatabase_ini .= "updated_name    = \"" . $nc3_multidatabase->update_user_name . "\"\n";
            $multidatabase_ini .= "update_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_multidatabase->modified_user) . "\"\n";

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

            // 行情報
            //$row_group_header = 0;
            //$row_group_left = 0;
            //$row_group_right = 0;
            //$row_group_footer = 0;

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
                if ($multidatabase_metadata->display_pos == 1) {
                    //$row_group_header++;
                    $multidatabase_cols_rows[$metadata_id]["row_group"]    = 1;
                    $multidatabase_cols_rows[$metadata_id]["column_group"] = 1;
                }
                if ($multidatabase_metadata->display_pos == 2) {
                    //$row_group_left++;
                    $multidatabase_cols_rows[$metadata_id]["row_group"]    = 2;
                    $multidatabase_cols_rows[$metadata_id]["column_group"] = 1;
                }
                if ($multidatabase_metadata->display_pos == 3) {
                    //$row_group_right++;
                    $multidatabase_cols_rows[$metadata_id]["row_group"]    = 2;
                    $multidatabase_cols_rows[$metadata_id]["column_group"] = 2;
                }
                if ($multidatabase_metadata->display_pos == 4) {
                    //$row_group_footer++;
                    $multidatabase_cols_rows[$metadata_id]["row_group"]    = 3;
                    $multidatabase_cols_rows[$metadata_id]["column_group"] = 1;
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
            $display_sequence = 0;  // 順番は振りなおす。（NC3 は4つのエリアごとの順番のため）
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

            $tsv_header .= "status" . "\t" . "display_sequence" . "\t" . "posted_at" . "\t" .
                "created_at" . "\t" . "created_name" . "\t" . "insert_login_id" . "\t" . "updated_at" . "\t" . "updated_name" . "\t" . "update_login_id" . "\t" .
                "content_id";

            $tsv_cols['status'] = "";
            $tsv_cols['display_sequence'] = "";
            $tsv_cols['posted_at'] = "";
            $tsv_cols['created_at'] = "";
            $tsv_cols['created_name'] = "";
            $tsv_cols['insert_login_id'] = "";
            $tsv_cols['updated_at'] = "";
            $tsv_cols['updated_name'] = "";
            $tsv_cols['update_login_id'] = "";
            $tsv_cols['content_id'] = "";

            // データベースの記事
            $multidatabase_metadata_contents = Nc2MultidatabaseMetadataContent::
                select(
                    'multidatabase_metadata_content.*',
                    'multidatabase_metadata.type',
                    'multidatabase_content.agree_flag',
                    'multidatabase_content.temporary_flag',
                    'multidatabase_content.display_sequence as content_display_sequence',
                    'multidatabase_content.insert_time as multidatabase_content_insert_time',
                    'multidatabase_content.insert_user_name as multidatabase_content_insert_user_name',
                    'multidatabase_content.insert_user_id as multidatabase_content_insert_user_id',
                    'multidatabase_content.update_time as multidatabase_content_update_time',
                    'multidatabase_content.update_user_name as multidatabase_content_update_user_name',
                    'multidatabase_content.update_user_id as multidatabase_content_update_user_id'
                )
                ->join('multidatabase_metadata', 'multidatabase_metadata.metadata_id', '=', 'multidatabase_metadata_content.metadata_id')
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
            Storage::delete($this->getImportPath('databases/database_') . $this->zeroSuppress($multidatabase_id) . '.tsv');
            $tsv = '';
            $old_metadata_content = null; // コントロールブレイク用、一つ前のレコード（追加・更新日時で使用）
            foreach ($multidatabase_metadata_contents as $multidatabase_metadata_content) {
                // レコードのID が変わった＝コントロールブレイク
                if ($content_id != $multidatabase_metadata_content->content_id) {
                    if ($content_id == 0) {
                        // 最初の1件
                        //Storage::append($this->getImportPath('databases/database_') . $this->zeroSuppress($multidatabase_id) . '.tsv', $tsv_header);
                        $old_metadata_content = $multidatabase_metadata_content;
                        $tsv .= $tsv_header . "\n";
                    } else {
                        // 承認待ち、一時保存
                        $tsv_record['status'] = 0;
                        if ($old_metadata_content->agree_flag == 1) {
                            $tsv_record['status'] = 1;
                        }
                        if ($old_metadata_content->temporary_flag == 1) {
                            $tsv_record['status'] = 2;
                        }
                        // 表示順
                        $tsv_record['display_sequence'] = $old_metadata_content->content_display_sequence;
                        // 投稿日
                        $tsv_record['posted_at']       = $this->getCCDatetime($old_metadata_content->multidatabase_content_insert_time);
                        // 登録日時、更新日時等
                        $tsv_record['created_at']      = $this->getCCDatetime($old_metadata_content->multidatabase_content_insert_time);
                        $tsv_record['created_name']    = $old_metadata_content->multidatabase_content_insert_user_name;
                        $tsv_record['insert_login_id'] = $this->getNc3LoginIdFromNc3UserId($nc3_users, $old_metadata_content->multidatabase_content_insert_user_id);
                        $tsv_record['updated_at']      = $this->getCCDatetime($old_metadata_content->multidatabase_content_update_time);
                        $tsv_record['updated_name']    = $old_metadata_content->multidatabase_content_update_user_name;
                        $tsv_record['update_login_id'] = $this->getNc3LoginIdFromNc3UserId($nc3_users, $old_metadata_content->multidatabase_content_update_user_id);
                        // NC3 レコードを示すID
                        $tsv_record['content_id'] = $old_metadata_content->content_id;
                        // データ行の書き出し
                        //Storage::append($this->getImportPath('databases/database_') . $this->zeroSuppress($multidatabase_id) . '.tsv', implode("\t", $tsv_record));
                        $tsv .= implode("\t", $tsv_record) . "\n";
                    }
                    $content_id = $multidatabase_metadata_content->content_id;
                    $tsv_record = $tsv_cols;
                }
                $content = str_replace("\n", "<br />", $multidatabase_metadata_content->content);

                // メタデータの型による変換
                if ($multidatabase_metadata_content->type == 0 || $multidatabase_metadata_content->type == 5) {
                    // 画像型、ファイル型
                    if (strpos($content, '?action=multidatabase_action_main_filedownload&upload_id=') !== false) {
                        // NC3 のアップロードID 抜き出し
                        $nc3_uploads_id = str_replace('?action=multidatabase_action_main_filedownload&upload_id=', '', $content);
                        // uploads.ini からファイルを探す
                        if (array_key_exists('uploads', $this->uploads_ini) && array_key_exists('upload', $this->uploads_ini['uploads']) && array_key_exists($nc3_uploads_id, $this->uploads_ini['uploads']['upload'])) {
                            if (array_key_exists($nc3_uploads_id, $this->uploads_ini) && array_key_exists('temp_file_name', $this->uploads_ini[$nc3_uploads_id])) {
                                $content = '../../uploads/' . $this->uploads_ini[$nc3_uploads_id]['temp_file_name'];
                            } else {
                                $this->putMonitor(3, "No Match uploads_ini array_key_exists temp_file_name.", "nc3_uploads_id = " . $nc3_uploads_id);
                            }
                        } else {
                            $this->putMonitor(3, "No Match uploads_ini array_key_exists uploads_ini_uploads_upload.", "nc3_uploads_id = " . $nc3_uploads_id);
                        }
                    } else {
                        $this->putMonitor(3, "No Match content strpos. :". $content);
                    }
                } elseif ($multidatabase_metadata_content->type == 6) {
                    // WYSIWYG
                    $content = $this->nc3Wysiwyg(null, null, null, null, $content, 'multidatabase');
                } elseif ($multidatabase_metadata_content->type == 9) {
                    // 日付型
                    if (!empty($content) && strlen($content) == 14) {
                        $content = $this->getCCDatetime($content);
                    }
                } elseif ($multidatabase_metadata_content->type == 3) {
                    // リンク. NC3のリンク切れチェック
                    $this->checkDeadLinkNc2($content, 'multidatabase', $nc3_block);
                }
                // データ中にタブ文字が存在するケースがあったため、タブ文字は半角スペースに置き換えるようにした。
                $tsv_record[$multidatabase_metadata_content->metadata_id] = str_replace("\t", " ", $content);
                $old_metadata_content = $multidatabase_metadata_content;
            }
            // 最後の行の登録日時、更新日時
            // レコードがない場合もあり得る。
            if (!empty($old_metadata_content)) {
                // 承認待ち、一時保存
                $tsv_record['status'] = 0;
                if ($old_metadata_content->agree_flag == 1) {
                    $tsv_record['status'] = 1;
                }
                if ($old_metadata_content->temporary_flag == 1) {
                    $tsv_record['status'] = 2;
                }
                // 表示順
                $tsv_record['display_sequence'] = $old_metadata_content->content_display_sequence;
                // 投稿日
                $tsv_record['posted_at']       = $this->getCCDatetime($old_metadata_content->multidatabase_content_insert_time);
                // 登録日時、更新日時等
                $tsv_record['created_at']      = $this->getCCDatetime($old_metadata_content->multidatabase_content_insert_time);
                $tsv_record['created_name']    = $old_metadata_content->multidatabase_content_insert_user_name;
                $tsv_record['insert_login_id'] = $this->getNc3LoginIdFromNc3UserId($nc3_users, $old_metadata_content->multidatabase_content_insert_user_id);
                $tsv_record['updated_at']      = $this->getCCDatetime($old_metadata_content->multidatabase_content_update_time);
                $tsv_record['updated_name']    = $old_metadata_content->multidatabase_content_update_user_name;
                $tsv_record['update_login_id'] = $this->getNc3LoginIdFromNc3UserId($nc3_users, $old_metadata_content->multidatabase_content_update_user_id);
                $tsv_record['content_id'] = $old_metadata_content->content_id;
                $tsv .= implode("\t", $tsv_record);
            }

            // データ行の書き出し
            //Storage::append($this->getImportPath('databases/database_') . $this->zeroSuppress($multidatabase_id) . '.tsv', implode("\t", $tsv_record));
            //Storage::append($this->getImportPath('databases/database_') . $this->zeroSuppress($multidatabase_id) . '.tsv', $tsv);
            $tsv = $this->exportStrReplace($tsv, 'databases');
            $this->storageAppend($this->getImportPath('databases/database_') . $this->zeroSuppress($multidatabase_id) . '.tsv', $tsv);

            // detabase の設定
            //Storage::put($this->getImportPath('databases/database_') . $this->zeroSuppress($multidatabase_id) . '.ini', $multidatabase_ini);
            $this->storagePut($this->getImportPath('databases/database_') . $this->zeroSuppress($multidatabase_id) . '.ini', $multidatabase_ini);
        }
    }

    /**
     * NC3：登録フォーム（Registration）の移行
     */
    private function nc3ExportRegistration($redo)
    {
        $this->putMonitor(3, "Start nc3ExportRegistration.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('forms/'));
        }

        // NC3登録フォーム（Registration）を移行する。
        $nc3_export_where_registration_ids = $this->getMigrationConfig('forms', 'nc3_export_where_registration_ids');

        if (empty($nc3_export_where_registration_ids)) {
            $nc3_registrations = Nc2Registration::orderBy('registration_id')->get();
        } else {
            $nc3_registrations = Nc2Registration::whereIn('registration_id', $nc3_export_where_registration_ids)->orderBy('registration_id')->get();
        }

        // 空なら戻る
        if ($nc3_registrations->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc2User::get();

        // NC3登録フォーム（Registration）のループ
        foreach ($nc3_registrations as $nc3_registration) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_registration->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // 対象外指定があれば、読み飛ばす
            if ($this->isOmmit('forms', 'export_ommit_registration_ids', $nc3_registration->registration_id)) {
                continue;
            }

            // (nc3) mail_send = (1)登録をメールで通知する          => 通知メールアドレスありなら (cc) mail_send_flag = 以下のアドレスにメール送信するON
            //     (nc3) regist_user_send = 登録者本人にメールする  => (cc) user_mail_send_flag = 登録者にメール送信する
            // (nc3) mail_send = (0)登録をメールで通知しない        => (cc) mail_send_flag      = (0 固定) 以下のアドレスにメール送信しない
            //                                                    => (cc) user_mail_send_flag = (0 固定) 登録者にメール送信しない
            // (nc3) rcpt_to = 主担以外で通知するメールアドレス      => (cc) mail_send_address   = 送信するメールアドレス（複数ある場合はカンマで区切る）

            $mail_send_address = $nc3_registration->rcpt_to;

            // (nc3) mail_send = 登録をメールで通知する
            if ($nc3_registration->mail_send) {
                // メール通知ON
                $user_mail_send_flag = $nc3_registration->regist_user_send;
                // 通知メールアドレスありなら (cc) mail_send_flag = 以下のアドレスにメール送信するON
                $mail_send_flag = $mail_send_address ? 1 : 0;

            } else {
                // メール通知OFF
                $user_mail_send_flag = 0;
                $mail_send_flag = 0;
            }

            $registration_id = $nc3_registration->registration_id;
            $regist_control_flag = $nc3_registration->period ? 1 : 0;
            $regist_to =  $nc3_registration->period ? $this->getCCDatetime($nc3_registration->period) : '';

            // 登録フォーム設定
            $registration_ini = "";
            $registration_ini .= "[form_base]\n";
            $registration_ini .= "forms_name = \""        . $nc3_registration->registration_name . "\"\n";
            $registration_ini .= "mail_send_flag = "      . $mail_send_flag . "\n";
            $registration_ini .= "mail_send_address = \"" . $mail_send_address . "\"\n";
            $registration_ini .= "user_mail_send_flag = " . $user_mail_send_flag . "\n";
            $registration_ini .= "mail_subject = \""      . $nc3_registration->mail_subject . "\"\n";
            $registration_ini .= "mail_format = \""       . str_replace("\n", '\n', $nc3_registration->mail_body) . "\"\n";
            $registration_ini .= "data_save_flag = 1\n";
            $registration_ini .= "after_message = \""     . str_replace("\n", '\n', $nc3_registration->accept_message) . "\"\n";
            $registration_ini .= "numbering_use_flag = 0\n";
            $registration_ini .= "numbering_prefix = null\n";
            $registration_ini .= "regist_control_flag = " . $regist_control_flag. "\n";
            $registration_ini .= "regist_to = \""         . $regist_to . "\"\n";

            // NC3 情報
            $registration_ini .= "\n";
            $registration_ini .= "[source_info]\n";
            $registration_ini .= "registration_id = " . $nc3_registration->registration_id . "\n";
            $registration_ini .= "active_flag = "     . $nc3_registration->active_flag . "\n";
            $registration_ini .= "room_id = "         . $nc3_registration->room_id . "\n";
            $registration_ini .= "module_name = \"registration\"\n";
            $registration_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_registration->created) . "\"\n";
            $registration_ini .= "created_name    = \"" . $nc3_registration->insert_user_name . "\"\n";
            $registration_ini .= "insert_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_registration->created_user) . "\"\n";
            $registration_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_registration->modified) . "\"\n";
            $registration_ini .= "updated_name    = \"" . $nc3_registration->update_user_name . "\"\n";
            $registration_ini .= "update_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_registration->modified_user) . "\"\n";

            // 登録フォームのカラム情報
            $registration_items = Nc2RegistrationItem::where('registration_id', $registration_id)
                ->orderBy('item_sequence', 'asc')
                ->get();

            if (empty($registration_items)) {
                continue;
            }

            // カラム情報出力
            $registration_ini .= "\n";
            $registration_ini .= "[form_columns]\n";

            // カラム情報
            //$forms_columns_rows = array();
            foreach ($registration_items as $registration_item) {
                $registration_ini .= "form_column[" . $registration_item->item_id . "] = \"" . $registration_item->item_name . "\"\n";
            }
            $registration_ini .= "\n";

            // カラム詳細情報
            foreach ($registration_items as $registration_item) {
                $item_id = $registration_item->item_id;

                $registration_ini .= "[" . $item_id . "]" . "\n";

                // type
                if ($registration_item->item_type == 1) {
                    $column_type = "text";
                } elseif ($registration_item->item_type == 2) {
                    $column_type = "checkbox";
                } elseif ($registration_item->item_type == 3) {
                    $column_type = "radio";
                } elseif ($registration_item->item_type == 4) {
                    $column_type = "select";
                } elseif ($registration_item->item_type == 5) {
                    $column_type = "textarea";
                } elseif ($registration_item->item_type == 6) {
                    $column_type = "mail";
                } elseif ($registration_item->item_type == 7) {
                    $column_type = "file";
                }

                $item_id = $registration_item->item_id;
                $registration_ini .= "column_type                = \"" . $column_type                     . "\"\n";
                $registration_ini .= "column_name                = \"" . $registration_item->item_name    . "\"\n";
                $registration_ini .= "option_value               = \"" . $registration_item->option_value . "\"\n";
                $registration_ini .= "required                   = "   . $registration_item->require_flag . "\n";
                $registration_ini .= "frame_col                  = "   . 0                                . "\n";
                $registration_ini .= "caption                    = \"" . $registration_item->description  . "\"\n";
                $registration_ini .= "caption_color              = \"" . "text-dark"                      . "\"\n";
                $registration_ini .= "minutes_increments         = "   . 10                               . "\n";
                $registration_ini .= "minutes_increments_from    = "   . 10                               . "\n";
                $registration_ini .= "minutes_increments_to      = "   . 10                               . "\n";
                $registration_ini .= "rule_allowed_numeric       = null\n";
                $registration_ini .= "rule_allowed_alpha_numeric = null\n";
                $registration_ini .= "rule_digits_or_less        = null\n";
                $registration_ini .= "rule_max                   = null\n";
                $registration_ini .= "rule_min                   = null\n";
                $registration_ini .= "rule_word_count            = null\n";
                $registration_ini .= "rule_date_after_equal      = null\n";
                $registration_ini .= "\n";
            }

            // フォーム の設定
            //Storage::put($this->getImportPath('forms/form_') . $this->zeroSuppress($registration_id) . '.ini', $registration_ini);
            $this->storagePut($this->getImportPath('forms/form_') . $this->zeroSuppress($registration_id) . '.ini', $registration_ini);

            // 登録データもエクスポートする場合
            if ($this->hasMigrationConfig('forms', 'nc3_export_registration_data', true)) {
                // 対象外指定があれば、読み飛ばす
                if ($this->isOmmit('forms', 'export_ommit_registration_data_ids', $nc3_registration->registration_id)) {
                    continue;
                }

                // データ部
                $registration_data_header = "[form_inputs]\n";
                $registration_data = "";
                $registration_item_datas = Nc2RegistrationItemData::
                    select(
                        'registration_item_data.*',
                        'registration_data.insert_time AS data_insert_time',
                        'registration_data.insert_user_name AS data_insert_user_name',
                        'registration_data.insert_user_id AS data_insert_user_id',
                        'registration_data.update_time AS data_update_time',
                        'registration_data.update_user_name AS data_update_user_name',
                        'registration_data.update_user_id AS data_update_user_id'
                    )
                    ->join('registration_item', function ($join) {
                        $join->on('registration_item.registration_id', '=', 'registration_item_data.registration_id')
                            ->on('registration_item.item_id', '=', 'registration_item_data.item_id');
                    })
                    ->join('registration_data', function ($join) {
                        $join->on('registration_data.registration_id', '=', 'registration_item_data.registration_id')
                            ->on('registration_data.data_id', '=', 'registration_item_data.data_id');
                    })
                    ->where('registration_item_data.registration_id', $registration_id)
                    ->orderBy('registration_item_data.data_id', 'asc')
                    ->orderBy('registration_item.item_sequence', 'asc')
                    ->get();

                $data_id = null;
                foreach ($registration_item_datas as $registration_item_data) {
                    if ($registration_item_data->data_id != $data_id) {
                        $registration_data_header .= "input[" . $registration_item_data->data_id . "] = \"\"\n";
                        $registration_data .= "\n[" . $registration_item_data->data_id . "]\n";
                        $registration_data .= "created_at      = \"" . $this->getCCDatetime($registration_item_data->data_insert_time) . "\"\n";
                        $registration_data .= "created_name    = \"" . $registration_item_data->data_insert_user_name . "\"\n";
                        $registration_data .= "insert_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $registration_item_data->data_insert_user_id) . "\"\n";
                        $registration_data .= "updated_at      = \"" . $this->getCCDatetime($registration_item_data->data_update_time) . "\"\n";
                        $registration_data .= "updated_name    = \"" . $registration_item_data->data_update_user_name . "\"\n";
                        $registration_data .= "update_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $registration_item_data->data_update_user_id) . "\"\n";
                        $data_id = $registration_item_data->data_id;
                    }
                    $registration_data .= $registration_item_data->item_id . " = \"" . str_replace("\n", '\n', $registration_item_data->item_data_value) . "\"\n";
                }
                // フォーム の登録データ
                //Storage::put($this->getImportPath('forms/form_') . $this->zeroSuppress($registration_id) . '.txt', $registration_data_header . $registration_data);
                $this->storagePut($this->getImportPath('forms/form_') . $this->zeroSuppress($registration_id) . '.txt', $registration_data_header . $registration_data);
            }
        }
    }

    /**
     * NC3：新着情報（Whatsnew）の移行
     */
    private function nc3ExportWhatsnew($redo)
    {
        $this->putMonitor(3, "Start nc3ExportWhatsnew.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('whatsnews/'));
        }

        // NC3新着情報（Whatsnew）を移行する。
        $nc3_whatsnew_blocks_query = Nc2WhatsnewBlock::select('whatsnew_block.*', 'blocks.block_name', 'pages.page_name')
                                                     ->join('blocks', 'blocks.block_id', '=', 'whatsnew_block.block_id');
        $nc3_whatsnew_blocks_query->join('pages', function ($join) {
            $join->on('pages.page_id', '=', 'blocks.page_id')
                 ->where('pages.private_flag', '=', 0);
        });
        $nc3_whatsnew_blocks = $nc3_whatsnew_blocks_query->orderBy('block_id')->get();

        // 空なら戻る
        if ($nc3_whatsnew_blocks->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc2User::get();

        // NC3新着情報（Whatsnew）のループ
        foreach ($nc3_whatsnew_blocks as $nc3_whatsnew_block) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_whatsnew_block->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            $whatsnew_block_id = $nc3_whatsnew_block->block_id;

            // 新着情報設定
            $whatsnew_ini = "";
            $whatsnew_ini .= "[whatsnew_base]\n";

            // 新着情報の名前は、ブロックタイトルがあればブロックタイトル。なければページ名＋「の新着情報」。
            $whatsnew_name = '無題';
            if (!empty($nc3_whatsnew_block->page_name)) {
                $whatsnew_name = $nc3_whatsnew_block->page_name;
            }
            if (!empty($nc3_whatsnew_block->block_name)) {
                $whatsnew_name = $nc3_whatsnew_block->block_name;
            }

            $whatsnew_ini .= "whatsnew_name = \""  . $whatsnew_name . "\"\n";
            $whatsnew_ini .= "view_pattern = "     . ($nc3_whatsnew_block->display_flag == 1 ? 0 : 1) . "\n"; // NC3: 0=日数, 1=件数 Connect-CMS: 0=件数, 1=日数
            $whatsnew_ini .= "count = "            . $nc3_whatsnew_block->display_number . "\n";
            $whatsnew_ini .= "days = "             . $nc3_whatsnew_block->display_days . "\n";
            $whatsnew_ini .= "rss = "              . $nc3_whatsnew_block->allow_rss_feed . "\n";
            $whatsnew_ini .= "rss_count = "        . $nc3_whatsnew_block->display_number . "\n";
            $whatsnew_ini .= "view_posted_name = " . $nc3_whatsnew_block->display_user_name    . "\n";
            $whatsnew_ini .= "view_posted_at = "   . $nc3_whatsnew_block->display_insert_time . "\n";

            // 対象のプラグインを取得（Connect-CMS にまだないものは除外＆ログ出力）
            $display_modules = explode(',', $nc3_whatsnew_block->display_modules);
            $nc3_modules = Nc2Modules::whereIn('module_id', $display_modules)->orderBy('module_id', 'asc')->get();
            $whatsnew_ini .= "target_plugins = \"" . $this->nc3GetModuleNames($nc3_modules->pluck('action_name')) . "\"\n";

            $whatsnew_ini .= "frame_select = 0\n";

            // NC3 情報
            $whatsnew_ini .= "\n";
            $whatsnew_ini .= "[source_info]\n";
            $whatsnew_ini .= "whatsnew_block_id = " . $whatsnew_block_id . "\n";
            $whatsnew_ini .= "room_id = "           . $nc3_whatsnew_block->room_id . "\n";
            $whatsnew_ini .= "module_name = \"whatsnew\"\n";
            $whatsnew_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_whatsnew_block->created) . "\"\n";
            $whatsnew_ini .= "created_name    = \"" . $nc3_whatsnew_block->insert_user_name . "\"\n";
            $whatsnew_ini .= "insert_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_whatsnew_block->created_user) . "\"\n";
            $whatsnew_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_whatsnew_block->modified) . "\"\n";
            $whatsnew_ini .= "updated_name    = \"" . $nc3_whatsnew_block->update_user_name . "\"\n";
            $whatsnew_ini .= "update_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_whatsnew_block->modified_user) . "\"\n";

            // 新着情報の設定を出力
            //Storage::put($this->getImportPath('whatsnews/whatsnew_') . $this->zeroSuppress($whatsnew_block_id) . '.ini', $whatsnew_ini);
            $this->storagePut($this->getImportPath('whatsnews/whatsnew_') . $this->zeroSuppress($whatsnew_block_id) . '.ini', $whatsnew_ini);
        }
    }

    /**
     * NC3：キャビネット（キャビネット）の移行
     */
    private function nc3ExportCabinet($redo)
    {
        $this->putMonitor(3, "Start nc3ExportCabinet.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('cabinets/'));
        }

        // NC3キャビネット（Cabinet）を移行する。
        $where_cabinet_ids = $this->getMigrationConfig('cabinets', 'nc3_export_where_cabinet_ids');
        if (empty($where_cabinet_ids)) {
            $cabinet_manages = Nc2CabinetManage::orderBy('cabinet_id')->get();
        } else {
            $cabinet_manages = Nc2CabinetManage::whereIn('cabinet_id', $where_cabinet_ids)->orderBy('cabinet_id')->get();
        }

        // 空なら戻る
        if ($cabinet_manages->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc2User::get();

        // NC3キャビネット（Cabinet）のループ
        foreach ($cabinet_manages as $cabinet_manage) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($room_ids) && !in_array($cabinet_manage->room_id, $room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // キャビネット設定
            $ini = "";
            $ini .= "[cabinet_base]\n";
            $ini .= "cabinet_name = \"" . $cabinet_manage->cabinet_name . "\"\n";
            $ini .= "active_flag = " .  $cabinet_manage->active_flag . "\n";
            $ini .= "add_authority_id = " . $cabinet_manage->add_authority_id . "\n";
            $ini .= "cabinet_max_size = " . $cabinet_manage->cabinet_max_size . "\n";
            $ini .= "upload_max_size = " . $cabinet_manage->upload_max_size . "\n";

            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "cabinet_id = " . $cabinet_manage->cabinet_id . "\n";
            $ini .= "room_id = " . $cabinet_manage->room_id . "\n";
            $ini .= "module_name = \"cabinet\"\n";
            $ini .= "created_at      = \"" . $this->getCCDatetime($cabinet_manage->created) . "\"\n";
            $ini .= "created_name    = \"" . $cabinet_manage->insert_user_name . "\"\n";
            $ini .= "insert_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $cabinet_manage->created_user) . "\"\n";
            $ini .= "updated_at      = \"" . $this->getCCDatetime($cabinet_manage->modified) . "\"\n";
            $ini .= "updated_name    = \"" . $cabinet_manage->update_user_name . "\"\n";
            $ini .= "update_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $cabinet_manage->modified_user) . "\"\n";

            // ファイル情報
            $cabinet_files = Nc2CabinetFile::select('cabinet_file.*', 'cabinet_comment.comment')
                                ->leftJoin('cabinet_comment', 'cabinet_file.file_id', '=', 'cabinet_comment.file_id')
                                ->where('cabinet_file.cabinet_id', $cabinet_manage->cabinet_id)
                                ->orderBy('cabinet_file.cabinet_id', 'asc')
                                ->orderBy('cabinet_file.depth', 'asc')
                                ->get();
            if (empty($cabinet_files)) {
                continue;
            }

            $tsv = '';
            foreach ($cabinet_files as $index => $cabinet_file) {
                $tsv .= $cabinet_file['file_id'] . "\t";
                $tsv .= $cabinet_file['cabinet_id'] . "\t";
                $tsv .= $cabinet_file['upload_id'] . "\t";
                $tsv .= $cabinet_file['parent_id'] . "\t";
                $tsv .= $cabinet_file['file_name'] . "\t";
                $tsv .= $cabinet_file['extension'] . "\t";
                $tsv .= $cabinet_file['depth'] . "\t";
                $tsv .= $cabinet_file['size'] . "\t";
                $tsv .= $cabinet_file['download_num'] . "\t";
                $tsv .= $cabinet_file['file_type'] . "\t";
                $tsv .= $cabinet_file['display_sequence'] . "\t";
                $tsv .= $cabinet_file['room_id'] . "\t";
                $tsv .= $cabinet_file['comment'] . "\t";
                $tsv .= $this->getCCDatetime($cabinet_file->created)                             . "\t";    // [13]
                $tsv .= $cabinet_file->insert_user_name                                              . "\t";    // [14]
                $tsv .= $this->getNc3LoginIdFromNc3UserId($nc3_users, $cabinet_file->created_user) . "\t";    // [15]
                $tsv .= $this->getCCDatetime($cabinet_file->modified)                             . "\t";    // [16]
                $tsv .= $cabinet_file->update_user_name                                              . "\t";    // [17]
                $tsv .= $this->getNc3LoginIdFromNc3UserId($nc3_users, $cabinet_file->modified_user);           // [18]

                // 最終行は改行コード不要
                if ($index !== ($cabinet_files->count() - 1)) {
                    $tsv .= "\n";
                }
            }
            // キャビネットの設定を出力
            $this->storagePut($this->getImportPath('cabinets/cabinet_') . $this->zeroSuppress($cabinet_manage->cabinet_id) . '.ini', $ini);
            $tsv = $this->exportStrReplace($tsv, 'cabinets');
            $this->storagePut($this->getImportPath('cabinets/cabinet_') . $this->zeroSuppress($cabinet_manage->cabinet_id) . '.tsv', $tsv);
        }
    }

    /**
     * NC3：カウンター（カウンター）の移行
     */
    private function nc3ExportCounter($redo)
    {
        $this->putMonitor(3, "Start nc3ExportCounter.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('counters/'));
        }

        // NC3カウンター（Counter）を移行する。
        $where_counter_block_ids = $this->getMigrationConfig('counters', 'nc3_export_where_counter_block_ids');
        if (empty($where_counter_block_ids)) {
            $nc3_counters = Nc2Counter::orderBy('block_id')->get();
        } else {
            $nc3_counters = Nc2Counter::whereIn('block_id', $where_counter_block_ids)->orderBy('block_id')->get();
        }

        // 空なら戻る
        if ($nc3_counters->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc2User::get();

        // NC3カウンター（Counter）のループ
        foreach ($nc3_counters as $nc3_counter) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($room_ids) && !in_array($nc3_counter->room_id, $room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // (NC3)show_type -> (Connect)design_type 変換
            $convert_design_types = [
                'black'       => CounterDesignType::badge_dark,
                'black2'      => CounterDesignType::badge_dark,
                'black3'      => CounterDesignType::badge_dark,
                'color'       => CounterDesignType::badge_light,
                'digit01'     => CounterDesignType::white_number_warning,
                'digit02'     => CounterDesignType::white_number_warning,
                'digit03'     => CounterDesignType::white_number_danger,
                'digit04'     => CounterDesignType::white_number_danger,
                'digit05'     => CounterDesignType::white_number_primary,
                'digit06'     => CounterDesignType::white_number_info,
                'digit07'     => CounterDesignType::white_number_dark,
                'digit08'     => CounterDesignType::white_number_dark,
                'digit09'     => CounterDesignType::white_number_dark,
                'digit10'     => CounterDesignType::white_number_dark,
                'digit11'     => CounterDesignType::white_number_success,
                'digit12'     => CounterDesignType::white_number_success,
                'gray'        => CounterDesignType::badge_light,
                'gray2'       => CounterDesignType::badge_light,
                'gray3'       => CounterDesignType::badge_light,
                'gray_large'  => CounterDesignType::badge_light,
                'green'       => CounterDesignType::badge_success,
                'green_large' => CounterDesignType::badge_success,
                'white'       => CounterDesignType::white_number,
                'white_large' => CounterDesignType::circle_success,
            ];
            $design_type = $convert_design_types[$nc3_counter->show_type] ?? CounterDesignType::numeric;

            // カウンター設定
            $ini = "";
            $ini .= "[counter_base]\n";
            // カウント数
            $ini .= "counter_num = " . $nc3_counter->counter_num . "\n";
            // 表示する桁数
            // $ini .= "counter_digit = " .  $nc3_counter->counter_digit . "\n";

            $ini .= "design_type = " . $design_type . "\n";

            // 文字(前)
            $ini .= "show_char_before = " . $nc3_counter->show_char_before . "\n";
            // 文字(後)
            $ini .= "show_char_after = " . $nc3_counter->show_char_after . "\n";
            // 上記以外に表示したい文字
            // $ini .= "comment = " . $nc3_counter->comment . "\n";

            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "counter_block_id = " . $nc3_counter->block_id . "\n";
            $ini .= "room_id = " . $nc3_counter->room_id . "\n";
            $ini .= "module_name = \"counter\"\n";
            $ini .= "created_at      = \"" . $this->getCCDatetime($nc3_counter->created) . "\"\n";
            $ini .= "created_name    = \"" . $nc3_counter->insert_user_name . "\"\n";
            $ini .= "insert_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_counter->created_user) . "\"\n";
            $ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_counter->modified) . "\"\n";
            $ini .= "updated_name    = \"" . $nc3_counter->update_user_name . "\"\n";
            $ini .= "update_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_counter->modified_user) . "\"\n";

            // カウンターの設定を出力
            $this->storagePut($this->getImportPath('counters/counter_') . $this->zeroSuppress($nc3_counter->block_id) . '.ini', $ini);
        }
    }

    /**
     * NC3：カレンダー（カレンダー）の移行
     */
    private function nc3ExportCalendar($redo)
    {
        $this->putMonitor(3, "Start nc3ExportCalendar.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('calendars/'));
        }

        // ・NC3ルーム一覧とって、NC3予定データを移行する
        //   ※ ルームなしはありえない（必ずパブリックルームがあるため）
        // ・NC3カレンダーブロック（モジュール配置したブロック（どう見せるか、だけ。ここ無くても予定データある））を移行する。

        // NC3ルーム一覧を移行する。
        $nc3_export_private_room_calendar = $this->getMigrationConfig('calendars', 'nc3_export_private_room_calendar');
        if (empty($nc3_export_private_room_calendar)) {
            // プライベートルームをエクスポート（=移行）しない
            $nc3_page_rooms = Nc2Page::whereColumn('page_id', 'room_id')
                ->whereIn('space_type', [1, 2])     // 1:パブリックスペース, 2:グループスペース
                ->where('room_id', '!=', 2)         // 2:グループスペースを除外（枠だけでグループルームじゃないので除外）
                ->where('private_flag', 0)          // 0:プライベートルーム以外
                ->orderBy('room_id')
                ->get();
        } else {
            // プライベートルームをエクスポート（=移行）する
            $nc3_page_rooms = Nc2Page::whereColumn('page_id', 'room_id')
                ->whereIn('space_type', [1, 2])     // 1:パブリックスペース, 2:グループスペース
                ->where('room_id', '!=', 2)         // 2:グループスペースを除外（枠だけでグループルームじゃないので除外）
                ->orderBy('room_id')
                ->get();
        }

        // NC3権限設定（サイト全体で１設定のみ）. インストール時は空。権限設定でOK押さないとデータできない。
        $nc3_calendar_manages = Nc2CalendarManage::orderBy('room_id')->get();

        $nc3_export_room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');

        // nc3の全ユーザ取得
        $nc3_users = Nc2User::get();

        // ルームでループ（NC3カレンダーはルーム単位でエクスポート）
        foreach ($nc3_page_rooms as $nc3_page_room) {

            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($nc3_export_room_ids) && !in_array($nc3_page_room->room_id, $nc3_export_room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // カレンダー設定
            $ini = "";
            $ini .= "[calendar_base]\n";

            // NC3 権限設定
            $nc3_calendar_manage = $nc3_calendar_manages->firstWhere('room_id', $nc3_page_room->room_id);
            $ini .= "\n";
            $ini .= "[calendar_manage]\n";
            if (is_null($nc3_calendar_manage)) {
                // データなしは 4:主担。 ここに全会員ルームのデータは入ってこないため、これでOK
                $ini .= "add_authority_id = 4\n";
                // フラグは必ず1
                // $ini .= "use_flag = 1\n";
            } else {
                // 予定を追加できる権限. 2:主担,モデレータ,一般  3:主担,モデレータ  4:主担  5:なし（全会員のみ設定可能）
                $ini .= "add_authority_id = " . $nc3_calendar_manage->add_authority_id . "\n";
                // フラグ. 1:使う
                // $ini .= "use_flag = " . $nc3_calendar_manage->use_flag . "\n";
            }

            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "room_id = " . $nc3_page_room->room_id . "\n";
            // ルーム名
            $ini .= "room_name = '" . $nc3_page_room->page_name . "'\n";
            // プライベートフラグ, 1:プライベートルーム, 0:プライベートルーム以外
            $ini .= "private_flag = " . $nc3_page_room->private_flag . "\n";
            // スペースタイプ, 1:パブリックスペース, 2:グループスペース
            $ini .= "space_type = " . $nc3_page_room->space_type . "\n";
            $ini .= "module_name = \"calendar\"\n";


            // カラムのヘッダー及びTSV 行毎の枠準備
            $tsv_header = "calendar_id" . "\t" . "plan_id" . "\t" . "user_id" . "\t" . "user_name" . "\t" . "title" . "\t" .
                "allday_flag" . "\t" . "start_date" . "\t" . "start_time" . "\t" . "end_date" . "\t" . "end_time" . "\t" .
                // NC3 calendar_plan_details
                "location" . "\t" . "contact" . "\t" . "body" . "\t" . "rrule" . "\t" .
                // NC3 calendar_plan 登録日・更新日等
                "created_at" . "\t" . "created_name" . "\t" . "insert_login_id" . "\t" . "updated_at" . "\t" . "updated_name" . "\t" . "update_login_id" . "\t" .
                // CC 状態
                "status";

            // NC3 calendar_plan
            $tsv_cols['calendar_id'] = "";
            $tsv_cols['plan_id'] = "";
            $tsv_cols['user_id'] = "";
            $tsv_cols['user_name'] = "";
            $tsv_cols['title'] = "";
            $tsv_cols['allday_flag'] = "";
            $tsv_cols['start_date'] = "";
            $tsv_cols['start_time'] = "";
            $tsv_cols['end_date'] = "";
            $tsv_cols['end_time'] = "";

            // NC3 calendar_plan_details
            // 場所
            $tsv_cols['location'] = "";
            // 連絡先
            $tsv_cols['contact'] = "";
            // 内容
            $tsv_cols['body'] = "";
            // 繰り返し条件
            $tsv_cols['rrule'] = "";

            // NC3 calendar_plan 登録日・更新日等
            $tsv_cols['created_at'] = "";
            $tsv_cols['created_name'] = "";
            $tsv_cols['insert_login_id'] = "";
            $tsv_cols['updated_at'] = "";
            $tsv_cols['updated_name'] = "";
            $tsv_cols['update_login_id'] = "";

            // CC 状態
            $tsv_cols['status'] = "";

            // カレンダーの予定 calendar_plan
            $calendar_plans = Nc2CalendarPlan::
                leftjoin('calendar_plan_details', function ($join) {
                    $join->on('calendar_plan.plan_id', '=', 'calendar_plan_details.plan_id')
                        ->whereColumn('calendar_plan.room_id', 'calendar_plan_details.room_id');
                })
                ->where('calendar_plan.room_id', $nc3_page_room->room_id)
                ->orderBy('calendar_plan.calendar_id', 'asc')
                ->get();

            // カラムデータのループ
            Storage::delete($this->getImportPath('calendars/calendar_room_') . $this->zeroSuppress($nc3_page_room->room_id) . '.tsv');

            $tsv = '';
            $tsv .= $tsv_header . "\n";

            foreach ($calendar_plans as $calendar_plan) {

                // 初期化
                $tsv_record = $tsv_cols;

                // NC3 calendar_plan
                $tsv_record['calendar_id'] = $calendar_plan->calendar_id;
                $tsv_record['plan_id'] = $calendar_plan->plan_id;
                $tsv_record['user_id'] = $calendar_plan->user_id;
                $tsv_record['user_name'] = $calendar_plan->user_name;
                $tsv_record['title'] = $calendar_plan->title;
                $tsv_record['allday_flag'] = $calendar_plan->allday_flag;

                // 予定開始日時
                // Carbon()で処理。必須値のため基本値がある想定で、timezone_offset で時間加算して予定時間を算出
                $start_time_full = (new Carbon($calendar_plan->start_time_full))->addHour($calendar_plan->timezone_offset);
                $tsv_record['start_date'] = $start_time_full->format('Y-m-d');
                $tsv_record['start_time'] = $start_time_full->format('H:i:s');

                // 予定終了日時
                // Carbon()で処理。必須値のため基本値がある想定で、timezone_offset で時間加算して予定時間を算出
                $end_time_full = (new Carbon($calendar_plan->end_time_full))->addHour($calendar_plan->timezone_offset);
                if ($calendar_plan->allday_flag == 1) {
                    // 全日で終了日時の変換対応. -1日する。
                    //
                    // ・NC3 で登録できる開始時間：0:00～23:55 （24:00ないため、こっちは対応不要）
                    // ・NC3 で登録できる終了時間：0:05～24:00 （0:00に設定しても前日24:00に自動変換される）
                    // ・Connect 終了時間 0:00～23:59
                    // 24:00はデータ上0:00のため、0:00から-1日して23:59に変換する。
                    //
                    // ※ NC3の全日１日は、        20210810 150000（+9時間）～20210811 150000（+9時間）←当日～翌日
                    //    Connect-CMSの全日１日は、2021-08-11 00:00:00～2021-08-11 00:00:00 ←前後同じ, 時間は設定できず 00:00:00 で登録される。
                    //    そのため、2021/08/11 0:00～2021/08/12 0:00 を 2021/08/11 0:00～2021/08/11 0:00に変換する。

                    // -1日
                    $end_time_full = $end_time_full->subDay();
                } elseif ($end_time_full->format('H:i:s') == '00:00:00') {
                    // 全日以外で終了日時が0:00の変換対応. -1分する。
                    // ※ 例えばNC3の「時間指定」で10:00～24:00という予定に対応して、10:00～23:59に終了時間を変換する

                    // -1分
                    $end_time_full = $end_time_full->subMinute();
                }
                $tsv_record['end_date'] = $end_time_full->format('Y-m-d');
                $tsv_record['end_time'] = $end_time_full->format('H:i:s');

                // NC3 calendar_plan_details（plan_id, room_idあり）
                // 場所
                $tsv_record['location'] = $calendar_plan->location;
                // 連絡先
                $tsv_record['contact'] = $calendar_plan->contact;
                // 内容 [WYSIWYG]
                $tsv_record['body'] = $this->nc3Wysiwyg(null, null, null, null, $calendar_plan->description, 'calendar');

                // 繰り返し条件
                $tsv_record['rrule'] = $calendar_plan->rrule;

                // NC3 calendar_plan 登録日・更新日等
                $tsv_record['created_at']      = $this->getCCDatetime($calendar_plan->created);
                $tsv_record['created_name']    = $calendar_plan->insert_user_name;
                $tsv_record['insert_login_id'] = $this->getNc3LoginIdFromNc3UserId($nc3_users, $calendar_plan->created_user);
                $tsv_record['updated_at']      = $this->getCCDatetime($calendar_plan->modified);
                $tsv_record['updated_name']    = $calendar_plan->update_user_name;
                $tsv_record['update_login_id'] = $this->getNc3LoginIdFromNc3UserId($nc3_users, $calendar_plan->modified_user);

                // NC3カレンダー予定は公開のみ
                $tsv_record['status'] = 0;

                $tsv .= implode("\t", $tsv_record) . "\n";
            }

            // データ行の書き出し
            $tsv = $this->exportStrReplace($tsv, 'calendars');
            $this->storageAppend($this->getImportPath('calendars/calendar_room_') . $this->zeroSuppress($nc3_page_room->room_id) . '.tsv', $tsv);

            // カレンダーの設定を出力
            $this->storagePut($this->getImportPath('calendars/calendar_room_') . $this->zeroSuppress($nc3_page_room->room_id) . '.ini', $ini);
        }


        // NC3全会員 room_id=0（nc3_page にデータないため手動で設定）
        $all_users_room_id = 0;

        // ルーム指定があれば、指定されたルームのみ処理する。
        if (empty($nc3_export_room_ids) || in_array($all_users_room_id, $nc3_export_room_ids)) {

            // カレンダー設定
            $ini = "";
            $ini .= "[calendar_base]\n";

            // NC3 権限設定
            $nc3_calendar_manage = $nc3_calendar_manages->firstWhere('room_id', $all_users_room_id);
            $ini .= "\n";
            $ini .= "[calendar_manage]\n";
            if (is_null($nc3_calendar_manage)) {
                // 全会員のデータなしは 5:なし（全会員のみ設定可能）
                $ini .= "add_authority_id = 5\n";
                // フラグは必ず1
                // $ini .= "use_flag = 1\n";
            } else {
                // 予定を追加できる権限. 2:主担,モデレータ,一般  3:主担,モデレータ  4:主担  5:なし（全会員のみ設定可能）
                $ini .= "add_authority_id = " . $nc3_calendar_manage->add_authority_id . "\n";
                // フラグ. 1:使う
                // $ini .= "use_flag = " . $nc3_calendar_manage->use_flag . "\n";
            }

            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "room_id = " . $all_users_room_id . "\n";
            // ルーム名
            $ini .= "room_name = 全会員\n";
            // プライベートフラグ, 1:プライベートルーム
            $ini .= "private_flag = 0\n";
            // スペースタイプ, 1:パブリックスペース, 2:グループスペース
            $ini .= "space_type =\n";
            $ini .= "module_name = \"calendar\"\n";

            // カレンダーの設定を出力
            $this->storagePut($this->getImportPath('calendars/calendar_room_') . $this->zeroSuppress($all_users_room_id) . '.ini', $ini);
        }


        // NC3カレンダーブロック（モジュール配置したブロック（どう見せるか、だけ。ここ無くても予定データある））を移行する。
        $where_calendar_block_ids = $this->getMigrationConfig('calendars', 'nc3_export_where_calendar_block_ids');
        if (empty($where_calendar_block_ids)) {
            $nc3_calendar_blocks = Nc2CalendarBlock::orderBy('block_id')->get();
        } else {
            $nc3_calendar_blocks = Nc2CalendarBlock::whereIn('block_id', $where_calendar_block_ids)->orderBy('block_id')->get();
        }

        // 空なら戻る
        if ($nc3_calendar_blocks->isEmpty()) {
            return;
        }

        // NC3 指定ルームのみ表示 nc3_calendar_select_room
        // if (empty($where_calendar_block_ids)) {
        //     $nc3_calendar_select_rooms = Nc2CalendarSelectRoom::orderBy('block_id')->get();
        // } else {
        //     $nc3_calendar_select_rooms = Nc2CalendarSelectRoom::whereIn('block_id', $where_calendar_block_ids)->orderBy('block_id')->get();
        // }

        // NC3カレンダーブロックのループ
        foreach ($nc3_calendar_blocks as $nc3_calendar_block) {

            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($nc3_export_room_ids) && !in_array($nc3_page_room->room_id, $nc3_export_room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // NC3 カレンダーブロック（表示方法）設定
            $ini = "";
            $ini .= "[calendar_block]\n";
            // 表示方法
            $ini .= "display_type = " . $nc3_calendar_block->display_type . "\n";
            // 開始位置
            // $ini .= "start_pos = " .  $nc3_calendar_block->start_pos . "\n";
            // 表示日数
            // $ini .= "display_count = " . $nc3_calendar_block->display_count . "\n";
            // 指定したルームのみ表示する 1:ルーム指定する 0:指定しない
            // $ini .= "select_room = " . $nc3_calendar_block->select_room . "\n";
            // [不明] 画面に該当項目なし。プライベートルームにカレンダー配置しても 0 だった。
            // $ini .= "myroom_flag = " . $nc3_calendar_block->myroom_flag . "\n";

            // NC3 指定ルームのみ表示
            // $ini .= "\n";
            // $ini .= "[calendar_select_room]\n";
            // foreach ($nc3_calendar_select_rooms as $nc3_calendar_select_room) {
            //     $ini .= "room_id[] = " . $nc3_calendar_select_room->room_id . "\n";
            // }

            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "calendar_block_id = " . $nc3_calendar_block->block_id . "\n";
            $ini .= "room_id = " . $nc3_calendar_block->room_id . "\n";
            $ini .= "module_name = \"calendar\"\n";

            // カレンダーの設定を出力
            $this->storagePut($this->getImportPath('calendars/calendar_block_') . $this->zeroSuppress($nc3_calendar_block->block_id) . '.ini', $ini);
        }
    }

    /**
     * NC3：スライダー（スライダー）の移行
     */
    private function nc3ExportSlides($redo)
    {
        $this->putMonitor(3, "Start nc3ExportSlides.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('slideshows/'));
        }

        // NC3スライダー（Slideshow）を移行する。
        $where_slideshow_block_ids = $this->getMigrationConfig('slideshows', 'nc3_export_where_slideshow_block_ids');
        if (empty($where_slideshow_block_ids)) {
            $nc3_slideshows = Nc2Slides::orderBy('block_id')->get();
        } else {
            $nc3_slideshows = Nc2Slides::whereIn('block_id', $where_slideshow_block_ids)->orderBy('block_id')->get();
        }

        // 空なら戻る
        if ($nc3_slideshows->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc2User::get();

        // NC3スライダー（Slideshow）のループ
        foreach ($nc3_slideshows as $nc3_slideshow) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($room_ids) && !in_array($nc3_slideshow->room_id, $room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // (nc3初期値) 5500
            $image_interval = $nc3_slideshow->pause ? $nc3_slideshow->pause : 5500;

            // スライダー設定
            $ini = "";
            $ini .= "[slideshow_base]\n";
            $ini .= "image_interval = " . $image_interval . "\n";

            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "slideshows_block_id = " . $nc3_slideshow->block_id . "\n";
            $ini .= "room_id = " . $nc3_slideshow->room_id . "\n";
            $ini .= "module_name = \"slides\"\n";
            $ini .= "created_at      = \"" . $this->getCCDatetime($nc3_slideshow->created) . "\"\n";
            $ini .= "created_name    = \"" . $nc3_slideshow->insert_user_name . "\"\n";
            $ini .= "insert_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_slideshow->created_user) . "\"\n";
            $ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_slideshow->modified) . "\"\n";
            $ini .= "updated_name    = \"" . $nc3_slideshow->update_user_name . "\"\n";
            $ini .= "update_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_slideshow->modified_user) . "\"\n";

            // 付与情報を移行する。
            $nc3_slides_urls = Nc2SlidesUrl::where('slides_id', $nc3_slideshow->slides_id)->orderBy('slides_url_id')->get();
            // TSV でエクスポート
            // image_path{\t}uploads_id{\t}link_url{\t}link_target{\t}caption{\t}display_flag{\t}display_sequence
            $slides_tsv = "";
            foreach ($nc3_slides_urls as $nc3_slides_url) {
                // TSV 形式でエクスポート
                if (!empty($slides_tsv)) {
                    $slides_tsv .= "\n";
                }
                $slides_tsv .= "\t";                                                            // image_path
                $slides_tsv .= $nc3_slides_url->image_file_id . "\t";                           // uploads_id
                $slides_tsv .= $nc3_slides_url->url . "\t";                                     // link_url
                $slides_tsv .= ($nc3_slides_url->target_new == 0) ? "\t" : '_blank' . "\t";     // link_target
                $slides_tsv .= $nc3_slides_url->linkstr . "\t";                                 // caption
                $slides_tsv .= $nc3_slides_url->view . "\t";                                    // display_flag
                $slides_tsv .= $nc3_slides_url->display_sequence . "\t";                        // display_sequence
            }
            // スライダーの設定を出力
            $this->storagePut($this->getImportPath('slideshows/slideshows_') . $this->zeroSuppress($nc3_slideshow->block_id) . '.ini', $ini);
            // スライダーの付与情報を出力
            $this->storagePut($this->getImportPath('slideshows/slideshows_') . $this->zeroSuppress($nc3_slideshow->block_id) . '.tsv', $slides_tsv);

        }
    }

    /**
     * NC3：シンプル動画の移行
     */
    private function nc3ExportSimplemovie($redo)
    {
        $this->putMonitor(3, "Start nc3ExportSimplemovie.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('simplemovie/'));
        }

        // NC3シンプル動画を移行する。
        $where_simplemovie_block_ids = $this->getMigrationConfig('simplemovie', 'nc3_export_where_simplemovie_block_ids');
        if (empty($where_simplemovie_block_ids)) {
            $nc3_simplemovies = Nc2Simplemovie::orderBy('block_id')->get();
        } else {
            $nc3_simplemovies = Nc2Simplemovie::whereIn('block_id', $where_simplemovie_block_ids)->orderBy('block_id')->get();
        }

        // 空なら戻る
        if ($nc3_simplemovies->isEmpty()) {
            return;
        }

        // NC3スライダー（Slideshow）のループ
        foreach ($nc3_simplemovies as $nc3_simplemovie) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($room_ids) && !in_array($nc3_simplemovie->room_id, $room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // 動画が設定されていない場合はエクスポートしない
            if ($nc3_simplemovie->movie_upload_id == null) {
                continue;
            }

            // シンプル動画設定
            $ini = "";
            $ini .= "[simplemovie_base]\n";
            $ini .= "simplemovie_movie_upload_id = " . $nc3_simplemovie->movie_upload_id . "\n";
            $ini .= "simplemovie_movie_upload_id_request = " . $nc3_simplemovie->movie_upload_id_request . "\n";
            $ini .= "simplemovie_thumbnail_upload_id = " . $nc3_simplemovie->thumbnail_upload_id . "\n";
            $ini .= "simplemovie_thumbnail_upload_id_request = " . $nc3_simplemovie->thumbnail_upload_id_request . "\n";
            $ini .= "simplemovie_width = " . $nc3_simplemovie->width . "\n";
            $ini .= "simplemovie_height = " . $nc3_simplemovie->height . "\n";
            $ini .= "simplemovie_autoplay_flag = " . $nc3_simplemovie->autoplay_flag . "\n";
            $ini .= "simplemovie_embed_show_flag = " . $nc3_simplemovie->embed_show_flag . "\n";
            $ini .= "simplemovie_agree_flag = " . $nc3_simplemovie->agree_flag . "\n";
            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "simplemovie_block_id = " . $nc3_simplemovie->block_id . "\n";
            $ini .= "room_id = " . $nc3_simplemovie->room_id . "\n";
            $ini .= "module_name = \"simplemovie\"\n";
            // シンプル動画の設定を出力
            $this->storagePut($this->getImportPath('simplemovie/simplemovie_') . $this->zeroSuppress($nc3_simplemovie->block_id) . '.ini', $ini);
        }
    }

    /**
     * NC3：施設予約の移行
     */
    private function nc3ExportReservation($redo)
    {
        $this->putMonitor(3, "Start nc3ExportReservation.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('reservations/'));
        }

        // ・NC3ルーム一覧とって、NC3予定データを移行する
        //   ※ ルームなしはありえない（必ずパブリックルームがあるため）
        // ・NC3施設予約ブロック（モジュール配置したブロック（どう見せるか、だけ。ここ無くても予定データある））を移行する。

        // 施設カテゴリ
        // ----------------------------------------------------
        $nc3_reservation_categories = Nc2ReservationCategory::orderBy('display_sequence')->get();
        foreach ($nc3_reservation_categories as $nc3_reservation_category) {
            // NC3 施設カテゴリ設定
            $ini = "";
            $ini .= "[reservation_category]\n";
            // カテゴリ名
            $ini .= "category_name = \"" . $nc3_reservation_category->category_name . "\"\n";

            // 表示順
            $ini .= "display_sequence = " . $nc3_reservation_category->display_sequence . "\n";

            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "category_id = " . $nc3_reservation_category->category_id . "\n";
            $ini .= "module_name = \"reservation\"\n";

            // 施設予約の設定を出力
            $this->storagePut($this->getImportPath('reservations/reservation_category_') . $this->zeroSuppress($nc3_reservation_category->category_id) . '.ini', $ini);
        }

        // NC3施設のエクスポート
        // ----------------------------------------------------
        $where_reservation_location_ids = $this->getMigrationConfig('reservations', 'nc3_export_where_reservation_location_ids');
        if (empty($where_reservation_location_ids)) {
            $nc3_reservation_locations = Nc2ReservationLocation::orderBy('category_id')->orderBy('display_sequence')->get();
            $nc3_reservation_location_details = Nc2ReservationLocationDetail::orderBy('location_id')->get();
        } else {
            $nc3_reservation_locations = Nc2ReservationLocation::whereIn('location_id', $where_reservation_location_ids)->orderBy('category_id')->orderBy('display_sequence')->get();
            $nc3_reservation_location_details = Nc2ReservationLocationDetail::whereIn('location_id', $where_reservation_location_ids)->orderBy('location_id')->get();
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc2User::get();

        foreach ($nc3_reservation_locations as $nc3_reservation_location) {
            // NC3 施設カテゴリ設定
            $ini = "";
            $ini .= "[reservation_location]\n";
            // カテゴリID
            $ini .= "category_id = " . $nc3_reservation_location->category_id . "\n";
            // 施設名
            $ini .= "location_name = \"" . $nc3_reservation_location->location_name . "\"\n";
            // （画面に対象となる項目なし）active_flag
            // $ini .= "active_flag = " . $nc3_reservation_location->active_flag . "\n";

            // 予約できる権限 4:主担のみ, 3:モデレータ以上, 2:一般以上
            // $ini .= "add_authority = " . $nc3_reservation_location->add_authority . "\n";
            if ($nc3_reservation_location->add_authority == 4 || $nc3_reservation_location->add_authority == 3) {
                $ini .= "is_limited_by_role = " . ReservationLimitedByRole::limited . "\n";
            } else {
                $ini .= "is_limited_by_role = " . ReservationLimitedByRole::not_limited . "\n";
            }

            // 利用曜日 例）SU,MO,TU,WE,TH,FR,SA
            // $ini .= "time_table = " . $nc3_reservation_location->time_table . "\n";
            $time_tables = explode(',', $nc3_reservation_location->time_table);
            // 変換
            $convert_day_of_week = [
                'SU' => DayOfWeek::sun,
                'MO' => DayOfWeek::mon,
                'TU' => DayOfWeek::tue,
                'WE' => DayOfWeek::wed,
                'TH' => DayOfWeek::thu,
                'FR' => DayOfWeek::fri,
                'SA' => DayOfWeek::sat,
            ];
            $day_of_weeks = [];
            foreach ($time_tables as $time_table) {
                $day_of_weeks[] = $convert_day_of_week[$time_table];
            }
            $ini .= "day_of_weeks = \"" . implode('|', $day_of_weeks) . "\"\n";

            $start_time = new Carbon($nc3_reservation_location->start_time);
            $start_time->addHour($nc3_reservation_location->timezone_offset); // 例）9.0 = 9時間後
            $end_time = new Carbon($nc3_reservation_location->end_time);
            $end_time->addHour($nc3_reservation_location->timezone_offset);
            $end_time_str = $end_time->format('H:i:s');

            // 開始～終了 の差が 24h なら「利用時間の制限なし」
            if ($start_time->diffInHours($end_time) == 24) {
                // 24:00 は0:00表示になってしまうため、文字列をセット
                $end_time_str = '24:00:00';
                // 制限なし
                $ini .= "is_time_control = 0\n";
            } else {
                // 制限あり
                $ini .= "is_time_control = 1\n";
            }

            // 利用時間-開始 例）20220203150000 = yyyyMMddhhiiss = 15(+9) = 24:00
            $ini .= "start_time = " . $start_time->format('H:i:s') . "\n";
            // 利用時間-終了 例）20220204150000 = yyyyMMddhhiiss = 15(+9) = 翌日24:00
            $ini .= "end_time = " . $end_time_str . "\n";
            // （画面に対象となる項目なし）duplication_flag、例) 0、※ DBから直接 1 にすると予約重複可能になるが、知られてない
            // $ini .= "duplication_flag = " . $nc3_reservation_location->duplication_flag . "\n";
            // 個人的な予約を受け付ける
            // $ini .= "use_private_flag = " . $nc3_reservation_location->use_private_flag . "\n";
            // 個人的な予約で使用する権限。0:会員の権限、1:ルームでの権限
            // $ini .= "use_auth_flag = " . $nc3_reservation_location->use_auth_flag . "\n";
            // 全てのルームから予約を受け付ける。1:ON、0:OFF
            // $ini .= "allroom_flag = " . $nc3_reservation_location->allroom_flag . "\n";
            // 並び順
            $ini .= "display_sequence = " . $nc3_reservation_location->display_sequence . "\n";

            $nc3_reservation_location_detail = $nc3_reservation_location_details->firstWhere('location_id', $nc3_reservation_location->location_id);
            $nc3_reservation_location_detail = $nc3_reservation_location_detail ?? new Nc2ReservationLocationDetail();

            // 施設管理者
            $ini .= "facility_manager_name = \"" . $nc3_reservation_location_detail->contact . "\"\n";
            // 補足
            $ini .= "supplement = \"" . str_replace('"', '\"', $nc3_reservation_location_detail->description) . "\"\n";

            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "location_id = " . $nc3_reservation_location->location_id . "\n";
            $ini .= "module_name = \"reservation\"\n";

            // 施設予約の予約
            // ----------------------------------------------------
            // カラムのヘッダー及びTSV 行毎の枠準備
            $tsv_header = "reserve_id" . "\t" . "reserve_details_id" . "\t" . "title" . "\t" .
                "allday_flag" . "\t" . "start_time_full" . "\t" . "end_time_full" . "\t" .
                // NC3 reservation_reserve_details
                "contact" . "\t" . "description" . "\t" . "rrule" . "\t" .
                // NC3 reservation_reserve 登録日・更新日等
                "created_at" . "\t" . "created_name" . "\t" . "insert_login_id" . "\t" . "updated_at" . "\t" . "updated_name" . "\t" . "update_login_id" . "\t" .
                // CC 状態
                "status";

            // NC3 reservation_reserve
            $tsv_cols['reserve_id'] = "";
            $tsv_cols['reserve_details_id'] = "";
            $tsv_cols['title'] = "";
            $tsv_cols['allday_flag'] = "";
            $tsv_cols['start_time_full'] = "";
            $tsv_cols['end_time_full'] = "";

            // NC3 reservation_reserve_details
            // 連絡先
            $tsv_cols['contact'] = "";
            // 内容
            $tsv_cols['description'] = "";
            // 繰り返し条件
            $tsv_cols['rrule'] = "";

            // NC3 reservation_reserve 登録日・更新日等
            $tsv_cols['created_at'] = "";
            $tsv_cols['created_name'] = "";
            $tsv_cols['insert_login_id'] = "";
            $tsv_cols['updated_at'] = "";
            $tsv_cols['updated_name'] = "";
            $tsv_cols['update_login_id'] = "";

            // CC 状態
            $tsv_cols['status'] = "";

            // 施設予約の予約 reservation_reserve
            $reservation_reserves = Nc2ReservationReserve::
                leftjoin('reservation_reserve_details', function ($join) {
                    $join->on('reservation_reserve.reserve_details_id', '=', 'reservation_reserve_details.reserve_details_id')
                        ->whereColumn('reservation_reserve.location_id', 'reservation_reserve_details.location_id')
                        ->whereColumn('reservation_reserve.room_id', 'reservation_reserve_details.room_id');
                })
                ->where('reservation_reserve.location_id', $nc3_reservation_location->location_id)
                ->orderBy('reservation_reserve.reserve_details_id', 'asc')
                ->get();

            // カラムデータのループ
            Storage::delete($this->getImportPath('reservations/reservation_location_reserve_') . $this->zeroSuppress($nc3_reservation_location->location_id) . '.tsv');

            $tsv = '';
            $tsv .= $tsv_header . "\n";

            foreach ($reservation_reserves as $reservation_reserve) {

                // 初期化
                $tsv_record = $tsv_cols;

                // NC3 reservation_reserve
                $tsv_record['reserve_id'] = $reservation_reserve->reserve_id;
                $tsv_record['reserve_details_id'] = $reservation_reserve->reserve_details_id;
                $tsv_record['title'] = $reservation_reserve->title;
                $tsv_record['allday_flag'] = $reservation_reserve->allday_flag;

                // 予定開始日時
                // Carbon()で処理。必須値のため基本値がある想定で、timezone_offset で時間加算して予定時間を算出
                $start_time_full = (new Carbon($reservation_reserve->start_time_full))->addHour($reservation_reserve->timezone_offset);
                $tsv_record['start_time_full'] = $start_time_full;

                // 予定終了日時
                // Carbon()で処理。必須値のため基本値がある想定で、timezone_offset で時間加算して予定時間を算出
                $end_time_full = (new Carbon($reservation_reserve->end_time_full))->addHour($reservation_reserve->timezone_offset);
                // if ($reservation_reserve->allday_flag == 1) {
                //     // 全日で終了日時の変換対応. -1日する。
                //     //
                //     // ・NC3 で登録できる開始時間：0:00～23:55 （24:00ないため、こっちは対応不要）
                //     // ・NC3 で登録できる終了時間：0:05～24:00 （0:00に設定しても前日24:00に自動変換される）
                //     // ・Connect 終了時間 0:00～23:59
                //     // 24:00はデータ上0:00のため、0:00から-5分して23:55に変換する。
                //     //
                //     // ※ NC3の全日１日は、        20210810 150000（+9時間）～20210811 150000（+9時間）←当日～翌日
                //     //    Connect-CMSの全日１日は、2021-08-11 00:00:00～2021-08-11 00:00:00 ←前後同じ, 時間は設定できず 00:00:00 で登録される。
                //     //    そのため、2021/08/11 0:00～2021/08/12 0:00 を 2021/08/11 0:00～2021/08/11 0:00に変換する。

                //     // -1日
                //     $end_time_full = $end_time_full->subDay();
                // } elseif ($end_time_full->format('H:i:s') == '00:00:00') {
                // if ($end_time_full->format('H:i:s') == '00:00:00') {
                //     // 全日以外で終了日時が0:00の変換対応. -5分する。
                //     // ※ 例えばNC3の「時間指定」で10:00～24:00という予定に対応して、10:00～23:55に終了時間を変換する

                //     // -5分
                //     $end_time_full = $end_time_full->subMinute(5);
                // }
                $tsv_record['end_time_full'] = $end_time_full;

                // NC3 reservation_reserve_details
                // 連絡先
                $tsv_record['contact'] = $reservation_reserve->contact;
                // 内容 [WYSIWYG]
                $tsv_record['description'] = $this->nc3Wysiwyg(null, null, null, null, $reservation_reserve->description, 'reservation');
                // 繰り返し条件
                $tsv_record['rrule'] = $reservation_reserve->rrule;

                // NC3 reservation_reserve システム項目
                $tsv_record['created_at'] = $this->getCCDatetime($reservation_reserve->created);
                $tsv_record['created_name'] = $reservation_reserve->insert_user_name;
                $tsv_record['insert_login_id'] = $this->getNc3LoginIdFromNc3UserId($nc3_users, $reservation_reserve->created_user);
                $tsv_record['updated_at'] = $this->getCCDatetime($reservation_reserve->modified);
                $tsv_record['updated_name'] = $reservation_reserve->update_user_name;
                $tsv_record['update_login_id'] = $this->getNc3LoginIdFromNc3UserId($nc3_users, $reservation_reserve->modified_user);

                // NC3施設予約予定は公開のみ
                $tsv_record['status'] = 0;

                $tsv .= implode("\t", $tsv_record) . "\n";
            }

            // 施設予約の設定を出力
            $this->storagePut($this->getImportPath('reservations/reservation_location_') . $this->zeroSuppress($nc3_reservation_location->location_id) . '.ini', $ini);

            // データ行の書き出し
            $tsv = $this->exportStrReplace($tsv, 'reservations');
            $this->storageAppend($this->getImportPath('reservations/reservation_location_') . $this->zeroSuppress($nc3_reservation_location->location_id) . '.tsv', $tsv);
        }

        // メール設定
        // ----------------------------------------------------
        // modules テーブルの reservationモジューデータ 取得
        $nc3_module = Nc2Modules::where('action_name', 'like', 'reservation%')->first();
        $nc3_module = $nc3_module ?? new Nc2Modules();

        // config テーブルの 施設予約のメール設定 取得
        $nc3_configs = Nc2Config::where('conf_modid', $nc3_module->module_id)->get();

        // mail_send（メール通知する）. default=_ON
        $nc3_config_mail_send = $nc3_configs->firstWhere('conf_name', 'mail_send');
        $mail_send = null;
        if (is_null($nc3_config_mail_send)) {
            // 通知しない
            $mail_send = 0;
        } elseif ($nc3_config_mail_send->conf_value == '_ON') {
            // 通知する
            $mail_send = 1;
        } else {
            $mail_send = (int) $nc3_config_mail_send->conf_value;
        }

        // mail_authority（通知する権限）. default=_AUTH_GUEST ゲストまで全て（主担,モデ,一般,ゲストのチェックON）
        $nc3_config_mail_authority = $nc3_configs->firstWhere('conf_name', 'mail_authority');
        $mail_authority = null;
        if (is_null($nc3_config_mail_authority)) {
            // 主担のみ
            $mail_authority = 4;
        } elseif ($nc3_config_mail_authority->conf_value == '_AUTH_GUEST') {
            // ゲストまで全て（主担,モデ,一般,ゲストのチェックON）
            $mail_authority = 1;
        } else {
            $mail_authority = (int) $nc3_config_mail_authority->conf_value;
        }

        // mail_authority
        // 1: ゲストまで
        // 2: 一般まで
        // 3: モデレータまで
        // 4: 主担のみ
        $notice_everyone = 0;
        $notice_admin_group = 0;
        $notice_all_moderator_group = 0;
        if ($mail_authority === 1) {
            // 全ユーザ通知
            $notice_everyone = 1;

        } elseif ($mail_authority == 2) {
            // 全一般ユーザ通知（≒全ユーザ通知）
            $notice_everyone = 1;
            $this->putMonitor(3, '施設予約のメール設定（一般まで）は、全ユーザ通知で移行します。', 'ini_path=' . $this->getImportPath('reservations/reservation_mail') . '.ini');

        } elseif ($mail_authority == 3) {
            // 全モデレータユーザ通知
            $notice_all_moderator_group = 1;
            $notice_admin_group = 1;

        } elseif ($mail_authority == 4) {
            // 管理者グループ通知
            $notice_admin_group = 1;
        }

        // mail_subject（件名）. default=RESERVATION_MAIL_SUBJECT ←多言語により表示言語によって変わる
        $nc3_config_mail_subject = $nc3_configs->firstWhere('conf_name', 'mail_subject');
        $mail_subject = null;
        if (is_null($nc3_config_mail_subject)) {
            $mail_subject = null;
        } elseif ($nc3_config_mail_subject->conf_value == 'RESERVATION_MAIL_SUBJECT') {
            $mail_subject = '[{X-SITE_NAME}]予約の通知';
        } else {
            $mail_subject = $nc3_config_mail_subject->conf_value;
        }

        // mail_body（本文）. default=RESERVATION_MAIL_BODY ←多言語により表示言語によって変わる
        $nc3_configmail_body = $nc3_configs->firstWhere('conf_name', 'mail_body');
        $mail_body = null;
        if (is_null($nc3_configmail_body)) {
            $mail_body = null;
        } elseif ($nc3_configmail_body->conf_value == 'RESERVATION_MAIL_BODY') {
            $mail_body = "施設の予約が入りましたのでお知らせします。\n\n施設:{X-LOCATION_NAME}\n件名:{X-TITLE}\n利用グループ:{X-RESERVE_FLAG}\n利用日時:{X-RESERVE_TIME}\n連絡先:{X-CONTACT}\n繰返し:{X-RRULE}\n登録者:{X-USER}\n登録時刻:{X-INPUT_TIME}\n\n{X-BODY}\n\nこの予約を確認するには、下記アドレスへ\n{X-URL}";
        } else {
            $mail_body = $nc3_configmail_body->conf_value;
        }

        // 変換
        $convert_embedded_tags = [
            // nc3埋込タグ, cc埋込タグ
            ['{X-SITE_NAME}', '[[' . ReservationNoticeEmbeddedTag::site_name . ']]'],
            ['{X-LOCATION_NAME}', '[[' . ReservationNoticeEmbeddedTag::facility_name . ']]'],
            // change: [[title]]は、施設管理の項目「タイトルの設定」で変わるため、タイトルの埋め込みタグは[[X-件名]]に変換する。
            // ['{X-TITLE}', '[[' . ReservationNoticeEmbeddedTag::title . ']]'],
            ['{X-TITLE}', '[[X-件名]]'],
            ['{X-RESERVE_TIME}', '[[' . ReservationNoticeEmbeddedTag::booking_time . ']]'],
            ['{X-CONTACT}', '[[X-連絡先]]'],
            ['{X-RRULE}', '[[' . ReservationNoticeEmbeddedTag::rrule . ']]'],
            ['{X-USER}', '[[' . ReservationNoticeEmbeddedTag::created_name . ']]'],
            ['{X-INPUT_TIME}', '[[' . ReservationNoticeEmbeddedTag::created_at . ']]'],
            ['{X-BODY}', '[[X-補足]]'],
            ['{X-URL}', '[[' . ReservationNoticeEmbeddedTag::url . ']]'],
            // 除外
            ['利用グループ:{X-RESERVE_FLAG}', ''],
            ['{X-RESERVE_FLAG}', ''],
        ];

        foreach ($convert_embedded_tags as $convert_embedded_tag) {
            $mail_subject = str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_subject);
            $mail_body = str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_body);
        }

        // 施設予約のメール設定
        $ini = "";
        $ini .= "[reservation_mail]\n";
        // メール通知する
        $ini .= "mail_send = " . $mail_send . "\n";
        // 全ユーザ通知
        $ini .= "notice_everyone = " . $notice_everyone . "\n";
        // 全モデレータユーザ通知
        $ini .= "notice_all_moderator_group = " . $notice_all_moderator_group . "\n";
        // 管理者グループ通知
        $ini .= "notice_admin_group = " . $notice_admin_group . "\n";
        // 件名
        $ini .= "mail_subject = \"" . $mail_subject . "\"\n";
        // 本文
        $ini .= "mail_body = \"" . $mail_body . "\"\n";

        // 施設予約の設定を出力
        $this->storagePut($this->getImportPath('reservations/reservation_mail') . '.ini', $ini);

        // NC3施設予約ブロック（モジュール配置したブロック（どう見せるか、だけ。ここ無くても予約データある））を移行する。
        // ----------------------------------------------------
        $where_reservation_block_ids = $this->getMigrationConfig('reservations', 'nc3_export_where_reservation_block_ids');
        if (empty($where_reservation_block_ids)) {
            $nc3_reservation_blocks_query = Nc2ReservationBlock::query();
        } else {
            $nc3_reservation_blocks_query = Nc2ReservationBlock::whereIn('reservation_block.block_id', $where_reservation_block_ids);
        }

        $nc3_reservation_blocks = $nc3_reservation_blocks_query->select('reservation_block.*', 'blocks.block_name', 'pages.page_name', 'page_rooms.page_name as room_name')
            ->join('blocks', 'blocks.block_id', '=', 'reservation_block.block_id')
            ->join('pages', function ($join) {
                $join->on('pages.page_id', '=', 'blocks.page_id')
                     ->where('pages.private_flag', 0);
            })
            ->join('pages as page_rooms', function ($join) {
                $join->on('page_rooms.page_id', '=', 'reservation_block.room_id')
                     ->whereColumn('page_rooms.page_id', 'page_rooms.room_id')
                     ->whereIn('page_rooms.space_type', [1, 2])     // 1:パブリックスペース, 2:グループスペース
                     ->where('page_rooms.room_id', '!=', 2)         // 2:グループスペースを除外（枠だけでグループルームじゃないので除外）
                     ->where('page_rooms.private_flag', 0);         // 0:プライベートルーム以外
            })
            ->orderBy('reservation_block.block_id')
            ->get();

        // 空なら戻る
        if ($nc3_reservation_blocks->isEmpty()) {
            return;
        }

        // エクスポート対象の施設予約名をページ名から取得する（指定がなければブロックタイトルがあればブロックタイトル。なければページ名）
        $reservation_name_is_page_name = $this->getMigrationConfig('reservations', 'nc3_export_reservation_name_is_page_name');

        // NC3施設予約ブロックのループ
        foreach ($nc3_reservation_blocks as $nc3_reservation_block) {

            // NC3 施設予約ブロック（表示方法）設定
            $ini = "";
            $ini .= "[reservation_block]\n";

            // 表示方法
            // 1: 月表示(施設別)
            // 2: 週表示(施設別)
            // 3: 日表示(カテゴリ別)
            $ini .= "display_type = " . $nc3_reservation_block->display_type . "\n";

            // （表示する）カテゴリ（「最初に表示する施設」を絞り込むための設定）
            // 0:全て表示
            // 1:カテゴリなし
            // 2以降: 任意のカテゴリ
            $ini .= "category_id = " . $nc3_reservation_block->category_id . "\n";

            // 最初に表示する施設
            // ※ 表示方法=月・週表示のみ設定される
            $ini .= "location_id = " . $nc3_reservation_block->location_id . "\n";

            // 時間枠表示
            // 0:表示しない
            // 1:表示する
            // $ini .= "display_timeframe = " . $nc3_reservation_block->display_timeframe . "\n";

            // 表示開始時
            // default: 閲覧時刻により変動
            // default以外（0900等）：時間固定
            // $ini .= "display_start_time = " . $nc3_reservation_block->display_start_time . "\n";

            // 表示幅
            // $ini .= "display_interval = " .  $nc3_reservation_block->display_interval . "\n";

            // 施設予約の名前は、ブロックタイトルがあればブロックタイトル。なければページ名。
            $reservation_name = '無題';
            if (!empty($nc3_reservation_block->page_name)) {
                $reservation_name = $nc3_reservation_block->page_name;
            }
            if (empty($reservation_name_is_page_name)) {
                if (!empty($nc3_reservation_block->block_name)) {
                    $reservation_name = $nc3_reservation_block->block_name;
                }
            }
            $ini .= "reservation_name = \""  . $reservation_name . "\"\n";

            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "reservation_block_id = " . $nc3_reservation_block->block_id . "\n";
            $ini .= "room_id = " . $nc3_reservation_block->room_id . "\n";
            $ini .= "room_name = \"" . $nc3_reservation_block->room_name . "\"\n";
            $ini .= "module_name = \"reservation\"\n";

            // 施設予約の設定を出力
            $this->storagePut($this->getImportPath('reservations/reservation_block_') . $this->zeroSuppress($nc3_reservation_block->block_id) . '.ini', $ini);
        }
    }

    /**
     * NC3：フォトアルバム（Photoalbum）の移行
     */
    private function nc3ExportPhotoalbum($redo)
    {
        $this->putMonitor(3, "Start nc3ExportPhotoalbum.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('photoalbums/'));
        }

        // NC3フォトアルバム（Photoalbum）を移行する。
        $nc3_export_where_photoalbum_ids = $this->getMigrationConfig('photoalbums', 'nc3_export_where_photoalbum_ids');

        if (empty($nc3_export_where_photoalbum_ids)) {
            $nc3_photoalbums = Nc2Photoalbum::orderBy('photoalbum_id')->get();
        } else {
            $nc3_photoalbums = Nc2Photoalbum::whereIn('photoalbum_id', $nc3_export_where_photoalbum_ids)->orderBy('photoalbum_id')->get();
        }

        // 空なら戻る
        if ($nc3_photoalbums->isEmpty()) {
            return;
        }


        // nc3の全ユーザ取得
        $nc3_users = Nc2User::get();

        $nc3_photoalbum_alubums_all = Nc2PhotoalbumAlbum::orderBy('photoalbum_id')->orderBy('album_sequence')->get();
        $nc3_photoalbum_photos_all = Nc2PhotoalbumPhoto::orderBy('photoalbum_id')->orderBy('album_id')->orderBy('photo_sequence')->get();

        // NC3フォトアルバム（Photoalbum）のループ
        foreach ($nc3_photoalbums as $nc3_photoalbum) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_photoalbum->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // データベース設定
            $photoalbum_ini = "";
            $photoalbum_ini .= "[photoalbum_base]\n";
            $photoalbum_ini .= "photoalbum_name = \"" . $nc3_photoalbum->photoalbum_name . "\"\n";

            // NC3 情報
            $photoalbum_ini .= "\n";
            $photoalbum_ini .= "[source_info]\n";
            $photoalbum_ini .= "photoalbum_id = " . $nc3_photoalbum->photoalbum_id . "\n";
            $photoalbum_ini .= "room_id = " . $nc3_photoalbum->room_id . "\n";
            $photoalbum_ini .= "module_name = \"photoalbum\"\n";
            $photoalbum_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_photoalbum->created) . "\"\n";
            $photoalbum_ini .= "created_name    = \"" . $nc3_photoalbum->insert_user_name . "\"\n";
            $photoalbum_ini .= "insert_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum->created_user) . "\"\n";
            $photoalbum_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_photoalbum->modified) . "\"\n";
            $photoalbum_ini .= "updated_name    = \"" . $nc3_photoalbum->update_user_name . "\"\n";
            $photoalbum_ini .= "update_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum->modified_user) . "\"\n";

            // アルバム 情報
            $photoalbum_ini .= "\n";
            $photoalbum_ini .= "[albums]\n";

            $nc3_photoalbum_alubums = $nc3_photoalbum_alubums_all->where('photoalbum_id', $nc3_photoalbum->photoalbum_id);
            foreach ($nc3_photoalbum_alubums as $nc3_photoalbum_alubum) {
                $photoalbum_ini .= "album[" . $nc3_photoalbum_alubum->album_id . "] = \"" . $nc3_photoalbum_alubum->album_name . "\"\n";
            }
            $photoalbum_ini .= "\n";

            // アルバム詳細 情報
            foreach ($nc3_photoalbum_alubums as $nc3_photoalbum_alubum) {
                $photoalbum_ini .= "[" . $nc3_photoalbum_alubum->album_id . "]" . "\n";
                $photoalbum_ini .= "album_id                   = \"" . $nc3_photoalbum_alubum->album_id . "\"\n";
                $photoalbum_ini .= "album_name                 = \"" . $nc3_photoalbum_alubum->album_name . "\"\n";
                $photoalbum_ini .= "album_description          = \"" . $nc3_photoalbum_alubum->album_description . "\"\n";
                $photoalbum_ini .= "public_flag                = "   . $nc3_photoalbum_alubum->public_flag . "\n";
                $photoalbum_ini .= "nc3_upload_id              = "   . $nc3_photoalbum_alubum->upload_id . "\n";
                $photoalbum_ini .= "width                      = "   . $nc3_photoalbum_alubum->width . "\n";
                $photoalbum_ini .= "height                     = "   . $nc3_photoalbum_alubum->height . "\n";
                $photoalbum_ini .= "created_at                 = \"" . $this->getCCDatetime($nc3_photoalbum_alubum->created) . "\"\n";
                $photoalbum_ini .= "created_name               = \"" . $nc3_photoalbum_alubum->insert_user_name . "\"\n";
                $photoalbum_ini .= "insert_login_id            = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_alubum->created_user) . "\"\n";
                $photoalbum_ini .= "updated_at                 = \"" . $this->getCCDatetime($nc3_photoalbum_alubum->modified) . "\"\n";
                $photoalbum_ini .= "updated_name               = \"" . $nc3_photoalbum_alubum->update_user_name . "\"\n";
                $photoalbum_ini .= "update_login_id            = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_alubum->modified_user) . "\"\n";
                $photoalbum_ini .= "\n";
            }

            // フォトアルバム の設定
            $this->storagePut($this->getImportPath('photoalbums/photoalbum_') . $this->zeroSuppress($nc3_photoalbum->photoalbum_id) . '.ini', $photoalbum_ini);

            // カラムのヘッダー及びTSV 行毎の枠準備
            $tsv_header = "photo_id" . "\t" . "nc3_upload_id" . "\t" . "photo_name" . "\t" . "photo_description" . "\t" . "width" . "\t" ."height" . "\t" .
                "created_at" . "\t" . "created_name" . "\t" . "insert_login_id" . "\t" . "updated_at" . "\t" . "updated_name" . "\t" . "update_login_id";

            $tsv_cols['photo_id'] = "";
            $tsv_cols['nc3_upload_id'] = "";
            $tsv_cols['photo_name'] = "";
            $tsv_cols['photo_description'] = "";
            $tsv_cols['width'] = "";
            $tsv_cols['height'] = "";
            $tsv_cols['created_at'] = "";
            $tsv_cols['created_name'] = "";
            $tsv_cols['insert_login_id'] = "";
            $tsv_cols['updated_at'] = "";
            $tsv_cols['updated_name'] = "";
            $tsv_cols['update_login_id'] = "";

            // 写真 情報
            foreach ($nc3_photoalbum_alubums as $nc3_photoalbum_alubum) {

                Storage::delete($this->getImportPath('photoalbums/photoalbum_') . $this->zeroSuppress($nc3_photoalbum->photoalbum_id) . '_' . $this->zeroSuppress($nc3_photoalbum_alubum->album_id) . '.tsv');

                $tsv = '';
                $tsv .= $tsv_header . "\n";

                $nc3_photoalbum_photos = $nc3_photoalbum_photos_all->where('album_id', $nc3_photoalbum_alubum->album_id);
                foreach ($nc3_photoalbum_photos as $nc3_photoalbum_photo) {

                    // 初期化
                    $tsv_record = $tsv_cols;

                    $tsv_record['photo_id']          = $nc3_photoalbum_photo->photo_id;
                    $tsv_record['nc3_upload_id']     = $nc3_photoalbum_photo->upload_id;
                    $tsv_record['photo_name']        = $nc3_photoalbum_photo->photo_name;
                    $tsv_record['photo_description'] = $nc3_photoalbum_photo->photo_description;
                    $tsv_record['width']             = $nc3_photoalbum_photo->width;
                    $tsv_record['height']            = $nc3_photoalbum_photo->height;
                    $tsv_record['created_at']        = $this->getCCDatetime($nc3_photoalbum_photo->created);
                    $tsv_record['created_name']      = $nc3_photoalbum_photo->insert_user_name;
                    $tsv_record['insert_login_id']   = $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_photo->created_user);
                    $tsv_record['updated_at']        = $this->getCCDatetime($nc3_photoalbum_photo->modified);
                    $tsv_record['updated_name']      = $nc3_photoalbum_photo->update_user_name;
                    $tsv_record['update_login_id']   = $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_photo->modified_user);

                    $tsv .= implode("\t", $tsv_record) . "\n";
                }

                // データ行の書き出し
                $tsv = $this->exportStrReplace($tsv, 'photoalbums');
                $this->storageAppend($this->getImportPath('photoalbums/photoalbum_') . $this->zeroSuppress($nc3_photoalbum->photoalbum_id) . '_' . $this->zeroSuppress($nc3_photoalbum_alubum->album_id) . '.tsv', $tsv);
            }

            // スライド表示はスライダーにも移行

            // photoalbum_block の取得
            // 1DB で複数ブロックがあるので、Join せずに、個別に読む
            $nc3_photoalbum_blocks = Nc2PhotoalbumBlock::where('photoalbum_id', $nc3_photoalbum->photoalbum_id)
                ->where('display', Nc2PhotoalbumBlock::DISPLAY_SLIDESHOW)
                ->orderBy('block_id', 'asc')->get();

            // NC3スライダー（Slideshow）のループ
            foreach ($nc3_photoalbum_blocks as $nc3_photoalbum_block) {
                // アルバム
                $nc3_photoalbum_alubum = $nc3_photoalbum_alubums_all->firstWhere('album_id', $nc3_photoalbum_block->display_album_id);
                $nc3_photoalbum_alubum = $nc3_photoalbum_alubum ?? new Nc2PhotoalbumAlbum();

                // (nc)秒 => (cc)ミリ秒
                $image_interval = $nc3_photoalbum_block->slide_time * 1000;

                $height = $nc3_photoalbum_block->size_flag ? $nc3_photoalbum_block->height : null;

                // スライダー設定
                $slide_ini = "";
                $slide_ini .= "[slideshow_base]\n";
                $slide_ini .= "slideshows_name = \"{$nc3_photoalbum_alubum->album_name}\"\n";
                $slide_ini .= "image_interval = {$image_interval}\n";
                $slide_ini .= "height = {$height}\n";

                // NC3 情報
                $slide_ini .= "\n";
                $slide_ini .= "[source_info]\n";
                $slide_ini .= "slideshows_block_id = " . $nc3_photoalbum_block->block_id . "\n";
                $slide_ini .= "photoalbum_id = " . $nc3_photoalbum->photoalbum_id . "\n";
                $slide_ini .= "photoalbum_name = \"" . $nc3_photoalbum->photoalbum_name . "\"\n";
                $slide_ini .= "room_id = " . $nc3_photoalbum_block->room_id . "\n";
                $slide_ini .= "module_name = \"photoalbum\"\n";
                $slide_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_photoalbum_block->created) . "\"\n";
                $slide_ini .= "created_name    = \"" . $nc3_photoalbum_block->insert_user_name . "\"\n";
                $slide_ini .= "insert_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_block->created_user) . "\"\n";
                $slide_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_photoalbum_block->modified) . "\"\n";
                $slide_ini .= "updated_name    = \"" . $nc3_photoalbum_block->update_user_name . "\"\n";
                $slide_ini .= "update_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_block->modified_user) . "\"\n";

                // 写真
                $nc3_photoalbum_photos = $nc3_photoalbum_photos_all->where('album_id', $nc3_photoalbum_block->display_album_id);

                // TSV でエクスポート
                // image_path{\t}uploads_id{\t}link_url{\t}link_target{\t}caption{\t}display_flag{\t}display_sequence
                $slides_tsv = "";
                foreach ($nc3_photoalbum_photos as $i => $nc3_photoalbum_photo) {

                    $display_sequence = $i + 1;

                    // TSV 形式でエクスポート
                    if (!empty($slides_tsv)) {
                        $slides_tsv .= "\n";
                    }
                    $slides_tsv .= "\t";                                        // image_path
                    $slides_tsv .= $nc3_photoalbum_photo->upload_id . "\t";     // uploads_id
                    $slides_tsv .= "\t";                                        // link_url
                    $slides_tsv .= "\t";                                        // link_target
                    $slides_tsv .= "\t";                                        // caption
                    $slides_tsv .= "1\t";                                       // display_flag
                    $slides_tsv .= $display_sequence . "\t";                    // display_sequence
                }

                // スライダーの設定を出力
                $this->storagePut($this->getImportPath('slideshows/slideshows_') . $this->zeroSuppress($nc3_photoalbum_block->block_id) . '.ini', $slide_ini);
                // スライダーの付与情報を出力
                $this->storagePut($this->getImportPath('slideshows/slideshows_') . $this->zeroSuppress($nc3_photoalbum_block->block_id) . '.tsv', $slides_tsv);
            }
        }
    }

    /**
     * NC3：固定リンク（abbreviate_url）の移行
     */
    private function nc3ExportAbbreviateUrl($redo)
    {
        $this->putMonitor(3, "Start nc3ExportAbbreviateUrl.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('permalinks/'));
        }

        /*
        [permalinks]
        permalink[0] = "jojip4z1j"
        permalink[1] = "joboq0e7j"

        [jojip4z1j]
        short_url      = "jojip4z1j"
        plugin_name    = "blogs"
        action         = show
        unique_id      = 1
        migrate_source = "NetCommons3"
        */

        // NC3固定リンク（abbreviate_url）を移行する。
        $nc3_abbreviate_urls = Nc2AbbreviateUrl::orderBy('insert_time')->get();

        // 空なら戻る
        if ($nc3_abbreviate_urls->isEmpty()) {
            return;
        }

        // ini ファイル用変数
        $permalinks_ini = "[permalinks]\n";

        // NC3固定リンクのループ（インデックス用）
        $index = 0;
        foreach ($nc3_abbreviate_urls as $nc3_abbreviate_url) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_abbreviate_url->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            $permalinks_ini .= "permalink[" . $index . "] = \"" . $nc3_abbreviate_url->short_url . "\"\n";
            $index++;
        }

        // [journal]
        // select
        //     nc3_blocks.block_id
        // from
        //     nc3_blocks,
        //     nc3_journal_block,
        //     nc3_journal_post,
        //     nc3_abbreviate_url
        // where
        //     nc3_blocks.block_id = nc3_journal_block.block_id
        //     and nc3_journal_block.journal_id = nc3_journal_post.journal_id
        //     and nc3_journal_post.journal_id = nc3_abbreviate_url.contents_id
        //     and nc3_journal_post.post_id = nc3_abbreviate_url.unique_id
        //     and nc3_abbreviate_url.short_url = "muwoibbvq"
        //
        // [multidatabase]
        // select
        //  nc3_blocks.block_id
        // from
        //  nc3_blocks,
        //  nc3_multidatabase_block,
        //  nc3_multidatabase_content,
        //  nc3_abbreviate_url
        // where
        //  nc3_blocks.block_id = nc3_multidatabase_block.block_id
        //  and nc3_multidatabase_block.multidatabase_id = nc3_multidatabase_content.multidatabase_id
        //  and nc3_multidatabase_content.multidatabase_id = nc3_abbreviate_url.contents_id
        //  and nc3_multidatabase_content.content_id = nc3_abbreviate_url.unique_id
        //  and nc3_abbreviate_url.short_url = "muwoibbvq"
        //
        // [bbs]
        // select
        //  nc3_blocks.block_id
        // from
        //  nc3_blocks,
        //  nc3_bbs_block,
        //  nc3_bbs_post,
        //  nc3_abbreviate_url
        // where
        //  nc3_blocks.block_id = nc3_bbs_block.block_id
        //  and nc3_bbs_block.bbs_id = nc3_bbs_post.bbs_id
        //  and nc3_bbs_post.bbs_id = nc3_abbreviate_url.contents_id
        //  and nc3_bbs_post.post_id = nc3_abbreviate_url.unique_id
        //  and nc3_abbreviate_url.short_url = "muwoibbvq"

        // 最新block_ids
        $journal_block_ids = Nc2AbbreviateUrl::select('blocks.block_id', 'abbreviate_url.short_url')
            ->join('journal_post', function ($join) {
                $join->on('journal_post.post_id', '=', 'abbreviate_url.unique_id')
                    ->whereColumn('journal_post.journal_id', 'abbreviate_url.contents_id');
            })
            ->join('journal_block', 'journal_block.journal_id', '=', 'journal_post.journal_id')
            ->join('blocks', 'blocks.block_id', '=', 'journal_block.block_id')
            ->get(['block_id', 'short_url']);
        $multidatabase_block_ids = Nc2AbbreviateUrl::select('blocks.block_id', 'abbreviate_url.short_url')
            ->join('multidatabase_content', 'multidatabase_content.content_id', '=', 'abbreviate_url.unique_id')
            ->join('multidatabase_block', 'multidatabase_block.multidatabase_id', '=', 'multidatabase_content.multidatabase_id')
            ->join('blocks', 'blocks.block_id', '=', 'multidatabase_block.block_id')
            ->get(['block_id', 'short_url']);
        $bbs_block_ids = Nc2AbbreviateUrl::select('blocks.block_id', 'abbreviate_url.short_url')
            ->join('bbs_post', function ($join) {
                $join->on('bbs_post.post_id', '=', 'abbreviate_url.unique_id')
                    ->whereColumn('bbs_post.bbs_id', 'abbreviate_url.contents_id');
            })
            ->join('bbs_block', 'bbs_block.bbs_id', '=', 'bbs_post.bbs_id')
            ->join('blocks', 'blocks.block_id', '=', 'bbs_block.block_id')
            ->get(['block_id', 'short_url']);

        // NC3固定リンクのループ（データ用）
        foreach ($nc3_abbreviate_urls as $nc3_abbreviate_url) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_abbreviate_url->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            if (!isset($this->plugin_name[$nc3_abbreviate_url->dir_name])) {
                $this->putError(3, '固定URLの未対応モジュール', "nc3_abbreviate_url.dir_name = " . $nc3_abbreviate_url->dir_name);
                continue;
            }

            $permalink  = "\n";
            $permalink .= "[\"" . $nc3_abbreviate_url->short_url . "\"]\n";

            $plugin_name = $this->plugin_name[$nc3_abbreviate_url->dir_name];
            $permalink .= "plugin_name    = \"" . $plugin_name . "\"\n";

            if ($plugin_name == 'blogs') {
                $permalink .= "action         = \"show\"\n";
            } elseif ($plugin_name == 'databases') {
                $permalink .= "action         = \"detail\"\n";
            } elseif ($plugin_name == 'bbses') {
                $permalink .= "action         = \"show\"\n";
            }

            // change: 新 unique_id は、エクスポート時に取得不可能のため、インポート時にセットする
            // if (!empty($plugin_name)) {
            //     $unique_id = 0;
            //     $migration_mappings = MigrationMapping::where('target_source_table', $plugin_name . '_post')->where('source_key', $nc3_abbreviate_url->unique_id)->first();
            //     if (empty($migration_mappings)) {
            //         continue;
            //     }
            //     $permalink .= "unique_id      = " . $migration_mappings->destination_key .  "\n";
            // }
            // nc3 unique_id
            $permalink .= "unique_id      = " . $nc3_abbreviate_url->unique_id .  "\n";

            // 最新block_id取得
            $block_id = null;
            if ($plugin_name == 'blogs') {
                $journal_block_id = $journal_block_ids->firstWhere('short_url', $nc3_abbreviate_url->short_url);
                $block_id = $journal_block_id->block_id ?? null;

            } elseif ($plugin_name == 'databases') {
                $multidatabase_block_id = $multidatabase_block_ids->firstWhere('short_url', $nc3_abbreviate_url->short_url);
                $block_id = $multidatabase_block_id->block_id ?? null;

            } elseif ($plugin_name == 'bbses') {
                $bbs_block_id = $bbs_block_ids->firstWhere('short_url', $nc3_abbreviate_url->short_url);
                $block_id = $bbs_block_id->block_id ?? null;
            }
            $permalink .= "block_id       = " . $block_id .  "\n";

            $permalink .= "migrate_source = \"NetCommons3\"\n";
            $permalinks_ini .= $permalink;
        }

        // Userデータの出力
        //Storage::put($this->getImportPath('permalinks/permalinks.ini'), $permalinks_ini);
        $this->storagePut($this->getImportPath('permalinks/permalinks.ini'), $permalinks_ini);
    }

    /**
     * NC3：ページ内のフレームをループ
     */
    private function nc3Frame(Nc3Page $nc3_page, int $new_page_index, Nc3Page $nc3_top_page)
    {
        // 指定されたページ内のブロックを取得
        $nc3_frames_query = Nc3Frame::
            select(
                'frames.*',
                'frames_languages.name as frame_name',
                'frames_languages.language_id as language_id',
                'boxes.container_type as container_type',
                'blocks.key as block_key'
            )
            ->join('boxes', 'boxes.id', '=', 'frames.box_id')
            ->join('frames_languages', function ($join) {
                $join->on('frames_languages.frame_id', '=', 'frames.id');
            })
            ->leftJoin('blocks', 'blocks.id', '=', 'frames.block_id')
            ->where('boxes.page_id', $nc3_page->id)
            ->where('frames.is_deleted', 0);

        // 対象外のフレームがあれば加味する。
        $export_ommit_frames = $this->getMigrationConfig('frames', 'export_ommit_frames');
        if (!empty($export_ommit_frames)) {
            $nc3_frames_query->whereNotIn('frames.id', $export_ommit_frames);
        }

        // migration_config.sample.iniにも設定値が存在しないため、コメントアウト
        // メニューが対象外なら除外する。
        // $export_ommit_menu = $this->getMigrationConfig('menus', 'export_ommit_menu');
        // if ($export_ommit_menu) {
        //     $nc3_frames_query->where('action_name', '<>', 'menu_view_main_init');
        // }

        $nc3_frames = $nc3_frames_query
            ->orderBy('boxes.space_id')
            ->orderBy('boxes.room_id')
            ->orderBy('boxes.page_id')
            ->orderBy('boxes.weight')
            ->get();

        // ブロックをループ
        $frame_index = 0; // フレームの連番

        // [Connect出力] 割り切り実装
        // ・サイトトップページ　　　：ヘッダ・フッタ・左・右は、（サイト全体・パブ共通・ルーム共通・当ページのみ）であっても、Connectでは結果として、サイト全体設定として扱われる。
        // ・ルームのトップページ　　：（サイト全体・パブ共通・ルーム共通）ヘッダ・フッタ・左・右を出力
        //  　　　 ・（ヘッダ・フッタ）サイトトップとbox_idが違ければ出力
        //   　　　・（左・右）　　　　サイトトップとbox_id（複数）が違ければframe_idが同じでも出力
        // ・全ページ共通　　　　　　：メインエリア出力,（当ページのみ）ヘッダ・フッタ・左・右を出力.
        //
        // [NC3]
        // NC3 では、ヘッダ、フッタが下記いずれかで別れてる。
        // ・サイト全体で共通のエリア = 切り替えると、ルーム単位で反映。中身はサイト共通
        // ・パブリック共通のエリア   = 切り替えると、ルーム単位で反映。中身はパブ共通
        // ・ルーム共通のエリア       = 切り替えると、ルーム単位で反映。中身はルーム共通
        // ・当ページのみのエリア     = 切り替えると、このページのみ反映。中身はページ単位
        //
        // 左、右は、ON・OFF設定（全体＋当ページのみ等）できる。
        // ・サイト全体で共通のエリア = ON・OFF設定、このページのみ反映。中身はサイト共通
        // ・パブリック共通のエリア   = ON・OFF設定、このページのみ反映。中身はパブ共通
        // ・ルーム共通のエリア       = ON・OFF設定、このページのみ反映。中身はルーム共通
        // ・当ページのみのエリア     = ON・OFF設定、このページのみ反映。中身はページ単位
        //
        // --- nc3でのヘッダ、左、右、フッタ取得順
        // page ->
        //  nc3_page_containers(どのエリアが見えてる(is_published = 1)・見えてないか) ->
        //    nc3_boxes_page_containers(全エリア(page_id = 999 and is_published = 1)のbox特定) ->
        //      box ->
        //        frame

        // ルームのトップページ
        if ($nc3_page->id == $nc3_page->page_id_top) {

            // 開いてるページのbox_id
            $nc3_boxes = Nc3PageContainer::select('boxes.*')
                ->where('page_containers.page_id', $nc3_page->id)
                ->join('boxes_page_containers', function ($join) {
                    $join->on('boxes_page_containers.page_container_id', '=', 'page_containers.id')
                        ->where('boxes_page_containers.is_published', 1);      // 有効なデータ
                })
                ->join('boxes', 'boxes.id', '=', 'boxes_page_containers.box_id')
                ->where('page_containers.is_published', 1)      // 見えてるエリア
                ->where('boxes.page_id', null)                  // page_id = nullは共通エリア（サイト全体・パブ共通・ルーム共通）
                ->get();

            $container_types = [
                Nc3Box::container_type_header,
                Nc3Box::container_type_left,
                Nc3Box::container_type_main,
                Nc3Box::container_type_right,
                Nc3Box::container_type_footer
            ];
            $common_box_ids = [];
            foreach ($container_types as $container_type) {
                // 差があれば、元のnc3_boxesをセット
                $nc3_boxes_arr = $nc3_boxes->where('container_type', $container_type)->pluck('id')->toArray();
                $nc3_boxes_diff = array_diff($nc3_boxes_arr, $this->exported_common_top_page_box_ids[$container_type]);
                if (!empty($nc3_boxes_diff)) {
                    $common_box_ids = array_merge_recursive($common_box_ids, $nc3_boxes_arr);
                }
            }

            // box_idを使って指定されたページ内のフレーム取得
            $nc3_common_frames_query = Nc3Frame::
                select(
                    'frames.*',
                    'frames_languages.name as frame_name',
                    'frames_languages.language_id as language_id',
                    'boxes.container_type as container_type',
                    'blocks.key as block_key'
                )
                ->join('boxes', 'boxes.id', '=', 'frames.box_id')
                ->join('frames_languages', function ($join) {
                    $join->on('frames_languages.frame_id', '=', 'frames.id');
                })
                ->leftJoin('blocks', 'blocks.id', '=', 'frames.block_id')
                ->whereIn('boxes.id', $common_box_ids)
                ->where('frames.is_deleted', 0);

            // 対象外のフレームがあれば加味する。
            if (!empty($export_ommit_frames)) {
                $nc3_common_frames_query->whereNotIn('frames.id', $export_ommit_frames);
            }

            $nc3_common_frames = $nc3_common_frames_query
                ->orderBy('boxes.space_id')
                ->orderBy('boxes.room_id')
                ->orderBy('boxes.page_id')
                ->orderBy('boxes.weight')
                ->get();

            // 共通部分を frame 設定に追加する。
            foreach ($nc3_common_frames as $nc3_common_frame) {
                // frame 設定に追加
                $nc3_frames->prepend($nc3_common_frame);
            }

            // サイトトップページのみbox_idを保持
            if ($nc3_page->id == $nc3_top_page->id) {
                $this->exported_common_top_page_box_ids = [
                    Nc3Box::container_type_header => $nc3_boxes->where('container_type', Nc3Box::container_type_header)->pluck('id')->toArray(),
                    Nc3Box::container_type_left   => $nc3_boxes->where('container_type', Nc3Box::container_type_left)->pluck('id')->toArray(),
                    Nc3Box::container_type_main   => $nc3_boxes->where('container_type', Nc3Box::container_type_main)->pluck('id')->toArray(),
                    Nc3Box::container_type_right  => $nc3_boxes->where('container_type', Nc3Box::container_type_right)->pluck('id')->toArray(),
                    Nc3Box::container_type_footer => $nc3_boxes->where('container_type', Nc3Box::container_type_footer)->pluck('id')->toArray(),
                ];
            }
        }

        // ページ内のブロック
        foreach ($nc3_frames as $nc3_frame) {
            $this->putMonitor(1, "Frame", "frame_id = " . $nc3_frame->id);

            // NC3 フレーム強制上書き設定があれば反映
            $nc3_frame = $this->overrideNc3Frame($nc3_frame);

            $frame_index++;
            $frame_index_str = sprintf("%'.04d", $frame_index);

            // (nc3)container_type 1:Header, 2:Major(Left), 3:Main, 4:Minor(Right), 5:Footer
            // (key:nc3)container_type => (value:cc)area_id
            $convert_area_ids = [
                Nc3Box::container_type_header => AreaType::header,
                Nc3Box::container_type_left   => AreaType::left,
                Nc3Box::container_type_right  => AreaType::right,
                Nc3Box::container_type_footer => AreaType::footer,
            ];
            $area_id = $convert_area_ids[$nc3_frame->container_type] ?? AreaType::main;

            // フレーム設定の保存用変数
            $frame_ini = "[frame_base]\n";
            $frame_ini .= "area_id = " . $area_id . "\n";

            // フレームタイトル＆メニューの特別処理
            if ($nc3_frame->plugin_key == 'menus') {
                $frame_ini .= "frame_title = \"\"\n";
            } else {
                $frame_ini .= "frame_title = \"" . $nc3_frame->frame_name . "\"\n";
            }

            if (!empty($nc3_frame->frame_design)) {
                // overrideNc3Frame()関連設定
                $frame_ini .= "frame_design = \"{$nc3_frame->frame_design}\"\n";
            } elseif ($nc3_frame->container_type == Nc3Box::container_type_header) {
                // ヘッダーは無条件にフレームデザインをnone にしておく
                $frame_ini .= "frame_design = \"none\"\n";
            } elseif ($nc3_frame->plugin_key == 'menus') {
                $frame_ini .= "frame_design = \"none\"\n";
            } else {
                $frame_ini .= "frame_design = \"" . $nc3_frame->getFrameDesign($this->getMigrationConfig('frames', 'export_frame_default_design', 'default')) . "\"\n";
            }

            if ($nc3_frame->plugin_key == 'photo_albums') {
                // フォトアルバムでスライド表示は、スライドプラグインに移行
                $nc3_photoalbum_frame_setting = Nc3PhotoAlbumFrameSetting::where('frame_key', $nc3_frame->key)
                    ->where('display_type', Nc3PhotoAlbumFrameSetting::DISPLAY_SLIDESHOW)
                    ->first();
                if ($nc3_photoalbum_frame_setting) {
                    $frame_ini .= "plugin_name = \"slideshows\"\n";
                } else {
                    $frame_ini .= "plugin_name = \"" . $this->nc3GetPluginName($nc3_frame->plugin_key) . "\"\n";
                }
            } else {
                $frame_ini .= "plugin_name = \"" . $this->nc3GetPluginName($nc3_frame->plugin_key) . "\"\n";
            }

            // overrideNc3Frame()関連設定
            if (!empty($nc3_frame->frame_col)) {
                $frame_ini .= "frame_col = " . $nc3_frame->frame_col . "\n";
            }

            // 各項目
            // [TODO] 未対応
            // if ($nc3_frame->plugin_key == 'calendars') {
            //     $calendar_block_ini = null;
            //     $calendar_display_type = null;

            //     // カレンダーブロックの情報取得
            //     if (Storage::exists($this->getImportPath('calendars/calendar_block_') . $this->zeroSuppress($nc3_frame->block_id) . '.ini')) {
            //         $calendar_block_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('calendars/calendar_block_') . $this->zeroSuppress($nc3_frame->block_id) . '.ini', true);
            //     }

            //     if (!empty($calendar_block_ini) && array_key_exists('calendar_block', $calendar_block_ini) && array_key_exists('display_type', $calendar_block_ini['calendar_block'])) {
            //         // NC3 のcalendar の display_type
            //         $calendar_display_type = MigrationUtils::getArrayValue($calendar_block_ini, 'calendar_block', 'display_type', null);
            //     }

            //     // frame_design 変換 (key:nc3)display_type => (value:cc)template
            //     // (NC3)初期値 = 月表示（縮小）= 2
            //     // (CC) 初期値 = 月表示（大）= default
            //     $display_type_to_frame_designs = [
            //         1 => 'default',     // 1:年間表示
            //         2 => 'small_month', // 2:月表示（縮小）
            //         3 => 'default',     // 3:月表示（拡大）
            //         4 => 'default',     // 4:週表示
            //         5 => 'day',         // 5:日表示
            //         6 => 'day',         // 6:スケジュール（時間順）
            //         7 => 'day',         // 7:スケジュール（会員順）
            //     ];
            //     $frame_design = $display_type_to_frame_designs[$calendar_display_type] ?? 'default';
            //     $frame_ini .= "template = \"" . $frame_design . "\"\n";
            // }
            if (!empty($nc3_frame->template)) {
                // overrideNc3Frame()関連設定 があれば最優先で設定
                $frame_ini .= "template = \"" . $nc3_frame->template . "\"\n";
            } elseif ($nc3_frame->plugin_key == 'menus') {
                $nc3_menu_frame_setting = Nc3MenuFrameSetting::where('frame_key', $nc3_frame->key)->first() ?? new Nc3MenuFrameSetting();

                // メニューの横長系のテンプレートの場合、Connect-CMS では「ドロップダウン」に変更する。
                // (key:nc3)display_type => (value:cc)template
                $convert_templates = [
                    Nc3MenuFrameSetting::display_list                     => 'opencurrenttree', // (cc)ディレクトリ展開式
                    Nc3MenuFrameSetting::display_nav_pills                => 'dropdown',
                    Nc3MenuFrameSetting::display_nav_tabs                 => 'dropdown',
                    Nc3MenuFrameSetting::display_minor                    => 'parentsandchild', // (nc3)下層のみ -> (cc)親子のみ [TODO]今後バグ修正で ancestor_descendant_sibling 親子兄弟 に直すかも
                    Nc3MenuFrameSetting::display_topic_path               => 'breadcrumbs',
                    Nc3MenuFrameSetting::display_header_flat              => 'tab_flat',
                    Nc3MenuFrameSetting::display_header_ids               => 'dropdown',
                    Nc3MenuFrameSetting::display_header_minor             => 'dropdown',        // (nc3) ヘッダー下層のみ
                    Nc3MenuFrameSetting::display_header_minor_noroot      => 'dropdown',
                    Nc3MenuFrameSetting::display_header_minor_noroot_room => 'dropdown',
                    Nc3MenuFrameSetting::display_minor_and_first          => 'opencurrenttree',
                ];
                $template = $convert_templates[$nc3_menu_frame_setting->display_type] ?? 'default';
                $frame_ini .= "template = \"{$template}\"\n";
            } else {
                $frame_ini .= "template = \"default\"\n";
            }

            // overrideNc3Frame()関連設定
            if (!empty($nc3_frame->browser_width)) {
                $frame_ini .= "browser_width = \"" . $nc3_frame->browser_width . "\"\n";
            }
            if (!empty($nc3_frame->disable_whatsnews)) {
                $frame_ini .= "disable_whatsnews = " . $nc3_frame->disable_whatsnews . "\n";
            }
            if (!empty($nc3_frame->page_only)) {
                $frame_ini .= "page_only = " . $nc3_frame->page_only . "\n";
            }
            if (!empty($nc3_frame->default_hidden)) {
                $frame_ini .= "default_hidden = " . $nc3_frame->default_hidden . "\n";
            }
            if (!empty($nc3_frame->classname)) {
                $frame_ini .= "classname = \"" . $nc3_frame->classname . "\"\n";
            }
            if (!empty($nc3_frame->none_hidden)) {
                $frame_ini .= "none_hidden = " . $nc3_frame->none_hidden . "\n";
            }
            if (!empty($nc3_frame->display_sequence)) {
                $frame_ini .= "display_sequence = " . $nc3_frame->display_sequence . "\n";
            }

            // モジュールに紐づくメインのデータのID
            $frame_ini .= $this->nc3FrameMainDataId($nc3_frame);

            // NC3 情報
            $frame_nc3 = "\n";
            $frame_nc3 .= "[source_info]\n";
            $frame_nc3 .= "source_key = \"" . $nc3_frame->id . "\"\n";
            $frame_nc3 .= "target_source_table = \"" . $nc3_frame->plugin_key . "\"\n";
            $frame_nc3 .= "created_at = \"" . $this->getCCDatetime($nc3_frame->created) . "\"\n";
            $frame_nc3 .= "updated_at = \"" . $this->getCCDatetime($nc3_frame->modified) . "\"\n";
            $frame_ini .= $frame_nc3;

            // frame_id重複すると、インポート時に登録されない（アップデートになる）ため、登録するよう対応
            // NC3は（ヘッダ・フッタ・左右）のルーム共通等で、同じフレームが別ページに表示される事がありえるため、同じフレームIDでも登録する。
            if (in_array($nc3_frame->id, $this->exported_frame_ids)) {
                $counts = array_count_values($this->exported_frame_ids);
                $count = $counts[$nc3_frame->id] + 1;

                $frame_nc3_add = "\n";
                $frame_nc3_add .= "[addition]\n";
                $frame_nc3_add .= "source_key = \"{$nc3_frame->id}-{$count}\"\n";
                $frame_ini .= $frame_nc3_add;
            }

            // エクスポート済みframe_id（重複したframe_idはカウントで使うため取り除かない）
            $this->exported_frame_ids[] = $nc3_frame->id;

            // フレーム設定ファイルの出力
            // メニューの場合は、移行完了したページデータを参照してインポートしたいので、insert 側に出力する。
            if ($nc3_frame->plugin_key == 'menus') {
                $this->storagePut($this->getImportPath('pages/', '@insert/') . $this->zeroSuppress($new_page_index) . "/frame_" . $frame_index_str . '.ini', $frame_ini);
            } else {
                $this->storagePut($this->getImportPath('pages/') . $this->zeroSuppress($new_page_index) . "/frame_" . $frame_index_str . '.ini', $frame_ini);
            }

            // ブロックのモジュールデータをエクスポート
            $this->nc3BlockExport($nc3_frame, $new_page_index, $frame_index_str);

            // Connect-CMS のプラグイン名の取得
            $plugin_name = $this->nc3GetPluginName($nc3_frame->plugin_key);
            if ($plugin_name == 'Development' || $plugin_name == 'Abolition' || $plugin_name == 'searchs') {
                // 移行できなかったモジュール
                $this->putError(3, "no migrate nc3-plugin", "プラグイン = " . $nc3_frame->plugin_key, $nc3_frame);
            }
        }
    }

    /**
     * NC3：NC3フレームの上書き
     */
    private function overrideNc3Frame(Nc3Frame $nc3_frame): Nc3Frame
    {
        // @nc3_override/frames/{frame_id}.ini があれば処理
        $nc3_override_frame_path = $this->migration_base . '@nc3_override/frames/' . $nc3_frame->id . '.ini';
        if (Storage::exists($nc3_override_frame_path)) {
            $nc3_override_frame = parse_ini_file(storage_path() . '/app/' . $nc3_override_frame_path, true);

            // ブロック属性（@nc3_override/frames の中の属性で上書き）
            if (array_key_exists('frame', $nc3_override_frame)) {
                foreach ($nc3_override_frame['frame'] as $column_name => $column_value) {
                    $nc3_frame->$column_name = $column_value;
                }
            }
        }
        return $nc3_frame;
    }

    /**
     * NC3：フレームに紐づくモジュールのメインデータのID 取得
     */
    private function nc3FrameMainDataId(Nc3Frame $nc3_frame): string
    {
        // 各プラグインテーブル（例：blogs）のlanguage_idは、データ作成時のlanguage_id。language_id = 1(英語)で表示してるページは日本語ページとかありえるため、
        // language_idで絞り込まない。

        $ret = "";
        if ($nc3_frame->plugin_key == 'blogs') {
            $nc3_blog = Nc3Blog::where('block_id', $nc3_frame->block_id)->first();
            // ブロックがあり、ブログがない場合は対象外
            if (!empty($nc3_blog)) {
                $ret = "blog_id = \"" . $this->zeroSuppress($nc3_blog->id) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'bbses') {
            $nc3_bbs = Nc3Bbs::where('block_id', $nc3_frame->block_id)->first();
            // ブロックがあり、掲示板がない場合は対象外
            if (!empty($nc3_bbs)) {
                $ret = "blog_id = \"bbs_" . $this->zeroSuppress($nc3_bbs->id) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'faqs') {
            $nc3_faq = Nc3Faq::where('block_id', $nc3_frame->block_id)->first();
            $ret = "faq_id = \"" . $this->zeroSuppress($nc3_faq->id) . "\"\n";
        } elseif ($nc3_frame->plugin_key == 'links') {
            // $nc3_link = Nc3Link::where('block_id', $nc3_frame->block_id)->where('language_id', $nc3_frame->language_id)->where('is_active', 1)->first();
            // リンクリストはNC2と違い、プラグイン固有のデータまとめテーブルがないため、ブロックテーブル参照
            $nc3_block = Nc3Block::find($nc3_frame->block_id);
            // ブロックがあり、リンクリストがない場合は対象外
            if (!empty($nc3_block)) {
                // NC3カウンターにプラグイン固有のデータまとめテーブルがないため、block_idをセット
                // [TODO] id名と値ズレ
                $ret = "linklist_id = \"" . $this->zeroSuppress($nc3_block->id) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'multidatabases') {
            $nc3_multidatabase = Nc3Multidatabase::where('block_id', $nc3_frame->block_id)->first();
            if (empty($nc3_multidatabase)) {
                $this->putError(3, "Nc2MultidatabaseBlock not found.", "block_id = " . $nc3_frame->block_id, $nc3_frame);
            } else {
                $ret = "database_id = \"" . $this->zeroSuppress($nc3_multidatabase->id) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'registrations') {
            $nc3_registration = Nc3Registration::where('block_id', $nc3_frame->block_id)->where('is_active', 1)->first();
            // ブロックがあり、登録フォームがない場合は対象外
            if (!empty($nc3_registration)) {
                $ret = "form_id = \"" . $this->zeroSuppress($nc3_registration->id) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'topics') {
            $nc3_topic_frame_setting = Nc3TopicFrameSetting::where('frame_key', $nc3_frame->key)->first();
            if (!empty($nc3_topic_frame_setting)) {
                // block_idはないため、frame_keyをセット
                // [TODO] id名と値ズレ
                $ret = "whatsnew_block_id = \"" . $this->zeroSuppress($nc3_topic_frame_setting->frame_key) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'cabinets') {
            $nc3_cabinet = Nc3Cabinet::where('block_id', $nc3_frame->block_id)->first();
            // ブロックがあり、キャビネットがない場合は対象外
            if (!empty($nc3_cabinet)) {
                $ret = "cabinet_id = \"" . $this->zeroSuppress($nc3_cabinet->id) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'menus') {
            // メニューの詳細設定（非表示設定が入っている）があれば、設定を加味する。
            // ルームの表示・非表示もmenu_frames_pagesに含まれてる
            $nc3_menu_frame_pages = Nc3MenuFramePage::select('menu_frames_pages.*', 'pages.sort_key')
                ->join('pages', 'pages.id', '=', 'menu_frames_pages.page_id')
                ->where("frame_key", $nc3_frame->key)
                ->orderBy('page_id', 'asc')
                ->get();
            if (empty($nc3_menu_frame_pages)) {
                $ret .= "\n";
                $ret .= "[menu]\n";
                $ret .= "select_flag       = \"0\"\n";
                $ret .= "folder_close_font = \"0\"\n";
                $ret .= "folder_open_font  = \"0\"\n";
                $ret .= "indent_font       = \"0\"\n";
            } else {
                // この時点では、ページはエクスポート途中のため、新との変換はできない。
                // そのため、旧データで対象外を記載しておき、import の際に変換する。

                // 選択しないページを除外
                $ommit_nc3_pages = array();
                foreach ($nc3_menu_frame_pages as $nc3_menu_frame_page) {
                    // 下層ページを含めて取得
                    $ommit_pages = Nc3Page::where('sort_key', 'like', $nc3_menu_frame_page->sort_key . '%')->get();
                    if ($ommit_pages->isNotEmpty()) {
                        $ommit_nc3_pages = $ommit_nc3_pages + $ommit_pages->pluck('id')->toArray();
                    }
                }
                $ret .= "\n";
                $ret .= "[menu]\n";
                $ret .= "select_flag        = \"1\"\n";
                $ret .= "folder_close_font  = \"0\"\n";
                $ret .= "folder_open_font   = \"0\"\n";
                $ret .= "indent_font        = \"0\"\n";
                if (!empty($ommit_nc3_pages)) {
                    asort($ommit_nc3_pages);
                    $ret .= "ommit_page_ids_nc3 = \"" . implode(",", $ommit_nc3_pages) . "\"\n";
                }
            }
        } elseif ($nc3_frame->plugin_key == 'access_counters') {
            $nc3_counter = Nc3AccessCounter::where('block_key', $nc3_frame->block_key)->first();
            // ブロックがあり、カウンターがない場合は対象外
            if (!empty($nc3_counter)) {
                // NC3カウンターにblock_idはないため、counter_idをセット
                // [TODO] id名と値ズレ
                $ret = "counter_block_id = \"" . $this->zeroSuppress($nc3_counter->id) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'calendars') {
            $nc3_calendar_frame_setting = Nc3CalendarFrameSetting::where('frame_key', $nc3_frame->key)->first();
            // 設定があり、カレンダーがない場合は対象外
            if (!empty($nc3_calendar_frame_setting)) {
                // block_idはないため、frame_keyをセット
                // [TODO] id名と値ズレ
                $ret = "calendar_block_id = \"" . $this->zeroSuppress($nc3_calendar_frame_setting->frame_key) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'reservations') {
            $nc3_reservation_frame_setting = Nc3ReservationFrameSetting::where('frame_key', $nc3_frame->key)->first();
            // ブロックがあり、施設予約がない場合は対象外
            if (!empty($nc3_reservation_frame_setting)) {
                // block_idはないため、frame_keyをセット
                // [TODO] id名と値ズレ
                $ret = "reservation_block_id = \"" . $this->zeroSuppress($nc3_reservation_frame_setting->frame_key) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'photo_albums') {
            $nc3_photoalbum = Nc3PhotoAlbum::where('block_id', $nc3_frame->block_id)->where('is_active', 1)->first();
            // ブロックがあり、フォトアルバムがない場合は対象外
            if (!empty($nc3_photoalbum)) {
                // フォトアルバムでスライド表示は、スライドプラグインに移行
                $nc3_photoalbum_frame_setting = Nc3PhotoAlbumFrameSetting::where('frame_key', $nc3_frame->key)
                    ->where('display_type', Nc3PhotoAlbumFrameSetting::DISPLAY_SLIDESHOW)
                    ->first();
                if ($nc3_photoalbum_frame_setting) {
                    $ret = "slideshows_block_id = \"" . $this->zeroSuppress($nc3_photoalbum->block_id) . "\"\n";
                } else {
                    $ret = "photoalbum_id = \"" . $this->zeroSuppress($nc3_photoalbum->id) . "\"\n";
                }
            }
        }
        return $ret;
    }

    /**
     * NC3：ページ内のブロックに配置されているモジュールのエクスポート。
     * モジュールごとのエクスポート処理に振り分け。
     */
    private function nc3BlockExport(Nc3Frame $nc3_frame, int $new_page_index, string $frame_index_str): void
    {
        // Connect-CMS のプラグイン名の取得
        $plugin_name = $this->nc3GetPluginName($nc3_frame->plugin_key);

        // モジュールごとに振り分け

        // プラグインで振り分け
        if ($plugin_name == 'contents') {
            // 固定記事（お知らせ）
            $this->nc3BlockExportContents($nc3_frame, $new_page_index, $frame_index_str);
        } elseif ($plugin_name == 'menus') {
            // メニュー
            // 今のところ、メニューの追加設定はなし。
        } elseif ($plugin_name == 'databases') {
            // データベース
            // [TODO] 未対応
            $this->nc3BlockExportDatabases($nc3_frame, $new_page_index, $frame_index_str);
        } elseif ($plugin_name == 'bbses') {
            // 掲示板
            // [TODO] 未対応
            $this->nc3BlockExportBbses($nc3_frame, $new_page_index, $frame_index_str);
        }
    }

    /**
     * NC3：固定記事（お知らせ）のエクスポート
     */
    private function nc3BlockExportContents(Nc3Frame $nc3_frame, int $new_page_index, string $frame_index_str): void
    {
        // お知らせモジュールのデータの取得
        // （NC3になって「続きを読む」機能なくなった。）
        $announcement = Nc3Announcement::where('block_id', $nc3_frame->block_id)->where('is_active', 1)->firstOrNew([]);

        // 記事

        // 「お知らせモジュール」のデータがなかった場合は、データの不整合としてエラーログを出力
        $content = "";
        if ($announcement->block_id) {
            $content = trim($announcement->content);
        } else {
            $this->putError(1, "no announcement record", "block_id = " . $nc3_frame->block_id);
        }

        // WYSIWYG 記事のエクスポート
        $save_folder = $this->getImportPath('pages/') . $this->zeroSuppress($new_page_index);
        $content_filename = "frame_" . $frame_index_str . '.html';
        $ini_filename = "frame_" . $frame_index_str . '.ini';

        $this->nc3Wysiwyg($nc3_frame, $save_folder, $content_filename, $ini_filename, $content, 'announcement');

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        // フレーム設定ファイルの追記
        $contents_ini = "[contents]\n";
        $contents_ini .= "contents_file   = \"" . $content_filename . "\"\n";
        $contents_ini .= "created_at      = \"" . $this->getCCDatetime($announcement->created) . "\"\n";
        $contents_ini .= "created_name    = \"" . $this->getNc3HandleFromNc3UserId($nc3_users, $announcement->created_user) . "\"\n";
        $contents_ini .= "insert_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $announcement->created_user) . "\"\n";
        $contents_ini .= "updated_at      = \"" . $this->getCCDatetime($announcement->modified) . "\"\n";
        $contents_ini .= "updated_name    = \"" . $this->getNc3HandleFromNc3UserId($nc3_users, $announcement->modified_user) . "\"\n";
        $contents_ini .= "update_login_id = \"" . $this->getNc3LoginIdFromNc3UserId($nc3_users, $announcement->modified_user) . "\"\n";
        $this->storageAppend($save_folder . "/" . $ini_filename, $contents_ini);
    }

    /**
     * NC3：汎用データベースのブロック特有部分のエクスポート
     */
    private function nc3BlockExportDatabases($nc3_frame, $new_page_index, $frame_index_str)
    {
        // NC3 ブロック設定の取得
        $nc3_multidatabase_block = Nc2MultidatabaseBlock::where('block_id', $nc3_frame->block_id)->first();
        if (empty($nc3_multidatabase_block)) {
            return;
        }

        $ini_filename = "frame_" . $frame_index_str . '.ini';

        $save_folder = $this->getImportPath('pages/') . $this->zeroSuppress($new_page_index);

        $frame_ini = "[database]\n";
        $frame_ini .= "use_search_flag = 1\n";
        $frame_ini .= "use_select_flag = 1\n";
        $frame_ini .= "use_sort_flag = \"\"\n";
        // デフォルトの表示順
        $default_sort_flag = '';
        if ($nc3_multidatabase_block->default_sort == 'seq') {
            $this->putError(3, 'データベースのソートが未対応順（カスタマイズ順）', "nc3_multidatabase_block = " . $nc3_multidatabase_block->block_id);
        } elseif ($nc3_multidatabase_block->default_sort == 'date') {
            $default_sort_flag = 'created_desc';
        } elseif ($nc3_multidatabase_block->default_sort == 'date_asc') {
            $default_sort_flag = 'created_asc';
        } else {
            $this->putError(3, 'データベースのソートが未対応順', "nc3_multidatabase_block = " . $nc3_multidatabase_block->block_id);
        }
        $frame_ini .= "default_sort_flag = \"" . $default_sort_flag . "\"\n";
        $frame_ini .= "view_count = "          . $nc3_multidatabase_block->visible_item . "\n";
        //Storage::append($save_folder . "/"     . $ini_filename, $frame_ini);
        $this->storageAppend($save_folder . "/"     . $ini_filename, $frame_ini);
    }

    /**
     * NC3：掲示板のブロック特有部分のエクスポート
     */
    private function nc3BlockExportBbses($nc3_frame, $new_page_index, $frame_index_str)
    {
        // NC3 ブロック設定の取得
        $nc3_bbs_block = Nc2BbsBlock::where('block_id', $nc3_frame->block_id)->first();
        if (empty($nc3_bbs_block)) {
            return;
        }

        $ini_filename = "frame_" . $frame_index_str . '.ini';

        $save_folder = $this->getImportPath('pages/') . $this->zeroSuppress($new_page_index);

        // 表示形式 変換
        // (nc) 0:スレッド,1:フラット
        // (cc) 0:フラット形式,1:スレッド形式
        // (key:nc3)expand => (value:cc)view_format
        $convert_view_formats = [
            0 => 1,
            1 => 0,
        ];
        if (isset($convert_view_formats[$nc3_bbs_block->expand])) {
            $view_format = $convert_view_formats[$nc3_bbs_block->expand];
        } else {
            $view_format = '';
            $this->putError(3, '掲示板の表示形式が未対応の形式', "nc3_bbs_block = " . $nc3_bbs_block->block_id);
        }

        $frame_ini = "[bbs]\n";
        $frame_ini .= "view_count = {$nc3_bbs_block->visible_row}\n";
        $frame_ini .= "view_format = {$view_format}\n";
        $this->storageAppend($save_folder . "/"     . $ini_filename, $frame_ini);
    }

    /**
     * コンテンツのクリーニング
     */
    private function cleaningContent($content, $nc3_module_name)
    {
        // 改行コードが含まれる場合があるので置換
        $content = str_replace(array("\r", "\n"), '', $content);

        $plugin_name = $this->nc3GetPluginName($nc3_module_name);

        // style から除去する属性の取得
        $clear_styles = $this->getMigrationConfig($plugin_name, 'export_clear_style');
        if (!$clear_styles) {
            return $content;
        }

        $pattern = "/style *= *(\".*?\"|'.*?')/i";
        $match_ret = preg_match_all($pattern, $content, $matches);
        // style が見つかれば、件数が返ってくる。
        if ($match_ret) {
            // [1] にstyle の中身のみ入ってくる。（style="background-color:rgb(255, 0, 0);" の "background-color:rgb(255, 0, 0);" の部分）
            foreach ($matches[1] as $match) {
                // セミコロンの位置
                $semicolon_pos = stripos($match, ';');
                // セミコロンがない場合は処理しない。
                if (!$semicolon_pos) {
                    continue;
                }

                // 属性項目名のみ抜き出し（background-color）
                // $property = substr($match, 1, stripos($match, ':') - 1);
                // $property = mb_strtolower($property);
                // if (in_array($property, $clear_styles)) {
                //     // 値を含めた属性全体の抜き出し（background-color:rgb(255, 0, 0);）
                //     $style_value = substr($match, 1, stripos($match, ';'));
                //     // 値の除去
                //     $content = str_replace($style_value, '', $content);
                // }

                // 1style複数属性に対応（;で分割, "background-color:rgb(255, 0, 0);" のダブルクォート除去）
                $attributes = explode(';', str_replace('"', '', $match));
                foreach ($attributes as $attribute) {

                    // 属性項目名のみ抜き出し（background-color）
                    $property = substr($attribute, 0, stripos($attribute, ':'));
                    $property = mb_strtolower($property);

                    if (in_array($property, $clear_styles)) {
                        // 値を含めた属性全体を除去（background-color:rgb(255, 0, 0);）
                        $content = str_replace($attribute . ';', '', $content);
                    }
                }
            }
        }

        // 不要な style="" があれば消す。
        $content = str_replace(' style=""', '', $content);

        // 不要な <span> のみで属性のないものがあれば消したいが、無効な<span> に対応する </span> のみ抜き出すのが難しく、
        // 今回は課題として残しておく。

        return $content;
    }

    /**
     * NC3：WYSIWYG の記事の保持
     */
    private function nc3Wysiwyg(?Nc3Frame $nc3_frame, ?string $save_folder, ?string $content_filename, ?string $ini_filename, ?string $content, ?string $nc3_module_name = null)
    {
        // [TODO] 未対応
        // nc3リンク切れチェック
        // $nc3_links = MigrationUtils::getContentHrefOrSrc($content);
        // if (is_array($nc3_links)) {
        //     foreach ($nc3_links as $nc3_link) {
        //         // $this->checkDeadLinkNc2($nc3_link, $nc3_module_name . '(wysiwyg)', $nc3_frame);
        //     }
        // }

        // コンテンツのクリーニング
        $content = $this->cleaningContent($content, $nc3_module_name);

        // 画像を探す
        $img_srcs = MigrationUtils::getContentImage($content);

        // 画像の中のcommon_download_main をエクスポートしたパスに変換する。
        $content = $this->nc3MigrationCommonDownloadMain($nc3_frame, $save_folder, $ini_filename, $content, $img_srcs, '[upload_images]');
        // [TODO] 未対応
        // cabinet_action_main_download をエクスポート形式に変換
        // [upload_images]に追記したいので、nc2MigrationCommonDownloadMainの直後に実行
        // $content = $this->nc3MigrationCabinetActionMainDownload($save_folder, $ini_filename, $content, 'src');

        // CSS の img-fluid を自動で付ける最小の画像幅
        $img_fluid_min_width = $this->getMigrationConfig('wysiwyg', 'img_fluid_min_width', 0);
        // 画像全体にレスポンシブCSS を適用する。
        $content = MigrationUtils::convertContentImageClassToImgFluid($content, $this->getImportPath(''), $img_fluid_min_width);

        // 画像のstyle設定を探し、height をmax-height に変換する。
        $content = MigrationUtils::convertContentImageHeightToMaxHeight($content);

        // Google Map 埋め込み時のスマホ用対応。widthを 100% に変更
        $content = MigrationUtils::convertContentIframeWidthTo100percent($content);

        // 添付ファイルを探す
        $anchors = MigrationUtils::getContentAnchor($content);
        // 添付ファイルの中のcommon_download_main をエクスポートしたパスに変換する。
        $content = $this->nc3MigrationCommonDownloadMain($nc3_frame, $save_folder, $ini_filename, $content, $anchors, '[upload_files]');
        // [TODO] 未対応
        // cabinet_action_main_download をエクスポート形式に変換
        // [upload_files]に追記したいので、nc2MigrationCommonDownloadMainの直後に実行
        // $content = $this->nc3MigrationCabinetActionMainDownload($save_folder, $ini_filename, $content, 'href');

        // [TODO] 未対応
        // ?page_id=XX置換
        // $content = $this->nc3MigrationPageIdToPermalink($content);

        // Google Analytics タグ部分を削除
        $content = MigrationUtils::deleteGATag($content);

        // HTML content の保存
        if ($save_folder) {
            $this->storagePut($save_folder . "/" . $content_filename, $content);
        }

        return $content;
    }

    /**
     * NC3：common_download_main をエクスポート形式に変換
     */
    private function nc3MigrationCommonDownloadMain(?Nc3Frame $nc3_frame, ?string $save_folder, ?string $ini_filename, ?string $content, $paths, string $section_name): ?string
    {
        if (empty($paths)) {
            return $content;
        }

        // 変換処理
        list($content, $export_paths) = $this->nc3MigrationCommonDownloadMainImple($content, $paths, $nc3_frame);

        // フレーム設定ファイルの追記
        $ini_text = $section_name . "\n";
        foreach ($export_paths as $export_key => $export_path) {
            $ini_text .= 'upload[' . $export_key . "] = \"" . $export_path . "\"\n";
        }

        // 記事ごとにini ファイルが必要な場合のみ出力する。
        if ($ini_filename) {
            $this->storageAppend($save_folder . "/" . $ini_filename, $ini_text);
        }

        // パスを変更した記事を返す。
        return $content;
    }

    /**
     * NC3：common_download_main をエクスポート形式に変換
     */
    private function nc3MigrationCommonDownloadMainImple(?string $content, array $paths, ?Nc3Frame $nc3_frame): array
    {
        // 修正したパスの配列
        $export_paths = array();

        foreach ($paths as $path) {
            // 画像URL例）
            // 　(標準サイズ) http://localhost/wysiwyg/image/download/1/172/big
            // 　(原寸大)     http://localhost/wysiwyg/image/download/1/174
            // ファイルURL例）
            // 　http://localhost/wysiwyg/file/download/1/173

            // common_download_main があれば、NC3 の画像として移行する。
            if (stripos($path, 'wysiwyg/image/download') !== false || stripos($path, 'wysiwyg/file/download') !== false) {
                // pathのみに置換
                $path_tmp = parse_url($path, PHP_URL_PATH);
                // 不要文字を取り除き
                $path_tmp = str_replace('/wysiwyg/image/download/', '', $path_tmp);
                $path_tmp = str_replace('/wysiwyg/file/download/', '', $path_tmp);
                // /で分割
                $src_params = explode('/', $path_tmp);

                // [TODO] image_size を参照していないため、今後見直しそう
                // $room_id = $src_params[0];
                $upload_id = $src_params[1];
                // $image_size = isset($src_params[2]) ? $src_params[2] : null;

                // フレーム設定ファイルの追記
                // 移行したアップロードファイルをini ファイルから探す
                if (Arr::has($this->uploads_ini, "uploads.upload.{$upload_id}")) {
                    // コンテンツ及び[upload_images] or [upload_files]セクション内のimg src or a href を作る。
                    $export_path = '../../uploads/' . $this->uploads_ini[$upload_id]['temp_file_name'];

                    // [upload_images] or [upload_files] 内の画像情報の追記
                    $export_paths[$upload_id] = $export_path;

                    // ファイルのパスの修正
                    // ファイル指定の前後の " も含めて置換
                    $content = str_replace('"' . $path . '"', '"' . $export_path . '"', $content);
                } else {
                    // 移行しなかったファイルのimg or a タグとしてログに記録
                    $this->putError(1, "no migrate img", "src = " . $path, $nc3_frame);
                }
            }
        }

        // パスを変更した記事を返す。
        return array($content, $export_paths);
    }

    /**
     * NC3：?page_id=XXをpermalinkに置換
     */
    private function nc3MigrationPageIdToPermalink($content, $links = true)
    {
        // wysiwygのパターン
        $pattern = '/\?page_id=(.*?)"/is';
        $endstring = '"';
        if (!$links) {
            // リンクリスト等のパターン
            $pattern = '/\?page_id=(.*?)$/is';
            $endstring = '';
        }
        if (preg_match_all($pattern, $content, $m)) {
            $replace_key_vals = [];
            $page_ids = $m[1];
            foreach ($page_ids as $page_id) {
                $nc2_page = Nc2Page::where('page_id', $page_id)->first();
                if ($nc2_page) {
                    $key = '?page_id='. $page_id. $endstring;
                    $replace_key_vals[$key] = $nc2_page["permalink"]. $endstring;
                }
            }
            $search = array_keys($replace_key_vals);
            $replace = array_values($replace_key_vals);
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }

    /**
     * NC3：cabinet_action_main_download をエクスポート形式に変換
     */
    private function nc3MigrationCabinetActionMainDownload($save_folder, $ini_filename, $content, $attr = 'href')
    {
        //?action=cabinet_action_main_download&block_id=778&room_id=1&cabinet_id=9&file_id=2020&upload_id=5688
        $pattern = '/'. $attr.'=".*?\?action=cabinet_action_main_download&.*?upload_id=([0-9]+)"/i';
        if (preg_match_all($pattern, $content, $cabinet_downloads)) {
            $cabinet_file_ids = $cabinet_downloads[1];
            $replace_key_vals = [];
            foreach ($cabinet_file_ids as $key => $file_id) {
                // 移行したアップロードファイルをini ファイルから探す
                if ($this->uploads_ini && array_key_exists('uploads', $this->uploads_ini) && array_key_exists($file_id, $this->uploads_ini['uploads']['upload'])) {
                    $path = '../../uploads/' . $this->uploads_ini[$file_id]['temp_file_name'];
                    $replace_href_pattern = '/'. $attr.'="(.*?\?action=cabinet_action_main_download&.*?upload_id='. $file_id.')"/i';
                    if (preg_match_all($replace_href_pattern, $content, $m)) {
                        $match_href = $m[1][0];
                        $replace_key_vals[$key] = [ 'upload_id' => $file_id,
                                                    'match_href' => $match_href,
                                                    'path' => $path,
                        ];
                    }
                }
            }
            // 既にnc2MigrationCommonDownloadMainでiniファイルに追記されているので、[upload_files]は空にする
            $ini_text = '';
            foreach ($replace_key_vals as $vals) {
                $content = str_replace($vals['match_href'], $vals['path'], $content);
                $ini_text .= 'upload[' . $vals['upload_id'] . "] = \"" . $vals['path'] . "\"\n";
            }
            if ($ini_filename) {
                $this->storageAppend($save_folder . "/" . $ini_filename, $ini_text);
            }
        }
        return $content;
    }
}
