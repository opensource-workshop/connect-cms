<?php

namespace App\Traits\Migration;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use Symfony\Component\Yaml\Yaml;

use App\Models\Migration\MigrationMapping;

use App\Models\Migration\Nc3\Nc3AccessCounter;
use App\Models\Migration\Nc3\Nc3AccessCounterFrameSetting;
use App\Models\Migration\Nc3\Nc3Announcement;
use App\Models\Migration\Nc3\Nc3AuthorizationKey;
use App\Models\Migration\Nc3\Nc3Box;
use App\Models\Migration\Nc3\Nc3Bbs;
use App\Models\Migration\Nc3\Nc3BbsArticle;
use App\Models\Migration\Nc3\Nc3BbsFrameSetting;
use App\Models\Migration\Nc3\Nc3Block;
use App\Models\Migration\Nc3\Nc3BlockRolePermission;
use App\Models\Migration\Nc3\Nc3BlockSetting;
use App\Models\Migration\Nc3\Nc3Blog;
use App\Models\Migration\Nc3\Nc3BlogEntry;
use App\Models\Migration\Nc3\Nc3BlogFrameSetting;
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
use App\Models\Migration\Nc3\Nc3ReservationEvent;
use App\Models\Migration\Nc3\Nc3ReservationFrameSetting;
use App\Models\Migration\Nc3\Nc3ReservationLocation;
use App\Models\Migration\Nc3\Nc3ReservationLocationsApprovalUser;
use App\Models\Migration\Nc3\Nc3ReservationLocationsReservable;
use App\Models\Migration\Nc3\Nc3Room;
use App\Models\Migration\Nc3\Nc3RoomRolePermission;
use App\Models\Migration\Nc3\Nc3PhotoAlbum;
use App\Models\Migration\Nc3\Nc3PhotoAlbumDisplayAlbum;
use App\Models\Migration\Nc3\Nc3PhotoAlbumFrameSetting;
use App\Models\Migration\Nc3\Nc3PhotoAlbumPhoto;
use App\Models\Migration\Nc3\Nc3SearchFramePlugin;
use App\Models\Migration\Nc3\Nc3SiteSetting;
use App\Models\Migration\Nc3\Nc3Space;
use App\Models\Migration\Nc3\Nc3UploadFile;
use App\Models\Migration\Nc3\Nc3User;
use App\Models\Migration\Nc3\Nc3UserAttribute;
use App\Models\Migration\Nc3\Nc3UserAttributeChoice;
use App\Models\Migration\Nc3\Nc3UsersLanguage;
use App\Models\Migration\Nc3\Nc3Video;
use App\Models\Migration\Nc3\Nc3VideoFrameSetting;

use App\Traits\ConnectCommonTrait;
use App\Utilities\Migration\MigrationUtils;

use App\Enums\AreaType;
use App\Enums\BlogNarrowingDownType;
use App\Enums\BlogNoticeEmbeddedTag;
use App\Enums\CounterDesignType;
use App\Enums\ContentOpenType;
use App\Enums\DatabaseNoticeEmbeddedTag;
use App\Enums\DatabaseSortFlag;
use App\Enums\DayOfWeek;
use App\Enums\FaqNarrowingDownType;
use App\Enums\FaqSequenceConditionType;
use App\Enums\FormAccessLimitType;
use App\Enums\FormColumnType;
use App\Enums\FormMode;
use App\Enums\LinklistType;
use App\Enums\NoticeEmbeddedTag;
use App\Enums\PhotoalbumSort;
use App\Enums\ReservationCalendarDisplayType;
use App\Enums\ReservationLimitedByRole;
use App\Enums\ReservationNoticeEmbeddedTag;
use App\Enums\ShowType;
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
     * @var array エクスポート済みトップページのbox_id保持
     */
    private $exported_common_top_page_box_ids = [
        Nc3Box::container_type_header => [],
        Nc3Box::container_type_left   => [],
        Nc3Box::container_type_right  => [],
        Nc3Box::container_type_footer => []
    ];

    /**
     * @var array エクスポート済みルームのトップページのbox_id保持
     *
     * [
     *     room_id => [
     *         header => [],
     *         left   => [],
     *         right  => [],
     *         footer => []
     *     ],
     * ]
     */
    private $exported_room_top_page_box_ids = [];

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
        'photo_albums'     => 'photoalbums',    // フォトアルバム
        'questionnaires'   => 'Development',    // アンケート
        'quizzes'          => 'Development',    // 小テスト
        'registrations'    => 'forms',          // フォーム
        'reservations'     => 'reservations',   // 施設予約
        'rss_readers'      => 'Development',    // RSS
        'searches'         => 'searchs',        // 検索
        'tasks'            => 'Development',    // ToDo
        'topics'           => 'whatsnews',      // 新着情報
        'videos'           => 'photoalbums',    // 動画
        'wysiwyg'          => 'Development',    // wysiwyg(upload用)
    ];

    /**
     * 新着の対応プラグイン
     */
    private $available_whatsnew_connect_plugin_names = ['blogs', 'bbses', 'databases'];

    /**
     * 検索の対応プラグイン
     */
    private $available_search_connect_plugin_names = ['contents', 'blogs', 'bbses', 'databases', 'faqs'];

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
        // created(datetime) = null の場合、取得すると string(19) "0000-00-00 00:00:00" となったため対応
        if (empty($utc_datetime) || $utc_datetime == "0000-00-00 00:00:00") {
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

        $this->putMonitor(3, "ExportNc3 Start.");

        // 移行の初期処理
        $this->migrationInit();

        // サイト基本設定のエクスポート
        if ($this->isTarget('nc3_export', 'basic')) {
            $this->nc3ExportBasic();
        }

        // アップロード・データとファイルのエクスポート
        if ($this->isTarget('nc3_export', 'uploads')) {
            $this->nc3ExportUploads($redo);
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

        // NC3 施設予約（reservations）データのエクスポート
        if ($this->isTarget('nc3_export', 'plugins', 'reservations')) {
            $this->nc3ExportReservation($redo);
        }

        // NC3 フォトアルバム（photoalbums）データのエクスポート
        if ($this->isTarget('nc3_export', 'plugins', 'photoalbums')) {
            $this->nc3ExportPhotoalbum($redo);
        }

        // NC3 動画（videos）データのエクスポート
        if ($this->isTarget('nc3_export', 'plugins', 'videos')) {
            $this->nc3ExportVideo($redo);
        }

        // NC3 検索（searches）データのエクスポート
        if ($this->isTarget('nc3_export', 'plugins', 'searchs')) {
            $this->nc3ExportSearch($redo);
        }

        // pages データとファイルのエクスポート
        if ($this->isTarget('nc3_export', 'pages')) {
            $this->putMonitor(3, "Nc3ExportPages Start.");
            $timer_start = $this->timerStart();

            // データクリア
            if ($redo === true) {
                MigrationMapping::where('target_source_table', 'source_pages')->delete();
                // 移行用ファイルの削除
                Storage::deleteDirectory($this->getImportPath('pages/'));
                // pagesエクスポート関連のnc3Frame()でmenuのエクスポートで@insert配下ディレクトリに出力しているため、同ディレクトリを削除
                // ⇒ 移行後用の新ページを作成したのを置いておき、移行後にinsertするような使い方だから削除されると微妙なため、コメントアウト
                // ⇒ 明示的にredoしているので@insertも消すことにする
                Storage::deleteDirectory($this->getImportPath('pages/', '@insert/'));
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
            $nc3_top_page_query = Nc3Page::
                select('pages.id')
                ->join('rooms', function ($join) {
                    $join->on('rooms.id', '=', 'pages.room_id')
                        ->where('rooms.space_id', Nc3Space::PUBLIC_SPACE_ID);
                })
                ->whereNotNull('pages.parent_id');
                // ->orderBy('pages.sort_key')
                // ->first();

            $older_than_nc3_2_0 = $this->getMigrationConfig('basic', 'older_than_nc3_2_0');
            if ($older_than_nc3_2_0) {
                // nc3.2.0より古い場合は、sort_key が無いため parent_id, lft でソートすると、ページの並び順を再現できた。
                $nc3_top_page_query->orderBy('pages.lft');
            } else {
                // 通常
                $nc3_top_page_query->orderBy('pages.sort_key');
            }
            $nc3_top_page = $nc3_top_page_query->first();

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

            $nc3_pages_query->orderBy('pages_languages.language_id');
                // ->orderBy('pages.sort_key')
                // ->orderBy('rooms.sort_key')
                // ->get();

            if ($older_than_nc3_2_0) {
                // nc3.2.0より古い場合は、sort_key が無いため parent_id, lft でソートすると、ページの並び順を再現できた。
                // @see https://github.com/NetCommons3/Pages/commit/77840e492352a21f7300ab1fa877f47f94f0bd1c
                // @see https://github.com/NetCommons3/Rooms/commit/8edfd1ea18f4b45f5aee7f961d0480048e2d6fc9
                //
                // ※ 若い親IDのページを先に移行しないと、MigrationMappingに親ページID達がなくマッチングできず、移行後にページ階層を再現できないため、若い親IDを上に並べる。
                // nc3.2.0より新しければ、sort_keyで対応され、親ページID達が先に登録されるため、parent_idのソートは不要と思う。
                $nc3_pages_query->orderBy('pages.lft');
            } else {
                // 通常
                $nc3_pages_query->orderBy('pages.sort_key')
                                ->orderBy('rooms.sort_key');
            }
            $nc3_pages = $nc3_pages_query->get();

            // 全ページレイアウトを出力する
            $export_full_page_layout = $this->getMigrationConfig('pages', 'export_full_page_layout');
            if ($export_full_page_layout) {
                // NC3ページレイアウト全件
                $container_types = [
                    Nc3Box::container_type_header,
                    Nc3Box::container_type_left,
                    Nc3Box::container_type_right,
                    Nc3Box::container_type_footer
                ];
                $nc3_page_containers = Nc3PageContainer::whereIn('container_type', $container_types)->get();

                // --- nc3でのヘッダ、左、右、フッタ取得順
                // page ->
                //  page_containers(どのエリアが見えてる(is_published = 1)・見えてないか) ->
                //    boxes_page_containers(全エリア(page_id = 999 and is_published = 1)のbox特定) ->
                //      box ->
                //        frame ->
                //          block

                // 該当レイアウトにプラグイン配置（フレームありなし）の有無、全件
                $nc3_page_container_frames = Nc3PageContainer::select('frames.*', 'page_containers.container_type', 'page_containers.page_id')
                    ->join('boxes_page_containers', function ($join) {
                        $join->on('boxes_page_containers.page_container_id', '=', 'page_containers.id')
                            ->where('boxes_page_containers.is_published', 1);      // 有効なデータ
                    })
                    ->join('boxes', 'boxes.id', '=', 'boxes_page_containers.box_id')
                    ->join('frames', 'frames.box_id', '=', 'boxes.id')
                    ->whereIn('page_containers.container_type', $container_types)
                    ->where('page_containers.is_published', 1)      // 見えてるエリア
                    ->where('frames.is_deleted', 0)
                    ->get();
            }

            // NC3 のページID を使うことにした。
            //// 新規ページ用のインデックス
            //// 新規ページは _99 のように _ 付でページを作っておく。（_ 付はデータ作成時に既存page_id の続きで採番する）

            // エクスポートしたページフォルダは連番にした。
            $new_page_index = 0;

            // メニューで表示している下層ページを表示 取得
            $nc3_menu_frame_pages_folder = Nc3MenuFramePage::whereIn("page_id", $nc3_pages->pluck('id'))
                ->where("folder_type", 1)   // 1:下層ページを表示
                ->where("is_hidden", 0)     // 0:表示
                ->get();

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

                // 全ページレイアウトを出力する
                if ($export_full_page_layout) {
                    $nc3_page_containers_by_page = $nc3_page_containers->where('page_id', $nc3_sort_page->id);
                    $nc3_page_container_frames_by_page = $nc3_page_container_frames->where('page_id', $nc3_sort_page->id);

                    // ;cc_import_force_layouts["インポートページのディレクトリNo"] = "ヘッダー|左|右|フッター"、 1:表示,0:非表示
                    $layout = '';
                    // ヘッダーエリア
                    $nc3_page_container_header = $nc3_page_containers_by_page->firstWhere('container_type', Nc3Box::container_type_header) ?? new Nc3PageContainer();
                    if ($nc3_page_container_header->is_published) {
                        // プラグイン配置（フレーム）あり・なし判定。NC3だとレイアウトONでもプラグイン配置なしだと、そのエリアがないものとして表示されるため。
                        $nc3_page_container_frame = $nc3_page_container_frames_by_page->firstWhere('container_type', Nc3Box::container_type_header);
                        if ($nc3_page_container_frame) {
                            // プラグイン配置（フレーム）あり
                            $layout .= '1';
                        } else {
                            // なし
                            $layout .= '0';
                        }
                    } else {
                        $layout .= '0';
                    }
                    // 左エリア
                    $nc3_page_container_left = $nc3_page_containers_by_page->firstWhere('container_type', Nc3Box::container_type_left) ?? new Nc3PageContainer();
                    if ($nc3_page_container_left->is_published) {
                        $nc3_page_container_frame = $nc3_page_container_frames_by_page->firstWhere('container_type', Nc3Box::container_type_left);
                        if ($nc3_page_container_frame) {
                            $layout .= '|1';
                        } else {
                            $layout .= '|0';
                        }
                    } else {
                        $layout .= '|0';
                    }
                    // 右エリア
                    $nc3_page_container_right = $nc3_page_containers_by_page->firstWhere('container_type', Nc3Box::container_type_right) ?? new Nc3PageContainer();
                    if ($nc3_page_container_right->is_published) {
                        $nc3_page_container_frame = $nc3_page_container_frames_by_page->firstWhere('container_type', Nc3Box::container_type_right);
                        if ($nc3_page_container_frame) {
                            $layout .= '|1';
                        } else {
                            $layout .= '|0';
                        }
                    } else {
                        $layout .= '|0';
                    }
                    // フッターエリア
                    $nc3_page_container_footer = $nc3_page_containers_by_page->firstWhere('container_type', Nc3Box::container_type_footer) ?? new Nc3PageContainer();
                    if ($nc3_page_container_footer->is_published) {
                        $nc3_page_container_frame = $nc3_page_container_frames_by_page->firstWhere('container_type', Nc3Box::container_type_footer);
                        if ($nc3_page_container_frame) {
                            $layout .= '|1';
                        } else {
                            $layout .= '|0';
                        }
                    } else {
                        $layout .= '|0';
                    }
                    $page_ini .= "layout = \"{$layout}\"\n";
                }

                // 下層ページを表示
                // １つでもメニューで「下層ページ表示」設定のページがあれば、ページは「下層ページを表示」ONで移行
                $transfer_lower_page_flag = $nc3_menu_frame_pages_folder->firstWhere('page_id', $nc3_sort_page->id) ? 1 : 0;
                $page_ini .= "transfer_lower_page_flag = {$transfer_lower_page_flag}\n";

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

                // （多言語対応）マッピングテーブルの追加 ※noteカラムに言語情報をJSON形式で追加
                $language_id = $nc3_sort_page->language_id ?? 2; // デフォルト日本語
                MigrationMapping::updateOrCreate(
                    ['target_source_table' => 'source_pages_lang', 'source_key' => $nc3_sort_page->id . '_' . $language_id],
                    ['target_source_table' => 'source_pages_lang',
                     'source_key'          => $nc3_sort_page->id . '_' . $language_id,
                     'destination_key'     => $this->zeroSuppress($new_page_index),
                     'note'                => json_encode([
                         'language_id' => $language_id,
                         'language_code' => $this->checkLangDirnameJpn($language_id) ? 'ja' : 'en',
                         'room_id' => $nc3_sort_page->room_id,
                         'room_page_id_top' => $nc3_sort_page->page_id_top ?? null,
                         'nc3_page_id' => $nc3_sort_page->id
                     ])]
                );

                // フレーム処理
                $this->nc3Frame($nc3_sort_page, $new_page_index, $nc3_top_page);
            }

            // ページ入れ替え
            $this->changePageSequence();

            $this->putMonitor(3, "Nc3ExportPages End.", $this->timerEnd($timer_start));
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
     * 新着でNC3プラグインキーからConnect-CMS のプラグイン名に変換
     */
    private function getCCPluginNamesFromNc3WhatsnewPluginKeys($plugin_keys)
    {
        return $this->getCCPluginNamesFromNc3PluginKeys($plugin_keys, $this->available_whatsnew_connect_plugin_names, '新着');
    }

    /**
     * 検索でNC3プラグインキーからConnect-CMS のプラグイン名に変換
     */
    private function getCCPluginNamesFromNc3SearchPluginKeys($plugin_keys)
    {
        return $this->getCCPluginNamesFromNc3PluginKeys($plugin_keys, $this->available_search_connect_plugin_names, '検索');
    }

    /**
     * NC3プラグインキーからConnect-CMS のプラグイン名に変換
     */
    private function getCCPluginNamesFromNc3PluginKeys($plugin_keys, array $available_connect_plugin_names, string $log_head_plugin_name)
    {
        $ret = array();
        foreach ($plugin_keys as $plugin_key) {
            // Connect-CMS のプラグイン名に変換
            if (array_key_exists($plugin_key, $this->plugin_name)) {
                $connect_plugin_name = $this->plugin_name[$plugin_key];
                if ($connect_plugin_name == 'Development') {
                    $this->putError(3, "{$log_head_plugin_name}：未開発プラグイン", "plugin_key = {$plugin_key}");
                } elseif (in_array($connect_plugin_name, $available_connect_plugin_names)) {
                    $ret[] = $connect_plugin_name;
                } else {
                    $this->putError(3, "{$log_head_plugin_name}：未対応プラグイン", "plugin_key = {$plugin_key}");
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
        $this->putMonitor(3, "Nc3ExportBasic Start.");
        $timer_start = $this->timerStart();

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

        // サイト概要
        $meta_description = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Meta.description');
        // NC初期設定のサイト概要は除去
        $meta_description = str_replace('CMS,Netcommons,NetCommons3,CakePHP', '', $meta_description);
        // ダブルクォーテーション対策
        $meta_description = str_replace('"', '\"', $meta_description);
        $basic_ini .= "description = \"" . $meta_description . "\"\n";

        // basic.ini ファイル保存
        $this->storagePut($this->getImportPath('basic/basic.ini'), $basic_ini);

        $this->putMonitor(3, "Nc3ExportBasic End.", $this->timerEnd($timer_start));
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
    private function nc3ExportUploads($redo)
    {
        $this->putMonitor(3, "Nc3ExportUploads Start.");
        $timer_start = $this->timerStart();

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

        // nc3 uploads_path の取得
        $uploads_path = $this->getExportUploadsPath();

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
            $uploads_ini_detail .= "client_original_name = \"" . mb_substr($nc3_upload->original_name, 0, 160) . "\"\n";
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

        $this->putMonitor(3, "Nc3ExportUploads End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：ユーザの移行
     */
    private function nc3ExportUsers($redo)
    {
        $this->putMonitor(3, "Nc3ExportUsers Start.");
        $timer_start = $this->timerStart();

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
            $this->putMonitor(3, "Nc3ExportUsers End: no data.");
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

        $this->putMonitor(3, "Nc3ExportUsers End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：グループの移行
     */
    private function nc3ExportRooms($redo)
    {
        $this->putMonitor(3, "Nc3ExportRooms Start.");
        $timer_start = $this->timerStart();

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

        $older_than_nc3_2_0 = $this->getMigrationConfig('basic', 'older_than_nc3_2_0');
        if ($older_than_nc3_2_0) {
            // nc3.2.0より古い場合は、sort_key が無いため sort_key でソートしない
            $nc3_rooms = $nc3_rooms_query->get();
        } else {
            // 通常
            $nc3_rooms = $nc3_rooms_query->orderBy('rooms.sort_key')->get();
        }

        // 空なら戻る
        if ($nc3_rooms->isEmpty()) {
            $this->putMonitor(3, "Nc3ExportRooms End: no data.");
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

        $this->putMonitor(3, "Nc3ExportRooms End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：ブログ（Blog）の移行
     */
    private function nc3ExportBlog($redo)
    {
        $this->putMonitor(3, "Nc3ExportBlog Start.");
        $timer_start = $this->timerStart();

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
            $this->putMonitor(3, "Nc3ExportBlog End: no data.");
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
            // $journals_ini .= "view_count = 10\n";
            $journals_ini .= "use_like = " . Nc3BlockSetting::getNc3BlockSettingValue($block_settings, $nc3_blog->block_key, 'use_like') . "\n";
            $journals_ini .= "use_view_count_spectator = 1\n";                              // 表示件数リストを表示ON
            $journals_ini .= "narrowing_down_type = \"" . BlogNarrowingDownType::dropdown . "\"\n"; // カテゴリの絞り込み機能ON
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
            $journals_ini .= "module_name = \"blogs\"\n";
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

            $journals_ini_key = "\n";
            $journals_ini_key .= "[content_keys]\n";    // インポートでMigrationMappingにセット用。その後プラグイン固有リンク置換で使う

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
                $journals_ini     .= "post_title[" . $nc3_blog_post->id . "] = \"" . str_replace('"', '', $nc3_blog_post->title) . "\"\n";

                $journals_ini_key .= "content_key[" . $nc3_blog_post->id . "] = \"" . $nc3_blog_post->key . "\"\n";
            }
            $journals_ini .= $journals_ini_key;

            // blog の設定
            $this->storagePut($this->getImportPath('blogs/blog_') . $this->zeroSuppress($nc3_blog->id) . '.ini', $journals_ini);

            // blog の記事
            $journals_tsv = $this->exportStrReplace($journals_tsv, 'blogs');
            $this->storagePut($this->getImportPath('blogs/blog_') . $this->zeroSuppress($nc3_blog->id) . '.tsv', $journals_tsv);
        }

        $this->putMonitor(3, "Nc3ExportBlog End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：掲示板（Bbs）の移行
     */
    private function nc3ExportBbs($redo)
    {
        $this->putMonitor(3, "Nc3ExportBbs Start.");
        $timer_start = $this->timerStart();

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
            $this->putMonitor(3, "Nc3ExportBbs End: no data.");
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
            $journals_ini .= "use_view_count_spectator = 1\n";                              // 表示件数リストを表示ON
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
            $journals_ini .= "journal_id = \"BBS_" . $nc3_bbs->id . "\"\n";
            $journals_ini .= "room_id = " . $nc3_bbs->room_id . "\n";
            $journals_ini .= "module_name = \"bbses\"\n";
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

            $journals_ini_key = "\n";
            $journals_ini_key .= "[content_keys]\n";    // インポートでMigrationMappingにセット用。その後プラグイン固有リンク置換で使う

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
                $journals_ini     .= "post_title[" . $nc3_bbs_post->id . "] = \"" . str_replace('"', '', $nc3_bbs_post->title) . "\"\n";

                $journals_ini_key .= "content_key[" . $nc3_bbs_post->id . "] = \"" . $nc3_bbs_post->key . "\"\n";
            }
            $journals_ini .= $journals_ini_key;

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

        $this->putMonitor(3, "Nc3ExportBbs End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：FAQ（faqs）の移行
     */
    private function nc3ExportFaq($redo)
    {
        $this->putMonitor(3, "Nc3ExportFaq Start.");
        $timer_start = $this->timerStart();

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
            $this->putMonitor(3, "Nc3ExportFaq End: no data.");
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
            $faqs_ini .= "module_name     = \"faqs\"\n";
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

            $faqs_ini_key = "\n";
            $faqs_ini_key .= "[content_keys]\n";    // インポートでMigrationMappingにセット用。その後プラグイン固有リンク置換で使う

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
                $faqs_tsv .= $nc3_faq_question->id                              . "\t"; // [5]

                $faqs_ini_key .= "content_key[" . $nc3_faq_question->id . "] = \"" . $nc3_faq_question->key . "\"\n";
            }
            $faqs_ini .= $faqs_ini_key;

            // FAQ の設定
            $this->storagePut($this->getImportPath('faqs/faq_') . $this->zeroSuppress($nc3_faq->id) . '.ini', $faqs_ini);

            // FAQ の記事
            $faqs_tsv = $this->exportStrReplace($faqs_tsv, 'faqs');
            $this->storagePut($this->getImportPath('faqs/faq_') . $this->zeroSuppress($nc3_faq->id) . '.tsv', $faqs_tsv);
        }

        $this->putMonitor(3, "Nc3ExportFaq End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：リンクリスト（links）の移行
     */
    private function nc3ExportLinklist($redo)
    {
        $this->putMonitor(3, "Nc3ExportLinklist Start.");
        $timer_start = $this->timerStart();

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
            $this->putMonitor(3, "Nc3ExportLinklist End: no data.");
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
            $linklists_ini .= "module_name     = \"links\"\n";
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
                $open_new_tab = $nc3_link_frame_setting->open_new_tab ?? 1;

                $linklists_tsv .= str_replace(array("\r", "\n", "\t"), "", $nc3_link->title)        . "\t";
                $linklists_tsv .= str_replace(array("\r", "\n", "\t"), "", $nc3_link->url)          . "\t";
                $linklists_tsv .= str_replace(array("\r", "\n", "\t"), " ", $nc3_link->description) . "\t";
                $linklists_tsv .= $open_new_tab                                                     . "\t"; // [3] 新規ウィンドウで表示
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

        $this->putMonitor(3, "Nc3ExportLinklist End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：汎用データベース（multidatabases）の移行
     */
    private function nc3ExportMultidatabase($redo)
    {
        $this->putMonitor(3, "Nc3ExportMultidatabase Start.");
        $timer_start = $this->timerStart();

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
            $this->putMonitor(3, "Nc3ExportMultidatabase End. No data.");
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

        $older_than_nc3_2_0 = $this->getMigrationConfig('basic', 'older_than_nc3_2_0');

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
            $multidatabase_ini .= "module_name = \"multidatabases\"\n";
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

            if ($older_than_nc3_2_0) {
                // nc3.2.0より古い場合(nc3.1.10で修正)は、生きてる記事でも multidatabase_id=0 のため multidatabase_key で検索する
                // @see https://github.com/NetCommons3/NetCommons3/issues/1280
                $multidatabase_contents = Nc3MultidatabaseContent::where('multidatabase_contents.multidatabase_key', $nc3_multidatabase->key)
                    ->where('multidatabase_contents.is_latest', 1)
                    ->orderBy('multidatabase_contents.id', 'asc')
                    ->get();
            } else {
                // 通常
                $multidatabase_contents = Nc3MultidatabaseContent::where('multidatabase_contents.multidatabase_id', $nc3_multidatabase->id)
                    ->where('multidatabase_contents.is_latest', 1)
                    ->orderBy('multidatabase_contents.id', 'asc')
                    ->get();
            }

            // アップロードファイル
            $multidatabase_uploads = Nc3UploadFile::where('plugin_key', 'multidatabases')
                ->whereIn('content_key', $multidatabase_contents->pluck('key'))
                ->get();

            $multidatabase_ini .= "\n";
            $multidatabase_ini .= "[content_keys]\n";    // インポートでMigrationMappingにセット用。その後プラグイン固有リンク置換で使う

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
                        $multidatabase_upload = $multidatabase_uploads->where('content_key', $multidatabase_content->key)->firstWhere('field_name', $value_no . '_attach') ?? new Nc3UploadFile();
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

                $multidatabase_ini .= "content_key[" . $multidatabase_content->id . "] = \"" . $multidatabase_content->key . "\"\n";
            }

            // データ行の書き出し
            $tsv = $this->exportStrReplace($tsv, 'databases');
            $this->storageAppend($this->getImportPath('databases/database_') . $this->zeroSuppress($nc3_multidatabase->id) . '.tsv', $tsv);

            // detabase の設定
            $this->storagePut($this->getImportPath('databases/database_') . $this->zeroSuppress($nc3_multidatabase->id) . '.ini', $multidatabase_ini);
        }

        $this->putMonitor(3, "Nc3ExportMultidatabase End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：登録フォーム（registrations）の移行
     */
    private function nc3ExportRegistration($redo)
    {
        $this->putMonitor(3, "Nc3ExportRegistration Start.");
        $timer_start = $this->timerStart();

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
            $this->putMonitor(3, "Nc3ExportRegistration End: no data.");
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        // NC3認証キー（閲覧パスワード）
        $nc3_authorization_keys = Nc3AuthorizationKey::where('model', 'Registration')->get();

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
                ['{X-SUBJECT}',   '[[form_name]]'],
                ['{X-TO_DATE}',   '[[to_datetime]]'],
                ['{X-DATA}',      '[[' . NoticeEmbeddedTag::body . ']]'],
                ['{X-PLUGIN_NAME}', '登録フォーム'],
                // 除外
                ['{X-ROOM}', ''],
                ['{X-USER}', ''],
            ];
            foreach ($convert_embedded_tags as $convert_embedded_tag) {
                $mail_subject =     str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_subject);
                $mail_body =        str_ireplace($convert_embedded_tag[0], $convert_embedded_tag[1], $mail_body);
            }

            $after_message = $this->nc3Wysiwyg(null, null, null, null, $nc3_registration->thanks_content, 'registrations');
            // ダブルクォーテーション対策
            $after_message = str_replace('"', '\"', $after_message);

            $access_limit_type = FormAccessLimitType::none;
            // (NC3)キーフレーズによる登録ガードを設けるか | 0:キーフレーズガードは用いない | 1:キーフレーズガードを用いる
            if ($nc3_registration->is_key_pass_use) {
                // 閲覧パスワードON
                $access_limit_type = FormAccessLimitType::password;
            }

            $nc3_authorization_key = $nc3_authorization_keys->firstWhere('content_id', $nc3_registration->id) ?? new Nc3AuthorizationKey();

            // 登録フォーム設定
            $registration_ini = "";
            $registration_ini .= "[form_base]\n";
            $registration_ini .= "forms_name = \""        . $nc3_registration->title . "\"\n";
            $registration_ini .= "form_mode = \""         . FormMode::form . "\"\n";
            $registration_ini .= "access_limit_type = "   . $access_limit_type . "\n";
            $registration_ini .= "form_password = \""     . $nc3_authorization_key->authorization_key . "\"\n";
            $registration_ini .= "mail_send_flag = "      . $mail_send_flag . "\n";
            $registration_ini .= "mail_send_address = \"\"\n";
            $registration_ini .= "user_mail_send_flag = " . $user_mail_send_flag . "\n";
            $registration_ini .= "mail_subject = \""      . $mail_subject . "\"\n";
            $registration_ini .= "mail_format = \""       . $mail_body . "\"\n";
            $registration_ini .= "data_save_flag = 1\n";
            $registration_ini .= "after_message = \""     . $after_message . "\"\n";
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
            $registration_ini .= "module_name     = \"registration\"\n";
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
                $registration_ini .= "caption                    = '" . $registration_question->description  . "'\n";
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

                $registration_answers_query = Nc3RegistrationPage::
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
                    ->where('registration_pages.registration_id', $nc3_registration->id);
                    // ->orderBy('registration_answer_summaries.serial_number', 'desc')
                    // ->orderBy('registration_questions.question_sequence', 'asc')
                    // ->get();

                $older_than_nc3_2_0 = $this->getMigrationConfig('basic', 'older_than_nc3_2_0');
                if ($older_than_nc3_2_0) {
                    // nc3.2.0より古い場合は、serial_number が無いため serial_number でソートしない（nc3.1.6でserial_number追加された）
                    // @see https://github.com/NetCommons3/Registrations/commit/7497b637e7568dd6625b21a2720e4a7a59c21227
                    $registration_answers_query->orderBy('registration_questions.question_sequence', 'asc');
                } else {
                    // 通常
                    $registration_answers_query->orderBy('registration_answer_summaries.serial_number', 'desc')
                                                ->orderBy('registration_questions.question_sequence', 'asc');
                }
                $registration_answers = $registration_answers_query->get();

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

        $this->putMonitor(3, "Nc3ExportRegistration End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：新着情報（Topics）の移行
     */
    private function nc3ExportTopics($redo)
    {
        $this->putMonitor(3, "Nc3ExportTopics Start.");
        $timer_start = $this->timerStart();

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('whatsnews/'));
        }

        // NC3新着情報（Topics）を移行する。
        // topic_frame_settingは表示方法で決定ボタンを押さないとデータできないが、データなくてもdefault値で検索可能。そのためフレームがあれば、新着プラグイン設置済みと判断。フレームを参照
        $nc3_frames = Nc3Frame::
            select('frames.*', 'frames_languages.name as frame_name', 'frames.id as frame_id', 'frames.key as frame_key', 'pages_languages.name as page_name')
            ->join('frames_languages', function ($join) {
                $join->on('frames_languages.frame_id', '=', 'frames.id')
                    ->where('frames_languages.language_id', Nc3Language::language_id_ja);
            })
            ->join('rooms', function ($join) {
                $join->on('rooms.id', '=', 'frames.room_id')
                    ->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID]);
            })
            ->join('boxes', 'boxes.id', '=', 'frames.box_id')
            ->leftJoin('pages_languages', function ($join) {
                $join->on('pages_languages.page_id', '=', 'boxes.page_id')
                    ->where('pages_languages.language_id', Nc3Language::language_id_ja);
            })
            ->where('frames.plugin_key', 'topics')
            ->where('frames.is_deleted', 0)
            ->orderBy('frames.id')
            ->get();

        // 空なら戻る
        if ($nc3_frames->isEmpty()) {
            $this->putMonitor(3, "Nc3ExportTopics End: no data.");
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        $nc3_topic_frame_settings_all = Nc3TopicFrameSetting::get();

        foreach ($nc3_frames as $nc3_frame) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_frame->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // 新着情報設定
            $whatsnew_ini = "";
            $whatsnew_ini .= "[whatsnew_base]\n";

            // 新着情報の名前は、フレームタイトルがあればフレームタイトル。なければページ名＋「の新着情報」。
            $whatsnew_name = '無題';
            if (!empty($nc3_frame->page_name)) {
                $whatsnew_name = $nc3_frame->page_name;
            }
            if (!empty($nc3_frame->frame_name)) {
                $whatsnew_name = $nc3_frame->frame_name;
            }

            $nc3_topic_frame_setting = $nc3_topic_frame_settings_all->firstWhere('frame_key', $nc3_frame->key) ?? new Nc3TopicFrameSetting();

            $whatsnew_ini .= "whatsnew_name = \""  . $whatsnew_name . "\"\n";
            $whatsnew_ini .= "view_pattern = "     . ($nc3_topic_frame_setting->unit_type == 1 ? 0 : 1) . "\n"; // NC3: 0=日数, 1=件数 Connect-CMS: 0=件数, 1=日数
            $whatsnew_ini .= "count = "            . ($nc3_topic_frame_setting->display_number ?? 10) . "\n";
            $whatsnew_ini .= "days = "             . ($nc3_topic_frame_setting->display_days ?? 3) . "\n";
            $whatsnew_ini .= "rss = "              . ($nc3_topic_frame_setting->use_rss_feed ?? 0) . "\n";
            $whatsnew_ini .= "rss_count = "        . ($nc3_topic_frame_setting->display_number ?? 10) . "\n";
            $whatsnew_ini .= "view_posted_name = " . ($nc3_topic_frame_setting->display_created_user ?? 1) . "\n";
            $whatsnew_ini .= "view_posted_at = "   . ($nc3_topic_frame_setting->display_created ?? 1) . "\n";

            // 対象のプラグインを取得（Connect-CMS にまだないものは除外＆ログ出力）
            if ($nc3_topic_frame_setting->select_plugin) {
                $plugin_keys = Nc3TopicFramePlugin::where('frame_key', $nc3_frame->key)->pluck('plugin_key');
                $whatsnew_ini .= "target_plugins = \"" . $this->getCCPluginNamesFromNc3WhatsnewPluginKeys($plugin_keys) . "\"\n";
            } else {
                // 新着対象の全プラグインON
                $plugin_keys = Nc3Plugin::where('display_topics', 1)
                    ->where('language_id', Nc3Language::language_id_ja)
                    ->orderBy('id', 'asc')
                    ->pluck('key');
                $whatsnew_ini .= "target_plugins = \"" . $this->getCCPluginNamesFromNc3WhatsnewPluginKeys($plugin_keys) . "\"\n";
            }

            // 特定のルームの特定のブロックを表示 の移行：未対応
            $whatsnew_ini .= "frame_select = 0\n";

            $whatsnew_ini .= "read_more_use_flag = 1\n";

            if ($nc3_topic_frame_setting->id) {
                $created_at      = $this->getCCDatetime($nc3_topic_frame_setting->created);
                $created_name    = Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_topic_frame_setting->created_user);
                $insert_login_id = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_topic_frame_setting->created_user);
                $updated_at      = $this->getCCDatetime($nc3_topic_frame_setting->modified);
                $updated_name    = Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_topic_frame_setting->modified_user);
                $update_login_id = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_topic_frame_setting->modified_user);
            } else {
                $created_at      = $this->getCCDatetime($nc3_frame->created);
                $created_name    = Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_frame->created_user);
                $insert_login_id = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_frame->created_user);
                $updated_at      = $this->getCCDatetime($nc3_frame->modified);
                $updated_name    = Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_frame->modified_user);
                $update_login_id = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_frame->modified_user);
            }

            // NC3 情報
            $whatsnew_ini .= "\n";
            $whatsnew_ini .= "[source_info]\n";
            $whatsnew_ini .= "whatsnew_block_id = " . $nc3_frame->id . "\n";
            $whatsnew_ini .= "room_id         = "   . $nc3_frame->room_id . "\n";
            $whatsnew_ini .= "module_name     = \"topics\"\n";
            $whatsnew_ini .= "created_at      = \"" . $created_at . "\"\n";
            $whatsnew_ini .= "created_name    = \"" . $created_name . "\"\n";
            $whatsnew_ini .= "insert_login_id = \"" . $insert_login_id . "\"\n";
            $whatsnew_ini .= "updated_at      = \"" . $updated_at . "\"\n";
            $whatsnew_ini .= "updated_name    = \"" . $updated_name . "\"\n";
            $whatsnew_ini .= "update_login_id = \"" . $update_login_id . "\"\n";

            // 新着情報の設定を出力
            $this->storagePut($this->getImportPath('whatsnews/whatsnew_') . $this->zeroSuppress($nc3_frame->id) . '.ini', $whatsnew_ini);
        }

        $this->putMonitor(3, "Nc3ExportTopics End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：キャビネット（cabinets）の移行
     */
    private function nc3ExportCabinet($redo)
    {
        $this->putMonitor(3, "Nc3ExportCabinet Start.");
        $timer_start = $this->timerStart();

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
            $this->putMonitor(3, "Nc3ExportCabinet End: no data.");
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
            $ini .= "module_name     = \"cabinets\"\n";
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

            $ini_key = "\n";
            $ini_key .= "[content_keys]\n";    // インポートでMigrationMappingにセット用。その後プラグイン固有リンク置換で使う

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
                $tsv .= str_replace(array("\r\n", "\r", "\n", "\t"), '', $cabinet_file->description) . "\t";
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

                $ini_key .= "content_key[" . $cabinet_file->id . "] = \"" . $cabinet_file->key . "\"\n";
            }
            $ini .= $ini_key;

            // キャビネットの設定を出力
            $this->storagePut($this->getImportPath('cabinets/cabinet_') . $this->zeroSuppress($cabinet->id) . '.ini', $ini);
            $tsv = $this->exportStrReplace($tsv, 'cabinets');
            $this->storagePut($this->getImportPath('cabinets/cabinet_') . $this->zeroSuppress($cabinet->id) . '.tsv', $tsv);
        }

        $this->putMonitor(3, "Nc3ExportCabinet End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：カウンター（access_counters）の移行
     */
    private function nc3ExportCounter($redo)
    {
        $this->putMonitor(3, "Nc3ExportCounter Start.");
        $timer_start = $this->timerStart();

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
            $this->putMonitor(3, "Nc3ExportCounter End: no data.");
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
            $ini .= "counter_name = '" . $nc3_counter->name . "'\n";
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
            $ini .= "module_name     = \"access_counters\"\n";
            $ini .= "created_at      = \"" . $this->getCCDatetime($nc3_counter->created) . "\"\n";
            $ini .= "created_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_counter->created_user) . "\"\n";
            $ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_counter->created_user) . "\"\n";
            $ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_counter->modified) . "\"\n";
            $ini .= "updated_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_counter->modified_user) . "\"\n";
            $ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_counter->modified_user) . "\"\n";

            // カウンターの設定を出力
            $this->storagePut($this->getImportPath('counters/counter_') . $this->zeroSuppress($nc3_counter->id) . '.ini', $ini);
        }

        $this->putMonitor(3, "Nc3ExportCounter End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：カレンダー（calendars）の移行
     */
    private function nc3ExportCalendar($redo)
    {
        $this->putMonitor(3, "Nc3ExportCalendar Start.");
        $timer_start = $this->timerStart();

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
            $nc3_rooms_query = $nc3_rooms_query->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID]);
        } else {
            // プライベートルームをエクスポート（=移行）する
            $nc3_rooms_query = $nc3_rooms_query->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID, Nc3Space::PRIVATE_SPACE_ID]);
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
            $ini .= "module_name = \"calendars\"\n";


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

            $ini_key = "\n";
            $ini_key .= "[content_keys]\n";    // インポートでMigrationMappingにセット用。その後プラグイン固有リンク置換で使う

            foreach ($calendar_events as $calendar_event) {

                // 初期化
                $tsv_record = $tsv_cols;

                $tsv_record['post_id'] = $calendar_event->id;
                $tsv_record['title'] = str_replace(array("\r", "\n", "\t"), '', $calendar_event->title);
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
                // 状態
                $tsv_record['status']          = $this->convertCCStatusFromNc3Status($calendar_event->status);

                $tsv .= implode("\t", $tsv_record) . "\n";

                $ini_key .= "content_key[" . $calendar_event->id . "] = \"" . $calendar_event->key . "\"\n";
            }
            $ini .= $ini_key;

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
            $use_workflow = Nc3BlockSetting::getNc3BlockSettingValue($block_settings, $nc3_calendar->block_key, 'use_workflow', $nc3_room->need_approval ?? '0');

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
            $ini .= "module_name = \"calendars\"\n";

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
            $this->putMonitor(3, "Nc3ExportCalendar End.", $this->timerEnd($timer_start));
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
            $ini .= "module_name       = \"calendars\"\n";

            // カレンダーの設定を出力
            $this->storagePut($this->getImportPath('calendars/calendar_block_') . $this->zeroSuppress($nc3_calendar_frame_setting->frame_id) . '.ini', $ini);
        }

        $this->putMonitor(3, "Nc3ExportCalendar End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：施設予約の移行
     */
    private function nc3ExportReservation($redo)
    {
        $this->putMonitor(3, "Nc3ExportReservation Start.");
        $timer_start = $this->timerStart();

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('reservations/'));
        }

        // ・NC3ルーム一覧とって、NC3予定データを移行する
        //   ※ ルームなしはありえない（必ずパブリックルームがあるため）
        // ・NC3施設予約フレーム（配置したフレーム（どう見せるか、だけ。ここ無くても予定データある））を移行する。

        // 施設カテゴリ
        // ----------------------------------------------------
        $block = Nc3Block::where('plugin_key', 'reservations')->first();

        // 空なら戻る
        if (empty($block)) {
            $this->putMonitor(3, "Nc3ExportReservation End: no data.");
            return;
        }

        // カテゴリ
        $nc3_reservation_categories = Nc3Category::getCategoriesByBlockIds([$block->id]);
        foreach ($nc3_reservation_categories as $nc3_reservation_category) {

            // (cc)表示順=1（カテゴリなし）あり＋(nc3)の並び順は1から、のため+1
            $display_sequence = $nc3_reservation_category->display_sequence + 1;

            // NC3 施設カテゴリ設定
            $ini = "";
            $ini .= "[reservation_category]\n";
            // カテゴリ名
            $ini .= "category_name = \"" . $nc3_reservation_category->name . "\"\n";
            // 表示順
            $ini .= "display_sequence = " . $display_sequence . "\n";
            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "category_id = " . $nc3_reservation_category->id . "\n";
            $ini .= "module_name = \"reservations\"\n";

            // 施設予約の設定を出力
            $this->storagePut($this->getImportPath('reservations/reservation_category_') . $this->zeroSuppress($nc3_reservation_category->id) . '.ini', $ini);
        }

        // NC3施設のエクスポート
        // ----------------------------------------------------
        $where_reservation_location_ids = $this->getMigrationConfig('reservations', 'nc3_export_where_reservation_location_ids');
        if (empty($where_reservation_location_ids)) {
            $nc3_reservation_locations = Nc3ReservationLocation::orderBy('category_id')->orderBy('weight')->get();
        } else {
            $nc3_reservation_locations = Nc3ReservationLocation::whereIn('id', $where_reservation_location_ids)->orderBy('category_id')->orderBy('weight')->get();
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        // 施設管理者
        $nc3_reservation_locations_approval_users = Nc3ReservationLocationsApprovalUser::select('reservation_locations_approval_users.location_key', 'users.handlename')
            ->join('users', function ($join) {
                $join->on('users.id', '=', 'reservation_locations_approval_users.user_id');
            })
            ->orderBy('reservation_locations_approval_users.id')
            ->get();

        // 予約できる権限
        $nc3_reservation_location_reservables = Nc3ReservationLocationsReservable::get();

        // 並び順（nc3の施設並び順が壊れていて、1番目から0,3,4,5 だったため、並び順を振りなおす）
        $display_sequence = 1;
        $before_category_id = null;

        foreach ($nc3_reservation_locations as $nc3_reservation_location) {
            // 表示順初期化
            if ($before_category_id != $nc3_reservation_location->category_id) {
                $display_sequence = 1;
            }

            // NC3 施設カテゴリ設定
            $ini = "";
            $ini .= "[reservation_location]\n";
            // カテゴリID
            $ini .= "category_id = " . $nc3_reservation_location->category_id . "\n";
            // 施設名
            $ini .= "location_name = \"" . $nc3_reservation_location->location_name . "\"\n";

            // 予約できる権限
            // 一般
            $is_reservable_general_user = Nc3ReservationLocationsReservable::getReservableValue($nc3_reservation_location_reservables, $nc3_reservation_location->key, 'general_user');
            // 編集者
            $is_reservable_editor       = Nc3ReservationLocationsReservable::getReservableValue($nc3_reservation_location_reservables, $nc3_reservation_location->key, 'editor');
            // 編集長
            $is_reservable_chief_editor = Nc3ReservationLocationsReservable::getReservableValue($nc3_reservation_location_reservables, $nc3_reservation_location->key, 'chief_editor');

            if ($is_reservable_general_user) {
                // 一般まで
                $ini .= "is_limited_by_role = " . ReservationLimitedByRole::not_limited . "\n";
            } elseif ($is_reservable_editor) {
                // 編集者まで
                $ini .= "is_limited_by_role = " . ReservationLimitedByRole::limited . "\n";
            } elseif ($is_reservable_chief_editor) {
                // 編集長まで
                $ini .= "is_limited_by_role = " . ReservationLimitedByRole::limited . "\n";
            } else {
                // ルーム管理者のみ
                $ini .= "is_limited_by_role = " . ReservationLimitedByRole::limited . "\n";
            }

            // 利用曜日 例）Sun|Mon|Tue|Wed|Thu|Fri|Sat
            $time_tables = explode('|', $nc3_reservation_location->time_table);
            // 変換
            $convert_day_of_week = [
                'Sun' => DayOfWeek::sun,
                'Mon' => DayOfWeek::mon,
                'Tue' => DayOfWeek::tue,
                'Wed' => DayOfWeek::wed,
                'Thu' => DayOfWeek::thu,
                'Fri' => DayOfWeek::fri,
                'Sat' => DayOfWeek::sat,
            ];
            $day_of_weeks = [];
            foreach ($time_tables as $time_table) {
                $day_of_weeks[] = $convert_day_of_week[$time_table];
            }
            $ini .= "day_of_weeks = \"" . implode('|', $day_of_weeks) . "\"\n";

            $start_time = (new Carbon($nc3_reservation_location->start_time, 'UTC'))->timezone($nc3_reservation_location->timezone);
            $end_time = (new Carbon($nc3_reservation_location->end_time, 'UTC'))->timezone($nc3_reservation_location->timezone);
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

            // 利用時間-開始 例）2022-02-03 15:00:00 = 15(+9) = 24:00
            $ini .= "start_time = " . $start_time->format('H:i:s') . "\n";
            // 利用時間-終了 例）2022-02-04 15:00:00 = 15(+9) = 翌日24:00
            $ini .= "end_time = " . $end_time_str . "\n";
            // 並び順
            // $ini .= "display_sequence = " . $nc3_reservation_location->weight . "\n";
            $ini .= "display_sequence = " . $display_sequence . "\n";
            // 施設管理者
            $ini .= "facility_manager_name = \"" . $nc3_reservation_locations_approval_users->where('location_key', $nc3_reservation_location->key)->pluck('handlename')->implode(', ') . "\"\n";
            // 補足
            $ini .= "supplement = \"" . str_replace('"', '\"', $nc3_reservation_location->detail) . "\"\n";
            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "location_id = " . $nc3_reservation_location->id . "\n";
            $ini .= "module_name = \"reservations\"\n";

            $display_sequence++;

            // 施設予約の予約
            // ----------------------------------------------------
            // カラムのヘッダー及びTSV 行毎の枠準備
            $tsv_header = "reserve_id" . "\t" . "reserve_details_id" . "\t" . "title" . "\t" .
                "allday_flag" . "\t" . "start_time_full" . "\t" . "end_time_full" . "\t" .
                "contact" . "\t" . "description" . "\t" . "rrule" . "\t" .
                // 登録日・更新日等
                "created_at" . "\t" . "created_name" . "\t" . "insert_login_id" . "\t" . "updated_at" . "\t" . "updated_name" . "\t" . "update_login_id" . "\t" .
                // CC 状態
                "status";

            $tsv_cols['reserve_id'] = "";
            $tsv_cols['reserve_details_id'] = "";
            $tsv_cols['title'] = "";
            $tsv_cols['allday_flag'] = "";
            $tsv_cols['start_time_full'] = "";
            $tsv_cols['end_time_full'] = "";
            // 連絡先
            $tsv_cols['contact'] = "";
            // 内容
            $tsv_cols['description'] = "";
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

            // 施設予約の予約
            $reservation_events = Nc3ReservationEvent::select('reservation_events.*', 'reservation_rrules.rrule')
                ->leftJoin('reservation_rrules', function ($join) {
                    $join->on('reservation_rrules.id', '=', 'reservation_events.reservation_rrule_id');
                })
                ->where('reservation_events.location_key', $nc3_reservation_location->key)
                ->where('reservation_events.is_latest', 1)
                ->orderBy('reservation_events.id', 'asc')
                ->get();

            // カラムデータのループ
            Storage::delete($this->getImportPath('reservations/reservation_location_reserve_') . $this->zeroSuppress($nc3_reservation_location->id) . '.tsv');

            $tsv = '';
            $tsv .= $tsv_header . "\n";

            $ini_key = "\n";
            $ini_key .= "[content_keys]\n";    // インポートでMigrationMappingにセット用。その後プラグイン固有リンク置換で使う

            foreach ($reservation_events as $reservation_event) {
                // 初期化
                $tsv_record = $tsv_cols;

                $tsv_record['reserve_id']         = $reservation_event->id;
                $tsv_record['reserve_details_id'] = $reservation_event->reservation_rrule_id;   // reserve_details_idがないため、reservation_rrule_idで代用
                $tsv_record['title']              = $reservation_event->title;
                $tsv_record['allday_flag']        = $reservation_event->is_allday;
                // 予定開始日時
                $tsv_record['start_time_full']    = (new Carbon($reservation_event->dtstart, 'UTC'))->timezone($reservation_event->timezone);
                // 予定終了日時
                $tsv_record['end_time_full']      = (new Carbon($reservation_event->dtend, 'UTC'))->timezone($reservation_event->timezone);
                // 連絡先
                $tsv_record['contact']            = $reservation_event->contact;
                // 内容 [WYSIWYG]
                $tsv_record['description']        = $this->nc3Wysiwyg(null, null, null, null, $reservation_event->description, 'reservations');
                // 繰り返し条件
                $tsv_record['rrule']              = $reservation_event->rrule;
                // システム項目
                $tsv_record['created_at']      = $this->getCCDatetime($reservation_event->created);
                $tsv_record['created_name']    = Nc3User::getNc3HandleFromNc3UserId($nc3_users, $reservation_event->created_user);
                $tsv_record['insert_login_id'] = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $reservation_event->created_user);
                $tsv_record['updated_at']      = $this->getCCDatetime($reservation_event->modified);
                $tsv_record['updated_name']    = Nc3User::getNc3HandleFromNc3UserId($nc3_users, $reservation_event->modified_user);
                $tsv_record['update_login_id'] = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $reservation_event->modified_user);

                // 状態
                // (nc3)
                // const STATUS_PUBLISHED = '1';
                // const STATUS_APPROVAL_WAITING = '2';
                // const STATUS_IN_DRAFT = '3';
                // const STATUS_DISAPPROVED = '4';

                // (NC3)status -> (Connect)status
                $convert_statuses = [
                    1 => StatusType::active,
                    2 => StatusType::approval_pending,
                    3 => StatusType::active,            // ccの施設予約に一時保存はないため、公開で移行
                    4 => StatusType::approval_pending,  // 差し戻しは承認待ちへ
                ];
                $status = $convert_statuses[$reservation_event->status] ?? StatusType::active;
                if ($reservation_event->status == 3) {
                    $this->putMonitor(1, '施設の予約の一時保存は公開で移行します。', "施設名={$nc3_reservation_location->location_name}|件名={$reservation_event->title}|予約ID={$reservation_event->id}");
                }

                $tsv_record['status']          = $status;

                $tsv .= implode("\t", $tsv_record) . "\n";

                $ini_key .= "content_key[" . $reservation_event->id . "] = \"" . $reservation_event->key . "\"\n";
            }
            $ini .= $ini_key;

            // 施設予約の設定を出力
            $this->storagePut($this->getImportPath('reservations/reservation_location_') . $this->zeroSuppress($nc3_reservation_location->id) . '.ini', $ini);

            // データ行の書き出し
            $tsv = $this->exportStrReplace($tsv, 'reservations');
            $this->storageAppend($this->getImportPath('reservations/reservation_location_') . $this->zeroSuppress($nc3_reservation_location->id) . '.tsv', $tsv);
        }

        // メール設定
        // ----------------------------------------------------
        // 通知を受け取る権限
        $block_role_permissions = Nc3BlockRolePermission::getBlockRolePermissionsByBlockKeys([$block->key]);
        $nc3_space = Nc3Space::find(Nc3Space::PUBLIC_SPACE_ID);
        // ゲスト
        $is_mail_content_receivable_visitor = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $block->key, $nc3_space->room_id_root, 'mail_content_receivable', 'visitor');
        // 一般
        $is_mail_content_receivable_general_user = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $block->key, $nc3_space->room_id_root, 'mail_content_receivable', 'general_user');
        // 編集者
        $is_mail_content_receivable_editor = Nc3BlockRolePermission::getNc3BlockRolePermissionValue($block_role_permissions, $block->key, $nc3_space->room_id_root, 'mail_content_receivable', 'editor');

        $notice_everyone = 0;
        $notice_admin_group = 0;
        $notice_all_moderator_group = 0;
        if ($is_mail_content_receivable_visitor) {
            // ゲストまで：　　全ユーザ通知
            $notice_everyone = 1;

        } elseif ($is_mail_content_receivable_general_user) {
            // 一般まで：　　　全一般ユーザ通知（≒全ユーザ通知）
            $notice_everyone = 1;
            $this->putMonitor(3, '施設予約のメール設定（一般まで）は、全ユーザ通知で移行します。', 'ini_path=' . $this->getImportPath('reservations/reservation_mail') . '.ini');

        } elseif ($is_mail_content_receivable_editor) {
            // 編集者まで：　　全モデレータユーザ通知
            $notice_all_moderator_group = 1;
            $notice_admin_group = 1;

        } else {
            // 編集長まで：　　管理者グループ通知
            $notice_admin_group = 1;
        }

        $mail_settings = Nc3MailSetting::getMailSettingsByBlockKeys([$block->key], 'reservations');

        // 通知メール（データなければblock_key=nullの初期設定取得）
        $mail_setting = $mail_settings->firstWhere('block_key', $block->key) ?? $mail_settings->firstWhere('block_key', null);
        $mail_subject = $mail_setting->mail_fixed_phrase_subject;
        $mail_body = $mail_setting->mail_fixed_phrase_body;

        // サイト設定
        $site_settings = Nc3SiteSetting::where('language_id', Nc3Language::language_id_ja)->get();

        // 承認メール
        $approval_subject = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_mail_subject');
        $approval_body = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_mail_body');

        // 承認完了メール
        $approved_subject = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_completion_mail_subject');
        $approved_body = Nc3SiteSetting::getNc3SiteSettingValueByKey($site_settings, 'Workflow.approval_completion_mail_body');

        // --- メール配信設定
        // [{X-SITE_NAME}]予約通知
        //
        // 施設に予約が追加されたのでお知らせします。
        //
        // 件名:{X-SUBJECT}
        // 公開対象:{X-ROOM}
        // 開始日時:{X-START_TIME}
        // 終了日時:{X-END_TIME}
        // 場所:{X-LOCATION}
        // 連絡先:{X-CONTACT}
        // 繰返し:{X-RRULE}
        // 記入者:{X-USER}
        // 記入日時:{X-TO_DATE}
        //
        // {X-BODY}
        //
        // この予約を見るには、下記アドレスへ
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
            ['{X-SITE_NAME}',  '[[' . NoticeEmbeddedTag::site_name . ']]'],
            // [[title]]は、施設管理の項目「タイトルの設定」で変わるため、タイトルの埋め込みタグは[[X-件名]]に変換する。
            ['{X-SUBJECT}',    '[[X-件名]]'],
            ['{X-USER}',       '[[' . NoticeEmbeddedTag::created_name . ']]'],
            ['{X-TO_DATE}',    '[[' . NoticeEmbeddedTag::created_at . ']]'],
            ['{X-BODY}',       '[[X-補足]]'],
            ['{X-URL}',        '[[' . NoticeEmbeddedTag::url . ']]'],
            ['開始日時:{X-START_TIME}', '利用日時:[[' . ReservationNoticeEmbeddedTag::booking_time . ']]'],
            ['{X-START_TIME}', '[[' . ReservationNoticeEmbeddedTag::booking_time . ']]'],
            ['{X-LOCATION}',   '[[' . ReservationNoticeEmbeddedTag::facility_name . ']]'],
            ['{X-CONTACT}',    '[[X-連絡先]]'],
            ['{X-RRULE}',      '[[' . ReservationNoticeEmbeddedTag::rrule . ']]'],

            ['{X-PLUGIN_NAME}', '施設予約'],
            // 除外
            ['公開対象:{X-ROOM}', ''],
            ['終了日時:{X-END_TIME}', ''],
            ['{X-ROOM}', ''],
            ['{X-END_TIME}', ''],
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

        // 施設予約のメール設定
        $ini = "";
        $ini .= "[reservation_mail]\n";
        // メール通知する
        $ini .= "notice_on = " . $mail_setting->is_mail_send . "\n";
        // 全ユーザ通知
        $ini .= "notice_everyone = " . $notice_everyone . "\n";
        // 全モデレータユーザ通知
        $ini .= "notice_all_moderator_group = " . $notice_all_moderator_group . "\n";
        // 管理者グループ通知
        $ini .= "notice_admin_group = " . $notice_admin_group . "\n";

        $ini .= "mail_subject = \"" . $mail_subject . "\"\n";
        $ini .= "mail_body = \"" . $mail_body . "\"\n";
        $ini .= "approval_on = " . $mail_setting->is_mail_send_approval . "\n";
        $ini .= "approval_admin_group = 1\n";                                   // 1:「管理者グループ」通知
        $ini .= "approval_subject = \"" . $approval_subject . "\"\n";
        $ini .= "approval_body = \"" . $approval_body . "\"\n";
        $ini .= "approved_on = 0\n";                                            // 承認完了通知はメール飛ばなかった
        $ini .= "approved_author = 0\n";                                        // 1:投稿者へ通知する
        $ini .= "approved_admin_group = 0\n";                                   // 1:「管理者グループ」通知
        $ini .= "approved_subject = \"" . $approved_subject . "\"\n";
        $ini .= "approved_body = \"" . $approved_body . "\"\n";

        // 施設予約の設定を出力
        $this->storagePut($this->getImportPath('reservations/reservation_mail') . '.ini', $ini);

        // NC3施設予約フレーム（モ配置したフレーム（どう見せるか、だけ。ここ無くても予約データある））を移行する。
        // ----------------------------------------------------
        $nc3_reservation_frame_settings_query = Nc3ReservationFrameSetting::
            select(
                'reservation_frame_settings.*',
                'frames.id as frame_id',
                'frames.room_id',
                'frames_languages.name as frame_name',
                'pages_languages.name as page_name',
                'rooms_languages.name as room_name',
                'reservation_locations.id as location_id',
            )
            ->leftJoin('frames', function ($join) {
                $join->on('frames.key', '=', 'reservation_frame_settings.frame_key');
            })
            ->leftJoin('frames_languages', function ($join) {
                $join->on('frames_languages.frame_id', '=', 'frames.id')
                    ->where('frames_languages.language_id', Nc3Language::language_id_ja);
            })
            ->leftJoin('boxes', function ($join) {
                $join->on('boxes.id', '=', 'frames.box_id');
            })
            ->leftJoin('pages_languages', function ($join) {
                $join->on('pages_languages.page_id', '=', 'boxes.page_id')
                    ->where('pages_languages.language_id', Nc3Language::language_id_ja);
            })
            ->leftJoin('rooms_languages', function ($join) {
                $join->on('rooms_languages.room_id', 'frames.room_id')
                    ->where('rooms_languages.language_id', Nc3Language::language_id_ja);
            })
            ->leftJoin('reservation_locations', function ($join) {
                $join->on('reservation_locations.key', 'reservation_frame_settings.location_key')
                    ->where('rooms_languages.language_id', Nc3Language::language_id_ja);
            })
            ->orderBy('reservation_frame_settings.id');

        $where_reservation_frame_ids = $this->getMigrationConfig('reservations', 'nc3_export_where_reservation_frame_ids');
        if ($where_reservation_frame_ids) {
            $nc3_reservation_frame_settings_query = $nc3_reservation_frame_settings_query->whereIn('frame_id', $where_reservation_frame_ids);
        }
        $nc3_reservation_frame_settings = $nc3_reservation_frame_settings_query->get();

        // 空なら戻る
        if ($nc3_reservation_frame_settings->isEmpty()) {
            $this->putMonitor(3, "Nc3ExportReservation End.", $this->timerEnd($timer_start));
            return;
        }

        // エクスポート対象の施設予約名をページ名から取得する（指定がなければフレームタイトルがあればフレームタイトル。なければページ名）
        $reservation_name_is_page_name = $this->getMigrationConfig('reservations', 'nc3_export_reservation_name_is_page_name');

        foreach ($nc3_reservation_frame_settings as $nc3_reservation_frame_setting) {

            // NC3 施設予約（表示方法）設定
            $ini = "";
            $ini .= "[reservation_block]\n";

            // 表示方法
            // (nc3)
            // 1: 週表示(カテゴリ別)
            // 2: 日表示(カテゴリ別)
            // 3: 月表示(施設別)
            // 4: 週表示(施設別)

            // 表示方法 変換 (key:nc3)display_type => (value:cc) reservation_initial_display_type
            $reservation_initial_display_types = [
                1 => ReservationCalendarDisplayType::week,
                2 => ReservationCalendarDisplayType::week,
                3 => ReservationCalendarDisplayType::month,
                4 => ReservationCalendarDisplayType::week,
            ];
            $display_type = $reservation_initial_display_types[$nc3_reservation_frame_setting->display_type] ?? ReservationCalendarDisplayType::month;

            $ini .= "display_type = " . $display_type . "\n";

            // 最初に表示する施設
            // ※ 表示方法=月・週(施設別)表示のみ設定される
            $ini .= "location_id = " . $nc3_reservation_frame_setting->location_id . "\n";

            // 施設予約の名前は、フレームタイトルがあればフレームタイトル。なければページ名。
            $reservation_name = '無題';
            if (!empty($nc3_reservation_frame_setting->page_name)) {
                $reservation_name = $nc3_reservation_frame_setting->page_name;
            }
            if (empty($reservation_name_is_page_name)) {
                if (!empty($nc3_reservation_frame_setting->frame_name)) {
                    $reservation_name = $nc3_reservation_frame_setting->frame_name;
                }
            }
            $ini .= "reservation_name = \""  . $reservation_name . "\"\n";

            // NC3 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "reservation_block_id = " . $nc3_reservation_frame_setting->frame_id . "\n";    // block_idがないため、frame_idで代用
            $ini .= "room_id              = " . $nc3_reservation_frame_setting->room_id . "\n";
            $ini .= "room_name            = \"" . $nc3_reservation_frame_setting->room_name . "\"\n";
            $ini .= "module_name          = \"reservations\"\n";

            // 施設予約の設定を出力
            $this->storagePut($this->getImportPath('reservations/reservation_block_') . $this->zeroSuppress($nc3_reservation_frame_setting->frame_id) . '.ini', $ini);
        }

        $this->putMonitor(3, "Nc3ExportReservation End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：フォトアルバム（Photoalbums）の移行
     */
    private function nc3ExportPhotoalbum($redo)
    {
        $this->putMonitor(3, "Nc3ExportPhotoalbum Start.");
        $timer_start = $this->timerStart();

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('photoalbums/'));
        }

        // NC3ルーム一覧
        $nc3_rooms = Nc3Room::select('rooms.*', 'rooms_languages.name as room_name')
            ->join('rooms_languages', function ($join) {
                $join->on('rooms_languages.room_id', 'rooms.id')
                    ->where('rooms_languages.language_id', Nc3Language::language_id_ja);
            })
            ->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID])
            ->orderBy('rooms.id')
            ->get();

        // NC3フォトアルバム（Photoalbums）を移行する。
        $nc3_photoalbums_query = Nc3Photoalbum::select('photo_albums.*', 'blocks.key as block_key', 'blocks.room_id', 'rooms.space_id')
            ->join('blocks', function ($join) {
                $join->on('blocks.id', '=', 'photo_albums.block_id')
                    ->where('blocks.plugin_key', 'photo_albums');
            })
            ->join('rooms', function ($join) {
                $join->on('rooms.id', '=', 'blocks.room_id')
                    ->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID]);
            })
            ->where('photo_albums.is_latest', 1)
            ->orderBy('photo_albums.id');

        $nc3_export_where_photoalbum_ids = $this->getMigrationConfig('photoalbums', 'nc3_export_where_photoalbum_ids');
        if ($nc3_export_where_photoalbum_ids) {
            $nc3_photoalbums_query = $nc3_photoalbums_query->whereIn('photo_albums.id', $nc3_export_where_photoalbum_ids);
        }
        $nc3_photoalbums = $nc3_photoalbums_query->get();

        // 空なら戻る
        if ($nc3_photoalbums->isEmpty()) {
            $this->putMonitor(3, "Nc3ExportPhotoalbum End: no data.");
            return;
        }


        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        $nc3_photoalbum_photos_all = Nc3PhotoAlbumPhoto::where('is_latest', 1)->orderBy('id')->get();

        // アップロードファイル
        $photoalbum_uploads_all = Nc3UploadFile::where('plugin_key', 'photo_albums')->get();

        // 表示アルバム
        $photo_album_display_albums_all = Nc3PhotoAlbumDisplayAlbum::get();

        // nc3 uploads_path の取得
        $nc3_uploads_path = $this->getExportUploadsPath();

        // nc3はフォトアルバムのバケツがないため、ルーム単位で出力する
        // 例）name=パブリックルームのフォトアルバム

        // ルーム単位で出力
        foreach ($nc3_rooms as $nc3_room) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_room->id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // 空なら移行しない
            $nc3_photoalbum_alubums = $nc3_photoalbums->where('room_id', $nc3_room->id);
            if ($nc3_photoalbum_alubums->isEmpty()) {
                continue;
            }

            // データベース設定
            $photoalbum_ini = "";
            $photoalbum_ini .= "[photoalbum_base]\n";
            $photoalbum_ini .= "photoalbum_name = \"" . $nc3_room->room_name . "のフォトアルバム\"\n";

            // NC3 情報
            $photoalbum_ini .= "\n";
            $photoalbum_ini .= "[source_info]\n";
            $photoalbum_ini .= "photoalbum_id   = " . $nc3_room->id . "\n";
            $photoalbum_ini .= "room_id         = " . $nc3_room->id . "\n";
            $photoalbum_ini .= "module_name     = \"photoalbums\"\n";

            // アルバム 情報
            $photoalbum_ini .= "\n";
            $photoalbum_ini .= "[albums]\n";

            $photoalbum_ini_key = "\n";
            $photoalbum_ini_key .= "[album_keys]\n";    // インポートでMigrationMappingにセット用。その後プラグイン固有リンク置換で使う

            foreach ($nc3_photoalbum_alubums as $nc3_photoalbum_alubum) {
                $photoalbum_ini     .= "album["     . $nc3_photoalbum_alubum->id . "] = \"" . $nc3_photoalbum_alubum->name . "\"\n";
                $photoalbum_ini_key .= "album_key[" . $nc3_photoalbum_alubum->id . "] = \"" . $nc3_photoalbum_alubum->key . "\"\n";
            }
            $photoalbum_ini .= $photoalbum_ini_key;
            $photoalbum_ini .= "\n";

            // アルバム詳細 情報
            foreach ($nc3_photoalbum_alubums as $nc3_photoalbum_alubum) {
                $album_upload = $photoalbum_uploads_all->where('field_name', 'jacket')->firstWhere('content_key', $nc3_photoalbum_alubum->key) ?? new Nc3UploadFile();

                // 画像：原寸
                $image_file_path = $nc3_uploads_path . $album_upload->path . $album_upload->id . '/' . $album_upload->real_file_name;
                $image_width = 0;
                $image_height = 0;
                if (File::exists($image_file_path)) {
                    list($image_width, $image_height) = getimagesize($image_file_path);
                } else {
                    $this->putError(3, "Image file not exists: " . $nc3_uploads_path . $album_upload->path . $album_upload->id . '/' . $album_upload->real_file_name);
                }

                $photoalbum_ini .= "[" . $nc3_photoalbum_alubum->id . "]" . "\n";
                $photoalbum_ini .= "album_id                   = \"" . $nc3_photoalbum_alubum->id . "\"\n";
                $photoalbum_ini .= "album_name                 = \"" . $nc3_photoalbum_alubum->name . "\"\n";
                $photoalbum_ini .= "album_description          = \"" . $nc3_photoalbum_alubum->description . "\"\n";
                $photoalbum_ini .= "public_flag                = 1\n";   // 1:公開
                $photoalbum_ini .= "upload_id                  = "   . $album_upload->id . "\n";
                $photoalbum_ini .= "width                      = {$image_width}\n";
                $photoalbum_ini .= "height                     = {$image_height}\n";
                $photoalbum_ini .= "created_at                 = \"" . $this->getCCDatetime($nc3_photoalbum_alubum->created) . "\"\n";
                $photoalbum_ini .= "created_name               = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_photoalbum_alubum->created_user) . "\"\n";
                $photoalbum_ini .= "insert_login_id            = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_alubum->created_user) . "\"\n";
                $photoalbum_ini .= "updated_at                 = \"" . $this->getCCDatetime($nc3_photoalbum_alubum->modified) . "\"\n";
                $photoalbum_ini .= "updated_name               = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_photoalbum_alubum->modified_user) . "\"\n";
                $photoalbum_ini .= "update_login_id            = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_alubum->modified_user) . "\"\n";
                $photoalbum_ini .= "\n";
            }

            // フォトアルバム の設定
            $this->storagePut($this->getImportPath('photoalbums/photoalbum_') . $this->zeroSuppress($nc3_room->id) . '.ini', $photoalbum_ini);

            // カラムのヘッダー及びTSV 行毎の枠準備
            $tsv_header = "photo_id" . "\t" . "upload_id" . "\t" . "video_upload_id" . "\t" . "photo_name" . "\t" . "photo_description" . "\t" . "width" . "\t" ."height" . "\t" .
                "created_at" . "\t" . "created_name" . "\t" . "insert_login_id" . "\t" . "updated_at" . "\t" . "updated_name" . "\t" . "update_login_id";

            $tsv_cols['photo_id'] = "";
            $tsv_cols['upload_id'] = "";
            $tsv_cols['video_upload_id'] = "";
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

                Storage::delete($this->getImportPath('photoalbums/photoalbum_') . $this->zeroSuppress($nc3_room->id) . '_' . $this->zeroSuppress($nc3_photoalbum_alubum->id) . '.tsv');

                $tsv = '';
                $tsv .= $tsv_header . "\n";

                $nc3_photoalbum_photos = $nc3_photoalbum_photos_all->where('album_key', $nc3_photoalbum_alubum->key);
                foreach ($nc3_photoalbum_photos as $nc3_photoalbum_photo) {

                    // 初期化
                    $tsv_record = $tsv_cols;

                    $photo_upload = $photoalbum_uploads_all->where('field_name', 'photo')->firstWhere('content_key', $nc3_photoalbum_photo->key) ?? new Nc3UploadFile();

                    // 画像：原寸
                    $image_file_path = $nc3_uploads_path . $photo_upload->path . $photo_upload->id . '/' . $photo_upload->real_file_name;
                    $image_width = 0;
                    $image_height = 0;
                    if (File::exists($image_file_path)) {
                        list($image_width, $image_height) = getimagesize($image_file_path);
                    } else {
                        $this->putError(3, "Image file not exists: " . $nc3_uploads_path . $photo_upload->path . $photo_upload->id . '/' . $photo_upload->real_file_name);
                    }

                    $nc3_photoalbum_photo->title = str_replace(array("\r\n", "\r", "\n"), "", $nc3_photoalbum_photo->title);
                    $nc3_photoalbum_photo->description = str_replace(array("\r\n", "\r", "\n"), "", $nc3_photoalbum_photo->description);

                    $tsv_record['photo_id']          = $nc3_photoalbum_photo->id;
                    $tsv_record['upload_id']         = $photo_upload->id;
                    $tsv_record['video_upload_id']   = '';
                    $tsv_record['photo_name']        = $nc3_photoalbum_photo->title;
                    $tsv_record['photo_description'] = $nc3_photoalbum_photo->description;
                    $tsv_record['width']             = $image_width;
                    $tsv_record['height']            = $image_height;
                    $tsv_record['created_at']        = $this->getCCDatetime($nc3_photoalbum_photo->created);
                    $tsv_record['created_name']      = Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_photoalbum_photo->created_user);
                    $tsv_record['insert_login_id']   = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_photo->created_user);
                    $tsv_record['updated_at']        = $this->getCCDatetime($nc3_photoalbum_photo->modified);
                    $tsv_record['updated_name']      = Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_photoalbum_photo->modified_user);
                    $tsv_record['update_login_id']   = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_photo->modified_user);

                    $tsv .= implode("\t", $tsv_record) . "\n";
                }

                // データ行の書き出し
                $tsv = $this->exportStrReplace($tsv, 'photoalbums');
                $this->storageAppend($this->getImportPath('photoalbums/photoalbum_') . $this->zeroSuppress($nc3_room->id) . '_' . $this->zeroSuppress($nc3_photoalbum_alubum->id) . '.tsv', $tsv);
            }

            // スライド表示はスライダーにも移行
            $nc3_photoalbum_frame_settings = Nc3Frame::select('photo_album_frame_settings.*', 'frames.id as frame_id')
                ->join('photo_album_frame_settings', function ($join) {
                    $join->on('photo_album_frame_settings.frame_key', '=', 'frames.key');
                })
                ->where('photo_album_frame_settings.display_type', Nc3PhotoAlbumFrameSetting::DISPLAY_SLIDESHOW)
                ->whereIn('frames.block_id', $nc3_photoalbum_alubums->pluck('block_id'))
                ->get();

            // NC3スライダー（Slideshow）のループ
            foreach ($nc3_photoalbum_frame_settings as $nc3_photoalbum_frame_setting) {
                // アルバム
                $photo_album_display_album = Nc3PhotoAlbumDisplayAlbum::where('frame_key', $nc3_photoalbum_frame_setting->frame_key)->orderBy('created', 'desc')->first();
                if (!$photo_album_display_album) {
                    continue;
                }
                $nc3_photoalbum_alubum = $nc3_photoalbum_alubums->firstWhere('key', $photo_album_display_album->album_key) ?? new Nc3Photoalbum();

                // (nc3)5000ミリ秒固定 => (cc)ミリ秒
                // @see (nc3) app\Plugin\PhotoAlbums\View\PhotoAlbumPhotos\slide.ctp
                $image_interval = 5000;

                $height = $nc3_photoalbum_frame_setting->slide_height;
                if ($height == 0) {
                    // height=0(自動)の場合、固定値をセット
                    $height = $this->getMigrationConfig('photoalbums', 'nc3_export_slideshow_convert_auto_height_value', 250);
                }

                // スライダー設定
                $slide_ini = "";
                $slide_ini .= "[slideshow_base]\n";
                $slide_ini .= "slideshows_name = \"{$nc3_photoalbum_alubum->name}\"\n";
                $slide_ini .= "image_interval  = {$image_interval}\n";
                $slide_ini .= "height          = {$height}\n";

                // NC3 情報
                $slide_ini .= "\n";
                $slide_ini .= "[source_info]\n";
                $slide_ini .= "slideshows_block_id = " . $nc3_photoalbum_frame_setting->frame_id . "\n";    // block_idは無いため、frame_idで代用
                $slide_ini .= "photoalbum_id       = " . $nc3_photoalbum_alubum->id . "\n";
                $slide_ini .= "photoalbum_name     = \"" . $nc3_photoalbum_alubum->name . "\"\n";
                $slide_ini .= "room_id             = " . $nc3_photoalbum_alubum->room_id . "\n";
                $slide_ini .= "module_name     = \"photoalbums\"\n";
                $slide_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_photoalbum_frame_setting->created) . "\"\n";
                $slide_ini .= "created_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_photoalbum_frame_setting->created_user) . "\"\n";
                $slide_ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_frame_setting->created_user) . "\"\n";
                $slide_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_photoalbum_frame_setting->modified) . "\"\n";
                $slide_ini .= "updated_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_photoalbum_frame_setting->modified_user) . "\"\n";
                $slide_ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_photoalbum_frame_setting->modified_user) . "\"\n";

                // 写真（並び順指定のため、DBから再取得）
                // photos_sort=PhotoAlbumPhoto.modified
                $photos_sorts = explode('.', $nc3_photoalbum_frame_setting->photos_sort);
                $nc3_photoalbum_photos = Nc3PhotoAlbumPhoto::where('album_key', $nc3_photoalbum_alubum->key)
                    ->where('is_latest', 1)
                    ->orderBy($photos_sorts[1], $nc3_photoalbum_frame_setting->photos_direction)
                    ->get();

                // TSV でエクスポート
                // image_path{\t}uploads_id{\t}link_url{\t}link_target{\t}caption{\t}display_flag{\t}display_sequence
                $slides_tsv = "";
                foreach ($nc3_photoalbum_photos as $i => $nc3_photoalbum_photo) {

                    $display_sequence = $i + 1;

                    $photo_upload = $photoalbum_uploads_all->where('field_name', 'photo')->firstWhere('content_key', $nc3_photoalbum_photo->key) ?? new Nc3UploadFile();

                    // TSV 形式でエクスポート
                    if (!empty($slides_tsv)) {
                        $slides_tsv .= "\n";
                    }

                    $nc3_photoalbum_photo->description = str_replace(array("\r\n", "\r", "\n"), "", $nc3_photoalbum_photo->description);

                    $slides_tsv .= "\t";                                        // image_path
                    $slides_tsv .= $photo_upload->id . "\t";                    // uploads_id
                    $slides_tsv .= "\t";                                        // link_url
                    $slides_tsv .= "\t";                                        // link_target
                    $slides_tsv .= "{$nc3_photoalbum_photo->description}\t";    // caption
                    $slides_tsv .= "1\t";                                       // display_flag
                    $slides_tsv .= $display_sequence . "\t";                    // display_sequence
                }

                // スライダーの設定を出力
                $this->storagePut($this->getImportPath('slideshows/slideshows_') . $this->zeroSuppress($nc3_photoalbum_frame_setting->frame_id) . '.ini', $slide_ini);
                // スライダーの付与情報を出力
                $this->storagePut($this->getImportPath('slideshows/slideshows_') . $this->zeroSuppress($nc3_photoalbum_frame_setting->frame_id) . '.tsv', $slides_tsv);
            }
        }

        $this->putMonitor(3, "Nc3ExportPhotoalbum End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：検索（searches）の移行
     */
    private function nc3ExportSearch($redo)
    {
        $this->putMonitor(3, "Nc3ExportSearch Start.");
        $timer_start = $this->timerStart();

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('searchs/'));
        }

        // NC3検索（searches）を移行する。
        // search_frame_settingは表示方法で決定ボタンを押さないとデータできないが、データなくてもdefault値で検索可能。そのためフレームがあれば、検索プラグイン設置済みと判断。フレームを参照
        $nc3_frames = Nc3Frame::
            select('frames.*', 'frames_languages.name as frame_name', 'frames.id as frame_id', 'frames.key as frame_key', 'pages_languages.name as page_name')
            ->join('frames_languages', function ($join) {
                $join->on('frames_languages.frame_id', '=', 'frames.id')
                    ->where('frames_languages.language_id', Nc3Language::language_id_ja);
            })
            ->join('rooms', function ($join) {
                $join->on('rooms.id', '=', 'frames.room_id')
                    ->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID]);
            })
            ->join('boxes', 'boxes.id', '=', 'frames.box_id')
            ->leftJoin('pages_languages', function ($join) {
                $join->on('pages_languages.page_id', '=', 'boxes.page_id')
                    ->where('pages_languages.language_id', Nc3Language::language_id_ja);
            })
            ->where('frames.plugin_key', 'searches')
            ->where('frames.is_deleted', 0)
            ->orderBy('frames.id')
            ->get();

        // 空なら戻る
        if ($nc3_frames->isEmpty()) {
            $this->putMonitor(3, "Nc3ExportSearch End: no data.");
            return;
        }

        // nc3の全ユーザ取得
        // $nc3_users = Nc3User::get();

        foreach ($nc3_frames as $nc3_frame) {
            $room_ids = $this->getMigrationConfig('basic', 'nc3_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc3_frame->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // 検索設定
            $search_ini = "";
            $search_ini .= "[search_base]\n";

            // 検索の名前は、フレームタイトルがあればフレームタイトル。
            $search_name = '無題';
            if (!empty($nc3_frame->page_name)) {
                $search_name = $nc3_frame->page_name;
            }
            if (!empty($nc3_frame->frame_name)) {
                $search_name = $nc3_frame->frame_name;
            }

            $search_ini .= "search_name      = \"{$search_name}\"\n";
            $search_ini .= "count            = 20\n";   // 表示件数
            $search_ini .= "view_posted_name = 1\n";    // 登録者の表示
            $search_ini .= "view_posted_at   = 1\n";    // 登録日時の表示

            // 対象のプラグインを取得（Connect-CMS にまだないものは除外＆ログ出力）
            $plugin_keys = Nc3SearchFramePlugin::where('frame_key', $nc3_frame->frame_key)->pluck('plugin_key');
            // 表示方法で「決定」ボタン押さないと、表示方法データなし。だけど検索できる。defaultは下記。
            // @see (nc3) app\Plugin\Searches\Model\SearchFrameSetting.php
            $plugin_keys = $plugin_keys->isEmpty() ? ['announcements', 'bbses', 'blogs'] : $plugin_keys;
            $search_ini .= "target_plugins = \"" . $this->getCCPluginNamesFromNc3SearchPluginKeys($plugin_keys) . "\"\n";

            // NC3 情報
            $search_ini .= "\n";
            $search_ini .= "[source_info]\n";
            $search_ini .= "search_block_id = " . $nc3_frame->frame_id . "\n";   // block_idは無いためframe_idで代用
            $search_ini .= "room_id         = " . $nc3_frame->room_id . "\n";
            $search_ini .= "module_name     = \"searches\"\n";
            $search_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_frame->created) . "\"\n";
            // $search_ini .= "created_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_frame->created_user) . "\"\n";
            // $search_ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_frame->created_user) . "\"\n";
            $search_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_frame->modified) . "\"\n";
            // $search_ini .= "updated_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_frame->modified_user) . "\"\n";
            // $search_ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_frame->modified_user) . "\"\n";

            // 新着情報の設定を出力
            $this->storagePut($this->getImportPath('searchs/search_') . $this->zeroSuppress($nc3_frame->frame_id) . '.ini', $search_ini);
        }

        $this->putMonitor(3, "Nc3ExportSearch End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：動画（videos）の移行
     */
    private function nc3ExportVideo($redo)
    {
        $this->putMonitor(3, "Nc3ExportVideo Start.");
        $timer_start = $this->timerStart();

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            $import_file_paths = glob($this->getImportPath('photoalbums/photoalbum_video_*'));
            foreach ($import_file_paths as $import_file_path) {
                Storage::delete($import_file_path);
            }
        }

        // NC3動画（videos）を移行する。
        $nc3_blocks = Nc3Block::select('blocks.*', 'blocks.key as block_key', 'rooms.space_id', 'blocks_languages.name')
            ->join('blocks_languages', function ($join) {
                $join->on('blocks_languages.block_id', '=', 'blocks.id')
                    ->where('blocks_languages.language_id', Nc3Language::language_id_ja);
            })
            ->join('rooms', function ($join) {
                $join->on('rooms.id', '=', 'blocks.room_id')
                    ->whereIn('rooms.space_id', [Nc3Space::PUBLIC_SPACE_ID, Nc3Space::COMMUNITY_SPACE_ID]);
            })
            ->where('blocks.plugin_key', 'videos')
            ->orderBy('blocks.id')
            ->get();

        // 空なら戻る
        if ($nc3_blocks->isEmpty()) {
            $this->putMonitor(3, "Nc3ExportVideo End: no data.");
            return;
        }

        // nc3の全ユーザ取得
        $nc3_users = Nc3User::get();

        $nc3_videos_all = Nc3Video::where('is_latest', 1)->orderBy('id')->get();

        // アップロードファイル
        $videos_uploads_all = Nc3UploadFile::where('plugin_key', 'videos')->get();

        // nc3 uploads_path の取得
        $nc3_uploads_path = $this->getExportUploadsPath();

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

            // 空なら移行しない
            $nc3_videos = $nc3_videos_all->where('block_id', $nc3_block->id);
            if ($nc3_videos->isEmpty()) {
                continue;
            }

            // データベース設定
            $photoalbum_ini = "";
            $photoalbum_ini .= "[photoalbum_base]\n";
            $photoalbum_ini .= "photoalbum_name = \"" . $nc3_block->name . "\"\n";

            // NC3 情報
            $photoalbum_ini .= "\n";
            $photoalbum_ini .= "[source_info]\n";
            $photoalbum_ini .= "photoalbum_id   = \"VIDEO_" . $nc3_block->id . "\"\n";
            $photoalbum_ini .= "room_id         = " . $nc3_block->room_id . "\n";
            $photoalbum_ini .= "module_name     = \"videos\"\n";
            $photoalbum_ini .= "created_at      = \"" . $this->getCCDatetime($nc3_block->created) . "\"\n";
            $photoalbum_ini .= "created_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_block->created_user) . "\"\n";
            $photoalbum_ini .= "insert_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_block->created_user) . "\"\n";
            $photoalbum_ini .= "updated_at      = \"" . $this->getCCDatetime($nc3_block->modified) . "\"\n";
            $photoalbum_ini .= "updated_name    = \"" . Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_block->modified_user) . "\"\n";
            $photoalbum_ini .= "update_login_id = \"" . Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_block->modified_user) . "\"\n";

            // アルバム 情報
            $photoalbum_ini .= "\n";
            $photoalbum_ini .= "[albums]\n";
            $photoalbum_ini .= "album[" . $nc3_block->id . "] = \"" . $nc3_block->name . "\"\n";
            $photoalbum_ini .= "\n";

            // カラムのヘッダー及びTSV 行毎の枠準備
            $tsv_header = "photo_id" . "\t" . "upload_id" . "\t" . "video_upload_id" . "\t" . "photo_name" . "\t" . "photo_description" . "\t" . "width" . "\t" ."height" . "\t" .
                "created_at" . "\t" . "created_name" . "\t" . "insert_login_id" . "\t" . "updated_at" . "\t" . "updated_name" . "\t" . "update_login_id";

            $tsv_cols['photo_id'] = "";
            $tsv_cols['upload_id'] = "";
            $tsv_cols['video_upload_id'] = "";
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

            // videos->photoalbums移行の場合は、photoalbums用のフォルダに吐き出す
            // $export_path = 'videos/video_';
            // if ($this->plugin_name['videos'] === 'photoalbums') {
            //     $export_path = 'photoalbums/photoalbum_video_';
            // }
            $export_path = 'photoalbums/photoalbum_video_';

            Storage::delete($this->getImportPath($export_path) . $this->zeroSuppress($nc3_block->id) . '_' . $this->zeroSuppress($nc3_block->id) . '.tsv');

            $tsv = '';
            $tsv .= $tsv_header . "\n";

            $photoalbum_ini_key = "\n";
            $photoalbum_ini_key .= "[content_keys]\n";    // インポートでMigrationMappingにセット用。その後プラグイン固有リンク置換で使う

            foreach ($nc3_videos as $nc3_video) {
                // 初期化
                $tsv_record = $tsv_cols;

                $video_upload = $videos_uploads_all->where('field_name', 'video_file')->firstWhere('content_key', $nc3_video->key) ?? new Nc3UploadFile();
                $poster_upload = $videos_uploads_all->where('field_name', 'thumbnail')->firstWhere('content_key', $nc3_video->key) ?? new Nc3UploadFile();

                // 動画
                $video_file_path = $nc3_uploads_path . $video_upload->path . $video_upload->id . '/' . $video_upload->real_file_name;
                if (!File::exists($video_file_path)) {
                    $this->putError(3, "Video file not exists: " . $video_file_path);
                }

                $tsv_record['photo_id']          = $nc3_video->id;
                $tsv_record['upload_id']         = $poster_upload->id;
                $tsv_record['video_upload_id']   = $video_upload->id;
                $tsv_record['photo_name']        = $nc3_video->title;
                $tsv_record['photo_description'] = $this->nc3Wysiwyg(null, null, null, null, $nc3_video->description, 'videos');
                $tsv_record['width']             = '';
                $tsv_record['height']            = '';
                $tsv_record['created_at']        = $this->getCCDatetime($nc3_video->created);
                $tsv_record['created_name']      = Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_video->created_user);
                $tsv_record['insert_login_id']   = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_video->created_user);
                $tsv_record['updated_at']        = $this->getCCDatetime($nc3_video->modified);
                $tsv_record['updated_name']      = Nc3User::getNc3HandleFromNc3UserId($nc3_users, $nc3_video->modified_user);
                $tsv_record['update_login_id']   = Nc3User::getNc3LoginIdFromNc3UserId($nc3_users, $nc3_video->modified_user);

                $tsv .= implode("\t", $tsv_record) . "\n";

                $photoalbum_ini_key .= "content_key[" . $nc3_video->id . "] = \"" . $nc3_video->key . "\"\n";
            }
            $photoalbum_ini .= $photoalbum_ini_key;

            // 動画 の設定
            $this->storagePut($this->getImportPath($export_path) . $this->zeroSuppress($nc3_block->id) . '.ini', $photoalbum_ini);

            // データ行の書き出し
            $tsv = $this->exportStrReplace($tsv, 'videos');
            $this->storageAppend($this->getImportPath($export_path) . $this->zeroSuppress($nc3_block->id) . '_' . $this->zeroSuppress($nc3_block->id) . '.tsv', $tsv);
        }

        $this->putMonitor(3, "Nc3ExportVideo End.", $this->timerEnd($timer_start));
    }

    /**
     * NC3：ページ内のフレームをループ
     */
    private function nc3Frame(Nc3Page $nc3_page, int $new_page_index, Nc3Page $nc3_top_page)
    {
        $export_ommit_frames = $this->getMigrationConfig('frames', 'export_ommit_frames');
        $export_ommit_menu = $this->getMigrationConfig('menus', 'export_ommit_menu');
        $nc3_page_language_id = $nc3_page->language_id;
        if ($nc3_page->id == $nc3_page->page_id_top) {
            // ルームのトップページ
            // boxes.page_id = null（共通エリア（サイト全体・パブ共通・ルーム共通））は後処理でとるため、ここで取らない

            // 指定されたページ内のフレームを取得
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
                ->join('boxes_page_containers', function ($join) {
                    $join->on('boxes_page_containers.box_id', '=', 'boxes.id')
                    ->where('boxes_page_containers.is_published', 1);      // 有効なデータ
                })
                ->join('frames_languages', function ($join) use ($nc3_page_language_id) {
                    $join->on('frames_languages.frame_id', '=', 'frames.id')
                    ->where('frames_languages.language_id', '=', $nc3_page_language_id);
                })
                ->join('languages', function ($join) {
                    $join->on('languages.id', '=', 'frames_languages.language_id')
                        ->where('languages.is_active', 1);  // 使用言語（日本語・英語）で有効な言語を取得
                })
                ->leftJoin('blocks', 'blocks.id', '=', 'frames.block_id')
                ->where('boxes.page_id', $nc3_page->id)
                ->where('frames.is_deleted', 0);

            // 対象外のフレームがあれば加味する。
            if (!empty($export_ommit_frames)) {
                $nc3_frames_query->whereNotIn('frames.id', $export_ommit_frames);
            }

            // メニューが対象外なら除外する。
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

        } else {
            // ルームのトップページ以外
            // boxes.page_id = null（パブ共通・ルーム共通）はここでとるけど、boxes.type 1:サイト全体はいらない。
            // トップページ・ルームトップで取得したboxes.idがあったら取り除く。
            //
            // boxes.type           1:サイト全体, 2:スペース, 3:ルーム, 4:ページ
            // boxes.container_type 1:Header, 2:Major, 3:Main, 4:Minor, 5:Footer

            $nc3_frames_query = Nc3PageContainer::
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
                ->join('boxes_page_containers', function ($join) {
                    $join->on('boxes_page_containers.page_container_id', '=', 'page_containers.id')
                    ->where('boxes_page_containers.is_published', 1);      // 有効なデータ
                })
                ->join('boxes', function ($join) {
                    $join->on('boxes.id', '=', 'boxes_page_containers.box_id')
                        ->where('boxes.type', '!=', 1);                  // 1:サイト全体 以外
                })
                ->join('frames', function ($join) {
                    $join->on('frames.box_id', '=', 'boxes.id')
                    ->where('frames.is_deleted', 0);      // 有効なデータ
                })
                ->join('frames_languages', function ($join) use ($nc3_page_language_id) {
                    $join->on('frames_languages.frame_id', '=', 'frames.id')
                    ->where('frames_languages.language_id', '=', $nc3_page_language_id);
                })
                ->join('languages', function ($join) {
                    $join->on('languages.id', '=', 'frames_languages.language_id')
                        ->where('languages.is_active', 1);  // 使用言語（日本語・英語）で有効な言語を取得
                })
                ->leftJoin('blocks', 'blocks.id', '=', 'frames.block_id')
                ->where('page_containers.page_id', $nc3_page->id);

            // 対象外のフレームがあれば加味する。
            if (!empty($export_ommit_frames)) {
                $nc3_frames_query->whereNotIn('frames.id', $export_ommit_frames);
            }

            // メニューが対象外なら除外する。
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


            // ここでトップページ・ルームトップの除外
            $container_types = [
                Nc3Box::container_type_header,
                Nc3Box::container_type_left,
                Nc3Box::container_type_right,
                Nc3Box::container_type_footer
            ];
            foreach ($container_types as $container_type) {
                $nc3_boxes_arr = $nc3_frames->where('container_type', $container_type)->pluck('box_id')->toArray();

                // 例えばトップページのヘッダーエリアのbox_idと差がなければ、トップページと同じものが配置されているため、結果から取り除く
                $nc3_boxes_diff_top = array_diff($nc3_boxes_arr, $this->exported_common_top_page_box_ids[$container_type]);
                if (empty($nc3_boxes_diff_top)) {
                    $nc3_frames = $nc3_frames->whereNotIn('box_id', $this->exported_common_top_page_box_ids[$container_type]);
                }

                // 例えばルームトップのヘッダーエリアのbox_idと差がなければ、ルームトップと同じものが配置されているため、結果から取り除く
                $exported_room_top_page_box_ids = Arr::get($this->exported_room_top_page_box_ids, "{$nc3_page->room_id}.{$container_type}", []);
                $nc3_boxes_diff_room = array_diff($nc3_boxes_arr, $exported_room_top_page_box_ids);
                if (empty($nc3_boxes_diff_room) && $exported_room_top_page_box_ids) {
                    $nc3_frames = $nc3_frames->whereNotIn('box_id', $exported_room_top_page_box_ids);
                }
            }
        }

        // フレームをループ
        $frame_index = 0; // フレームの連番

        // [Connect出力] 割り切り実装
        // ・サイトトップページ　　　　：ヘッダ・フッタ・左・右は、（サイト全体・パブ共通・ルーム共通・当ページのみ）であっても、Connectでは結果として、サイト全体設定として扱われる。
        // ・ルームのトップページ　　　：（サイト全体・パブ共通・ルーム共通）ヘッダ・フッタ・左・右を出力
        //  　　　 ・（ヘッダ・フッタ）　サイトトップとbox_idが違ければ出力
        //   　　　・（左・右）　　　　　サイトトップとbox_id（複数）が違ければframe_idが同じでも出力
        // ・トップ・ルームトップ以外　：ヘッダ・フッタ・左・右でトップ・ルームトップとbox_idが違ければ(当ページのみ)で出力。
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
                ->where('boxes.container_type', '!=', Nc3Box::container_type_main)    // メインエリアは使いまわさないため、除外
                ->get();

            // エリア毎に違いがあれば、そのbox_id取得して加える
            $container_types = [
                Nc3Box::container_type_header,
                Nc3Box::container_type_left,
                Nc3Box::container_type_right,
                Nc3Box::container_type_footer
            ];
            $common_box_ids = [];
            foreach ($container_types as $container_type) {
                // 差があれば、元のnc3_boxesをセット
                $nc3_boxes_arr = $nc3_boxes->where('container_type', $container_type)->pluck('id')->toArray();
                $nc3_boxes_diff = array_diff($nc3_boxes_arr, $this->exported_common_top_page_box_ids[$container_type]);
                if ($nc3_boxes_diff) {
                    $common_box_ids = array_merge_recursive($common_box_ids, $nc3_boxes_arr);
                }
            }

            // box_idを使って指定されたページ内のフレーム取得
            // ※ select()の内容は、既に取ってる $nc3_frames に追加するため、同じにする必要あり
            $nc3_common_frames_query = Nc3Frame::
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

            if ($nc3_page->id == $nc3_top_page->id) {
                // サイトトップページのbox_idを保持
                $this->exported_common_top_page_box_ids = [
                    Nc3Box::container_type_header => $nc3_boxes->where('container_type', Nc3Box::container_type_header)->pluck('id')->toArray(),
                    Nc3Box::container_type_left   => $nc3_boxes->where('container_type', Nc3Box::container_type_left)->pluck('id')->toArray(),
                    Nc3Box::container_type_right  => $nc3_boxes->where('container_type', Nc3Box::container_type_right)->pluck('id')->toArray(),
                    Nc3Box::container_type_footer => $nc3_boxes->where('container_type', Nc3Box::container_type_footer)->pluck('id')->toArray(),
                ];
            } else {
                // ルームのトップページのbox_idを保持
                $this->exported_room_top_page_box_ids[$nc3_page->room_id] = [
                    Nc3Box::container_type_header => $nc3_boxes->where('container_type', Nc3Box::container_type_header)->pluck('id')->toArray(),
                    Nc3Box::container_type_left   => $nc3_boxes->where('container_type', Nc3Box::container_type_left)->pluck('id')->toArray(),
                    Nc3Box::container_type_right  => $nc3_boxes->where('container_type', Nc3Box::container_type_right)->pluck('id')->toArray(),
                    Nc3Box::container_type_footer => $nc3_boxes->where('container_type', Nc3Box::container_type_footer)->pluck('id')->toArray(),
                ];
            }
        }

        // メニューのフレームタイトルを消さずに残す
        $export_frame_title = $this->getMigrationConfig('menus', 'export_frame_title');

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
                if ($export_frame_title) {
                    // メニューのフレームタイトルを残す
                    $frame_ini .= "frame_title = \"" . $nc3_frame->frame_name . "\"\n";
                } else {
                    $frame_ini .= "frame_title = \"\"\n";
                }
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
                $frame_ini .= "frame_design = \"" . Nc3Frame::getFrameDesign($nc3_frame->header_type, $this->getMigrationConfig('frames', 'export_frame_default_design', 'default')) . "\"\n";
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
            } elseif ($nc3_frame->plugin_key == 'bbses') {
                // NC3 フレーム設定の取得
                $nc3_bbs_frame_setting = Nc3BbsFrameSetting::where('frame_key', $nc3_frame->key)->firstOrNew([]);

                // 表示形式 変換
                // (nc) all:全件一覧, root:根記事一覧,flat:フラット
                // (cc) default:デフォルト, no_frame:枠なし
                // (key:nc3)display_type => (value:cc)テンプレート
                $convert_view_formats = [
                    'all'  => 'no_frame',
                    'root' => 'no_frame',
                    'flat' => 'default',
                ];
                $template = $convert_view_formats[$nc3_bbs_frame_setting->display_type] ?? 'default';

                $frame_ini .= "template = \"{$template}\"\n";
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
                // Connectの限定公開はFrom-Toどちらも必須&timestamp型のため、nullの場合はtimestamp型上限加減の値をセットする
                $publish_start = $this->getCCDatetime($nc3_frame->publish_start) ?? new Carbon('1970-01-02 00:00:00');
                $publish_end   = $this->getCCDatetime($nc3_frame->publish_end) ?? new Carbon('2038-01-01 00:00:00');
                $frame_ini .= "content_open_date_from = \"{$publish_start}\"\n";
                $frame_ini .= "content_open_date_to = \"{$publish_end}\"\n";
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
            if ($plugin_name == 'Development' || $plugin_name == 'Abolition') {
                // 移行できなかったNC3プラグイン
                $this->putError(3, "no migrate nc3-plugin", "プラグイン = " . $nc3_frame->plugin_key, $nc3_frame);
            }
        }
    }

    /**
     * NC3：NC3フレームの上書き
     */
    private function overrideNc3Frame($nc3_frame)
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
    private function nc3FrameMainDataId($nc3_frame): string
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
            // ブロックがない場合は対象外
            if (!empty($nc3_block)) {
                // linklist_idないため、block_idで代用
                $ret = "linklist_id = \"" . $this->zeroSuppress($nc3_block->id) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'multidatabases') {
            $nc3_multidatabase = Nc3Multidatabase::where('block_id', $nc3_frame->block_id)->first();
            if (empty($nc3_multidatabase)) {
                $this->putError(3, "Nc3Multidatabase not found.", "block_id = " . $nc3_frame->block_id, $nc3_frame);
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
            // topic_frame_settingは表示方法で決定ボタンを押さないとデータできないが、データなくてもdefault値で検索可能。そのためフレームがあれば、新着プラグイン設置済みと判断。
            // $nc3_topic_frame_setting = Nc3TopicFrameSetting::where('frame_key', $nc3_frame->key)->first();
            // block_idないため、frame_idで代用
            $ret = "whatsnew_block_id = \"" . $this->zeroSuppress($nc3_frame->id) . "\"\n";
        } elseif ($nc3_frame->plugin_key == 'cabinets') {
            $nc3_cabinet = Nc3Cabinet::where('block_id', $nc3_frame->block_id)->first();
            // ブロックがあり、キャビネットがない場合は対象外
            if (!empty($nc3_cabinet)) {
                $ret = "cabinet_id = \"" . $this->zeroSuppress($nc3_cabinet->id) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'menus') {
            // メニューの非表示設定を加味する。
            $nc3_menu_frame_pages_hidden = Nc3MenuFramePage::select('menu_frames_pages.*', 'pages.id as page_id')
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
                $ommit_nc3_pages = $nc3_menu_frame_pages_hidden->pluck('page_id')->toArray();
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
                    // NC3フォトアルバムにblock_idはないため、frame_idで代用
                    $ret = "slideshows_block_id = \"" . $this->zeroSuppress($nc3_frame->id) . "\"\n";
                } else {
                    $ret = "photoalbum_id = \"" . $this->zeroSuppress($nc3_photoalbum->id) . "\"\n";
                }
            }
        } elseif ($nc3_frame->plugin_key == 'videos') {
            // プラグイン固有のデータまとめテーブルがないため、ブロックテーブル参照
            $nc3_block = Nc3Block::find($nc3_frame->block_id);
            // ブロックがない場合は対象外
            if (!empty($nc3_block)) {
                // photoalbum_idないため、block_idで代用
                $ret = "photoalbum_id = \"" . $this->zeroSuppress($nc3_block->id) . "\"\n";
            }
        } elseif ($nc3_frame->plugin_key == 'searches') {
            // search_frame_settingは表示方法で決定ボタンを押さないとデータできないが、データなくてもdefault値で検索可能。そのためフレームがあれば、検索プラグイン設置済みと判断。
            // $nc3_search_frame_setting = Nc3SearchFrameSetting::where('frame_key', $nc3_frame->key)->first();
            // block_idないため、frame_idで代用
            $ret = "search_block_id = \"" . $this->zeroSuppress($nc3_frame->id) . "\"\n";
        }
        return $ret;
    }

    /**
     * NC3：ページ内のフレームに配置されているプラグインのエクスポート。
     * プラグインごとのエクスポート処理に振り分け。
     */
    private function nc3FrameExport($nc3_frame, int $new_page_index, string $frame_index_str): void
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
        } elseif ($plugin_name == 'photoalbums') {
            if ($nc3_frame->plugin_key == 'videos') {
                // 動画 -> フォトアルバム
                $this->nc3FrameExportPhotoalbumVideos($nc3_frame, $new_page_index, $frame_index_str);
            } else {
                // フォトアルバム
                $this->nc3FrameExportPhotoalbums($nc3_frame, $new_page_index, $frame_index_str);
            }
        } elseif ($plugin_name == 'blogs') {
            // ブログ
            $this->nc3FrameExportBlogs($nc3_frame, $new_page_index, $frame_index_str);
        } elseif ($plugin_name == 'faqs') {
            // FAQ
            $this->nc3FrameExportFaqs($nc3_frame, $new_page_index, $frame_index_str);
        }
    }

    /**
     * NC3：固定記事（お知らせ）のエクスポート
     */
    private function nc3FrameExportContents($nc3_frame, int $new_page_index, string $frame_index_str): void
    {
        // お知らせモジュールのデータの取得
        // （NC3になって「続きを読む」機能なくなった。）
        $announcement = Nc3Announcement::where('block_id', $nc3_frame->block_id)->where('is_active', 1)->where('language_id', $nc3_frame->language_id)->firstOrNew([]);

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
    private function nc3FrameExportDatabases($nc3_frame, int $new_page_index, string $frame_index_str): void
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
    private function nc3FrameExportBbses($nc3_frame, int $new_page_index, string $frame_index_str): void
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
    private function nc3FrameExportLinklists($nc3_frame, int $new_page_index, string $frame_index_str): void
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
    private function nc3FrameExportCounters($nc3_frame, int $new_page_index, string $frame_index_str): void
    {
        // NC3 フレーム設定の取得
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
     * NC3：フォトアルバムのフレーム特有部分のエクスポート
     */
    private function nc3FrameExportPhotoalbums($nc3_frame, int $new_page_index, string $frame_index_str): void
    {
        // NC3 フレーム設定の取得
        $photo_album_frame_setting = Nc3PhotoAlbumFrameSetting::where('frame_key', $nc3_frame->key)->firstOrNew([]);

        $ini_filename = "frame_" . $frame_index_str . '.ini';

        $save_folder = $this->getImportPath('pages/') . $this->zeroSuppress($new_page_index);

        // (NC3)albums_order -> (Connect)sort_album 変換
        $convert_sort_albums = [
            Nc3PhotoAlbumFrameSetting::albums_order_new    => PhotoalbumSort::created_desc,
            Nc3PhotoAlbumFrameSetting::albums_order_create => PhotoalbumSort::created_asc,
            Nc3PhotoAlbumFrameSetting::albums_order_title  => PhotoalbumSort::name_asc,
        ];
        $sort_album = $convert_sort_albums[$photo_album_frame_setting->albums_order] ?? PhotoalbumSort::name_asc;

        // (NC3)photos_order -> (Connect)sort_photo 変換
        $convert_sort_photos = [
            Nc3PhotoAlbumFrameSetting::photos_order_new    => PhotoalbumSort::created_desc,
            Nc3PhotoAlbumFrameSetting::photos_order_create => PhotoalbumSort::created_asc,
        ];
        $sort_photo = $convert_sort_photos[$photo_album_frame_setting->photos_order] ?? PhotoalbumSort::name_asc;

        $frame_ini  = "[photoalbum]\n";
        $frame_ini .= "sort_album = \"{$sort_album}\"\n";
        $frame_ini .= "sort_photo = \"{$sort_photo}\"\n";
        $this->storageAppend($save_folder . "/"     . $ini_filename, $frame_ini);
    }

    /**
     * NC3：動画のフレーム特有部分をフォトアルバム設定としてエクスポート
     */
    private function nc3FrameExportPhotoalbumVideos($nc3_frame, int $new_page_index, string $frame_index_str): void
    {
        // NC3 フレーム設定の取得
        $video_frame_setting = Nc3VideoFrameSetting::where('frame_key', $nc3_frame->key)->firstOrNew([]);

        $ini_filename = "frame_" . $frame_index_str . '.ini';

        $save_folder = $this->getImportPath('pages/') . $this->zeroSuppress($new_page_index);

        // (NC3)display_order -> (Connect)sort_album, sort_photo 変換
        $convert_sorts = [
            Nc3VideoFrameSetting::display_order_new    => PhotoalbumSort::created_desc,
            Nc3VideoFrameSetting::display_order_title => PhotoalbumSort::name_asc,
            Nc3VideoFrameSetting::display_order_play  => PhotoalbumSort::name_asc,
            Nc3VideoFrameSetting::display_order_like  => PhotoalbumSort::name_asc,
        ];
        $sort = $convert_sorts[$video_frame_setting->display_order] ?? PhotoalbumSort::name_asc;

        $frame_ini  = "[photoalbum]\n";
        $frame_ini .= "sort_album = \"{$sort}\"\n";
        $frame_ini .= "sort_photo = \"{$sort}\"\n";
        $frame_ini .= "embed_code = " . ShowType::show . "\n";
        $frame_ini .= "posted_at  = " . ShowType::show . "\n";
        $this->storageAppend($save_folder . "/"     . $ini_filename, $frame_ini);
    }

    /**
     * NC3：ブログのフレーム特有部分のエクスポート
     */
    private function nc3FrameExportBlogs($nc3_frame, int $new_page_index, string $frame_index_str): void
    {
        // NC3 フレーム設定の取得
        $nc3_blog_frame_setting = Nc3BlogFrameSetting::where('frame_key', $nc3_frame->key)->first();
        if (empty($nc3_blog_frame_setting)) {
            return;
        }

        $ini_filename = "frame_" . $frame_index_str . '.ini';

        $save_folder = $this->getImportPath('pages/') . $this->zeroSuppress($new_page_index);

        $frame_ini = "[blog]\n";
        $frame_ini .= "view_count = {$nc3_blog_frame_setting->articles_per_page}\n";
        $this->storageAppend($save_folder . "/"     . $ini_filename, $frame_ini);
    }

    /**
     * NC3：FAQのフレーム特有部分のエクスポート
     */
    private function nc3FrameExportFaqs($nc3_frame, int $new_page_index, string $frame_index_str): void
    {
        $ini_filename = "frame_" . $frame_index_str . '.ini';

        $save_folder = $this->getImportPath('pages/') . $this->zeroSuppress($new_page_index);

        $frame_ini = "[faq]\n";
        $frame_ini .= "narrowing_down_type = \"". FaqNarrowingDownType::dropdown . "\"\n";
        $this->storageAppend($save_folder . "/"     . $ini_filename, $frame_ini);
    }

    /**
     * コンテンツのクリーニング
     */
    private function cleaningContent($content, $nc3_plugin_key)
    {
        // 改行コード・タブコードが含まれる場合があるので置換
        $content = str_replace(array("\r", "\n", "\t"), '', $content);

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

        $pattern = '/<img.*?(data-size\s*=\s*[\"\'].*?[\"\']).*?>/i';
        $match_cnt = preg_match_all($pattern, $content, $matches);
        if ($match_cnt) {
            // [1] に中身のみ入ってくる。
            foreach ($matches[1] as $match) {
                // 除去
                $content = str_replace($match . ' ', '', $content);
            }
        }

        $pattern = '/<img.*?(data-position\s*=\s*[\"\'].*?[\"\']).*?>/i';
        $match_cnt = preg_match_all($pattern, $content, $matches);
        if ($match_cnt) {
            // [1] に中身のみ入ってくる。
            foreach ($matches[1] as $match) {
                // 除去
                $content = str_replace($match . ' ', '', $content);
            }
        }

        $pattern = '/<img.*?(data-imgid\s*=\s*[\"\'].*?[\"\']).*?>/i';
        $match_cnt = preg_match_all($pattern, $content, $matches);
        if ($match_cnt) {
            // [1] に中身のみ入ってくる。
            foreach ($matches[1] as $match) {
                // 除去
                $content = str_replace($match . ' ', '', $content);
            }
        }

        $pattern = '/<img.*?(class\s*=\s*[\"\'].*?[\"\']).*?>/i';
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
        $pattern = '/<img.*?title\s*=\s*[\"\'](.*?)[\"\'].*?>/i';
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
    private function nc3Wysiwyg($nc3_frame, ?string $save_folder, ?string $content_filename, ?string $ini_filename, ?string $content, ?string $nc3_plugin_key = null)
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
        // /cabinets/cabinet_files/download/ をエクスポート形式に変換は不要。MigrationTrait::convertNc3PluginPermalink() プラグイン固有リンク置換 で同じ処理されてるため。

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
        // /cabinets/cabinet_files/download/ をエクスポート形式に変換は不要。MigrationTrait::convertNc3PluginPermalink() プラグイン固有リンク置換 で同じ処理されてるため。

        // Google Analytics タグ部分を削除
        $content = MigrationUtils::deleteGATag($content);

        // NC3絵文字を削除
        $content = MigrationUtils::deleteNc3Emoji($content);

        // HTML content の保存
        if ($save_folder) {
            $content = $this->exportStrReplace($content, 'contents');
            $this->storagePut($save_folder . "/" . $content_filename, $content);
        }

        return $content;
    }

    /**
     * NC3：wysiwygのdownload をエクスポート形式に変換
     */
    private function nc3MigrationCommonDownloadMain($nc3_frame, ?string $save_folder, ?string $ini_filename, ?string $content, $paths, string $section_name): ?string
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
    private function nc3MigrationCommonDownloadMainImple(?string $content, array $paths, $nc3_frame): array
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
    private function checkDeadLinkNc3(string $url, string $nc3_plugin_key, $nc3_frame = null): void
    {
        // リンクチェックしない場合は返却
        $check_deadlink_nc3 = $this->getMigrationConfig('basic', 'check_deadlink_nc3', '');
        if (empty($check_deadlink_nc3)) {
            return;
        }

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

        } elseif (in_array($scheme, ['mailto'])) {
            // 対象外
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
        while (isset($headers[$i])) {
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
                $path_tmp = str_replace('wysiwyg/image/download/', '', $path_tmp);
                $path_tmp = str_replace('wysiwyg/file/download/', '', $path_tmp);
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

        // (通常プラグイン)
        //  （対応済み）
        //  ブログ                      http://localhost:8081/blogs/blog_entries/view/27/2e19fea842dd98fe341ad536771b90a8?frame_id=49
        //  汎用DB                      http://localhost:8081/multidatabases/multidatabase_contents/detail/43/50ed8d82a743a87bb78e89f2a654b490?frame_id=43
        //  動画埋込                    http://localhost:8081/setting/videos/videos/embed/55/a66fda57248fe7e64818e2438cac5e7c?frame_id=398
        //  掲示板-親記事               http://localhost:8081/bbses/bbs_articles/view/31/7cc26bc0b09822e45e04956a774e31d8?frame_id=55
        //  掲示板-子記事               http://localhost:8081/bbses/bbs_articles/view/31/7cc26bc0b09822e45e04956a774e31d8?frame_id=55#!#bbs-article-26
        //  キャビネット-フォルダ        http://localhost:8081/cabinets/cabinet_files/index/42/ae8a188d05776556078a79200bbc6b3a?frame_id=378
        //  キャビネット-ファイル        http://localhost:8081/cabinets/cabinet_files/download/42/b203268ac59db031fc8d20a8e4380ef0?frame_id=378
        //  FAQ                        http://localhost:8081/faqs/faq_questions/view/81/a6caf71b3ab8c4220d8a2102575c1f05?frame_id=434
        //  フォトアルバム-アルバム表示  http://localhost:8081/photo_albums/photo_album_photos/index/7/0c5b4369a2ff04786ee5ac0e02273cc9?frame_id=392
        //  施設予約                    http://localhost:8081/reservations/reservation_plans/view/c7fb658e08e5265a9dfada9dee24d8db?frame_id=446
        //  カレンダー                  http://localhost:8081/calendars/calendar_plans/view/05b08f33b1e13953d3caf1e8d1ceeb01?frame_id=463
        //  -----------------------
        //  （cc機能無しのため実装せず）
        //  動画（⇒ccフォトアルバムに詳細ページなし）             http://localhost:8081/videos/videos/view/33/20e8fdb50d8a31a23b542050850260b4?frame_id=24
        //  お知らせ-新着or検索リンク（⇒ccお知らせに固有URLなし）  http://localhost:8081/announcements/announcements/view/107/9d3641e6a1dda574509e42d04f04892a
        //  アンケート-回答                                      http://localhost:8081/questionnaires/questionnaire_answers/view/25/a272c029cefee372dd0623794ebe962a?frame_id=44
        //  小テスト-回答                                        http://localhost:8081/quizzes/quiz_answers/start/86/3edf210b7fa05a5e735b26c0bd988552?frame_id=442
        //  TODO
        //  回覧板

        if ($check_page_permalink) {
            if (stripos($check_page_permalink, 'blogs/blog_entries/view/') !== false) {
                // ブログ
                $this->checkDeadLinkInsideNc3Plugin($check_page_permalink, 'blogs/blog_entries/view/', Nc3BlogEntry::query(), $url, $nc3_plugin_key, $nc3_frame);
                return;
            } elseif (stripos($check_page_permalink, 'multidatabases/multidatabase_contents/detail/') !== false) {
                // 汎用DB
                $this->checkDeadLinkInsideNc3Plugin($check_page_permalink, 'multidatabases/multidatabase_contents/detail/', Nc3MultidatabaseContent::query(), $url, $nc3_plugin_key, $nc3_frame);
                return;
            } elseif (stripos($check_page_permalink, 'videos/videos/view/') !== false) {
                // 動画
                // リンク切れチェックは、CCに機能あるものだけチェックするため、動画⇒フォトアルバムの場合、CCフォトアルバムに詳細URLがないため、チェックしない。
                // $this->checkDeadLinkInsideNc3Plugin($check_page_permalink, 'videos/videos/view/', Nc3Video::query(), $url, $nc3_plugin_key, $nc3_frame);
                // return;
            } elseif (stripos($check_page_permalink, 'videos/videos/embed/') !== false) {
                // 動画埋込
                $this->checkDeadLinkInsideNc3Plugin($check_page_permalink, 'videos/videos/embed/', Nc3Video::query(), $url, $nc3_plugin_key, $nc3_frame);
                return;
            } elseif (stripos($check_page_permalink, 'bbses/bbs_articles/view/') !== false) {
                // 掲示板
                $this->checkDeadLinkInsideNc3Plugin($check_page_permalink, 'bbses/bbs_articles/view/', Nc3BbsArticle::query(), $url, $nc3_plugin_key, $nc3_frame);
                return;
            } elseif (stripos($check_page_permalink, 'cabinets/cabinet_files/index/') !== false) {
                // キャビネット-フォルダ
                $cabinet_files_query = Nc3CabinetFile::join('cabinets', 'cabinets.key', '=', 'cabinet_files.cabinet_key');
                $this->checkDeadLinkInsideNc3Plugin($check_page_permalink, 'cabinets/cabinet_files/index/', $cabinet_files_query, $url, $nc3_plugin_key, $nc3_frame, 'cabinet_files.key');
                return;
            } elseif (stripos($check_page_permalink, 'cabinets/cabinet_files/download/') !== false) {
                // キャビネット-ファイル
                $cabinet_files_query = Nc3CabinetFile::join('cabinets', 'cabinets.key', '=', 'cabinet_files.cabinet_key');
                $this->checkDeadLinkInsideNc3Plugin($check_page_permalink, 'cabinets/cabinet_files/download/', $cabinet_files_query, $url, $nc3_plugin_key, $nc3_frame, 'cabinet_files.key');
                return;
            } elseif (stripos($check_page_permalink, 'faqs/faq_questions/view/') !== false) {
                // FAQ
                $this->checkDeadLinkInsideNc3Plugin($check_page_permalink, 'faqs/faq_questions/view/', Nc3FaqQuestion::query(), $url, $nc3_plugin_key, $nc3_frame);
                return;
            } elseif (stripos($check_page_permalink, 'photo_albums/photo_album_photos/index/') !== false) {
                // フォトアルバム-アルバム表示
                $this->checkDeadLinkInsideNc3Plugin($check_page_permalink, 'photo_albums/photo_album_photos/index/', Nc3PhotoAlbum::query(), $url, $nc3_plugin_key, $nc3_frame);
                return;
            } elseif (stripos($check_page_permalink, 'reservations/reservation_plans/view/') !== false) {
                // 施設予約
                $this->checkDeadLinkInsideNc3PluginCal($check_page_permalink, 'reservations/reservation_plans/view/', Nc3ReservationEvent::query(), $url, $nc3_plugin_key, $nc3_frame);
                return;
            } elseif (stripos($check_page_permalink, 'calendars/calendar_plans/view/') !== false) {
                // カレンダー
                $this->checkDeadLinkInsideNc3PluginCal($check_page_permalink, 'calendars/calendar_plans/view/', Nc3CalendarEvent::query(), $url, $nc3_plugin_key, $nc3_frame);
                return;
            }
        }

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

    /**
     * 内部URL(nc3)のプラグイン個別のリンク切れチェック
     */
    private function checkDeadLinkInsideNc3Plugin(string $check_page_permalink, string $nc3_plugin_permalink, Builder $nc3_plugin_content_model_query, string $url, ?string $nc3_plugin_key, ?Nc3Frame $nc3_frame = null, ?string $key_colum = 'key'): bool
    {
        // pathのみに置換
        $path_tmp = parse_url($check_page_permalink, PHP_URL_PATH);
        // 不要文字を取り除き
        // $path_tmp = str_replace('blogs/blog_entries/view/', '', $path_tmp);
        $path_tmp = str_replace($nc3_plugin_permalink, '', $path_tmp);
        // /で分割
        $src_params = explode('/', $path_tmp);

        $block_id = $src_params[0];
        $content_key = $src_params[1];

        if ($block_id && $content_key) {
            // $check_nc3_content = Nc3BlogEntry::where('block_id', $block_id)->where('key', $content_key)->where('is_latest', 1)->first();
            $check_nc3_content = $nc3_plugin_content_model_query->where('block_id', $block_id)->where($key_colum, $content_key)->where('is_latest', 1)->first();
            if ($check_nc3_content) {
                // OK
                return true;
            } else {
                // NG
                $model = $nc3_plugin_content_model_query->make();
                $this->putLinkCheck(3, $nc3_plugin_key . "|内部リンク|{$model->getTable()}データなし", $url, $nc3_frame);
                return false;
            }
        } else {
            // NG
            $this->putLinkCheck(3, $nc3_plugin_key . "|内部リンク|{$nc3_plugin_permalink}でblock_id or content_keyなし", $url, $nc3_frame);
            return false;
        }
    }

    /**
     * 内部URL(nc3)のカレンダー系プラグイン個別のリンク切れチェック
     */
    private function checkDeadLinkInsideNc3PluginCal(string $check_page_permalink, string $nc3_plugin_permalink, Builder $nc3_plugin_content_model_query, string $url, ?string $nc3_plugin_key, ?Nc3Frame $nc3_frame = null, ?string $key_colum = 'key'): bool
    {
        // pathのみに置換
        $path_tmp = parse_url($check_page_permalink, PHP_URL_PATH);
        // 不要文字を取り除き
        $path_tmp = str_replace($nc3_plugin_permalink, '', $path_tmp);
        // /で分割
        $src_params = explode('/', $path_tmp);

        $content_key = $src_params[0];

        if ($content_key) {
            $check_nc3_content = $nc3_plugin_content_model_query->where($key_colum, $content_key)->where('is_latest', 1)->first();
            if ($check_nc3_content) {
                // OK
                return true;
            } else {
                // NG
                $model = $nc3_plugin_content_model_query->make();
                $this->putLinkCheck(3, $nc3_plugin_key . "|内部リンク|{$model->getTable()}データなし", $url, $nc3_frame);
                return false;
            }
        } else {
            // NG
            $this->putLinkCheck(3, $nc3_plugin_key . "|内部リンク|{$nc3_plugin_permalink}でcontent_keyなし", $url, $nc3_frame);
            return false;
        }
    }

    /**
     * nc3 uploads_path の取得
     */
    private function getExportUploadsPath(): string
    {
        // uploads_path の取得
        $uploads_path = config('migration.NC3_EXPORT_UPLOADS_PATH');

        // uploads_path の最後に / がなければ追加
        if (!empty($uploads_path) && mb_substr($uploads_path, -1) != '/') {
            $uploads_path = $uploads_path . '/';
        }
        return $uploads_path;
    }
}
