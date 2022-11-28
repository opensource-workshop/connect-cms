<?php

namespace App\Traits\Migration;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use Symfony\Component\Yaml\Yaml;

use App\Models\Migration\MigrationMapping;

use App\Models\Migration\Nc3\Nc3AccessCounter;
use App\Models\Migration\Nc3\Nc3AccessCounterFrameSetting;
use App\Models\Migration\Nc3\Nc3Announcement;
use App\Models\Migration\Nc3\Nc3Box;
use App\Models\Migration\Nc3\Nc3Bbs;
use App\Models\Migration\Nc3\Nc3BbsArticle;
use App\Models\Migration\Nc3\Nc3BbsFrameSetting;
use App\Models\Migration\Nc3\Nc3Block;
use App\Models\Migration\Nc3\Nc3BlockRolePermission;
use App\Models\Migration\Nc3\Nc3BlockSetting;
use App\Models\Migration\Nc3\Nc3Blog;
use App\Models\Migration\Nc3\Nc3BlogEntry;
use App\Models\Migration\Nc3\Nc3Cabinet;
use App\Models\Migration\Nc3\Nc3CabinetFile;
use App\Models\Migration\Nc3\Nc3Calendar;
use App\Models\Migration\Nc3\Nc3CalendarEvent;
use App\Models\Migration\Nc3\Nc3CalendarFrameSetting;
use App\Models\Migration\Nc3\Nc3Category;
use App\Models\Migration\Nc3\Nc3Faq;
use App\Models\Migration\Nc3\Nc3FaqQuestion;
use App\Models\Migration\Nc3\Nc3Frame;
use App\Models\Migration\Nc3\Nc3Like;
use App\Models\Migration\Nc3\Nc3Link;
use App\Models\Migration\Nc3\Nc3LinkFrameSetting;
use App\Models\Migration\Nc3\Nc3Language;
use App\Models\Migration\Nc3\Nc3MailSetting;
use App\Models\Migration\Nc3\Nc3MenuFramePage;
use App\Models\Migration\Nc3\Nc3MenuFrameSetting;
use App\Models\Migration\Nc3\Nc3Multidatabase;
use App\Models\Migration\Nc3\Nc3MultidatabaseContent;
use App\Models\Migration\Nc3\Nc3MultidatabaseFrameSetting;
use App\Models\Migration\Nc3\Nc3MultidatabaseMetadata;
use App\Models\Migration\Nc3\Nc3TopicFramePlugin;
use App\Models\Migration\Nc3\Nc3TopicFrameSetting;
use App\Models\Migration\Nc3\Nc3Page;
use App\Models\Migration\Nc3\Nc3PageContainer;
use App\Models\Migration\Nc3\Nc3Plugin;
use App\Models\Migration\Nc3\Nc3Registration;
use App\Models\Migration\Nc3\Nc3RegistrationChoice;
use App\Models\Migration\Nc3\Nc3RegistrationPage;
use App\Models\Migration\Nc3\Nc3RegistrationQuestion;
use App\Models\Migration\Nc3\Nc3ReservationFrameSetting;
use App\Models\Migration\Nc3\Nc3Room;
use App\Models\Migration\Nc3\Nc3RoomRolePermission;
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
use App\Enums\BlogNoticeEmbeddedTag;
use App\Enums\CounterDesignType;
use App\Enums\ContentOpenType;
use App\Enums\DatabaseNoticeEmbeddedTag;
use App\Enums\DatabaseSortFlag;
use App\Enums\DayOfWeek;
use App\Enums\FaqSequenceConditionType;
use App\Enums\FormColumnType;
use App\Enums\LinklistType;
use App\Enums\NoticeEmbeddedTag;
use App\Enums\ReservationLimitedByRole;
use App\Enums\ReservationNoticeEmbeddedTag;
use App\Enums\StatusType;
use App\Enums\UserColumnType;
use App\Enums\UserStatus;

/**
 * NC3移行エクスポートプログラム
 */
trait MigrationNc3ExportTrait
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
        // 'photo_albums'     => 'photoalbums',  // フォトアルバム
        // 'reservations'     => 'reservations', // 施設予約
        // 'searches'         => 'searchs',      // 検索
        // 'videos'           => 'Development',  // 動画
        'access_counters'  => 'counters',       // カウンター
        'announcements'    => 'contents',       // お知らせ
        'bbses'            => 'bbses',          // 掲示板
        'blogs'            => 'blogs',          // ブログ
        'cabinets'         => 'cabinets',       // キャビネット
        'calendars'        => 'calendars',      // カレンダー
        'circular_notices' => 'Development',    // 回覧板
        'faqs'             => 'faqs',           // FAQ
        'iframes'          => 'Development',    // iFrame
        'links'            => 'linklists',      // リンクリスト
        'menus'            => 'menus',          // メニュー
        'multidatabases'   => 'databases',      // データベース
        'photo_albums'     => 'Development',    // フォトアルバム
        'questionnaires'   => 'Development',    // アンケート
        'quizzes'          => 'Development',    // 小テスト
        'registrations'    => 'forms',          // フォーム
        'reservations'     => 'Development',    // 施設予約
        'rss_readers'      => 'Development',    // RSS
        'searches'         => 'Development',    // 検索
        'tasks'            => 'Development',    // ToDo
        'topics'           => 'whatsnews',      // 新着情報
        'videos'           => 'Development',    // 動画
        'wysiwyg'          => 'Development',    // wysiwyg(upload用)
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
    private function getCCDatetime($utc_datetime): ?Carbon
    {
        if (empty($utc_datetime)) {
            return null;
        }
        if (is_string($utc_datetime)) {
            $utc_datetime = new Carbon($utc_datetime);
        }

        // 9時間足す
        return $utc_datetime->addHours(9);
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
        if ($language_id == Nc3Language::language_id_ja) {
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
     * NC3_EXPORT_UPLOADS_PATH=/path_to_nc3/app/Uploads/
     * NC3_APPLICATION_YML_PATH=/path_to_nc3/app/Config/application.yml
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

        // ルームデータのエクスポート
        if ($this->isTarget('nc3_export', 'groups')) {
            $this->nc3ExportRooms($redo);
        }

        // NC3 ブログ（blogs）データのエクスポート
        if ($this->isTarget('nc3_export', 'plugins', 'blogs')) {
            $this->nc3ExportBlog($redo);
        }

        // NC3 掲示板（bbses）データのエクスポート
        if ($this->isTarget('nc3_export', 'plugins', 'bbses')) {
            $this->nc3ExportBbs($redo);
        }

        // NC3 汎用データベース（multidatabases）データのエクスポート
        if ($this->isTarget('nc3_export', 'plugins', 'databases')) {
            $this->nc3ExportMultidatabase($redo);
        }

        // NC3 新着情報（topics）データのエクスポート
        if ($this->isTarget('nc3_export', 'plugins', 'whatsnews')) {
            $this->nc3ExportTopics($redo);
        }

        // NC3 キャビネット（cabinets）データのエクスポート
        if ($this->isTarget('nc3_export', 'plugins', 'cabinets')) {
            $this->nc3ExportCabinet($redo);
        }

        // NC3 登録フォーム（registrations）データのエクスポート
        if ($this->isTarget('nc3_export', 'plugins', 'forms')) {
            $this->nc3ExportRegistration($redo);
        }

        // NC3 FAQ（faqs）データのエクスポート
        if ($this->isTarget('nc3_export', 'plugins', 'faqs')) {
            $this->nc3ExportFaq($redo);
        }

        // NC3 リンクリスト（links）データのエクスポート
        if ($this->isTarget('nc3_export', 'plugins', 'linklists')) {
            $this->nc3ExportLinklist($redo);
        }

        // NC3 カウンター（access_counters）データのエクスポート
        if ($this->isTarget('nc3_export', 'plugins', 'counters')) {
            $this->nc3ExportCounter($redo);
        }

        // NC3 カレンダー（calendars）データのエクスポート
        if ($this->isTarget('nc3_export', 'plugins', 'calendars')) {
            $this->nc3ExportCalendar($redo);
        }

        //////////////////
        // [TODO] まだ
        //////////////////

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

        // pages データとファイルのエクスポート
        if ($this->isTarget('nc3_export', 'pages')) {
            // データクリア
            if ($redo === true) {
                MigrationMapping::where('target_source_table', 'source_pages')->delete();
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
                    // 「すべての会員をデフォルトで参加させる」 & 「すべての会員をデフォルトで参加させる」ルームはグループ作成しない
                    if ($nc3_sort_page->default_participation == 1 && !$this->getMigrationConfig('groups', 'nc3_export_make_group_of_default_entry_room')) {
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
                        $permanent_link = '/' . $nc3_sort_page->permalink;
                    } else {
                        $permanent_link = '/en/' . $nc3_sort_page->permalink;
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
                    $parent_page_mapping = MigrationMapping::where('target_source_table', 'source_pages')->where('source_key', $nc3_sort_page->parent_id)->first();
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
                    ['target_source_table' => 'source_pages', 'source_key' => $nc3_sort_page->id],
                    ['target_source_table' => 'source_pages',
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
            $source_page = MigrationMapping::where('target_source_table', 'source_pages')->where('source_key', $source_page_id)->first();
            $destination_page = MigrationMapping::where('target_source_table', 'source_pages')->where('source_key', $destination_page_id)->first();

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
     * NC3プラグインキーからConnect-CMS のプラグイン名に変換
     */
    private function getCCPluginNamesFromNc3PluginKeys($plugin_keys)
    {
        $available_connect_plugin_names = ['blogs', 'bbses', 'databases'];
        $ret = array();
        foreach ($plugin_keys as $plugin_key) {
            // Connect-CMS のプラグイン名に変換
            if (array_key_exists($plugin_key, $this->plugin_name)) {
                $connect_plugin_name = $this->plugin_name[$plugin_key];
                if ($connect_plugin_name == 'Development') {
                    $this->putError(3, '新着：未開発プラグイン', "plugin_key = {$plugin_key}");
                } elseif (in_array($connect_plugin_name, $available_connect_plugin_names)) {
                    $ret[] = $connect_plugin_name;
                } else {
                    $this->putError(3, '新着：未対応プラグイン', "plugin_key = {$plugin_key}");
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

        $site_settings = Nc3SiteSetting::where('language_id', Nc3Language::language_id_ja)->get();

        // site,ini ファイル編集
        $basic_ini = "[basic]\n";

        // サイト名
        $basic_ini .= "base_site_name = \"" . Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'App.site_name') . "\"\n";

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
        $nc3_uploads = Nc3UploadFile::select('upload_files.*', 'rooms.page_id_top as room_page_id_top')
            ->leftJoin('rooms', function ($join) {
                $join->on('rooms.id', 'upload_files.room_id');
            })
            ->orderBy('upload_files.id')
            ->get();

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
            $uploads_ini_detail .= "room_page_id_top = " . $nc3_upload->room_page_id_top . "\n";
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
        $nc3_users_languages = Nc3UsersLanguage::where('language_id', Nc3Language::language_id_ja)->whereIn('user_id', $nc3_users->pluck('id'))->get();

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
            $users_ini .= "created_at         = \"" . $this->getCCDatetime($nc3_user->created) . "\"\n";
            $users_ini .= "updated_at         = \"" . $this->getCCDatetime($nc3_user->modified) . "\"\n";
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
                    $item_value = str_replace('"', '\"', $item_value);
                    $users_ini .= "{$item_name}            = \"{$item_value}\"\n";
                }
            }

            if ($nc3_user->role_key == Nc3User::role_system_administrator) {
                $users_ini .= "users_roles_manage = \"admin_system\"\n";
                $users_ini .= "users_roles_base   = \"role_article_admin\"\n";
            } elseif ($nc3_user->role_key == Nc3User::role_administrator) {
                $users_ini .= "users_roles_manage = \"admin_site|admin_page|admin_user\"\n";
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

        ※ user["ユーザID"] = "room_role_key"
        */

        // NC3 ルームの取得. ルーム名はConnectが多言語してないので日本語固定で取る
        $nc3_rooms_query = Nc3Room::select('rooms.*', 'rooms_languages.name as room_name')
            ->whereIn('space_id', [Nc3Space::COMMUNITY_SPACE_ID, Nc3Space::PUBLIC_SPACE_ID])
            ->join('rooms_languages', function ($join) {
                $join->on('rooms_languages.room_id', 'rooms.id')
                    ->where('rooms_languages.language_id', Nc3Language::language_id_ja);
            });

        // 対象外ページ指定の有無
        // if ($this->getMigrationConfig('pages', 'nc3_export_ommit_page_ids')) {
        //     $nc3_rooms_query->whereNotIn('page_id', $this->getMigrationConfig('pages', 'nc3_export_ommit_page_ids'));
        // }
        $nc3_rooms = $nc3_rooms_query->orderBy('rooms.sort_key')->get();

        // 空なら戻る
        if ($nc3_rooms->isEmpty()) {
            return;
        }

        // [NC3]
        // ルーム管理＞（タブ）パブリック＞各ルームの[編集]ボタン
        //  「ルーム内の役割」のデフォルト値: ゲスト:visitor, 一般:general_user（デフォ）,編集者:editor
        //   ※ パブリックは各ルームで、「ルーム内の役割」のデフォルト値を設定できる。
        // ルーム管理＞（タブ）コミュニティ＞[編集]ボタン
        //  「ルーム内の役割」のデフォルト値: ゲスト:visitor, 一般:general_user（デフォ）,編集者:editor
        //   ※ コミュニティはサイトで１個の「ルーム内の役割」のデフォルト値のみ。各ルームで設定できない。

        // グループをループ
        foreach ($nc3_rooms as $nc3_room) {
            if ($nc3_room->space_id == Nc3Space::COMMUNITY_SPACE_ID && $nc3_room->default_participation == 1) {
                if ($this->getMigrationConfig('groups', 'nc3_export_make_group_of_default_entry_room')) {
                    // 「すべての会員をデフォルトで参加させる」ルームをグループ作成する
                    $this->putMonitor(3, '「すべての会員をデフォルトで参加させる」ルームをグループ作成する', "ルーム名={$nc3_room->room_name}");
                } else {
                    //「すべての会員をデフォルトで参加させる」ルームはグループ作成しない
                    $this->putMonitor(3, '「すべての会員をデフォルトで参加させる」ルームはグループ作成しない', "ルーム名={$nc3_room->room_name}");
                    continue;
                }
            }

            //                           (public)role_key,           (コミュ)role_key
            // _ルーム管理者              = room_administrator,       room_administrator
            // _編集長                   = chief_editor,              chief_editor
            // _編集者                   = editor,                    editor
            // _一般                     = general_user,              general_user
            // _ゲスト                   = visitor,                   visitor
            // 不参加(デフォルトで参加OFF) = 選択肢なし,                null

            $nc3_roles_rooms_users = Nc3User::select('roles_rooms_users.*', 'users.username as login_id', 'roles_rooms.role_key as room_role_key')
                ->leftJoin('roles_rooms_users', function ($join) use ($nc3_room) {
                    $join->on('roles_rooms_users.user_id', 'users.id')
                        ->where('roles_rooms_users.room_id', $nc3_room->id);
                })
                ->leftJoin('roles_rooms', function ($join) {
                    $join->on('roles_rooms.id', 'roles_rooms_users.roles_room_id');
                })
                ->where('users.is_deleted', 0)
                ->orderBy('roles_rooms_users.room_id')
                ->orderBy('roles_rooms.role_key')
                ->orderBy('users.created')
                ->get();

            $role_keys = [
                1 => ['nc3_role_key' => Nc3Room::role_key_room_administrator, 'cc_name' =>'_コンテンツ管理者', 'cc_role_name' =>'role_article_admin'],
                2 => ['nc3_role_key' => Nc3Room::role_key_chief_editor,       'cc_name' =>'_コンテンツ管理者', 'cc_role_name' =>'role_article_admin'],
                3 => ['nc3_role_key' => Nc3Room::role_key_editor,             'cc_name' =>'_モデレータ',      'cc_role_name' =>'role_article'],
                4 => ['nc3_role_key' => Nc3Room::role_key_general_user,       'cc_name' =>'_編集者',          'cc_role_name' =>'role_reporter'],
                5 => ['nc3_role_key' => Nc3Room::role_key_visitor,            'cc_name' =>'_ゲスト',          'cc_role_name' =>'role_guest'],
            ];

            foreach ($role_keys as $no => $names) {

                $nc3_roles_rooms_users_subgroup = $nc3_roles_rooms_users->where('room_role_key', $names['nc3_role_key']);
                if ($nc3_roles_rooms_users_subgroup->isEmpty()) {
                    // ユーザいないグループは作らない。
                    continue;
                }

                // ini ファイル用変数
                $groups_ini  = "[group_base]\n";
                $groups_ini .= "name = \"" . $nc3_room->room_name . $names['cc_name'] . "\"\n";
                $groups_ini .= "role_name = \"" . $names['cc_role_name'] . "\"\n";
                $groups_ini .= "\n";
                $groups_ini .= "[source_info]\n";
                $groups_ini .= "room_id = " . $nc3_room->id . "\n";
                $groups_ini .= "room_page_id_top = " . $nc3_room->page_id_top . "\n";
                $groups_ini .= "\n";
                $groups_ini .= "[users]\n";

                foreach ($nc3_roles_rooms_users_subgroup as $nc3_roles_rooms_user) {
                    $groups_ini .= "user[\"" . $nc3_roles_rooms_user->login_id . "\"] = " . $nc3_roles_rooms_user->room_role_key . "\n";
                }

                // グループデータの出力
                $this->storagePut($this->getImportPath('groups/group_') . $this->zeroSuppress($nc3_room->id) . '_' . $no . '.ini', $groups_ini);
            }
        }
    }

    /**
     * NC3：ブログ（Blog）の移行
     */
    private function nc3ExportBlog($redo)
    {
        $this->putMonitor(3, "Start nc3ExportBlog.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('blogs/'));
        }

        // NC3ブログを移行する。
        $nc3_blogs = Nc3Blog::select('blogs.*', 'blocks.key as block_key', 'blocks.room_id', 'rooms.space_id')
            ->join('blocks', function ($join) {
                $join->on('blocks.id', '=', 'blogs.block_id')
                    ->where('blocks.plugin_key', 'blogs');
            })
            ->join('rooms', function ($join) {
                $join->on('rooms.id', '=', 'blocks.room_id')
                    ->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID]);
            })
            ->orderBy('blogs.id')
            ->get();

        // 空なら戻る
        if ($nc3_blogs->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        // 記事を投稿できる権限, メール通知を受け取る権限
        $block_role_permissions = Nc3BlockRolePermission::getBlockRolePermissionsByBlockKeys($nc3_blogs->pluck('block_key'));

        // メール設定
        $mail_settings = Nc3MailSetting::getMailSettingsByBlockKeys($nc3_blogs->pluck('block_key'), 'blogs');

        // サイト設定
        $site_settings = Nc3SiteSetting::where('language_id', Nc3Language::language_id_ja)->get();

        // カテゴリ
        $categories = Nc3Category::getCategoriesByBlockIds($nc3_blogs->pluck('block_id'));

        // ブロック設定
        $block_settings = Nc3BlockSetting::whereIn('block_key', $nc3_blogs->pluck('block_key'))->get();

        // 使用言語（日本語・英語）で有効な言語を取得
        $language_ids = Nc3Language::where('is_active', 1)->pluck('id');

        // いいね
        $likes = Nc3Like::whereIn('block_key', $nc3_blogs->pluck('block_key'))->get();

        // NC3ブログのループ
        foreach ($nc3_blogs as $nc3_blog) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_blog->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // (nc3)投稿権限は１件のみ 投稿権限, 一般
            $post_permission_general_user = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $nc3_blog->block_key, $nc3_blog->room_id, 'content_creatable', 'general_user');

            // 権限設定
            // ----------------------------------------------------
            // ※ユーザ (nc3)一般 => (cc)編集者

            $article_post_flag = 1;     // 投稿権限はnc3編集者まで常時チェックON
            $reporter_post_flag = 0;

            // 一般まで
            if ($post_permission_general_user) {
                $reporter_post_flag = 1;
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

            // (nc3)メール通知を受け取る権限
            // ゲスト
            $mail_permission_visitor = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $nc3_blog->block_key, $nc3_blog->room_id, 'mail_content_receivable', 'visitor');
            // 一般
            $mail_permission_general_user = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $nc3_blog->block_key, $nc3_blog->room_id, 'mail_content_receivable', 'general_user');
            // 編集者
            $mail_permission_editor = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $nc3_blog->block_key, $nc3_blog->room_id, 'mail_content_receivable', 'editor');

            $notice_everyone = 0;
            $notice_admin_group = 0;
            $notice_moderator_group = 0;
            $notice_group = 0;
            $notice_public_general_group = 0;
            $notice_public_moderator_group = 0;

            // ゲストまで
            if ($mail_permission_visitor) {
                if ($nc3_blog->space_id == Nc3Space::PUBLIC_SPACE_ID) {
                    // 全ユーザ通知
                    $notice_everyone = 1;

                } elseif ($nc3_blog->space_id == Nc3Space::COMMUNITY_SPACE_ID) {
                    // グループ通知
                    $notice_group = 1;
                }

            // 一般まで
            } elseif ($mail_permission_general_user) {
                if ($nc3_blog->space_id == Nc3Space::PUBLIC_SPACE_ID) {
                    // パブリック一般通知
                    $notice_public_general_group = 1;
                    $notice_admin_group = 1;

                } elseif ($nc3_blog->space_id == Nc3Space::COMMUNITY_SPACE_ID) {
                    // グループ通知
                    $notice_group = 1;
                }

            // 編集者まで
            } elseif ($mail_permission_editor) {
                if ($nc3_blog->space_id == Nc3Space::PUBLIC_SPACE_ID) {
                    // パブリックモデレーター通知
                    $notice_public_moderator_group = 1;
                    $notice_admin_group = 1;
                } elseif ($nc3_blog->space_id == Nc3Space::COMMUNITY_SPACE_ID) {
                    // モデレータユーザ通知
                    $notice_moderator_group = 1;
                    $notice_admin_group = 1;
                }

            // 編集者OFF=編集長までON
            } elseif ($mail_permission_editor == 0) {
                // 管理者グループ通知
                $notice_admin_group = 1;
            }

            // 通知メール（データなければblock_key=nullの初期設定取得）
            $mail_setting = $mail_settings->firstWhere('block_key', $nc3_blog->block_key) ?? $mail_settings->firstWhere('block_key', null);
            $mail_subject = $mail_setting->mail_fixed_phrase_subject;
            $mail_body = $mail_setting->mail_fixed_phrase_body;

            // 承認メール
            $approval_subject = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_mail_subject');
            $approval_body = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_mail_body');

            // 承認完了メール
            $approved_subject = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_completion_mail_subject');
            $approved_body = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_completion_mail_body');

            // --- メール配信設定
            // [{X-SITE_NAME}-{X-PLUGIN_NAME}]{X-SUBJECT}({X-ROOM} {X-BLOCK_NAME})
            //
            // {X-PLUGIN_NAME}にコンテンツが投稿されたのでお知らせします。
            // ルーム名:{X-ROOM}
            // ブロック名:{X-BLOCK_NAME}
            // タイトル:{X-SUBJECT}
            // 投稿者:{X-USER}
            // 投稿日時:{X-TO_DATE}
            //
            // {X-BODY}
            //
            // この投稿内容を確認するには下記のリンクをクリックしてください。
            // {X-URL}

            // ※ {X-BODY}は「続きを読む」内容は含んでいなかった。

            // --- 承認申請メール
            // (承認依頼){X-PLUGIN_MAIL_SUBJECT}
            //
            // {X-USER}さんから{X-PLUGIN_NAME}の承認依頼があったことをお知らせします。
            //
            // {X-WORKFLOW_COMMENT}
            //
            //
            // {X-PLUGIN_MAIL_BODY}

            // --- 承認完了メール
            // (承認完了){X-PLUGIN_MAIL_SUBJECT}
            //
            // {X-USER}さんの{X-PLUGIN_NAME}の承認が完了されたことをお知らせします。
            // もし{X-USER}さんの{X-PLUGIN_NAME}に覚えがない場合はこのメールを破棄してください。
            //
            // {X-WORKFLOW_COMMENT}
            //
            //
            // {X-PLUGIN_MAIL_BODY}

            // 変換
            $convert_embedded_tags = [
                // nc3埋込タグ, cc埋込タグ
                ['{X-PLUGIN_MAIL_SUBJECT}', $mail_subject],
                ['{X-PLUGIN_MAIL_BODY}', $mail_body],
                ['{X-SITE_NAME}', '[[' . NoticeEmbeddedTag::site_name . ']]'],
                ['{X-SUBJECT}',   '[[' . NoticeEmbeddedTag::title . ']]'],
                ['{X-USER}',      '[[' . NoticeEmbeddedTag::created_name . ']]'],
                ['{X-TO_DATE}',   '[[' . NoticeEmbeddedTag::created_at . ']]'],
                ['{X-BODY}',      '[[' . NoticeEmbeddedTag::body . ']]'],
                ['{X-URL}',       '[[' . NoticeEmbeddedTag::url . ']]'],
                ['{X-TAGS}',      '[[' . BlogNoticeEmbeddedTag::tag . ']]'],
                ['{X-PLUGIN_NAME}', 'ブログ'],
                // 除外
                ['({X-ROOM} {X-BLOCK_NAME})', ''],
                ['ブロック名:{X-BLOCK_NAME}', ''],
                ['ルーム名:{X-ROOM}', ''],
                ['{X-BLOCK_NAME}', ''],
                ['{X-ROOM}', ''],
                ['{X-WORKFLOW_COMMENT}', ''],
            ];
            foreach ($convert_embedded_tags as $convert_embedded_tag) {
                $mail_subject =     str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_subject);
                $mail_body =        str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_body);
                $approval_subject = str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $approval_subject);
                $approval_body =    str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $approval_body);
                $approved_subject = str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $approved_subject);
                $approved_body =    str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $approved_body);
            }

            // （NC3）承認メールは、承認必要＋承認メール通知ONで、承認権限（～編集者まで設定可）に飛ぶ。
            // （NC3）承認完了メールは、サイトセッティングにあるけどメール飛ばなかった。
            //        承認完了時、メール通知ON（～ゲストまで通知）でメール通知フォーマットでメール飛ぶ。
            //        ⇒ （CC）NC3承認完了通知フォーマットを、CC承認完了通知フォーマットにセット。けど通知しない。

            // 記事承認（content_publishable）はルーム管理者・編集長固定. 編集者は承認必要

            $use_workflow = Nc3BlockSetting::getNc3BlockSettingValue($block_settings, $nc3_blog->block_key, 'use_workflow');

            // ブログ設定
            $journals_ini = "";
            $journals_ini .= "[blog_base]\n";
            $journals_ini .= "blog_name = \"" . $nc3_blog->name . "\"\n";
            $journals_ini .= "view_count = 10\n";
            $journals_ini .= "use_like = " . Nc3BlockSetting::getNc3BlockSettingValue($block_settings, $nc3_blog->block_key, 'use_like') . "\n";
            $journals_ini .= "article_post_flag = " . $article_post_flag . "\n";
            $journals_ini .= "article_approval_flag = 0\n";                                 // 編集長=モデは承認不要
            $journals_ini .= "reporter_post_flag = " . $reporter_post_flag . "\n";
            $journals_ini .= "reporter_approval_flag = " . $use_workflow . "\n";            // 承認ありなら編集者承認ON
            $journals_ini .= "notice_on = " . $mail_setting->is_mail_send . "\n";
            $journals_ini .= "notice_everyone = " . $notice_everyone . "\n";
            $journals_ini .= "notice_group = " . $notice_group . "\n";
            $journals_ini .= "notice_moderator_group = " . $notice_moderator_group . "\n";
            $journals_ini .= "notice_admin_group = " . $notice_admin_group . "\n";
            $journals_ini .= "notice_public_general_group = " . $notice_public_general_group . "\n";
            $journals_ini .= "notice_public_moderator_group = " . $notice_public_moderator_group . "\n";
            $journals_ini .= "mail_subject = \"" . $mail_subject . "\"\n";
            $journals_ini .= "mail_body = \"" . $mail_body . "\"\n";
            $journals_ini .= "approval_on = " . $mail_setting->is_mail_send_approval . "\n";
            $journals_ini .= "approval_admin_group = " . $use_workflow . "\n";              // 1:「管理者グループ」通知
            $journals_ini .= "approval_subject = \"" . $approval_subject . "\"\n";
            $journals_ini .= "approval_body = \"" . $approval_body . "\"\n";
            $journals_ini .= "approved_on = 0\n";                                           // 承認完了通知はメール飛ばなかった
            $journals_ini .= "approved_author = 0\n";                                       // 1:投稿者へ通知する
            $journals_ini .= "approved_admin_group = 0\n";                                  // 1:「管理者グループ」通知
            $journals_ini .= "approved_subject = \"" . $approved_subject . "\"\n";
            $journals_ini .= "approved_body = \"" . $approved_body . "\"\n";

            // NC3 情報
            $journals_ini .= "\n";
            $journals_ini .= "[source_info]\n";
            $journals_ini .= "journal_id = " . $nc3_blog->id . "\n";
            $journals_ini .= "room_id = " . $nc3_blog->room_id . "\n";
            $journals_ini .= "plugin_key = \"blogs\"\n";
            $journals_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_blog->created) . "\"\n";
            $journals_ini .= "created_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_blog->created_user) . "\"\n";
            $journals_ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_blog->created_user) . "\"\n";
            $journals_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_blog->modified) . "\"\n";
            $journals_ini .= "updated_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_blog->modified_user) . "\"\n";
            $journals_ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_blog->modified_user) . "\"\n";

            // NC3日誌で使ってるカテゴリのみ移行する。
            $journals_ini .= "\n";
            $journals_ini .= "[categories]\n";
            $blog_categories = $categories->where('block_id', $nc3_blog->block_id);

            $journals_ini_originals = "";

            foreach ($blog_categories as $blog_category) {
                $journals_ini_originals .= "original_categories[" . $blog_category->id . "] = \"" . $blog_category->name . "\"\n";
            }
            if (!empty($journals_ini_originals)) {
                $journals_ini .= $journals_ini_originals;
            }

            // NC3日誌の記事を移行する。
            $nc3_blog_posts = Nc3BlogEntry::where('block_id', $nc3_blog->block_id)
                ->where('is_latest', 1)
                ->whereIn('language_id', $language_ids)
                ->orderBy('id')
                ->get();

            // 日誌の記事はTSV でエクスポート
            $journals_tsv = "";

            // NC3日誌の記事をループ
            $journals_ini .= "\n";
            $journals_ini .= "[blog_post]\n";
            foreach ($nc3_blog_posts as $nc3_blog_post) {
                // TSV 形式でエクスポート
                if (!empty($journals_tsv)) {
                    $journals_tsv .= "\n";
                }

                $content       = $this->nc3Wysiwyg(null, null, null, null, $nc3_blog_post->body1, 'blogs');
                $more_content  = $this->nc3Wysiwyg(null, null, null, null, $nc3_blog_post->body2, 'blogs');

                $blog_category = $blog_categories->firstWhere('id', $nc3_blog_post->category_id) ?? new Nc3Category();

                $like = $likes->firstWhere('content_key', $nc3_blog_post->key) ?? new Nc3Like();

                $journals_tsv .= $this->getCCDatetime($nc3_blog_post->publish_start)            . "\t"; // [0] 投稿日時
                $journals_tsv .= $blog_category->name                                           . "\t";
                $journals_tsv .= $this->convertCCStatusFromNc3Status($nc3_blog_post->status)    . "\t"; // [2] ccステータス
                $journals_tsv .= '0'                                                            . "\t"; // [3] agree 使ってない
                $journals_tsv .= str_replace("\t", '', $nc3_blog_post->title)                   . "\t";
                $journals_tsv .= $content                                                       . "\t";
                $journals_tsv .= $more_content                                                  . "\t";
                $journals_tsv .=                                                                  "\t"; // [7] 続きを読む
                $journals_tsv .=                                                                  "\t"; // [8] 閉じる
                $journals_tsv .= $like->like_count                                              . "\t"; // [9] いいね数
                $journals_tsv .=                                                                  "\t"; // [10]いいねのsession_id & user_id. nc3ない
                $journals_tsv .= $this->getCCDatetime($nc3_blog_post->created)                                  . "\t";   // [11]
                $journals_tsv .= Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_blog_post->created_user)   . "\t";   // [12]
                $journals_tsv .= Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_blog_post->created_user)  . "\t";   // [13]
                $journals_tsv .= $this->getCCDatetime($nc3_blog_post->modified)                                 . "\t";   // [14]
                $journals_tsv .= Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_blog_post->modified_user)  . "\t";   // [15]
                $journals_tsv .= Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_blog_post->modified_user);          // [16]

                // 記事のタイトルの一覧
                // タイトルに " あり
                if (strpos($nc3_blog_post->title, '"')) {
                    // ログ出力
                    $this->putError(1, 'Blog title in double-quotation', "タイトル = " . $nc3_blog_post->title);
                }
                $journals_ini .= "post_title[" . $nc3_blog_post->id . "] = \"" . str_replace('"', '', $nc3_blog_post->title) . "\"\n";
            }

            // blog の設定
            $this->storagePut($this->getImportPath('blogs/blog_') . $this->zeroSuppress($nc3_blog->id) . '.ini', $journals_ini);

            // blog の記事
            $journals_tsv = $this->exportStrReplace($journals_tsv, 'blogs');
            $this->storagePut($this->getImportPath('blogs/blog_') . $this->zeroSuppress($nc3_blog->id) . '.tsv', $journals_tsv);
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
        $nc3_bbses = Nc3Bbs::select('bbses.*', 'blocks.key as block_key', 'blocks.room_id', 'rooms.space_id')
            ->join('blocks', function ($join) {
                $join->on('blocks.id', '=', 'bbses.block_id')
                    ->where('blocks.plugin_key', 'bbses');
            })
            ->join('rooms', function ($join) {
                $join->on('rooms.id', '=', 'blocks.room_id')
                    ->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID]);
            })
            ->orderBy('bbses.id')
            ->get();

        // 空なら戻る
        if ($nc3_bbses->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        // 記事を投稿できる権限, メール通知を受け取る権限
        $block_role_permissions = Nc3BlockRolePermission::getBlockRolePermissionsByBlockKeys($nc3_bbses->pluck('block_key'));

        // メール設定
        $mail_settings = Nc3MailSetting::getMailSettingsByBlockKeys($nc3_bbses->pluck('block_key'), 'bbses');

        // サイト設定
        $site_settings = Nc3SiteSetting::where('language_id', Nc3Language::language_id_ja)->get();

        // ブロック設定
        $block_settings = Nc3BlockSetting::whereIn('block_key', $nc3_bbses->pluck('block_key'))->get();

        // 使用言語（日本語・英語）で有効な言語を取得
        $language_ids = Nc3Language::where('is_active', 1)->pluck('id');

        // いいね
        $likes = Nc3Like::whereIn('block_key', $nc3_bbses->pluck('block_key'))->get();

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

            // 権限設定
            // ----------------------------------------------------
            // ※ユーザ (nc3)一般 => (cc)編集者
            // 記事を投稿できる権限（content_creatable）=根記事の投稿OK
            // コメントを投稿できる権限（content_comment_creatable）=根記事への返信OK

            // (nc3)投稿権限 １件のみ=投稿権限, 一般
            $post_permission_general_user = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $nc3_bbs->block_key, $nc3_bbs->room_id, 'content_creatable', 'general_user');
            // (nc3)返信権限 3件あるけど一般までのみ取得（他2件:1-ゲストまでの場合、一般もONのため、取得不要。2-編集長までの場合、編集長は投稿権限が常にONのため、CCでは投稿可と判断して返信権限の判定不要）
            $reply_permission_general_user = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $nc3_bbs->block_key, $nc3_bbs->room_id, 'content_comment_creatable', 'general_user');

            $article_post_flag = 1;     // 投稿権限はnc3編集者まで常時チェックON
            $reporter_post_flag = 0;

            // 投稿権限: 一般まで
            if ($post_permission_general_user) {
                $reporter_post_flag = 1;

            // 返信権限: 一般まで
            } elseif ($reply_permission_general_user) {
                $reporter_post_flag = 1;
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

            // (nc3)メール通知を受け取る権限
            // ゲスト
            $mail_permission_visitor = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $nc3_bbs->block_key, $nc3_bbs->room_id, 'mail_content_receivable', 'visitor');
            // 一般
            $mail_permission_general_user = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $nc3_bbs->block_key, $nc3_bbs->room_id, 'mail_content_receivable', 'general_user');
            // 編集者
            $mail_permission_editor = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $nc3_bbs->block_key, $nc3_bbs->room_id, 'mail_content_receivable', 'editor');

            $notice_everyone = 0;
            $notice_admin_group = 0;
            $notice_moderator_group = 0;
            $notice_group = 0;
            $notice_public_general_group = 0;
            $notice_public_moderator_group = 0;

            // ゲストまで
            if ($mail_permission_visitor) {
                if ($nc3_bbs->space_id == Nc3Space::PUBLIC_SPACE_ID) {
                    // 全ユーザ通知
                    $notice_everyone = 1;

                } elseif ($nc3_bbs->space_id == Nc3Space::COMMUNITY_SPACE_ID) {
                    // グループ通知
                    $notice_group = 1;
                }

            // 一般まで
            } elseif ($mail_permission_general_user) {
                if ($nc3_bbs->space_id == Nc3Space::PUBLIC_SPACE_ID) {
                    // パブリック一般通知
                    $notice_public_general_group = 1;
                    $notice_admin_group = 1;

                } elseif ($nc3_bbs->space_id == Nc3Space::COMMUNITY_SPACE_ID) {
                    // グループ通知
                    $notice_group = 1;
                }

            // 編集者まで
            } elseif ($mail_permission_editor) {
                if ($nc3_bbs->space_id == Nc3Space::PUBLIC_SPACE_ID) {
                    // パブリックモデレーター通知
                    $notice_public_moderator_group = 1;
                    $notice_admin_group = 1;
                } elseif ($nc3_bbs->space_id == Nc3Space::COMMUNITY_SPACE_ID) {
                    // モデレータユーザ通知
                    $notice_moderator_group = 1;
                    $notice_admin_group = 1;
                }

            // 編集者OFF=編集長までON
            } elseif ($mail_permission_editor == 0) {
                // 管理者グループ通知
                $notice_admin_group = 1;
            }

            // 通知メール（データなければblock_key=nullの初期設定取得）
            $mail_setting = $mail_settings->firstWhere('block_key', $nc3_bbs->block_key) ?? $mail_settings->firstWhere('block_key', null);
            $mail_subject = $mail_setting->mail_fixed_phrase_subject;
            $mail_body = $mail_setting->mail_fixed_phrase_body;

            // 承認メール
            $approval_subject = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_mail_subject');
            $approval_body = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_mail_body');

            // 承認完了メール
            $approved_subject = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_completion_mail_subject');
            $approved_body = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_completion_mail_body');

            // --- メール配信設定
            // [{X-SITE_NAME}-{X-PLUGIN_NAME}]{X-SUBJECT}({X-ROOM} {X-BBS_NAME})
            //
            // {X-PLUGIN_NAME}に投稿されたのでお知らせします。
            // ルーム名:{X-ROOM}
            // 掲示板タイトル:{X-BBS_NAME}
            // 記事タイトル:{X-SUBJECT}
            // 投稿者:{X-USER}
            // 投稿日時:{X-TO_DATE}
            //
            // {X-BODY}
            //
            // この記事に返信するには、下記アドレスへ
            // {X-URL}

            // --- 承認申請メール
            // (承認依頼){X-PLUGIN_MAIL_SUBJECT}
            //
            // {X-USER}さんから{X-PLUGIN_NAME}の承認依頼があったことをお知らせします。
            //
            // {X-WORKFLOW_COMMENT}
            //
            //
            // {X-PLUGIN_MAIL_BODY}

            // --- 承認完了メール
            // (承認完了){X-PLUGIN_MAIL_SUBJECT}
            //
            // {X-USER}さんの{X-PLUGIN_NAME}の承認が完了されたことをお知らせします。
            // もし{X-USER}さんの{X-PLUGIN_NAME}に覚えがない場合はこのメールを破棄してください。
            //
            // {X-WORKFLOW_COMMENT}
            //
            //
            // {X-PLUGIN_MAIL_BODY}

            // 変換
            $convert_embedded_tags = [
                // nc3埋込タグ, cc埋込タグ
                ['{X-PLUGIN_MAIL_SUBJECT}', $mail_subject],
                ['{X-PLUGIN_MAIL_BODY}', $mail_body],
                ['{X-SITE_NAME}', '[[' . NoticeEmbeddedTag::site_name . ']]'],
                ['{X-SUBJECT}',   '[[' . NoticeEmbeddedTag::title . ']]'],
                ['{X-USER}',      '[[' . NoticeEmbeddedTag::created_name . ']]'],
                ['{X-TO_DATE}',   '[[' . NoticeEmbeddedTag::created_at . ']]'],
                ['{X-BODY}',      '[[' . NoticeEmbeddedTag::body . ']]'],
                ['{X-URL}',       '[[' . NoticeEmbeddedTag::url . ']]'],
                ['{X-PLUGIN_NAME}', '掲示板'],
                // 除外
                ['({X-ROOM} {X-BBS_NAME})', ''],
                ['掲示板タイトル:{X-BBS_NAME}', ''],
                ['ルーム名:{X-ROOM}', ''],
                ['{X-BBS_NAME}', ''],
                ['{X-ROOM}', ''],
                ['{X-WORKFLOW_COMMENT}', ''],
            ];
            foreach ($convert_embedded_tags as $convert_embedded_tag) {
                $mail_subject =     str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_subject);
                $mail_body =        str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_body);
                $approval_subject = str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $approval_subject);
                $approval_body =    str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $approval_body);
                $approved_subject = str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $approved_subject);
                $approved_body =    str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $approved_body);
            }

            $journals_ini = "";
            $journals_ini .= "[blog_base]\n";
            $journals_ini .= "blog_name = \"" . $nc3_bbs->name . "\"\n";
            $journals_ini .= "use_like = " . Nc3BlockSetting::getNc3BlockSettingValue($block_settings, $nc3_bbs->block_key, 'use_like') . "\n";
            $journals_ini .= "article_post_flag = " . $article_post_flag . "\n";
            $journals_ini .= "reporter_post_flag = " . $reporter_post_flag . "\n";
            $journals_ini .= "notice_on = " . $mail_setting->is_mail_send . "\n";
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
            $journals_ini .= "journal_id = " . 'BBS_' . $nc3_bbs->id . "\n";
            $journals_ini .= "room_id = " . $nc3_bbs->room_id . "\n";
            $journals_ini .= "plugin_key = \"bbses\"\n";
            $journals_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_bbs->created) . "\"\n";
            $journals_ini .= "created_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_bbs->created_user) . "\"\n";
            $journals_ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_bbs->created_user) . "\"\n";
            $journals_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_bbs->modified) . "\"\n";
            $journals_ini .= "updated_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_bbs->modified_user) . "\"\n";
            $journals_ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_bbs->modified_user) . "\"\n";

            // NC3掲示板の記事を移行する。
            $nc3_bbs_posts = Nc3BbsArticle::
                select('bbs_articles.*', 'bbs_article_trees.parent_id', 'bbs_article_trees.root_id', 'bbs_article_trees.id as bbs_article_tree_id')
                ->join('bbs_article_trees', 'bbs_article_trees.bbs_article_key', '=', 'bbs_articles.key')
                ->where('bbs_articles.bbs_key', $nc3_bbs->key)
                ->where('bbs_articles.is_latest', 1)
                ->whereIn('bbs_articles.language_id', $language_ids)
                ->orderBy('bbs_articles.created')
                ->get();

            // 記事はTSV でエクスポート
            $journals_tsv = "";

            // NC3記事をループ
            $journals_ini .= "\n";
            $journals_ini .= "[blog_post]\n";
            foreach ($nc3_bbs_posts as $nc3_bbs_post) {
                // TSV 形式でエクスポート
                if (!empty($journals_tsv)) {
                    $journals_tsv .= "\n";
                }

                $content = $this->nc3Wysiwyg(null, null, null, null, $nc3_bbs_post->content, 'bbses');

                $like = $likes->firstWhere('content_key', $nc3_bbs_post->key) ?? new Nc3Like();

                // 親記事
                $nc3_bbs_post_parent = $nc3_bbs_posts->firstWhere('bbs_article_tree_id', $nc3_bbs_post->parent_id) ?? new Nc3BbsArticle();
                // 根記事
                $nc3_bbs_post_root = $nc3_bbs_posts->firstWhere('bbs_article_tree_id', $nc3_bbs_post->root_id) ?? new Nc3BbsArticle();

                $journals_tsv .= $this->getCCDatetime($nc3_bbs_post->created)               . "\t"; // 0:投稿日時
                $journals_tsv .=                                                              "\t"; // 1:カテゴリ
                $journals_tsv .= $this->convertCCStatusFromNc3Status($nc3_bbs_post->status) . "\t"; // 2:状態
                $journals_tsv .=                                                              "\t"; // 3:承認フラグ
                $journals_tsv .= str_replace("\t", "", $nc3_bbs_post->title)                . "\t"; // 4:タイトル（タブ文字除去）
                $journals_tsv .= $content                                                   . "\t"; // 5:本文
                $journals_tsv .=                                                              "\t"; // more_content
                $journals_tsv .=                                                              "\t"; // more_title
                $journals_tsv .=                                                              "\t"; // hide_more_title
                $journals_tsv .= $nc3_bbs_post_parent->id                                   . "\t"; // 9:親記事ID
                $journals_tsv .= $nc3_bbs_post_root->id                                     . "\t"; // 10:根記事ID（nc2だとトピックID）
                $journals_tsv .= $this->getCCDatetime($nc3_bbs_post->created)                                   . "\t"; // 11:最新投稿日時
                $journals_tsv .= Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_bbs_post->created_user)      . "\t"; // 12:投稿者名
                $journals_tsv .= $like->like_count                                                              . "\t"; // 13:いいね数
                $journals_tsv .=                                                                                  "\t"; // 14:いいねのsession_id & nc3 user_id
                $journals_tsv .= Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_bbs_post->created_user)     . "\t"; // 15:投稿者ID
                $journals_tsv .= $this->getCCDatetime($nc3_bbs_post->modified)                                  . "\t"; // 16:更新日時
                $journals_tsv .= Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_bbs_post->modified_user)     . "\t"; // 17:更新者名
                $journals_tsv .= Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_bbs_post->modified_user)    . "\t"; // 18:更新者ID

                // 記事のタイトルの一覧
                // タイトルに " あり
                if (strpos($nc3_bbs_post->title, '"')) {
                    // ログ出力
                    $this->putError(1, 'BBS title in double-quotation', "タイトル = " . $nc3_bbs_post->title);
                }
                $journals_ini .= "post_title[" . $nc3_bbs_post->id . "] = \"" . str_replace('"', '', $nc3_bbs_post->title) . "\"\n";
            }

            // bbs->blog移行の場合は、blog用のフォルダに吐き出す
            $export_path = 'bbses/bbs_';
            if ($this->plugin_name['bbses'] === 'blogs') {
                $export_path = 'blogs/blog_bbs_';
            }

            // blog の設定
            $this->storagePut($this->getImportPath($export_path) . $this->zeroSuppress($nc3_bbs->id) . '.ini', $journals_ini);

            // blog の記事
            $journals_tsv = $this->exportStrReplace($journals_tsv, 'bbses');
            $this->storagePut($this->getImportPath($export_path) . $this->zeroSuppress($nc3_bbs->id) . '.tsv', $journals_tsv);
        }
    }

    /**
     * NC3：FAQ（faqs）の移行
     */
    private function nc3ExportFaq($redo)
    {
        $this->putMonitor(3, "Start nc3ExportFaq.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('faqs/'));
        }

        // NC3FAQ（faqs）を移行する。
        $nc3_faqs = Nc3Faq::select('faqs.*', 'blocks.key as block_key', 'blocks.room_id', 'rooms.space_id')
            ->join('blocks', function ($join) {
                $join->on('blocks.id', '=', 'faqs.block_id')
                    ->where('blocks.plugin_key', 'faqs');
            })
            ->join('rooms', function ($join) {
                $join->on('rooms.id', '=', 'blocks.room_id')
                    ->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID]);
            })
            ->orderBy('faqs.id')
            ->get();

        // 空なら戻る
        if ($nc3_faqs->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        // 記事を投稿できる権限, メール通知を受け取る権限
        $block_role_permissions = Nc3BlockRolePermission::getBlockRolePermissionsByBlockKeys($nc3_faqs->pluck('block_key'));

        // カテゴリ
        $categories = Nc3Category::getCategoriesByBlockIds($nc3_faqs->pluck('block_id'));

        // ブロック設定
        $block_settings = Nc3BlockSetting::whereIn('block_key', $nc3_faqs->pluck('block_key'))->get();

        // 使用言語（日本語・英語）で有効な言語を取得
        $language_ids = Nc3Language::where('is_active', 1)->pluck('id');

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

            // (nc3)投稿権限は１件のみ 投稿権限, 一般
            $post_permission_general_user = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $nc3_faq->block_key, $nc3_faq->room_id, 'content_creatable', 'general_user');

            // 権限設定
            // ----------------------------------------------------
            // ※ユーザ (nc3)一般 => (cc)編集者

            $article_post_flag = 1;     // 投稿権限はnc3編集者まで常時チェックON
            $reporter_post_flag = 0;

            // 一般まで
            if ($post_permission_general_user) {
                $reporter_post_flag = 1;
            }

            $use_workflow = Nc3BlockSetting::getNc3BlockSettingValue($block_settings, $nc3_faq->block_key, 'use_workflow');

            $faqs_ini = "";
            $faqs_ini .= "[faq_base]\n";
            $faqs_ini .= "faq_name = \"" . $nc3_faq->name . "\"\n";
            $faqs_ini .= "view_count = 10\n";
            $faqs_ini .= "sequence_conditions = " . FaqSequenceConditionType::display_sequence_order . "\n";
            $faqs_ini .= "article_post_flag = " . $article_post_flag . "\n";
            $faqs_ini .= "article_approval_flag = 0\n";                                 // 編集長=モデは承認不要
            $faqs_ini .= "reporter_post_flag = " . $reporter_post_flag . "\n";
            $faqs_ini .= "reporter_approval_flag = " . $use_workflow . "\n";            // 承認ありなら編集者承認ON

            // NC3 情報
            $faqs_ini .= "\n";
            $faqs_ini .= "[source_info]\n";
            $faqs_ini .= "faq_id          = " . $nc3_faq->id . "\n";
            $faqs_ini .= "room_id         = " . $nc3_faq->room_id . "\n";
            $faqs_ini .= "plugin_key      = \"faqs\"\n";
            $faqs_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_faq->created) . "\"\n";
            $faqs_ini .= "created_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_faq->created_user) . "\"\n";
            $faqs_ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_faq->created_user) . "\"\n";
            $faqs_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_faq->modified) . "\"\n";
            $faqs_ini .= "updated_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_faq->modified_user) . "\"\n";
            $faqs_ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_faq->modified_user) . "\"\n";

            // NC3FAQで使ってるカテゴリ（faq_category）のみ移行する。
            $faqs_ini .= "\n";
            $faqs_ini .= "[categories]\n";
            $faq_categories = $categories->where('block_id', $nc3_faq->block_id);

            $faqs_ini_originals = "";

            foreach ($faq_categories as $faq_category) {
                $faqs_ini_originals .= "original_categories[" . $faq_category->id . "] = \"" . $faq_category->name . "\"\n";
            }
            if (!empty($faqs_ini_originals)) {
                $faqs_ini .= $faqs_ini_originals;
            }

            // NC3FAQの記事（faq_question）を移行する。
            $nc3_faq_questions = Nc3FaqQuestion::select('faq_questions.*', 'faq_question_orders.weight as display_sequence')
                ->join('faq_question_orders', function ($join) {
                    $join->on('faq_question_orders.faq_question_key', '=', 'faq_questions.key');
                })
                ->where('faq_questions.faq_key', $nc3_faq->key)
                ->where('faq_questions.is_latest', 1)
                ->whereIn('faq_questions.language_id', $language_ids)
                ->orderBy('faq_question_orders.weight')
                ->get();

            // FAQの記事はTSV でエクスポート
            // カテゴリID{\t}表示順{\t}タイトル{\t}本文
            $faqs_tsv = "";

            // NC3FAQの記事をループ
            foreach ($nc3_faq_questions as $nc3_faq_question) {
                // TSV 形式でエクスポート
                if (!empty($faqs_tsv)) {
                    $faqs_tsv .= "\n";
                }

                $faq_category = $faq_categories->firstWhere('id', $nc3_faq_question->category_id) ?? new Nc3Category();

                $answer = $this->nc3Wysiwyg(null, null, null, null, $nc3_faq_question->answer, 'faqs');

                $faqs_tsv .= $faq_category->name                                . "\t"; // [0]
                $faqs_tsv .= $nc3_faq_question->display_sequence                . "\t"; // [1]
                $faqs_tsv .= $this->getCCDatetime($nc3_faq_question->created)   . "\t";
                $faqs_tsv .= $nc3_faq_question->question                        . "\t";
                $faqs_tsv .= $answer                                            . "\t";
            }

            // FAQ の設定
            $this->storagePut($this->getImportPath('faqs/faq_') . $this->zeroSuppress($nc3_faq->id) . '.ini', $faqs_ini);

            // FAQ の記事
            $faqs_tsv = $this->exportStrReplace($faqs_tsv, 'faqs');
            $this->storagePut($this->getImportPath('faqs/faq_') . $this->zeroSuppress($nc3_faq->id) . '.tsv', $faqs_tsv);
        }
    }

    /**
     * NC3：リンクリスト（links）の移行
     */
    private function nc3ExportLinklist($redo)
    {
        $this->putMonitor(3, "Start nc3ExportLinklist.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('linklists/'));
        }

        // NC3リンクリスト（links）を移行する。
        $nc3_blocks = Nc3Block::select('blocks.*', 'blocks.key as block_key', 'rooms.space_id', 'blocks_languages.name')
            ->join('blocks_languages', function ($join) {
                $join->on('blocks_languages.block_id', '=', 'blocks.id')
                    ->where('blocks_languages.language_id', Nc3Language::language_id_ja);
            })
            ->join('rooms', function ($join) {
                $join->on('rooms.id', '=', 'blocks.room_id')
                    ->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID]);
            })
            ->where('blocks.plugin_key', 'links')
            ->orderBy('blocks.id')
            ->get();

        // 空なら戻る
        if ($nc3_blocks->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        // カテゴリ
        $categories = Nc3Category::getCategoriesByBlockIds($nc3_blocks->pluck('id'));

        // NC3リンクリスト（Linklist）のループ
        foreach ($nc3_blocks as $nc3_block) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_block->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            $linklists_ini = "";
            $linklists_ini .= "[linklist_base]\n";
            $linklists_ini .= "linklist_name = \"" . $nc3_block->name . "\"\n";

            // NC3 情報
            $linklists_ini .= "\n";
            $linklists_ini .= "[source_info]\n";
            $linklists_ini .= "linklist_id     = " . $nc3_block->id . "\n";
            $linklists_ini .= "room_id         = " . $nc3_block->room_id . "\n";
            $linklists_ini .= "plugin_key      = \"links\"\n";
            $linklists_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_block->created) . "\"\n";
            $linklists_ini .= "created_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_block->created_user) . "\"\n";
            $linklists_ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_block->created_user) . "\"\n";
            $linklists_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_block->modified) . "\"\n";
            $linklists_ini .= "updated_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_block->modified_user) . "\"\n";
            $linklists_ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_block->modified_user) . "\"\n";

            // NC3リンクリストで使っているカテゴリのみ移行する。
            $linklists_ini .= "\n";
            $linklists_ini .= "[categories]\n";

            $linklist_categories = $categories->where('block_id', $nc3_block->id);

            $journals_ini_originals = "";

            foreach ($linklist_categories as $linklist_category) {
                $journals_ini_originals .= "original_categories[" . $linklist_category->id . "] = \"" . $linklist_category->name . "\"\n";
            }
            if (!empty($journals_ini_originals)) {
                $linklists_ini .= $journals_ini_originals;
            }

            // NC3リンクリストの記事（links）を移行する。
            $nc3_links = Nc3Link::select('links.*', 'link_orders.weight as display_sequence')
                ->join('link_orders', function ($join) {
                    $join->on('link_orders.link_key', '=', 'links.key');
                })
                ->where('links.block_id', $nc3_block->id)
                ->where('links.is_latest', 1)
                ->orderBy('link_orders.category_key')
                ->orderBy('link_orders.weight')
                ->get();

            // target 指定を取るために最初のブロックを参照（NC3 はブロック単位でtarget 指定していた。最初を移行する）
            $nc3_link_frame_setting = Nc3Frame::select('link_frame_settings.*')
                ->join('link_frame_settings', function ($join) {
                    $join->on('link_frame_settings.frame_key', '=', 'frames.key');
                })
                ->where('frames.block_id', $nc3_block->id)
                ->first() ?? new Nc3LinkFrameSetting();

            // リンクリストの記事はTSV でエクスポート
            // タイトル{\t}URL{\t}説明{\t}新規ウィンドウflag{\t}表示順
            $linklists_tsv = "";

            $nc3_frame = Nc3Frame::where('block_id', $nc3_block->id)->first();

            // NC3リンクリストの記事をループ
            foreach ($nc3_links as $nc3_link) {
                // TSV 形式でエクスポート
                if (!empty($linklists_tsv)) {
                    $linklists_tsv .= "\n";
                }

                $linklist_category = $linklist_categories->firstWhere('id', $nc3_link->category_id) ?? new Nc3Category();

                $linklists_tsv .= str_replace(array("\r", "\n", "\t"), "", $nc3_link->title)        . "\t";
                $linklists_tsv .= str_replace(array("\r", "\n", "\t"), "", $nc3_link->url)          . "\t";
                $linklists_tsv .= str_replace(array("\r", "\n", "\t"), " ", $nc3_link->description) . "\t";
                $linklists_tsv .= $nc3_link_frame_setting->open_new_tab                             . "\t"; // [3] 新規ウィンドウで表示
                $linklists_tsv .= $nc3_link->display_sequence                                       . "\t";
                $linklists_tsv .= $linklist_category->name;

                // NC3のリンク切れチェック
                $this->checkDeadLinkNc3($nc3_link->url, 'links', $nc3_frame);
            }

            // リンクリストの設定
            $this->storagePut($this->getImportPath('linklists/linklist_') . $this->zeroSuppress($nc3_block->id) . '.ini', $linklists_ini);

            // リンクリストの記事
            $this->storagePut($this->getImportPath('linklists/linklist_') . $this->zeroSuppress($nc3_block->id) . '.tsv', $linklists_tsv);
        }
    }

    /**
     * NC3：汎用データベース（multidatabases）の移行
     */
    private function nc3ExportMultidatabase($redo)
    {
        $this->putMonitor(3, "Start nc3ExportMultidatabase.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('databases/'));
        }

        // NC3汎用データベース（multidatabases）を移行する。
        $nc3_multidatabases_query = Nc3Multidatabase::select('multidatabases.*', 'blocks.key as block_key', 'blocks.room_id', 'rooms.space_id')
            ->join('blocks', function ($join) {
                $join->on('blocks.id', '=', 'multidatabases.block_id')
                    ->where('blocks.plugin_key', 'multidatabases');
            })
            ->join('rooms', function ($join) {
                $join->on('rooms.id', '=', 'blocks.room_id')
                    ->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID]);
            })
            ->orderBy('multidatabases.id');

        $nc3_export_where_multidatabase_ids = $this->getMigrationConfig('databases', 'nc3_export_where_multidatabase_ids');
        if ($nc3_export_where_multidatabase_ids) {
            $nc3_multidatabases_query = $nc3_multidatabases_query->whereIn('multidatabases.id', $nc3_export_where_multidatabase_ids);
        }

        $nc3_multidatabases = $nc3_multidatabases_query->get();

        // 空なら戻る
        if ($nc3_multidatabases->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        // 記事を投稿できる権限, メール通知を受け取る権限
        $block_role_permissions = Nc3BlockRolePermission::getBlockRolePermissionsByBlockKeys($nc3_multidatabases->pluck('block_key'));

        // メール設定
        $mail_settings = Nc3MailSetting::getMailSettingsByBlockKeys($nc3_multidatabases->pluck('block_key'), 'multidatabases');

        // サイト設定
        $site_settings = Nc3SiteSetting::where('language_id', Nc3Language::language_id_ja)->get();

        // ブロック設定
        $block_settings = Nc3BlockSetting::whereIn('block_key', $nc3_multidatabases->pluck('block_key'))->get();

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

            // (nc3)投稿権限は１件のみ 投稿権限, 一般
            $post_permission_general_user = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $nc3_multidatabase->block_key, $nc3_multidatabase->room_id, 'content_creatable', 'general_user');

            // 権限設定
            // ----------------------------------------------------
            // ※ユーザ (nc3)一般 => (cc)編集者

            $article_post_flag = 1;     // 投稿権限はnc3編集者まで常時チェックON
            $reporter_post_flag = 0;

            // 一般まで
            if ($post_permission_general_user) {
                $reporter_post_flag = 1;
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

            // (nc3)メール通知を受け取る権限
            // ゲスト
            $mail_permission_visitor = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $nc3_multidatabase->block_key, $nc3_multidatabase->room_id, 'mail_content_receivable', 'visitor');
            // 一般
            $mail_permission_general_user = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $nc3_multidatabase->block_key, $nc3_multidatabase->room_id, 'mail_content_receivable', 'general_user');
            // 編集者
            $mail_permission_editor = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $nc3_multidatabase->block_key, $nc3_multidatabase->room_id, 'mail_content_receivable', 'editor');

            $notice_everyone = 0;
            $notice_admin_group = 0;
            $notice_moderator_group = 0;
            $notice_group = 0;
            $notice_public_general_group = 0;
            $notice_public_moderator_group = 0;

            // ゲストまで
            if ($mail_permission_visitor) {
                if ($nc3_multidatabase->space_id == Nc3Space::PUBLIC_SPACE_ID) {
                    // 全ユーザ通知
                    $notice_everyone = 1;

                } elseif ($nc3_multidatabase->space_id == Nc3Space::COMMUNITY_SPACE_ID) {
                    // グループ通知
                    $notice_group = 1;
                }

            // 一般まで
            } elseif ($mail_permission_general_user) {
                if ($nc3_multidatabase->space_id == Nc3Space::PUBLIC_SPACE_ID) {
                    // パブリック一般通知
                    $notice_public_general_group = 1;
                    $notice_admin_group = 1;

                } elseif ($nc3_multidatabase->space_id == Nc3Space::COMMUNITY_SPACE_ID) {
                    // グループ通知
                    $notice_group = 1;
                }

            // 編集者まで
            } elseif ($mail_permission_editor) {
                if ($nc3_multidatabase->space_id == Nc3Space::PUBLIC_SPACE_ID) {
                    // パブリックモデレーター通知
                    $notice_public_moderator_group = 1;
                    $notice_admin_group = 1;
                } elseif ($nc3_multidatabase->space_id == Nc3Space::COMMUNITY_SPACE_ID) {
                    // モデレータユーザ通知
                    $notice_moderator_group = 1;
                    $notice_admin_group = 1;
                }

            // 編集者OFF=編集長までON
            } elseif ($mail_permission_editor == 0) {
                // 管理者グループ通知
                $notice_admin_group = 1;
            }

            // 通知メール（データなければblock_key=nullの初期設定取得）
            $mail_setting = $mail_settings->firstWhere('block_key', $nc3_multidatabase->block_key) ?? $mail_settings->firstWhere('block_key', null);
            $mail_subject = $mail_setting->mail_fixed_phrase_subject;
            $mail_body = $mail_setting->mail_fixed_phrase_body;

            // 承認メール
            $approval_subject = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_mail_subject');
            $approval_body = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_mail_body');

            // 承認完了メール
            $approved_subject = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_completion_mail_subject');
            $approved_body = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_completion_mail_body');

            // --- メール配信設定
            // [{X-SITE_NAME}-{X-PLUGIN_NAME}]{X-SUBJECT}({X-ROOM} {X-BLOCK_NAME})
            //
            // {X-PLUGIN_NAME}に投稿されたのでお知らせします。
            // ルーム名:{X-ROOM}
            // 汎用データベースタイトル:{X-BLOCK_NAME}
            // コンテンツタイトル:{X-SUBJECT}
            // 投稿者:{X-USER}
            // 投稿日時:{X-TO_DATE}
            //
            //
            // {X-DATA}
            //
            //
            // この記事に返信するには、下記アドレスへ
            // {X-URL}

            // --- 承認申請メール
            // (承認依頼){X-PLUGIN_MAIL_SUBJECT}
            //
            // {X-USER}さんから{X-PLUGIN_NAME}の承認依頼があったことをお知らせします。
            //
            // {X-WORKFLOW_COMMENT}
            //
            //
            // {X-PLUGIN_MAIL_BODY}

            // --- 承認完了メール
            // (承認完了){X-PLUGIN_MAIL_SUBJECT}
            //
            // {X-USER}さんの{X-PLUGIN_NAME}の承認が完了されたことをお知らせします。
            // もし{X-USER}さんの{X-PLUGIN_NAME}に覚えがない場合はこのメールを破棄してください。
            //
            // {X-WORKFLOW_COMMENT}
            //
            //
            // {X-PLUGIN_MAIL_BODY}

            // 変換
            $convert_embedded_tags = [
                // nc3埋込タグ, cc埋込タグ
                ['{X-PLUGIN_MAIL_SUBJECT}', $mail_subject],
                ['{X-PLUGIN_MAIL_BODY}', $mail_body],
                ['{X-SITE_NAME}', '[[' . NoticeEmbeddedTag::site_name . ']]'],
                ['{X-SUBJECT}',   '[[' . NoticeEmbeddedTag::title . ']]'],
                ['{X-USER}',      '[[' . NoticeEmbeddedTag::created_name . ']]'],
                ['{X-TO_DATE}',   '[[' . NoticeEmbeddedTag::created_at . ']]'],
                ['{X-DATA}',      '[[' . DatabaseNoticeEmbeddedTag::all_items . ']]'],
                ['{X-URL}',       '[[' . NoticeEmbeddedTag::url . ']]'],
                ['{X-PLUGIN_NAME}', 'データベース'],
                // 除外
                ['({X-ROOM} {X-BLOCK_NAME})', ''],
                ['汎用データベースタイトル:{X-BLOCK_NAME}', ''],
                ['ルーム名:{X-ROOM}', ''],
                ['{X-BLOCK_NAME}', ''],
                ['{X-ROOM}', ''],
                ['{X-WORKFLOW_COMMENT}', ''],
            ];
            foreach ($convert_embedded_tags as $convert_embedded_tag) {
                $mail_subject =     str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_subject);
                $mail_body =        str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_body);
                $approval_subject = str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $approval_subject);
                $approval_body =    str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $approval_body);
                $approved_subject = str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $approved_subject);
                $approved_body =    str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $approved_body);
            }

            // （NC3）承認メールは、承認必要＋承認メール通知ONで、承認権限（～編集者まで設定可）に飛ぶ。
            // （NC3）承認完了メールは、サイトセッティングにあるけどメール飛ばなかった。
            //        承認完了時、メール通知ON（～ゲストまで通知）でメール通知フォーマットでメール飛ぶ。
            //        ⇒ （CC）NC3承認完了通知フォーマットを、CC承認完了通知フォーマットにセット。けど通知しない。

            // 記事承認（content_publishable）はルーム管理者・編集長固定. 編集者は承認必要

            $use_workflow = Nc3BlockSetting::getNc3BlockSettingValue($block_settings, $nc3_multidatabase->block_key, 'use_workflow');

            // データベース設定
            $multidatabase_ini = "";
            $multidatabase_ini .= "[database_base]\n";
            $multidatabase_ini .= "database_name = \"" . $nc3_multidatabase->name . "\"\n";
            $multidatabase_ini .= "article_post_flag = " . $article_post_flag . "\n";
            $multidatabase_ini .= "article_approval_flag = 0\n";                                 // 編集長=モデは承認不要
            $multidatabase_ini .= "reporter_post_flag = " . $reporter_post_flag . "\n";
            $multidatabase_ini .= "reporter_approval_flag = " . $use_workflow . "\n";            // 承認ありなら編集者承認ON
            $multidatabase_ini .= "notice_on = " . $mail_setting->is_mail_send . "\n";
            $multidatabase_ini .= "notice_everyone = " . $notice_everyone . "\n";
            $multidatabase_ini .= "notice_group = " . $notice_group . "\n";
            $multidatabase_ini .= "notice_moderator_group = " . $notice_moderator_group . "\n";
            $multidatabase_ini .= "notice_admin_group = " . $notice_admin_group . "\n";
            $multidatabase_ini .= "notice_public_general_group = " . $notice_public_general_group . "\n";
            $multidatabase_ini .= "notice_public_moderator_group = " . $notice_public_moderator_group . "\n";
            $multidatabase_ini .= "mail_subject = \"" . $mail_subject . "\"\n";
            $multidatabase_ini .= "mail_body = \"" . $mail_body . "\"\n";
            $multidatabase_ini .= "approval_on = " . $mail_setting->is_mail_send_approval . "\n";
            $multidatabase_ini .= "approval_admin_group = 1\n";                                  // approval_onのON/OFFに関わらず通知先は1:「管理者グループ」をセット
            $multidatabase_ini .= "approval_subject = \"" . $approval_subject . "\"\n";
            $multidatabase_ini .= "approval_body = \"" . $approval_body . "\"\n";
            $multidatabase_ini .= "approved_on = 0\n";                                           // 承認完了通知はメール飛ばなかった
            $multidatabase_ini .= "approved_author = 0\n";                                       // 1:投稿者へ通知する
            $multidatabase_ini .= "approved_admin_group = 0\n";                                  // 1:「管理者グループ」通知
            $multidatabase_ini .= "approved_subject = \"" . $approved_subject . "\"\n";
            $multidatabase_ini .= "approved_body = \"" . $approved_body . "\"\n";

            // NC3 情報
            $multidatabase_ini .= "\n";
            $multidatabase_ini .= "[source_info]\n";
            $multidatabase_ini .= "multidatabase_id = " . $nc3_multidatabase->id . "\n";
            $multidatabase_ini .= "room_id = " . $nc3_multidatabase->room_id . "\n";
            $multidatabase_ini .= "plugin_key = \"multidatabases\"\n";
            $multidatabase_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_multidatabase->created) . "\"\n";
            $multidatabase_ini .= "created_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_multidatabase->created_user) . "\"\n";
            $multidatabase_ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_multidatabase->created_user) . "\"\n";
            $multidatabase_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_multidatabase->modified) . "\"\n";
            $multidatabase_ini .= "updated_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_multidatabase->modified_user) . "\"\n";
            $multidatabase_ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_multidatabase->modified_user) . "\"\n";

            // 汎用データベースのカラム情報
            $multidatabase_metadatas = Nc3MultidatabaseMetadata::where('multidatabase_id', $nc3_multidatabase->id)
                ->orderBy('position', 'asc')
                ->orderBy('rank', 'asc')
                ->get();
            if (empty($multidatabase_metadatas)) {
                continue;
            }

            $nc3_frame = Nc3Frame::where('block_id', $nc3_multidatabase->block_id)->first();

            // カラム情報
            $multidatabase_cols_rows = array();

            foreach ($multidatabase_metadatas as $multidatabase_metadata) {
                // type
                if ($multidatabase_metadata->type == 'text') {
                    $column_type = "text";
                } elseif ($multidatabase_metadata->type == 'textarea') {
                    $column_type = "textarea";
                } elseif ($multidatabase_metadata->type == 'link') {
                    $column_type = "link";
                } elseif ($multidatabase_metadata->type == 'select') {
                    $column_type = "select";
                } elseif ($multidatabase_metadata->type == 'checkbox') {
                    $column_type = "checkbox";
                } elseif ($multidatabase_metadata->type == 'file') {
                    $column_type = "file";
                } elseif ($multidatabase_metadata->type == 'image') {
                    $column_type = "image";
                } elseif ($multidatabase_metadata->type == 'wysiwyg') {
                    $column_type = "wysiwyg";
                } elseif ($multidatabase_metadata->type == 'autonumber') {  // 自動採番
                    $column_type = "text";
                } elseif ($multidatabase_metadata->type == 'mail') {
                    $column_type = "mail";
                } elseif ($multidatabase_metadata->type == 'date') {
                    $column_type = "date";
                } elseif ($multidatabase_metadata->type == 'created') {
                    $column_type = "created";
                } elseif ($multidatabase_metadata->type == 'updated') {
                    $column_type = "updated";
                }
                $select_flag = 0;
                // (nc) 絞り込みは、select|checkboxで一覧表示の時に表示
                if ($multidatabase_metadata->type == 'select' || $multidatabase_metadata->type == 'checkbox') {
                    if ($multidatabase_metadata->is_visible_list == 1) {
                        $select_flag = 1;
                    }
                }
                $metadata_id = $multidatabase_metadata->id;
                $multidatabase_cols_rows[$metadata_id]["column_type"]      = $column_type;
                $multidatabase_cols_rows[$metadata_id]["column_name"]      = $multidatabase_metadata->name;
                $multidatabase_cols_rows[$metadata_id]["required"]         = $multidatabase_metadata->is_require;
                $multidatabase_cols_rows[$metadata_id]["frame_col"]        = null;
                $multidatabase_cols_rows[$metadata_id]["title_flag"]       = $multidatabase_metadata->is_title;
                $multidatabase_cols_rows[$metadata_id]["list_hide_flag"]   = ($multidatabase_metadata->is_visible_list == 0) ? 1 : 0;
                $multidatabase_cols_rows[$metadata_id]["detail_hide_flag"] = ($multidatabase_metadata->is_visible_detail == 0) ? 1 : 0;
                $multidatabase_cols_rows[$metadata_id]["sort_flag"]        = $multidatabase_metadata->is_sortable;
                $multidatabase_cols_rows[$metadata_id]["search_flag"]      = $multidatabase_metadata->is_searchable;
                $multidatabase_cols_rows[$metadata_id]["select_flag"]      = $select_flag;
                $multidatabase_cols_rows[$metadata_id]["display_sequence"] = null;  // 後処理で連番セット
                $multidatabase_cols_rows[$metadata_id]["row_group"]        = null;
                $multidatabase_cols_rows[$metadata_id]["column_group"]     = null;
                if ($multidatabase_metadata->position == 0) {
                    // header
                    $multidatabase_cols_rows[$metadata_id]["row_group"]    = 1;
                    $multidatabase_cols_rows[$metadata_id]["column_group"] = 1;
                }
                if ($multidatabase_metadata->position == 1) {
                    // left
                    $multidatabase_cols_rows[$metadata_id]["row_group"]    = 2;
                    $multidatabase_cols_rows[$metadata_id]["column_group"] = 1;
                }
                if ($multidatabase_metadata->position == 2) {
                    // right
                    $multidatabase_cols_rows[$metadata_id]["row_group"]    = 2;
                    $multidatabase_cols_rows[$metadata_id]["column_group"] = 2;
                }
                if ($multidatabase_metadata->position == 3) {
                    // footer
                    $multidatabase_cols_rows[$metadata_id]["row_group"]    = 3;
                    $multidatabase_cols_rows[$metadata_id]["column_group"] = 1;
                }
                $columns_selects = json_decode($multidatabase_metadata->selections) ?? [];
                // columns_selects <= aaa|bbb|ccc
                $multidatabase_cols_rows[$metadata_id]["columns_selects"]  = implode('|', $columns_selects);
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
                $multidatabase_ini .= "title_flag       = "   . $multidatabase_cols["title_flag"]       . "\n";
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
            $multidatabase_contents = Nc3MultidatabaseContent::where('multidatabase_contents.multidatabase_id', $nc3_multidatabase->id)
                ->where('multidatabase_contents.is_latest', 1)
                ->orderBy('multidatabase_contents.id', 'asc')
                ->get();

            // アップロードファイル
            $multidatabase_uploads = Nc3UploadFile::where('plugin_key', 'multidatabases')
                ->whereIn('content_key', $multidatabase_contents->pluck('key'))
                ->get();

            Storage::delete($this->getImportPath('databases/database_') . $this->zeroSuppress($nc3_multidatabase->id) . '.tsv');
            $tsv = '';
            $tsv .= $tsv_header . "\n";
            foreach ($multidatabase_contents as $multidatabase_content) {
                // tsv_record配列 初期化
                $tsv_record = $tsv_cols;

                // メタデータ分ループして各valueを取得
                foreach ($multidatabase_metadatas as $multidatabase_metadata) {
                    $value_no = 'value' . $multidatabase_metadata->col_no;
                    $content = $multidatabase_content->$value_no;

                    $content = str_replace("\n", "<br />", $content);
                    // データ中にタブ文字が存在するケースがあったため、タブ文字は半角スペースに置き換えるようにした。
                    $content = str_replace("\t", " ", $content);

                    // メタデータの型による変換
                    if ($multidatabase_metadata->type == 'file' || $multidatabase_metadata->type == 'image') {
                        // 画像型、ファイル型
                        // (nc3) 画像、ファイル型は、ファイルあってもvalue空。毎回UploadFile見る必要あり。

                        // NC3 のアップロードID 抜き出し
                        $multidatabase_upload = $multidatabase_uploads->firstWhere('field_name', $value_no . '_attach');
                        $nc3_uploads_id = $multidatabase_upload->id;
                        if ($nc3_uploads_id) {
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
                        }

                    } elseif ($multidatabase_metadata->type == 'wysiwyg') {
                        // WYSIWYG
                        $content = $this->nc3Wysiwyg(null, null, null, null, $content, 'multidatabase');
                    } elseif ($multidatabase_metadata->type == 'date') {
                        // 日付型
                        $content = $this->getCCDatetime($content);
                    } elseif ($multidatabase_metadata->type == 'link') {
                        // リンク. NC3のリンク切れチェック
                        $this->checkDeadLinkNc3($content, 'multidatabases', $nc3_frame);
                    }

                    $tsv_record[$multidatabase_metadata->id] = $content;
                }

                // 状態
                $tsv_record['status'] = $this->convertCCStatusFromNc3Status($multidatabase_content->status);
                // 表示順
                $tsv_record['display_sequence'] = null;
                // 投稿日
                $tsv_record['posted_at']       = $this->getCCDatetime($multidatabase_content->created);
                // 登録日時、更新日時等
                $tsv_record['created_at']      = $this->getCCDatetime($multidatabase_content->created);
                $tsv_record['created_name']    = Nc3User::getNc3HandleFromNc3UserId($nc3_users, $multidatabase_content->created_user);
                $tsv_record['insert_login_id'] = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $multidatabase_content->created_user);
                $tsv_record['updated_at']      = $this->getCCDatetime($multidatabase_content->modified);
                $tsv_record['updated_name']    = Nc3User::getNc3HandleFromNc3UserId($nc3_users, $multidatabase_content->modified_user);
                $tsv_record['update_login_id'] = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $multidatabase_content->modified_user);
                $tsv_record['content_id']      = $multidatabase_content->id;
                $tsv .= implode("\t", $tsv_record) . "\n";
            }

            // データ行の書き出し
            $tsv = $this->exportStrReplace($tsv, 'databases');
            $this->storageAppend($this->getImportPath('databases/database_') . $this->zeroSuppress($nc3_multidatabase->id) . '.tsv', $tsv);

            // detabase の設定
            $this->storagePut($this->getImportPath('databases/database_') . $this->zeroSuppress($nc3_multidatabase->id) . '.ini', $multidatabase_ini);
        }
    }

    /**
     * NC3：登録フォーム（registrations）の移行
     */
    private function nc3ExportRegistration($redo)
    {
        $this->putMonitor(3, "Start nc3ExportRegistration.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('forms/'));
        }

        // NC3登録フォーム（registrations）を移行する。
        $nc3_registrations_query = Nc3Registration::select('registrations.*', 'blocks.key as block_key', 'blocks.room_id', 'rooms.space_id')
            ->join('blocks', function ($join) {
                $join->on('blocks.id', '=', 'registrations.block_id')
                    ->where('blocks.plugin_key', 'registrations');
            })
            ->join('rooms', function ($join) {
                $join->on('rooms.id', '=', 'blocks.room_id')
                    ->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID]);
            })
            ->where('registrations.is_latest', 1)
            ->orderBy('registrations.id');

        $nc3_export_where_registration_ids = $this->getMigrationConfig('forms', 'nc3_export_where_registration_ids');
        if ($nc3_export_where_registration_ids) {
            $nc3_registrations_query = $nc3_registrations_query->whereIn('registrations.id', $nc3_export_where_registration_ids);
        }
        $nc3_registrations = $nc3_registrations_query->get();

        // 空なら戻る
        if ($nc3_registrations->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

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
            if ($this->isOmmit('forms', 'export_ommit_registration_ids', $nc3_registration->id)) {
                continue;
            }

            // (nc3) is_answer_mail_send = (1)登録をメールで通知する   => 通知メールアドレスありなら (cc) mail_send_flag = 以下のアドレスにメール送信するON
            //     (nc3) is_regist_user_send = 登録者本人にメールする  => (cc) user_mail_send_flag = 登録者にメール送信する
            // (nc3) is_answer_mail_send = (0)登録をメールで通知しない => (cc) mail_send_flag      = (0 固定) 以下のアドレスにメール送信しない
            //                                                       => (cc) user_mail_send_flag = (0 固定) 登録者にメール送信しない
            // (nc3) rcpt_to = 主担以外で通知するメールアドレス = なし  => (cc) mail_send_address   = 送信するメールアドレス（複数ある場合はカンマで区切る）

            // (nc3) mail_send = 登録をメールで通知する
            if ($nc3_registration->is_answer_mail_send) {
                // メール通知ON
                $user_mail_send_flag = $nc3_registration->is_regist_user_send;
                // 通知メールアドレスありなら (cc) mail_send_flag = 以下のアドレスにメール送信するON
                $mail_send_flag = 0;
            } else {
                // メール通知OFF
                $user_mail_send_flag = 0;
                $mail_send_flag = 0;
            }

            $regist_control_flag = $nc3_registration->answer_timing ? 1 : 0;
            $regist_from =  $nc3_registration->answer_start_period ? $this->getCCDatetime($nc3_registration->answer_start_period) : '';
            $regist_to =  $nc3_registration->answer_end_period ? $this->getCCDatetime($nc3_registration->answer_end_period) : '';

            // 登録メール
            $mail_subject = $nc3_registration->registration_mail_subject;
            $mail_body = $nc3_registration->registration_mail_body;
            $mail_body = str_replace("\n", '\n', $mail_body);

            // --- 登録メール
            // [{X-SITE_NAME}-{X-PLUGIN_NAME}]{X-SUBJECT}を受け付けました。
            //
            // {X-SUBJECT}の登録通知先メールアドレスとしてあなたのメールアドレスが使用されました。
            // もし{X-SUBJECT}への登録に覚えがない場合はこのメールを破棄してください。
            //
            //
            // {X-SUBJECT}を受け付けました。
            //
            // 登録日時:{X-TO_DATE}
            //
            //
            // {X-DATA}
            //
            // メール内容を印刷の上、会場にご持参ください。

            // 変換
            $convert_embedded_tags = [
                // nc3埋込タグ, cc埋込タグ
                ['{X-SITE_NAME}', '[[' . NoticeEmbeddedTag::site_name . ']]'],
                ['{X-SUBJECT}',   '[[' . NoticeEmbeddedTag::title . ']]'],
                ['{X-USER}',      '[[' . NoticeEmbeddedTag::created_name . ']]'],
                ['{X-TO_DATE}',   '[[' . NoticeEmbeddedTag::created_at . ']]'],
                ['{X-DATA}',      '[[' . NoticeEmbeddedTag::body . ']]'],
                ['{X-PLUGIN_NAME}', '登録フォーム'],
                // 除外
                ['{X-ROOM}', ''],
            ];
            foreach ($convert_embedded_tags as $convert_embedded_tag) {
                $mail_subject =     str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_subject);
                $mail_body =        str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_body);
            }

            // 登録フォーム設定
            $registration_ini = "";
            $registration_ini .= "[form_base]\n";
            $registration_ini .= "forms_name = \""        . $nc3_registration->title . "\"\n";
            $registration_ini .= "mail_send_flag = "      . $mail_send_flag . "\n";
            $registration_ini .= "mail_send_address = \"\"\n";
            $registration_ini .= "user_mail_send_flag = " . $user_mail_send_flag . "\n";
            $registration_ini .= "mail_subject = \""      . $mail_subject . "\"\n";
            $registration_ini .= "mail_format = \""       . $mail_body . "\"\n";
            $registration_ini .= "data_save_flag = 1\n";
            $registration_ini .= "after_message = \""     . str_replace("\n", '\n', $nc3_registration->thanks_content) . "\"\n";
            $registration_ini .= "numbering_use_flag = 0\n";
            $registration_ini .= "numbering_prefix = null\n";
            $registration_ini .= "regist_control_flag = " . $regist_control_flag . "\n";
            $registration_ini .= "regist_from = \""       . $regist_from . "\"\n";
            $registration_ini .= "regist_to = \""         . $regist_to . "\"\n";

            // NC3 情報
            $registration_ini .= "\n";
            $registration_ini .= "[source_info]\n";
            $registration_ini .= "registration_id = " . $nc3_registration->id . "\n";
            $registration_ini .= "active_flag     = " . $nc3_registration->is_active . "\n";
            $registration_ini .= "room_id         = " . $nc3_registration->room_id . "\n";
            $registration_ini .= "plugin_key      = \"registration\"\n";
            $registration_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_registration->created) . "\"\n";
            $registration_ini .= "created_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_registration->created_user) . "\"\n";
            $registration_ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_registration->created_user) . "\"\n";
            $registration_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_registration->modified) . "\"\n";
            $registration_ini .= "updated_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_registration->modified_user) . "\"\n";
            $registration_ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_registration->modified_user) . "\"\n";

            // 登録フォームのカラム情報
            $registration_questions = Nc3RegistrationPage::select('registration_questions.*')
                ->join('registration_questions', function ($join) {
                    $join->on('registration_questions.registration_page_id', '=', 'registration_pages.id');
                })
                ->where('registration_pages.registration_id', $nc3_registration->id)
                ->orderBy('registration_pages.id', 'asc')
                ->orderBy('registration_questions.question_sequence', 'asc')
                ->get();

            if (empty($registration_questions)) {
                continue;
            }

            $registration_choices = Nc3RegistrationChoice::whereIn('registration_question_id', $registration_questions->pluck('id'))
                ->orderBy('registration_question_id')
                ->orderBy('choice_sequence')
                ->get();

            // カラム情報出力
            $registration_ini .= "\n";
            $registration_ini .= "[form_columns]\n";

            // カラム情報
            foreach ($registration_questions as $registration_question) {
                $registration_ini .= "form_column[" . $registration_question->id . "] = \"" . $registration_question->question_value . "\"\n";
            }
            $registration_ini .= "\n";

            // カラム詳細情報
            foreach ($registration_questions as $registration_question) {
                $registration_ini .= "[" . $registration_question->id . "]" . "\n";

                $rule_allowed_numeric = 'null';
                $rule_max = 'null';
                $rule_min = 'null';
                $rule_word_count = 'null';

                // type | 1:択一選択 | 2:複数選択 | 3:テキスト | 4:テキストエリア | 7:日付・時刻 | 8:リスト
                if ($registration_question->question_type == Nc3RegistrationQuestion::question_type_text) {
                    // (オプション) 登録を数値で求める
                    if ($registration_question->question_type_option == 1) {
                        $rule_allowed_numeric = 1;
                        // (オプション) 範囲を指定する
                        if ($registration_question->is_range) {
                            $rule_max = $registration_question->max;
                            $rule_min = $registration_question->min;
                        }
                    } else {
                        // (オプション) 範囲(文字数)を指定する
                        if ($registration_question->is_range) {
                            $rule_word_count = $registration_question->max;
                        }
                    }
                    $column_type = FormColumnType::text;
                } elseif ($registration_question->question_type == Nc3RegistrationQuestion::question_type_checkbox) {
                    $column_type = FormColumnType::checkbox;
                } elseif ($registration_question->question_type == Nc3RegistrationQuestion::question_type_radio) {
                    $column_type = FormColumnType::radio;
                } elseif ($registration_question->question_type == Nc3RegistrationQuestion::question_type_select) {
                    $column_type = FormColumnType::select;
                } elseif ($registration_question->question_type == Nc3RegistrationQuestion::question_type_textarea) {
                    $column_type = FormColumnType::textarea;
                } elseif ($registration_question->question_type == Nc3RegistrationQuestion::question_type_date) {
                    // 2:日付 / 3:時間 / 7:日付と時間
                    if ($registration_question->question_type_option == 2) {
                        // 日付
                        $column_type = FormColumnType::date;
                    } elseif ($registration_question->question_type_option == 3) {
                        // 時間
                        $column_type = FormColumnType::time;
                    } elseif ($registration_question->question_type_option == 7) {
                        // 日付と時間
                        $column_type = FormColumnType::date;
                        $this->putError(1, 'Form 項目「日付と時間」は日付として移行', "項目名 = " . $registration_question->question_value);
                    }

                    // (オプション) 期間を設定する = 移行しない
                    if ($registration_question->is_range) {
                        $this->putError(1, 'Form 項目「日付と時間」で「期間を設定する」は移行しない', "{$registration_question->min} ～ {$registration_question->max}");
                    }
                } elseif ($registration_question->question_type == Nc3RegistrationQuestion::question_type_mail) {
                    $column_type = FormColumnType::mail;
                } elseif ($registration_question->question_type == Nc3RegistrationQuestion::question_type_file) {
                    $column_type = FormColumnType::file;
                }

                $option_value = $registration_choices->where('registration_question_id', $registration_question->id)->pluck('choice_value')->implode('|');
                $registration_ini .= "column_type                = \"" . $column_type                     . "\"\n";
                $registration_ini .= "column_name                = \"" . $registration_question->question_value . "\"\n";
                $registration_ini .= "option_value               = \"" . $option_value                    . "\"\n"; // |区切り
                $registration_ini .= "required                   = "   . $registration_question->is_require   . "\n";
                $registration_ini .= "frame_col                  = "   . 0                                . "\n";
                $registration_ini .= "caption                    = \"" . $registration_question->description  . "\"\n";
                $registration_ini .= "caption_color              = \"" . "text-dark"                      . "\"\n";
                $registration_ini .= "minutes_increments         = "   . 10                               . "\n";
                $registration_ini .= "minutes_increments_from    = "   . 10                               . "\n";
                $registration_ini .= "minutes_increments_to      = "   . 10                               . "\n";
                $registration_ini .= "rule_allowed_numeric       = {$rule_allowed_numeric}\n";
                $registration_ini .= "rule_allowed_alpha_numeric = null\n";
                $registration_ini .= "rule_digits_or_less        = null\n";
                $registration_ini .= "rule_max                   = {$rule_max}\n";
                $registration_ini .= "rule_min                   = {$rule_min}\n";
                $registration_ini .= "rule_word_count            = {$rule_word_count}\n";
                $registration_ini .= "rule_date_after_equal      = null\n";
                $registration_ini .= "\n";
            }

            // フォーム の設定
            $this->storagePut($this->getImportPath('forms/form_') . $this->zeroSuppress($nc3_registration->id) . '.ini', $registration_ini);

            // 登録データもエクスポートする場合
            if ($this->hasMigrationConfig('forms', 'nc3_export_registration_data', true)) {
                // 対象外指定があれば、読み飛ばす
                if ($this->isOmmit('forms', 'export_ommit_registration_data_ids', $nc3_registration->id)) {
                    continue;
                }

                // データ部
                $registration_data_header = "[form_inputs]\n";
                $registration_data = "";

                // registration_answers.registration_question_key から registration_questions.key (複数) になってて、一意にたどれないため、
                // registration_pages から結合で対応

                $registration_answers = Nc3RegistrationPage::
                    select(
                        'registration_answers.*',
                        'registration_questions.id as registration_question_id',
                        'registration_questions.question_type',
                        'registration_answer_summaries.id AS registration_answer_summary_id',
                        'registration_answer_summaries.created AS data_created',
                        'registration_answer_summaries.created_user AS data_created_user',
                        'registration_answer_summaries.modified AS data_modified',
                        'registration_answer_summaries.modified_user AS data_modified_user'
                    )
                    ->join('registration_questions', function ($join) {
                        $join->on('registration_questions.registration_page_id', '=', 'registration_pages.id');
                    })
                    ->join('registration_answers', function ($join) {
                        $join->on('registration_answers.registration_question_key', '=', 'registration_questions.key');
                    })
                    ->join('registration_answer_summaries', function ($join) {
                        $join->on('registration_answer_summaries.id', '=', 'registration_answers.registration_answer_summary_id');
                    })
                    ->where('registration_pages.registration_id', $nc3_registration->id)
                    ->orderBy('registration_answer_summaries.serial_number', 'desc')
                    ->orderBy('registration_questions.question_sequence', 'asc')
                    ->get();

                // アップロードファイル
                $registration_uploads = Nc3UploadFile::where('plugin_key', 'registrations')
                    ->whereIn('content_key', $registration_answers->pluck('id'))
                    ->get();

                $answer_summary_id = null;
                foreach ($registration_answers as $registration_answer) {
                    if ($registration_answer->registration_answer_summary_id != $answer_summary_id) {
                        $registration_data_header .= "input[" . $registration_answer->registration_answer_summary_id . "] = \"\"\n";
                        $registration_data .= "\n[" . $registration_answer->registration_answer_summary_id . "]\n";
                        $registration_data .= "created_at      = \"" . $this->getCCDatetime($registration_answer->data_created) . "\"\n";
                        $registration_data .= "created_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $registration_answer->data_created_user) . "\"\n";
                        $registration_data .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $registration_answer->data_created_user) . "\"\n";
                        $registration_data .= "updated_at      = \"" . $this->getCCDatetime($registration_answer->data_modified) . "\"\n";
                        $registration_data .= "updated_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $registration_answer->data_modified_user) . "\"\n";
                        $registration_data .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $registration_answer->data_modified_user) . "\"\n";
                        $answer_summary_id = $registration_answer->registration_answer_summary_id;
                    }

                    $value = str_replace('"', '\"', $registration_answer->answer_value);
                    $value = str_replace("\n", '\n', $value);

                    if (Nc3RegistrationQuestion::isOptionItem($registration_answer->question_type)) {
                        // | で選択肢をばらす
                        $options = explode('|', $value);
                        $option_values = [];
                        foreach ($options as $option) {
                            // key valueを:でばらして、valueだけ取得
                            $option_value = explode(':', $option);
                            if (isset($option_value[1])) {
                                $option_values[] = $option_value[1];
                            }
                        }
                        // valueに詰めなおし
                        $value = implode('|', $option_values);
                    } elseif ($registration_answer->question_type == Nc3RegistrationQuestion::question_type_file) {
                        // ファイル型
                        // (nc3) ファイル型は、毎回UploadFile見る必要あり。

                        // NC3 のアップロードID 抜き出し
                        $nc3_upload = $registration_uploads->firstWhere('content_key', $registration_answer->id) ?? new Nc3UploadFile();
                        $nc3_uploads_id = $nc3_upload->id;
                        if ($nc3_uploads_id) {
                            // uploads.ini からファイルを探す
                            if (array_key_exists('uploads', $this->uploads_ini) && array_key_exists('upload', $this->uploads_ini['uploads']) && array_key_exists($nc3_uploads_id, $this->uploads_ini['uploads']['upload'])) {
                                if (array_key_exists($nc3_uploads_id, $this->uploads_ini) && array_key_exists('temp_file_name', $this->uploads_ini[$nc3_uploads_id])) {
                                    $value = '../../uploads/' . $this->uploads_ini[$nc3_uploads_id]['temp_file_name'];
                                } else {
                                    $this->putMonitor(3, "No Match uploads_ini array_key_exists temp_file_name.", "nc3_uploads_id = " . $nc3_uploads_id);
                                }
                            } else {
                                $this->putMonitor(3, "No Match uploads_ini array_key_exists uploads_ini_uploads_upload.", "nc3_uploads_id = " . $nc3_uploads_id);
                            }
                        }
                    }

                    $registration_data .=  "{$registration_answer->registration_question_id} = \"{$value}\"\n";
                }
                // フォーム の登録データ
                $this->storagePut($this->getImportPath('forms/form_') . $this->zeroSuppress($nc3_registration->id) . '.txt', $registration_data_header . $registration_data);
            }
        }
    }

    /**
     * NC3：新着情報（Topics）の移行
     */
    private function nc3ExportTopics($redo)
    {
        $this->putMonitor(3, "Start nc3ExportTopics.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('whatsnews/'));
        }

        // NC3新着情報（Topics）を移行する。
        $nc3_topic_frame_settings = Nc3TopicFrameSetting::
            select('topic_frame_settings.*', 'frames_languages.name as frame_name', 'frames.room_id', 'frames.id as frame_id', 'pages_languages.name as page_name')
            ->join('frames', 'frames.key', '=', 'topic_frame_settings.frame_key')
            ->join('frames_languages', function ($join) {
                $join->on('frames_languages.frame_id', '=', 'frames.id')
                    ->where('frames_languages.language_id', Nc3Language::language_id_ja);
            })
            ->join('boxes', 'boxes.id', '=', 'frames.box_id')
            ->leftJoin('pages_languages', function ($join) {
                $join->on('pages_languages.page_id', '=', 'boxes.page_id')
                    ->where('pages_languages.language_id', Nc3Language::language_id_ja);
            })
            ->orderBy('topic_frame_settings.frame_key')
            ->get();

        // 空なら戻る
        if ($nc3_topic_frame_settings->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        // NC3新着情報（Topics）のループ
        foreach ($nc3_topic_frame_settings as $nc3_topic_frame_setting) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_topic_frame_setting->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // 新着情報設定
            $whatsnew_ini = "";
            $whatsnew_ini .= "[whatsnew_base]\n";

            // 新着情報の名前は、ブロックタイトルがあればブロックタイトル。なければページ名＋「の新着情報」。
            $whatsnew_name = '無題';
            if (!empty($nc3_topic_frame_setting->page_name)) {
                $whatsnew_name = $nc3_topic_frame_setting->page_name;
            }
            if (!empty($nc3_topic_frame_setting->frame_name)) {
                $whatsnew_name = $nc3_topic_frame_setting->frame_name;
            }

            $whatsnew_ini .= "whatsnew_name = \""  . $whatsnew_name . "\"\n";
            $whatsnew_ini .= "view_pattern = "     . ($nc3_topic_frame_setting->unit_type == 1 ? 0 : 1) . "\n"; // NC3: 0=日数, 1=件数 Connect-CMS: 0=件数, 1=日数
            $whatsnew_ini .= "count = "            . $nc3_topic_frame_setting->display_number . "\n";
            $whatsnew_ini .= "days = "             . $nc3_topic_frame_setting->display_days . "\n";
            $whatsnew_ini .= "rss = "              . $nc3_topic_frame_setting->use_rss_feed . "\n";
            $whatsnew_ini .= "rss_count = "        . $nc3_topic_frame_setting->display_number . "\n";
            $whatsnew_ini .= "view_posted_name = " . $nc3_topic_frame_setting->display_created_user . "\n";
            $whatsnew_ini .= "view_posted_at = "   . $nc3_topic_frame_setting->display_created . "\n";

            // 対象のプラグインを取得（Connect-CMS にまだないものは除外＆ログ出力）
            if ($nc3_topic_frame_setting->select_plugin) {
                $plugin_keys = Nc3TopicFramePlugin::where('frame_key', $nc3_topic_frame_setting->frame_key)->pluck('plugin_key');
                $whatsnew_ini .= "target_plugins = \"" . $this->getCCPluginNamesFromNc3PluginKeys($plugin_keys) . "\"\n";
            } else {
                // 新着対象の全プラグインON
                $plugin_keys = Nc3Plugin::where('display_topics', 1)
                    ->where('language_id', Nc3Language::language_id_ja)
                    ->orderBy('id', 'asc')
                    ->pluck('key');
                $whatsnew_ini .= "target_plugins = \"" . $this->getCCPluginNamesFromNc3PluginKeys($plugin_keys) . "\"\n";
            }

            // 特定のルームの特定のブロックを表示 の移行：未対応
            $whatsnew_ini .= "frame_select = 0\n";

            $whatsnew_ini .= "read_more_use_flag = 1\n";

            // NC3 情報
            $whatsnew_ini .= "\n";
            $whatsnew_ini .= "[source_info]\n";
            $whatsnew_ini .= "whatsnew_block_id = " . $nc3_topic_frame_setting->frame_id . "\n";
            $whatsnew_ini .= "room_id         = "   . $nc3_topic_frame_setting->room_id . "\n";
            $whatsnew_ini .= "plugin_key      = \"topics\"\n";
            $whatsnew_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_topic_frame_setting->created) . "\"\n";
            $whatsnew_ini .= "created_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_topic_frame_setting->created_user) . "\"\n";
            $whatsnew_ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_topic_frame_setting->created_user) . "\"\n";
            $whatsnew_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_topic_frame_setting->modified) . "\"\n";
            $whatsnew_ini .= "updated_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_topic_frame_setting->modified_user) . "\"\n";
            $whatsnew_ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_topic_frame_setting->modified_user) . "\"\n";

            // 新着情報の設定を出力
            $this->storagePut($this->getImportPath('whatsnews/whatsnew_') . $this->zeroSuppress($nc3_topic_frame_setting->frame_id) . '.ini', $whatsnew_ini);
        }
    }

    /**
     * NC3：キャビネット（cabinets）の移行
     */
    private function nc3ExportCabinet($redo)
    {
        $this->putMonitor(3, "Start nc3ExportCabinet.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('cabinets/'));
        }

        // NC3キャビネット（cabinets）を移行する。
        $cabinets_query = Nc3Cabinet::select('cabinets.*', 'blocks.key as block_key', 'blocks.room_id', 'rooms.space_id')
            ->join('blocks', function ($join) {
                $join->on('blocks.id', '=', 'cabinets.block_id')
                    ->where('blocks.plugin_key', 'cabinets');
            })
            ->join('rooms', function ($join) {
                $join->on('rooms.id', '=', 'blocks.room_id')
                    ->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID]);
            });

        $where_cabinet_ids = $this->getMigrationConfig('cabinets', 'nc3_export_where_cabinet_ids');
        if ($where_cabinet_ids) {
            $cabinets_query = $cabinets_query->whereIn('cabinets.id', $where_cabinet_ids);
        }
        $cabinets = $cabinets_query->orderBy('cabinets.id')->get();

        // 空なら戻る
        if ($cabinets->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        // 記事を投稿できる権限, メール通知を受け取る権限
        $block_role_permissions = Nc3BlockRolePermission::getBlockRolePermissionsByBlockKeys($cabinets->pluck('block_key'));

        // 使用言語（日本語・英語）で有効な言語を取得
        $language_ids = Nc3Language::where('is_active', 1)->pluck('id');

        // NC3キャビネット（Cabinet）のループ
        foreach ($cabinets as $cabinet) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($room_ids) && !in_array($cabinet->room_id, $room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // (nc3)投稿権限は１件のみ 投稿権限, 一般
            $post_permission_general_user = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $cabinet->block_key, $cabinet->room_id, 'content_creatable', 'general_user');

            // 権限設定
            // ----------------------------------------------------
            // ※ユーザ (nc3)一般 => (cc)編集者

            $article_post_flag = 1;     // 投稿権限はnc3編集者まで常時チェックON
            $reporter_post_flag = 0;

            // 一般まで
            if ($post_permission_general_user) {
                $reporter_post_flag = 1;
            }

            // キャビネット設定
            $ini = "";
            $ini .= "[cabinet_base]\n";
            $ini .= "cabinet_name = \"" . $cabinet->name . "\"\n";
            $ini .= "upload_max_size = 10485760\n";    // 10M
            $ini .= "article_post_flag = " . $article_post_flag . "\n";
            $ini .= "reporter_post_flag = " . $reporter_post_flag . "\n";

            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "cabinet_id      = " . $cabinet->id . "\n";
            $ini .= "room_id         = " . $cabinet->room_id . "\n";
            $ini .= "plugin_key      = \"cabinets\"\n";
            $ini .= "created_at      = \"" . $this->getCCDatetime($cabinet->created) . "\"\n";
            $ini .= "created_name    = \"" . $cabinet->insert_user_name . "\"\n";
            $ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $cabinet->created_user) . "\"\n";
            $ini .= "updated_at      = \"" . $this->getCCDatetime($cabinet->modified) . "\"\n";
            $ini .= "updated_name    = \"" . $cabinet->update_user_name . "\"\n";
            $ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $cabinet->modified_user) . "\"\n";

            // ファイル情報（CabinetFileは使用言語の影響を受けていたため設定）
            $cabinet_files = Nc3CabinetFile::
                select('cabinet_files.*', 'upload_files.id as upload_id', 'upload_files.extension', 'upload_files.size', 'upload_files.download_count')
                ->leftJoin('upload_files', function ($join) {
                    $join->on('upload_files.content_key', '=', 'cabinet_files.key')
                        ->where('upload_files.plugin_key', 'cabinets');
                })
                ->where('cabinet_files.cabinet_key', $cabinet->key)
                ->where('cabinet_files.is_latest', 1)
                ->whereNotNull('cabinet_files.cabinet_file_tree_parent_id')     // parent_id=nullはtree使用上の一番の親（キャビネット名）になるため除外
                ->whereIn('cabinet_files.language_id', $language_ids)
                ->orderBy('cabinet_files.id', 'asc')
                ->get();
            if (empty($cabinet_files)) {
                continue;
            }

            $tsv = '';
            foreach ($cabinet_files as $index => $cabinet_file) {
                // 親ID
                $cabinet_file_parent = $cabinet_files->firstWhere('cabinet_file_tree_id', $cabinet_file->cabinet_file_tree_parent_id);
                $parent_id = $cabinet_file_parent ? $cabinet_file_parent->id : 0;

                $tsv .= $cabinet_file->id . "\t";                   // [0] ID
                $tsv .= $cabinet->id . "\t";
                $tsv .= $cabinet_file->upload_id . "\t";
                $tsv .= $parent_id . "\t";                          // [3] 親ID
                $tsv .= str_replace("\t", '', $cabinet_file->filename) . "\t";
                $tsv .= $cabinet_file->extension . "\t";
                $tsv .= "\t";                                       // [6] 階層の深さ（インポートで使ってない）
                $tsv .= $cabinet_file->size . "\t";
                $tsv .= $cabinet_file->download_count . "\t";
                $tsv .= $cabinet_file->is_folder . "\t";            // [9] is_folder
                $tsv .= "\t";                                       // [10] 表示順（インポートで使ってない）
                $tsv .= $cabinet->room_id . "\t";
                $tsv .= str_replace("\t", '', $cabinet_file->description) . "\t";
                $tsv .= $this->getCCDatetime($cabinet_file->created)                                  . "\t";   // [13]
                $tsv .= Nc3User::getNc3HandleFromNc3UserId($nc3_users, $cabinet_file->created_user)   . "\t";   // [14]
                $tsv .= Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $cabinet_file->created_user)  . "\t";   // [15]
                $tsv .= $this->getCCDatetime($cabinet_file->modified)                                 . "\t";   // [16]
                $tsv .= Nc3User::getNc3HandleFromNc3UserId($nc3_users, $cabinet_file->modified_user)  . "\t";   // [17]
                $tsv .= Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $cabinet_file->modified_user);          // [18]

                // 最終行は改行コード不要
                if ($index !== ($cabinet_files->count() - 1)) {
                    $tsv .= "\n";
                }
            }
            // キャビネットの設定を出力
            $this->storagePut($this->getImportPath('cabinets/cabinet_') . $this->zeroSuppress($cabinet->id) . '.ini', $ini);
            $tsv = $this->exportStrReplace($tsv, 'cabinets');
            $this->storagePut($this->getImportPath('cabinets/cabinet_') . $this->zeroSuppress($cabinet->id) . '.tsv', $tsv);
        }
    }

    /**
     * NC3：カウンター（access_counters）の移行
     */
    private function nc3ExportCounter($redo)
    {
        $this->putMonitor(3, "Start nc3ExportCounter.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('counters/'));
        }

        // NC3カウンター（access_counters）を移行する。
        $nc3_counters_query = Nc3AccessCounter::select('access_counters.*', 'blocks.key as block_key', 'blocks.room_id', 'blocks_languages.name')
            ->join('blocks', function ($join) {
                $join->on('blocks.key', '=', 'access_counters.block_key')
                    ->where('blocks.plugin_key', 'access_counters');
            })
            ->join('blocks_languages', function ($join) {
                $join->on('blocks_languages.block_id', '=', 'blocks.id')
                    ->where('blocks_languages.language_id', Nc3Language::language_id_ja);
            })
            ->join('rooms', function ($join) {
                $join->on('rooms.id', '=', 'blocks.room_id')
                    ->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID]);
            })
            ->orderBy('access_counters.id');

        $where_counter_ids = $this->getMigrationConfig('counters', 'nc3_export_where_counter_ids');
        if ($where_counter_ids) {
            $nc3_counters_query = $nc3_counters_query->whereIn('access_counters.id', $where_counter_ids);
        }
        $nc3_counters = $nc3_counters_query->get();

        // 空なら戻る
        if ($nc3_counters->isEmpty()) {
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        // NC3カウンター（Counter）のループ
        foreach ($nc3_counters as $nc3_counter) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($room_ids) && !in_array($nc3_counter->room_id, $room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // カウンター設定
            $ini = "";
            $ini .= "[counter_base]\n";
            $ini .= "counter_name = " . $nc3_counter->name . "\n";
            // カウント数
            $ini .= "counter_num = " . $nc3_counter->count . "\n";
            // 文字(前)
            $ini .= "show_char_before = ''\n";
            // 文字(後)
            $ini .= "show_char_after = ''\n";

            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "counter_block_id = " . $nc3_counter->id . "\n";
            $ini .= "room_id         = " . $nc3_counter->room_id . "\n";
            $ini .= "plugin_key      = \"access_counters\"\n";
            $ini .= "created_at      = \"" . $this->getCCDatetime($nc3_counter->created) . "\"\n";
            $ini .= "created_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_counter->created_user) . "\"\n";
            $ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_counter->created_user) . "\"\n";
            $ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_counter->modified) . "\"\n";
            $ini .= "updated_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_counter->modified_user) . "\"\n";
            $ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_counter->modified_user) . "\"\n";

            // カウンターの設定を出力
            $this->storagePut($this->getImportPath('counters/counter_') . $this->zeroSuppress($nc3_counter->id) . '.ini', $ini);
        }
    }

    /**
     * NC3：カレンダー（calendars）の移行
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

        // NC3全会員 （communityのルートroom_idが全会員のroom_id）
        $nc3_space = Nc3Space::find(Nc3Space::COMMUNITY_SPACE_ID);
        $all_users_room_id = $nc3_space->room_id_root;

        // NC3ルーム一覧を移行する。（全会員は除外）
        $nc3_rooms_query = Nc3Room::select('rooms.*', 'rooms_languages.name as room_name')
            ->join('rooms_languages', function ($join) {
                $join->on('rooms_languages.room_id', 'rooms.id')
                    ->where('rooms_languages.language_id', Nc3Language::language_id_ja);
            })
            ->where('rooms.id', '!=', $all_users_room_id)
            ->orderBy('rooms.id');

        $nc3_export_private_room_calendar = $this->getMigrationConfig('calendars', 'nc3_export_private_room_calendar');
        if (empty($nc3_export_private_room_calendar)) {
            // プライベートルームをエクスポート（=移行）しない
            $nc3_rooms_query = $nc3_rooms_query->whereIn('space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID]);

        } else {
            // プライベートルームをエクスポート（=移行）する
            $nc3_rooms_query = $nc3_rooms_query->whereIn('space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID, Nc3Space::PRIVATE_SPACE_ID]);

        }
        $nc3_rooms = $nc3_rooms_query->get();

        // (nc3) calendarsはなぜかblock_key=1ルームとして設定を保持しているため取得
        $nc3_calendars = Nc3Calendar::select('calendars.*', 'blocks.room_id')
            ->join('blocks', function ($join) {
                $join->on('blocks.key', 'calendars.block_key')
                    ->where('blocks.plugin_key', 'calendars');
            })
            ->get();

        $nc3_export_room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        // NC3権限設定
        $nc3_room_role_permissions = Nc3RoomRolePermission::getRoomRolePermissionsByRoomIds($nc3_rooms->pluck('id'));

        // ブロック設定
        $block_settings = Nc3BlockSetting::whereIn('block_key', $nc3_calendars->pluck('block_key'))->get();

        // ルームでループ（NC3カレンダーはルーム単位でエクスポート）
        foreach ($nc3_rooms as $nc3_room) {

            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($nc3_export_room_ids) && !in_array($nc3_room->id, $nc3_export_room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // カレンダー設定
            $ini = "";
            $ini .= "[calendar_base]\n";

            // 権限設定
            // ----------------------------------------------------
            // ※ユーザ (nc3)一般 => (cc)編集者
            // カレンダーの権限はサイトで１セット。だけどなぜか 一般の権限設定は block_role_permission にあるためそっちも見る。
            $nc3_room_role_permission = $nc3_room_role_permissions->where('permission', 'content_creatable')
                ->where('role_key', 'general_user')
                ->firstWhere('room_id', $nc3_room->id);

            $article_post_flag = 1;     // 投稿権限はnc3編集者まで常時チェックON
            $reporter_post_flag = 0;

            if (empty($nc3_room_role_permission)) {
                $reporter_post_flag = 0;
            } else {
                $reporter_post_flag = $nc3_room_role_permission->block_role_permission_value ? $nc3_room_role_permission->block_role_permission_value : $nc3_room_role_permission->value;
            }

            // 承認あり
            // パブリック　：デフォ承認ありっぽい
            // コミュニティ：デフォ承認なしっぽい
            // 全会員　　　：デフォ承認ありっぽい。（全会員は room_id=3, Nc3Space::COMMUNITY_SPACE_IDのルートroom_id）
            // $roomBlock[$this->alias]['use_workflow'] = Hash::get($roomBlock, 'Room.need_approval'); がデフォ値。
            // 記事承認（content_publishable）はルーム管理者・編集長固定. 編集者は承認必要

            // room_idからcalendarsのblock_keyを取り出し
            $nc3_calendar = $nc3_calendars->firstWhere('room_id', $nc3_room->id) ?? new Nc3Calendar();
            $use_workflow = Nc3BlockSetting::getNc3BlockSettingValue($block_settings, $nc3_calendar->block_key, 'use_workflow', $nc3_room->need_approval);

            $ini .= "\n";
            $ini .= "[calendar_manage]\n";
            $ini .= "article_post_flag      = {$article_post_flag}\n";
            $ini .= "article_approval_flag  = 0\n";                                 // 編集長=モデは承認不要
            $ini .= "reporter_post_flag     = {$reporter_post_flag}\n";
            $ini .= "reporter_approval_flag = {$use_workflow}\n";                   // 承認ありなら編集者承認ON

            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "room_id = " . $nc3_room->id . "\n";
            // ルーム名
            $ini .= "room_name = '" . $nc3_room->room_name . "'\n";
            // スペースID
            $ini .= "space_id = " . $nc3_room->space_id . "\n";
            $ini .= "plugin_key = \"calendars\"\n";


            // カラムのヘッダー及びTSV 行毎の枠準備
            $tsv_header = "post_id" . "\t" . "title" . "\t" . "allday_flag" . "\t" . "start_date" . "\t" . "start_time" . "\t" . "end_date" . "\t" . "end_time" . "\t" .
                "location" . "\t" . "contact" . "\t" . "body" . "\t" . "rrule" . "\t" .
                "created_at" . "\t" . "created_name" . "\t" . "insert_login_id" . "\t" . "updated_at" . "\t" . "updated_name" . "\t" . "update_login_id" . "\t" .
                // CC 状態
                "status";

            $tsv_cols['post_id'] = "";
            $tsv_cols['title'] = "";
            $tsv_cols['allday_flag'] = "";
            $tsv_cols['start_date'] = "";
            $tsv_cols['start_time'] = "";
            $tsv_cols['end_date'] = "";
            $tsv_cols['end_time'] = "";

            // 場所
            $tsv_cols['location'] = "";
            // 連絡先
            $tsv_cols['contact'] = "";
            // 内容
            $tsv_cols['body'] = "";
            // 繰り返し条件
            $tsv_cols['rrule'] = "";

            // 登録日・更新日等
            $tsv_cols['created_at'] = "";
            $tsv_cols['created_name'] = "";
            $tsv_cols['insert_login_id'] = "";
            $tsv_cols['updated_at'] = "";
            $tsv_cols['updated_name'] = "";
            $tsv_cols['update_login_id'] = "";

            // CC 状態
            $tsv_cols['status'] = "";

            // カレンダーの予定
            $calendar_events = Nc3CalendarEvent::select('calendar_events.*', 'calendar_rrules.rrule')
                ->leftJoin('calendar_rrules', function ($join) {
                    $join->on('calendar_rrules.id', '=', 'calendar_events.calendar_rrule_id');
                })
                ->where('calendar_events.is_latest', 1)
                ->where('calendar_events.room_id', $nc3_room->id)
                ->orderBy('calendar_events.room_id', 'asc')
                ->get();

            // カラムデータのループ
            Storage::delete($this->getImportPath('calendars/calendar_room_') . $this->zeroSuppress($nc3_room->id) . '.tsv');

            $tsv = '';
            $tsv .= $tsv_header . "\n";

            foreach ($calendar_events as $calendar_event) {

                // 初期化
                $tsv_record = $tsv_cols;

                $tsv_record['post_id'] = $calendar_event->id;
                $tsv_record['title']       = $calendar_event->title;
                $tsv_record['allday_flag'] = $calendar_event->is_allday;

                // 予定開始日時
                // Carbon()で処理。必須値のため基本値がある想定で、timezone_offset で時間加算して予定時間を算出
                $dtstart = (new Carbon($calendar_event->dtstart))->addHour($calendar_event->timezone_offset);
                $tsv_record['start_date'] = $dtstart->format('Y-m-d');
                $tsv_record['start_time'] = $dtstart->format('H:i:s');

                // 予定終了日時
                // Carbon()で処理。必須値のため基本値がある想定で、timezone_offset で時間加算して予定時間を算出
                $dtend = (new Carbon($calendar_event->dtend))->addHour($calendar_event->timezone_offset);
                if ($calendar_event->is_allday == 1) {
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
                    $dtend = $dtend->subDay();
                } elseif ($dtend->format('H:i:s') == '00:00:00') {
                    // 全日以外で終了日時が0:00の変換対応. -1分する。
                    // ※ 例えばNC3の「時間指定」で10:00～24:00という予定に対応して、10:00～23:59に終了時間を変換する

                    // -1分
                    $dtend = $dtend->subMinute();
                }
                $tsv_record['end_date'] = $dtend->format('Y-m-d');
                $tsv_record['end_time'] = $dtend->format('H:i:s');

                // 場所
                $tsv_record['location'] = $calendar_event->location;
                // 連絡先
                $tsv_record['contact'] = $calendar_event->contact;
                // 内容 [WYSIWYG]
                $tsv_record['body'] = $this->nc3Wysiwyg(null, null, null, null, $calendar_event->description, 'calendars');
                // 繰り返し条件
                $tsv_record['rrule'] = $calendar_event->rrule;
                // 登録日・更新日等
                $tsv_record['created_at']      = $this->getCCDatetime($calendar_event->created);
                $tsv_record['created_name']    = Nc3User::getNc3HandleFromNc3UserId($nc3_users, $calendar_event->created_user);
                $tsv_record['insert_login_id'] = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $calendar_event->created_user);
                $tsv_record['updated_at']      = $this->getCCDatetime($calendar_event->modified);
                $tsv_record['updated_name']    = Nc3User::getNc3HandleFromNc3UserId($nc3_users, $calendar_event->modified_user);
                $tsv_record['update_login_id'] = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $calendar_event->modified_user);
                $tsv_record['status']          = $calendar_event->status;

                $tsv .= implode("\t", $tsv_record) . "\n";
            }

            // データ行の書き出し
            $tsv = $this->exportStrReplace($tsv, 'calendars');
            $this->storageAppend($this->getImportPath('calendars/calendar_room_') . $this->zeroSuppress($nc3_room->id) . '.tsv', $tsv);

            // カレンダーの設定を出力
            $this->storagePut($this->getImportPath('calendars/calendar_room_') . $this->zeroSuppress($nc3_room->id) . '.ini', $ini);
        }


        // NC3全会員

        // ルーム指定があれば、指定されたルームのみ処理する。
        if (empty($nc3_export_room_ids) || in_array($all_users_room_id, $nc3_export_room_ids)) {

            // NC3ルーム（全会員）
            $nc3_room = Nc3Room::find($all_users_room_id);

            // 権限設定
            // ----------------------------------------------------
            // ※ユーザ (nc3)一般 => (cc)編集者
            // カレンダーの権限はサイトで１セット。だけどなぜか 一般の権限設定は block_role_permission にあるためそっちも見る。
            $nc3_room_role_permission = $nc3_room_role_permissions->where('permission', 'content_creatable')
                ->where('role_key', 'general_user')
                ->firstWhere('room_id', $nc3_room->id);

            $article_post_flag = 1;     // 投稿権限はnc3編集者まで常時チェックON
            $reporter_post_flag = 0;

            if (empty($nc3_room_role_permission)) {
                $reporter_post_flag = 0;
            } else {
                $reporter_post_flag = $nc3_room_role_permission->block_role_permission_value ? $nc3_room_role_permission->block_role_permission_value : $nc3_room_role_permission->value;
            }

            // 承認あり
            // room_idからcalendarsのblock_keyを取り出し
            $nc3_calendar = $nc3_calendars->firstWhere('room_id', $nc3_room->id) ?? new Nc3Calendar();
            $use_workflow = Nc3BlockSetting::getNc3BlockSettingValue($block_settings, $nc3_calendar->block_key, 'use_workflow', $nc3_room->need_approval);

            // カレンダー設定
            $ini = "";
            $ini .= "[calendar_base]\n";
            $ini .= "\n";
            $ini .= "[calendar_manage]\n";
            $ini .= "article_post_flag      = {$article_post_flag}\n";
            $ini .= "article_approval_flag  = 0\n";                                 // 編集長=モデは承認不要
            $ini .= "reporter_post_flag     = {$reporter_post_flag}\n";
            $ini .= "reporter_approval_flag = {$use_workflow}\n";                   // 承認ありなら編集者承認ON

            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "room_id = " . $nc3_room->id . "\n";
            // ルーム名
            $ini .= "room_name = '全会員'\n";
            // スペースID
            $ini .= "space_id = " . $nc3_room->space_id . "\n";
            $ini .= "plugin_key = \"calendars\"\n";

            // カレンダーの設定を出力
            $this->storagePut($this->getImportPath('calendars/calendar_room_') . $this->zeroSuppress($all_users_room_id) . '.ini', $ini);
        }


        // NC3カレンダーフレーム（インポート時にframe_idからroom_idを取得するために出力）
        $nc3_calendar_frame_settings = Nc3CalendarFrameSetting::select('calendar_frame_settings.*', 'frames.id as frame_id', 'frames.room_id')
            ->leftJoin('frames', function ($join) {
                $join->on('frames.key', '=', 'calendar_frame_settings.frame_key');
            })
            ->orderBy('calendar_frame_settings.id')
            ->get();

        // 空なら戻る
        if ($nc3_calendar_frame_settings->isEmpty()) {
            return;
        }

        // NC3カレンダーブロックのループ
        foreach ($nc3_calendar_frame_settings as $nc3_calendar_frame_setting) {

            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($nc3_export_room_ids) && !in_array($nc3_room->id, $nc3_export_room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // NC3 情報
            $ini = "";
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "calendar_block_id = " . $nc3_calendar_frame_setting->frame_id . "\n";
            $ini .= "room_id           = " . $nc3_calendar_frame_setting->room_id . "\n";
            $ini .= "plugin_key        = \"calendars\"\n";

            // カレンダーの設定を出力
            $this->storagePut($this->getImportPath('calendars/calendar_block_') . $this->zeroSuppress($nc3_calendar_frame_setting->frame_id) . '.ini', $ini);
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
            $ini .= "plugin_key = \"slides\"\n";
            $ini .= "created_at      = \"" . $this->getCCDatetime($nc3_slideshow->created) . "\"\n";
            $ini .= "created_name    = \"" . $nc3_slideshow->insert_user_name . "\"\n";
            $ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_slideshow->created_user) . "\"\n";
            $ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_slideshow->modified) . "\"\n";
            $ini .= "updated_name    = \"" . $nc3_slideshow->update_user_name . "\"\n";
            $ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_slideshow->modified_user) . "\"\n";

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
            $ini .= "plugin_key = \"simplemovie\"\n";
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
            $ini .= "plugin_key = \"reservation\"\n";

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
            $ini .= "plugin_key = \"reservation\"\n";

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
                $tsv_record['insert_login_id'] = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $reservation_reserve->created_user);
                $tsv_record['updated_at'] = $this->getCCDatetime($reservation_reserve->modified);
                $tsv_record['updated_name'] = $reservation_reserve->update_user_name;
                $tsv_record['update_login_id'] = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $reservation_reserve->modified_user);

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
            $ini .= "plugin_key = \"reservation\"\n";

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
            $photoalbum_ini .= "plugin_key = \"photoalbum\"\n";
            $photoalbum_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_photoalbum->created) . "\"\n";
            $photoalbum_ini .= "created_name    = \"" . $nc3_photoalbum->insert_user_name . "\"\n";
            $photoalbum_ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum->created_user) . "\"\n";
            $photoalbum_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_photoalbum->modified) . "\"\n";
            $photoalbum_ini .= "updated_name    = \"" . $nc3_photoalbum->update_user_name . "\"\n";
            $photoalbum_ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum->modified_user) . "\"\n";

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
                $photoalbum_ini .= "insert_login_id            = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_alubum->created_user) . "\"\n";
                $photoalbum_ini .= "updated_at                 = \"" . $this->getCCDatetime($nc3_photoalbum_alubum->modified) . "\"\n";
                $photoalbum_ini .= "updated_name               = \"" . $nc3_photoalbum_alubum->update_user_name . "\"\n";
                $photoalbum_ini .= "update_login_id            = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_alubum->modified_user) . "\"\n";
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
                    $tsv_record['insert_login_id']   = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_photo->created_user);
                    $tsv_record['updated_at']        = $this->getCCDatetime($nc3_photoalbum_photo->modified);
                    $tsv_record['updated_name']      = $nc3_photoalbum_photo->update_user_name;
                    $tsv_record['update_login_id']   = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_photo->modified_user);

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
                $slide_ini .= "plugin_key = \"photoalbum\"\n";
                $slide_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_photoalbum_block->created) . "\"\n";
                $slide_ini .= "created_name    = \"" . $nc3_photoalbum_block->insert_user_name . "\"\n";
                $slide_ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_block->created_user) . "\"\n";
                $slide_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_photoalbum_block->modified) . "\"\n";
                $slide_ini .= "updated_name    = \"" . $nc3_photoalbum_block->update_user_name . "\"\n";
                $slide_ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_block->modified_user) . "\"\n";

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
     * NC3：ページ内のフレームをループ
     */
    private function nc3Frame(Nc3Page $nc3_page, int $new_page_index, Nc3Page $nc3_top_page)
    {
        // 指定されたページ内のブロックを取得
        $nc3_frames_query = Nc3Frame::
            select(
                'frames.*',
                'frames_languages.name as frame_name',
                'frames_languages.language_id',
                'boxes.container_type',
                'blocks.key as block_key',
                'blocks.public_type',
                'blocks.publish_start',
                'blocks.publish_end'
            )
            ->join('boxes', 'boxes.id', '=', 'frames.box_id')
            ->join('frames_languages', function ($join) {
                $join->on('frames_languages.frame_id', '=', 'frames.id');
            })
            ->join('languages', function ($join) {
                $join->on('languages.id', '=', 'frames_languages.language_id')
                    ->where('languages.is_active', 1);  // 使用言語（日本語・英語）で有効な言語を取得
            })
            ->leftJoin('blocks', 'blocks.id', '=', 'frames.block_id')
            ->where('boxes.page_id', $nc3_page->id)
            ->where('frames.is_deleted', 0);

        // 対象外のフレームがあれば加味する。
        $export_ommit_frames = $this->getMigrationConfig('frames', 'export_ommit_frames');
        if (!empty($export_ommit_frames)) {
            $nc3_frames_query->whereNotIn('frames.id', $export_ommit_frames);
        }

        // メニューが対象外なら除外する。
        $export_ommit_menu = $this->getMigrationConfig('menus', 'export_ommit_menu');
        if ($export_ommit_menu) {
            $nc3_frames_query->where('frames.plugin_key', '<>', 'menus');
        }

        $nc3_frames = $nc3_frames_query
            ->orderBy('boxes.space_id')
            ->orderBy('boxes.room_id')
            ->orderBy('boxes.page_id')
            ->orderBy('frames.box_id')
            ->orderBy('frames.weight')
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
        //  page_containers(どのエリアが見えてる(is_published = 1)・見えてないか) ->
        //    boxes_page_containers(全エリア(page_id = 999 and is_published = 1)のbox特定) ->
        //      box ->
        //        frame ->
        //          block

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
                ->orderBy('frames.box_id')
                ->orderBy('frames.weight')
                ->get();

            // 共通部分を frame 設定に追加する。
            foreach ($nc3_common_frames as $nc3_common_frame) {
                // frame 設定に追加
                $nc3_frames->push($nc3_common_frame);
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

            } elseif ($nc3_frame->plugin_key == 'calendars') {
                // NC3カレンダーフレーム（どう見せるか、だけ。ここ無くても予定データある）を移行する。
                $nc3_calendar_frame_setting = Nc3CalendarFrameSetting::where('frame_key', $nc3_frame->key)->first();

                // frame_design 変換 (key:nc3)display_type => (value:cc)template
                // (NC3)初期値 = 月表示（縮小）= 1
                // (CC) 初期値 = 月表示（大）= default
                $display_type_to_frame_designs = [
                    1 => 'small_month', // 1:月表示（縮小）
                    2 => 'default',     // 2:月表示（拡大）
                    3 => 'default',     // 3:週表示
                    4 => 'day',         // 4:日表示
                    5 => 'day',         // 5:スケジュール（時間順）
                    6 => 'day',         // 6:スケジュール（会員順）
                ];
                $frame_design = $display_type_to_frame_designs[$nc3_calendar_frame_setting->display_type] ?? 'default';
                $frame_ini .= "template = \"" . $frame_design . "\"\n";
            } else {
                $frame_ini .= "template = \"default\"\n";
            }

            // 公開設定
            // (key:nc3)public_type => (value:cc)content_open_type
            $convert_content_open_types = [
                Nc3Block::public_type_open    => ContentOpenType::always_open,
                Nc3Block::public_type_close   => ContentOpenType::always_close,
                Nc3Block::public_type_limited => ContentOpenType::limited_open,
            ];
            $content_open_type = $convert_content_open_types[$nc3_frame->public_type] ?? ContentOpenType::always_open;
            $frame_ini .= "content_open_type = \"{$content_open_type}\"\n";

            if ($content_open_type == ContentOpenType::limited_open) {
                $frame_ini .= "content_open_date_from = \"" . $this->getCCDatetime($nc3_frame->publish_start) . "\"\n";
                $frame_ini .= "content_open_date_to = \"" . $this->getCCDatetime($nc3_frame->publish_end) . "\"\n";
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

            // モジュールに紐づくメインのデータのID
            $frame_ini .= $this->nc3FrameMainDataId($nc3_frame);

            // overrideNc3Frame()関連設定
            if (!empty($nc3_frame->display_sequence)) {
                $frame_ini .= "\n";
                $frame_ini .= "[frame_option]\n";
                $frame_ini .= "display_sequence = " . $nc3_frame->display_sequence . "\n";
            }

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

            // フレームのプラグインデータをエクスポート
            $this->nc3FrameExport($nc3_frame, $new_page_index, $frame_index_str);

            // Connect-CMS のプラグイン名の取得
            $plugin_name = $this->nc3GetPluginName($nc3_frame->plugin_key);
            // [?] ここにsearchsのみプラグイン指定されてる理由はなんだろ？
            if ($plugin_name == 'Development' || $plugin_name == 'Abolition' || $plugin_name == 'searchs') {
                // 移行できなかったNC3プラグイン
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
            // リンクリストはNC2と違い、プラグイン固有のデータまとめテーブルがないため、ブロックテーブル参照
            $nc3_block = Nc3Block::find($nc3_frame->block_id);
            // ブロックがあり、リンクリストがない場合は対象外
            if (!empty($nc3_block)) {
                // linklist_idないため、block_idで代用
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
                // block_idないため、frame_idで代用
                $ret = "whatsnew_block_id = \"" . $this->zeroSuppress($nc3_frame->id) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'cabinets') {
            $nc3_cabinet = Nc3Cabinet::where('block_id', $nc3_frame->block_id)->first();
            // ブロックがあり、キャビネットがない場合は対象外
            if (!empty($nc3_cabinet)) {
                $ret = "cabinet_id = \"" . $this->zeroSuppress($nc3_cabinet->id) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'menus') {
            // メニューの非表示設定を加味する。
            $nc3_menu_frame_pages_hidden = Nc3MenuFramePage::select('menu_frames_pages.*', 'pages.sort_key')
                ->join('pages', 'pages.id', '=', 'menu_frames_pages.page_id')
                ->where("menu_frames_pages.frame_key", $nc3_frame->key)
                ->where("menu_frames_pages.is_hidden", 1)   // 1:非表示
                ->orderBy('menu_frames_pages.page_id', 'asc')
                ->get();
            if ($nc3_menu_frame_pages_hidden->isEmpty()) {
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
                foreach ($nc3_menu_frame_pages_hidden as $nc3_menu_frame_page_hidden) {
                    // 下層ページを含めて取得
                    $ommit_pages = Nc3Page::where('sort_key', 'like', $nc3_menu_frame_page_hidden->sort_key . '%')->get();
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
                    $ret .= "ommit_page_ids_source = \"" . implode(",", $ommit_nc3_pages) . "\"\n";
                }
            }
        } elseif ($nc3_frame->plugin_key == 'access_counters') {
            $nc3_counter = Nc3AccessCounter::where('block_key', $nc3_frame->block_key)->first();
            // ブロックがあり、カウンターがない場合は対象外
            if (!empty($nc3_counter)) {
                // NC3カウンターにblock_idはないため、counter_idで代用
                $ret = "counter_block_id = \"" . $this->zeroSuppress($nc3_counter->id) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'calendars') {
            $nc3_calendar_frame_setting = Nc3CalendarFrameSetting::where('frame_key', $nc3_frame->key)->first();
            // 設定があり、カレンダーがない場合は対象外
            if (!empty($nc3_calendar_frame_setting)) {
                // block_idないため、frame_idで代用
                $ret = "calendar_block_id = \"" . $this->zeroSuppress($nc3_frame->id) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'reservations') {
            $nc3_reservation_frame_setting = Nc3ReservationFrameSetting::where('frame_key', $nc3_frame->key)->first();
            // ブロックがあり、施設予約がない場合は対象外
            if (!empty($nc3_reservation_frame_setting)) {
                // block_idないため、frame_idで代用
                $ret = "reservation_block_id = \"" . $this->zeroSuppress($nc3_frame->id) . "\"\n";
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
                    $ret = "slideshows_block_id = \"" . $this->zeroSuppress($nc3_frame->id) . "\"\n";
                } else {
                    $ret = "photoalbum_id = \"" . $this->zeroSuppress($nc3_photoalbum->id) . "\"\n";
                }
            }
        }
        return $ret;
    }

    /**
     * NC3：ページ内のフレームに配置されているプラグインのエクスポート。
     * プラグインごとのエクスポート処理に振り分け。
     */
    private function nc3FrameExport(Nc3Frame $nc3_frame, int $new_page_index, string $frame_index_str): void
    {
        // Connect-CMS のプラグイン名の取得
        $plugin_name = $this->nc3GetPluginName($nc3_frame->plugin_key);

        // モジュールごとに振り分け

        // プラグインで振り分け
        if ($plugin_name == 'contents') {
            // 固定記事（お知らせ）
            $this->nc3FrameExportContents($nc3_frame, $new_page_index, $frame_index_str);
        } elseif ($plugin_name == 'menus') {
            // メニュー
            // 今のところ、メニューの追加設定はなし。
        } elseif ($plugin_name == 'databases') {
            // データベース
            $this->nc3FrameExportDatabases($nc3_frame, $new_page_index, $frame_index_str);
        } elseif ($plugin_name == 'bbses') {
            // 掲示板
            $this->nc3FrameExportBbses($nc3_frame, $new_page_index, $frame_index_str);
        } elseif ($plugin_name == 'linklists') {
            // リンクリスト
            $this->nc3FrameExportLinklists($nc3_frame, $new_page_index, $frame_index_str);
        } elseif ($plugin_name == 'counters') {
            // カウンター
            $this->nc3FrameExportCounters($nc3_frame, $new_page_index, $frame_index_str);
        }
    }

    /**
     * NC3：固定記事（お知らせ）のエクスポート
     */
    private function nc3FrameExportContents(Nc3Frame $nc3_frame, int $new_page_index, string $frame_index_str): void
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
        $contents_ini .= "created_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $announcement->created_user) . "\"\n";
        $contents_ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $announcement->created_user) . "\"\n";
        $contents_ini .= "updated_at      = \"" . $this->getCCDatetime($announcement->modified) . "\"\n";
        $contents_ini .= "updated_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $announcement->modified_user) . "\"\n";
        $contents_ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $announcement->modified_user) . "\"\n";
        $this->storageAppend($save_folder . "/" . $ini_filename, $contents_ini);
    }

    /**
     * NC3：汎用データベースのブロック特有部分のエクスポート
     */
    private function nc3FrameExportDatabases(Nc3Frame $nc3_frame, int $new_page_index, string $frame_index_str): void
    {
        // NC3 フレーム設定の取得
        $nc3_multidatabase_frame_setting = Nc3MultidatabaseFrameSetting::where('frame_key', $nc3_frame->key)->first();
        if (empty($nc3_multidatabase_frame_setting)) {
            return;
        }

        $ini_filename = "frame_" . $frame_index_str . '.ini';

        $save_folder = $this->getImportPath('pages/') . $this->zeroSuppress($new_page_index);

        $frame_ini = "[database]\n";
        $frame_ini .= "use_search_flag = 1\n";
        $frame_ini .= "use_select_flag = 1\n";
        $frame_ini .= "use_sort_flag = \"\"\n";

        // (nc3) デフォルトの表示順
        // 0            : 指定なし
        // created      : 作成日時(昇順)
        // created_desc : 作成日時(降順)
        // modified     : 更新日時(昇順)
        // modified_desc: 更新日時(降順)
        // ※任意項目例
        // value1      : タイトル(昇順)
        // value1_desc : タイトル(降順)

        // (cc) ※任意項目例
        // 1_asc       : カラムID＋_asc(昇順)
        // 1_desc      : カラムID＋_desc(降順)

        // nc3任意項目ソート
        $multidatabase_metadatas = Nc3Multidatabase::select('multidatabase_metadatas.*')
            ->join('multidatabase_metadatas', function ($join) {
                // 日本語のみでもmultidatabase_metadatas.language_idは1(英語)でも表示されたため、whereに含めない
                $join->on('multidatabase_metadatas.multidatabase_id', '=', 'multidatabases.id');
            })
            ->where('multidatabases.block_id', $nc3_frame->block_id)
            ->where('multidatabase_metadatas.is_sortable', 1)    // ソート対象とするか 0:対象外,1:対象
            ->get();

        // (NC3)default_sort_type -> (Connect)default_sort_flag
        $convert_default_sort_flags = [
            0               => '',  // 指定なし
            'created'       => DatabaseSortFlag::created_asc,
            'created_desc'  => DatabaseSortFlag::created_desc,
            'modified'      => DatabaseSortFlag::updated_asc,
            'modified_desc' => DatabaseSortFlag::updated_desc,
        ];
        // 任意項目ソート
        foreach ($multidatabase_metadatas as $multidatabase_metadata) {
            // エクスポート時では任意項目のConnectソート置換ができないため、仮値をセットしてインポート時に置換する
            $convert_default_sort_flags["value{$multidatabase_metadata->col_no}"] = $multidatabase_metadata->id . '|' . DatabaseSortFlag::order_asc;
            $convert_default_sort_flags["value{$multidatabase_metadata->col_no}_desc"] = $multidatabase_metadata->id . '|' . DatabaseSortFlag::order_desc;
        }

        $default_sort_flag = $convert_default_sort_flags[$nc3_multidatabase_frame_setting->default_sort_type] ?? null;
        if (is_null($default_sort_flag)) {
            $this->putError(3, 'データベースのソートが未対応順', "frame_key = {$nc3_multidatabase_frame_setting->frame_key}|nc3_multidatabase_frame_setting.default_sort_type = {$nc3_multidatabase_frame_setting->default_sort_type}");
        }

        $frame_ini .= "default_sort_flag = \"" . $default_sort_flag . "\"\n";
        $frame_ini .= "view_count = "          . $nc3_multidatabase_frame_setting->content_per_page . "\n";
        $this->storageAppend($save_folder . "/" . $ini_filename, $frame_ini);
    }

    /**
     * NC3：掲示板のフレーム特有部分のエクスポート
     */
    private function nc3FrameExportBbses(Nc3Frame $nc3_frame, int $new_page_index, string $frame_index_str): void
    {
        // NC3 フレーム設定の取得
        $nc3_bbs_frame_setting = Nc3BbsFrameSetting::where('frame_key', $nc3_frame->key)->first();
        if (empty($nc3_bbs_frame_setting)) {
            return;
        }

        $ini_filename = "frame_" . $frame_index_str . '.ini';

        $save_folder = $this->getImportPath('pages/') . $this->zeroSuppress($new_page_index);

        // 表示形式 変換
        // (nc) all:全件一覧, root:根記事一覧,flat:フラット
        // (cc) 0:フラット形式,1:ツリー形式
        // (key:nc3)display_type => (value:cc)view_format
        $convert_view_formats = [
            'all' => 1,
            'root' => 1,
            'flat' => 0,
        ];
        if (isset($convert_view_formats[$nc3_bbs_frame_setting->display_type])) {
            $view_format = $convert_view_formats[$nc3_bbs_frame_setting->display_type];
        } else {
            $view_format = '';
            $this->putError(3, '掲示板の表示形式が未対応の形式', "frame_key = {$nc3_bbs_frame_setting->frame_key}|nc3_bbs_frame_setting.display_type = {$nc3_bbs_frame_setting->display_type}");
        }

        $frame_ini = "[bbs]\n";
        $frame_ini .= "view_count = {$nc3_bbs_frame_setting->articles_per_page}\n";
        $frame_ini .= "view_format = {$view_format}\n";
        $this->storageAppend($save_folder . "/"     . $ini_filename, $frame_ini);
    }

    /**
     * NC3：リンクリストのフレーム特有部分のエクスポート
     */
    private function nc3FrameExportLinklists(Nc3Frame $nc3_frame, int $new_page_index, string $frame_index_str): void
    {
        // NC3 フレーム設定の取得
        $nc3_link_frame_setting = Nc3LinkFrameSetting::where('frame_key', $nc3_frame->key)->first();
        if (empty($nc3_link_frame_setting)) {
            return;
        }

        $ini_filename = "frame_" . $frame_index_str . '.ini';

        $save_folder = $this->getImportPath('pages/') . $this->zeroSuppress($new_page_index);

        // (NC3)list_style リストマーカー -> (Connect)type 表示形式 変換
        // (nc3) @see app\Plugin\Links\Model\LinkFrameSetting.php
        // (nc3) @see app\Plugin\Links\webroot\img\mark
        $convert_types = [
            ''            => LinklistType::none,
            'disc'        => LinklistType::black_circle,
            'circle'      => LinklistType::white_circle,
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
        $type = $convert_types[$nc3_link_frame_setting->list_style] ?? LinklistType::none;

        $frame_ini = "[linklist]\n";
        // $frame_ini .= "view_count = 10\n";
        $frame_ini .= "type = {$type}\n";
        $this->storageAppend($save_folder . "/"     . $ini_filename, $frame_ini);
    }

    /**
     * NC3：カウンターのフレーム特有部分のエクスポート
     */
    private function nc3FrameExportCounters(Nc3Frame $nc3_frame, int $new_page_index, string $frame_index_str): void
    {
        // NC3 フレーム設定の取得（データなければ、badge_secondaryを指定）
        $access_counter_frame_setting = Nc3AccessCounterFrameSetting::where('frame_key', $nc3_frame->key)->firstOrNew([]);

        $ini_filename = "frame_" . $frame_index_str . '.ini';

        $save_folder = $this->getImportPath('pages/') . $this->zeroSuppress($new_page_index);

        // (NC3)display_type -> (Connect)design_type 変換
        // (nc3) @see app\Plugin\AccessCounters\Model\AccessCounterFrameSetting.php
        $convert_design_types = [
            Nc3AccessCounterFrameSetting::display_type_default => CounterDesignType::badge_secondary,
            Nc3AccessCounterFrameSetting::display_type_primary => CounterDesignType::badge_primary,
            Nc3AccessCounterFrameSetting::display_type_success => CounterDesignType::badge_success,
            Nc3AccessCounterFrameSetting::display_type_info    => CounterDesignType::badge_info,
            Nc3AccessCounterFrameSetting::display_type_warning => CounterDesignType::badge_warning,
            Nc3AccessCounterFrameSetting::display_type_danger  => CounterDesignType::badge_danger,
        ];
        $design_type = $convert_design_types[$access_counter_frame_setting->display_type] ?? CounterDesignType::badge_secondary;

        $frame_ini  = "[counter]\n";
        $frame_ini .= "design_type = {$design_type}\n";
        $this->storageAppend($save_folder . "/"     . $ini_filename, $frame_ini);
    }

    /**
     * コンテンツのクリーニング
     */
    private function cleaningContent($content, $nc3_plugin_key)
    {
        // 改行コードが含まれる場合があるので置換
        $content = str_replace(array("\r", "\n"), '', $content);

        $plugin_name = $this->nc3GetPluginName($nc3_plugin_key);

        // style から除去する属性の取得
        $clear_styles = $this->getMigrationConfig($plugin_name, 'export_clear_style');
        if ($clear_styles) {
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
        }

        // 不要な <span> のみで属性のないものがあれば消したいが、無効な<span> に対応する </span> のみ抜き出すのが難しく、
        // 今回は課題として残しておく。

        // imgタグの不要属性 除去
        // <img class="img-responsive nc3-img nc3-img-block" title="" src="../../uploads/upload_00059.jpg" alt="" data-size="big" data-position="" data-imgid="59" />

        $pattern = '/<img.*?(data-size\s*=\s*[\"|\'].*?[\"|\']).*?>/i';
        $match_cnt = preg_match_all($pattern, $content, $matches);
        if ($match_cnt) {
            // [1] に中身のみ入ってくる。
            foreach ($matches[1] as $match) {
                // 除去
                $content = str_replace($match . ' ', '', $content);
            }
        }

        $pattern = '/<img.*?(data-position\s*=\s*[\"|\'].*?[\"|\']).*?>/i';
        $match_cnt = preg_match_all($pattern, $content, $matches);
        if ($match_cnt) {
            // [1] に中身のみ入ってくる。
            foreach ($matches[1] as $match) {
                // 除去
                $content = str_replace($match . ' ', '', $content);
            }
        }

        $pattern = '/<img.*?(data-imgid\s*=\s*[\"|\'].*?[\"|\']).*?>/i';
        $match_cnt = preg_match_all($pattern, $content, $matches);
        if ($match_cnt) {
            // [1] に中身のみ入ってくる。
            foreach ($matches[1] as $match) {
                // 除去
                $content = str_replace($match . ' ', '', $content);
            }
        }

        $pattern = '/<img.*?(class\s*=\s*[\"|\'].*?[\"|\']).*?>/i';
        $match_cnt = preg_match_all($pattern, $content, $matches);
        if ($match_cnt) {
            // [1] に中身のみ入ってくる。
            foreach ($matches[1] as $match) {
                // 除去class
                $replace = str_replace(' nc3-img-block', '', $match);
                $replace = str_replace('nc3-img-block', '', $replace);
                $replace = str_replace(' nc3-img', '', $replace);
                $replace = str_replace('nc3-img', '', $replace);

                // 除去
                $content = str_replace($match, $replace, $content);
            }

            // 不要な class="" があれば消す。
            $content = str_replace(' class=""', '', $content);
        }

        // nc3ではimgにaltとtitleが自動設定されるため、titleが空なら消す
        $pattern = '/<img.*?title\s*=\s*[\"|\'](.*?)[\"|\'].*?>/i';
        $match_cnt = preg_match_all($pattern, $content, $matches);
        if ($match_cnt) {
            // [1] に中身のみ入ってくる。
            foreach ($matches[1] as $match) {
                if (empty($match)) {
                    // 中身が空なら不要な title="" を消す。
                    $content = str_replace(' title=""', '', $content);
                }
            }
        }

        return $content;
    }

    /**
     * NC3：WYSIWYG の記事の保持
     */
    private function nc3Wysiwyg(?Nc3Frame $nc3_frame, ?string $save_folder, ?string $content_filename, ?string $ini_filename, ?string $content, ?string $nc3_plugin_key = null)
    {
        // nc3リンク切れチェック
        $nc3_links = MigrationUtils::getContentHrefOrSrc($content);
        if (is_array($nc3_links)) {
            foreach ($nc3_links as $nc3_link) {
                $this->checkDeadLinkNc3($nc3_link, $nc3_plugin_key . '(wysiwyg)', $nc3_frame);
            }
        }

        // コンテンツのクリーニング
        $content = $this->cleaningContent($content, $nc3_plugin_key);

        // 画像を探す
        $img_srcs = MigrationUtils::getContentImage($content);

        // 画像の中の wysiwygのdownload をエクスポートしたパスに変換する。
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
        // 添付ファイルの中の wysiwygのdownload をエクスポートしたパスに変換する。
        $content = $this->nc3MigrationCommonDownloadMain($nc3_frame, $save_folder, $ini_filename, $content, $anchors, '[upload_files]');
        // [TODO] 未対応
        // cabinet_action_main_download をエクスポート形式に変換
        // [upload_files]に追記したいので、nc2MigrationCommonDownloadMainの直後に実行
        // $content = $this->nc3MigrationCabinetActionMainDownload($save_folder, $ini_filename, $content, 'href');

        // Google Analytics タグ部分を削除
        $content = MigrationUtils::deleteGATag($content);

        // HTML content の保存
        if ($save_folder) {
            $this->storagePut($save_folder . "/" . $content_filename, $content);
        }

        return $content;
    }

    /**
     * NC3：wysiwygのdownload をエクスポート形式に変換
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
     * NC3：wysiwygのdownload をエクスポート形式に変換
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

            // wysiwygのdownload があれば、NC3 の画像として移行する。
            if (stripos($path, 'wysiwyg/image/download') !== false || stripos($path, 'wysiwyg/file/download') !== false) {
                // pathのみに置換
                $path_tmp = parse_url($path, PHP_URL_PATH);
                // 不要文字を取り除き
                $path_tmp = str_replace('/wysiwyg/image/download/', '', $path_tmp);
                $path_tmp = str_replace('/wysiwyg/file/download/', '', $path_tmp);
                // /で分割
                $src_params = explode('/', $path_tmp);

                // $room_id = $src_params[0];
                $upload_id = $src_params[1];
                // image_size = (bigとかsmallとか)
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


    /**
     * NC3の記事ステータスからConnectのステータスへ変換
     */
    private function convertCCStatusFromNc3Status(int $nc3_status): int
    {
        // (nc3)
        // const STATUS_PUBLISHED = '1';
        // const STATUS_APPROVAL_WAITING = '2';
        // const STATUS_IN_DRAFT = '3';
        // const STATUS_DISAPPROVED = '4';

        // (NC3)status -> (Connect)status
        $convert_statuses = [
            1 => StatusType::active,
            2 => StatusType::approval_pending,
            3 => StatusType::temporary,
            4 => StatusType::approval_pending,  // 差し戻しは承認待ちへ
        ];
        return $convert_statuses[$nc3_status] ?? StatusType::active;
    }

    /**
     * NC3のリンク切れチェック
     */
    private function checkDeadLinkNc3(string $url, string $nc3_plugin_key, ?Nc3Frame $nc3_frame = null): void
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
                $this->checkDeadLinkInsideNc3($url, $nc3_plugin_key, $nc3_frame);
            } else {
                // 外部リンク
                $this->checkDeadLinkOutside($url, $nc3_plugin_key, $nc3_frame);
            }

        } elseif (is_null($scheme)) {
            // "{{__BASE_URL__}}/images/comp/textarea/titleicon/icon-weather9.gif" 等はここで処理

            // 内部リンク
            $this->checkDeadLinkInsideNc3($url, $nc3_plugin_key, $nc3_frame);
        } else {
            // 対象外
            $this->putLinkCheck(3, $nc3_plugin_key . '|リンク切れチェック対象外', $url, $nc3_frame);
        }
    }

    /**
     * 外部URLのリンク切れチェック
     */
    private function checkDeadLinkOutside(string $url, string $nc3_plugin_key, ?Nc3Frame $nc3_frame = null): bool
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
            $this->putLinkCheck(3, $nc3_plugin_key . '|外部リンク|リンク切れ|' . $e->getMessage(), $url, $nc3_frame);
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
                $this->putLinkCheck(3, $nc3_plugin_key . '|外部リンク|リンク切れ|' . $headers[$i], $url, $nc3_frame);
                return false;
            }
            $i++;
        }

        // NG. 基本ここには到達しない想定
        $this->putLinkCheck(3, $nc3_plugin_key . '|外部リンク|リンク切れ', $url, $nc3_frame);
        return false;
    }

    /**
     * 内部URL(nc3)のリンク切れチェック
     */
    private function checkDeadLinkInsideNc3(string $url, string $nc3_plugin_key, ?Nc3Frame $nc3_frame = null): void
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
        // {{__BASE_URL__}} 置換
        $check_url = str_replace("{{__BASE_URL__}}", $nc3_base_url, $check_url);

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
        // http://localhost:8080/?lang=ja
        // http://localhost:8080/?lang=en
        // ---------------------------------
        // queryあり＋pathがトップページに該当するもの＋queryはlang１つだけ、はOK扱いにする
        parse_str($check_url_query, $check_url_query_array);
        if ($check_url_query_array) {
            $lang = MigrationUtils::getArrayValue($check_url_query_array, 'lang', null, null);
            if (in_array($check_url_path, ['/', './', '/index.php', './index.php']) && count($check_url_query_array) === 1 && $lang) {
                if (in_array($lang, ['ja', 'en'])) {
                    // OK
                } else {
                    // NG
                    $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|lang値の間違い', $url, $nc3_frame);
                }
                return;
            }
        }
        // 以下 check_url_path は値が存在する

        // ページ＋mod_rewrite
        // ---------------------------------
        // http://localhost:8081/カウンター/
        // http://localhost:8081/setting/カウンター/
        // http://localhost:8081/community/room1
        // http://localhost:8081/community/subgroup1
        // http://localhost:8081/フォトアルバム/page_20191024091805
        // ---------------------------------
        $check_page_permalink = trim($check_url_path, '/');
        if ($check_page_permalink) {
            // 頭とお尻の/を取り除いたpath & 頭のsetting/を取り除いたpath + 空以外 の permalink でページの存在チェック
            $check_page_permalink = ltrim($check_page_permalink, 'setting/');
            $nc3_page = Nc3Page::where('permalink', $check_page_permalink)->where('permalink', '!=', '')->first();
            if ($nc3_page) {
                // ページデータあり. チェックOK
                return;
            }
        }

        // ダウンロードURL（ファイル・画像）
        // ---------------------------------
        // 画像URL例）
        // 　(標準サイズ) http://localhost/wysiwyg/image/download/1/172/big
        // 　(原寸大)     http://localhost/wysiwyg/image/download/1/174
        // ファイルURL例）
        // 　http://localhost/wysiwyg/file/download/1/173
        // ---------------------------------
        if ($check_page_permalink) {
            // wysiwygのdownload あり
            if (stripos($check_page_permalink, 'wysiwyg/image/download') !== false || stripos($check_page_permalink, 'wysiwyg/file/download') !== false) {
                // pathのみに置換
                $path_tmp = parse_url($check_page_permalink, PHP_URL_PATH);
                // 不要文字を取り除き
                $path_tmp = str_replace('/wysiwyg/image/download/', '', $path_tmp);
                $path_tmp = str_replace('/wysiwyg/file/download/', '', $path_tmp);
                // /で分割
                $src_params = explode('/', $path_tmp);

                // $room_id = $src_params[0];
                $upload_id = $src_params[1];
                // image_size = (bigとかsmallとか)
                // $image_size = isset($src_params[2]) ? $src_params[2] : null;

                if ($upload_id) {
                    $nc3_upload = Nc3UploadFile::where('id', $upload_id)->first();
                    if ($nc3_upload) {
                        // アップロードデータあり. チェックOK
                    } else {
                        // NG
                        $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|アップロードデータなし', $url, $nc3_frame);
                    }
                } else {
                    // NG
                    $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|/wysiwyg/image/download/ or /wysiwyg/file/download/でアップロードIDなし', $url, $nc3_frame);
                }
                return;
            }
        }

        // [TODO] まだ
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

        // if ($check_url_query_array) {

        //     $action = MigrationUtils::getArrayValue($check_url_query_array, 'action', null, null);
        //     if ($action == 'pages_view_main') {

        //         // (通常モジュール)
        //         //   (action)active_action & block_id(必須)         例：掲示板, お知らせ, キャビネット等
        //         // (中央エリアに表示)
        //         //   (action)active_center & active_block_id(任意)  例：カレンダー, 施設予約, 検索
        //         $active_action = MigrationUtils::getArrayValue($check_url_query_array, 'active_action', null, null);
        //         $active_center = MigrationUtils::getArrayValue($check_url_query_array, 'active_center', null, null);

        //         if ($active_action) {
        //             // block存在チェック(必須)
        //             $block_id = MigrationUtils::getArrayValue($check_url_query_array, 'block_id', null, null);
        //             $check_nc3_block = Nc2Block::where('block_id', $block_id)->first();
        //             if ($check_nc3_block) {
        //                 // OK
        //             } else {
        //                 // NG
        //                 $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|blockデータなし', $url, $nc3_frame);
        //                 return;
        //             }
        //         }

        //         if ($active_action || $active_center) {
        //             // page_id存在チェック(任意)
        //             $page_id = MigrationUtils::getArrayValue($check_url_query_array, 'page_id', null, null);
        //             if ($page_id) {
        //                 $check_nc3_page = Nc2Page::where('page_id', $page_id)->first();
        //                 if ($check_nc3_page) {
        //                     // OK
        //                 } else {
        //                     // NG
        //                     $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|pageデータなし', $url, $nc3_frame);
        //                     return;
        //                 }
        //             }
        //         }

        //         // (通常モジュール) active_action
        //         // --------------------------------
        //         if ($active_action == 'bbs_view_main_post') {
        //             // (掲示板パラメータ)
        //             //   block_id 必須
        //             //   post_id  必須
        //             //   bbs_id   任意. あれば存在チェック
        //             //
        //             // (掲示板-新着)
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=bbs_view_main_post&post_id=9&block_id=56#_56
        //             // block_idを存在しないIDにすると、「該当ページに配置してある掲示板が削除された可能性があります。」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=bbs_view_main_post&post_id=9&block_id=56999999999999#_56
        //             // post_idを存在しないIDにすると、ページは開けて、掲示板の箇所が「入力値が不正です。不正にアクセスされた可能性があります。」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=bbs_view_main_post&post_id=99999999999999&block_id=56#_56
        //             //
        //             // (掲示板-検索)
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=56&active_action=bbs_view_main_post&bbs_id=3&post_id=9#_56
        //             // bbs_idなくても表示できた
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=56&active_action=bbs_view_main_post&post_id=9#_56
        //             // bbs_idを存在しないIDにすると、ページは開けて、掲示板の箇所が「入力値が不正です。不正にアクセスされた可能性があります。」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=56&active_action=bbs_view_main_post&bbs_id=39999999&post_id=9#_56
        //             //
        //             // (掲示板-active_center, 手動でリンク作成を想定)
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_block_id=56&active_center=bbs_view_main_post&bbs_id=3&post_id=9#_56
        //             // active_block_idを存在しないID, active_block_id設定なしにしても、詳細表示部分が空白なだけで、エラーにはならない。
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_block_id=56999999999&active_center=bbs_view_main_post&bbs_id=3&post_id=9#_56
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=bbs_view_main_post&bbs_id=3&post_id=9#_56

        //             // bbs_post存在チェック
        //             $post_id = MigrationUtils::getArrayValue($check_url_query_array, 'post_id', null, null);
        //             $check_nc3_bbs_post = Nc2BbsPost::where('post_id', $post_id)->first();
        //             if ($check_nc3_bbs_post) {
        //                 // OK
        //             } else {
        //                 // NG
        //                 $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|bbs_postデータなし', $url, $nc3_frame);
        //                 return;
        //             }

        //             // bbs_id存在チェック(任意)
        //             $bbs_id = MigrationUtils::getArrayValue($check_url_query_array, 'bbs_id', null, null);
        //             if ($bbs_id) {
        //                 $check_nc3_bbs = Nc2Bbs::where('bbs_id', $bbs_id)->first();
        //                 if ($check_nc3_bbs) {
        //                     // OK
        //                 } else {
        //                     // NG
        //                     $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|bbsデータなし', $url, $nc3_frame);
        //                     return;
        //                 }
        //             }

        //             // OK
        //             return;

        //         } elseif ($active_action == 'announcement_view_main_init') {
        //             // (お知らせパラメータ)
        //             //   block_id 必須
        //             //   page_id  任意. あれば存在チェック
        //             //
        //             // (お知らせ-新着)
        //             // http://localhost:8080/index.php?action=pages_view_main&&block_id=72#_72
        //             //
        //             // (お知らせ-検索)
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=72&page_id=50&active_action=announcement_view_main_init#_72
        //             // page_idなしでも表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=72&active_action=announcement_view_main_init#_72
        //             // page_idを存在しないIDにすると「データ取得に失敗」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=72&page_id=50999999&active_action=announcement_view_main_init#_72
        //             // block_idがない or 存在しないIDにすると、「該当ページに配置してある掲示板が削除された可能性があります。」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&page_id=50&active_action=announcement_view_main_init#_72
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=7299999&page_id=50&active_action=announcement_view_main_init#_72

        //             // OK
        //             return;

        //         } elseif ($active_action == 'journal_view_main_detail') {
        //             // (日誌パラメータ)
        //             //   block_id       必須
        //             //   post_id        必須
        //             //   comment_flag   任意. (チェック不要). 1:コメント入力あり 1以外:コメント入力なし
        //             //
        //             // (日誌-新着)
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=journal_view_main_detail&post_id=4&comment_flag=1&block_id=34#_34
        //             // post_idなし or 存在しないIDにすると「記事は存在しいません」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=journal_view_main_detail&comment_flag=1&block_id=34#_34
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=journal_view_main_detail&post_id=49999&comment_flag=1&block_id=34#_34
        //             // comment_flagなし or 変な値でも記事表示できる＋コメント入力なし
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=journal_view_main_detail&post_id=4&block_id=34#_34
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=journal_view_main_detail&post_id=4&comment_flag=199999&block_id=34#_34
        //             //
        //             // (日誌-検索)
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=34&active_action=journal_view_main_detail&post_id=4#_34

        //             // journal_post存在チェック
        //             $post_id = MigrationUtils::getArrayValue($check_url_query_array, 'post_id', null, null);
        //             $check_nc3_journal_post = Nc2JournalPost::where('post_id', $post_id)->first();
        //             if ($check_nc3_journal_post) {
        //                 // OK
        //             } else {
        //                 // NG
        //                 $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|journal_postデータなし', $url, $nc3_frame);
        //                 return;
        //             }

        //             // OK
        //             return;

        //         } elseif ($active_action == 'multidatabase_view_main_detail') {
        //             // (汎用DBパラメータ)
        //             //   block_id          必須
        //             //   multidatabase_id  必須
        //             //   content_id        必須
        //             //
        //             // (汎用DB-新着)
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=multidatabase_view_main_detail&content_id=4&multidatabase_id=1&block_id=51#_51
        //             // multidatabase_idなし or IDが存在しないと「入力値が不正」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=multidatabase_view_main_detail&content_id=4&block_id=51#_51
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=multidatabase_view_main_detail&content_id=4&multidatabase_id=19999&block_id=51#_51
        //             // content_idなし or IDが存在しないとコンテンツが存在しません」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=multidatabase_view_main_detail&multidatabase_id=1&block_id=51#_51
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=multidatabase_view_main_detail&content_id=499999&multidatabase_id=1&block_id=51#_51
        //             //
        //             // (汎用DB-検索)
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=51&active_action=multidatabase_view_main_detail&content_id=4&multidatabase_id=1&block_id=51#_51

        //             // multidatabase存在チェック
        //             $multidatabase_id = MigrationUtils::getArrayValue($check_url_query_array, 'multidatabase_id', null, null);
        //             $check_nc3_multidatabase = Nc2Multidatabase::where('multidatabase_id', $multidatabase_id)->first();
        //             if ($check_nc3_multidatabase) {
        //                 // OK
        //             } else {
        //                 // NG
        //                 $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|multidatabaseデータなし', $url, $nc3_frame);
        //                 return;
        //             }

        //             // multidatabase_content存在チェック
        //             $content_id = MigrationUtils::getArrayValue($check_url_query_array, 'content_id', null, null);
        //             $check_nc3_multidatabase_content = Nc2MultidatabaseContent::where('content_id', $content_id)->first();
        //             if ($check_nc3_multidatabase_content) {
        //                 // OK
        //             } else {
        //                 // NG
        //                 $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|multidatabase_contentデータなし', $url, $nc3_frame);
        //                 return;
        //             }

        //             // OK
        //             return;

        //         } elseif ($active_action == 'cabinet_view_main_init') {
        //             // (キャビネットパラメータ)
        //             //   block_id          必須
        //             //   cabinet_id        任意.
        //             //   folder_id         任意.
        //             //
        //             // (キャビネット-新着)
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&cabinet_id=2&folder_id=&block_id=69#_69
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&block_id=69#_69
        //             // cabinet_idなしは表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&folder_id=&block_id=69#_69
        //             // cabinet_idのIDが存在しないと「公開されているキャビネットはありません」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&cabinet_id=2999999&folder_id=&block_id=69#_69
        //             // folder_idなしは表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&cabinet_id=2&block_id=69#_69
        //             // folder_idのIDが存在しないと「権限が不正」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=cabinet_view_main_init&cabinet_id=2&folder_id=99999&block_id=69#_69
        //             //
        //             // (キャビネット-検索)
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=69&active_action=cabinet_view_main_init&cabinet_id=2&folder_id=0#_69

        //             // cabinet_manage存在チェック(任意)
        //             $cabinet_id = MigrationUtils::getArrayValue($check_url_query_array, 'cabinet_id', null, null);
        //             if ($cabinet_id) {
        //                 $check_nc3_cabinet_manage = Nc2CabinetManage::where('cabinet_id', $cabinet_id)->first();
        //                 if ($check_nc3_cabinet_manage) {
        //                     // OK
        //                 } else {
        //                     // NG
        //                     $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|cabinet_manageデータなし', $url, $nc3_frame);
        //                     return;
        //                 }
        //             }

        //             // folder_id(=file_id)のcabinet_file存在チェック(任意)
        //             $folder_id = MigrationUtils::getArrayValue($check_url_query_array, 'folder_id', null, null);
        //             if ($folder_id) {
        //                 $check_nc3_cabinet_file = Nc2CabinetFile::where('file_id', $folder_id)->first();
        //                 if ($check_nc3_cabinet_file) {
        //                     // OK
        //                 } else {
        //                     // NG
        //                     $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|cabinet_fileデータなし.folder_id=file_id', $url, $nc3_frame);
        //                     return;
        //                 }
        //             }

        //             // OK
        //             return;

        //         } elseif ($active_action == 'faq_view_main_init') {
        //             // (FAQパラメータ)
        //             //   block_id          必須
        //             //   question_id       任意.（チェック不要）
        //             //
        //             // (FAQ-検索-のみ)
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=35&active_action=faq_view_main_init&question_id=4#_faq_answer_4
        //             // question_idなし or 存在しないIDでも表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=35&active_action=faq_view_main_init#_faq_answer_4
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=35&active_action=faq_view_main_init&question_id=4999#_faq_answer_4

        //             // OK
        //             return;

        //         } elseif ($active_action == 'photoalbum_view_main_init') {
        //             // (フォトアルバムパラメータ)
        //             //   block_id          必須
        //             //   album_id          任意.（チェック不要）
        //             //
        //             // (フォトアルバム-新着-のみ)
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=photoalbum_view_main_init&album_id=1&block_id=68#photoalbum_album_68_1
        //             // album_idなし or 存在しないIDでも表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=photoalbum_view_main_init&block_id=68#photoalbum_album_68_1
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=photoalbum_view_main_init&album_id=19999999999999&block_id=68#photoalbum_album_68_1

        //             // OK
        //             return;

        //         } elseif ($active_action == 'assignment_view_main_whatsnew' || $active_action == 'assignment_view_main_init') {
        //             // (レポートパラメータ)
        //             //   block_id          必須
        //             //   (新着：assignment_view_main_whatsnew) assignment_id     必須
        //             //   (検索：assignment_view_main_init)     assignment_id     任意.
        //             //
        //             // (レポート-新着)
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=assignment_view_main_whatsnew&assignment_id=1&block_id=74#_74
        //             // assignment_idなし or 存在しないIDだと「入力値が不正」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=assignment_view_main_whatsnew&block_id=74#_74
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=assignment_view_main_whatsnew&assignment_id=1999999&block_id=74#_74
        //             //
        //             // (レポート-検索)
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=74&active_action=assignment_view_main_init&assignment_id=1#_74
        //             // assignment_idなしでも表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=74&active_action=assignment_view_main_init#_74
        //             // assignment_idで存在しないIDだと「公開されている課題はありません」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=74&active_action=assignment_view_main_init&assignment_id=1999999#_74


        //             if ($active_action == 'assignment_view_main_whatsnew') {
        //                 // (レポート-新着)
        //                 // assignment存在チェック（必須）
        //                 $assignment_id = MigrationUtils::getArrayValue($check_url_query_array, 'assignment_id', null, null);
        //                 $check_nc3_assignment = Nc2Assignment::where('assignment_id', $assignment_id)->first();
        //                 if ($check_nc3_assignment) {
        //                     // OK
        //                 } else {
        //                     // NG
        //                     $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|assignmentデータなし', $url, $nc3_frame);
        //                     return;
        //                 }

        //             } elseif ($active_action == 'assignment_view_main_init') {
        //                 // (レポート-検索)
        //                 // assignment存在チェック（任意）
        //                 $assignment_id = MigrationUtils::getArrayValue($check_url_query_array, 'assignment_id', null, null);
        //                 if ($assignment_id) {
        //                     $check_nc3_assignment = Nc2Assignment::where('assignment_id', $assignment_id)->first();
        //                     if ($check_nc3_assignment) {
        //                         // OK
        //                     } else {
        //                         // NG
        //                         $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|assignmentデータなし', $url, $nc3_frame);
        //                         return;
        //                     }
        //                 }
        //             }

        //             // OK
        //             return;

        //         } elseif ($active_action == 'questionnaire_view_main_whatsnew') {
        //             // (アンケートパラメータ)
        //             //   block_id          必須
        //             //   questionnaire_id  必須
        //             //
        //             // (アンケート-新着-のみ)
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=questionnaire_view_main_whatsnew&questionnaire_id=1&block_id=75#_75
        //             // questionnaire_idなし or 存在しないIDだと「入力値が不正」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=questionnaire_view_main_whatsnew&block_id=75#_75
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=questionnaire_view_main_whatsnew&questionnaire_id=19999999&block_id=75#_75

        //             // questionnaire存在チェック
        //             $questionnaire_id = MigrationUtils::getArrayValue($check_url_query_array, 'questionnaire_id', null, null);
        //             $check_nc3_questionnaire = Nc2Questionnaire::where('questionnaire_id', $questionnaire_id)->first();
        //             if ($check_nc3_questionnaire) {
        //                 // OK
        //             } else {
        //                 // NG
        //                 $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|questionnaireデータなし', $url, $nc3_frame);
        //                 return;
        //             }

        //             // OK
        //             return;

        //         } elseif ($active_action == 'quiz_view_main_whatsnew') {
        //             // (小テストパラメータ)
        //             //   block_id          必須
        //             //   quiz_id           必須
        //             //
        //             // (小テスト-新着-のみ)
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=quiz_view_main_whatsnew&quiz_id=1&block_id=77#_77
        //             // quiz_idなし or 存在しないIDだと「入力値が不正」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=quiz_view_main_whatsnew&block_id=77#_77
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=quiz_view_main_whatsnew&quiz_id=1999999&block_id=77#_77

        //             // quiz存在チェック
        //             $quiz_id = MigrationUtils::getArrayValue($check_url_query_array, 'quiz_id', null, null);
        //             $check_nc3_quiz = Nc2Quiz::where('quiz_id', $quiz_id)->first();
        //             if ($check_nc3_quiz) {
        //                 // OK
        //             } else {
        //                 // NG
        //                 $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|quizデータなし', $url, $nc3_frame);
        //                 return;
        //             }

        //             // OK
        //             return;

        //         } elseif ($active_action == 'todo_view_main_init') {
        //             // (Todoパラメータ)
        //             //   block_id          必須
        //             //   todo_id           任意
        //             //   page_id           任意
        //             //
        //             // (Todo-新着)
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=todo_view_main_init&todo_id=11&block_id=76#_76
        //             // todo_idなし だと表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=todo_view_main_init&block_id=76#_76
        //             // todo_idが存在しないID だと「公開されているTodoリストはありません」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=todo_view_main_init&todo_id=11999999&block_id=76#_76
        //             //
        //             // (Todo-検索)
        //             //   http://localhost:8080/index.php?action=pages_view_main&block_id=76&page_id=55&active_action=todo_view_main_init#_76

        //             // todo存在チェック（任意）
        //             $todo_id = MigrationUtils::getArrayValue($check_url_query_array, 'todo_id', null, null);
        //             if ($todo_id) {
        //                 $check_nc3_todo = Nc2Todo::where('todo_id', $todo_id)->first();
        //                 if ($check_nc3_todo) {
        //                     // OK
        //                 } else {
        //                     // NG
        //                     $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|todoデータなし', $url, $nc3_frame);
        //                     return;
        //                 }
        //             }

        //             // OK
        //             return;

        //         } elseif ($active_action == 'circular_view_main_detail') {
        //             // (回覧板パラメータ)
        //             //   block_id          必須
        //             //   circular_id       必須
        //             //   page_id           任意
        //             //   room_id           任意（チェック不要）
        //             //
        //             // (回覧板-新着)
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=circular_view_main_detail&circular_id=2&page_id=53&block_id=78#_78
        //             // circular_idなし or 存在しないID だと「既に削除されています」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=circular_view_main_detail&page_id=53&block_id=78#_78
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=circular_view_main_detail&circular_id=299999&page_id=53&block_id=78#_78
        //             //
        //             // (回覧板-検索)
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=circular_view_main_detail&block_id=78&room_id=1&circular_id=2#_78
        //             // room_idなし or 存在しないID でも表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=circular_view_main_detail&block_id=78&circular_id=2#_78
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_action=circular_view_main_detail&block_id=78&room_id=19999&circular_id=2#_78

        //             // circular存在チェック
        //             $circular_id = MigrationUtils::getArrayValue($check_url_query_array, 'circular_id', null, null);
        //             $check_nc3_circular = Nc2Circular::where('circular_id', $circular_id)->first();
        //             if ($check_nc3_circular) {
        //                 // OK
        //             } else {
        //                 // NG
        //                 $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|circularデータなし', $url, $nc3_frame);
        //                 return;
        //             }

        //             // OK
        //             return;
        //         }


        //         // (中央エリアに表示) active_center
        //         // --------------------------------
        //         if ($active_center == 'search_view_main_center') {
        //             // （検索-初期インストール配置のヘッダー検索お知らせ）
        //             //   ./index.php?action=pages_view_main&active_center=search_view_main_center
        //             // （検索-active_action, 手動でリンク作成を想定
        //             //   → 対応しない
        //             //   block_idがないと、「該当ページに配置してある検索が削除された可能性があります。」エラー
        //             //   ./index.php?action=pages_view_main&active_action=search_view_main_center

        //             // OK
        //             return;

        //         } elseif ($active_center == 'reservation_view_main_init') {
        //             // (施設予約-新着)
        //             //   active_block_id      任意. (チェック不要)
        //             //   page_id              任意. あれば存在チェック
        //             //   reserve_details_id   任意. (チェック不要)
        //             //   display_type         任意. あれば値チェック=1|2|3
        //             //   reserve_id           任意. (チェック不要)
        //             //
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=42&page_id=0&display_type=2#_active_center_42
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init
        //             // active_block_idなしでも, 存在しないIDでも表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&page_id=0&display_type=2#_active_center_42
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=4299999999999999&page_id=0&display_type=2#_active_center_42
        //             // page_idなしでも表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&display_type=2#_active_center_42
        //             // page_idを存在しないIDにすると「データ取得に失敗」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&page_id=9999999&display_type=2#_active_center_42
        //             // reserve_details_idなし or 存在しないIDでも表示できる. あれば該当日の一覧を表示
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=1999999999&active_block_id=42&page_id=0&display_type=2#_active_center_42
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&active_block_id=42&page_id=0&display_type=2#_active_center_42
        //             // display_typeなしでも表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=42&page_id=0#_active_center_42
        //             // display_typeの「入力値が不正です」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=42&page_id=0&display_type=999#_active_center_42
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=42&page_id=0&display_type=4#_active_center_42
        //             //
        //             // >>> parse_str("action=pages_view_main&active_center=reservation_view_main_init&reserve_details_id=19&active_block_id=42&page_id=0&display_type=2", $result)
        //             // >>> $result
        //             // => [
        //             //      "action" => "pages_view_main",
        //             //      "active_center" => "reservation_view_main_init",
        //             //      "reserve_details_id" => "19",
        //             //      "active_block_id" => "42",
        //             //      "page_id" => "0",
        //             //      "display_type" => "2",
        //             //    ]
        //             //
        //             // (施設予約-検索)
        //             //   http://localhost:8080/index.php?action=pages_view_main&page_id=13&active_center=reservation_view_main_init&reserve_id=74
        //             // reserve_id が存在しないIDでも表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&page_id=13&active_center=reservation_view_main_init&reserve_id=74999999

        //             // display_typeの有効値チェック(任意)
        //             $display_type = MigrationUtils::getArrayValue($check_url_query_array, 'display_type', null, null);
        //             if ($display_type) {
        //                 if ((int)$display_type <= 3) {
        //                     // OK 1|2|3, ※ イレギュラーだけど0,-1,-2...でも表示可
        //                 } else {
        //                     // NG
        //                     $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|display_type対象外', $url, $nc3_frame);
        //                     return;
        //                 }
        //             }

        //             // OK
        //             return;

        //         } elseif ($active_center == 'calendar_view_main_init') {
        //             // (カレンダー-新着)
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42&active_block_id=11&page_id=1&display_type=5#_active_center_11
        //             // page_idなしでも表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42&active_block_id=11&display_type=5#_active_center_11
        //             // page_idを存在しないIDにすると「データ取得に失敗」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42&active_block_id=11&page_id=19999999999&display_type=5#_active_center_11
        //             // display_typeなしでも表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42&active_block_id=11&page_id=1#_active_center_11
        //             // display_type=0|-1|-2...は表示できちゃう
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42&active_block_id=11&page_id=1&display_type=-1#_active_center_11
        //             // display_typeの「入力値が不正です」エラー
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42&active_block_id=11&page_id=1&display_type=8#_active_center_11
        //             // plan_id なし or ID存在しなくても表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&active_block_id=11&page_id=1&display_type=5#_active_center_11
        //             //   http://localhost:8080/index.php?action=pages_view_main&active_center=calendar_view_main_init&plan_id=42999&active_block_id=11&page_id=1&display_type=5#_active_center_11
        //             //
        //             // http://localhost:8080/index.php?
        //             //   - action=pages_view_main
        //             //   - active_center=calendar_view_main_init
        //             //   - plan_id=42           任意. (チェック不要)
        //             //   - active_block_id=11
        //             //   - page_id=1            上にチェック処理あり
        //             //   o display_type=5       任意. あれば値チェック=1～8
        //             //   - #_active_center_11
        //             //
        //             // (カレンダー-検索)
        //             //   http://localhost:8080/index.php?action=pages_view_main&page_id=13&active_center=calendar_view_main_init&date=20210811&current_time=000000&display_type=5
        //             // date|current_time なし or 値が変な値でも表示できる
        //             //   http://localhost:8080/index.php?action=pages_view_main&page_id=13&active_center=calendar_view_main_init&display_type=5
        //             //   http://localhost:8080/index.php?action=pages_view_main&page_id=13&active_center=calendar_view_main_init&date=202108119999&current_time=0000009999&display_type=5
        //             //   http://localhost:8080/index.php?action=pages_view_main&page_id=13&active_center=calendar_view_main_init&date=20210811&display_type=5
        //             //
        //             // http://localhost:8080/index.php?
        //             //   - date=20210811        任意. (チェック不要)
        //             //   - current_time=000000  任意. (チェック不要)
        //             //   o display_type=5       任意. あれば値チェック=1～8

        //             // display_typeの有効値チェック(任意)
        //             $display_type = MigrationUtils::getArrayValue($check_url_query_array, 'display_type', null, null);
        //             if ($display_type) {
        //                 if ((int)$display_type <= 8) {
        //                     // OK ※イレギュラーだけど0,-1,-2...でも表示可
        //                 } else {
        //                     // NG
        //                     $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク|display_type対象外', $url, $nc3_frame);
        //                     return;
        //                 }
        //             }

        //             // OK
        //             return;
        //         }

        //     }
        // }

        // 外部リンク
        // 内部リンクの直ファイル指定の存在チェック。例）http://localhost:8080/htdocs/install/logo.gif
        // if ($this->checkDeadLinkOutside($check_url, $nc3_plugin_key, $nc3_frame)) {
        //     // 外部OK=移行対象外 (link_checkログには吐かない)
        //     $this->putMonitor(3, $nc3_plugin_key . '|内部リンク＋外部リンクチェックOK|移行対象外URL', $url, $nc3_frame);
        // } else {
        //     // 外部NG
        //     $header = get_headers($check_url, true);
        //     $this->putLinkCheck(3, $nc3_plugin_key . '|内部リンク＋外部リンクチェックNG|未対応URL|' . $header[0], $url, $nc3_frame);
        // }

        // 移行対象外 (link_checkログには吐かない)
        $this->putMonitor(3, $nc3_plugin_key . '|内部リンク|移行対象外URL', $url, $nc3_frame);
    }
}
