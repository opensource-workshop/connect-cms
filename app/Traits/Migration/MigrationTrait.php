<?php

namespace App\Traits\Migration;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use RRule\RRule;

use App\Models\Common\Buckets;
use App\Models\Common\BucketsMail;
use App\Models\Common\BucketsRoles;
use App\Models\Common\Categories;
use App\Models\Common\PluginCategory;
use App\Models\Common\Frame;
use App\Models\Common\Group;
use App\Models\Common\GroupUser;
use App\Models\Common\InputsRepeat;
use App\Models\Common\Like;
use App\Models\Common\LikeUser;
use App\Models\Common\Page;
use App\Models\Common\PageRole;
use App\Models\Common\Permalink;
use App\Models\Common\Uploads;
use App\Models\Core\Configs;
use App\Models\Core\FrameConfig;
use App\Models\Core\UsersColumns;
use App\Models\Core\UsersInputCols;
use App\Models\Core\UsersRoles;
use App\Models\User\Bbses\Bbs;
use App\Models\User\Bbses\BbsFrame;
use App\Models\User\Bbses\BbsPost;
use App\Models\User\Blogs\Blogs;
use App\Models\User\Blogs\BlogsPosts;
use App\Models\User\Cabinets\Cabinet;
use App\Models\User\Cabinets\CabinetContent;
use App\Models\User\Calendars\Calendar;
use App\Models\User\Calendars\CalendarFrame;
use App\Models\User\Calendars\CalendarPost;
use App\Models\User\Contents\Contents;
use App\Models\User\Counters\Counter;
use App\Models\User\Counters\CounterCount;
use App\Models\User\Counters\CounterFrame;
use App\Models\User\Databases\Databases;
use App\Models\User\Databases\DatabasesColumns;
use App\Models\User\Databases\DatabasesColumnsSelects;
use App\Models\User\Databases\DatabasesFrames;
use App\Models\User\Databases\DatabasesInputCols;
use App\Models\User\Databases\DatabasesInputs;
use App\Models\User\Faqs\Faqs;
use App\Models\User\Faqs\FaqsPosts;
use App\Models\User\Forms\Forms;
use App\Models\User\Forms\FormsColumns;
use App\Models\User\Forms\FormsColumnsSelects;
use App\Models\User\Forms\FormsInputCols;
use App\Models\User\Forms\FormsInputs;
use App\Models\User\Linklists\Linklist;
use App\Models\User\Linklists\LinklistFrame;
use App\Models\User\Linklists\LinklistPost;
use App\Models\User\Menus\Menu;
use App\Models\User\Reservations\Reservation;
use App\Models\User\Reservations\ReservationsCategory;
use App\Models\User\Reservations\ReservationsChoiceCategory;
use App\Models\User\Reservations\ReservationsColumn;
use App\Models\User\Reservations\ReservationsColumnsSelect;
use App\Models\User\Reservations\ReservationsColumnsSet;
use App\Models\User\Reservations\ReservationsFacility;
use App\Models\User\Reservations\ReservationsInput;
use App\Models\User\Reservations\ReservationsInputsColumn;
use App\Models\User\Whatsnews\Whatsnews;
use App\Models\User\Slideshows\Slideshows;
use App\Models\User\Slideshows\SlideshowsItems;

use App\User;

use App\Models\Migration\MigrationMapping;
use App\Models\Migration\Nc2\Nc2AbbreviateUrl;
use App\Models\Migration\Nc2\Nc2Announcement;
use App\Models\Migration\Nc2\Nc2Bbs;
use App\Models\Migration\Nc2\Nc2BbsBlock;
use App\Models\Migration\Nc2\Nc2BbsPost;
use App\Models\Migration\Nc2\Nc2BbsPostBody;
use App\Models\Migration\Nc2\Nc2Block;
use App\Models\Migration\Nc2\Nc2CabinetBlock;
use App\Models\Migration\Nc2\Nc2CabinetFile;
use App\Models\Migration\Nc2\Nc2CabinetManage;
use App\Models\Migration\Nc2\Nc2CalendarBlock;
use App\Models\Migration\Nc2\Nc2CalendarManage;
use App\Models\Migration\Nc2\Nc2CalendarPlan;
use App\Models\Migration\Nc2\Nc2CalendarPlanDetails;
use App\Models\Migration\Nc2\Nc2CalendarSelectRoom;
use App\Models\Migration\Nc2\Nc2Config;
use App\Models\Migration\Nc2\Nc2Counter;
use App\Models\Migration\Nc2\Nc2Faq;
use App\Models\Migration\Nc2\Nc2FaqBlock;
use App\Models\Migration\Nc2\Nc2FaqCategory;
use App\Models\Migration\Nc2\Nc2FaqQuestion;
use App\Models\Migration\Nc2\Nc2Item;
use App\Models\Migration\Nc2\Nc2Journal;
use App\Models\Migration\Nc2\Nc2JournalBlock;
use App\Models\Migration\Nc2\Nc2JournalCategory;
use App\Models\Migration\Nc2\Nc2JournalPost;
use App\Models\Migration\Nc2\Nc2Linklist;
use App\Models\Migration\Nc2\Nc2LinklistBlock;
use App\Models\Migration\Nc2\Nc2LinklistLink;
use App\Models\Migration\Nc2\Nc2LinklistCategory;
use App\Models\Migration\Nc2\Nc2MenuDetail;
use App\Models\Migration\Nc2\Nc2Modules;
use App\Models\Migration\Nc2\Nc2Multidatabase;
use App\Models\Migration\Nc2\Nc2MultidatabaseBlock;
use App\Models\Migration\Nc2\Nc2MultidatabaseMetadata;
use App\Models\Migration\Nc2\Nc2MultidatabaseMetadataContent;
use App\Models\Migration\Nc2\Nc2Page;
use App\Models\Migration\Nc2\Nc2PageUserLink;
use App\Models\Migration\Nc2\Nc2Registration;
use App\Models\Migration\Nc2\Nc2RegistrationBlock;
use App\Models\Migration\Nc2\Nc2RegistrationData;
use App\Models\Migration\Nc2\Nc2RegistrationItem;
use App\Models\Migration\Nc2\Nc2RegistrationItemData;
use App\Models\Migration\Nc2\Nc2ReservationBlock;
use App\Models\Migration\Nc2\Nc2ReservationCategory;
use App\Models\Migration\Nc2\Nc2ReservationLocation;
use App\Models\Migration\Nc2\Nc2ReservationLocationDetail;
use App\Models\Migration\Nc2\Nc2ReservationLocationRoom;
use App\Models\Migration\Nc2\Nc2ReservationReserve;
use App\Models\Migration\Nc2\Nc2ReservationReserveDetail;
use App\Models\Migration\Nc2\Nc2ReservationTimeframe;
use App\Models\Migration\Nc2\Nc2Upload;
use App\Models\Migration\Nc2\Nc2User;
use App\Models\Migration\Nc2\Nc2WhatsnewBlock;
use App\Models\Migration\Nc2\Nc2Slides;
use App\Models\Migration\Nc2\Nc2SlidesUrl;
use App\Models\Migration\Nc2\Nc2Simplemovie;

use App\Traits\ConnectCommonTrait;

use App\Enums\BlogFrameConfig;
use App\Enums\CounterDesignType;
use App\Enums\DayOfWeek;
use App\Enums\FacilityDisplayType;
use App\Enums\LinklistType;
use App\Enums\NoticeEmbeddedTag;
use App\Enums\NotShowType;
use App\Enums\PermissionType;
use App\Enums\Required;
use App\Enums\ReservationCalendarDisplayType;
use App\Enums\ReservationColumnType;
use App\Enums\ReservationFrameConfig;
use App\Enums\ReservationLimitedByRole;
use App\Enums\ReservationNoticeEmbeddedTag;
use App\Enums\ShowType;
use App\Enums\UserColumnType;

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
 * frames       : source_key にNC2 のblock_id、destination_key に新 frame_id
 * menus        : source_key にNC2 のblock_id or 追加キー、destination_key に新 menu_id
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
        'bbs'           => 'bbses',        // 掲示板
        'cabinet'       => 'cabinets',     // キャビネット
        'calendar'      => 'calendars',    // カレンダー
        'chat'          => 'Development',  // チャット
        'circular'      => 'Development',  // 回覧板
        'counter'       => 'counters',     // カウンター
        'faq'           => 'faqs',         // FAQ
        'iframe'        => 'Development',  // iFrame
        'imagine'       => 'Abolition',    // imagine
        'journal'       => 'blogs',        // ブログ
        'language'      => 'Development',  // 言語選択
        'linklist'      => 'linklists',    // リンクリスト
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
        'slides'        => 'slideshows',   // スライダー
        'simplemovie'   => 'contents',     // シンプル動画
    ];

    // delete: 全体カテゴリは作らない
    // /**
    //  * NC2 日誌のデフォルトカテゴリー
    //  */
    // protected $nc2_default_categories = [
    //     0   => '今日の出来事',
    //     1   => '連絡事項',
    //     2   => '報告事項',
    //     3   => 'ミーティング',
    //     4   => '本・雑誌',
    //     5   => 'ニュース',
    //     6   => '映画・テレビ',
    //     7   => '音楽',
    //     8   => 'スポーツ',
    //     9   => 'パソコン・インターネット',
    //     10  => 'ペット',
    //     11  => '総合学習',
    //     12  => 'アニメ・コミック',
    // ];

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
     * テストメソッド
     */
    private function getTestStr()
    {
        return "This is MigrationTrait test.";
    }

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
     * データのクリア
     */
    private function clearData($target, $initial = false)
    {
        if ($target == 'all' && $initial == true) {
            // 全クリア
            Buckets::truncate();
            BucketsRoles::truncate();
            Categories::truncate();
            PluginCategory::truncate();
            Like::truncate();
            LikeUser::truncate();
            Page::truncate();
            InputsRepeat::truncate();
        }

        if ($target == 'pages' || $target == 'all') {
            // トップページ以外の削除
            Page::where('permanent_link', '<>', '/')->delete();
            Frame::truncate();
            FrameConfig::truncate();
            BbsFrame::truncate();
            Menu::truncate();
            Contents::truncate();
            Buckets::where('plugin_name', 'contents')->delete();
            MigrationMapping::where('target_source_table', 'connect_page')->delete();
            MigrationMapping::where('target_source_table', 'frames')->delete();
        }

        if ($target == 'uploads' || $target == 'all') {
            // アップロードテーブルのtruncate とmigration_mappings のuploads の削除、アップロードファイルの削除
            Uploads::truncate();
            MigrationMapping::where('target_source_table', 'uploads')->delete();
            Storage::deleteDirectory(config('connect.directory_base'));
        }

        if ($target == 'categories' || $target == 'all') {
            // アップロードテーブルのtruncate とmigration_mappings のuploads の削除、アップロードファイルの削除
            Categories::truncate();
            PluginCategory::truncate();
            MigrationMapping::where('target_source_table', 'categories')->delete();
        }

        if ($target == 'users' || $target == 'all') {
            // 最初のユーザ以外の削除、migration_mappings のusers の削除
            $first_user = User::orderBy('id', 'asc')->first();
            UsersRoles::where('users_id', '<>', $first_user->id)->delete();
            User::where('id', '<>', $first_user->id)->delete();
            UsersColumns::truncate();
            UsersInputCols::truncate();
            MigrationMapping::where('target_source_table', 'users')->delete();
        }

        if ($target == 'groups' || $target == 'all') {
            PageRole::truncate();
            Group::truncate();
            GroupUser::truncate();
        }

        if ($target == 'permalinks' || $target == 'all') {
            Permalink::truncate();
        }

        if ($target == 'blogs' || $target == 'all') {
            // blogs、blogs_posts のtruncate
            Blogs::truncate();
            BlogsPosts::truncate();
            PluginCategory::where('target', 'blogs')->delete();
            Like::where('target', 'blogs')->delete();
            LikeUser::where('target', 'blogs')->delete();
            Buckets::where('plugin_name', 'blogs')->delete();
            MigrationMapping::where('target_source_table', 'blogs')->delete();
            MigrationMapping::where('target_source_table', 'blogs_post')->delete();
            MigrationMapping::where('target_source_table', 'categories_blogs')->delete();

            // bbs to blog 移行を指定されたら
            if ($this->plugin_name['bbs'] === 'blogs') {
                // bbs to blog の移行用
                MigrationMapping::where('target_source_table', 'bbses_post')->delete();
            }
        }

        if ($target == 'databases' || $target == 'all') {
            // databases、databases_columns、databases_columns_selects、databases_inputs、databases_input_cols、databases_frames のtruncate
            Databases::truncate();
            DatabasesColumns::truncate();
            DatabasesColumnsSelects::truncate();
            DatabasesInputs::truncate();
            DatabasesInputCols::truncate();
            DatabasesFrames::truncate();
            Buckets::where('plugin_name', 'databases')->delete();
            MigrationMapping::where('target_source_table', 'databases')->delete();
            MigrationMapping::where('target_source_table', 'databases_post')->delete();
        }

        if ($target == 'forms' || $target == 'all') {
            Forms::truncate();
            FormsColumns::truncate();
            FormsColumnsSelects::truncate();
            FormsInputCols::truncate();
            FormsInputs::truncate();
            Buckets::where('plugin_name', 'forms')->delete();
            MigrationMapping::where('target_source_table', 'forms')->delete();
        }

        if ($target == 'faqs' || $target == 'all') {
            Faqs::truncate();
            // FaqsCategories::truncate();
            PluginCategory::where('target', 'faqs')->delete();
            FaqsPosts::truncate();
            Buckets::where('plugin_name', 'faqs')->delete();
            MigrationMapping::where('target_source_table', 'faqs')->delete();
            MigrationMapping::where('target_source_table', 'categories_faqs')->delete();
        }

        if ($target == 'linklists' || $target == 'all') {
            Linklist::truncate();
            LinklistPost::truncate();
            LinklistFrame::truncate();
            PluginCategory::where('target', 'linklists')->delete();
            Buckets::where('plugin_name', 'linklists')->delete();
            MigrationMapping::where('target_source_table', 'linklists')->delete();
            MigrationMapping::where('target_source_table', 'categories_linklists')->delete();
        }

        if ($target == 'whatsnews' || $target == 'all') {
            Whatsnews::truncate();
            Buckets::where('plugin_name', 'whatsnews')->delete();
            MigrationMapping::where('target_source_table', 'whatsnews')->delete();
        }

        if ($target == 'cabinets' || $target == 'all') {
            Cabinet::truncate();
            CabinetContent::truncate();
            Buckets::where('plugin_name', 'cabinets')->delete();
            MigrationMapping::where('target_source_table', 'cabinets')->delete();
            MigrationMapping::where('target_source_table', 'cabinet_contents')->delete();
        }

        if ($target == 'bbses' || $target == 'all') {
            Bbs::truncate();
            BbsPost::truncate();
            Like::where('target', 'bbses')->delete();
            LikeUser::where('target', 'bbses')->delete();
            Buckets::where('plugin_name', 'bbses')->delete();
            BbsFrame::truncate();
            MigrationMapping::where('target_source_table', 'bbses')->delete();
            MigrationMapping::where('target_source_table', 'bbs_posts')->delete();
        }

        if ($target == 'counters' || $target == 'all') {
            Counter::truncate();
            CounterCount::truncate();
            CounterFrame::truncate();
            Buckets::where('plugin_name', 'counters')->delete();
            MigrationMapping::where('target_source_table', 'counters')->delete();
        }

        if ($target == 'calendars' || $target == 'all') {
            Calendar::truncate();
            CalendarFrame::truncate();
            CalendarPost::truncate();
            $buckets_ids = Buckets::where('plugin_name', 'calendars')->pluck('id');
            BucketsRoles::whereIn('buckets_id', $buckets_ids)->delete();
            Buckets::where('plugin_name', 'calendars')->delete();
            MigrationMapping::where('target_source_table', 'calendars')->delete();
        }

        if ($target == 'slideshows' || $target == 'all') {
            Slideshows::truncate();
            SlideshowsItems::truncate();
            Buckets::where('plugin_name', 'slideshows')->delete();
            MigrationMapping::where('target_source_table', 'slideshows')->delete();
        }

        if ($target == 'simplemovie' || $target == 'all') {
            /* シンプル動画はコンテンツに移行する */
            $simplimovieBuckets = Buckets::where('plugin_name', 'contents')->where('bucket_name', 'simplemovie')->get();
            foreach ($simplimovieBuckets as $simplimovieBucket) {
                Contents::where('bucket_id', $simplimovieBucket->id)->delete();
            }
            if ($simplimovieBuckets) {
                Buckets::where('plugin_name', 'contents')->where('bucket_name', 'simplemovie')->delete();
            }
            MigrationMapping::where('target_source_table', 'simplemovie')->delete();
        }

        if ($target == 'reservations' || $target == 'all') {
            Reservation::truncate();
            // change: auto incrementをクリアするため、truncateを採用
            // ReservationsCategory::where('id', '!=', 1)->forceDelete();
            ReservationsCategory::truncate();
            ReservationsChoiceCategory::truncate();
            ReservationsFacility::truncate();
            ReservationsInput::truncate();
            ReservationsInputsColumn::truncate();

            // $columns_set_basic = ReservationsColumnsSet::where('name', '基本')->first();
            // ReservationsColumnsSet::where('id', '!=', $columns_set_basic->id)->forceDelete();
            // ReservationsColumn::where('columns_set_id', '!=', $columns_set_basic->id)->forceDelete();
            // ReservationsColumnsSelect::where('columns_set_id', '!=', $columns_set_basic->id)->forceDelete();
            ReservationsColumnsSet::truncate();
            ReservationsColumn::truncate();
            ReservationsColumnsSelect::truncate();

            // 消してしまった初期登録のカテゴリなし、基本項目セットの再登録
            // php artisan db:seed --class=DefaultReservationsTableSeeder
            Artisan::call('db:seed --class=DefaultReservationsTableSeeder');

            $buckets_ids = Buckets::where('plugin_name', 'reservations')->pluck('id');
            BucketsRoles::whereIn('buckets_id', $buckets_ids)->delete();
            Buckets::where('plugin_name', 'reservations')->delete();
            InputsRepeat::where('target', 'reservations')->forceDelete();
            MigrationMapping::where('target_source_table', 'reservations_category')->delete();
            MigrationMapping::where('target_source_table', 'reservations_post')->delete();
            MigrationMapping::where('target_source_table', 'reservations_location')->delete();
            MigrationMapping::where('target_source_table', 'reservations_block')->delete();
        }
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
            $this->log_path[$filename] = "migration/logs/" . $filename . "_" . date('His') . '.log';

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
     * 配列の値の取得
     */
    private function getArrayValue($array, $key1, $key2 = null, $default = "")
    {
        if (empty($array)) {
            return $default;
        }

        $value1 = $default;
        if (array_key_exists($key1, $array)) {
            $value1 = $array[$key1];
        } else {
            return $default;
        }
        if (empty($key2)) {
            return $value1;
        }
        $value2 = $default;
        if (array_key_exists($key2, $value1)) {
            $value2 = $value1[$key2];
        }
        return $value2;
    }

    /**
     * インポートの初期処理
     */
    private function migrationInit()
    {
        // 環境ごとの移行設定の読み込み
        //if (Storage::exists('migration_config/migration_config.ini')) {
        //    $this->migration_config = parse_ini_file(storage_path() . '/app/migration_config/migration_config.ini', true);
        //}
        if (File::exists(config('migration.MIGRATION_CONFIG_PATH'))) {
            // 手動で設置のmigration config がある場合
            $this->migration_config = parse_ini_file(config('migration.MIGRATION_CONFIG_PATH'), true);
        } elseif (File::exists(storage_path('app/migration/oneclick/migration_config.oneclick.ini'))) {
            // NetCommons2 からのワンクリック移行用の migration config がある場合
            $this->migration_config = parse_ini_file(storage_path('app/migration/oneclick/migration_config.oneclick.ini'), true);
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
     * 記事内に p タグがなければ、p タグで囲む
     */
    private function addParagraph($target, $value)
    {
        $return_value = $value;
        if ($this->hasMigrationConfig($target, 'cc_import_add_if_not_p', true)) {
            $pattern = '/<p.*?>/i';
            if (!empty(trim($return_value)) && !preg_match($pattern, $value, $matches)) {
                $return_value = '<p>' . $return_value . '</p>';
            }
        }
        return $return_value;
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
     * インポート時TSVから日時取得 ＆ 日時フォーマットチェック
     */
    private function getDatetimeFromTsvAndCheckFormat($idx, $tsv_cols, $column_name, $default = null)
    {
        if (is_null($default)) {
            $default = date('Y-m-d H:i:s');
        }

        // カラムがない or データが空の場合は、処理時間を入れる。
        if (array_key_exists($idx, $tsv_cols) && !empty($tsv_cols[$idx])) {
            $date = $tsv_cols[$idx];
            if (!\DateTime::createFromFormat('Y-m-d H:i:s', $date)) {
                $this->putError(3, '日付エラー', "{$column_name} = {$date}");
                // $date = date('Y-m-d H:i:s');
                $date = $default;
            }
        } else {
            // $date = date('Y-m-d H:i:s');
            $date = $default;
        }

        return $date;
    }

    /**
     * インポート時INIから日時取得 ＆ 日時フォーマットチェック
     */
    private function getDatetimeFromIniAndCheckFormat($ini, $key1, $key2, $default = null)
    {
        if (is_null($default)) {
            $default = date('Y-m-d H:i:s');
        }

        $date = $this->getArrayValue($ini, $key1, $key2, null);

        // データが空の場合は、処理時間を入れる。
        if (empty($date)) {
            return $default;
        }

        if (!\DateTime::createFromFormat('Y-m-d H:i:s', $date)) {
            $this->putError(3, '日付エラー', "[{$key1}] $key2 = {$date}");
            return $default;
        }

        return $date;
    }

    /**
     * ログインIDからユーザID取得
     */
    private function getUserIdFromLoginId($users, $login_id)
    {
        $user = $users->firstWhere('userid', $login_id);
        $user = $user ?? new User();
        return $user->id;
    }

    /**
     * NC2ユーザIDからNC2ログインID取得
     */
    private function getNc2LoginIdFromNc2UserId($nc2_users, $nc2_user_id)
    {
        $nc2_user = $nc2_users->firstWhere('user_id', $nc2_user_id);
        $nc2_user = $nc2_user ?? new Nc2User();
        return $nc2_user->login_id;
    }

    /**
     * Connect-CMS 移行形式のHTML をインポート
     */
    private function importSite($target, $target_plugin, $redo = null)
    {
        $this->importSiteImpl($target, $target_plugin, false, $redo);
        $this->import_base = '@insert/';
        $this->importSiteImpl($target, $target_plugin, true);
    }

    /**
     * Connect-CMS 移行形式のHTML をインポート
     */
    private function importSiteImpl($target, $target_plugin, $added, $redo = null)
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
        $this->redo          = $redo;
        $this->added         = $added;

        $this->putMonitor(3, "importSite() Start.");

        // 移行の初期処理
        if ($added == false) {
            $this->clearData($target, true);
        }

        // 移行の初期処理
        $this->migrationInit();

        // サイト基本設定ファイルの取り込み
        if ($this->isTarget('cc_import', 'basic')) {
            $this->importBasic($redo);
        }

        // アップロード・ファイルの取り込み
        if ($this->isTarget('cc_import', 'uploads')) {
            $this->importUploads($redo);
        }

        // 共通カテゴリの取り込み
        // if ($this->isTarget('cc_import', 'categories')) {
        //     $this->importCommonCategories($redo);
        // }

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

        // フォームの取り込み
        if ($this->isTarget('cc_import', 'plugins', 'forms')) {
            $this->importForms($redo);
        }

        // FAQの取り込み
        if ($this->isTarget('cc_import', 'plugins', 'faqs')) {
            $this->importFaqs($redo);
        }

        // リンクリストの取り込み
        if ($this->isTarget('cc_import', 'plugins', 'linklists')) {
            $this->importLinklists($redo);
        }

        // 新着情報の取り込み
        if ($this->isTarget('cc_import', 'plugins', 'whatsnews')) {
            $this->importWhatsnews($redo);
        }

        // キャビネットの取り込み
        if ($this->isTarget('cc_import', 'plugins', 'cabinets')) {
            $this->importCabinets($redo);
        }

        // BBSの取り込み
        if ($this->isTarget('cc_import', 'plugins', 'bbses')) {
            $this->importBbses($redo);
        }

        // カウンターの取り込み
        if ($this->isTarget('cc_import', 'plugins', 'counters')) {
            $this->importCounters($redo);
        }

        // カレンダーの取り込み
        if ($this->isTarget('cc_import', 'plugins', 'calendars')) {
            $this->importCalendars($redo);
        }

        // スライダーの取り込み
        if ($this->isTarget('cc_import', 'plugins', 'slideshows')) {
            $this->importSlideshows($redo);
        }

        // Contentsに入れるためclearData('pages')処理後に実行
        // シンプル動画の取り込み
        $importSimplemovieFlg = false;
        if ($this->isTarget('cc_import', 'plugins', 'simplemovie')) {
            $importSimplemovieFlg = true;
        }

        // 施設予約の取り込み
        if ($this->isTarget('cc_import', 'plugins', 'reservations')) {
            $this->importReservations($redo);
        }


        // 固定URLの取り込み
        $this->importPermalinks($redo);

        // 新ページの取り込み
        if ($this->isTarget('cc_import', 'pages')) {
            // データクリア
            if ($redo === true) {
                // トップページ以外の削除
                $this->clearData('pages');
            }

            // Contentsに入れるためclearData('pages')処理後に実行
            // シンプル動画の取り込み
            if ($importSimplemovieFlg) {
                $this->importSimplemovie($redo);
                $importSimplemovieFlg = false;
            }

            $paths = File::glob(storage_path() . '/app/' . $this->getImportPath('pages/*'));

            // ルームの指定（あれば後で使う）
            //$cc_import_page_room_ids = $this->getMigrationConfig('pages', 'cc_import_page_room_ids');

            // 新ページのループ
            foreach ($paths as $path) {
                // ページ指定の有無
                $cc_import_where_page_dirs = $this->getMigrationConfig('pages', 'cc_import_where_page_dirs');
                if (!empty($cc_import_where_page_dirs)) {
                    if (!in_array(basename($path), $cc_import_where_page_dirs)) {
                        continue;
                    }
                }

                $this->putMonitor(1, "Page data loop.", "dir = " . basename($path));

                // ページの設定取得
                $page_ini = @parse_ini_file($path . '/page.ini', true);
                //print_r($page_ini);

                // @insert で page.ini がない場合は、import から参照する。
                if ($this->import_base == '@insert/') {
                    if (!File::exists($path . '/page.ini')) {
                        $page_ini = @parse_ini_file(str_replace('@insert', 'import', $path . '/page.ini'), true);
                    }
                    if (!$page_ini) {
                        continue;
                    }
                }

                // ルーム指定を探しておく。
                $room_id = null;
                if (array_key_exists('page_base', $page_ini) && array_key_exists('nc2_room_id', $page_ini['page_base'])) {
                    $room_id = $page_ini['page_base']['nc2_room_id'];
                }

                // ルーム指定があれば、指定されたルームのみ処理する。
                //if (empty($cc_import_page_room_ids)) {
                //    // ルーム指定なし。全データの移行
                //} elseif (!empty($room_id) && !empty($cc_import_page_room_ids) && in_array($room_id, $cc_import_page_room_ids)) {
                //    // ルーム指定あり。指定ルームに合致する。
                //} else {
                //    // ルーム指定あり。条件に合致せず。移行しない。
                //    continue;
                //}

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
                    $this->putMonitor(1, "Page create.");

                    // ページの作成
                    $page = Page::create(['page_name'         => $page_ini['page_base']['page_name'],
                                          'permanent_link'    => $page_ini['page_base']['permanent_link'],
                                          'layout'            => array_key_exists('layout', $page_ini['page_base']) ? $page_ini['page_base']['layout'] : null,
                                          'base_display_flag' => $page_ini['page_base']['base_display_flag'],
                                          'membership_flag'   => empty($page_ini['page_base']['membership_flag']) ? 0 : $page_ini['page_base']['membership_flag'],
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
                } else {
                    // 対象のURL があった場合はページの更新
                    $page->page_name         = $page_ini['page_base']['page_name'];
                    $page->layout            = array_key_exists('layout', $page_ini['page_base']) ? $page_ini['page_base']['layout'] : null;
                    $page->base_display_flag = $page_ini['page_base']['base_display_flag'];
                    $page->membership_flag   = empty($page_ini['page_base']['membership_flag']) ? 0 : $page_ini['page_base']['membership_flag'];
                    $page->save();

                    $this->putMonitor(3, "Page found. Use existing page. url=" . $page_ini['page_base']['permanent_link']);
                }

                // マッピングテーブルの追加
                $mapping = MigrationMapping::updateOrCreate(
                    ['target_source_table' => 'connect_page',
                    'source_key' => ltrim(basename($path), '_')],
                    ['target_source_table'  => 'connect_page',
                    'source_key'           => ltrim(basename($path), '_'),
                    'destination_key'      => $page->id]
                );

                // ページの中身の作成
                $this->importHtmlImpl($page, $path);
            }
        }

        // シンプル動画単独実行用
        if ($importSimplemovieFlg) {
            $this->importSimplemovie($redo);
        }

        // グループデータの取り込み
        if ($this->isTarget('cc_import', 'groups')) {
            $this->importGroups($redo);
        }

        // ページ内リンクの編集
        if ($added == false) {
            $this->changePageInLink();
        }

        // シーダーの呼び出し
        //if ($this->isTarget('cc_import', 'addition')) {
        //    $this->importSeeder($redo);
        //}
    }

    /**
     * ページ内リンクの編集
     */
    private function changePageInLink()
    {
        // 固定記事
        $contents = Contents::where('content_text', 'like', '%#_%')->get();
        foreach ($contents as $content) {
            // a タグの href 抜き出し
            $hrefs = $this->getContentAnchor($content->content_text);
            if ($hrefs === false) {
                continue;
            }
            foreach ($hrefs as $href) {
                // horiguchi 修正
                //// 対象判断（自URLで始まっている(フルパスのページ内リンク) or #_(NC2のページ内リンク)で始まっている）
                //if (mb_stripos($href, config('app.url')) === 0 || mb_stripos($href, '#_') === 0) {
                //    // NC2 ブロックID取得
                //    $nc2_block_id = mb_substr($href, mb_strripos($href, '#_') + 2);
                // #_が含まれているhrefを取得に変更 href="/hoge/fuga/#_123" に対応できないため
                if (preg_match('/#_(.*?)$/', $href, $m)) {
                    $nc2_block_id = $m[1];
                    // Connect-CMS フレームID
                    $map_frame = MigrationMapping::where('target_source_table', 'frames')->where('source_key', $nc2_block_id)->first();
                    if (!empty($map_frame)) {
                        $content->content_text = str_replace('#_' . $nc2_block_id, '#frame-' . $map_frame->destination_key, $content->content_text);
                        $content->save();
                    }
                }
            }
        }
    }

    /**
     * ページ内リンクの編集
     */
    private function changePageInLinkImpl($text)
    {
        $hrefs = $this->getContentAnchor($text);
        foreach ($hrefs as $href) {
            // 対象判断（自URLで始まっている(フルパスのページ内リンク) or #_(NC2のページ内リンク)で始まっている）
            if (mb_stripos($href, config('app.url')) === 0 || mb_stripos($href, '#_') === 0) {
                // NC2 ブロックID取得
                $nc2_block_id = mb_substr($href, mb_strripos($href, '#_') + 2);
                // Connect-CMS フレームID
                $map_frame = MigrationMapping::where('target_source_table', 'frames')->where('source_key', $nc2_block_id)->first();
                if (!empty($map_frame)) {
                    $text = str_replace('#_' . $nc2_block_id, '#frame-' . $map_frame->destination_key, $text);
                }
            }
        }
        return $text;
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
     * サイト基本設定をインポート
     */
    private function updateConfig($name, $ini, $category = 'general')
    {
        if (!array_key_exists('basic', $ini)) {
            return;
        }

        if (array_key_exists($name, $ini['basic'])) {
            $config = Configs::updateOrCreate(
                ['name'     => $name],
                ['name'     => $name,
                 'value'    => $ini['basic'][$name],
                 'category' => $category]
            );
        }
    }

    /**
     * サイト基本設定をインポート
     */
    private function importBasic($redo)
    {
        $this->putMonitor(3, "Basic import Start.");

        // サイト基本設定ファイル読み込み
        $basic_file_path = $this->getImportPath('basic/basic.ini');
        if (Storage::exists($basic_file_path)) {
            $basic_ini = parse_ini_file(storage_path() . '/app/' . $basic_file_path, true);

            // サイト名
            $this->updateConfig('base_site_name', $basic_ini);

            // フッター幅
            $this->updateConfig('browser_width_footer', $basic_ini);
        }
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
            $this->clearData('uploads');
        }

        // アップロード・ファイル定義の取り込み
        if (!Storage::exists($this->getImportPath('uploads/uploads.ini'))) {
            return;
        }

        $uploads_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('uploads/uploads.ini'), true);

        // ルームの指定（あれば後で使う）
        // $cc_import_uploads_room_ids = $this->getMigrationConfig('uploads', 'cc_import_uploads_room_ids');

        // アップロード・ファイルのループ
        if (array_key_exists('uploads', $uploads_ini) && array_key_exists('upload', $uploads_ini['uploads'])) {
            foreach ($uploads_ini['uploads']['upload'] as $upload_key => $upload_item) {
                // ルーム指定を探しておく。
                $room_id = null;
                if (array_key_exists('nc2_room_id', $uploads_ini[$upload_key])) {
                    $room_id = $uploads_ini[$upload_key]['nc2_room_id'];
                }

                // ルーム指定があれば、指定されたルームのみ処理する。
                //if (empty($cc_import_uploads_room_ids)) {
                //    // ルーム指定なし。全データの移行
                //} elseif (!empty($room_id) && !empty($cc_import_uploads_room_ids) && in_array($room_id, $cc_import_uploads_room_ids)) {
                //    // ルーム指定あり。指定ルームに合致する。
                //} else {
                //    // ルーム指定あり。条件に合致せず。移行しない。
                //    continue;
                //}

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
                $source_file_path = $this->getImportPath('uploads/') . $upload_item;
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

    // delete: 全体カテゴリは作らない
    // /**
    //  * Connect-CMS 移行形式のカテゴリをインポート
    //  */
    // private function importCommonCategories($redo)
    // {
    //     $this->putMonitor(3, "Categories import Start.");

    //     // データクリア
    //     if ($redo === true) {
    //         // カテゴリテーブルのtruncate とmigration_mappings のcategories の削除
    //         $this->clearData('categories');
    //     }

    //     // 共通カテゴリのファイル読み込み
    //     $source_file_path = $this->getImportPath('categories/categories.ini');
    //     if (Storage::exists($source_file_path)) {
    //         $categories_ini = parse_ini_file(storage_path() . '/app/' . $source_file_path, true);
    //         if (array_key_exists('categories', $categories_ini) && array_key_exists('categories', $categories_ini['categories'])) {
    //             $this->importCategories($categories_ini['categories']['categories']);
    //         }
    //     }
    // }

    /**
     * Connect-CMS 移行形式のカテゴリをインポート
     */
    private function importCategories($categories, $target = null, $plugin_id = null)
    {
        $target_source_table = "categories";
        if (!empty($target)) {
            $target_source_table = $target_source_table . "_" . $target;
        }

        $display_sequence = 0;
        foreach ($categories as $category_id => $category_name) {
            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', $target_source_table)->where('source_key', $category_id)->first();

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
                    'target_source_table'  => $target_source_table,
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
            $this->clearData('users');
        }

        // ユーザ任意項目の取り込み
        // ------------------------------------------
        // UsersColumns のコレクションを保持。後で入力データを移行する際に nc2_item_id でひっぱるため。
        $create_users_columns = collect();

        $ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('users/users_columns_*.ini'));
        foreach ($ini_paths as $ini_path) {
            // ini_file の解析
            $ini = parse_ini_file($ini_path, true);

            // nc2 の item_id
            $nc2_item_id = $this->getArrayValue($ini, 'source_info', 'item_id', 0);

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'users_columns')->where('source_key', $nc2_item_id)->first();

            // マッピングテーブルを確認して、あれば削除
            if (!empty($mapping)) {
                // ユーザカラム削除
                UsersColumns::where('id', $mapping->destination_key)->delete();

                // マッピングテーブル削除
                $mapping->delete();
            }

            $users_column = UsersColumns::create([
                'column_type'      => $this->getArrayValue($ini, 'users_columns_base', 'column_type'),
                'column_name'      => $this->getArrayValue($ini, 'users_columns_base', 'column_name'),
                'required'         => intval($this->getArrayValue($ini, 'users_columns_base', 'required', 0)),
                'caption'          => $this->getArrayValue($ini, 'users_columns_base', 'caption'),
                'display_sequence' => intval($this->getArrayValue($ini, 'users_columns_base', 'display_sequence')),
            ]);

            $users_column->nc2_item_id = $nc2_item_id;
            // コレクションに要素追加
            $create_users_columns = $create_users_columns->concat([$users_column]);

            // マッピングテーブルの追加
            $mapping = MigrationMapping::create([
                'target_source_table'  => 'users_columns',
                'source_key'           => $nc2_item_id,
                'destination_key'      => $users_column->id,
            ]);
        }

        // ユーザ定義・ファイルの存在確認
        if (!Storage::exists($this->getImportPath('users/users.ini'))) {
            return;
        }

        // ユーザ定義・ファイルの取り込み
        $users_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('users/users.ini'), true);

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

                // パスワード変更
                $nc2_override_pass = $this->getMigrationConfig('users', 'nc2_export_login_users_overridepass');
                if (!empty($nc2_override_pass) && isset($nc2_override_pass[$user_item['userid']])) {
                    $user_item['password'] = $nc2_override_pass[$user_item['userid']];
                    $this->putError(3, 'パスワードを変更しました', "userid = " . $user_item['userid']);
                }

                // ユーザがあるかの確認
                if (empty($user)) {
                    // ユーザテーブルがなければ、追加
                    $user = User::create([
                        'name'     => $user_item['name'],
                        'email'    => $email,
                        'userid'   => $user_item['userid'],
                        'password' => Hash::make($user_item['password']),
                        'status'   => $user_item['status'],
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
                    $user->status    = $user_item['status'];
                    $user->save();
                }

                // 任意項目インポート
                foreach ($create_users_columns as $create_users_column) {
                    $col = UsersInputCols::updateOrCreate([
                        'users_id' => $user->id,
                        'users_columns_id' => $create_users_column->id,
                    ], [
                        'value' => $user_item["item_{$create_users_column->nc2_item_id}"]
                    ]);
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
     * Connect-CMS 移行形式のグループをインポート
     */
    private function importGroups($redo)
    {
        $this->putMonitor(3, "Groups import Start.");

        // データクリア
        if ($redo === true) {
            // 最初のユーザ以外の削除、migration_mappings のusers の削除
            $this->clearData('groups');
        }

        // グループ定義の取り込み
        $group_ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('groups/group_*.ini'));

        // グループ定義のループ
        foreach ($group_ini_paths as $i => $group_ini_path) {
            // ini_file の解析
            $group_ini = parse_ini_file($group_ini_path, true);

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'groups')->where('source_key', $group_ini['source_info']['room_id'])->first();

            // マッピングテーブルを確認して、追加か更新の処理を分岐
            if (empty($mapping)) {
                // 追加
                $group = Group::create([
                    'name' => $group_ini['group_base']['name'],
                    'display_sequence' => $i + 1,
                ]);

                // マッピングテーブルの追加
                $mapping = MigrationMapping::create([
                    'target_source_table'  => 'groups',
                    'source_key'           => $group_ini['source_info']['room_id'],
                    'destination_key'      => $group->id,
                ]);
            } else {
                // 更新
                $group = Group::updateOrCreate([
                    'id' => $mapping->destination_key
                ], [
                    'name' => $group_ini['group_base']['name'],
                    'display_sequence' => $i + 1,
                ]);
            }

            // group_users 作成
            foreach ($group_ini['users']['user'] as $login_id => $role_authority_id) {
                $user = User::where('userid', $login_id)->first();
                if (empty($user)) {
                    continue;
                }
                $group_user = GroupUser::updateOrCreate(
                    ['group_id' => $group->id, 'user_id' => $user->id],
                    ['group_id' => $group->id, 'user_id' => $user->id, 'group_role' => 'general']
                );
            }

            // page_roles 作成（元 page_id -> マッピング -> 新フォルダ -> マッピング -> 新 page_id）
            $source_page = MigrationMapping::where('target_source_table', 'nc2_pages')->where('source_key', $group_ini['source_info']['room_id'])->first();
            if (empty($source_page)) {
                continue;
            }
            $destination_page = MigrationMapping::where('target_source_table', 'connect_page')->where('source_key', $source_page->destination_key)->first();
            if (empty($destination_page)) {
                continue;
            }
            $page_role = PageRole::updateOrCreate(
                ['page_id' => $destination_page->destination_key, 'group_id' => $group->id],
                ['page_id' => $destination_page->destination_key, 'group_id' => $group->id, 'target' => 'base', 'role_name' => 'role_reporter', 'role_value' => 1]
            );
        }

        // ※ 上ループで管理者グループを登録しようと組んだが、なぜかgroup->idがズレるため、上ループ後に管理グループ追加
        // 管理者グループ追加
        $display_sequence = Group::where('name', '<>', '管理者グループ')->max('display_sequence') + 1;
        $admin_group = Group::updateOrCreate([
            'name' => '管理者グループ'
        ], [
            'name' => '管理者グループ',
            'display_sequence' => $display_sequence,
        ]);

        // 管理者 group_users 作成
        $admin_users_roles = UsersRoles::where('target', 'base')->where('role_name', 'role_article_admin')->get();
        foreach ($admin_users_roles as $users_roles) {
            $group_user = GroupUser::updateOrCreate(
                ['group_id' => $admin_group->id, 'user_id' => $users_roles->users_id],
                ['group_id' => $admin_group->id, 'user_id' => $users_roles->users_id, 'group_role' => 'general']
            );
        }

        $groups_mappings = MigrationMapping::where('target_source_table', 'groups')->get();

        // BucketsMailの管理者グループ仮コード, 仮nc2ルームID置換
        foreach (BucketsMail::get() as $bucket_mail) {
            $notice_groups = explode('|', $bucket_mail->notice_groups);
            foreach ($notice_groups as &$notice_group) {
                // 先頭X-ありは置換
                if (strpos($notice_group, 'X-') === 0) {
                    if ($notice_group == 'X-管理者グループ') {
                        // 管理者グループ仮コード置換
                        $notice_group = str_ireplace('X-管理者グループ', $admin_group->id, $notice_group);
                    } else {
                        // 仮nc2ルームID -> nc2ルームID
                        $nc2_room_id = str_ireplace('X-', '', $notice_group);
                        // nc2ルームID -> グループID置換
                        $mapping = $groups_mappings->where('source_key', $nc2_room_id)->first();
                        $notice_group = $mapping ? $mapping->destination_key : null;
                    }

                }
            }
            // array_filter()でarrayの空要素削除
            $notice_groups = array_filter($notice_groups);

            $bucket_mail->notice_groups = implode('|', $notice_groups);
            $bucket_mail->save();
        }

        // グループ定義のループ
        foreach ($group_ini_paths as $group_ini_path) {
            // ini_file の解析
            $group_ini = parse_ini_file($group_ini_path, true);

            // page_roles 作成（元 page_id -> マッピング -> 新フォルダ -> マッピング -> 新 page_id）
            $source_page = MigrationMapping::where('target_source_table', 'nc2_pages')->where('source_key', $group_ini['source_info']['room_id'])->first();
            if (empty($source_page)) {
                continue;
            }
            $destination_page = MigrationMapping::where('target_source_table', 'connect_page')->where('source_key', $source_page->destination_key)->first();
            if (empty($destination_page)) {
                continue;
            }
            // 管理者グループに権限付与
            $page_role = PageRole::updateOrCreate(
                ['page_id' => $destination_page->destination_key, 'group_id' => $admin_group->id],
                ['page_id' => $destination_page->destination_key, 'group_id' => $admin_group->id, 'target' => 'base', 'role_name' => 'role_article_admin', 'role_value' => 1]
            );
        }

        // アップロード・ファイルのページIDを書き換えるために、アップロード・ファイル定義の読み込み
        if (!Storage::exists($this->getImportPath('uploads/uploads.ini'))) {
            return;
        }

        $uploads_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('uploads/uploads.ini'), true);

        // アップロード・ファイルのループ
        if (array_key_exists('uploads', $uploads_ini) && array_key_exists('upload', $uploads_ini['uploads'])) {
            foreach ($uploads_ini['uploads']['upload'] as $upload_key => $upload_item) {
                // ルーム指定を探しておく。
                $room_id = null;
                if (array_key_exists('nc2_room_id', $uploads_ini[$upload_key])) {
                    $room_id = $uploads_ini[$upload_key]['nc2_room_id'];
                }
                if (empty($room_id)) {
                    continue;
                }
                // アップロードファイルに対応するConnect-CMS のページを探す
                $nc2_page = MigrationMapping::where('target_source_table', 'nc2_pages')->where('source_key', $room_id)->first();
                if (empty($nc2_page)) {
                    continue;
                }
                $connect_page = MigrationMapping::where('target_source_table', 'connect_page')->where('source_key', $nc2_page->destination_key)->first();
                if (empty($connect_page)) {
                    continue;
                }
                // アップロードファイルを探す
                $upload_map = MigrationMapping::where('target_source_table', 'uploads')->where('source_key', $upload_key)->first();
                if (empty($upload_map)) {
                    continue;
                }
                // アップロードファイルのページid を更新
                $upload = Uploads::find($upload_map->destination_key);
                if (empty($upload)) {
                    continue;
                }
                $upload->page_id = $connect_page->destination_key;
                $upload->save();
            }
        }
    }

    /**
     * Connect-CMS 移行形式の固定URLをインポート
     */
    private function importPermalinks($redo)
    {
        $this->putMonitor(3, "Permalinks import Start.");

        // データクリア
        if ($redo === true) {
            $this->clearData('permalinks');
        }

        // 定義ファイルの存在確認
        if (!Storage::exists($this->getImportPath('permalinks/permalinks.ini'))) {
            return;
        }

        // 定義ファイルの取り込み
        $permalinks_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('permalinks/permalinks.ini'), true);

        // 定義のループ
        if (array_key_exists('permalinks', $permalinks_ini) && array_key_exists('permalink', $permalinks_ini['permalinks'])) {
            // バルクINSERT対応
            $bulks = array();

            foreach ($permalinks_ini['permalinks']['permalink'] as $permalink_index => $short_url) {
                // 固定URL情報
                $permalink_item = null;
                if (array_key_exists($short_url, $permalinks_ini)) {
                    $permalink_item = $permalinks_ini[$short_url];
                } else {
                    $this->putError(3, '固定URLの詳細なし', "short_url = " . $short_url);
                    continue;
                }

                // Permalinks 登録 or 更新
                $bulks[] = ['short_url' => $short_url,
                    'plugin_name'    => $this->getArrayValue($permalinks_ini, $short_url, 'plugin_name'),
                    'action'         => $this->getArrayValue($permalinks_ini, $short_url, 'action'),
                    'unique_id'      => $this->getArrayValue($permalinks_ini, $short_url, 'unique_id'),
                    'migrate_source' => $this->getArrayValue($permalinks_ini, $short_url, 'migrate_source')];
                /*
                $permalink = Permalink::updateOrCreate(
                    ['short_url'     => $short_url],
                    ['short_url'     => $short_url,
                    'plugin_name'    => $this->getArrayValue($permalinks_ini, $short_url, 'plugin_name'),
                    'action'         => $this->getArrayValue($permalinks_ini, $short_url, 'action'),
                    'unique_id'      => $this->getArrayValue($permalinks_ini, $short_url, 'unique_id'),
                    'migrate_source' => $this->getArrayValue($permalinks_ini, $short_url, 'migrate_source')]
                );
                */

                // マップ 登録 or 更新
                /*
                $mapping = MigrationMapping::updateOrCreate(
                    ['target_source_table' => 'menus', 'source_key' => $source_key],
                    ['target_source_table' => 'menus',
                     'source_key'          => $source_key,
                     'destination_key'     => $menus->id]
                );
                */
            }
            // バルクINSERT
            $size = 1000; //Prepared statement contains too many placeholders 対策
            $chunk_bulks = array_chunk($bulks, $size);
            foreach ($chunk_bulks as $bulk) {
                DB::table('permalinks')->insert($bulk);
            }
        }
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
            $this->clearData('blogs');
        }

        // 共通カテゴリの取得
        // $common_categories = Categories::whereNull('target')->whereNull('plugin_id')->orderBy('id', 'asc')->get();

        // ブログ定義の取り込み
        $blogs_ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('blogs/blog_*.ini'));

        // ルームの指定（あれば後で使う）
        //$cc_import_blogs_room_ids = $this->getMigrationConfig('blogs', 'cc_import_blogs_room_ids');

        // ブログ定義のループ
        foreach ($blogs_ini_paths as $blogs_ini_path) {
            // ini_file の解析
            $blog_ini = parse_ini_file($blogs_ini_path, true);

            // ルーム指定を探しておく。
            $room_id = null;
            if (array_key_exists('source_info', $blog_ini) && array_key_exists('room_id', $blog_ini['source_info'])) {
                $room_id = $blog_ini['source_info']['room_id'];
            }

            // ルーム指定があれば、指定されたルームのみ処理する。
            //if (empty($cc_import_blogs_room_ids)) {
            //    // ルーム指定なし。全データの移行
            //} elseif (!empty($room_id) && !empty($cc_import_blogs_room_ids) && in_array($room_id, $cc_import_blogs_room_ids)) {
            //    // ルーム指定あり。指定ルームに合致する。
            //} else {
            //    // ルーム指定あり。条件に合致せず。移行しない。
            //    continue;
            //}

            // nc2 の journal_id
            $nc2_journal_id = 0;
            if (array_key_exists('source_info', $blog_ini) && array_key_exists('journal_id', $blog_ini['source_info'])) {
                $nc2_journal_id = $blog_ini['source_info']['journal_id'];
            }

            // ブログの統合
            $cc_import_marges = $this->getMigrationConfig('blogs', 'cc_import_marges');
            if (is_array($cc_import_marges) && array_key_exists($nc2_journal_id, $cc_import_marges)) {
                $nc2_journal_id = $cc_import_marges[$nc2_journal_id];
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

                $use_like = 0;
                if (array_key_exists('blog_base', $blog_ini) && array_key_exists('use_like', $blog_ini['blog_base'])) {
                    $use_like = $blog_ini['blog_base']['use_like'];
                }
                $blog = Blogs::create(['bucket_id' => $bucket->id, 'blog_name' => $blog_name, 'use_like' => $use_like]);

                // マッピングテーブルの追加
                $mapping = MigrationMapping::create([
                    'target_source_table'  => 'blogs',
                    'source_key'           => $nc2_journal_id,
                    'destination_key'      => $blog->id,
                ]);
            }

            // これ以降は追加も更新も同じロジック

            // ブログ固有カテゴリ追加
            $blog_categories = collect();
            if (array_key_exists('categories', $blog_ini) && array_key_exists('original_categories', $blog_ini['categories'])) {
                $blog_categories = $this->importCategories($blog_ini['categories']['original_categories'], 'blogs', $blog->id);
            }

            // ブログのカテゴリーテーブル作成（カテゴリーの使用on設定）
            $index = 1;
            foreach ($blog_categories as $blog_category) {
                PluginCategory::create(['target' => 'blogs', 'target_id' => $blog_category->plugin_id, 'categories_id' => $blog_category->id, 'view_flag' => 1, 'display_sequence' => $index]);
                $index++;
            }

            // 記事のマッピングテーブル作成用に記事一覧（post_title）を使用する。
            // post_title のキーはNC2 の記事ID になっている。
            $post_source_keys = array();
            if (array_key_exists('blog_post', $blog_ini) && array_key_exists('post_title', $blog_ini['blog_post'])) {
                $post_source_keys = array_keys($blog_ini['blog_post']['post_title']);
            }

            // Blogs の記事を取得（TSV）
            $blog_tsv_filename = str_replace('ini', 'tsv', basename($blogs_ini_path));
            if (Storage::exists($this->getImportPath('blogs/') . $blog_tsv_filename)) {
                // TSV ファイル取得（1つのTSV で1つのブログ丸ごと）
                $blog_tsv = Storage::get($this->getImportPath('blogs/') . $blog_tsv_filename);
                // POST が無いものは対象外
                if (empty($blog_tsv)) {
                    continue;
                }
                // 記事のインデックス（記事のマッピングテーブル用）
                $post_index = 0;

                // 改行で記事毎に分割
                $blog_tsv_lines = explode("\n", $blog_tsv);
                foreach ($blog_tsv_lines as $blog_tsv_line) {
                    // タブで項目に分割
                    $blog_tsv_cols = explode("\t", $blog_tsv_line);

                    // 投稿日時の変換(NC2 の投稿日時はGMT のため、9時間プラスする) NC2=20151020122600
                    $posted_at_ts = mktime(substr($blog_tsv_cols[0], 8, 2), substr($blog_tsv_cols[0], 10, 2), substr($blog_tsv_cols[0], 12, 2), substr($blog_tsv_cols[0], 4, 2), substr($blog_tsv_cols[0], 6, 2), substr($blog_tsv_cols[0], 0, 4));
                    $posted_at = date('Y-m-d H:i:s', $posted_at_ts + (60 * 60 * 9));

                    // 記事のカテゴリID
                    // 記事のカテゴリID = original_categories にキーがあれば、original_categories の文言でブログ単位のカテゴリを探してID 特定。
                    $categories_id = null;
                    // if ($common_categories->firstWhere('category', $blog_tsv_cols[1])) {
                    //     $categories_id = $common_categories->firstWhere('category', $blog_tsv_cols[1])->id;
                    // }
                    // if (empty($categories_id) && $blog_categories->firstWhere('category', $blog_tsv_cols[1])) {
                    //     $categories_id = $blog_categories->firstWhere('category', $blog_tsv_cols[1])->id;
                    // }
                    if ($blog_categories->firstWhere('category', $blog_tsv_cols[1])) {
                        $categories_id = $blog_categories->firstWhere('category', $blog_tsv_cols[1])->id;
                    }

                    // 本文
                    $post_text = $this->changeWYSIWYG($blog_tsv_cols[5]);
                    $post_text = $this->addParagraph('blogs', $post_text);

                    // 本文2
                    $post_text2 = $this->changeWYSIWYG($blog_tsv_cols[6]);
                    $post_text2 = $this->addParagraph('blogs', $post_text2);

                    // 続きを読む
                    $read_more_flag = 0;
                    $read_more_button = null;
                    $close_more_button = null;
                    if (!empty($post_text2)) {
                        $read_more_flag = 1;
                        $read_more_button = '続きを読む';
                        $close_more_button = '閉じる';
                    }

                    // ブログ記事テーブル追加
                    $blogs_posts = BlogsPosts::create([
                        'blogs_id' => $blog->id,
                        'post_title' => $blog_tsv_cols[4],
                        'post_text' => $post_text,
                        'post_text2' => $post_text2,
                        'read_more_flag' => $read_more_flag,
                        'read_more_button' => $read_more_button,
                        'close_more_button' => $close_more_button,
                        'categories_id' => $categories_id,
                        'important' => null,
                        'status' => 0,
                        'posted_at' => $posted_at
                    ]);

                    // contents_id を初回はid と同じものを入れて、更新
                    $blogs_posts->contents_id = $blogs_posts->id;
                    $blogs_posts->save();

                    // いいね数があれば likesテーブル保存
                    if ($blog_tsv_cols[9]) {

                        $like = Like::create([
                            'target' => 'blogs',
                            'target_id' => $blog->id,
                            'target_contents_id' => $blogs_posts->contents_id,
                            'count' => $blog_tsv_cols[9],
                        ]);

                        // 移行はsession_id でも nc2 user_id でも、全てセッションIDに格納で処理
                        $session_ids = explode('|', $blog_tsv_cols[10]);
                        // $like_users_datas = [];
                        foreach ($session_ids as $session_id) {
                            // $like_users_datas[] = [
                            $like_users = LikeUser::create([
                                'target' => 'blogs',
                                'target_id' => $blog->id,
                                'target_contents_id' => $blogs_posts->contents_id,
                                'likes_id' => $like->id,
                                'session_id' => $session_id,
                                'users_id' => null,
                            ]);
                        }
                        // $like_users = LikeUser::insert($like_users_datas);
                    }

                    // マッピングテーブルの追加
                    if (array_key_exists($post_index, $post_source_keys)) {
                        $target_source_table = 'blogs_post';
                        if (array_key_exists('source_info', $blog_ini) && array_key_exists('module_name', $blog_ini['source_info']) && $blog_ini['source_info']['module_name'] == 'bbs') {
                            $target_source_table = 'bbses_post';
                        }
                        $mapping = MigrationMapping::create([
                            'target_source_table'  => $target_source_table,
                            'source_key'           => $post_source_keys[$post_index],
                            'destination_key'      => $blogs_posts->id,
                        ]);
                    }
                    $post_index++;
                }
            }
        }
    }

    /**
     * Connect-CMS 移行形式のFAQをインポート
     */
    private function importFaqs($redo)
    {
        $this->putMonitor(3, "Faqs import Start.");

        // データクリア
        if ($redo === true) {
            $this->clearData('faqs');
        }

        // FAQ定義の取り込み
        $faqs_ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('faqs/faq_*.ini'));

        // FAQ定義のループ
        foreach ($faqs_ini_paths as $faqs_ini_path) {
            // ini_file の解析
            $faq_ini = parse_ini_file($faqs_ini_path, true);

            // ルーム指定を探しておく。
            $room_id = null;
            if (array_key_exists('source_info', $faq_ini) && array_key_exists('room_id', $faq_ini['source_info'])) {
                $room_id = $faq_ini['source_info']['room_id'];
            }

            // nc2 の faq_id
            $nc2_faq_id = 0;
            if (array_key_exists('source_info', $faq_ini) && array_key_exists('faq_id', $faq_ini['source_info'])) {
                $nc2_faq_id = $faq_ini['source_info']['faq_id'];
            }

            // FAQの統合
            $cc_import_marges = $this->getMigrationConfig('faqs', 'cc_import_marges');
            if (is_array($cc_import_marges) && array_key_exists($nc2_faq_id, $cc_import_marges)) {
                $nc2_faq_id = $cc_import_marges[$nc2_faq_id];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'faqs')->where('source_key', $nc2_faq_id)->first();

            // マッピングテーブルを確認して、追加か更新の処理を分岐
            if (empty($mapping)) {
                // マッピングテーブルがなければ、Buckets テーブルと Faqs テーブル、マッピングテーブルを追加
                $faq_name = '無題';
                if (array_key_exists('faq_base', $faq_ini) && array_key_exists('faq_name', $faq_ini['faq_base'])) {
                    $faq_name = $faq_ini['faq_base']['faq_name'];
                }
                $bucket = Buckets::create(['bucket_name' => $faq_name, 'plugin_name' => 'faqs']);

                $view_count = 10;
                $faq = Faqs::create(['bucket_id' => $bucket->id, 'faq_name' => $faq_name, 'view_count' => $view_count]);

                // マッピングテーブルの追加
                $mapping = MigrationMapping::create([
                    'target_source_table'  => 'faqs',
                    'source_key'           => $nc2_faq_id,
                    'destination_key'      => $faq->id,
                ]);
            }

            // これ以降は追加も更新も同じロジック

            // FAQ固有カテゴリ追加
            $faq_categories = collect();
            if (array_key_exists('categories', $faq_ini) && array_key_exists('original_categories', $faq_ini['categories'])) {
                $faq_categories = $this->importCategories($faq_ini['categories']['original_categories'], 'faqs', $faq->id);
            }

            // FAQのカテゴリーテーブル作成（カテゴリーの使用on設定）
            $index = 1;
            foreach ($faq_categories as $faq_category) {
                // FaqsCategories::create(['faqs_id' => $faq_category->plugin_id, 'categories_id' => $faq_category->id, 'view_flag' => 1, 'display_sequence' => $index]);
                PluginCategory::create(['target' => 'faqs', 'target_id' => $faq_category->plugin_id, 'categories_id' => $faq_category->id, 'view_flag' => 1, 'display_sequence' => $index]);
                $index++;
            }

            // Faqs の記事を取得（TSV）
            $faq_tsv_filename = str_replace('ini', 'tsv', basename($faqs_ini_path));
            if (Storage::exists($this->getImportPath('faqs/') . $faq_tsv_filename)) {
                // TSV ファイル取得（1つのTSV で1つのブログ丸ごと）
                $faq_tsv = Storage::get($this->getImportPath('faqs/') . $faq_tsv_filename);
                // POST が無いものは対象外
                if (empty($faq_tsv)) {
                    continue;
                }
                // 改行で記事毎に分割
                $faq_tsv_lines = explode("\n", $faq_tsv);
                foreach ($faq_tsv_lines as $faq_tsv_line) {
                    // タブで項目に分割
                    $faq_tsv_cols = explode("\t", $faq_tsv_line);
                    if (!isset($faq_tsv_cols[2])) {
                        continue;
                    }
                    // 投稿日時の変換(NC2 の投稿日時はGMT のため、9時間プラスする) NC2=20151020122600
                    $posted_at_ts = mktime((int)substr($faq_tsv_cols[2], 8, 2), (int)substr($faq_tsv_cols[2], 10, 2), (int)substr($faq_tsv_cols[2], 12, 2), (int)substr($faq_tsv_cols[2], 4, 2), (int)substr($faq_tsv_cols[2], 6, 2), (int)substr($faq_tsv_cols[2], 0, 4));
                    $posted_at = date('Y-m-d H:i:s', $posted_at_ts + (60 * 60 * 9));

                    // 記事のカテゴリID
                    // 記事のカテゴリID = original_categories にキーがあれば、original_categories の文言でFAQ単位のカテゴリを探してID 特定。
                    $categories_id = null;
                    if ($faq_categories->firstWhere('category', $faq_tsv_cols[0])) {
                        $categories_id = $faq_categories->firstWhere('category', $faq_tsv_cols[0])->id;
                    }

                    // 本文
                    $faq_tsv_cols[3] = isset($faq_tsv_cols[3]) ? $faq_tsv_cols[3] : '';
                    $faq_tsv_cols[4] = isset($faq_tsv_cols[4]) ? $faq_tsv_cols[4] : '';
                    $post_text = $this->changeWYSIWYG($faq_tsv_cols[4]);
                    $post_text = $this->addParagraph('faqs', $post_text);

                    // FAQ記事テーブル追加
                    $faqs_posts = FaqsPosts::create(['faqs_id' => $faq->id, 'post_title' => $faq_tsv_cols[3], 'post_text' => $post_text, 'categories_id' => $categories_id, 'posted_at' => $posted_at]);
                    $faqs_posts->save();
                    $faqs_posts->contents_id = $faqs_posts->id;

                    // 更新
                    $faqs_posts->save();
                }
            }
        }
    }

    /**
     * Connect-CMS 移行形式のリンクリストをインポート
     */
    private function importLinklists($redo)
    {
        $this->putMonitor(3, "Linklists import Start.");

        // データクリア
        if ($redo === true) {
            $this->clearData('linklists');
        }

        // リンクリスト定義の取り込み
        $linklists_ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('linklists/linklist_*.ini'));

        // リンクリスト定義のループ
        foreach ($linklists_ini_paths as $linklists_ini_path) {
            // ini_file の解析
            $linklist_ini = parse_ini_file($linklists_ini_path, true);

            // ルーム指定を探しておく。
            $room_id = null;
            if (array_key_exists('source_info', $linklist_ini) && array_key_exists('room_id', $linklist_ini['source_info'])) {
                $room_id = $linklist_ini['source_info']['room_id'];
            }

            // nc2 の linklist_id
            $nc2_linklist_id = 0;
            if (array_key_exists('source_info', $linklist_ini) && array_key_exists('linklist_id', $linklist_ini['source_info'])) {
                $nc2_linklist_id = $linklist_ini['source_info']['linklist_id'];
            }

            // リンクリストの統合
            $cc_import_marges = $this->getMigrationConfig('linklists', 'cc_import_marges');
            if (is_array($cc_import_marges) && array_key_exists($nc2_linklist_id, $cc_import_marges)) {
                $nc2_linklist_id = $cc_import_marges[$nc2_linklist_id];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'linklists')->where('source_key', $nc2_linklist_id)->first();

            // マッピングテーブルを確認して、追加か更新の処理を分岐
            if (empty($mapping)) {
                // マッピングテーブルがなければ、Buckets テーブルと リンクリストテーブル、マッピングテーブルを追加
                $linklist_name = '無題';
                if (array_key_exists('linklist_base', $linklist_ini) && array_key_exists('linklist_name', $linklist_ini['linklist_base'])) {
                    $linklist_name = $linklist_ini['linklist_base']['linklist_name'];
                }
                $bucket = Buckets::create(['bucket_name' => $linklist_name, 'plugin_name' => 'linklists']);

                $view_count = 10;
                $linklist = Linklist::create(['bucket_id' => $bucket->id, 'name' => $linklist_name, 'view_count' => $view_count]);

                // マッピングテーブルの追加
                $mapping = MigrationMapping::create([
                    'target_source_table'  => 'linklists',
                    'source_key'           => $nc2_linklist_id,
                    'destination_key'      => $linklist->id,
                ]);
            }

            // これ以降は追加も更新も同じロジック

            // リンクリスト固有カテゴリ追加
            $linklist_categories = collect();
            if (array_key_exists('categories', $linklist_ini) && array_key_exists('original_categories', $linklist_ini['categories'])) {
                $linklist_categories = $this->importCategories($linklist_ini['categories']['original_categories'], 'linklists', $linklist->id);
            }

            // リンクリストのカテゴリーテーブル作成（カテゴリーの使用on設定）
            $index = 1;
            foreach ($linklist_categories as $linklist_category) {
                PluginCategory::create(['target' => 'linklists', 'target_id' => $linklist_category->plugin_id, 'categories_id' => $linklist_category->id, 'view_flag' => 1, 'display_sequence' => $index]);
                $index++;
            }

            // リンクリストの記事を取得（TSV）
            $linklist_tsv_filename = str_replace('ini', 'tsv', basename($linklists_ini_path));
            if (Storage::exists($this->getImportPath('linklists/') . $linklist_tsv_filename)) {
                // TSV ファイル取得（1つのTSV で1つのブログ丸ごと）
                $linklist_tsv = Storage::get($this->getImportPath('linklists/') . $linklist_tsv_filename);
                // POST が無いものは対象外
                if (empty($linklist_tsv)) {
                    continue;
                }
                // 改行で記事毎に分割
                $linklist_tsv_lines = explode("\n", $linklist_tsv);
                foreach ($linklist_tsv_lines as $linklist_tsv_line) {
                    // タブで項目に分割
                    $linklist_tsv_cols = explode("\t", $linklist_tsv_line);
                    $linklist_tsv_cols[3] = isset($linklist_tsv_cols[3]) ? $linklist_tsv_cols[3] : '0';
                    $linklist_tsv_cols[4] = isset($linklist_tsv_cols[4]) ? $linklist_tsv_cols[4] : '0';
                    $linklist_tsv_cols[5] = isset($linklist_tsv_cols[5]) ? $linklist_tsv_cols[5] : '0';

                    // 記事のカテゴリID
                    // 記事のカテゴリID = original_categories にキーがあれば、original_categories の文言でリンクリスト単位のカテゴリを探してID 特定。
                    $categories_id = null;
                    if ($linklist_categories->firstWhere('category', $linklist_tsv_cols[5])) {
                        $categories_id = $linklist_categories->firstWhere('category', $linklist_tsv_cols[5])->id;
                    }

                    // リンクリストテーブル追加
                    $linklists_posts = LinklistPost::create(['linklist_id' => $linklist->id, 'title' => $linklist_tsv_cols[0], 'url' => $linklist_tsv_cols[1], 'description' => $linklist_tsv_cols[2], 'categories_id' => $categories_id, 'target_blank_flag' => $linklist_tsv_cols[3], 'display_sequence' => $linklist_tsv_cols[4]]);

                    // 更新
                    $linklists_posts->save();
                }
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
            $this->clearData('databases');
        }

        // データベース定義の取り込み
        $databases_ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('databases/database_*.ini'));

        // ルームの指定（あれば後で使う）
        //$cc_import_databases_room_ids = $this->getMigrationConfig('databases', 'cc_import_databases_room_ids');

        // ユーザ取得
        $users = User::get();

        // データベース定義のループ
        foreach ($databases_ini_paths as $databases_ini_path) {
            // ini_file の解析
            $databases_ini = parse_ini_file($databases_ini_path, true);

            // ルーム指定を探しておく。
            // $room_id = null;
            // if (array_key_exists('source_info', $databases_ini) && array_key_exists('room_id', $databases_ini['source_info'])) {
            //     $room_id = $databases_ini['source_info']['room_id'];
            // }

            //// ルーム指定があれば、指定されたルームのみ処理する。
            //if (empty($cc_import_databases_room_ids)) {
            //    // ルーム指定なし。全データの移行
            //} elseif (!empty($room_id) && !empty($cc_import_databases_room_ids) && in_array($room_id, $cc_import_databases_room_ids)) {
            //    // ルーム指定あり。指定ルームに合致する。
            //} else {
            //    // ルーム指定あり。条件に合致せず。移行しない。
            //    continue;
            //}

            // nc2 の multidatabase_id
            $nc2_multidatabase_id = $this->getArrayValue($databases_ini, 'source_info', 'multidatabase_id', 0);

            // データベース指定の有無
            $cc_import_where_database_ids = $this->getMigrationConfig('databases', 'cc_import_where_database_ids');
            if (!empty($cc_import_where_database_ids)) {
                if (!in_array($nc2_multidatabase_id, $cc_import_where_database_ids)) {
                    continue;
                }
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'databases')->where('source_key', $nc2_multidatabase_id)->first();

            // マッピングテーブルを確認して、追加か更新の処理を分岐
            if (empty($mapping)) {
                // マッピングテーブルがなければ、Buckets テーブルと Database テーブル、マッピングテーブルを追加
                $database_name = $this->getArrayValue($databases_ini, 'database_base', 'database_name', '無題');
                $bucket = Buckets::create(['bucket_name' => $database_name, 'plugin_name' => 'databases']);

                $database = new Databases(['bucket_id' => $bucket->id, 'databases_name' => $database_name, 'data_save_flag' => 1]);
                $database->created_id = $this->getUserIdFromLoginId($users, $this->getArrayValue($databases_ini, 'source_info', 'insert_login_id', null));
                $database->created_name = $this->getArrayValue($databases_ini, 'source_info', 'insert_user_name', null);
                $database->created_at = $this->getDatetimeFromIniAndCheckFormat($databases_ini, 'source_info', 'insert_time');
                $database->updated_id = $this->getUserIdFromLoginId($users, $this->getArrayValue($databases_ini, 'source_info', 'update_login_id', null));
                $database->updated_name = $this->getArrayValue($databases_ini, 'source_info', 'update_user_name', null);
                $database->updated_at = $this->getDatetimeFromIniAndCheckFormat($databases_ini, 'source_info', 'update_time');
                // 登録更新日時を自動更新しない
                $database->timestamps = false;
                $database->save();

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

            if (Storage::exists($this->getImportPath('databases/') . $database_tsv_filename)) {
                // TSV ファイル取得（1つのTSV で1つのデータベース丸ごと）
                $database_tsv = Storage::get($this->getImportPath('databases/') . $database_tsv_filename);
                // POST が無いものは対象外
                if (empty($database_tsv)) {
                    continue;
                }

                // 行ループで使用する各種変数
                $header_skip = true;       // ヘッダースキップフラグ（1行目はカラム名の行）
                $status_idx = 0;           // status のカラムインデックス（0 の場合は無効）
                $status = '';              // status の内容
                $display_sequence_idx = 0; // display_sequence のカラムインデックス（0 の場合は無効）
                $display_sequence = '';    // display_sequence の内容
                $posted_at_idx = 0;        // posted_at のカラムインデックス（0 の場合は無効）
                $posted_at = '';           // posted_at の内容（日時）
                $created_at_idx = 0;       // created_at のカラムインデックス（0 の場合は無効）
                $created_at = '';          // created_at の内容（日時）
                $updated_at_idx = 0;       // updated_at のカラムインデックス（0 の場合は無効）
                $updated_at = '';          // updated_at の内容（日時）
                $content_id_idx = 0;       // content_id のカラムインデックス（0 の場合は無効）
                $content_id = '';          // content_id の内容（日時）

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
                            if ($database_tsv_col == 'posted_at') {
                                $posted_at_idx = $loop_idx;
                            } elseif ($database_tsv_col == 'created_at') {
                                $created_at_idx = $loop_idx;
                            } elseif ($database_tsv_col == 'updated_at') {
                                $updated_at_idx = $loop_idx;
                            } elseif ($database_tsv_col == 'status') {
                                $status_idx = $loop_idx;
                            } elseif ($database_tsv_col == 'display_sequence') {
                                $display_sequence_idx = $loop_idx;
                            } elseif ($database_tsv_col == 'content_id') {
                                $content_id_idx = $loop_idx;
                            }
                            $loop_idx++;
                        }
                        continue;
                    }
                    // 行データをタブで項目に分割
                    $database_tsv_cols = explode("\t", trim($database_tsv_line, "\n\r"));

                    // posted_at、created_at、updated_at の設定
                    // posted_at、created_at、updated_at のカラムがない or データが空の場合は、処理時間を入れる。
                    if ($posted_at_idx != 0 && array_key_exists($posted_at_idx, $database_tsv_cols) && !empty($database_tsv_cols[$posted_at_idx])) {
                        $posted_at = $database_tsv_cols[$posted_at_idx];
                        if (!\DateTime::createFromFormat('Y-m-d H:i:s', $posted_at)) {
                            $this->putError(3, '日付エラー', "posted_at = " . $posted_at);
                            $posted_at = date('Y-m-d H:i:s');
                        }
                    } else {
                        $posted_at = date('Y-m-d H:i:s');
                    }
                    if ($created_at_idx != 0 && array_key_exists($created_at_idx, $database_tsv_cols) && !empty($database_tsv_cols[$created_at_idx])) {
                        $created_at = $database_tsv_cols[$created_at_idx];
                        if (!\DateTime::createFromFormat('Y-m-d H:i:s', $created_at)) {
                            $this->putError(3, '日付エラー', "created_at = " . $created_at);
                            $created_at = date('Y-m-d H:i:s');
                        }
                    } else {
                        $created_at = date('Y-m-d H:i:s');
                    }
                    if ($updated_at_idx != 0 && array_key_exists($updated_at_idx, $database_tsv_cols) && !empty($database_tsv_cols[$updated_at_idx])) {
                        $updated_at = $database_tsv_cols[$updated_at_idx];
                        if (!\DateTime::createFromFormat('Y-m-d H:i:s', $updated_at)) {
                            $this->putError(3, '日付エラー', "updated_at = " . $updated_at);
                            $updated_at = date('Y-m-d H:i:s');
                        }
                    } else {
                        $updated_at = date('Y-m-d H:i:s');
                    }

                    // status
                    if ($status_idx != 0 && array_key_exists($status_idx, $database_tsv_cols) && !empty($database_tsv_cols[$status_idx])) {
                        $status = $database_tsv_cols[$status_idx];
                    } else {
                        $status = 0;
                    }

                    // display_sequence
                    if ($display_sequence_idx != 0 && array_key_exists($display_sequence_idx, $database_tsv_cols) && !empty($database_tsv_cols[$display_sequence_idx])) {
                        $display_sequence = $database_tsv_cols[$display_sequence_idx];
                    } else {
                        $display_sequence = 0;
                    }

                    // 行データの追加
                    $databases_input = DatabasesInputs::create([
                        'databases_id'     => $database->id,
                        'status'           => $status,
                        'display_sequence' => $display_sequence,
                        'posted_at'        => $posted_at,
                        'created_at'       => $created_at,
                        'updated_at'       => $updated_at,
                    ]);

                    // content_id
                    if ($content_id_idx != 0 && array_key_exists($content_id_idx, $database_tsv_cols) && !empty($database_tsv_cols[$content_id_idx])) {
                        $content_id = $database_tsv_cols[$content_id_idx];
                    } else {
                        $content_id = 0;
                    }

                    // 記事のマッピングテーブルの追加
                    if ($content_id_idx) {
                        $mapping = MigrationMapping::create([
                            'target_source_table'  => 'databases_post',
                            'source_key'           => $content_id,
                            'destination_key'      => $databases_input->id,
                        ]);
                    }

                    $databases_columns_id_idx = 0; // 処理カラムのloop index

                    // データベースのバルクINSERT対応
                    $bulks = array();

                    foreach ($database_tsv_cols as $database_tsv_col) {
                        // posted_at、created_at、updated_at はカラムとしては読み飛ばす
                        if ($databases_columns_id_idx == $posted_at_idx ||
                            $databases_columns_id_idx == $created_at_idx ||
                            $databases_columns_id_idx == $updated_at_idx ||
                            $databases_columns_id_idx == $status_idx ||
                            $databases_columns_id_idx == $display_sequence_idx) {
                            continue;
                        }

                        // エラーの内容は再度、チェックすること。
                        if (array_key_exists($databases_columns_id_idx, $column_ids)) {
                            // 項目の型により変換するもの
                            if ($create_columns[$databases_columns_id_idx]->column_type == 'text' || $create_columns[$databases_columns_id_idx]->column_type == 'textarea') {
                                // テキスト or 複数行テキスト
                                $database_tsv_col = str_replace('<br />', "\n", $database_tsv_col);
                            } elseif ($create_columns[$databases_columns_id_idx]->column_type == 'wysiwyg') {
                                // WYSIWYG
                                $database_tsv_col = $this->changeWYSIWYG($database_tsv_col);
                            } elseif ($create_columns[$databases_columns_id_idx]->column_type == 'image' || $create_columns[$databases_columns_id_idx]->column_type == 'file') {
                                // 画像、ファイル
                                $database_tsv_col = str_replace('../../uploads/upload_', "", $database_tsv_col);
                                if (empty($database_tsv_col)) {
                                    $database_tsv_col = '';
                                } else {
                                    $database_tsv_col = intval(substr($database_tsv_col, 0, strpos($database_tsv_col, '.')));
                                    $upload_mapping = MigrationMapping::where('target_source_table', 'uploads')->where('source_key', $database_tsv_col)->first();
                                    if (!empty($upload_mapping)) {
                                        $database_tsv_col = $upload_mapping->destination_key;
                                    }
                                }
                            } elseif ($create_columns[$databases_columns_id_idx]->column_type == 'created' || $create_columns[$databases_columns_id_idx]->column_type == 'updated') {
                                // 登録日、更新日の場合にはセルデータを作らず返却
                                $databases_columns_id_idx++;
                                continue;
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
     * Connect-CMS 移行形式のフォームをインポート
     */
    private function importForms($redo)
    {
        $this->putMonitor(3, "Forms import Start.");

        // データクリア
        if ($redo === true) {
            $this->clearData('forms');
        }

        // フォーム定義の取り込み
        $form_ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('forms/form_*.ini'));

        // フォーム定義のループ
        foreach ($form_ini_paths as $form_ini_path) {
            // ini_file の解析
            $form_ini = parse_ini_file($form_ini_path, true);

            // ルーム指定を探しておく。
            $room_id = null;
            if (array_key_exists('source_info', $form_ini) && array_key_exists('room_id', $form_ini['source_info'])) {
                $room_id = $form_ini['source_info']['room_id'];
            }

            // nc2 の registration_id
            $nc2_registration_id = 0;
            if (array_key_exists('source_info', $form_ini) && array_key_exists('registration_id', $form_ini['source_info'])) {
                $nc2_registration_id = $form_ini['source_info']['registration_id'];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'forms')->where('source_key', $nc2_registration_id)->first();

            // マッピングテーブルを確認して、あれば削除
            if (!empty($mapping)) {
                // form 取得。この情報から紐づけて、消すものを消してゆく。
                $form = Forms::where('id', $mapping->destination_key)->first();
                // form カラム情報取得
                $forms_columns = FormsColumns::where('forms_id', $mapping->destination_key)->get();
                // form データ行情報取得
                $forms_inputs = FormsInputs::where('forms_id', $mapping->destination_key)->get();

                // 登録データ詳細削除
                FormsInputCols::whereIn('forms_inputs_id', $forms_inputs->pluck('id'))->delete();
                // 登録データ行削除
                FormsInputs::where('forms_id', $mapping->destination_key)->get();
                // カラム選択肢削除
                FormsColumnsSelects::whereIn('forms_columns_id', $forms_columns->pluck('id'))->delete();
                // カラム削除
                FormsColumns::where('id', $mapping->destination_key)->delete();

                if (!empty($form)) {
                    // Buckets 削除
                    Buckets::where('id', $form->bucket_id)->delete();
                    // フォーム削除
                    $form->delete();
                }
                // マッピングテーブル削除
                $mapping->delete();
            }

            // Buckets テーブルと Forms テーブル、マッピングテーブルを追加
            $form_name = '無題';
            if (array_key_exists('form_base', $form_ini) && array_key_exists('forms_name', $form_ini['form_base'])) {
                $form_name = $form_ini['form_base']['forms_name'];
            }
            $bucket = Buckets::create(['bucket_name' => $form_name, 'plugin_name' => 'forms']);

            // 登録期間で制御する
            $regist_control_flag = 0;
            $regist_to = null;

            // nc2 の active_flag (動作／停止)
            $nc2_active_flag = $this->getArrayValue($form_ini, 'source_info', 'active_flag', 1);
            if ($nc2_active_flag == 0) {
                // 停止
                // 停止フォームなら、登録期間外で代用してフォーム登録を停止する。
                $regist_control_flag = 1;
                $regist_to = Carbon::now()->setTime(0, 0, 0);
                $this->putMonitor(3, '停止フォームのため、登録期間外で代用してフォーム登録を停止します。', "バケツ名={$bucket->bucket_name}, bucket_id={$bucket->id}");

            } else {
                // 動作
                $regist_control_flag = $form_ini['form_base']['regist_control_flag'];
                $regist_to = $this->getDatetimeFromIniAndCheckFormat($form_ini, 'form_base', 'regist_to', '');
                $regist_to = $regist_to ? $regist_to : null;
            }

            // メールフォーマットの置換処理
            $mail_format = str_replace('\n', "\n", $form_ini['form_base']['mail_format']);
            // 登録日時、ルームの出力は未実装機能
            $replace_tags = [
                '{X-SITE_NAME}'=>'[[site_name]]',
                '{X-ROOM}'=>'',
                '{X-REGISTRATION_NAME}'=>'[[form_name]]',
                '{X-TO_DATE}'=>'[[to_datetime]]',
                '{X-DATA}'=>'[[body]]',
            ];
            $mail_subject = str_replace(array_keys($replace_tags), array_values($replace_tags), $form_ini['form_base']['mail_subject']);
            $mail_format = str_replace(array_keys($replace_tags), array_values($replace_tags), $mail_format);
            $form = Forms::create([
                'bucket_id'           => $bucket->id,
                'forms_name'          => $form_name,
                'mail_send_flag'      => $form_ini['form_base']['mail_send_flag'],
                'mail_send_address'   => $form_ini['form_base']['mail_send_address'],
                'user_mail_send_flag' => $form_ini['form_base']['user_mail_send_flag'],
                'mail_subject'        => $mail_subject,
                'mail_format'         => $mail_format,
                'data_save_flag'      => $form_ini['form_base']['data_save_flag'],
                'after_message'       => str_replace('\n', "\n", $form_ini['form_base']['after_message']),
                'numbering_use_flag'  => $form_ini['form_base']['numbering_use_flag'],
                'numbering_prefix'    => $form_ini['form_base']['numbering_prefix'],
                'regist_control_flag' => $regist_control_flag,
                'regist_to'           => $regist_to,
            ]);

            // マッピングテーブルの追加
            $mapping = MigrationMapping::create([
                'target_source_table'  => 'forms',
                'source_key'           => $nc2_registration_id,
                'destination_key'      => $form->id,
            ]);

            // カラムID のNC2, Connect-CMS 変換テーブル（項目データの登録時に使うため）
            $column_ids = array();

            // カラムテーブルとカラム選択肢テーブルの追加
            $display_sequence_column = 0;
            foreach ($form_ini['form_columns']['form_column'] as $item_id => $item_name) {
                $form_column = FormsColumns::create([
                    'forms_id'                   => $form->id,
                    'column_type'                => $form_ini[$item_id]['column_type'],
                    'column_name'                => $form_ini[$item_id]['column_name'],
                    'required'                   => $form_ini[$item_id]['required'],
                    'frame_col'                  => $form_ini[$item_id]['frame_col'],
                    'caption'                    => $form_ini[$item_id]['caption'],
                    'caption_color'              => $form_ini[$item_id]['caption_color'],
                    'minutes_increments'         => $form_ini[$item_id]['minutes_increments'],
                    'minutes_increments_from'    => $form_ini[$item_id]['minutes_increments_from'],
                    'minutes_increments_to'      => $form_ini[$item_id]['minutes_increments_to'],
                    'rule_allowed_numeric'       => $form_ini[$item_id]['rule_allowed_numeric'],
                    'rule_allowed_alpha_numeric' => $form_ini[$item_id]['rule_allowed_alpha_numeric'],
                    'rule_digits_or_less'        => $form_ini[$item_id]['rule_digits_or_less'],
                    'rule_max'                   => $form_ini[$item_id]['rule_max'],
                    'rule_min'                   => $form_ini[$item_id]['rule_min'],
                    'rule_word_count'            => $form_ini[$item_id]['rule_word_count'],
                    'rule_date_after_equal'      => $form_ini[$item_id]['rule_date_after_equal'],
                    'display_sequence'           => $display_sequence_column++,
                ]);

                $column_ids[$item_id] = $form_column->id;

                if (!empty($form_ini[$item_id]['option_value'])) {
                    $column_selects = explode('|', $form_ini[$item_id]['option_value']);

                    if (!empty($column_selects)) {
                        $display_sequence_column_select = 0;
                        foreach ($column_selects as $column_select) {
                            $form_column_select = FormsColumnsSelects::create([
                                'forms_columns_id' => $form_column->id,
                                'value'            => $column_select,
                                'caption'          => null,
                                'default'          => null,
                                'display_sequence' => $display_sequence_column_select++,
                            ]);
                        }
                    }
                }
            }

            // データ行と登録データの移行

            // 登録データの取り込み
            if (!File::exists(str_replace('.ini', '.txt', $form_ini_path))) {
                continue;
            }

            $data_txt_ini = parse_ini_file(str_replace('.ini', '.txt', $form_ini_path), true);

            // データがなければ戻る
            if (!array_key_exists('form_inputs', $data_txt_ini) || !array_key_exists('input', $data_txt_ini['form_inputs'])) {
                continue;
            }

            // 行のループ
            foreach ($data_txt_ini['form_inputs']['input'] as $data_id => $null) {
                $forms_inputs = FormsInputs::create([
                    'forms_id' => $form->id,
                ]);

                // データベースのバルクINSERT対応
                $bulks = array();

                // 項目データのループ
                foreach ($data_txt_ini[$data_id] as $item_id => $data) {
                    $bulks[] = ['forms_inputs_id'  => $forms_inputs->id,
                        'forms_columns_id' => $column_ids[$item_id],
                        'value'            => $data];
                    /*
                    $forms_inputs_cols = FormsInputCols::create([
                        'forms_inputs_id'  => $forms_inputs->id,
                        'forms_columns_id' => $column_ids[$item_id],
                        'value'            => $data,
                    ]);
                    */
                }
                // バルクINSERT
                DB::table('forms_input_cols')->insert($bulks);
            }
        }
    }


    /**
     * Connect-CMS 移行形式の新着情報をインポート
     */
    private function importWhatsnews($redo)
    {
        $this->putMonitor(3, "Whatsnews import Start.");

        // データクリア
        if ($redo === true) {
            $this->clearData('whatsnews');
        }

        // 新着情報定義の取り込み
        $whatsnew_ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('whatsnews/whatsnew_*.ini'));

        // 新着情報定義のループ
        foreach ($whatsnew_ini_paths as $whatsnew_ini_paths) {
            // ini_file の解析
            $whatsnew_ini = parse_ini_file($whatsnew_ini_paths, true);

            // ルーム指定を探しておく。
            $room_id = null;
            if (array_key_exists('source_info', $whatsnew_ini) && array_key_exists('room_id', $whatsnew_ini['source_info'])) {
                $room_id = $whatsnew_ini['source_info']['room_id'];
            }

            // nc2 の whatsnew_block_id
            $nc2_whatsnew_block_id = 0;
            if (array_key_exists('source_info', $whatsnew_ini) && array_key_exists('whatsnew_block_id', $whatsnew_ini['source_info'])) {
                $nc2_whatsnew_block_id = $whatsnew_ini['source_info']['whatsnew_block_id'];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'whatsnews')->where('source_key', $nc2_whatsnew_block_id)->first();

            // マッピングテーブルを確認して、あれば削除
            if (!empty($mapping)) {
                // whatsnews 取得。この情報から紐づけて、消すものを消してゆく。
                $whatsnew = Whatsnews::where('id', $mapping->destination_key)->first();

                if (!empty($whatsnew)) {
                    // Buckets 削除
                    Buckets::where('id', $whatsnew->bucket_id)->delete();
                    // 新着情報削除
                    $whatsnew->delete();
                }
                // マッピングテーブル削除
                $mapping->delete();
            }

            // Buckets テーブルと Whatsnew テーブル、マッピングテーブルを追加
            $whatsnew_name = '無題';
            if (array_key_exists('whatsnew_base', $whatsnew_ini) && array_key_exists('whatsnew_name', $whatsnew_ini['whatsnew_base'])) {
                $whatsnew_name = $whatsnew_ini['whatsnew_base']['whatsnew_name'];
            }
            $bucket = Buckets::create(['bucket_name' => $whatsnew_name, 'plugin_name' => 'whatsnews']);

            $whatsnew = Whatsnews::create([
                'bucket_id'        => $bucket->id,
                'whatsnew_name'    => $whatsnew_name,
                'view_pattern'     => $whatsnew_ini['whatsnew_base']['view_pattern'],
                'count'            => $whatsnew_ini['whatsnew_base']['count'],
                'days'             => $whatsnew_ini['whatsnew_base']['days'],
                'rss'              => $whatsnew_ini['whatsnew_base']['rss'],
                'rss_count'        => $whatsnew_ini['whatsnew_base']['rss_count'],
                'view_posted_name' => $whatsnew_ini['whatsnew_base']['view_posted_name'],
                'view_posted_at'   => $whatsnew_ini['whatsnew_base']['view_posted_at'],
                'target_plugins'   => $whatsnew_ini['whatsnew_base']['target_plugins'],
                'frame_select'     => $whatsnew_ini['whatsnew_base']['frame_select'],
            ]);

            // マッピングテーブルの追加
            $mapping = MigrationMapping::create([
                'target_source_table'  => 'whatsnews',
                'source_key'           => $nc2_whatsnew_block_id,
                'destination_key'      => $whatsnew->id,
            ]);
        }
    }

    /**
     * Connect-CMS 移行形式のキャビネットをインポート
     */
    private function importCabinets($redo)
    {
        $this->putMonitor(3, "Cabinets import Start.");

        // データクリア
        if ($redo === true) {
            $this->clearData('cabinets');
        }

        // キャビネット定義の取り込み
        $ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('cabinets/cabinet_*.ini'));

        // キャビネット定義のループ
        foreach ($ini_paths as $ini_path) {
            // ini_file の解析
            $ini = parse_ini_file($ini_path, true);

            // nc2 の cabinet_id
            $nc2_cabinet_id = 0;
            if (array_key_exists('source_info', $ini) && array_key_exists('cabinet_id', $ini['source_info'])) {
                $nc2_cabinet_id = $ini['source_info']['cabinet_id'];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'cabinets')->where('source_key', $nc2_cabinet_id)->first();

            // マッピングテーブルを確認して、あれば削除
            if (!empty($mapping)) {
                // cabinet 取得。この情報から紐づけて、消すものを消してゆく。
                $cabinet = Cabinet::where('id', $mapping->destination_key)->first();
                // キャビネットコンテンツ削除
                CabinetContent::where('cabinet_id', $mapping->destination_key)->delete();
                if (!empty($cabinet)) {
                    // Buckets 削除
                    Buckets::where('id', $cabinet->bucket_id)->delete();
                    // キャビネット削除
                    $cabinet->delete();
                }
                // マッピングテーブル削除
                $mapping->delete();
            }

            // Buckets テーブルと Cabinets テーブル、マッピングテーブルを追加
            $cabinet_name = '無題';
            if (array_key_exists('cabinet_base', $ini) && array_key_exists('cabinet_name', $ini['cabinet_base'])) {
                $cabinet_name = $ini['cabinet_base']['cabinet_name'];
            }
            $bucket = Buckets::create(['bucket_name' => $cabinet_name, 'plugin_name' => 'cabinets']);

            $cabinet = Cabinet::create([
                'bucket_id' => $bucket->id,
                'name' => $cabinet_name,
                'upload_max_size' => intval($ini['cabinet_base']['upload_max_size']) / 1024,
            ]);

            // ルートディレクトを作成する
            $root_cabinet_content = CabinetContent::create([
                'cabinet_id' => $cabinet->id,
                'name' => $cabinet_name,
                'is_folder' => CabinetContent::is_folder_on,
            ]);

            // マッピングテーブルの追加
            $mapping = MigrationMapping::create([
                'target_source_table'  => 'cabinets',
                'source_key'           => $nc2_cabinet_id,
                'destination_key'      => $cabinet->id,
            ]);

            // ファイルの移行
            $tsv_filename = str_replace('ini', 'tsv', basename($ini_path));
            if (Storage::exists($this->getImportPath('cabinets/') . $tsv_filename)) {
                // TSV ファイル取得（1つのTSV で1つのキャビネット丸ごと）
                $tsv = Storage::get($this->getImportPath('cabinets/') . $tsv_filename);
                // ファイル が無いものは対象外
                if (empty($tsv)) {
                    continue;
                }

                // 改行でノード毎に分割
                $migrated_contents = collect();
                $tsv_lines = explode("\n", $tsv);
                foreach ($tsv_lines as $tsv_line) {
                    // タブで項目に分割
                    $tsv_cols = explode("\t", $tsv_line);

                    $upload_mapping = MigrationMapping::where('target_source_table', 'uploads')
                                        ->where('source_key', $tsv_cols[2])->first();

                    $cabinet_content = CabinetContent::create([
                        'cabinet_id' => $cabinet->id,
                        'upload_id' => $upload_mapping ? $upload_mapping->destination_key : null,
                        'name' => empty($tsv_cols[5]) ? $tsv_cols[4] : $tsv_cols[4] . '.' . $tsv_cols[5],
                        'is_folder' => $tsv_cols[9],
                        'comment' => $tsv_cols[12],
                    ]);
                    $cabinet_content->migrate_parent_id = $tsv_cols[3];
                    $migrated_contents->push($cabinet_content);
                    $mapping = MigrationMapping::create([
                        'target_source_table'  => 'cabinet_contents',
                        'source_key'           => $tsv_cols[0],
                        'destination_key'      => $cabinet_content->id,
                    ]);
                }

                // 移行したcabinet_contentsの入れ子構造再構築
                $mapping_migrated = MigrationMapping::where('target_source_table', 'cabinet_contents')->get();
                foreach ($migrated_contents as $cabinet_content) {
                    if ($cabinet_content->migrate_parent_id == 0) {
                        $root = CabinetContent::where('cabinet_id', $cabinet_content->cabinet_id)
                            ->where('parent_id', null)->first();
                        $cabinet_content->parent_id = $root->id;
                    } else {
                        $cabinet_content->parent_id = $mapping_migrated->where('source_key', $cabinet_content->migrate_parent_id)
                                                        ->first()->destination_key;
                    }
                    $cabinet_content->save();
                }
                CabinetContent::fixTree();
            }
        }
    }

    /**
     * Connect-CMS 移行形式のBBSをインポート
     */
    private function importBbses($redo)
    {
        $this->putMonitor(3, "Bbses import Start.");

        // データクリア
        if ($redo === true) {
            $this->clearData('bbses');
        }

        // BBS定義の取り込み
        $ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('bbses/bbs_*.ini'));
        // ユーザ取得
        $users = User::get();

        // BBS定義のループ
        foreach ($ini_paths as $ini_path) {
            // ini_file の解析
            $ini = parse_ini_file($ini_path, true);

            // nc2 の bbs_id
            $nc2_bbs_id = 0;
            if (array_key_exists('source_info', $ini) && array_key_exists('journal_id', $ini['source_info'])) {
                $tmp = explode('_', $ini['source_info']['journal_id']);
                $nc2_bbs_id = $tmp[1];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'bbses')->where('source_key', $nc2_bbs_id)->first();

            // マッピングテーブルを確認して、あれば削除
            if (!empty($mapping)) {
                // cabinet 取得。この情報から紐づけて、消すものを消してゆく。
                $bbs = Bbs::where('id', $mapping->destination_key)->first();
                // BBS投稿削除
                BbsPost::where('bbs_id', $mapping->destination_key)->delete();
                if (!empty($cabinet)) {
                    // Buckets 削除
                    Buckets::where('id', $bbs->bucket_id)->delete();
                    // BBS削除
                    $bbs->delete();
                }
                // マッピングテーブル削除
                $mapping->delete();
            }

            // Buckets テーブルと Cabinets テーブル、マッピングテーブルを追加
            $bbs_name = $this->getArrayValue($ini, 'blog_base', 'blog_name', '無題');
            $bucket = Buckets::create(['bucket_name' => $bbs_name, 'plugin_name' => 'bbses']);

            $bbs = new Bbs([
                'bucket_id' => $bucket->id,
                'name' => $bbs_name,
                'use_like' => $this->getArrayValue($ini, 'blog_base', 'use_like', 0),
            ]);
            $bbs->created_id = $this->getUserIdFromLoginId($users, $this->getArrayValue($ini, 'source_info', 'insert_login_id', null));
            $bbs->created_name = $this->getArrayValue($ini, 'source_info', 'insert_user_name', null);
            $bbs->created_at = $this->getDatetimeFromIniAndCheckFormat($ini, 'source_info', 'insert_time');
            $bbs->updated_id = $this->getUserIdFromLoginId($users, $this->getArrayValue($ini, 'source_info', 'update_login_id', null));
            $bbs->updated_name = $this->getArrayValue($ini, 'source_info', 'update_user_name', null);
            $bbs->updated_at = $this->getDatetimeFromIniAndCheckFormat($ini, 'source_info', 'update_time');
            // 登録更新日時を自動更新しない
            $bbs->timestamps = false;
            $bbs->save();

            // マッピングテーブルの追加
            $mapping = MigrationMapping::create([
                'target_source_table'  => 'bbses',
                'source_key'           => $nc2_bbs_id,
                'destination_key'      => $bbs->id,
            ]);

            // 記事のマッピングテーブル作成用に記事一覧（post_title）を使用する。
            // post_title のキーはNC2 のBBSID になっている。
            $post_source_keys = array();
            if (array_key_exists('blog_post', $ini) && array_key_exists('post_title', $ini['blog_post'])) {
                $post_source_keys = array_keys($ini['blog_post']['post_title']);
            }

            // 投稿の移行
            $post_index = 0;
            $tsv_filename = str_replace('ini', 'tsv', basename($ini_path));
            if (Storage::exists($this->getImportPath('bbses/') . $tsv_filename)) {
                // TSV ファイル取得（1つのTSV で1つのキャビネット丸ごと）
                $tsv = Storage::get($this->getImportPath('bbses/') . $tsv_filename);
                // ファイル が無いものは対象外
                if (empty($tsv)) {
                    continue;
                }

                // 改行でノード毎に分割
                $tsv_lines = explode("\n", $tsv);
                foreach ($tsv_lines as $tsv_line) {
                    // タブで項目に分割
                    $tsv_cols = explode("\t", $tsv_line);
                    $bbs_post = new BbsPost([
                        'bbs_id' => $bbs->id,
                        'title' => $tsv_cols[4],
                        'body' => $this->changeWYSIWYG($tsv_cols[5]),
                        'thread_root_id' => $tsv_cols[9] === '0' ? 0 : $this->fetchMigratedKey('bbses_post', $tsv_cols[10]),
                        'thread_updated_at' => $this->getDatetimeFromTsvAndCheckFormat(11, $tsv_cols, 11),
                        'first_committed_at' => $this->getDatetimeFromTsvAndCheckFormat(0, $tsv_cols, 0),
                        'parent_id' => $this->fetchMigratedKey('bbses_post', $tsv_cols[9]),
                    ]);
                    $bbs_post->created_id = $this->getUserIdFromLoginId($users, $tsv_cols[15]);
                    $bbs_post->created_name = $tsv_cols[12];
                    $bbs_post->created_at = $this->getDatetimeFromTsvAndCheckFormat(0, $tsv_cols, 0);
                    $bbs_post->updated_id = $this->getUserIdFromLoginId($users, $tsv_cols[18]);
                    $bbs_post->updated_name = $tsv_cols[17];
                    $bbs_post->updated_at = $this->getDatetimeFromTsvAndCheckFormat(16, $tsv_cols, 16);
                    // 登録更新日時を自動更新しない
                    $bbs_post->timestamps = false;
                    $bbs_post->save();
                    // 根記事の場合、保存後のid をthread_root_id にセットして更新
                    if ($tsv_cols[9] === '0') {
                        $bbs_post->thread_root_id = $bbs_post->id;
                        $bbs_post->save();
                    }

                    // いいね数があれば likesテーブル保存
                    if ($tsv_cols[13]) {

                        $like = Like::create([
                            'target' => 'bbses',
                            'target_id' => $bbs->id,
                            'target_contents_id' => $bbs_post->id,
                            'count' => $tsv_cols[13],
                        ]);

                        // いいね数とlike_usersの件数を合わせるため、session_id & users_id 空のデータをいいね数分作成する。
                        for ($i = 0; $tsv_cols[13] > $i; $i++) {
                            $like_users = LikeUser::create([
                                'target' => 'bbses',
                                'target_id' => $bbs->id,
                                'target_contents_id' => $bbs_post->id,
                                'likes_id' => $like->id,
                                'session_id' => null,   // nc2bbsは session_id を保持していないため、nullセット
                                'users_id' => null,
                            ]);
                        }
                    }

                    // マッピングテーブルの追加
                    if (array_key_exists($post_index, $post_source_keys)) {
                        $mapping = MigrationMapping::create([
                            'target_source_table'  => 'bbses_post',
                            'source_key'           => $post_source_keys[$post_index],
                            'destination_key'      => $bbs_post->id,
                        ]);
                    }
                    $post_index++;
                }
            }
        }
    }

    private function fetchMigratedKey($target_table, $key)
    {
        $mapping = MigrationMapping::where('target_source_table', $target_table)
                            ->where('source_key', $key)
                            ->first();
        if ($mapping) {
            return $mapping->destination_key;
        }
        return null;
    }

    /**
     * Connect-CMS 移行形式のカウンターをインポート
     */
    private function importCounters($redo)
    {
        $this->putMonitor(3, "Counters import start.");

        // データクリア
        if ($redo === true) {
            $this->clearData('counters');
        }

        // カウンター定義の取り込み
        $ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('counters/counter_*.ini'));

        // カウンター定義のループ
        foreach ($ini_paths as $ini_path) {
            // ini_file の解析
            $ini = parse_ini_file($ini_path, true);

            // nc2 の counter_block_id
            $nc2_counter_block_id = 0;
            if (array_key_exists('source_info', $ini) && array_key_exists('counter_block_id', $ini['source_info'])) {
                $nc2_counter_block_id = $ini['source_info']['counter_block_id'];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'counters')->where('source_key', $nc2_counter_block_id)->first();

            // マッピングテーブルを確認して、あれば削除
            if (!empty($mapping)) {
                // counter 取得。この情報から紐づけて、消すものを消してゆく。
                $counter = Counter::where('id', $mapping->destination_key)->first();
                // カウンターコンテンツ削除
                CounterCount::where('counter_id', $mapping->destination_key)->delete();
                if (!empty($counter)) {
                    // Buckets 削除
                    Buckets::where('id', $counter->bucket_id)->delete();
                    // カウンター削除
                    $counter->delete();
                }
                // マッピングテーブル削除
                $mapping->delete();
            }

            // Buckets テーブルと Counters テーブル、マッピングテーブルを追加
            $counter_name = '無題';
            $bucket = Buckets::create(['bucket_name' => $counter_name, 'plugin_name' => 'counters']);

            $counter = Counter::create([
                'bucket_id' => $bucket->id,
                'name' => $counter_name,
            ]);

            // カウントを作成する
            $counter_count = CounterCount::create([
                'counter_id' => $counter->id,
                'counted_at' => now()->format('Y-m-d'),
                'day_count' => intval($ini['counter_base']['counter_num']),
                'total_count' => intval($ini['counter_base']['counter_num']),
            ]);

            // マッピングテーブルの追加
            $mapping = MigrationMapping::create([
                'target_source_table'  => 'counters',
                'source_key'           => $nc2_counter_block_id,
                'destination_key'      => $counter->id,
            ]);
        }
    }

    /**
     * Connect-CMS 移行形式のカレンダーの予定をインポート
     */
    private function importCalendars($redo)
    {
        $this->putMonitor(3, "Calendars import start.");

        // データクリア
        if ($redo === true) {
            $this->clearData('calendars');
        }

        // カレンダー定義の取り込み
        $ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('calendars/calendar_room_*.ini'));

        // カレンダー定義のループ
        foreach ($ini_paths as $ini_path) {
            // ini_file の解析
            $ini = parse_ini_file($ini_path, true);

            // nc2 の calendar_room_id
            $nc2_calendar_room_id = 0;
            if (array_key_exists('source_info', $ini) && array_key_exists('room_id', $ini['source_info'])) {
                $nc2_calendar_room_id = $ini['source_info']['room_id'];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'calendars')->where('source_key', $nc2_calendar_room_id)->first();

            // マッピングテーブルを確認して、あれば削除
            if (!empty($mapping)) {
                // calendar 取得。この情報から紐づけて、消すものを消してゆく。
                $calendar = Calendar::where('id', $mapping->destination_key)->first();

                // カレンダー予定 削除
                CalendarPost::where('calendar_id', $mapping->destination_key)->delete();
                if (!empty($calendar)) {
                    // Buckets 削除
                    Buckets::where('id', $calendar->bucket_id)->delete();
                    // カレンダー削除
                    $calendar->delete();
                }
                // マッピングテーブル削除
                $mapping->delete();
            }

            // Buckets テーブルと Calendars テーブル、マッピングテーブルを追加
            $calendar_name = $ini['source_info']['room_name'];

            $bucket = Buckets::create(['bucket_name' => $calendar_name, 'plugin_name' => 'calendars']);

            $calendar = Calendar::create([
                'bucket_id' => $bucket->id,
                'name' => $calendar_name,
            ]);


            // Calendar のデータを取得（TSV） ※ iniとtsvが同じ名前の時、この処理でファイル名が取れる。
            $calendar_tsv_filename = str_replace('ini', 'tsv', basename($ini_path));

            if (Storage::exists($this->getImportPath('calendars/') . $calendar_tsv_filename)) {
                // TSV ファイル取得（1つのTSV で1つのデータベース丸ごと）
                $calendar_tsv = Storage::get($this->getImportPath('calendars/') . $calendar_tsv_filename);
                // POST が無いものは対象外
                if (empty($calendar_tsv)) {
                    continue;
                }

                // 行ループで使用する各種変数
                $header_skip = true;       // ヘッダースキップフラグ（1行目はカラム名の行）

                // NC2 calendar_plan
                $tsv_idxs['calendar_id'] = 0;
                $tsv_idxs['plan_id'] = 0;
                $tsv_idxs['room_id'] = 0;
                $tsv_idxs['user_id'] = 0;
                $tsv_idxs['user_name'] = 0;
                $tsv_idxs['title'] = 0;
                $tsv_idxs['title_icon'] = 0;
                $tsv_idxs['allday_flag'] = 0;
                $tsv_idxs['start_date'] = 0;
                $tsv_idxs['start_time'] = 0;
                $tsv_idxs['start_time_full'] = 0;
                $tsv_idxs['end_date'] = 0;
                $tsv_idxs['end_time'] = 0;
                $tsv_idxs['end_time_full'] = 0;
                $tsv_idxs['timezone_offset'] = 0;
                $tsv_idxs['link_module'] = 0;
                $tsv_idxs['link_id'] = 0;
                $tsv_idxs['link_action_name'] = 0;

                // NC2 calendar_plan_details
                // 場所
                $tsv_idxs['location'] = 0;
                // 連絡先
                $tsv_idxs['contact'] = 0;
                // 内容
                $tsv_idxs['description'] = 0;
                // 繰り返し条件
                $tsv_idxs['rrule'] = 0;

                // NC2 calendar_plan 登録日・更新日
                $tsv_idxs['insert_time'] = 0;
                $tsv_idxs['update_time'] = 0;

                // CC 状態
                $tsv_idxs['status'] = 0;


                // 改行で記事毎に分割（行の処理）
                $calendar_tsv_lines = explode("\n", $calendar_tsv);
                foreach ($calendar_tsv_lines as $calendar_tsv_line) {
                    // 1行目はカラム名の行のため、対象外
                    if ($header_skip) {
                        $header_skip = false;

                        // タブで項目に分割
                        $calendar_tsv_cols = explode("\t", trim($calendar_tsv_line, "\n\r"));

                        foreach ($calendar_tsv_cols as $loop_idx => $calendar_tsv_col) {
                            if (isset($tsv_idxs[$calendar_tsv_col])) {
                                $tsv_idxs[$calendar_tsv_col] = $loop_idx;
                            } else {
                                // dd($tsv_idxs, $calendar_tsv_cols);
                                $this->putError(3, 'インポートに必要なカラムなしエラー', "{$calendar_tsv_col}, ini_path={$ini_path}");
                            }
                        }
                        continue;
                    }
                    // 行データをタブで項目に分割
                    $calendar_tsv_cols = explode("\t", trim($calendar_tsv_line, "\n\r"));
                    if (!isset($calendar_tsv_cols[1])) {
                        // タブ区切りで１番目がセットされないのは、末尾空行と判断してスルーする。
                        // dd($calendar_tsv_cols);
                        continue;
                    }

                    // カレンダー予定の追加
                    $calendar_post = CalendarPost::create([
                        'calendar_id'      => $calendar->id,
                        'allday_flag'      => $calendar_tsv_cols[$tsv_idxs['allday_flag']],
                        'start_date'       => $calendar_tsv_cols[$tsv_idxs['start_date']],
                        'start_time'       => $calendar_tsv_cols[$tsv_idxs['start_time']],
                        'end_date'         => $calendar_tsv_cols[$tsv_idxs['end_date']],
                        'end_time'         => $calendar_tsv_cols[$tsv_idxs['end_time']],
                        'title'            => $calendar_tsv_cols[$tsv_idxs['title']],
                        'body'             => $this->changeWYSIWYG($calendar_tsv_cols[$tsv_idxs['description']]),
                        'status'           => $calendar_tsv_cols[$tsv_idxs['status']],
                        'created_at'       => $this->getDatetimeFromTsvAndCheckFormat($tsv_idxs['insert_time'], $calendar_tsv_cols, 'insert_time'),
                        'updated_at'       => $this->getDatetimeFromTsvAndCheckFormat($tsv_idxs['update_time'], $calendar_tsv_cols, 'update_time'),
                    ]);

                    // 記事のマッピングテーブルの追加
                    $mapping = MigrationMapping::create([
                        'target_source_table'  => 'calendars_post',
                        'source_key'           => $calendar_tsv_cols[$tsv_idxs['calendar_id']],
                        'destination_key'      => $calendar_post->id,
                    ]);

                }
            }

            if (CalendarPost::where('calendar_id', $calendar->id)->count() == 0) {
                // カレンダー予定の移行なし

                $this->putError(3, 'カレンダー予定なし', "カレンダー名={$calendar->name}, ini_path={$ini_path}");

                // 予定空のカレンダーは移行せず、ログ出力する。
                Calendar::destroy($calendar->id);

            } else {
                // カレンダー予定の移行あり

                // マッピングテーブルの追加
                $mapping = MigrationMapping::create([
                    'target_source_table'  => 'calendars',
                    'source_key'           => $nc2_calendar_room_id,
                    'destination_key'      => $calendar->id,
                ]);
            }

        }
    }

    /**
     * Connect-CMS 移行形式のスライダーをインポート
     */
    private function importSlideshows($redo)
    {
        $this->putMonitor(3, "Slideshows import start.");

        // データクリア
        if ($redo === true) {
            $this->clearData('slideshows');
        }

        // 定義の取り込み
        $ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('slideshows/slideshows_*.ini'));
        // 定義のループ
        foreach ($ini_paths as $ini_path) {
            // ini_file の解析
            $ini = parse_ini_file($ini_path, true);

            // nc2 の block_id
            $nc2_slideshows_block_id = 0;
            if (array_key_exists('source_info', $ini) && array_key_exists('slideshows_block_id', $ini['source_info'])) {
                $nc2_slideshows_block_id = $ini['source_info']['slideshows_block_id'];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'slideshows')->where('source_key', $nc2_slideshows_block_id)->first();

            // マッピングテーブルを確認して、あれば削除
            if (!empty($mapping)) {
                // slideshows 取得。この情報から紐づけて、消すものを消してゆく。
                $slideshows = slideshows::where('id', $mapping->destination_key)->first();
                // コンテンツ削除
                slideshowsItems::where('slideshows_id', $mapping->destination_key)->delete();
                if (!empty($slideshows)) {
                    // Buckets 削除
                    Buckets::where('id', $slideshows->bucket_id)->delete();
                    // 削除
                    $slideshows->delete();
                }
                // マッピングテーブル削除
                $mapping->delete();
            }
            // Buckets テーブルと slideshows テーブル、マッピングテーブルを追加
            $slideshows_name = '無題';
            $bucket = Buckets::create(['bucket_name' => $slideshows_name, 'plugin_name' => 'slideshows']);
            $control_display_flag = 1;
            $indicators_display_flag = 1;
            $fade_use_flag = 1;
            $image_interval = 3000;
            $slideshows = Slideshows::create([
                'bucket_id' => $bucket->id,
                'slideshows_name' => $slideshows_name,
                'control_display_flag' => $control_display_flag,
                'indicators_display_flag' => $indicators_display_flag,
                'fade_use_flag' => $fade_use_flag,
                'image_interval' => $image_interval,
            ]);

            // スライダーのデータを取得（TSV）
            $slideshows_tsv_filename = str_replace('ini', 'tsv', basename($ini_path));
            if (Storage::exists($this->getImportPath('slideshows/') . $slideshows_tsv_filename)) {
                // TSV ファイル取得（1つのTSV で1つのスライダー）
                $slideshows_tsv = Storage::get($this->getImportPath('slideshows/') . $slideshows_tsv_filename);
                // 無いものは対象外
                if (empty($slideshows_tsv)) {
                    continue;
                }
                // 改行で記事毎に分割
                $slideshows_tsv_lines = explode("\n", $slideshows_tsv);
                foreach ($slideshows_tsv_lines as $slideshows_tsv_line) {
                    // タブで項目に分割
                    $slideshows_tsv_cols = explode("\t", $slideshows_tsv_line);
                    // image_path{\t}uploads_id{\t}link_url{\t}link_target{\t}caption{\t}display_flag{\t}display_sequence
                    $image_path = isset($slideshows_tsv_cols[0]) ? $slideshows_tsv_cols[0] : null;
                    $source_key = isset($slideshows_tsv_cols[1]) ? $slideshows_tsv_cols[1] : null;
                    $link_url = isset($slideshows_tsv_cols[2]) ? $slideshows_tsv_cols[2] : null;
                    $link_target = isset($slideshows_tsv_cols[3]) ? $slideshows_tsv_cols[3] : null;
                    $caption = isset($slideshows_tsv_cols[4]) ? $slideshows_tsv_cols[4] : null;
                    $display_flag = isset($slideshows_tsv_cols[5]) ? $slideshows_tsv_cols[5] : null;
                    $display_sequence = isset($slideshows_tsv_cols[6]) ? $slideshows_tsv_cols[6] : null;

                    /* uploads_idの取得 */
                    $uploads_id = null;
                    if ($source_key) {
                        $upload_mapping = MigrationMapping::where('target_source_table', 'uploads')->where('source_key', $source_key)->first();
                        $uploads_id = $upload_mapping ? $upload_mapping->destination_key : null;
                        if ($uploads_id) {
                            $upload = Uploads::find($uploads_id);
                            if (empty($upload)) {
                                $this->putMonitor(1, "No target = uploads", "uploads_id = " . $uploads_id);
                            } else {
                                // 100000
                                $dir_no = 0;
                                foreach (range(0, 1000000, 1000) as $number) {
                                    $dir_no++;
                                    if ($uploads_id <= $number) {
                                        break;
                                    }
                                }
                                $image_path = 'uploads/'.$dir_no.'/'.$uploads_id.'.'.$upload->extension;
                            }
                        }
                    }


                    // 付与テーブルデータを作成する
                    $slideshows_count = SlideshowsItems::create([
                        'slideshows_id' => $slideshows->id,
                        'image_path' => $image_path,
                        'uploads_id' => $uploads_id,
                        'link_url' => $link_url,
                        'link_target' => $link_target,
                        'caption' => $caption,
                        'display_flag' => $display_flag,
                        'display_sequence' => $display_sequence,
                    ]);
                }
            }
            // マッピングテーブルの追加
            $mapping = MigrationMapping::create([
                'target_source_table'  => 'slideshows',
                'source_key'           => $nc2_slideshows_block_id,
                'destination_key'      => $slideshows->id,
            ]);
        }
    }

    /**
     * Connect-CMS 移行形式のシンプル動画をインポート
     */
    private function importSimplemovie($redo)
    {
        $this->putMonitor(3, "Simplemovie import start.");

        // データクリア
        if ($redo === true) {
            $this->clearData('simplemovie');
        }
        // 定義の取り込み
        $ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('simplemovie/simplemovie_*.ini'));
        // 定義のループ
        foreach ($ini_paths as $ini_path) {
            // ini_file の解析
            $ini = parse_ini_file($ini_path, true);

            // nc2 の block_id
            $nc2_simplemovie_block_id = 0;
            if (array_key_exists('source_info', $ini) && array_key_exists('simplemovie_block_id', $ini['source_info'])) {
                $nc2_simplemovie_block_id = $ini['source_info']['simplemovie_block_id'];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'simplemovie')->where('source_key', $nc2_simplemovie_block_id)->first();

            // マッピングテーブルを確認して、あれば削除
            if (!empty($mapping)) {
                /*

                // TODO 固定記事のデータを参照して消込のはず

                // simplemovie 取得。この情報から紐づけて、消すものを消してゆく。
                $simplemovie = simplemovie::where('id', $mapping->destination_key)->first();
                if (!empty($simplemovie)) {
                    // Buckets 削除
                    Buckets::where('id', $simplemovie->bucket_id)->delete();
                    // 削除
                    $simplemovie->delete();
                }
                */

                // マッピングテーブル削除
                $mapping->delete();

            }
            // Buckets テーブルと simplemovie テーブル、マッピングテーブルを追加
            $simplemovie_name = 'simplemovie';//固定記事に移行するのでバケツ名はsimplemovieにする
            $bucket = Buckets::create(['bucket_name' => $simplemovie_name, 'plugin_name' => 'contents']);

            /* uploads_idの取得 */
            $source_upload_ids = [
                        'movie' => $ini['simplemovie_base']['simplemovie_movie_upload_id'],
                        'thumb' => $ini['simplemovie_base']['simplemovie_thumbnail_upload_id'],
            ];
            $uploads_ids = [];
            foreach ($source_upload_ids as $key => $source_key) {
                if ($source_key) {
                    $upload_mapping = MigrationMapping::where('target_source_table', 'uploads')->where('source_key', $source_key)->first();
                    $uploads_id = $upload_mapping ? $upload_mapping->destination_key : null;
                    if ($uploads_id) {
                        $uploads_ids[$key] = $uploads_id;
                    }
                }
            }
            // 動画のHTMLを作成
            $movie_file_path = '/file/'. $uploads_ids['movie'];
            $thumb_file_path = '/file/'. $uploads_ids['thumb'];
            $content_html = "
                <video class=\"simplemovie\" controls=\"\" preload=\"none\" controlslist=\"nodownload\" style=\"max-width: 100%; height: auto;\" poster=\"$thumb_file_path\" >
                    <source src=\"$movie_file_path\" type=\"video/mp4\">
                </video>
            ";
            // 固定記事として登録
            $content = Contents::create(['bucket_id' => $bucket->id,
                                        'content_text' => $content_html,
                                        'status' => 0]);

            // マッピングテーブルの追加
            $mapping = MigrationMapping::create([
                'target_source_table'  => 'simplemovie',
                'source_key'           => $nc2_simplemovie_block_id,
                'destination_key'      => $content->id, //固定記事のID
            ]);
        }
    }

    /**
     * Connect-CMS 移行形式の施設予約の予定をインポート
     */
    private function importReservations($redo)
    {
        $this->putMonitor(3, "Reservations import start.");

        // データクリア
        if ($redo === true) {
            $this->clearData('reservations');
        }

        // 施設カテゴリの取り込み
        // ------------------------------------------
        // ReservationsCategory のコレクションを保持。後で入力データを移行する際に nc2_category_id でひっぱるため。
        $create_reservation_categories = collect();

        $ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('reservations/reservation_category_*.ini'));
        foreach ($ini_paths as $ini_path) {
            // ini_file の解析
            $ini = parse_ini_file($ini_path, true);

            // nc2 の category_id
            $nc2_category_id = 0;
            if (array_key_exists('source_info', $ini) && array_key_exists('category_id', $ini['source_info'])) {
                $nc2_category_id = $ini['source_info']['category_id'];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'reservations_category')->where('source_key', $nc2_category_id)->first();

            // マッピングテーブルを確認して、あれば削除
            if (!empty($mapping)) {
                // 施設カテゴリ削除
                ReservationsCategory::where('id', $mapping->destination_key)->forceDelete();

                // マッピングテーブル削除
                $mapping->delete();
            }

            // カテゴリなし(id=1)以外を移行
            if ($ini['source_info']['category_id'] == 1) {
                $reservation_categories = ReservationsCategory::find(1);
                $this->putMonitor(3, '施設予約のカテゴリなしは移行しない', "施設カテゴリ名={$ini['reservation_category']['category_name']}, ini_path={$ini_path}");

            } else {
                $reservation_categories = ReservationsCategory::create([
                    'category' => $ini['reservation_category']['category_name'],
                    'display_sequence' => $ini['reservation_category']['display_sequence'],
                ]);
            }

            $reservation_categories->nc2_category_id = $ini['source_info']['category_id'];
            // コレクションに要素追加
            $create_reservation_categories = $create_reservation_categories->concat([$reservation_categories]);

            if ($ini['source_info']['category_id'] != 1) {
                // マッピングテーブルの追加
                $mapping = MigrationMapping::create([
                    'target_source_table'  => 'reservations_category',
                    'source_key'           => $ini['source_info']['category_id'],
                    'destination_key'      => $reservation_categories->id,
                ]);
            }
        }

        // 項目セットの移行初期設定
        // ------------------------------------------
        $columns_set_basic = ReservationsColumnsSet::find(1);

        if (ReservationsColumn::whereIn('column_name', ['連絡先', '補足'])->count() == 0) {
            // 項目設定にセット
            // 連絡先
            $column = ReservationsColumn::create([
                'columns_set_id'   => $columns_set_basic->id,
                'column_type'      => ReservationColumnType::text,
                'column_name'      => '連絡先',
                'required'         => Required::off,
                'hide_flag'        => NotShowType::show,
                'title_flag'       => 0,
                'display_sequence' => 4,
            ]);

            // 補足
            $column = ReservationsColumn::create([
                'columns_set_id'   => $columns_set_basic->id,
                'column_type'      => ReservationColumnType::wysiwyg,
                'column_name'      => '補足',
                'required'         => Required::off,
                'hide_flag'        => NotShowType::show,
                'title_flag'       => 0,
                'display_sequence' => 5,
            ]);
        }

        // 基本カラム取得
        $columns = ReservationsColumn::where('columns_set_id', $columns_set_basic->id)->get();
        // ユーザ取得
        $users = User::get();

        // 施設の取り込み
        // ------------------------------------------
        $ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('reservations/reservation_location_*.ini'));
        foreach ($ini_paths as $ini_path) {
            // ini_file の解析
            $ini = parse_ini_file($ini_path, true);

            // nc2 の nc2_location_id
            $nc2_location_id = 0;
            if (array_key_exists('source_info', $ini) && array_key_exists('location_id', $ini['source_info'])) {
                $nc2_location_id = $ini['source_info']['location_id'];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'reservations_location')->where('source_key', $nc2_location_id)->first();

            // マッピングテーブルを確認して、あれば削除
            if (!empty($mapping)) {
                // 施設削除
                ReservationsFacility::where('id', $mapping->destination_key)->forceDelete();

                $inputs = ReservationsInput::where('facility_id', $mapping->destination_key)->get();

                // 予約カラム値 削除（入力親ID単位で削除）
                ReservationsInputsColumn::whereIn('inputs_parent_id', $inputs->pluck('inputs_parent_id'))->delete();

                // 予約削除（id単位で削除）
                ReservationsInput::destroy($inputs->pluck('id'));

                // 繰り返しルール削除（施設ID単位で削除）
                InputsRepeat::where('target', 'reservations')->where('target_id', $mapping->destination_key)->delete();

                // マッピングテーブル削除
                $mapping->delete();
            }

            // 対象カテゴリ
            $create_reservation_category = $create_reservation_categories->firstWhere('nc2_category_id', $ini['reservation_location']['category_id']);

            // 施設の登録処理
            $reservations_facility = ReservationsFacility::create([
                'facility_name' => $ini['reservation_location']['location_name'],
                'is_time_control' => $ini['reservation_location']['is_time_control'] ? 1 : 0,
                'start_time' => $ini['reservation_location']['start_time'],
                'end_time' => $ini['reservation_location']['end_time'],
                'day_of_weeks' => $ini['reservation_location']['day_of_weeks'],
                'hide_flag' => NotShowType::show,
                'is_allow_duplicate' => PermissionType::not_allowed,
                'is_limited_by_role' => $ini['reservation_location']['is_limited_by_role'] ? ReservationLimitedByRole::limited : ReservationLimitedByRole::not_limited,
                'facility_manager_name' => $ini['reservation_location']['facility_manager_name'],
                'supplement' => $ini['reservation_location']['supplement'],
                'reservations_categories_id' => $create_reservation_category->id,
                'columns_set_id' => $columns_set_basic->id,
                'display_sequence' => $ini['reservation_location']['display_sequence'],
            ]);

            // １つ前の予約ID
            $before_nc2_reserve_details_id = null;
            // １つ前の親ID
            $before_inputs_parent_id = null;

            // Calendar のデータを取得（TSV） ※ iniとtsvが同じ名前の時、この処理でファイル名が取れる。
            $reservation_tsv_filename = str_replace('ini', 'tsv', basename($ini_path));

            if (Storage::exists($this->getImportPath('reservations/') . $reservation_tsv_filename)) {
                // TSV ファイル取得（1つのTSV で1つのデータベース丸ごと）
                $reservation_tsv = Storage::get($this->getImportPath('reservations/') . $reservation_tsv_filename);
                // POST が無いものは対象外
                if (empty($reservation_tsv)) {
                    continue;
                }

                // 行ループで使用する各種変数
                $header_skip = true;       // ヘッダースキップフラグ（1行目はカラム名の行）

                // NC2 reservation_reserve
                $tsv_idxs['reserve_id'] = 0;
                $tsv_idxs['reserve_details_id'] = 0;
                $tsv_idxs['location_id'] = 0;
                $tsv_idxs['room_id'] = 0;
                $tsv_idxs['user_id'] = 0;
                $tsv_idxs['user_name'] = 0;
                $tsv_idxs['calendar_id'] = 0;
                $tsv_idxs['title'] = 0;
                $tsv_idxs['title_icon'] = 0;
                $tsv_idxs['allday_flag'] = 0;
                $tsv_idxs['start_date'] = 0;
                $tsv_idxs['start_time'] = 0;
                $tsv_idxs['start_time_full'] = 0;
                $tsv_idxs['end_date'] = 0;
                $tsv_idxs['end_time'] = 0;
                $tsv_idxs['end_time_full'] = 0;
                $tsv_idxs['timezone_offset'] = 0;

                // NC2 reservation_reserve_details
                // 連絡先
                $tsv_idxs['contact'] = 0;
                // 内容
                $tsv_idxs['description'] = 0;
                // 繰り返し条件
                $tsv_idxs['rrule'] = 0;

                // NC2 reservation_reserve システム項目
                $tsv_idxs['insert_time']  = 0;
                $tsv_idxs['insert_user_name']  = 0;
                $tsv_idxs['insert_login_id']  = 0;
                $tsv_idxs['update_time']  = 0;
                $tsv_idxs['update_user_name']  = 0;
                $tsv_idxs['update_login_id']  = 0;

                // CC 状態
                $tsv_idxs['status'] = 0;


                // 改行で記事毎に分割（行の処理）
                $reservation_tsv_lines = explode("\n", $reservation_tsv);
                foreach ($reservation_tsv_lines as $reservation_tsv_line) {
                    // 1行目はカラム名の行のため、対象外
                    if ($header_skip) {
                        $header_skip = false;

                        // タブで項目に分割
                        $reservation_tsv_cols = explode("\t", trim($reservation_tsv_line, "\n\r"));

                        foreach ($reservation_tsv_cols as $loop_idx => $reservation_tsv_col) {
                            if (isset($tsv_idxs[$reservation_tsv_col])) {
                                $tsv_idxs[$reservation_tsv_col] = $loop_idx;
                            } else {
                                $this->putError(3, 'インポートに必要なカラムなしエラー', "{$reservation_tsv_col}, ini_path={$ini_path}");
                            }
                        }
                        continue;
                    }
                    // 行データをタブで項目に分割
                    $reservation_tsv_cols = explode("\t", trim($reservation_tsv_line, "\n\r"));
                    if (!isset($reservation_tsv_cols[1])) {
                        // タブ区切りで１番目がセットされないのは、末尾空行と判断してスルーする。
                        continue;
                    }

                    // 施設予約の予約追加
                    $reservation_post = new ReservationsInput([
                        'facility_id'      => $reservations_facility->id,
                        'allday_flag'      => $reservation_tsv_cols[$tsv_idxs['allday_flag']],
                        'start_datetime'   => $reservation_tsv_cols[$tsv_idxs['start_time_full']],
                        'end_datetime'     => $reservation_tsv_cols[$tsv_idxs['end_time_full']],
                        'first_committed_at' => $this->getDatetimeFromTsvAndCheckFormat($tsv_idxs['insert_time'], $reservation_tsv_cols, 'insert_time'),
                        'status'           => $reservation_tsv_cols[$tsv_idxs['status']],
                    ]);
                    $reservation_post->created_id = $this->getUserIdFromLoginId($users, $reservation_tsv_cols[$tsv_idxs['insert_login_id']]);
                    $reservation_post->created_name = $reservation_tsv_cols[$tsv_idxs['insert_user_name']];
                    $reservation_post->created_at = $this->getDatetimeFromTsvAndCheckFormat($tsv_idxs['insert_time'], $reservation_tsv_cols, 'insert_time');
                    $reservation_post->updated_id = $this->getUserIdFromLoginId($users, $reservation_tsv_cols[$tsv_idxs['update_login_id']]);
                    $reservation_post->updated_name = $reservation_tsv_cols[$tsv_idxs['update_user_name']];
                    $reservation_post->updated_at = $this->getDatetimeFromTsvAndCheckFormat($tsv_idxs['update_time'], $reservation_tsv_cols, 'update_time');
                    // 登録更新日時を自動更新しない
                    $reservation_post->timestamps = false;
                    $reservation_post->save();

                    if ($before_nc2_reserve_details_id != $reservation_tsv_cols[$tsv_idxs['reserve_details_id']]) {
                        // １つ前のreserve_details_id と違えば登録

                        // 親IDセット
                        $before_inputs_parent_id = $reservation_post->id;
                        $reservation_post->inputs_parent_id = $reservation_post->id;
                        $reservation_post->save();

                        $column_title = $columns->firstWhere('column_name', '件名');
                        $column_contact = $columns->firstWhere('column_name', '連絡先');
                        $column_description = $columns->firstWhere('column_name', '補足');

                        // bulk insert
                        $reservations_inputs_columns = ReservationsInputsColumn::insert(
                            // 件名
                            [
                                'inputs_parent_id' => $reservation_post->inputs_parent_id,
                                'column_id' => $column_title->id,
                                'value' => $reservation_tsv_cols[$tsv_idxs['title']],
                            ],
                            // 連絡先
                            [
                                'inputs_parent_id' => $reservation_post->inputs_parent_id,
                                'column_id' => $column_contact->id,
                                'value' => $reservation_tsv_cols[$tsv_idxs['contact']],
                            ],
                            // 補足
                            [
                                'inputs_parent_id' => $reservation_post->inputs_parent_id,
                                'column_id' => $column_description->id,
                                'value' => $this->changeWYSIWYG($reservation_tsv_cols[$tsv_idxs['description']]),
                            ],
                        );

                        // rruleあれば登録
                        $rrule_setting = $reservation_tsv_cols[$tsv_idxs['rrule']];
                        if ($rrule_setting) {
                            $inputs_repeat = InputsRepeat::firstOrNew([
                                'target' => 'reservations',
                                'target_id' => $reservation_post->facility_id,   // 施設予約は、施設IDをtarget_idにセット
                                'parent_id' => $reservation_post->inputs_parent_id
                            ]);

                            // 開始日
                            // ※ [要注意] NC2の reservation_reserve_details.rrule は DTSTART がないため、移行時は開始日の補完が必要。
                            //            DTSTART無指定だと、今日日付で処理される。
                            $dtstart = RRule::parseDate($reservation_tsv_cols[$tsv_idxs['start_time_full']]);

                            // copy from RRule::rfcString()
                            // - 週の開始曜日 WKST=SU
                            $rrule_setting = sprintf(
                                "DTSTART:%s\nRRULE:%s;WKST=SU",
                                $dtstart->format('Ymd\THis'),
                                $rrule_setting
                            );

                            $rrule = new RRule($rrule_setting);
                            $inputs_repeat->rrule = $rrule->rfcString(false);
                            $inputs_repeat->save();
                        }
                    } else {
                        // １つ前のreserve_details_id と同じなら、１つ前の親ID登録

                        // 親IDセット
                        $reservation_post->inputs_parent_id = $before_inputs_parent_id;
                        $reservation_post->save();
                    }

                    $before_nc2_reserve_details_id = $reservation_tsv_cols[$tsv_idxs['reserve_details_id']];

                    // 記事のマッピングテーブルの追加
                    $mapping = MigrationMapping::create([
                        'target_source_table'  => 'reservations_post',
                        'source_key'           => $reservation_tsv_cols[$tsv_idxs['reserve_id']],
                        'destination_key'      => $reservation_post->id,
                    ]);

                }
            }

            if (ReservationsInput::where('facility_id', $reservations_facility->id)->count() == 0) {
                // 施設予約の予約の移行なし
                $this->putError(3, '施設予約の予約なし', "施設名={$reservations_facility->facility_name}, ini_path={$ini_path}");
            }

            // マッピングテーブルの追加
            $mapping = MigrationMapping::create([
                'target_source_table'  => 'reservations_location',
                'source_key'           => $ini['source_info']['location_id'],
                'destination_key'      => $reservations_facility->id,
            ]);
        }

        // バケツ移行
        // --------------------------------------------
        // ブロック単位でバケツ作成

        // 施設ブロックの取り込み
        $ini_paths = File::glob(storage_path() . '/app/' . $this->getImportPath('reservations/reservation_block_*.ini'));
        foreach ($ini_paths as $ini_path) {
            // ini_file の解析
            $ini = parse_ini_file($ini_path, true);

            // nc2 の reservation_block_id
            $nc2_reservation_block_id = 0;
            if (array_key_exists('source_info', $ini) && array_key_exists('reservation_block_id', $ini['source_info'])) {
                $nc2_reservation_block_id = $ini['source_info']['reservation_block_id'];
            }

            // マッピングテーブルの取得
            $mapping = MigrationMapping::where('target_source_table', 'reservations_block')->where('source_key', $nc2_reservation_block_id)->first();

            // マッピングテーブルを確認して、あれば削除
            if (!empty($mapping)) {
                $delete_reservation = Reservation::where('id', $mapping->destination_key)->first();
                Buckets::destory($delete_reservation->bucket_id);
                $delete_reservation->destory();

                // マッピングテーブル削除
                $mapping->delete();
            }

            // Buckets テーブルと Reservations テーブル、マッピングテーブルを追加
            $reservation_name = $ini['reservation_block']['reservation_name'];

            $bucket = Buckets::create(['bucket_name' => $reservation_name, 'plugin_name' => 'reservations']);

            $reservation = Reservation::create([
                'bucket_id' => $bucket->id,
                'reservation_name' => $reservation_name,
            ]);

            // マッピングテーブルの追加
            $mapping = MigrationMapping::create([
                'target_source_table'  => 'reservations_block',
                'source_key'           => $nc2_reservation_block_id,
                'destination_key'      => $reservation->id,
            ]);
        }
    }

    /**
     * シーダーの呼び出し
     */
    //private function importSeeder($redo)
    //{
    //    $this->putMonitor(3, "seeder import Start.");
    //
    //    // メニューの処理
    //    $this->importSeederMenu($redo);
    //}

    /**
     * シーダー（メニュー）の呼び出し
     */
    //private function importSeederMenu($redo)
    //{
    //    // メニュー追加ファイル読み込み
    //    $frame_ini_paths = File::glob(storage_path() . '/app/migration/@addition/menus/menu*.ini');
    //
    //    foreach ($frame_ini_paths as $frame_ini_path) {
    //        // フレーム毎のini_file の解析
    //        $frame_ini = parse_ini_file($frame_ini_path, true);
    //
    //        // redo でマッピングがあれば削除
    //        if (!empty($this->getArrayValue($frame_ini, 'addition', 'source_key')) && !empty($this->getArrayValue($frame_ini, 'frame_base', 'plugin_name'))) {
    //            // plugin のマップを削除
    //            MigrationMapping::where('target_source_table', $this->getArrayValue($frame_ini, 'frame_base', 'plugin_name'))
    //                            ->where('source_key', $this->getArrayValue($frame_ini, 'addition', 'source_key'))
    //                            ->delete();
    //            // frame のマップを削除
    //            MigrationMapping::where('target_source_table', 'frames')
    //                            ->where('source_key', $this->getArrayValue($frame_ini, 'addition', 'source_key'))
    //                            ->delete();
    //        }
    //
    //        // ページ
    //        $page_id = $this->getArrayValue($frame_ini, 'frame_base', 'page_id');
    //        if (empty($page_id)) {
    //            $this->putError(3, 'メニューの追加でpage_id なし', "frame_ini_path = " . $frame_ini_path);
    //        } elseif ($page_id == '{top_page_id}') {
    //            $page = Page::where('permanent_link', '/')->first();
    //        } else {
    //            $page = Page::get('id', $page_id)->first();
    //        }
    //
    //        // その他パラメータ
    //        $page_dir = null;
    //        $display_sequence = $this->getArrayValue($frame_ini, 'frame_base', 'display_sequence');
    //        $this->importPluginMenus($page, $page_dir, $frame_ini, $display_sequence);
    //    }
    //}

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
            $change_list = array_merge($change_list, $images);
        }
        if (is_array($anchors)) {
            $change_list = array_merge($change_list, $anchors);
        }

        // 対象がなければ戻る
        if (empty($change_list)) {
            return $content;
        }

        // アップロードファイルのパスへ変換
        foreach ($change_list as $image_path) {
            if (strpos($image_path, '../../uploads') === 0) {
                $img_filename = str_replace('../../uploads/', '', $image_path);
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
        $this->importHtmlImpl($page, $dir);
    }

    /**
     * frame_9999.ini を読んで解析する。上書き設定も反映する。
     */
    private function parseIni($ini_path)
    {
        // フレーム毎のini_file の解析
        $ini = parse_ini_file($ini_path, true);

        // 上書き用ファイル
        $overwrite_ini_path = str_replace('import', '@update', $ini_path);
        $overwrite_ini_path = str_replace('@insert', '@update', $overwrite_ini_path);

        // NC2 用の上書き設定があるか確認
        //$source_key = $this->getArrayValue($ini, 'source_info', 'source_key');
        //if (empty($source_key)) {
        //    return $ini;
        //}
        //$nc2_block_overwrite_path = 'migration/@addition/nc2_blocks/block_overwrite_' . $source_key . '.ini';
        //if (Storage::exists($nc2_block_overwrite_path)) {
        //    $overwrite_ini = parse_ini_file(storage_path() . '/app/' . $nc2_block_overwrite_path, true);
        $overwrite_ini_laravel_path = substr($overwrite_ini_path, strpos($overwrite_ini_path, 'migration/'));

        // frame.ini のオーバーライドでは、初回の場合も追加の場合も、@update を見てよい。
        // if ($this->added == false && Storage::exists($overwrite_ini_laravel_path)) {
        if (Storage::exists($overwrite_ini_laravel_path)) {
            $overwrite_ini = parse_ini_file($overwrite_ini_path, true);
            $marge_ini = array();
            // 第1階層のsection があるか確認して、あれば第2階層をマージ。
            // array_merge_recursive だと、勝手に階層が変わったりするので、
            foreach ($ini as $section_key => $ini_section) {
                if (array_key_exists($section_key, $overwrite_ini)) {
                    $marge_ini[$section_key] = array_merge($ini_section, $overwrite_ini[$section_key]);
                } else {
                    $marge_ini[$section_key] = $ini_section;
                }
            }
            return $marge_ini;
        }
        // overwrite ファイルがないので、元のまま。
        return $ini;
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
            //$frame_ini = parse_ini_file($frame_ini_path, true);
            $frame_ini = $this->parseIni($frame_ini_path);
            //print_r($frame_ini);

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
            if ($frame_ini["source_info"]["target_source_table"] == 'simplemovie') {
                // シンプル動画（固定記事登録）
                $this->importPluginSimplemovie($page, $page_dir, $frame_ini, $display_sequence);
            } else {
                // 固定記事（お知らせ）
                $this->importPluginContents($page, $page_dir, $frame_ini, $display_sequence);
            }
        } elseif ($plugin_name == 'menus') {
            // メニュー
            $this->importPluginMenus($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'blogs') {
            // ブログ
            $this->importPluginBlogs($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'faqs') {
            // FAQ
            $this->importPluginFaqs($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'linklists') {
            // リンクリスト
            $this->importPluginLinklists($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'databases') {
            // データベース
            $this->importPluginDatabases($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'forms') {
            // フォーム
            $this->importPluginForms($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'whatsnews') {
            // 新着情報
            $this->importPluginWhatsnews($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'cabinets') {
            // キャビネット
            $this->importPluginCabinets($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'bbses') {
            // BBS
            $this->importPluginBbses($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'counters') {
            // カウンター
            $this->importPluginCounters($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'calendars') {
            // カレンダー
            $this->importPluginCalendars($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'slideshows') {
            // スライダー
            $this->importPluginSlideshows($page, $page_dir, $frame_ini, $display_sequence);
        } elseif ($plugin_name == 'reservations') {
            // 施設予約
            $this->importPluginReservations($page, $page_dir, $frame_ini, $display_sequence);
        }
    }

    /**
     * メニュープラグインの登録処理
     */
    private function importPluginMenus($page, $page_dir, $frame_ini, $display_sequence)
    {
        //// 追加じゃない場合は、migration_config のメニューのインポート条件を見る。
        //if (!array_key_exists('addition', $frame_ini)) {
        // addition はやめた

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
        //}

        // migration_mapping 確認
        // if (array_key_exists('addition', $frame_ini)) {
        //     $source_key = $this->getArrayValue($frame_ini, 'addition', 'source_key');
        // } else {
        //     $source_key = $this->getArrayValue($frame_ini, 'source_info', 'source_key');
        // }

        // migration_mapping 確認
        $source_key = $this->getArrayValue($frame_ini, 'source_info', 'source_key');
        $migration_mappings = MigrationMapping::where('target_source_table', 'menus')->where('source_key', $source_key)->first();

        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence);

        // NC2 からの移行時の非表示設定の反映
        $ommit_page_ids_nc2 = $this->getArrayValue($frame_ini, 'menu', 'ommit_page_ids_nc2');
        $ommit_page_ids = array();
        if (!empty($ommit_page_ids_nc2)) {
            foreach (explode(",", $ommit_page_ids_nc2) as $ommit_page_id_nc2) {
                $nc2_page = MigrationMapping::where('target_source_table', 'nc2_pages')
                                            ->where('source_key', $ommit_page_id_nc2)
                                            ->first();
                if (!empty($nc2_page)) {
                    $connect_page = MigrationMapping::where('target_source_table', 'connect_page')
                                                    ->where('source_key', $nc2_page->destination_key)
                                                    ->first();
                }
                if (!empty($connect_page)) {
                    $ommit_page_ids[] = $connect_page->destination_key;
                }
            }
        }

        // 全ての新ページID を取得して、ommit 分を省く
        $all_page_ids = Page::orderBy('_lft', 'asc')->get()->pluck('id')->all();
        $view_page_ids = array_diff($all_page_ids, $ommit_page_ids);
        $view_page_ids_str = implode(",", $view_page_ids);

        // Menus 登録 or 更新
        $menus = Menu::updateOrCreate(
            ['frame_id' => $frame->id],
            ['frame_id'         => $frame->id,
            'select_flag'       => $this->getArrayValue($frame_ini, 'menu', 'select_flag'),
            'page_ids'          => $view_page_ids_str,
            'folder_close_font' => intval($this->getArrayValue($frame_ini, 'menu', 'folder_close_font')),
            'folder_open_font'  => intval($this->getArrayValue($frame_ini, 'menu', 'folder_open_font')),
            'indent_font'       => intval($this->getArrayValue($frame_ini, 'menu', 'indent_font'))]
        );

        // マップ 登録 or 更新
        $mapping = MigrationMapping::updateOrCreate(
            ['target_source_table' => 'menus', 'source_key' => $source_key],
            ['target_source_table' => 'menus',
             'source_key'          => $source_key,
             'destination_key'     => $menus->id]
        );
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
        if (!empty($blog_id) && Storage::exists($this->getImportPath('blogs/blog_') . $blog_id . '.ini')) {
            $blog_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('blogs/blog_') . $blog_id . '.ini', true);
        }
        // NC2 のjournal_id
        if (!empty($blog_ini) && array_key_exists('source_info', $blog_ini) && array_key_exists('journal_id', $blog_ini['source_info'])) {
            $journal_id = $blog_ini['source_info']['journal_id'];
        }
        // NC2 のjournal_id でマップ確認
        if (!empty($blog_ini) && array_key_exists('source_info', $blog_ini) && array_key_exists('journal_id', $blog_ini['source_info'])) {
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
        if (empty($bucket)) {
            $this->putError(1, 'Blog フレームのみで実体なし', "page_dir = " . $page_dir);
        }

        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);

        // frame_configs 登録
        if (!empty($blogs)) {
            // 表示件数
            $view_count = 10;
            if (array_key_exists('blog_base', $blog_ini) && array_key_exists('view_count', $blog_ini['blog_base'])) {
                $view_count = $blog_ini['blog_base']['view_count'];
                // view_count が 0 を含む空の場合は、初期値にする。（NC2 で0 で全件表示されているものがあるので、その対応）
                if (empty($view_count)) {
                    $view_count = 10;
                }
            }

            $frame_config = FrameConfig::updateOrCreate(
                ['frame_id' => $frame->id, 'name' => BlogFrameConfig::blog_view_count],
                ['value' => $view_count]
            );
        }
    }

    /**
     * FAQプラグインの登録処理
     */
    private function importPluginFaqs($page, $page_dir, $frame_ini, $display_sequence)
    {
        // 変数定義
        $faq_id = null;
        $faq_ini = null;
        $nc2_faq_id = null;
        $migration_mappings = null;
        $faqs = null;
        $bucket = null;

        // エクスポートファイルの faq_id 取得（エクスポート時の連番）
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('faq_id', $frame_ini['frame_base'])) {
            $faq_id = $frame_ini['frame_base']['faq_id'];
        }
        // ブログの情報取得
        if (!empty($faq_id) && Storage::exists($this->getImportPath('faqs/faq_') . $faq_id . '.ini')) {
            $faq_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('faqs/faq_') . $faq_id . '.ini', true);
        }
        // NC2 のfaq_id
        if (!empty($faq_ini) && array_key_exists('source_info', $faq_ini) && array_key_exists('faq_id', $faq_ini['source_info'])) {
            $nc2_faq_id = $faq_ini['source_info']['faq_id'];
        }
        // NC2 のfaq_id でマップ確認
        if (!empty($faq_ini) && array_key_exists('source_info', $faq_ini) && array_key_exists('faq_id', $faq_ini['source_info'])) {
            $migration_mappings = MigrationMapping::where('target_source_table', 'faqs')->where('source_key', $nc2_faq_id)->first();
        }
        // マップから新Blog を取得
        if (!empty($migration_mappings)) {
            $faqs = Faqs::find($migration_mappings->destination_key);
        }
        // 新Blog からBucket ID を取得
        if (!empty($faqs)) {
            $bucket = Buckets::find($faqs->bucket_id);
        }
        // bucket がない場合は、フレームは作るけど、エラーログを出しておく。
        if (empty($bucket)) {
            $this->putError(1, 'Faq フレームのみで実体なし', "page_dir = " . $page_dir);
        }
        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);
    }

    /**
     * リンクリストプラグインの登録処理
     */
    private function importPluginLinklists($page, $page_dir, $frame_ini, $display_sequence)
    {
        // 変数定義
        $linklist_id = null;
        $linklist_ini = null;
        $nc2_linklist_id = null;
        $migration_mappings = null;
        $linklists = null;
        $bucket = null;

        // エクスポートファイルの linklist_id 取得（エクスポート時の連番）
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('linklist_id', $frame_ini['frame_base'])) {
            $linklist_id = $frame_ini['frame_base']['linklist_id'];
        }
        // リンクリストの情報取得
        if (!empty($linklist_id) && Storage::exists($this->getImportPath('linklists/linklist_') . $linklist_id . '.ini')) {
            $linklist_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('linklists/linklist_') . $linklist_id . '.ini', true);
        }
        // NC2 のlinklist_id
        if (!empty($linklist_ini) && array_key_exists('source_info', $linklist_ini) && array_key_exists('linklist_id', $linklist_ini['source_info'])) {
            $nc2_linklist_id = $linklist_ini['source_info']['linklist_id'];
        }
        // NC2 のlinklist_id でマップ確認
        if (!empty($linklist_ini) && array_key_exists('source_info', $linklist_ini) && array_key_exists('linklist_id', $linklist_ini['source_info'])) {
            $migration_mappings = MigrationMapping::where('target_source_table', 'linklists')->where('source_key', $nc2_linklist_id)->first();
        }
        // マップから新リンクリストを取得
        if (!empty($migration_mappings)) {
            $linklist = Linklist::find($migration_mappings->destination_key);
        }
        // 新リンクリストからBucket ID を取得
        if (!empty($linklist)) {
            $bucket = Buckets::find($linklist->bucket_id);
        }
        // bucket がない場合は、フレームは作るけど、エラーログを出しておく。
        if (empty($bucket)) {
            $this->putError(1, 'リンクリスト フレームのみで実体なし', "page_dir = " . $page_dir);
        }
        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);

        // リンクリストの表示形式
        $type = LinklistType::none; // 初期値
        if (!empty($linklist_ini) && array_key_exists('linklist_base', $linklist_ini) && array_key_exists('type', $linklist_ini['linklist_base'])) {
            $type = $linklist_ini['linklist_base']['type'];
        }

        // linklist_frames 登録
        if (!empty($frame)) {
            LinklistFrame::create([
                'frame_id'          => $frame->id,
                'view_count'        => null,
                'type'              => $type,
            ]);
        }
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
        if (!empty($database_id) && Storage::exists($this->getImportPath('databases/database_') . $database_id . '.ini')) {
            $database_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('databases/database_') . $database_id . '.ini', true);
        }
        // NC2 のmultidatabase_id
        if (!empty($database_ini) && array_key_exists('source_info', $database_ini) && array_key_exists('multidatabase_id', $database_ini['source_info'])) {
            $multidatabase_id = $database_ini['source_info']['multidatabase_id'];
        }
        // NC2 のmultidatabase_id でマップ確認
        if (!empty($database_ini) && array_key_exists('source_info', $database_ini) && array_key_exists('multidatabase_id', $database_ini['source_info'])) {
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
        if (empty($bucket)) {
            $this->putError(1, 'Database フレームのみで実体なし', "page_dir = " . $page_dir);
        }

        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);

        // NC2 のview_count
        $view_count = 10; // 初期値
        if (!empty($database_ini) && array_key_exists('database', $database_ini) && array_key_exists('view_count', $database_ini['database'])) {
            $view_count = $database_ini['database']['view_count'];
        }

        // databases_frames 登録
        if (!empty($databases)) {
            DatabasesFrames::create([
                'databases_id'      => $databases->id,
                'frames_id'         => $frame->id,
                'use_search_flag'   => $this->getArrayValue($frame_ini, 'database', 'use_search_flag', 1),
                'use_select_flag'   => $this->getArrayValue($frame_ini, 'database', 'use_select_flag', 1),
                'use_sort_flag'     => $this->getArrayValue($frame_ini, 'database', 'use_sort_flag', null),
                'default_sort_flag' => $this->getArrayValue($frame_ini, 'database', 'default_sort_flag', null),
                'use_filter_flag'   => $this->getArrayValue($frame_ini, 'database', 'use_filter_flag', 0),
                'view_count'        => $view_count,
                'default_hide'      => 0,
            ]);
        }
    }

    /**
     * キャビネットプラグインの登録処理
     */
    private function importPluginCabinets($page, $page_dir, $frame_ini, $display_sequence)
    {
        // 変数定義
        $cabinet_id = null;
        $cabinet_ini = null;
        $nc2_cabinet_id = null;
        $migration_mapping = null;
        $cabinet = null;
        $bucket = null;

        // エクスポートファイルの cabinet_id 取得（エクスポート時の連番）
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('cabinet_id', $frame_ini['frame_base'])) {
            $cabinet_id = $frame_ini['frame_base']['cabinet_id'];
        }
        // キャビネットの情報取得
        if (!empty($cabinet_id) && Storage::exists($this->getImportPath('cabinets/cabinet_') . $cabinet_id . '.ini')) {
            $cabinet_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('cabinets/cabinet_') . $cabinet_id . '.ini', true);
        }
        // NC2 のcabinet_id
        if (!empty($cabinet_ini) && array_key_exists('source_info', $cabinet_ini) && array_key_exists('cabinet_id', $cabinet_ini['source_info'])) {
            $nc2_cabinet_id = $cabinet_ini['source_info']['cabinet_id'];
        }
        // NC2 のcabinet_id でマップ確認
        if (!empty($cabinet_ini) && array_key_exists('source_info', $cabinet_ini) && array_key_exists('cabinet_id', $cabinet_ini['source_info'])) {
            $migration_mapping = MigrationMapping::where('target_source_table', 'cabinets')->where('source_key', $nc2_cabinet_id)->first();
        }
        // マップから新Cabinet を取得
        if (!empty($migration_mapping)) {
            $cabinet = Cabinet::find($migration_mapping->destination_key);
        }
        // 新Cabinet からBucket ID を取得
        if (!empty($cabinet)) {
            $bucket = Buckets::find($cabinet->bucket_id);
        }
        // bucket がない場合は、フレームは作るけど、エラーログを出しておく。
        if (empty($bucket)) {
            $this->putError(1, 'Cabinet フレームのみで実体なし', "page_dir = " . $page_dir);
        }
        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);
    }

    /**
     * 掲示板プラグインの登録処理
     */
    private function importPluginBbses($page, $page_dir, $frame_ini, $display_sequence)
    {
        // 変数定義
        $bbs_id = null;
        $bbs_ini = null;
        $nc2_bbs_id = null;
        $migration_mapping = null;
        $bbs = null;
        $bucket = null;

        // エクスポートファイルの bbs_id 取得（エクスポート時の連番）
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('blog_id', $frame_ini['frame_base'])) {
            $tmp = explode('_', $frame_ini['frame_base']['blog_id']);
            $bbs_id = $tmp[1];
        }
        // 掲示板の情報取得
        if (!empty($bbs_id) && Storage::exists($this->getImportPath('bbses/bbs_') . $bbs_id . '.ini')) {
            $bbs_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('bbses/bbs_') . $bbs_id . '.ini', true);
        }
        // NC2 のbbs_id
        if (!empty($bbs_ini) && array_key_exists('source_info', $bbs_ini) && array_key_exists('journal_id', $bbs_ini['source_info'])) {
            $tmp = explode('_', $bbs_ini['source_info']['journal_id']);
            $nc2_bbs_id = $tmp[1];
        }
        // NC2 のbbs_id でマップ確認
        if (!empty($bbs_ini) && array_key_exists('source_info', $bbs_ini) && array_key_exists('journal_id', $bbs_ini['source_info'])) {
            $migration_mapping = MigrationMapping::where('target_source_table', 'bbses')->where('source_key', $nc2_bbs_id)->first();
        }
        // マップから新bbs を取得
        if (!empty($migration_mapping)) {
            $bbs = bbs::find($migration_mapping->destination_key);
        }
        // 新BBS からBucket ID を取得
        if (!empty($bbs)) {
            $bucket = Buckets::find($bbs->bucket_id);
        }
        // bucket がない場合は、フレームは作るけど、エラーログを出しておく。
        if (empty($bucket)) {
            $this->putError(1, 'bbs フレームのみで実体なし', "page_dir = " . $page_dir);
        }
        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);

        if (!empty($bbs)) {
            // フレーム設定保存
            // ---------------------------------------

            $view_format = (int) $this->getArrayValue($frame_ini, 'bbs', 'view_format', 0);

            // 一覧での展開方法 変換
            // (cc) 0:フラット形式,1:スレッド形式
            // (cc) 0:すべて展開,1:根記事のみ展開,2:すべて閉じておく
            // (key:cc)view_format => (value:cc)list_format
            $convert_list_formats = [
                0 => 0,
                1 => 2,
            ];
            $list_format = $convert_list_formats[$view_format] ?? 2;

            // 表示設定
            $bbs_frame = BbsFrame::updateOrCreate(
                ['bbs_id' => $bbs_id, 'frame_id' => $frame->id],
                [
                    // 表示形式 0:フラット形式,1:スレッド形式
                    'view_format' => $view_format,
                    // 根記事の表示順 0:スレッド内の新しい更新日時順,1:根記事の新しい日時順
                    'thread_sort_flag' => 0,
                    // 一覧での展開方法 0:すべて展開,1:根記事のみ展開,2:すべて閉じておく
                    'list_format' => $list_format,
                    // 詳細でのスレッド記事の展開方法 0:すべて展開,1:詳細表示している記事のみ展開,2:すべて閉じておく
                    'thread_format' => 0,
                    // スレッド記事の下線 0:表示しない,1:表示する
                    'list_underline' => 0,
                    // スレッド記事枠のタイトル
                    'thread_caption' => null,
                    // 1ページの表示件数
                    'view_count' => $this->getArrayValue($frame_ini, 'bbs', 'view_count', null),
                ]
            );
        }

        // bucketあり
        if (!empty($bucket)) {
            // 権限設定
            // ---------------------------------------
            // 投稿権限：(nc2) 掲示板単位であり、(cc) バケツ単位であり
            // 承認権限：(nc2) なし、(cc) あり => buckets_roles.approval_flag = 0固定
            BucketsRoles::updateOrCreate(
                [
                    'buckets_id' => $bucket->id,
                    'role' => 'role_article',   // モデレータ
                ], [
                    'post_flag' => $this->getArrayValue($bbs_ini, 'blog_base', 'article_post_flag', 0),
                    'approval_flag' => 0,
                ]
            );
            BucketsRoles::updateOrCreate(
                [
                    'buckets_id' => $bucket->id,
                    'role' => 'role_reporter',  // 編集者
                ], [
                    'post_flag' => $this->getArrayValue($bbs_ini, 'blog_base', 'reporter_post_flag', 0),
                    'approval_flag' => 0,
                ]
            );

            // メール設定
            // ---------------------------------------
            // Buckets のメール設定取得
            $bucket_mail = BucketsMail::firstOrNew(['buckets_id' => $bucket->id]);

            $notice_groups = [];
            if ($this->getArrayValue($bbs_ini, 'blog_base', 'notice_admin_group')) {
                // グループ通知
                // ※ importGroups()は処理前のため管理者グループなし。そのため仮コードを登録してimportGroups()で置換する。
                $notice_groups[] = 'X-管理者グループ';
            }

            if ($this->getArrayValue($bbs_ini, 'blog_base', 'notice_group')) {
                // グループ通知
                // ※ importGroups()は処理前のためnc2ルームグループなし。そのため仮コード(nc2ルームID)を登録してimportGroups()で置換する。
                $notice_groups[] = $this->getArrayValue($bbs_ini, 'source_info', 'room_id') ? 'X-' . $this->getArrayValue($bbs_ini, 'source_info', 'room_id') : '';
            }

            $mail_send = $this->getArrayValue($bbs_ini, 'blog_base', 'mail_send') ? 1 : 0;

            if ($mail_send && $this->getArrayValue($bbs_ini, 'blog_base', 'notice_moderator_group')) {
                // グループ通知
                $this->putMonitor(3, '掲示板のメール設定（モデレータまで）は、手動で「モデレータグループ」を作成して、追加で「モデレータグループ」に通知設定してください。', "バケツ名={$bucket->bucket_name}, bucket_id={$bucket->id}");
            }
            if ($mail_send && $this->getArrayValue($bbs_ini, 'blog_base', 'notice_public_general_group')) {
                // パブリック一般通知
                $this->putMonitor(3, '公開エリアの掲示板のメール設定（一般まで）は、手動で「一般グループ」を作成して、追加で「一般グループ」に通知設定してください。', "バケツ名={$bucket->bucket_name}, bucket_id={$bucket->id}");
            }
            if ($mail_send && $this->getArrayValue($bbs_ini, 'blog_base', 'notice_public_moderator_group')) {
                // パブリックモデレーター通知
                $this->putMonitor(3, '公開エリアの掲示板のメール設定（モデレータまで）は、手動で「モデレータグループ」を作成して、追加で「モデレータグループ」に通知設定してください。', "バケツ名={$bucket->bucket_name}, bucket_id={$bucket->id}");
            }

            // array_filter()でarrayの空要素削除
            $notice_groups = array_filter($notice_groups);

            // 投稿通知
            $bucket_mail->timing             = 0;       // 0:即時送信
            $bucket_mail->notice_on          = $mail_send ? 1 : 0;
            $bucket_mail->notice_create      = $mail_send ? 1 : 0;
            $bucket_mail->notice_update      = 0;
            $bucket_mail->notice_delete      = 0;
            $bucket_mail->notice_addresses   = null;
            $bucket_mail->notice_everyone    = $this->getArrayValue($bbs_ini, 'blog_base', 'notice_everyone') ? 1 : 0;
            $bucket_mail->notice_groups      = implode('|', $notice_groups) == "" ? null : implode('|', $notice_groups);
            $bucket_mail->notice_roles       = null;    // 画面項目なし
            $bucket_mail->notice_subject     = $this->getArrayValue($bbs_ini, 'blog_base', 'mail_subject');
            $bucket_mail->notice_body        = $this->getArrayValue($bbs_ini, 'blog_base', 'mail_body');

            // 関連記事通知
            $bucket_mail->relate_on          = 0;
            // 承認通知
            $bucket_mail->approval_on        = 0;
            // 承認済み通知
            $bucket_mail->approved_on        = 0;
            $bucket_mail->approved_author    = 0;
            // BucketsMails の更新
            $bucket_mail->save();
        }
    }

    /**
     * フォームプラグインの登録処理
     */
    private function importPluginForms($page, $page_dir, $frame_ini, $display_sequence)
    {
        // 変数定義
        $form_id = null;
        $form_ini = null;
        $registration_id = null;
        $migration_mappings = null;
        $forms = null;
        $bucket = null;

        // エクスポートファイルの form_id 取得（エクスポート時の連番）
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('form_id', $frame_ini['frame_base'])) {
            $form_id = $frame_ini['frame_base']['form_id'];
        }
        // フォームの情報取得
        if (!empty($form_id) && Storage::exists($this->getImportPath('forms/form_') . $form_id . '.ini')) {
            $form_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('forms/form_') . $form_id . '.ini', true);
        }
        // NC2 の registration_id でマップ確認
        if (!empty($form_ini) && array_key_exists('source_info', $form_ini) && array_key_exists('registration_id', $form_ini['source_info'])) {
            $registration_id = $form_ini['source_info']['registration_id'];
            $migration_mappings = MigrationMapping::where('target_source_table', 'forms')->where('source_key', $registration_id)->first();
        }
        // マップから新Form を取得
        if (!empty($migration_mappings)) {
            $forms = Forms::find($migration_mappings->destination_key);
        }
        // 新Form からBucket ID を取得
        if (!empty($forms)) {
            $bucket = Buckets::find($forms->bucket_id);
        }
        // bucket がない場合は、フレームは作るけど、エラーログを出しておく。
        if (empty($bucket)) {
            $this->putError(1, 'Form フレームのみで実体なし', "page_dir = " . $page_dir);
        }
        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);
    }

    /**
     * 新着情報プラグインの登録処理
     */
    private function importPluginWhatsnews($page, $page_dir, $frame_ini, $display_sequence)
    {
        // ページ移行の中の、フレーム（ブロック）移行。
        // フレーム（ブロック）で指定されている内容から、移行した新しいバケツを探して、フレーム作成処理へつなげる。

        // 変数定義
        $nc2_whatsnew_block_id = null;
        $whatsnew_ini = null;
        $registration_id = null;
        $migration_mappings = null;
        $whatsnew = null;
        $bucket = null;

        // フレームのエクスポートファイルから、NC2 の新着情報のID 取得
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('whatsnew_block_id', $frame_ini['frame_base'])) {
            $nc2_whatsnew_block_id = $frame_ini['frame_base']['whatsnew_block_id'];
        }
        // エクスポートした新着情報の設定内容の取得
        if (!empty($nc2_whatsnew_block_id) && Storage::exists($this->getImportPath('whatsnews/whatsnew_') . $nc2_whatsnew_block_id . '.ini')) {
            $whatsnew_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('whatsnews/whatsnew_') . $nc2_whatsnew_block_id . '.ini', true);
        }
        // NC2 の whatsnew_block_id でマップ確認
        if (!empty($whatsnew_ini) && array_key_exists('source_info', $whatsnew_ini) && array_key_exists('whatsnew_block_id', $whatsnew_ini['source_info'])) {
            $whatsnew_block_id = $whatsnew_ini['source_info']['whatsnew_block_id'];
            $migration_mappings = MigrationMapping::where('target_source_table', 'whatsnews')->where('source_key', $whatsnew_block_id)->first();
        }
        // マップから新・新着情報 を取得
        if (!empty($migration_mappings)) {
            $whatsnew = Whatsnews::find($migration_mappings->destination_key);
        }
        // 新・新着情報 からBucket ID を取得
        if (!empty($whatsnew)) {
            $bucket = Buckets::find($whatsnew->bucket_id);
        }
        // bucket がない場合は、フレームは作るけど、エラーログを出しておく。
        if (empty($bucket)) {
            $this->putError(1, 'Whatsnew フレームのみで実体なし', "page_dir = " . $page_dir);
        }
        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);
    }

    /**
     * カウンタープラグインの登録処理
     */
    private function importPluginCounters($page, $page_dir, $frame_ini, $display_sequence)
    {
        // 変数定義
        $counter_block_id = null;
        $counter_ini = null;
        $nc2_counter_block_id = null;
        $migration_mapping = null;
        $counter = null;
        $bucket = null;

        // エクスポートファイルの counter_block_id 取得（エクスポート時の連番）
        $counter_block_id = $this->getArrayValue($frame_ini, 'frame_base', 'counter_block_id', null);

        // カウンターの情報取得
        if (!empty($counter_block_id) && Storage::exists($this->getImportPath('counters/counter_') . $counter_block_id . '.ini')) {
            $counter_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('counters/counter_') . $counter_block_id . '.ini', true);
        }
        // NC2 のcounter_block_id
        $nc2_counter_block_id = $this->getArrayValue($counter_ini, 'source_info', 'counter_block_id', null);

        // NC2 のcounter_block_id でマップ確認
        if (!empty($counter_ini) && array_key_exists('source_info', $counter_ini) && array_key_exists('counter_block_id', $counter_ini['source_info'])) {
            $migration_mapping = MigrationMapping::where('target_source_table', 'counters')->where('source_key', $nc2_counter_block_id)->first();
        }
        // マップから新Counter を取得
        if (!empty($migration_mapping)) {
            $counter = Counter::find($migration_mapping->destination_key);
        }
        // 新Counter からBucket ID を取得
        if (!empty($counter)) {
            $bucket = Buckets::find($counter->bucket_id);
        }
        // bucket がない場合は、フレームは作るけど、エラーログを出しておく。
        if (empty($bucket)) {
            $this->putError(1, 'Counter フレームのみで実体なし', "page_dir = " . $page_dir);
        }
        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);

        // counter_frames 登録
        if (!empty($counter)) {
            CounterFrame::create([
                'frame_id' => $frame->id,
                'design_type' => $this->getArrayValue($counter_ini, 'counter_base', 'design_type', CounterDesignType::numeric),
                'use_total_count' => 1,
                'use_today_count' => 1,
                'use_yesterday_count' => 1,
                'total_count_title' => $this->getArrayValue($counter_ini, 'counter_base', 'show_char_before', '累計'),
                'today_count_title' => '今日',
                'yesterday_count_title' => '昨日',
                'total_count_after' => $this->getArrayValue($counter_ini, 'counter_base', 'show_char_after', null),
                'today_count_after' => null,
                'yesterday_count_after' => null,
            ]);
        }
    }

    /**
     * カレンダープラグインの登録処理
     */
    private function importPluginCalendars($page, $page_dir, $frame_ini, $display_sequence)
    {
        // 変数定義
        $calendar_block_id = null;
        $calendar_block_ini = null;
        $nc2_calendar_room_id = null;
        $calendar_room_ini = null;
        $migration_mapping = null;
        $calendar = null;
        $bucket = null;

        // エクスポートファイルの calendar_block_id 取得（エクスポート時の連番）
        $calendar_block_id = $this->getArrayValue($frame_ini, 'frame_base', 'calendar_block_id', null);

        // カレンダーブロックの情報取得
        if (!empty($calendar_block_id) && Storage::exists($this->getImportPath('calendars/calendar_block_') . $calendar_block_id . '.ini')) {
            $calendar_block_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('calendars/calendar_block_') . $calendar_block_id . '.ini', true);
        }

        // NC2 のcalendar_block_id でマップ確認
        if (!empty($calendar_block_ini) && array_key_exists('source_info', $calendar_block_ini) && array_key_exists('room_id', $calendar_block_ini['source_info'])) {
            // NC2 のcalendar の room_id
            $nc2_calendar_room_id = $this->getArrayValue($calendar_block_ini, 'source_info', 'room_id', null);

            $migration_mapping = MigrationMapping::where('target_source_table', 'calendars')->where('source_key', $nc2_calendar_room_id)->first();
        }

        // カレンダールームの情報取得
        if (!empty($nc2_calendar_room_id) && Storage::exists($this->getImportPath('calendars/calendar_room_') . $this->zeroSuppress($nc2_calendar_room_id) . '.ini')) {
            // dd($nc2_calendar_room_id);

            $calendar_room_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('calendars/calendar_room_') . $this->zeroSuppress($nc2_calendar_room_id) . '.ini', true);
        }

        // マップから新Calendar を取得
        if (!empty($migration_mapping)) {
            $calendar = Calendar::find($migration_mapping->destination_key);
        }
        // 新Calendar からBucket ID を取得
        if (!empty($calendar)) {
            $bucket = Buckets::find($calendar->bucket_id);
        }
        // bucket がない場合は、フレームは作るけど、エラーログを出しておく。
        if (empty($bucket)) {
            $this->putError(1, 'Calendar フレームのみで実体なし', "page_dir = " . $page_dir);
        }

        // bucketあり
        if (!empty($bucket)) {

            // calendar_room_iniに[calendar_manage]add_authority_idあり
            if (!empty($calendar_room_ini) && array_key_exists('calendar_manage', $calendar_room_ini) && array_key_exists('add_authority_id', $calendar_room_ini['calendar_manage'])) {

                // NC2 のcalendar の add_authority_id
                $add_authority_id = $this->getArrayValue($calendar_room_ini, 'calendar_manage', 'add_authority_id', null);

                // 権限設定
                // 投稿権限：(nc2) あり、(cc) あり
                //   (nc2) モデレータ⇒ (cc) モデレータ
                //   (nc2) 一般⇒ (cc) 編集者
                //   (nc2) [calendar_manage] => add_authority_id, 予定を追加できる権限. 2:主担,モデレータ,一般  3:主担,モデレータ  4:主担  5:なし（全会員のみ設定可能）
                // 承認権限：(nc2) なし、(cc) あり => buckets_roles.approval_flag = 0固定

                // モデレータの投稿権限 変換 (key:nc2)add_authority_id => (value:cc)post_flag
                $role_article_post_flags = [
                    2 => 1,
                    3 => 1,
                    4 => 0,
                    5 => 0,
                ];
                // 編集者の投稿権限 変換 (key:nc2)add_authority_id => (value:cc)post_flag
                $role_reporter_post_flags = [
                    2 => 1,
                    3 => 0,
                    4 => 0,
                    5 => 0,
                ];

                BucketsRoles::create([
                    'buckets_id' => $bucket->id,
                    'role' => 'role_article',   // モデレータ
                    'post_flag' => $role_article_post_flags[$add_authority_id] ?? 0,
                    'approval_flag' => 0,
                ]);
                BucketsRoles::create([
                    'buckets_id' => $bucket->id,
                    'role' => 'role_reporter',  // 編集者
                    'post_flag' => $role_reporter_post_flags[$add_authority_id] ?? 0,
                    'approval_flag' => 0,
                ]);
            }
        }

        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);

        // calendar_frames 登録
        if (!empty($calendar)) {
            CalendarFrame::create([
                'calendar_id' => $calendar->id,
                'frame_id' => $frame->id,
            ]);
        }
    }

    /**
     * スライダープラグインの登録処理
     */
    private function importPluginSlideshows($page, $page_dir, $frame_ini, $display_sequence)
    {
        // 変数定義
        $slideshows_block_id = null;
        $slideshow_ini = null;
        $nc2_slideshows_block_id = null;
        $migration_mapping = null;
        $slideshow = null;
        $bucket = null;

        // エクスポートファイルの slideshows_block_id 取得（エクスポート時の連番）
        $slideshows_block_id = $this->getArrayValue($frame_ini, 'frame_base', 'slideshows_block_id', null);

        // スライダーの情報取得
        if (!empty($slideshows_block_id) && Storage::exists($this->getImportPath('slideshows/slideshows_') . $slideshows_block_id . '.ini')) {
            $slideshow_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('slideshows/slideshows_') . $slideshows_block_id . '.ini', true);
        }
        // NC2 のslideshow_block_id
        $nc2_slideshows_block_id = $this->getArrayValue($slideshow_ini, 'source_info', 'slideshows_block_id', null);

        // NC2 のslideshow_block_id でマップ確認
        if (!empty($slideshow_ini) && array_key_exists('source_info', $slideshow_ini) && array_key_exists('slideshows_block_id', $slideshow_ini['source_info'])) {
            $migration_mapping = MigrationMapping::where('target_source_table', 'slideshows')->where('source_key', $nc2_slideshows_block_id)->first();
        }

        // マップから新Slideshow を取得
        if (!empty($migration_mapping)) {
            $slideshow = Slideshows::find($migration_mapping->destination_key);
        }
        // 新Slideshow からBucket ID を取得
        if (!empty($slideshow)) {
            $bucket = Buckets::find($slideshow->bucket_id);
        }
        // bucket がない場合は、フレームは作るけど、エラーログを出しておく。
        if (empty($bucket)) {
            $this->putError(1, 'Slideshow フレームのみで実体なし', "page_dir = " . $page_dir);
        }
        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);
    }

    /**
     * シンプル動画（固定記事）プラグインの登録処理
     */
    private function importPluginSimplemovie($page, $page_dir, $frame_ini, $display_sequence)
    {
        // 変数定義
        $simplemovie_block_id = null;
        $simplemovie_ini = null;
        $nc2_simplemovie_block_id = null;
        $migration_mapping = null;
        $simplemovie = null;
        $bucket = null;

        // エクスポートファイルの simplemovie_block_id 取得（エクスポート時の連番）
        $simplemovie_block_id = $this->getArrayValue($frame_ini, 'frame_base', 'simplemovie_block_id', null);

        // シンプル動画の情報取得
        if (!empty($simplemovie_block_id) && Storage::exists($this->getImportPath('simplemovie/simplemovie_') . $simplemovie_block_id . '.ini')) {
            $simplemovie_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('simplemovie/simplemovie_') . $simplemovie_block_id . '.ini', true);
        }
        // NC2 のsimplemovie_block_id
        $nc2_simplemovie_block_id = $this->getArrayValue($simplemovie_ini, 'source_info', 'simplemovie_block_id', null);

        // NC2 のsimplemovie_block_id でマップ確認
        if (!empty($simplemovie_ini) && array_key_exists('source_info', $simplemovie_ini) && array_key_exists('simplemovie_block_id', $simplemovie_ini['source_info'])) {
            $migration_mapping = MigrationMapping::where('target_source_table', 'simplemovie')->where('source_key', $nc2_simplemovie_block_id)->first();
        }
/* TODO ここがおかしいはず */
        // マップから新Simplemovie を取得
        if (!empty($migration_mapping)) {
            $simplemovie = Contents::find($migration_mapping->destination_key);
        }
        // 新Simplemovie からBucket ID を取得
        if (!empty($simplemovie)) {
            $bucket = Buckets::find($simplemovie->bucket_id);
        }

        // bucket がない場合は、フレームは作るけど、エラーログを出しておく。
        if (empty($bucket)) {
            $this->putError(1, 'Simplemovie フレームのみで実体なし', "page_dir = " . $page_dir);
        }
        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);
    }

    /**
     * 施設予約プラグインの登録処理
     */
    private function importPluginReservations($page, $page_dir, $frame_ini, $display_sequence)
    {
        // 変数定義
        $reservation_block_ini = null;
        $migration_mapping = null;
        $reservation = null;
        $bucket = null;

        // エクスポートファイルの reservation_block_id 取得（エクスポート時の連番）
        $reservation_block_id = $this->getArrayValue($frame_ini, 'frame_base', 'reservation_block_id', null);

        // 施設予約ブロックの情報取得
        if (!empty($reservation_block_id) && Storage::exists($this->getImportPath('reservations/reservation_block_') . $reservation_block_id . '.ini')) {
            $reservation_block_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('reservations/reservation_block_') . $reservation_block_id . '.ini', true);

            $migration_mapping = MigrationMapping::where('target_source_table', 'reservations_block')->where('source_key', $reservation_block_ini['source_info']['reservation_block_id'])->first();
        }

        // メール設定
        $reservation_mail_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('reservations/reservation_mail') . '.ini', true);

        // マップから新Reservation を取得
        if (!empty($migration_mapping)) {
            $reservation = Reservation::find($migration_mapping->destination_key);
        }
        // 新Reservation からBucket ID を取得
        if (!empty($reservation)) {
            $bucket = Buckets::find($reservation->bucket_id);
        }
        // bucket がない場合は、フレームは作るけど、エラーログを出しておく。
        if (empty($bucket)) {
            $this->putError(3, 'Reservation フレームのみで実体なし', "page_dir = " . $page_dir);
        }

        // 新Reservationあり
        if (!empty($reservation)) {

            // 表示施設カテゴリ
            // 基本は、全施設表示しない。移行後に表示施設いじってね。
            // オプションで、全バケツで表示する施設カテゴリを指定できる。

            // インポート対象の表示施設カテゴリで、全バケツで表示する施設カテゴリを指定する（指定がなければ全施設表示しない）
            // $all_show_reservations_categories_ids = $this->getMigrationConfig('reservations', 'import_all_show_reservations_categories_ids');
            // if ($all_show_reservations_categories_ids) {
            //     // 施設カテゴリ
            //     $reservations_categories = ReservationsCategory::whereIn('id', $all_show_reservations_categories_ids)
            //         ->orderBy('display_sequence', 'asc')
            //         ->get();
            // } else {
            //     $reservations_categories = collect();
            // }
            $reservations_categories = collect();

            // インポート対象の表示施設カテゴリで、施設カテゴリ名とルーム名が同じものは表示する
            $is_show_same_name = $this->getMigrationConfig('reservations', 'import_is_show_reservations_category_name_and_room_name_are_the_same');
            if ($is_show_same_name) {
                // 施設カテゴリ
                $same_reservations_categories = ReservationsCategory::where('category', $reservation_block_ini['source_info']['room_name'])
                    ->orderBy('display_sequence', 'asc')
                    ->get();

                // コレクションに要素追加
                $reservations_categories = $reservations_categories->concat($same_reservations_categories);
            }

            foreach ($reservations_categories as $reservations_category) {
                // 施設カテゴリー選択テーブルになければ追加、あれば更新
                ReservationsChoiceCategory::updateOrCreate(
                    [
                        'reservations_id' => $reservation->id,
                        'reservations_categories_id' => $reservations_category->id,
                    ], [
                        'view_flag' => ShowType::show,
                        'display_sequence' => intval($reservations_category->display_sequence),
                    ]
                );
            }
        }

        // bucketあり
        if (!empty($bucket)) {

            // 権限設定
            // ---------------------------------------
            // 投稿権限：(nc2) 施設単位であり、(cc) バケツ単位であり =>  buckets_roles.post_flag = 1固定
            // 承認権限：(nc2) なし、(cc) あり => buckets_roles.approval_flag = 0固定
            BucketsRoles::updateOrCreate(
                [
                    'buckets_id' => $bucket->id,
                    'role' => 'role_article',   // モデレータ
                ], [
                    'post_flag' => 1,
                    'approval_flag' => 0,
                ]
            );
            BucketsRoles::updateOrCreate(
                [
                    'buckets_id' => $bucket->id,
                    'role' => 'role_reporter',  // 編集者
                ], [
                    'post_flag' => 1,
                    'approval_flag' => 0,
                ]
            );

            // メール設定
            // ---------------------------------------
            // Buckets のメール設定取得
            $bucket_mail = BucketsMail::firstOrNew(['buckets_id' => $bucket->id]);

            $notice_groups = null;
            if ($this->getArrayValue($reservation_mail_ini, 'reservation_mail', 'notice_admin_group')) {
                // グループ通知
                // ※ importGroups()は処理前のため管理者グループなし。そのため仮コードを登録してimportGroups()で置換する。
                $notice_groups = 'X-管理者グループ';
            }

            $mail_send = $this->getArrayValue($reservation_mail_ini, 'reservation_mail', 'mail_send') ? 1 : 0;

            if ($mail_send && $this->getArrayValue($reservation_mail_ini, 'reservation_mail', 'notice_all_moderator_group')) {
                // グループ通知
                $this->putMonitor(3, '施設予約のメール設定（モデレータまで）は、手動で「モデレータグループ」を作成して、追加で「モデレータグループ」に通知設定してください。', "バケツ名={$bucket->bucket_name}, bucket_id={$bucket->id}");
            }

            // 投稿通知
            $bucket_mail->timing             = 0;       // 0:即時送信
            $bucket_mail->notice_on          = $mail_send ? 1 : 0;
            $bucket_mail->notice_create      = $mail_send ? 1 : 0;
            $bucket_mail->notice_update      = 0;
            $bucket_mail->notice_delete      = 0;
            $bucket_mail->notice_addresses   = null;
            $bucket_mail->notice_everyone    = $this->getArrayValue($reservation_mail_ini, 'reservation_mail', 'notice_everyone') ? 1 : 0;
            $bucket_mail->notice_groups      = $notice_groups;
            $bucket_mail->notice_roles       = null;    // 画面項目なし
            $bucket_mail->notice_subject     = $this->getArrayValue($reservation_mail_ini, 'reservation_mail', 'mail_subject');
            $bucket_mail->notice_body        = $this->getArrayValue($reservation_mail_ini, 'reservation_mail', 'mail_body');

            // 関連記事通知
            $bucket_mail->relate_on          = 0;
            // 承認通知
            $bucket_mail->approval_on        = 0;
            // 承認済み通知
            $bucket_mail->approved_on        = 0;
            $bucket_mail->approved_author    = 0;
            // BucketsMails の更新
            $bucket_mail->save();
        }

        // Frames 登録
        $frame = $this->importPluginFrame($page, $frame_ini, $display_sequence, $bucket);

        if (!empty($reservation)) {
            // フレーム設定保存
            // ---------------------------------------

            // nc2表示方法
            // 1: 月表示(施設別)
            // 2: 週表示(施設別)
            // 3: 日表示(カテゴリ別)
            $display_type = $this->getArrayValue($reservation_block_ini, 'reservation_block', 'display_type');

            // モデレータの投稿権限 変換 (key:nc2)display_type => (value:cc) calendar_initial_display_type
            $calendar_initial_display_types = [
                1 => ReservationCalendarDisplayType::month,
                2 => ReservationCalendarDisplayType::week,
                3 => ReservationCalendarDisplayType::week,
            ];

            // カレンダー初期表示
            $frame_config = FrameConfig::updateOrCreate(
                ['frame_id' => $frame->id, 'name' => ReservationFrameConfig::calendar_initial_display_type],
                ['value' => $calendar_initial_display_types[$display_type] ?? ReservationCalendarDisplayType::month]
            );

            // nc2最初に表示する施設
            // ※ 表示方法=月・週表示のみ設定される. 日表示の場合 0 になる
            $location_id = $this->getArrayValue($reservation_block_ini, 'reservation_block', 'location_id');

            $migration_mapping_location = MigrationMapping::where('target_source_table', 'reservations_location')->where('source_key', $location_id)->first();

            if ($location_id) {
                // 施設IDありは、「１つの施設を選んで表示」にする

                // 施設表示
                $frame_config = FrameConfig::updateOrCreate(
                    ['frame_id' => $frame->id, 'name' => ReservationFrameConfig::facility_display_type],
                    ['value' => FacilityDisplayType::only]
                );
                // カレンダー初期表示
                $frame_config = FrameConfig::updateOrCreate(
                    ['frame_id' => $frame->id, 'name' => ReservationFrameConfig::initial_facility],
                    ['value' => $migration_mapping_location->destination_key]
                );

            } else {
                // 施設IDなし（日表示）は、「全ての施設を表示」にする

                // 施設表示
                $frame_config = FrameConfig::updateOrCreate(
                    ['frame_id' => $frame->id, 'name' => ReservationFrameConfig::facility_display_type],
                    ['value' => FacilityDisplayType::all]
                );
            }

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
//print_r($frame_ini);
        // コンテンツが指定されていない場合は戻る
        if (!array_key_exists('contents', $frame_ini) || !array_key_exists('contents_file', $frame_ini['contents'])) {
            return;
        }

        // HTML コンテンツの取得（画像処理をループしながら、タグを編集するので、ここで読みこんでおく）
        $html_file_path = $page_dir . '/' . $frame_ini['contents']['contents_file'];
        $content_html = File::get($html_file_path);

        // 対象外の条件を確認
        $import_ommit_keywords = $this->getMigrationConfig('contents', 'import_ommit_keyword', array());
        foreach ($import_ommit_keywords as $import_ommit_keyword) {
            if (stripos($content_html, $import_ommit_keyword) !== false) {
                return;
            }
        }

        // Google Analytics タグ部分を削除
        $content_html = $this->deleteGATag($content_html);

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
        // img src="../../uploads/upload_00002.jpg"
        //
        if (array_key_exists('upload_images', $frame_ini) && array_key_exists('upload', $frame_ini['upload_images'])) {
            // アップロードファイル定義のループ
            foreach ($frame_ini['upload_images']['upload'] as $nc2_upload_id => $image_path) {
//print_r($frame_ini);
//echo $nc2_upload_id . "\n";
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
        if (array_key_exists('upload_files', $frame_ini) && array_key_exists('upload', $frame_ini['upload_files'])) {
            foreach ($frame_ini['upload_files']['upload'] as $nc2_upload_id => $file_path) {
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
        $content = new Contents([
            'bucket_id' => $bucket->id,
            'content_text' => $content_html,
            'status' => 0
        ]);
        $content->created_at = $this->getDatetimeFromIniAndCheckFormat($frame_ini, 'source_info', 'insert_time');
        $content->updated_at = $this->getDatetimeFromIniAndCheckFormat($frame_ini, 'source_info', 'update_time');
        // 登録更新日時を自動更新しない
        $content->timestamps = false;
        $content->save();
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

        // 強制的にフレームデザインを適用する指定があれば上書きする。
        if ($frame_design != 'none') {
            $cc_import_force_frame_design = $this->getMigrationConfig('frames', 'cc_import_force_frame_design', null);
            if (!empty($cc_import_force_frame_design)) {
                $frame_design = $cc_import_force_frame_design;
            }
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

        // browser_width
        $browser_width = null;
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('browser_width', $frame_ini['frame_base'])) {
            $browser_width = $frame_ini['frame_base']['browser_width'];
        }

        // disable_whatsnews
        $disable_whatsnews = 0;
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('disable_whatsnews', $frame_ini['frame_base'])) {
            $disable_whatsnews = $frame_ini['frame_base']['disable_whatsnews'];
        }

        // page_only
        $page_only = 0;
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('page_only', $frame_ini['frame_base'])) {
            $page_only = $frame_ini['frame_base']['page_only'];
        }

        // default_hidden
        $default_hidden = 0;
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('default_hidden', $frame_ini['frame_base'])) {
            $default_hidden = $frame_ini['frame_base']['default_hidden'];
        }

        // plugin_name
        $plugin_name = '';
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('plugin_name', $frame_ini['frame_base'])) {
            $plugin_name = $frame_ini['frame_base']['plugin_name'];
        }

        // classname
        $classname = '';
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('classname', $frame_ini['frame_base'])) {
            $classname = $frame_ini['frame_base']['classname'];
        }

        // none_hidden
        $none_hidden = 0;
        if (array_key_exists('frame_base', $frame_ini) && array_key_exists('none_hidden', $frame_ini['frame_base'])) {
            $none_hidden = $frame_ini['frame_base']['none_hidden'];
        }

        // bucket_id
        $bucket_id = null;
        if ($bucket) {
            $bucket_id = $bucket->id;
        }

        // migration_mapping 取得
        if (array_key_exists('addition', $frame_ini)) {
            $source_key = $this->getArrayValue($frame_ini, 'addition', 'source_key');
        } else {
            $source_key = $this->getArrayValue($frame_ini, 'source_info', 'source_key');
        }

        // display_sequence（順番）確定
        // オプションの display_sequence が指定されている場合は、それ以降のフレームを +1 してから追加する。
        $option_display_sequence = $this->getArrayValue($frame_ini, 'frame_option', 'display_sequence');
        if (!empty($option_display_sequence)) {
            Frame::where('page_id', $page->id)->where('display_sequence', '>=', $option_display_sequence)->increment('display_sequence');
            $display_sequence = $option_display_sequence;
        }

        // map 確認
        $migration_mappings = MigrationMapping::where('target_source_table', 'frames')->where('source_key', $source_key)->first();
        if (empty($migration_mappings)) {
            $frame = Frame::create([
                'page_id'           => $page->id,
                'area_id'           => $frame_area_id,
                'frame_title'       => $frame_title,
                'frame_design'      => $frame_design,
                'plugin_name'       => $plugin_name,
                'frame_col'         => $frame_col,
                'template'          => $template,
                'browser_width'     => $browser_width,
                'disable_whatsnews' => $disable_whatsnews,
                'page_only'         => $page_only,
                'default_hidden'    => $default_hidden,
                'classname'         => $classname,
                'none_hidden'       => $none_hidden,
                'bucket_id'         => $bucket_id,
                'display_sequence'  => $display_sequence,
            ]);
            $migration_mappings = MigrationMapping::create([
                'target_source_table' => 'frames',
                'source_key' => $source_key,
                'destination_key' => $frame->id,
            ]);
        } else {
            $frame = Frame::find($migration_mappings->destination_key);
            $frame->page_id           = $page->id;
            $frame->area_id           = $frame_area_id;
            $frame->frame_title       = $frame_title;
            $frame->frame_design      = $frame_design;
            $frame->plugin_name       = $plugin_name;
            $frame->frame_col         = $frame_col;
            $frame->template          = $template;
            $frame->browser_width     = $browser_width;
            $frame->disable_whatsnews = $disable_whatsnews;
            $frame->page_only         = $page_only;
            $frame->default_hidden    = $default_hidden;
            $frame->classname         = $classname;
            $frame->none_hidden       = $none_hidden;
            $frame->bucket_id         = $bucket_id;
            $frame->display_sequence  = $display_sequence;
            $frame->save();
        }

        // firstOrNew しておき、後でframe_id を追加してsave
        /*
        $migration_mappings = MigrationMapping::firstOrNew(
            ['target_source_table' => 'frames', 'source_key' => $source_key],
            ['target_source_table' => 'frames', 'source_key' => $source_key]
        );
        */

        // frame の追加 or 更新
        // $frame = Frame::create(
        // 追加のみの方式から、あれば更新へ変更
        // ※ destination_key が空の場合がある。空で作って、次のフレームで上書きになっている。
        /*
        $frame = Frame::updateOrCreate(
            ['id'               => $migration_mappings->destination_key],
            ['page_id'          => $page->id,
             'area_id'          => $frame_area_id,
             'frame_title'      => $frame_title,
             'frame_design'     => $frame_design,
             'plugin_name'      => $plugin_name,
             'frame_col'        => $frame_col,
             'template'         => $template,
             'bucket_id'        => $bucket_id,
             'display_sequence' => $display_sequence]
        );
        $migration_mappings->destination_key = $frame->id;
        $migration_mappings->save();
        */

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
        Storage::makeDirectory('migration/import/pages/' . $page_id);

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
            $frame_ini .= "template = \"default\"\n";

            // 元のNC3情報
            $frame_ini .= "\n";
            $frame_ini .= "[source_info]\n";
            $frame_ini .= "target_source_table = \"announcement\"\n";

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
                    $savePath = 'migration/import/pages/' . $page_id . "/" . $file_name;
                    $saveStragePath = storage_path() . '/app/' . $savePath;

                    // CURL 設定、ファイル取得
                    $ch = curl_init($downloadPath);
                    $fp = fopen($saveStragePath, 'w');
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$this,'callbackHeader'));
                    $result = curl_exec($ch);
                    if (!empty(curl_errno($ch))) {
                        continue;
                    }
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
            Storage::put('migration/import/pages/' . $page_id . "/frame_" . $frame_index_str . '.ini', $frame_ini);

            // Contents 変換
            $content_html = $this->migrationHtml($content_html);

            // HTML content の保存
            Storage::put('migration/import/pages/' . $page_id . "/frame_" . $frame_index_str . '.html', trim($content_html));
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
                return $images;
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
     * HTML からiframe タグの style 属性を取得
     */
    private function getIframeStyle($content)
    {
        $pattern = '/<iframe.*?style\s*=\s*[\"|\'](.*?)[\"|\'].*?>/i';

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
     * HTML からiframe タグの src 属性を取得
     */
    private function getIframeSrc($content)
    {
        $pattern = '/<iframe.*?src\s*=\s*[\"|\'](.*?)[\"|\'].*?>/i';

        if (preg_match_all($pattern, $content, $matches)) {
            if (is_array($matches) && isset($matches[1])) {
                return $matches[1];
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
     * HTML からa タグの 相対パスリンクを絶対パスに修正
     */
    private function changeFullPath($content, $nc2_page)
    {
        // A タグの相対パス（./ and ../）を探して絶対パスに変換する。
        if (!empty($nc2_page)) {
            // 1つのWYSIWYG に複数存在する可能性があるため、preg_match_all
            $match_count = preg_match_all("|<a href=\"(.*?)\".*?>(.*?)</a>|mis", $content, $matches);
            if ($match_count !== false) {
                for ($i = 0; $i < $match_count; $i++) {
                    // ./ の場合、./ を現在の固定リンクに変換
                    if (stripos($matches[1][$i], './') === 0) {
                        $replace_str = str_ireplace('./', '/' . $nc2_page->permalink . '/', $matches[0][$i]);
                        $content = str_ireplace($matches[0][$i], $replace_str, $content);
                    }
                    // 何階層上がるか計算
                    $count = mb_substr_count($matches[1][$i], '../');
                    if ($count > 0) {
                        $new_permalink = $nc2_page->permalink;
                        // 上がる階層分、右から / の左まで切り取り
                        for ($j = 0; $j < $count; $j++) {
                            if (mb_strpos($new_permalink, '/', 0, "UTF-8") !== false) {
                                // 1階層上がる
                                $new_permalink = mb_substr($new_permalink, 0, mb_strrpos($new_permalink, '/', 0, "UTF-8"));
                            } else {
                                $new_permalink = '';
                            }
                        }
                        // ../ の回数分をフルパスに変換
                        $search = str_repeat('../', $count);
                        // 計算したフルパスは最初と最後の / がないので追加（ルートの場合は / 1つのみ）
                        if (empty($new_permalink)) {
                            $new_permalink = '/';
                        } else {
                            $new_permalink = '/' . $new_permalink . '/';
                        }
                        $replace_str = str_ireplace($search, $new_permalink, $matches[0][$i]);
                        $content = str_ireplace($matches[0][$i], $replace_str, $content);
                    }
                }
            }
        }
        return $content;
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
            return $this->getRouteBlockLangStr($nc2_page->lang_dirname) . $this->zeroSuppress($nc2_page->root_id) . '_' . $this->zeroSuppress($nc2_page->display_sequence);
        } else {
            return $this->getRouteBlockLangStr($nc2_page->lang_dirname) . $this->zeroSuppress($nc2_page->root_id) . '_' . $this->zeroSuppress($nc2_page->page_id);
        }
    }

    /**
     * 経路探索キーの取得（Block）
     */
    private function getRouteBlockStr($nc2_block, $nc2_sort_blocks, $nc2_page, $get_display_sequence = false)
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
            return $this->getRouteBlockLangStr($nc2_page->lang_dirname) . $this->zeroSuppress($nc2_block->root_id) . '_' . $this->zeroSuppress($nc2_block->row_num . $nc2_block->col_num . $nc2_block->thread_num) . '_' . $nc2_block->block_id;
        } else {
            return $this->getRouteBlockLangStr($nc2_page->lang_dirname) . $this->zeroSuppress($nc2_block->root_id) . '_' . $this->zeroSuppress($nc2_block->block_id);
        }
    }

    /**
     * 多言語化判定（日本語）
     */
    private function checkLangDirnameJpn($lang_dirname)
    {
        /* 日本語（とgroupルーム等は空）の場合はtrue */
        if ($lang_dirname == "japanese" || $lang_dirname == "") {
            return true;
        }
        return false;
    }
    /**
     * 多言語化対応文字列返却
     */
    private function getRouteBlockLangStr($lang_dirname)
    {
        if ($this->checkLangDirnameJpn($lang_dirname)) {
            return 'r';
        }
        return $lang_dirname;
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
    private function exportNc2($target, $target_plugin, $redo = null)
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

        // uploads_path の取得
        $uploads_path = config('migration.NC2_EXPORT_UPLOADS_PATH');

        // NC2_EXPORT_UPLOADS_PATH にない場合は、NetCommons2 ワンクリックディレクトリを確認する。
        if (!File::exists($uploads_path)) {
            $uploads_path = storage_path('app/migration/oneclick/htdocs/webapp/uploads/');
        }

        // uploads_path の最後に / がなければ追加
        if (!empty($uploads_path) && mb_substr($uploads_path, -1) != '/') {
            $uploads_path = $uploads_path . '/';
        }

        // サイト基本設定のエクスポート
        if ($this->isTarget('nc2_export', 'basic')) {
            $this->nc2ExportBasic($uploads_path);
        }

        // アップロード・データとファイルのエクスポート
        if ($this->isTarget('nc2_export', 'uploads')) {
            $this->nc2ExportUploads($uploads_path, $redo);
        }

        // 共通カテゴリデータのエクスポート
        // if ($this->isTarget('nc2_export', 'categories')) {
        //     $this->nc2ExportCategories($redo);
        // }

        // ユーザデータのエクスポート
        if ($this->isTarget('nc2_export', 'users')) {
            $this->nc2ExportUsers($redo);
        }

        // ルームデータのエクスポート
        if ($this->isTarget('nc2_export', 'groups')) {
            $this->nc2ExportRooms($redo);
        }

        // NC2 日誌（journal）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'blogs')) {
            $this->nc2ExportJournal($redo);
        }

        // NC2 掲示板（bbs）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'bbses')) {
            $this->nc2ExportBbs($redo);
        }

        // NC2 汎用データベース（multidatabase）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'databases')) {
            $this->nc2ExportMultidatabase($redo);
        }

        // NC2 登録フォーム（registration）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'forms')) {
            $this->nc2ExportRegistration($redo);
        }

        // NC2 FAQ（faq）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'faqs')) {
            $this->nc2ExportFaq($redo);
        }

        // NC2 リンクリスト（linklist）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'linklists')) {
            $this->nc2ExportLinklist($redo);
        }

        // NC2 新着情報（whatsnew）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'whatsnews')) {
            $this->nc2ExportWhatsnew($redo);
        }

        // NC2 キャビネット（cabinet）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'cabinets')) {
            $this->nc2ExportCabinet($redo);
        }

        // NC2 カウンター（counter）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'counters')) {
            $this->nc2ExportCounter($redo);
        }

        // NC2 カレンダー（calendar）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'calendars')) {
            $this->nc2ExportCalendar($redo);
        }

        // NC2 スライダー（slides）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'slideshows')) {
            $this->nc2ExportSlides($redo);
        }

        // NC2 シンプル動画（simplemovie）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'simplemovie')) {
            $this->nc2ExportSimplemovie($redo);
        }

        // NC2 施設予約（reservation）データのエクスポート
        if ($this->isTarget('nc2_export', 'plugins', 'reservations')) {
            $this->nc2ExportReservation($redo);
        }

        // NC2 固定リンク（abbreviate_url）データのエクスポート
        $this->nc2ExportAbbreviateUrl($redo);

        // pages データとファイルのエクスポート
        if ($this->isTarget('nc2_export', 'pages')) {
            // データクリア
            if ($redo === true) {
                MigrationMapping::where('target_source_table', 'nc2_pages')->delete();
                // 移行用ファイルの削除
                Storage::deleteDirectory($this->getImportPath('pages/'));
            }

            // NC2 のページデータ
            $nc2_pages_query = Nc2Page::where('private_flag', 0)        // 0:プライベートルーム以外
                                      ->where('root_id', '<>', 0)
                                      ->where('display_sequence', '<>', 0);

            // ページ指定の有無
            if ($this->getMigrationConfig('pages', 'nc2_export_where_page_ids')) {
                $nc2_pages_query->whereIn('page_id', $this->getMigrationConfig('pages', 'nc2_export_where_page_ids'));
            }

            // 対象外ページ指定の有無
            if ($this->getMigrationConfig('pages', 'nc2_export_ommit_page_ids')) {
                $nc2_pages_query->whereNotIn('page_id', $this->getMigrationConfig('pages', 'nc2_export_ommit_page_ids'));
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

                $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
                // ルーム指定があれば、指定されたルームのみ処理する。
                if (empty($room_ids)) {
                    // ルーム指定なし。全データの移行
                } elseif (!empty($room_ids) && in_array($nc2_sort_page->room_id, $room_ids)) {
                    // ルーム指定あり。指定ルームに合致する。
                } else {
                    // ルーム指定あり。条件に合致せず。移行しない。
                    continue;
                }

                // ページ設定の保存用変数
                $membership_flag = null;
                if ($nc2_sort_page->space_type == 2) {
                    // 「すべての会員をデフォルトで参加させる」
                    if ($nc2_sort_page->default_entry_flag == 1) {
                        $membership_flag = 2;
                    } else {
                        // ルームで選択した会員のみ
                        if ($nc2_sort_page->page_id == $nc2_sort_page->room_id) {
                            $membership_flag = 1;
                        }
                    }
                }
                /* 多言語化対応 */
                if ($this->checkLangDirnameJpn($nc2_sort_page->lang_dirname)) {
                    $lang_link = '';
                } else {
                    $lang_link = '/'.$nc2_sort_page->lang_dirname;
                }
                $permanent_link = ($lang_link != "" && $nc2_sort_page->permalink == "" ) ? $lang_link : $lang_link."/".$nc2_sort_page->permalink;
                $page_ini = "[page_base]\n";
                $page_ini .= "page_name = \"" . $nc2_sort_page->page_name . "\"\n";
                $page_ini .= "permanent_link = \"". $permanent_link . "\"\n";
                $page_ini .= "base_display_flag = 1\n";
                $page_ini .= "membership_flag = " . $membership_flag . "\n";
                $page_ini .= "nc2_page_id = \"" . $nc2_sort_page->page_id . "\"\n";
                $page_ini .= "nc2_room_id = \"" . $nc2_sort_page->room_id . "\"\n";

                // 親ページの検索（parent_id = 1 はパブリックのトップレベルなので、1 より大きいものを探す）
                if ($nc2_sort_page->parent_id > 1) {
                    // マッピングテーブルから親のページのディレクトリを探す
                    $parent_page_mapping = MigrationMapping::where('target_source_table', 'nc2_pages')->where('source_key', $nc2_sort_page->parent_id)->first();
                    //1ルームのみの移行の場合を考慮
                    $parent_room_flg = true;
                    $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
                    if (!empty($room_ids) && count($room_ids) == 1 && isset($room_ids[0])) {
                        if ($nc2_sort_page->parent_id == $room_ids[0]) {
                            $parent_room_flg = false;
                        }
                    }
                    if (!empty($parent_page_mapping) && $parent_room_flg) {
                        $page_ini .= "parent_page_dir = \"" . $parent_page_mapping->destination_key . "\"\n";
                    }
                }

                // ページディレクトリの作成
                //$new_page_index = $nc2_sort_page->page_id;
                $new_page_index++;
                Storage::makeDirectory($this->getImportPath('pages/') . $this->zeroSuppress($new_page_index));

                // ページ設定ファイルの出力
                Storage::put($this->getImportPath('pages/') . $this->zeroSuppress($new_page_index) . '/' . "/page.ini", $page_ini);

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

            // ページ入れ替え
            $this->changePageSequence();
        }

        // ページ、ブロックの関係をCSV 形式で出力。ファイルにしたい場合はコマンドラインでファイルに出力
        // echo $this->frame_tree;
    }

    /**
     *  ページ入れ替え
     */
    private function changePageSequence()
    {
        // パラメータの取得とチェック
        $nc2_export_change_pages = $this->getMigrationConfig('pages', 'nc2_export_change_page');
        if (empty($nc2_export_change_pages)) {
            return;
        }

        // パラメータのループと入れ替え処理
        foreach ($nc2_export_change_pages as $source_page_id => $destination_page_id) {
            // マッピングテーブルを見て、移行後のフォルダ名を取得
            $source_page = MigrationMapping::where('target_source_table', 'nc2_pages')->where('source_key', $source_page_id)->first();
            $destination_page = MigrationMapping::where('target_source_table', 'nc2_pages')->where('source_key', $destination_page_id)->first();

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
        $nc2_export_str_replaces = $this->getMigrationConfig($target, 'nc2_export_str_replace');
        if (!empty($nc2_export_str_replaces)) {
            foreach ($nc2_export_str_replaces as $search => $replace) {
                $value = str_replace($search, $replace, $value);
            }
        }
        return $value;
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
     *  NC2モジュール名の取得
     */
    public function nc2GetModuleNames($action_names, $connect_change = true)
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
     *  NC2 の基本情報をエクスポートする。
     */
    private function nc2ExportBasic($uploads_path)
    {
        $this->putMonitor(3, "Start this->nc2ExportBasic.");

        // config テーブルの取得
        $configs = Nc2Config::get();

        // site,ini ファイル編集
        $basic_ini = "[basic]\n";

        // サイト名
        $sitename = $configs->where('conf_name', 'sitename')->first();
        $sitename = empty($sitename) ? '' : $sitename->conf_value;
        $basic_ini .= "base_site_name = \"" . $sitename . "\"\n";

        // 基本デザイン（パブリック）
        $default_theme_public = $configs->where('conf_name', 'default_theme_public')->first();
        $default_theme_public = empty($default_theme_public) ? '' : $default_theme_public->conf_value;
        $basic_ini .= "default_theme_public = \"" . $default_theme_public . "\"\n";

        // basic,ini ファイル保存
        //Storage::put($this->getImportPath('basic/basic.ini'), $basic_ini);
        $this->storagePut($this->getImportPath('basic/basic.ini'), $basic_ini);
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
    private function nc2ExportUploads($uploads_path, $redo)
    {
        $this->putMonitor(3, "Start this->nc2ExportUploads.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('uploads/'));
            // アップロードファイルの削除
            Storage::deleteDirectory(config('connect.directory_base'));
        }

        // NC2 アップロードテーブルを移行する。
        $nc2_uploads = Nc2Upload::orderBy('upload_id')->get();

        // uploads,ini ファイル
        //Storage::put($this->getImportPath('uploads/uploads.ini'), "[uploads]");
        $this->storagePut($this->getImportPath('uploads/uploads.ini'), "[uploads]");

        // uploads,ini ファイルの詳細（変数に保持、後でappend。[uploads] セクションが切れないため。）
        $uploads_ini = "";
        $uploads_ini_detail = "";

        // アップロード・ファイルのループ
        foreach ($nc2_uploads as $nc2_upload) {
            // NC2 バックアップは対象外
            if ($nc2_upload->file_path == 'backup/') {
                continue;
            }

            // アップロードファイルのルームを無視する指定があれば全部を移行、なければルーム設定を参照
            if (!$this->hasMigrationConfig('uploads', 'nc2_export_uploads_force_room', true)) {
                $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
                // ルーム指定があれば、指定されたルームのみ処理する。
                if (empty($room_ids)) {
                    // ルーム指定なし。全データの移行
                } elseif (!empty($room_ids) && in_array($nc2_upload->room_id, $room_ids)) {
                    // ルーム指定あり。指定ルームに合致する。
                } else {
                    // ルーム指定あり。条件に合致せず。移行しない。
                    continue;
                }
            }

            // ファイルのコピー
            $source_file_path = $uploads_path . $nc2_upload->file_path . $nc2_upload->physical_file_name;
            $destination_file_dir = storage_path() . "/app/" . $this->getImportPath('uploads');
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
        Storage::append($this->getImportPath('uploads/uploads.ini'), $uploads_ini . $uploads_ini_detail);

        // uploads のini ファイルの再読み込み
        if (Storage::exists($this->getImportPath('uploads/uploads.ini'))) {
            $this->uploads_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('uploads/uploads.ini'), true);
        }
    }

    // delete: 全体カテゴリは作らない
    // /**
    //  * NC2：カテゴリの移行
    //  */
    // private function nc2ExportCategories($redo)
    // {
    //     $this->putMonitor(3, "Start nc2ExportCategories.");

    //     // データクリア
    //     if ($redo === true) {
    //         // 移行用ファイルの削除
    //         Storage::deleteDirectory($this->getImportPath('categories/'));
    //     }

    //     // categories,ini ファイル
    //     $uploads_ini = "[categories]";
    //     foreach ($this->nc2_default_categories as $nc2_default_category_key => $nc2_default_category) {
    //         $uploads_ini .= "\n" . "categories[" . $nc2_default_category_key . "] = \"" . $nc2_default_category . "\"";
    //     }
    //     //Storage::put($this->getImportPath('categories/categories.ini'), $uploads_ini);
    //     $this->storagePut($this->getImportPath('categories/categories.ini'), $uploads_ini);
    // }

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
    private function nc2ExportUsers($redo)
    {
        $this->putMonitor(3, "Start nc2ExportUsers.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('users/'));
        }

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

        // NC2 ユーザ任意項目
        $nc2_any_items = collect([]);
        $nc2_export_user_items = $this->getMigrationConfig('users', 'nc2_export_user_items');
        if ($nc2_export_user_items) {
            $nc2_any_items = Nc2Item::select('items.*', 'items_desc.description')
                ->whereIn('items.item_name', $nc2_export_user_items)
                ->leftJoin('items_desc', 'items_desc.item_id', '=', 'items.item_id')
                ->orderBy('items.col_num')
                ->orderBy('items.row_num')
                ->get();
        }

        // NC2 ユーザデータ取得
        $nc2_users_query = Nc2User::select('users.*');
        if (!empty($nc2_mail_item)) {
            // メール項目
            $nc2_users_query->addSelect('users_items_link.content AS email')
                ->leftJoin('users_items_link', function ($join) use ($nc2_mail_item) {
                    $join->on('users_items_link.user_id', '=', 'users.user_id')
                        ->where('users_items_link.item_id', $nc2_mail_item->item_id);
                });
        }
        if ($nc2_any_items->isNotEmpty()) {
            // 任意項目
            foreach ($nc2_any_items as $nc2_any_item) {
                $nc2_users_query->addSelect("users_items_link_{$nc2_any_item->item_id}.content AS item_{$nc2_any_item->item_id}")
                    ->leftJoin("users_items_link as users_items_link_{$nc2_any_item->item_id}", function ($join) use ($nc2_any_item) {
                        $join->on("users_items_link_{$nc2_any_item->item_id}.user_id", '=', 'users.user_id')
                            ->where("users_items_link_{$nc2_any_item->item_id}.item_id", $nc2_any_item->item_id);
                    });
            }
        }
        $nc2_users = $nc2_users_query->orderBy('users.insert_time')->get();

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
            if ($nc2_user->active_flag == 0) {
                $users_ini .= "status             = 1\n";
            } else {
                $users_ini .= "status             = 0\n";
            }
            if ($nc2_any_items->isNotEmpty()) {
                // 任意項目
                foreach ($nc2_any_items as $nc2_any_item) {
                    $item_name = "item_{$nc2_any_item->item_id}";
                    $users_ini .= "{$item_name}            = \"" . $nc2_user->$item_name . "\"\n";
                }
            }

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
        //Storage::put($this->getImportPath('users/users.ini'), $users_ini);
        $this->storagePut($this->getImportPath('users/users.ini'), $users_ini);

        // ユーザ任意項目
        foreach ($nc2_any_items as $i => $nc2_any_item) {
            // カラム型 変換
            $convert_user_column_types = [
                // nc2, cc
                'text' => UserColumnType::text,
                'email' => UserColumnType::mail,
                'mobile_email' => UserColumnType::mail,
                // 'radio' => UserColumnType::radio,
                'textarea' => UserColumnType::textarea,
                // 'select' => UserColumnType::select,
            ];

            // 未対応
            $exclude_user_column_types = [
                'password',
                'file',
                'label',
                'system',
                'radio',
                'select',
            ];

            $user_column_type = $nc2_any_item->type;
            if (in_array($user_column_type, $exclude_user_column_types)) {
                // 未対応
                $user_column_type = '';
                $this->putError(3, 'ユーザ任意項目の項目タイプが未対応', "item.type = " . $user_column_type);

            } elseif (array_key_exists($user_column_type, $convert_user_column_types)) {
                $user_column_type = $convert_user_column_types[$user_column_type];

            } else {
                // 未対応に未指定
                $user_column_type = '';
                $this->putError(3, 'ユーザ任意項目の項目タイプが未対応（未対応に未指定の型）', "item.type = " . $user_column_type);
            }

            // ini ファイル用変数
            $users_columns_ini  = "[users_columns_base]\n";
            $users_columns_ini .= "column_type      = \"" . $user_column_type . "\"\n";
            $users_columns_ini .= "column_name      = \"" . $nc2_any_item->item_name . "\"\n";
            $users_columns_ini .= "required         = " . $nc2_any_item->require_flag . "\n";
            $users_columns_ini .= "caption          = \"" . $nc2_any_item->description . "\"\n";
            $users_columns_ini .= "display_sequence = " . ($i + 1) . "\n";
            $users_columns_ini .= "\n";
            $users_columns_ini .= "[source_info]\n";
            $users_columns_ini .= "item_id = " . $nc2_any_item->item_id . "\n";

            // Userカラムデータの出力
            $this->storagePut($this->getImportPath('users/users_columns_') . $this->zeroSuppress($nc2_any_item->item_id) . '.ini', $users_columns_ini);
        }
    }


    /**
     * NC2：グループの移行
     */
    private function nc2ExportRooms($redo)
    {
        $this->putMonitor(3, "Start nc2ExportRooms.");

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

        // NC2 ルームの取得
        // 「すべての会員をデフォルトで参加させる」はグループにしないので対象外。'default_entry_flag'== 0
        $nc2_rooms_query = Nc2Page::where('space_type', 2)
                            ->whereColumn('page_id', 'room_id')
                            ->whereIn('thread_num', [1, 2])
                            ->where('default_entry_flag', 0);
        // 対象外ページ指定の有無
        if ($this->getMigrationConfig('pages', 'nc2_export_ommit_page_ids')) {
            $nc2_rooms_query->whereNotIn('page_id', $this->getMigrationConfig('pages', 'nc2_export_ommit_page_ids'));
        }
        $nc2_rooms = $nc2_rooms_query->orderBy('thread_num')->orderBy('display_sequence')->get();

        // 空なら戻る
        if ($nc2_rooms->isEmpty()) {
            return;
        }

        // グループをループ
        foreach ($nc2_rooms as $nc2_room) {
            // ini ファイル用変数
            $groups_ini  = "[group_base]\n";
            $groups_ini .= "name = \"" . $nc2_room->page_name . "\"\n";
            $groups_ini .= "\n";
            $groups_ini .= "[source_info]\n";
            $groups_ini .= "room_id = " . $nc2_room->room_id . "\n";
            $groups_ini .= "\n";
            $groups_ini .= "[users]\n";

            // NC2 参加ユーザの取得
            $nc2_pages_users_links = Nc2PageUserLink::select('pages_users_link.*', 'users.login_id')
                                                    ->join('users', 'users.user_id', 'pages_users_link.user_id')
                                                    ->where('room_id', $nc2_room->room_id)
                                                    ->orderBy('room_id')
                                                    ->orderBy('users.role_authority_id')
                                                    ->orderBy('users.insert_time')
                                                    ->get();

            foreach ($nc2_pages_users_links as $nc2_pages_users_link) {
                $groups_ini .= "user[\"" . $nc2_pages_users_link->login_id . "\"] = " . $nc2_pages_users_link->role_authority_id . "\n";
            }

            // グループデータの出力
            $this->storagePut($this->getImportPath('groups/group_') . $this->zeroSuppress($nc2_room->room_id) . '.ini', $groups_ini);
        }
    }

    /**
     * NC2：日誌（Journal）の移行
     */
    private function nc2ExportJournal($redo)
    {
        $this->putMonitor(3, "Start nc2ExportJournal.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('blogs/'));
        }

        // NC2日誌（Journal）を移行する。
        $nc2_journals = Nc2Journal::orderBy('journal_id')->get();

        // 空なら戻る
        if ($nc2_journals->isEmpty()) {
            return;
        }

        // NC2日誌（Journal）のループ
        foreach ($nc2_journals as $nc2_journal) {
            $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc2_journal->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // この日誌が配置されている最初のページオブジェクトを取得しておく
            // WYSIWYG で相対パスを絶対パスに変換する際に、ページの固定URL が必要になるため。
            $nc2_page = null;
            $nc2_journal_block = Nc2JournalBlock::where('journal_id', $nc2_journal->journal_id)->orderBy('block_id', 'asc')->first();
            if (!empty($nc2_journal_block)) {
                $nc2_block = Nc2Block::where('block_id', $nc2_journal_block->block_id)->first();
            }
            if (!empty($nc2_block)) {
                $nc2_page = Nc2Page::where('page_id', $nc2_block->page_id)->first();
            }

            // ブログ設定
            $journals_ini = "";
            $journals_ini .= "[blog_base]\n";
            $journals_ini .= "blog_name = \"" . $nc2_journal->journal_name . "\"\n";
            $journals_ini .= "view_count = 10\n";
            $journals_ini .= "use_like = " . $nc2_journal->vote_flag . "\n";

            // NC2 情報
            $journals_ini .= "\n";
            $journals_ini .= "[source_info]\n";
            $journals_ini .= "journal_id = " . $nc2_journal->journal_id . "\n";
            $journals_ini .= "room_id = " . $nc2_journal->room_id . "\n";
            $journals_ini .= "module_name = \"journal\"\n";

            // NC2日誌で使ってるカテゴリ（journal_category）のみ移行する。
            $journals_ini .= "\n";
            $journals_ini .= "[categories]\n";
            $nc2_journal_categories = Nc2JournalCategory::
                select(
                    'journal_category.category_id',
                    'journal_category.category_name'
                )
                ->where('journal_category.journal_id', $nc2_journal->journal_id)
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
            //Log::debug($nc2_journal_categories);
            // $journals_ini_commons = "";
            $journals_ini_originals = "";

            foreach ($nc2_journal_categories as $nc2_journal_category) {
                // if (in_array($nc2_journal_category->category_name, $this->nc2_default_categories)) {
                //     // 共通カテゴリにあるものは個別に作成しない。
                //     $journals_ini_commons .= "common_categories[" . array_search($nc2_journal_category->category_name, $this->nc2_default_categories) . "] = \"" . $nc2_journal_category->category_name . "\"\n";
                // } else {
                //     $journals_ini_originals .= "original_categories[" . $nc2_journal_category->category_id . "] = \"" . $nc2_journal_category->category_name . "\"\n";
                // }
                $journals_ini_originals .= "original_categories[" . $nc2_journal_category->category_id . "] = \"" . $nc2_journal_category->category_name . "\"\n";
            }
            // if (!empty($journals_ini_commons)) {
            //     $journals_ini .= $journals_ini_commons;
            // }
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

                $content       = $this->nc2Wysiwyg(null, null, null, null, $nc2_journal_post->content, 'journal', $nc2_page);
                $more_content  = $this->nc2Wysiwyg(null, null, null, null, $nc2_journal_post->more_content, 'journal', $nc2_page);

                $category_obj  = $nc2_journal_categories->firstWhere('category_id', $nc2_journal_post->category_id);
                $category      = "";
                if (!empty($category_obj)) {
                    $category  = $category_obj->category_name;
                }

                $like_count = empty($nc2_journal_post->vote) ? 0 : count(explode('|', $nc2_journal_post->vote));

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
                $journals_tsv .= $like_count                        . "\t";   // いいね数
                $journals_tsv .= $nc2_journal_post->vote            . "\t";   // いいねのsession_id & nc2 user_id

                // 記事のタイトルの一覧
                // タイトルに " あり
                if (strpos($nc2_journal_post->title, '"')) {
                    // ログ出力
                    $this->putError(1, 'Blog title in double-quotation', "タイトル = " . $nc2_journal_post->title);
                }
                $journals_ini .= "post_title[" . $nc2_journal_post->post_id . "] = \"" . str_replace('"', '', $nc2_journal_post->title) . "\"\n";
            }

            // blog の記事毎設定
            // $journals_ini .= $blog_post_ini_detail;

            // blog の設定
            //Storage::put($this->getImportPath('blogs/blog_') . $this->zeroSuppress($nc2_journal->journal_id) . '.ini', $journals_ini);
            $this->storagePut($this->getImportPath('blogs/blog_') . $this->zeroSuppress($nc2_journal->journal_id) . '.ini', $journals_ini);

            // blog の記事
            //Storage::put($this->getImportPath('blogs/blog_') . $this->zeroSuppress($nc2_journal->journal_id) . '.tsv', $journals_tsv);
            $journals_tsv = $this->exportStrReplace($journals_tsv, 'blogs');
            $this->storagePut($this->getImportPath('blogs/blog_') . $this->zeroSuppress($nc2_journal->journal_id) . '.tsv', $journals_tsv);
        }
    }

    /**
     * NC2：掲示板（Bbs）の移行
     */
    private function nc2ExportBbs($redo)
    {
        $this->putMonitor(3, "Start nc2ExportBbs.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('bbses/'));
        }

        // NC2掲示板（Bbs）を移行する。
        $nc2_bbses = Nc2Bbs::orderBy('bbs_id')->get();

        $nc2_bbses = Nc2Bbs::select('bbs.*', 'page_rooms.space_type')
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
        if ($nc2_bbses->isEmpty()) {
            return;
        }

        // nc2の全ユーザ取得
        $nc2_users = Nc2User::get();

        // NC2掲示板（Bbs）のループ
        foreach ($nc2_bbses as $nc2_bbs) {
            $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc2_bbs->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // この掲示板が配置されている最初のページオブジェクトを取得しておく
            // WYSIWYG で相対パスを絶対パスに変換する際に、ページの固定URL が必要になるため。
            $nc2_page = null;
            $nc2_bbs_block = Nc2BbsBlock::where('bbs_id', $nc2_bbs->bbs_id)->orderBy('block_id', 'asc')->first();
            if (!empty($nc2_bbs_block)) {
                $nc2_block = Nc2Block::where('block_id', $nc2_bbs_block->block_id)->first();
            }
            if (!empty($nc2_block)) {
                $nc2_page = Nc2Page::where('page_id', $nc2_block->page_id)->first();
            }

            // 権限設定
            // ----------------------------------------------------
            // topic_authority
            // 2: 一般まで
            // 3: モデレータまで
            // 4: 主担のみ
            $article_post_flag = 0;
            $reporter_post_flag = 0;
            if ($nc2_bbs->topic_authority == 2) {
                $article_post_flag = 1;
                $reporter_post_flag = 1;

            } elseif ($nc2_bbs->topic_authority == 3) {
                $article_post_flag = 1;

            } elseif ($nc2_bbs->topic_authority == 4) {
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

            if ($nc2_bbs->mail_authority === 1) {
                if ($nc2_bbs->space_type == Nc2Page::space_type_public) {
                    // 全ユーザ通知
                    $notice_everyone = 1;

                } elseif ($nc2_bbs->space_type == Nc2Page::space_type_group) {
                    // グループ通知
                    $notice_group = 1;
                }

            } elseif ($nc2_bbs->mail_authority == 2) {
                if ($nc2_bbs->space_type == Nc2Page::space_type_public) {
                    // パブリック一般通知
                    $notice_public_general_group = 1;
                    $notice_admin_group = 1;

                } elseif ($nc2_bbs->space_type == Nc2Page::space_type_group) {
                    // グループ通知
                    $notice_group = 1;
                }

            } elseif ($nc2_bbs->mail_authority == 3) {
                if ($nc2_bbs->space_type == Nc2Page::space_type_public) {
                    // パブリックモデレーター通知
                    $notice_public_moderator_group = 1;
                    $notice_admin_group = 1;
                } elseif ($nc2_bbs->space_type == Nc2Page::space_type_group) {
                    // モデレータユーザ通知
                    $notice_moderator_group = 1;
                    $notice_admin_group = 1;
                }

            } elseif ($nc2_bbs->mail_authority == 4) {
                // 管理者グループ通知
                $notice_admin_group = 1;
            }

            $mail_subject = $nc2_bbs->mail_subject;
            $mail_body = $nc2_bbs->mail_body;

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
                // nc2埋込タグ, cc埋込タグ
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
            $journals_ini .= "blog_name = \"" . $nc2_bbs->bbs_name . "\"\n";
            $journals_ini .= "use_like = " . $nc2_bbs->vote_flag . "\n";
            $journals_ini .= "article_post_flag = " . $article_post_flag . "\n";
            $journals_ini .= "reporter_post_flag = " . $reporter_post_flag . "\n";
            $journals_ini .= "mail_send = " . $nc2_bbs->mail_send . "\n";
            $journals_ini .= "notice_everyone = " . $notice_everyone . "\n";
            $journals_ini .= "notice_group = " . $notice_group . "\n";
            $journals_ini .= "notice_moderator_group = " . $notice_moderator_group . "\n";
            $journals_ini .= "notice_admin_group = " . $notice_admin_group . "\n";
            $journals_ini .= "notice_public_general_group = " . $notice_public_general_group . "\n";
            $journals_ini .= "notice_public_moderator_group = " . $notice_public_moderator_group . "\n";
            $journals_ini .= "mail_subject = \"" . $mail_subject . "\"\n";
            $journals_ini .= "mail_body = \"" . $mail_body . "\"\n";

            // NC2 情報
            $journals_ini .= "\n";
            $journals_ini .= "[source_info]\n";
            $journals_ini .= "journal_id = " . 'BBS_' . $nc2_bbs->bbs_id . "\n";
            $journals_ini .= "room_id = " . $nc2_bbs->room_id . "\n";
            $journals_ini .= "space_type = " . $nc2_bbs->space_type . "\n";   // スペースタイプ, 1:パブリックスペース, 2:グループスペース
            $journals_ini .= "module_name = \"bbs\"\n";
            $journals_ini .= "insert_time = \"" . $this->getCCDatetime($nc2_bbs->insert_time) . "\"\n";
            $journals_ini .= "insert_user_name = \"" . $nc2_bbs->insert_user_name . "\"\n";
            $journals_ini .= "insert_login_id = \"" . $this->getNc2LoginIdFromNc2UserId($nc2_users, $nc2_bbs->insert_user_id) . "\"\n";
            $journals_ini .= "update_time = \"" . $this->getCCDatetime($nc2_bbs->update_time) . "\"\n";
            $journals_ini .= "update_user_name = \"" . $nc2_bbs->update_user_name . "\"\n";
            $journals_ini .= "update_login_id = \"" . $this->getNc2LoginIdFromNc2UserId($nc2_users, $nc2_bbs->update_user_id) . "\"\n";

            // NC2掲示板の記事（bbs_post、bbs_post_body）を移行する。
            $nc2_bbs_posts = Nc2BbsPost::
                select('bbs_post.*', 'bbs_post_body.body', 'bbs_topic.newest_time')
                ->join('bbs_post_body', 'bbs_post_body.post_id', '=', 'bbs_post.post_id')
                ->leftJoin('bbs_topic', 'bbs_topic.topic_id', '=', 'bbs_post.topic_id')
                ->where('bbs_id', $nc2_bbs->bbs_id)
                ->orderBy('post_id')
                ->get();

            // 記事はTSV でエクスポート
            // 日付{\t}status{\t}タイトル{\t}本文
            $journals_tsv = "";

            // NC2記事をループ
            $journals_ini .= "\n";
            $journals_ini .= "[blog_post]\n";
            foreach ($nc2_bbs_posts as $nc2_bbs_post) {
                // TSV 形式でエクスポート
                if (!empty($journals_tsv)) {
                    $journals_tsv .= "\n";
                }

                $content       = $this->nc2Wysiwyg(null, null, null, null, $nc2_bbs_post->body, 'bbs', $nc2_page);

                $journals_tsv .= $this->getCCDatetime($nc2_bbs_post->insert_time) . "\t"; // 0:投稿日時
                $journals_tsv .=                              "\t"; // カテゴリ
                $journals_tsv .= $nc2_bbs_post->status      . "\t";
                $journals_tsv .=                              "\t"; // 承認フラグ
                // データ中にタブ文字が存在するケースがあったため、タブ文字は消すようにした。
                $journals_tsv .= str_replace("\t", "", $nc2_bbs_post->subject) . "\t";
                $journals_tsv .= $content                   . "\t";
                $journals_tsv .=                              "\t"; // more_content
                $journals_tsv .=                              "\t"; // more_title
                $journals_tsv .=                              "\t"; // hide_more_title
                $journals_tsv .= $nc2_bbs_post->parent_id   . "\t"; // 親ID
                $journals_tsv .= $nc2_bbs_post->topic_id .    "\t"; // トピックID
                $journals_tsv .= $this->getCCDatetime($nc2_bbs_post->newest_time) . "\t"; // 11:最新投稿日時
                $journals_tsv .= $nc2_bbs_post->insert_user_name . "\t"; // 12:投稿者名
                $journals_tsv .= $nc2_bbs_post->vote_num    . "\t"; // いいね数
                $journals_tsv .=                              "\t"; // いいねのsession_id & nc2 user_id
                $journals_tsv .= $this->getNc2LoginIdFromNc2UserId($nc2_users, $nc2_bbs_post->insert_user_id) . "\t";   // 15:投稿者ID
                $journals_tsv .= $this->getCCDatetime($nc2_bbs_post->update_time) . "\t";                               // 16:更新日時
                $journals_tsv .= $nc2_bbs_post->update_user_name . "\t";                                                // 17:更新者名
                $journals_tsv .= $this->getNc2LoginIdFromNc2UserId($nc2_users, $nc2_bbs_post->update_user_id) . "\t";   // 18:更新者ID

                // 記事のタイトルの一覧
                // タイトルに " あり
                if (strpos($nc2_bbs_post->subject, '"')) {
                    // ログ出力
                    $this->putError(1, 'BBS subject in double-quotation', "タイトル = " . $nc2_bbs_post->subject);
                }
                $journals_ini .= "post_title[" . $nc2_bbs_post->post_id . "] = \"" . str_replace('"', '', $nc2_bbs_post->subject) . "\"\n";
            }

            // bbs->blog移行の場合は、blog用のフォルダに吐き出す
            $export_path = 'bbses/bbs_';
            if ($this->plugin_name['bbs'] === 'blogs') {
                $export_path = 'blogs/blog_bbs_';
            }

            // blog の設定
            //Storage::put($this->getImportPath('blogs/blog_bbs_') . $this->zeroSuppress($nc2_bbs_post->bbs_id) . '.ini', $journals_ini);
            $this->storagePut($this->getImportPath($export_path) . $this->zeroSuppress($nc2_bbs->bbs_id) . '.ini', $journals_ini);

            // blog の記事
            //Storage::put($this->getImportPath('blogs/blog_bbs_') . $this->zeroSuppress($nc2_bbs_post->bbs_id) . '.tsv', $journals_tsv);
            $journals_tsv = $this->exportStrReplace($journals_tsv, 'bbses');
            $this->storagePut($this->getImportPath($export_path) . $this->zeroSuppress($nc2_bbs->bbs_id) . '.tsv', $journals_tsv);
        }
    }

    /**
     * NC2：FAQ（Faq）の移行
     */
    private function nc2ExportFaq($redo)
    {
        $this->putMonitor(3, "Start nc2ExportFaq.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('faqs/'));
        }

        // NC2FAQ（Faq）を移行する。
        $nc2_faqs = Nc2Faq::orderBy('faq_id')->get();

        // 空なら戻る
        if ($nc2_faqs->isEmpty()) {
            return;
        }

        // NC2FAQ（Faq）のループ
        foreach ($nc2_faqs as $nc2_faq) {
            $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc2_faq->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // このFAQが配置されている最初のページオブジェクトを取得しておく
            // WYSIWYG で相対パスを絶対パスに変換する際に、ページの固定URL が必要になるため。
            $nc2_page = null;
            $nc2_faq_block = Nc2FaqBlock::where('faq_id', $nc2_faq->faq_id)->orderBy('block_id', 'asc')->first();
            if (!empty($nc2_faq_block)) {
                $nc2_block = Nc2Block::where('block_id', $nc2_faq_block->block_id)->first();
            }
            if (!empty($nc2_block)) {
                $nc2_page = Nc2Page::where('page_id', $nc2_block->page_id)->first();
            }

            $faqs_ini = "";
            $faqs_ini .= "[faq_base]\n";
            $faqs_ini .= "faq_name = \"" . $nc2_faq->faq_name . "\"\n";
            $faqs_ini .= "view_count = 10\n";

            // NC2 情報
            $faqs_ini .= "\n";
            $faqs_ini .= "[source_info]\n";
            $faqs_ini .= "faq_id = " . $nc2_faq->faq_id . "\n";
            $faqs_ini .= "room_id = " . $nc2_faq->room_id . "\n";
            $faqs_ini .= "module_name = \"faq\"\n";

            // NC2FAQで使ってるカテゴリ（faq_category）のみ移行する。
            $faqs_ini .= "\n";
            $faqs_ini .= "[categories]\n";
            // $nc2_faq_categories = Nc2FaqCategory::where('faq_id', $nc2_faq->faq_id)->orderBy('display_sequence')->get();
            $nc2_faq_categories = Nc2FaqCategory::
                select(
                    'faq_category.category_id',
                    'faq_category.category_name'
                )
                ->where('faq_category.faq_id', $nc2_faq->faq_id)
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

            foreach ($nc2_faq_categories as $nc2_faq_category) {
                $faqs_ini_originals .= "original_categories[" . $nc2_faq_category->category_id . "] = \"" . $nc2_faq_category->category_name . "\"\n";
            }
            if (!empty($faqs_ini_originals)) {
                $faqs_ini .= $faqs_ini_originals;
            }

            // NC2FAQの記事（faq_question）を移行する。
            $nc2_faq_questions = Nc2FaqQuestion::where('faq_id', $nc2_faq->faq_id)->orderBy('display_sequence')->get();

            // FAQの記事はTSV でエクスポート
            // カテゴリID{\t}表示順{\t}タイトル{\t}本文
            $faqs_tsv = "";

            // NC2FAQの記事をループ
            // $faqs_ini .= "\n";
            // $faqs_ini .= "[faq_question]\n";
            foreach ($nc2_faq_questions as $nc2_faq_question) {
                // TSV 形式でエクスポート
                if (!empty($faqs_tsv)) {
                    $faqs_tsv .= "\n";
                }

                $category_obj  = $nc2_faq_categories->firstWhere('category_id', $nc2_faq_question->category_id);
                $category = "";
                if (!empty($category_obj)) {
                    $category  = $category_obj->category_name;
                }

                $question_answer = $this->nc2Wysiwyg(null, null, null, null, $nc2_faq_question->question_answer, 'faq', $nc2_page);

                $faqs_tsv .= $category                       . "\t";
                $faqs_tsv .= $nc2_faq_question->display_sequence . "\t";
                $faqs_tsv .= $nc2_faq_question->insert_time      . "\t";
                $faqs_tsv .= $nc2_faq_question->question_name    . "\t";
                $faqs_tsv .= $question_answer                . "\t";

                // $faqs_ini .= "post_title[" . $nc2_faq_question->question_id . "] = \"" . str_replace('"', '', $nc2_faq_question->question_name) . "\"\n";
            }

            // FAQ の設定
            //Storage::put($this->getImportPath('faqs/faq_') . $this->zeroSuppress($nc2_faq->faq_id) . '.ini', $faqs_ini);
            $this->storagePut($this->getImportPath('faqs/faq_') . $this->zeroSuppress($nc2_faq->faq_id) . '.ini', $faqs_ini);

            // FAQ の記事
            //Storage::put($this->getImportPath('faqs/faq_') . $this->zeroSuppress($nc2_faq->faq_id) . '.tsv', $faqs_tsv);
            $faqs_tsv = $this->exportStrReplace($faqs_tsv, 'faqs');
            $this->storagePut($this->getImportPath('faqs/faq_') . $this->zeroSuppress($nc2_faq->faq_id) . '.tsv', $faqs_tsv);
        }
    }

    /**
     * NC2：リンクリスト（Linklist）の移行
     */
    private function nc2ExportLinklist($redo)
    {
        $this->putMonitor(3, "Start nc2ExportLinklist.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('linklists/'));
        }

        // NC2リンクリスト（Linklist）を移行する。
        $nc2_linklists = Nc2Linklist::orderBy('linklist_id')->get();

        // 空なら戻る
        if ($nc2_linklists->isEmpty()) {
            return;
        }

        // NC2リンクリスト（Linklist）のループ
        foreach ($nc2_linklists as $nc2_linklist) {
            $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc2_linklist->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // target 指定を取るために最初のブロックを参照（NC2 はブロック単位でtarget 指定していた。最初を移行する）
            $nc2_linklist_block = Nc2LinklistBlock::firstOrNew(
                ['linklist_id' => $nc2_linklist->linklist_id],
                ['target_blank_flag' => '0']
            );

            // (NC2)mark リストマーカー -> (Connect)type 表示形式 変換
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

            $type = $convert_types[$nc2_linklist_block->mark] ?? LinklistType::none;

            $linklists_ini = "";
            $linklists_ini .= "[linklist_base]\n";
            $linklists_ini .= "linklist_name = \"" . $nc2_linklist->linklist_name . "\"\n";
            // $linklists_ini .= "view_count = 10\n";
            $linklists_ini .= "type = " . $type . "\n";

            // NC2 情報
            $linklists_ini .= "\n";
            $linklists_ini .= "[source_info]\n";
            $linklists_ini .= "linklist_id = " . $nc2_linklist->linklist_id . "\n";
            $linklists_ini .= "room_id = " . $nc2_linklist->room_id . "\n";
            $linklists_ini .= "module_name = \"linklist\"\n";

            // NC2リンクリストで使っているカテゴリ（linklist_category）のみ移行する。
            $linklists_ini .= "\n";
            $linklists_ini .= "[categories]\n";
            // NC2リンクリストは自動的に「カテゴリなし」（名前変更不可）カテゴリが作成されるため、「カテゴリなし」は移行除外する。
            // ※ また、NC2では「カテゴリなし」１個だけだと、カテゴリを表示しない仕様
            // $nc2_linklist_categories = Nc2LinklistCategory::where('linklist_id', $nc2_linklist->linklist_id)->where('category_name', '!=','カテゴリなし')->orderBy('category_sequence')->get();
            $nc2_linklist_categories = Nc2LinklistCategory::
                select(
                    'linklist_category.category_id',
                    'linklist_category.category_name'
                )
                ->where('linklist_category.linklist_id', $nc2_linklist->linklist_id)
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

            foreach ($nc2_linklist_categories as $nc2_linklist_category) {
                $linklists_ini_originals .= "original_categories[" . $nc2_linklist_category->category_id . "] = \"" . $nc2_linklist_category->category_name . "\"\n";
            }
            if (!empty($linklists_ini_originals)) {
                $linklists_ini .= $linklists_ini_originals;
            }

            // NC2リンクリストの記事（linklist_link）を移行する。
            $nc2_linklist_links = Nc2LinklistLink::where('linklist_id', $nc2_linklist->linklist_id)->orderBy('link_sequence')->get();

            // リンクリストの記事はTSV でエクスポート
            // タイトル{\t}URL{\t}説明{\t}新規ウィンドウflag{\t}表示順
            $linklists_tsv = "";

            // NC2リンクリストの記事をループ
            // $linklists_ini .= "\n";
            // $linklists_ini .= "[linklist_link]\n";
            foreach ($nc2_linklist_links as $nc2_linklist_link) {
                // TSV 形式でエクスポート
                if (!empty($linklists_tsv)) {
                    $linklists_tsv .= "\n";
                }

                $category_obj  = $nc2_linklist_categories->firstWhere('category_id', $nc2_linklist_link->category_id);
                $category = "";
                if (!empty($category_obj)) {
                    $category  = $category_obj->category_name;
                }

                $linklists_tsv .= str_replace(array("\r", "\n", "\t"), "", $nc2_linklist_link->title)              . "\t";
                $linklists_tsv .= str_replace(array("\r", "\n", "\t"), "", $nc2_linklist_link->url)                . "\t";
                $linklists_tsv .= str_replace(array("\r", "\n", "\t"), " ", $nc2_linklist_link->description)       . "\t";
                $linklists_tsv .= $nc2_linklist_block->target_blank_flag                        . "\t";
                $linklists_tsv .= $nc2_linklist_link->link_sequence                             . "\t";
                $linklists_tsv .= $category;

                // $linklists_ini .= "post_title[" . $nc2_linklist_link->link_id . "] = \"" . str_replace('"', '', $nc2_linklist_link->title) . "\"\n";
            }

            // リンクリストの設定
            //Storage::put($this->getImportPath('linklists/linklist_') . $this->zeroSuppress($nc2_linklist->linklist_id) . '.ini', $linklists_ini);
            $this->storagePut($this->getImportPath('linklists/linklist_') . $this->zeroSuppress($nc2_linklist->linklist_id) . '.ini', $linklists_ini);

            // リンクリストの記事
            //Storage::put($this->getImportPath('linklists/linklist_') . $this->zeroSuppress($nc2_linklist->linklist_id) . '.tsv', $linklists_tsv);
            $this->storagePut($this->getImportPath('linklists/linklist_') . $this->zeroSuppress($nc2_linklist->linklist_id) . '.tsv', $linklists_tsv);
        }
    }

    /**
     * NC2：汎用データベース（Databases）の移行
     */
    private function nc2ExportMultidatabase($redo)
    {
        $this->putMonitor(3, "Start nc2ExportMultidatabase.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('databases/'));
        }

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

        // nc2の全ユーザ取得
        $nc2_users = Nc2User::get();

        // NC2汎用データベース（Multidatabase）のループ
        foreach ($nc2_multidatabases as $nc2_multidatabase) {
            $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc2_multidatabase->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

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

            // この汎用データベースが配置されている最初のページオブジェクトを取得しておく
            // WYSIWYG で相対パスを絶対パスに変換する際に、ページの固定URL が必要になるため。
            $nc2_page = null;
            if (!empty($nc2_multidatabase_block)) {
                $nc2_block = Nc2Block::where('block_id', $nc2_multidatabase_block->block_id)->first();
            }
            if (!empty($nc2_block)) {
                $nc2_page = Nc2Page::where('page_id', $nc2_block->page_id)->first();
            }

            // NC2 情報
            $multidatabase_ini .= "\n";
            $multidatabase_ini .= "[source_info]\n";
            $multidatabase_ini .= "multidatabase_id = " . $nc2_multidatabase->multidatabase_id . "\n";
            $multidatabase_ini .= "room_id = " . $nc2_multidatabase->room_id . "\n";
            $multidatabase_ini .= "module_name = \"multidatabase\"\n";
            $multidatabase_ini .= "insert_time = \"" . $this->getCCDatetime($nc2_multidatabase->insert_time) . "\"\n";
            $multidatabase_ini .= "insert_user_name = \"" . $nc2_multidatabase->insert_user_name . "\"\n";
            $multidatabase_ini .= "insert_login_id = \"" . $this->getNc2LoginIdFromNc2UserId($nc2_users, $nc2_multidatabase->insert_user_id) . "\"\n";
            $multidatabase_ini .= "update_time = \"" . $this->getCCDatetime($nc2_multidatabase->update_time) . "\"\n";
            $multidatabase_ini .= "update_user_name = \"" . $nc2_multidatabase->update_user_name . "\"\n";
            $multidatabase_ini .= "update_login_id = \"" . $this->getNc2LoginIdFromNc2UserId($nc2_users, $nc2_multidatabase->update_user_id) . "\"\n";

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

            $tsv_header .= "status" . "\t" . "display_sequence" . "\t" . "posted_at" . "\t" . "created_at" . "\t" . "updated_at" . "\t" . "content_id";
            $tsv_cols['status'] = "";
            $tsv_cols['display_sequence'] = "";
            $tsv_cols['posted_at'] = "";
            $tsv_cols['insert_time'] = "";
            $tsv_cols['update_time'] = "";
            $tsv_cols['content_id'] = "";

            // データベースの記事
            $multidatabase_metadata_contents = Nc2MultidatabaseMetadataContent::select(
                'multidatabase_metadata_content.*',
                'multidatabase_metadata.type',
                'multidatabase_content.agree_flag',
                'multidatabase_content.temporary_flag',
                'multidatabase_content.display_sequence as content_display_sequence',
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
                        $tsv_record['posted_at'] = $this->getCCDatetime($old_metadata_content->multidatabase_content_insert_time);
                        // 登録日時、更新日時
                        $tsv_record['insert_time'] = $this->getCCDatetime($old_metadata_content->multidatabase_content_insert_time);
                        $tsv_record['update_time'] = $this->getCCDatetime($old_metadata_content->multidatabase_content_update_time);
                        // NC2 レコードを示すID
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
                        // NC2 のアップロードID 抜き出し
                        $nc2_uploads_id = str_replace('?action=multidatabase_action_main_filedownload&upload_id=', '', $content);
                        // uploads.ini からファイルを探す
                        if (array_key_exists('uploads', $this->uploads_ini) && array_key_exists('upload', $this->uploads_ini['uploads']) && array_key_exists($nc2_uploads_id, $this->uploads_ini['uploads']['upload'])) {
                            if (array_key_exists($nc2_uploads_id, $this->uploads_ini) && array_key_exists('temp_file_name', $this->uploads_ini[$nc2_uploads_id])) {
                                $content = '../../uploads/' . $this->uploads_ini[$nc2_uploads_id]['temp_file_name'];
                            } else {
                                $this->putMonitor(3, "No Match uploads_ini array_key_exists temp_file_name.", "nc2_uploads_id = " . $nc2_uploads_id);
                            }
                        } else {
                            $this->putMonitor(3, "No Match uploads_ini array_key_exists uploads_ini_uploads_upload.", "nc2_uploads_id = " . $nc2_uploads_id);
                        }
                    } else {
                        $this->putMonitor(3, "No Match content strpos. :". $content);
                    }
                } elseif ($multidatabase_metadata_content->type == 6) {
                    // WYSIWYG
                    $content = $this->nc2Wysiwyg(null, null, null, null, $content, 'multidatabase', $nc2_page);
                } elseif ($multidatabase_metadata_content->type == 9) {
                    // 日付型
                    if (!empty($content) && strlen($content) == 14) {
                        $content = $this->getCCDatetime($content);
                    }
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
                $tsv_record['posted_at'] = $this->getCCDatetime($old_metadata_content->multidatabase_content_insert_time);
                // 登録日時、更新日時
                $tsv_record['insert_time'] = $this->getCCDatetime($old_metadata_content->multidatabase_content_insert_time);
                $tsv_record['update_time'] = $this->getCCDatetime($old_metadata_content->multidatabase_content_update_time);
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
     * NC2：登録フォーム（Registration）の移行
     */
    private function nc2ExportRegistration($redo)
    {
        $this->putMonitor(3, "Start nc2ExportRegistration.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('forms/'));
        }

        // NC2登録フォーム（Registration）を移行する。
        $nc2_export_where_registration_ids = $this->getMigrationConfig('forms', 'nc2_export_where_registration_ids');

        if (empty($nc2_export_where_registration_ids)) {
            $nc2_registrations = Nc2Registration::orderBy('registration_id')->get();
        } else {
            $nc2_registrations = Nc2Registration::whereIn('registration_id', $nc2_export_where_registration_ids)->orderBy('registration_id')->get();
        }

        // 空なら戻る
        if ($nc2_registrations->isEmpty()) {
            return;
        }

        // NC2登録フォーム（Registration）のループ
        foreach ($nc2_registrations as $nc2_registration) {
            $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc2_registration->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // 対象外指定があれば、読み飛ばす
            if ($this->isOmmit('forms', 'export_ommit_registration_ids', $nc2_registration->registration_id)) {
                continue;
            }

            $registration_id = $nc2_registration->registration_id;
            $regist_control_flag = $nc2_registration->period ? 1 : 0;
            $regist_to =  $nc2_registration->period ? $this->getCCDatetime($nc2_registration->period) : '';

            // 登録フォーム設定
            $registration_ini = "";
            $registration_ini .= "[form_base]\n";
            $registration_ini .= "forms_name = \""        . $nc2_registration->registration_name . "\"\n";
            $registration_ini .= "mail_send_flag = "      . $nc2_registration->mail_send . "\n";
            $registration_ini .= "mail_send_address = \"" . $nc2_registration->rcpt_to . "\"\n";
            $registration_ini .= "user_mail_send_flag = " . $nc2_registration->regist_user_send . "\n";
            $registration_ini .= "mail_subject = \""      . $nc2_registration->mail_subject . "\"\n";
            $registration_ini .= "mail_format = \""       . str_replace("\n", '\n', $nc2_registration->mail_body) . "\"\n";
            $registration_ini .= "data_save_flag = 1\n";
            $registration_ini .= "after_message = \""     . str_replace("\n", '\n', $nc2_registration->accept_message) . "\"\n";
            $registration_ini .= "numbering_use_flag = 0\n";
            $registration_ini .= "numbering_prefix = null\n";
            $registration_ini .= "regist_control_flag = " . $regist_control_flag. "\n";
            $registration_ini .= "regist_to = \""         . $regist_to . "\"\n";

            // NC2 情報
            $registration_ini .= "\n";
            $registration_ini .= "[source_info]\n";
            $registration_ini .= "registration_id = " . $nc2_registration->registration_id . "\n";
            $registration_ini .= "active_flag = "     . $nc2_registration->active_flag . "\n";
            $registration_ini .= "room_id = "         . $nc2_registration->room_id . "\n";
            $registration_ini .= "module_name = \"registration\"\n";

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
            if ($this->hasMigrationConfig('forms', 'nc2_export_registration_data', true)) {
                // 対象外指定があれば、読み飛ばす
                if ($this->isOmmit('forms', 'export_ommit_registration_data_ids', $nc2_registration->registration_id)) {
                    continue;
                }

                // データ部
                $registration_data_header = "[form_inputs]\n";
                $registration_data = "";
                $registration_item_datas = Nc2RegistrationItemData::select('registration_item_data.*')
                                                                 ->join('registration_item', function ($join) {
                                                                     $join->on('registration_item.registration_id', '=', 'registration_item_data.registration_id')
                                                                          ->on('registration_item.item_id', '=', 'registration_item_data.item_id');
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
     * NC2：新着情報（Whatsnew）の移行
     */
    private function nc2ExportWhatsnew($redo)
    {
        $this->putMonitor(3, "Start nc2ExportWhatsnew.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('whatsnews/'));
        }

        // NC2新着情報（Whatsnew）を移行する。
        $nc2_whatsnew_blocks_query = Nc2WhatsnewBlock::select('whatsnew_block.*', 'blocks.block_name', 'pages.page_name')
                                                     ->join('blocks', 'blocks.block_id', '=', 'whatsnew_block.block_id');
        $nc2_whatsnew_blocks_query->join('pages', function ($join) {
            $join->on('pages.page_id', '=', 'blocks.page_id')
                 ->where('pages.private_flag', '=', 0);
        });
        $nc2_whatsnew_blocks = $nc2_whatsnew_blocks_query->orderBy('block_id')->get();

        // 空なら戻る
        if ($nc2_whatsnew_blocks->isEmpty()) {
            return;
        }

        // NC2新着情報（Whatsnew）のループ
        foreach ($nc2_whatsnew_blocks as $nc2_whatsnew_block) {
            $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc2_whatsnew_block->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            $whatsnew_block_id = $nc2_whatsnew_block->block_id;

            // 新着情報設定
            $whatsnew_ini = "";
            $whatsnew_ini .= "[whatsnew_base]\n";

            // 新着情報の名前は、ブロックタイトルがあればブロックタイトル。なければページ名＋「の新着情報」。
            $whatsnew_name = '無題';
            if (!empty($nc2_whatsnew_block->page_name)) {
                $whatsnew_name = $nc2_whatsnew_block->page_name;
            }
            if (!empty($nc2_whatsnew_block->block_name)) {
                $whatsnew_name = $nc2_whatsnew_block->block_name;
            }

            $whatsnew_ini .= "whatsnew_name = \""  . $whatsnew_name . "\"\n";
            $whatsnew_ini .= "view_pattern = "     . ($nc2_whatsnew_block->display_flag == 1 ? 0 : 1) . "\n"; // NC2: 0=日数, 1=件数 Connect-CMS: 0=件数, 1=日数
            $whatsnew_ini .= "count = "            . $nc2_whatsnew_block->display_number . "\n";
            $whatsnew_ini .= "days = "             . $nc2_whatsnew_block->display_days . "\n";
            $whatsnew_ini .= "rss = "              . $nc2_whatsnew_block->allow_rss_feed . "\n";
            $whatsnew_ini .= "rss_count = "        . $nc2_whatsnew_block->display_number . "\n";
            $whatsnew_ini .= "view_posted_name = " . $nc2_whatsnew_block->display_user_name    . "\n";
            $whatsnew_ini .= "view_posted_at = "   . $nc2_whatsnew_block->display_insert_time . "\n";

            // 対象のプラグインを取得（Connect-CMS にまだないものは除外＆ログ出力）
            $display_modules = explode(',', $nc2_whatsnew_block->display_modules);
            $nc2_modules = Nc2Modules::whereIn('module_id', $display_modules)->orderBy('module_id', 'asc')->get();
            $whatsnew_ini .= "target_plugins = \"" . $this->nc2GetModuleNames($nc2_modules->pluck('action_name')) . "\"\n";

            $whatsnew_ini .= "frame_select = 0\n";

            // NC2 情報
            $whatsnew_ini .= "\n";
            $whatsnew_ini .= "[source_info]\n";
            $whatsnew_ini .= "whatsnew_block_id = " . $whatsnew_block_id . "\n";
            $whatsnew_ini .= "room_id = "           . $nc2_whatsnew_block->room_id . "\n";
            $whatsnew_ini .= "module_name = \"whatsnew\"\n";

            // 新着情報の設定を出力
            //Storage::put($this->getImportPath('whatsnews/whatsnew_') . $this->zeroSuppress($whatsnew_block_id) . '.ini', $whatsnew_ini);
            $this->storagePut($this->getImportPath('whatsnews/whatsnew_') . $this->zeroSuppress($whatsnew_block_id) . '.ini', $whatsnew_ini);
        }
    }

    /**
     * NC2：キャビネット（キャビネット）の移行
     */
    private function nc2ExportCabinet($redo)
    {
        $this->putMonitor(3, "Start nc2ExportCabinet.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('cabinets/'));
        }

        // NC2キャビネット（Cabinet）を移行する。
        $where_cabinet_ids = $this->getMigrationConfig('cabinets', 'nc2_export_where_cabinet_ids');
        if (empty($where_cabinet_ids)) {
            $cabinet_manages = Nc2CabinetManage::orderBy('cabinet_id')->get();
        } else {
            $cabinet_manages = Nc2CabinetManage::whereIn('cabinet_id', $where_cabinet_ids)->orderBy('cabinet_id')->get();
        }

        // 空なら戻る
        if ($cabinet_manages->isEmpty()) {
            return;
        }

        // NC2キャビネット（Cabinet）のループ
        foreach ($cabinet_manages as $cabinet_manage) {
            $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
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

            // NC2 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "cabinet_id = " . $cabinet_manage->cabinet_id . "\n";
            $ini .= "room_id = " . $cabinet_manage->room_id . "\n";
            $ini .= "module_name = \"cabinet\"\n";

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
                $tsv .= $cabinet_file['comment'];

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
     * NC2：カウンター（カウンター）の移行
     */
    private function nc2ExportCounter($redo)
    {
        $this->putMonitor(3, "Start nc2ExportCounter.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('counters/'));
        }

        // NC2カウンター（Counter）を移行する。
        $where_counter_block_ids = $this->getMigrationConfig('counters', 'nc2_export_where_counter_block_ids');
        if (empty($where_counter_block_ids)) {
            $nc2_counters = Nc2Counter::orderBy('block_id')->get();
        } else {
            $nc2_counters = Nc2Counter::whereIn('block_id', $where_counter_block_ids)->orderBy('block_id')->get();
        }

        // 空なら戻る
        if ($nc2_counters->isEmpty()) {
            return;
        }

        // NC2カウンター（Counter）のループ
        foreach ($nc2_counters as $nc2_counter) {
            $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($room_ids) && !in_array($nc2_counter->room_id, $room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // (NC2)show_type -> (Connect)design_type 変換
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
            $design_type = $convert_design_types[$nc2_counter->show_type] ?? CounterDesignType::numeric;

            // カウンター設定
            $ini = "";
            $ini .= "[counter_base]\n";
            // カウント数
            $ini .= "counter_num = " . $nc2_counter->counter_num . "\n";
            // 表示する桁数
            // $ini .= "counter_digit = " .  $nc2_counter->counter_digit . "\n";

            $ini .= "design_type = " . $design_type . "\n";

            // 文字(前)
            $ini .= "show_char_before = " . $nc2_counter->show_char_before . "\n";
            // 文字(後)
            $ini .= "show_char_after = " . $nc2_counter->show_char_after . "\n";
            // 上記以外に表示したい文字
            // $ini .= "comment = " . $nc2_counter->comment . "\n";

            // NC2 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "counter_block_id = " . $nc2_counter->block_id . "\n";
            $ini .= "room_id = " . $nc2_counter->room_id . "\n";
            $ini .= "module_name = \"counter\"\n";

            // カウンターの設定を出力
            $this->storagePut($this->getImportPath('counters/counter_') . $this->zeroSuppress($nc2_counter->block_id) . '.ini', $ini);
        }
    }

    /**
     * NC2：カレンダー（カレンダー）の移行
     */
    private function nc2ExportCalendar($redo)
    {
        $this->putMonitor(3, "Start nc2ExportCalendar.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('calendars/'));
        }

        // ・NC2ルーム一覧とって、NC2予定データを移行する
        //   ※ ルームなしはありえない（必ずパブリックルームがあるため）
        // ・NC2カレンダーブロック（モジュール配置したブロック（どう見せるか、だけ。ここ無くても予定データある））を移行する。

        // NC2ルーム一覧を移行する。
        $nc2_export_private_room_calendar = $this->getMigrationConfig('calendars', 'nc2_export_private_room_calendar');
        if (empty($nc2_export_private_room_calendar)) {
            // プライベートルームをエクスポート（=移行）しない
            $nc2_page_rooms = Nc2Page::whereColumn('page_id', 'room_id')
                ->whereIn('space_type', [1, 2])     // 1:パブリックスペース, 2:グループスペース
                ->where('room_id', '!=', 2)         // 2:グループスペースを除外（枠だけでグループルームじゃないので除外）
                ->where('private_flag', 0)          // 0:プライベートルーム以外
                ->orderBy('room_id')
                ->get();
        } else {
            // プライベートルームをエクスポート（=移行）する
            $nc2_page_rooms = Nc2Page::whereColumn('page_id', 'room_id')
                ->whereIn('space_type', [1, 2])     // 1:パブリックスペース, 2:グループスペース
                ->where('room_id', '!=', 2)         // 2:グループスペースを除外（枠だけでグループルームじゃないので除外）
                ->orderBy('room_id')
                ->get();
        }

        // NC2権限設定（サイト全体で１設定のみ）. インストール時は空。権限設定でOK押さないとデータできない。
        $nc2_calendar_manages = Nc2CalendarManage::orderBy('room_id')->get();

        $nc2_export_room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');

        // ルームでループ（NC2カレンダーはルーム単位でエクスポート）
        foreach ($nc2_page_rooms as $nc2_page_room) {

            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($nc2_export_room_ids) && !in_array($nc2_page_room->room_id, $nc2_export_room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // カレンダー設定
            $ini = "";
            $ini .= "[calendar_base]\n";

            // NC2 権限設定
            $nc2_calendar_manage = $nc2_calendar_manages->firstWhere('room_id', $nc2_page_room->room_id);
            $ini .= "\n";
            $ini .= "[calendar_manage]\n";
            if (is_null($nc2_calendar_manage)) {
                // データなしは 4:主担。 ここに全会員ルームのデータは入ってこないため、これでOK
                $ini .= "add_authority_id = 4\n";
                // フラグは必ず1
                // $ini .= "use_flag = 1\n";
            } else {
                // 予定を追加できる権限. 2:主担,モデレータ,一般  3:主担,モデレータ  4:主担  5:なし（全会員のみ設定可能）
                $ini .= "add_authority_id = " . $nc2_calendar_manage->add_authority_id . "\n";
                // フラグ. 1:使う
                // $ini .= "use_flag = " . $nc2_calendar_manage->use_flag . "\n";
            }

            // NC2 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "room_id = " . $nc2_page_room->room_id . "\n";
            // ルーム名
            $ini .= "room_name = '" . $nc2_page_room->page_name . "'\n";
            // プライベートフラグ, 1:プライベートルーム, 0:プライベートルーム以外
            $ini .= "private_flag = " . $nc2_page_room->private_flag . "\n";
            // スペースタイプ, 1:パブリックスペース, 2:グループスペース
            $ini .= "space_type = " . $nc2_page_room->space_type . "\n";
            $ini .= "module_name = \"calendar\"\n";


            // カラムのヘッダー及びTSV 行毎の枠準備
            $tsv_header = "calendar_id" . "\t" . "plan_id" . "\t" . "room_id" . "\t" . "user_id" . "\t" . "user_name" . "\t" . "title" . "\t" ."title_icon" . "\t" .
                "allday_flag" . "\t" . "start_date" . "\t" . "start_time" . "\t" . "start_time_full" . "\t" . "end_date" . "\t" . "end_time" . "\t" .
                "end_time_full" . "\t" . "timezone_offset" . "\t" . "link_module" . "\t" . "link_id" . "\t" . "link_action_name" . "\t" .
                // NC2 calendar_plan_details
                "location" . "\t" . "contact" . "\t" . "description" . "\t" . "rrule" . "\t" .
                // NC2 calendar_plan 登録日・更新日
                "insert_time" . "\t" . "update_time" . "\t" .
                // CC 状態
                "status";

            // NC2 calendar_plan
            $tsv_cols['calendar_id'] = "";
            $tsv_cols['plan_id'] = "";
            $tsv_cols['room_id'] = "";
            $tsv_cols['user_id'] = "";
            $tsv_cols['user_name'] = "";
            $tsv_cols['title'] = "";
            $tsv_cols['title_icon'] = "";
            $tsv_cols['allday_flag'] = "";
            $tsv_cols['start_date'] = "";
            $tsv_cols['start_time'] = "";
            $tsv_cols['start_time_full'] = "";
            $tsv_cols['end_date'] = "";
            $tsv_cols['end_time'] = "";
            $tsv_cols['end_time_full'] = "";
            $tsv_cols['timezone_offset'] = "";
            $tsv_cols['link_module'] = "";
            $tsv_cols['link_id'] = "";
            $tsv_cols['link_action_name'] = "";

            // NC2 calendar_plan_details
            // 場所
            $tsv_cols['location'] = "";
            // 連絡先
            $tsv_cols['contact'] = "";
            // 内容
            $tsv_cols['description'] = "";
            // 繰り返し条件
            $tsv_cols['rrule'] = "";

            // NC2 calendar_plan 登録日・更新日
            $tsv_cols['insert_time'] = "";
            $tsv_cols['update_time'] = "";

            // CC 状態
            $tsv_cols['status'] = "";

            // カレンダーの予定 calendar_plan
            $calendar_plans = Nc2CalendarPlan::
                leftjoin('calendar_plan_details', function ($join) {
                    $join->on('calendar_plan.plan_id', '=', 'calendar_plan_details.plan_id')
                        ->whereColumn('calendar_plan.room_id', 'calendar_plan_details.room_id');
                })
                ->where('calendar_plan.room_id', $nc2_page_room->room_id)
                ->orderBy('calendar_plan.calendar_id', 'asc')
                ->get();

            // カラムデータのループ
            Storage::delete($this->getImportPath('calendars/calendar_room_') . $this->zeroSuppress($nc2_page_room->room_id) . '.tsv');

            $tsv = '';
            $tsv .= $tsv_header . "\n";

            foreach ($calendar_plans as $calendar_plan) {

                // 初期化
                $tsv_record = $tsv_cols;

                // NC2 calendar_plan
                $tsv_record['calendar_id'] = $calendar_plan->calendar_id;
                $tsv_record['plan_id'] = $calendar_plan->plan_id;
                $tsv_record['room_id'] = $calendar_plan->room_id;
                $tsv_record['user_id'] = $calendar_plan->user_id;
                $tsv_record['user_name'] = $calendar_plan->user_name;
                $tsv_record['title'] = $calendar_plan->title;
                $tsv_record['title_icon'] = $calendar_plan->title_icon;
                $tsv_record['allday_flag'] = $calendar_plan->allday_flag;

                // 予定開始日時
                // Carbon()で処理。必須値のため基本値がある想定で、timezone_offset で時間加算して予定時間を算出
                $start_time_full = (new Carbon($calendar_plan->start_time_full))->addHour($calendar_plan->timezone_offset);
                // $tsv_record['start_date'] = $calendar_plan->start_date;
                // $tsv_record['start_time'] = $calendar_plan->start_time;
                $tsv_record['start_date'] = $start_time_full->format('Y-m-d');
                $tsv_record['start_time'] = $start_time_full->format('H:i:s');
                $tsv_record['start_time_full'] = $start_time_full;

                // 予定終了日時
                // Carbon()で処理。必須値のため基本値がある想定で、timezone_offset で時間加算して予定時間を算出
                $end_time_full = (new Carbon($calendar_plan->end_time_full))->addHour($calendar_plan->timezone_offset);
                if ($calendar_plan->allday_flag == 1) {
                    // 全日で終了日時の変換対応. -1日する。
                    //
                    // ・NC2 で登録できる開始時間：0:00～23:55 （24:00ないため、こっちは対応不要）
                    // ・NC2 で登録できる終了時間：0:05～24:00 （0:00に設定しても前日24:00に自動変換される）
                    // ・Connect 終了時間 0:00～23:59
                    // 24:00はデータ上0:00のため、0:00から-1日して23:59に変換する。
                    //
                    // ※ NC2の全日１日は、        20210810 150000（+9時間）～20210811 150000（+9時間）←当日～翌日
                    //    Connect-CMSの全日１日は、2021-08-11 00:00:00～2021-08-11 00:00:00 ←前後同じ, 時間は設定できず 00:00:00 で登録される。
                    //    そのため、2021/08/11 0:00～2021/08/12 0:00 を 2021/08/11 0:00～2021/08/11 0:00に変換する。

                    // -1日
                    $end_time_full = $end_time_full->subDay();
                } elseif ($end_time_full->format('H:i:s') == '00:00:00') {
                    // 全日以外で終了日時が0:00の変換対応. -1分する。
                    // ※ 例えばNC2の「時間指定」で10:00～24:00という予定に対応して、10:00～23:59に終了時間を変換する

                    // -1分
                    $end_time_full = $end_time_full->subMinute();
                }
                // $tsv_record['end_date'] = $calendar_plan->end_date;
                // $tsv_record['end_time'] = $calendar_plan->end_time;
                $tsv_record['end_date'] = $end_time_full->format('Y-m-d');
                $tsv_record['end_time'] = $end_time_full->format('H:i:s');
                $tsv_record['end_time_full'] = $end_time_full;

                $tsv_record['timezone_offset'] = $calendar_plan->timezone_offset;
                $tsv_record['link_module'] = $calendar_plan->link_module;
                $tsv_record['link_id'] = $calendar_plan->link_id;
                $tsv_record['link_action_name'] = $calendar_plan->link_action_name;

                // NC2 calendar_plan_details（plan_id, room_idあり）
                // 場所
                $tsv_record['location'] = $calendar_plan->location;
                // 連絡先
                $tsv_record['contact'] = $calendar_plan->contact;
                // 内容 [WYSIWYG]
                $tsv_record['description'] = $this->nc2Wysiwyg(null, null, null, null, $calendar_plan->description, 'calendar');

                // 繰り返し条件
                $tsv_record['rrule'] = $calendar_plan->rrule;

                // NC2 calendar_plan 登録日・更新日
                $tsv_record['insert_time'] = $this->getCCDatetime($calendar_plan->insert_time);
                $tsv_record['update_time'] = $this->getCCDatetime($calendar_plan->update_time);

                // NC2カレンダー予定は公開のみ
                $tsv_record['status'] = 0;

                $tsv .= implode("\t", $tsv_record) . "\n";
            }

            // データ行の書き出し
            $tsv = $this->exportStrReplace($tsv, 'calendars');
            $this->storageAppend($this->getImportPath('calendars/calendar_room_') . $this->zeroSuppress($nc2_page_room->room_id) . '.tsv', $tsv);

            // カレンダーの設定を出力
            $this->storagePut($this->getImportPath('calendars/calendar_room_') . $this->zeroSuppress($nc2_page_room->room_id) . '.ini', $ini);
        }


        // NC2全会員 room_id=0（nc2_page にデータないため手動で設定）
        $all_users_room_id = 0;

        // ルーム指定があれば、指定されたルームのみ処理する。
        if (empty($nc2_export_room_ids) || in_array($all_users_room_id, $nc2_export_room_ids)) {

            // カレンダー設定
            $ini = "";
            $ini .= "[calendar_base]\n";

            // NC2 権限設定
            $nc2_calendar_manage = $nc2_calendar_manages->firstWhere('room_id', $all_users_room_id);
            $ini .= "\n";
            $ini .= "[calendar_manage]\n";
            if (is_null($nc2_calendar_manage)) {
                // 全会員のデータなしは 5:なし（全会員のみ設定可能）
                $ini .= "add_authority_id = 5\n";
                // フラグは必ず1
                // $ini .= "use_flag = 1\n";
            } else {
                // 予定を追加できる権限. 2:主担,モデレータ,一般  3:主担,モデレータ  4:主担  5:なし（全会員のみ設定可能）
                $ini .= "add_authority_id = " . $nc2_calendar_manage->add_authority_id . "\n";
                // フラグ. 1:使う
                // $ini .= "use_flag = " . $nc2_calendar_manage->use_flag . "\n";
            }

            // NC2 情報
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


        // NC2カレンダーブロック（モジュール配置したブロック（どう見せるか、だけ。ここ無くても予定データある））を移行する。
        $where_calendar_block_ids = $this->getMigrationConfig('calendars', 'nc2_export_where_calendar_block_ids');
        if (empty($where_calendar_block_ids)) {
            $nc2_calendar_blocks = Nc2CalendarBlock::orderBy('block_id')->get();
        } else {
            $nc2_calendar_blocks = Nc2CalendarBlock::whereIn('block_id', $where_calendar_block_ids)->orderBy('block_id')->get();
        }

        // 空なら戻る
        if ($nc2_calendar_blocks->isEmpty()) {
            return;
        }

        // NC2 指定ルームのみ表示 nc2_calendar_select_room
        // if (empty($where_calendar_block_ids)) {
        //     $nc2_calendar_select_rooms = Nc2CalendarSelectRoom::orderBy('block_id')->get();
        // } else {
        //     $nc2_calendar_select_rooms = Nc2CalendarSelectRoom::whereIn('block_id', $where_calendar_block_ids)->orderBy('block_id')->get();
        // }

        // NC2カレンダーブロックのループ
        foreach ($nc2_calendar_blocks as $nc2_calendar_block) {

            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($nc2_export_room_ids) && !in_array($nc2_page_room->room_id, $nc2_export_room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // NC2 カレンダーブロック（表示方法）設定
            $ini = "";
            $ini .= "[calendar_block]\n";
            // 表示方法
            $ini .= "display_type = " . $nc2_calendar_block->display_type . "\n";
            // 開始位置
            // $ini .= "start_pos = " .  $nc2_calendar_block->start_pos . "\n";
            // 表示日数
            // $ini .= "display_count = " . $nc2_calendar_block->display_count . "\n";
            // 指定したルームのみ表示する 1:ルーム指定する 0:指定しない
            // $ini .= "select_room = " . $nc2_calendar_block->select_room . "\n";
            // [不明] 画面に該当項目なし。プライベートルームにカレンダー配置しても 0 だった。
            // $ini .= "myroom_flag = " . $nc2_calendar_block->myroom_flag . "\n";

            // NC2 指定ルームのみ表示
            // $ini .= "\n";
            // $ini .= "[calendar_select_room]\n";
            // foreach ($nc2_calendar_select_rooms as $nc2_calendar_select_room) {
            //     $ini .= "room_id[] = " . $nc2_calendar_select_room->room_id . "\n";
            // }

            // NC2 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "calendar_block_id = " . $nc2_calendar_block->block_id . "\n";
            $ini .= "room_id = " . $nc2_calendar_block->room_id . "\n";
            $ini .= "module_name = \"calendar\"\n";

            // カレンダーの設定を出力
            $this->storagePut($this->getImportPath('calendars/calendar_block_') . $this->zeroSuppress($nc2_calendar_block->block_id) . '.ini', $ini);
        }
    }

    /**
     * NC2：スライダー（スライダー）の移行
     */
    private function nc2ExportSlides($redo)
    {
        $this->putMonitor(3, "Start nc2ExportSlides.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('slideshows/'));
        }

        // NC2スライダー（Slideshow）を移行する。
        $where_slideshow_block_ids = $this->getMigrationConfig('slideshows', 'nc2_export_where_slideshow_block_ids');
        if (empty($where_slideshow_block_ids)) {
            $nc2_slideshows = Nc2Slides::orderBy('block_id')->get();
        } else {
            $nc2_slideshows = Nc2Slides::whereIn('block_id', $where_slideshow_block_ids)->orderBy('block_id')->get();
        }

        // 空なら戻る
        if ($nc2_slideshows->isEmpty()) {
            return;
        }

        // NC2スライダー（Slideshow）のループ
        foreach ($nc2_slideshows as $nc2_slideshow) {
            $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($room_ids) && !in_array($nc2_slideshow->room_id, $room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // スライダー設定
            $ini = "";
            $ini .= "[slideshow_base]\n";
            $ini .= "slideshow_speed = " . $nc2_slideshow->speed . "\n";
            $ini .= "slideshow_pause = " . $nc2_slideshow->pause . "\n";
            // NC2 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "slideshows_block_id = " . $nc2_slideshow->block_id . "\n";
            $ini .= "room_id = " . $nc2_slideshow->room_id . "\n";
            $ini .= "module_name = \"slides\"\n";

            // 付与情報を移行する。
            $nc2_slides_urls = Nc2SlidesUrl::where('slides_id', $nc2_slideshow->slides_id)->orderBy('slides_url_id')->get();
            // TSV でエクスポート
            // image_path{\t}uploads_id{\t}link_url{\t}link_target{\t}caption{\t}display_flag{\t}display_sequence
            $slides_tsv = "";
            foreach ($nc2_slides_urls as $nc2_slides_url) {
                // TSV 形式でエクスポート
                if (!empty($slides_tsv)) {
                    $slides_tsv .= "\n";
                }
                $slides_tsv .= "\t"; //image_path
                $slides_tsv .= $nc2_slides_url->image_file_id. "\t";                        //uploads_id
                $slides_tsv .= $nc2_slides_url->url. "\t";                                  //link_url
                $slides_tsv .= ($nc2_slides_url->target_new == 0 ) ? "\t" : '_blank' . "\t";  //link_target
                $slides_tsv .= $nc2_slides_url->linkstr. "\t";                              //caption
                $slides_tsv .= $nc2_slides_url->view. "\t";                                 //display_flag
                $slides_tsv .= $nc2_slides_url->display_sequence. "\t";                     //display_sequence
            }
            // スライダーの設定を出力
            $this->storagePut($this->getImportPath('slideshows/slideshows_') . $this->zeroSuppress($nc2_slideshow->block_id) . '.ini', $ini);
            // スライダーの付与情報を出力
            $this->storagePut($this->getImportPath('slideshows/slideshows_') . $this->zeroSuppress($nc2_slideshow->block_id) . '.tsv', $slides_tsv);

        }
    }

    /**
     * NC2：シンプル動画の移行
     */
    private function nc2ExportSimplemovie($redo)
    {
        $this->putMonitor(3, "Start nc2ExportSimplemovie.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('simplemovie/'));
        }

        // NC2シンプル動画を移行する。
        $where_simplemovie_block_ids = $this->getMigrationConfig('simplemovie', 'nc2_export_where_simplemovie_block_ids');
        if (empty($where_simplemovie_block_ids)) {
            $nc2_simplemovies = Nc2Simplemovie::orderBy('block_id')->get();
        } else {
            $nc2_simplemovies = Nc2Simplemovie::whereIn('block_id', $where_simplemovie_block_ids)->orderBy('block_id')->get();
        }

        // 空なら戻る
        if ($nc2_simplemovies->isEmpty()) {
            return;
        }

        // NC2スライダー（Slideshow）のループ
        foreach ($nc2_simplemovies as $nc2_simplemovie) {
            $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (!empty($room_ids) && !in_array($nc2_simplemovie->room_id, $room_ids)) {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            // 動画が設定されていない場合はエクスポートしない
            if ($nc2_simplemovie->movie_upload_id == null) {
                continue;
            }

            // シンプル動画設定
            $ini = "";
            $ini .= "[simplemovie_base]\n";
            $ini .= "simplemovie_movie_upload_id = " . $nc2_simplemovie->movie_upload_id . "\n";
            $ini .= "simplemovie_movie_upload_id_request = " . $nc2_simplemovie->movie_upload_id_request . "\n";
            $ini .= "simplemovie_thumbnail_upload_id = " . $nc2_simplemovie->thumbnail_upload_id . "\n";
            $ini .= "simplemovie_thumbnail_upload_id_request = " . $nc2_simplemovie->thumbnail_upload_id_request . "\n";
            $ini .= "simplemovie_width = " . $nc2_simplemovie->width . "\n";
            $ini .= "simplemovie_height = " . $nc2_simplemovie->height . "\n";
            $ini .= "simplemovie_autoplay_flag = " . $nc2_simplemovie->autoplay_flag . "\n";
            $ini .= "simplemovie_embed_show_flag = " . $nc2_simplemovie->embed_show_flag . "\n";
            $ini .= "simplemovie_agree_flag = " . $nc2_simplemovie->agree_flag . "\n";
            // NC2 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "simplemovie_block_id = " . $nc2_simplemovie->block_id . "\n";
            $ini .= "room_id = " . $nc2_simplemovie->room_id . "\n";
            $ini .= "module_name = \"simplemovie\"\n";
            // シンプル動画の設定を出力
            $this->storagePut($this->getImportPath('simplemovie/simplemovie_') . $this->zeroSuppress($nc2_simplemovie->block_id) . '.ini', $ini);
        }
    }

    /**
     * NC2：施設予約の移行
     */
    private function nc2ExportReservation($redo)
    {
        $this->putMonitor(3, "Start nc2ExportReservation.");

        // データクリア
        if ($redo === true) {
            // 移行用ファイルの削除
            Storage::deleteDirectory($this->getImportPath('reservations/'));
        }

        // ・NC2ルーム一覧とって、NC2予定データを移行する
        //   ※ ルームなしはありえない（必ずパブリックルームがあるため）
        // ・NC2施設予約ブロック（モジュール配置したブロック（どう見せるか、だけ。ここ無くても予定データある））を移行する。

        // 施設カテゴリ
        // ----------------------------------------------------
        $nc2_reservation_categories = Nc2ReservationCategory::orderBy('display_sequence')->get();
        foreach ($nc2_reservation_categories as $nc2_reservation_category) {
            // NC2 施設カテゴリ設定
            $ini = "";
            $ini .= "[reservation_category]\n";
            // カテゴリ名
            $ini .= "category_name = \"" . $nc2_reservation_category->category_name . "\"\n";

            // 表示順
            $ini .= "display_sequence = " . $nc2_reservation_category->display_sequence . "\n";

            // NC2 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "category_id = " . $nc2_reservation_category->category_id . "\n";
            $ini .= "module_name = \"reservation\"\n";

            // 施設予約の設定を出力
            $this->storagePut($this->getImportPath('reservations/reservation_category_') . $this->zeroSuppress($nc2_reservation_category->category_id) . '.ini', $ini);
        }

        // NC2施設のエクスポート
        // ----------------------------------------------------
        $where_reservation_location_ids = $this->getMigrationConfig('reservations', 'nc2_export_where_reservation_location_ids');
        if (empty($where_reservation_location_ids)) {
            $nc2_reservation_locations = Nc2ReservationLocation::orderBy('category_id')->orderBy('display_sequence')->get();
            $nc2_reservation_location_details = Nc2ReservationLocationDetail::orderBy('location_id')->get();
        } else {
            $nc2_reservation_locations = Nc2ReservationLocation::whereIn('location_id', $where_reservation_location_ids)->orderBy('category_id')->orderBy('display_sequence')->get();
            $nc2_reservation_location_details = Nc2ReservationLocationDetail::whereIn('location_id', $where_reservation_location_ids)->orderBy('location_id')->get();
        }

        // nc2の全ユーザ取得
        $nc2_users = Nc2User::get();

        foreach ($nc2_reservation_locations as $nc2_reservation_location) {
            // NC2 施設カテゴリ設定
            $ini = "";
            $ini .= "[reservation_location]\n";
            // カテゴリID
            $ini .= "category_id = " . $nc2_reservation_location->category_id . "\n";
            // 施設名
            $ini .= "location_name = \"" . $nc2_reservation_location->location_name . "\"\n";
            // （画面に対象となる項目なし）active_flag
            // $ini .= "active_flag = " . $nc2_reservation_location->active_flag . "\n";

            // 予約できる権限 4:主担のみ, 3:モデレータ以上, 2:一般以上
            // $ini .= "add_authority = " . $nc2_reservation_location->add_authority . "\n";
            if ($nc2_reservation_location->add_authority == 4 || $nc2_reservation_location->add_authority == 3) {
                $ini .= "is_limited_by_role = " . ReservationLimitedByRole::limited . "\n";
            } else {
                $ini .= "is_limited_by_role = " . ReservationLimitedByRole::not_limited . "\n";
            }

            // 利用曜日 例）SU,MO,TU,WE,TH,FR,SA
            // $ini .= "time_table = " . $nc2_reservation_location->time_table . "\n";
            $time_tables = explode(',', $nc2_reservation_location->time_table);
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

            $start_time = new Carbon($nc2_reservation_location->start_time);
            $start_time->addHour($nc2_reservation_location->timezone_offset); // 例）9.0 = 9時間後
            $end_time = new Carbon($nc2_reservation_location->end_time);
            $end_time->addHour($nc2_reservation_location->timezone_offset);

            // 開始～終了 の差が 24h なら「利用時間の制限なし」
            if ($start_time->diffInHours($end_time) == 24) {
                // 24:00 は0:00表示になってしまうため、23:55に修正
                $end_time = new Carbon($end_time->format('Ymd') . '235500');
                // 制限なし
                $ini .= "is_time_control = 0\n";
            } else {
                // 制限あり
                $ini .= "is_time_control = 1\n";
            }

            // 利用時間-開始 例）20220203150000 = yyyyMMddhhiiss = 15(+9) = 24:00
            $ini .= "start_time = " . $start_time->format('H:i:s') . "\n";
            // 利用時間-終了 例）20220204150000 = yyyyMMddhhiiss = 15(+9) = 翌日24:00
            $ini .= "end_time = " . $end_time->format('H:i:s') . "\n";
            // （画面に対象となる項目なし）duplication_flag、例) 0、※ DBから直接 1 にすると予約重複可能になるが、知られてない
            // $ini .= "duplication_flag = " . $nc2_reservation_location->duplication_flag . "\n";
            // 個人的な予約を受け付ける
            // $ini .= "use_private_flag = " . $nc2_reservation_location->use_private_flag . "\n";
            // 個人的な予約で使用する権限。0:会員の権限、1:ルームでの権限
            // $ini .= "use_auth_flag = " . $nc2_reservation_location->use_auth_flag . "\n";
            // 全てのルームから予約を受け付ける。1:ON、0:OFF
            // $ini .= "allroom_flag = " . $nc2_reservation_location->allroom_flag . "\n";
            // 並び順
            $ini .= "display_sequence = " . $nc2_reservation_location->display_sequence . "\n";

            $nc2_reservation_location_detail = $nc2_reservation_location_details->firstWhere('location_id', $nc2_reservation_location->location_id);
            $nc2_reservation_location_detail = $nc2_reservation_location_detail ?? new Nc2ReservationLocationDetail();

            // 施設管理者
            $ini .= "facility_manager_name = \"" . $nc2_reservation_location_detail->contact . "\"\n";
            // 補足
            $ini .= "supplement = \"" . str_replace('"', '\"', $nc2_reservation_location_detail->description) . "\"\n";

            // NC2 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "location_id = " . $nc2_reservation_location->location_id . "\n";
            $ini .= "module_name = \"reservation\"\n";

            // 施設予約の予約
            // ----------------------------------------------------
            // カラムのヘッダー及びTSV 行毎の枠準備
            $tsv_header = "reserve_id" . "\t" . "reserve_details_id" . "\t" . "location_id" . "\t" . "room_id" . "\t" ."user_id" . "\t" . "user_name" . "\t" . "calendar_id" . "\t" . "title" . "\t" ."title_icon" . "\t" .
                "allday_flag" . "\t" . "start_date" . "\t" . "start_time" . "\t" . "start_time_full" . "\t" . "end_date" . "\t" . "end_time" . "\t" .
                "end_time_full" . "\t" . "timezone_offset" . "\t" .
                // NC2 reservation_reserve_details
                "contact" . "\t" . "description" . "\t" . "rrule" . "\t" .
                // NC2 reservation_reserve システム項目
                "insert_time" . "\t" . "insert_user_name" . "\t" . "insert_login_id" . "\t" . "update_time" . "\t" . "update_user_name" . "\t" . "update_login_id" . "\t" .
                // CC 状態
                "status";

            // NC2 reservation_reserve
            $tsv_cols['reserve_id'] = "";
            $tsv_cols['reserve_details_id'] = "";
            $tsv_cols['location_id'] = "";
            $tsv_cols['room_id'] = "";
            $tsv_cols['user_id'] = "";
            $tsv_cols['user_name'] = "";
            $tsv_cols['calendar_id'] = "";
            $tsv_cols['title'] = "";
            $tsv_cols['title_icon'] = "";
            $tsv_cols['allday_flag'] = "";
            $tsv_cols['start_date'] = "";
            $tsv_cols['start_time'] = "";
            $tsv_cols['start_time_full'] = "";
            $tsv_cols['end_date'] = "";
            $tsv_cols['end_time'] = "";
            $tsv_cols['end_time_full'] = "";
            $tsv_cols['timezone_offset'] = "";

            // NC2 reservation_reserve_details
            // 連絡先
            $tsv_cols['contact'] = "";
            // 内容
            $tsv_cols['description'] = "";
            // 繰り返し条件
            $tsv_cols['rrule'] = "";

            // NC2 reservation_reserve システム項目
            $tsv_cols['insert_time'] = "";
            $tsv_cols['insert_user_name'] = "";
            $tsv_cols['insert_login_id'] = "";
            $tsv_cols['update_time'] = "";
            $tsv_cols['update_user_name'] = "";
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
                ->where('reservation_reserve.location_id', $nc2_reservation_location->location_id)
                ->orderBy('reservation_reserve.reserve_details_id', 'asc')
                ->get();

            // カラムデータのループ
            Storage::delete($this->getImportPath('reservations/reservation_location_reserve_') . $this->zeroSuppress($nc2_reservation_location->location_id) . '.tsv');

            $tsv = '';
            $tsv .= $tsv_header . "\n";

            foreach ($reservation_reserves as $reservation_reserve) {

                // 初期化
                $tsv_record = $tsv_cols;

                // NC2 reservation_reserve
                $tsv_record['reserve_id'] = $reservation_reserve->reserve_id;
                $tsv_record['reserve_details_id'] = $reservation_reserve->reserve_details_id;
                $tsv_record['location_id'] = $reservation_reserve->location_id;
                $tsv_record['room_id'] = $reservation_reserve->room_id;
                $tsv_record['user_id'] = $reservation_reserve->user_id;
                $tsv_record['user_name'] = $reservation_reserve->user_name;
                $tsv_record['calendar_id'] = $reservation_reserve->calendar_id;
                $tsv_record['title'] = $reservation_reserve->title;
                $tsv_record['title_icon'] = $reservation_reserve->title_icon;
                $tsv_record['allday_flag'] = $reservation_reserve->allday_flag;

                // 予定開始日時
                // Carbon()で処理。必須値のため基本値がある想定で、timezone_offset で時間加算して予定時間を算出
                $start_time_full = (new Carbon($reservation_reserve->start_time_full))->addHour($reservation_reserve->timezone_offset);
                // $tsv_record['start_date'] = $reservation_reserve->start_date;
                // $tsv_record['start_time'] = $reservation_reserve->start_time;
                $tsv_record['start_date'] = $start_time_full->format('Y-m-d');
                $tsv_record['start_time'] = $start_time_full->format('H:i:s');
                $tsv_record['start_time_full'] = $start_time_full;

                // 予定終了日時
                // Carbon()で処理。必須値のため基本値がある想定で、timezone_offset で時間加算して予定時間を算出
                $end_time_full = (new Carbon($reservation_reserve->end_time_full))->addHour($reservation_reserve->timezone_offset);
                // if ($reservation_reserve->allday_flag == 1) {
                //     // 全日で終了日時の変換対応. -1日する。
                //     //
                //     // ・NC2 で登録できる開始時間：0:00～23:55 （24:00ないため、こっちは対応不要）
                //     // ・NC2 で登録できる終了時間：0:05～24:00 （0:00に設定しても前日24:00に自動変換される）
                //     // ・Connect 終了時間 0:00～23:59
                //     // 24:00はデータ上0:00のため、0:00から-5分して23:55に変換する。
                //     //
                //     // ※ NC2の全日１日は、        20210810 150000（+9時間）～20210811 150000（+9時間）←当日～翌日
                //     //    Connect-CMSの全日１日は、2021-08-11 00:00:00～2021-08-11 00:00:00 ←前後同じ, 時間は設定できず 00:00:00 で登録される。
                //     //    そのため、2021/08/11 0:00～2021/08/12 0:00 を 2021/08/11 0:00～2021/08/11 0:00に変換する。

                //     // -1日
                //     $end_time_full = $end_time_full->subDay();
                // } elseif ($end_time_full->format('H:i:s') == '00:00:00') {
                if ($end_time_full->format('H:i:s') == '00:00:00') {
                    // 全日以外で終了日時が0:00の変換対応. -5分する。
                    // ※ 例えばNC2の「時間指定」で10:00～24:00という予定に対応して、10:00～23:55に終了時間を変換する

                    // -5分
                    $end_time_full = $end_time_full->subMinute(5);
                }
                // $tsv_record['end_date'] = $reservation_reserve->end_date;
                // $tsv_record['end_time'] = $reservation_reserve->end_time;
                $tsv_record['end_date'] = $end_time_full->format('Y-m-d');
                $tsv_record['end_time'] = $end_time_full->format('H:i:s');
                $tsv_record['end_time_full'] = $end_time_full;

                $tsv_record['timezone_offset'] = $reservation_reserve->timezone_offset;

                // NC2 reservation_reserve_details
                // 連絡先
                $tsv_record['contact'] = $reservation_reserve->contact;
                // 内容 [WYSIWYG]
                $tsv_record['description'] = $this->nc2Wysiwyg(null, null, null, null, $reservation_reserve->description, 'reservation');
                // 繰り返し条件
                $tsv_record['rrule'] = $reservation_reserve->rrule;

                // NC2 reservation_reserve システム項目
                $tsv_record['insert_time'] = $this->getCCDatetime($reservation_reserve->insert_time);
                $tsv_record['insert_user_name'] = $reservation_reserve->insert_user_name;
                $tsv_record['insert_login_id'] = $this->getNc2LoginIdFromNc2UserId($nc2_users, $reservation_reserve->insert_user_id);
                $tsv_record['update_time'] = $this->getCCDatetime($reservation_reserve->update_time);
                $tsv_record['update_user_name'] = $reservation_reserve->update_user_name;
                $tsv_record['update_login_id'] = $this->getNc2LoginIdFromNc2UserId($nc2_users, $reservation_reserve->update_user_id);

                // NC2施設予約予定は公開のみ
                $tsv_record['status'] = 0;

                $tsv .= implode("\t", $tsv_record) . "\n";
            }

            // 施設予約の設定を出力
            $this->storagePut($this->getImportPath('reservations/reservation_location_') . $this->zeroSuppress($nc2_reservation_location->location_id) . '.ini', $ini);

            // データ行の書き出し
            $tsv = $this->exportStrReplace($tsv, 'reservations');
            $this->storageAppend($this->getImportPath('reservations/reservation_location_') . $this->zeroSuppress($nc2_reservation_location->location_id) . '.tsv', $tsv);
        }

        // メール設定
        // ----------------------------------------------------
        // modules テーブルの reservationモジューデータ 取得
        $nc2_module = Nc2Modules::where('action_name', 'like', 'reservation%')->first();
        $nc2_module = $nc2_module ?? new Nc2Modules();

        // config テーブルの 施設予約のメール設定 取得
        $nc2_configs = Nc2Config::where('conf_modid', $nc2_module->module_id)->get();

        // mail_send（メール通知する）. default=_ON
        $nc2_config_mail_send = $nc2_configs->firstWhere('conf_name', 'mail_send');
        $mail_send = null;
        if (is_null($nc2_config_mail_send)) {
            // 通知しない
            $mail_send = 0;
        } elseif ($nc2_config_mail_send->conf_value == '_ON') {
            // 通知する
            $mail_send = 1;
        } else {
            $mail_send = (int) $nc2_config_mail_send->conf_value;
        }

        // mail_authority（通知する権限）. default=_AUTH_GUEST ゲストまで全て（主担,モデ,一般,ゲストのチェックON）
        $nc2_config_mail_authority = $nc2_configs->firstWhere('conf_name', 'mail_authority');
        $mail_authority = null;
        if (is_null($nc2_config_mail_authority)) {
            // 主担のみ
            $mail_authority = 4;
        } elseif ($nc2_config_mail_authority->conf_value == '_AUTH_GUEST') {
            // ゲストまで全て（主担,モデ,一般,ゲストのチェックON）
            $mail_authority = 1;
        } else {
            $mail_authority = (int) $nc2_config_mail_authority->conf_value;
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
        $nc2_config_mail_subject = $nc2_configs->firstWhere('conf_name', 'mail_subject');
        $mail_subject = null;
        if (is_null($nc2_config_mail_subject)) {
            $mail_subject = null;
        } elseif ($nc2_config_mail_subject->conf_value == 'RESERVATION_MAIL_SUBJECT') {
            $mail_subject = '[{X-SITE_NAME}]予約の通知';
        } else {
            $mail_subject = $nc2_config_mail_subject->conf_value;
        }

        // mail_body（本文）. default=RESERVATION_MAIL_BODY ←多言語により表示言語によって変わる
        $nc2_configmail_body = $nc2_configs->firstWhere('conf_name', 'mail_body');
        $mail_body = null;
        if (is_null($nc2_configmail_body)) {
            $mail_body = null;
        } elseif ($nc2_configmail_body->conf_value == 'RESERVATION_MAIL_BODY') {
            $mail_body = "施設の予約が入りましたのでお知らせします。\n\n施設:{X-LOCATION_NAME}\n件名:{X-TITLE}\n利用グループ:{X-RESERVE_FLAG}\n利用日時:{X-RESERVE_TIME}\n連絡先:{X-CONTACT}\n繰返し:{X-RRULE}\n登録者:{X-USER}\n登録時刻:{X-INPUT_TIME}\n\n{X-BODY}\n\nこの予約を確認するには、下記アドレスへ\n{X-URL}";
        } else {
            $mail_body = $nc2_configmail_body->conf_value;
        }

        // 変換
        $convert_embedded_tags = [
            // nc2埋込タグ, cc埋込タグ
            ['{X-SITE_NAME}', '[[' . ReservationNoticeEmbeddedTag::site_name . ']]'],
            ['{X-LOCATION_NAME}', '[[' . ReservationNoticeEmbeddedTag::facility_name . ']]'],
            ['{X-TITLE}', '[[' . ReservationNoticeEmbeddedTag::title . ']]'],
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

        // NC2施設予約ブロック（モジュール配置したブロック（どう見せるか、だけ。ここ無くても予約データある））を移行する。
        // ----------------------------------------------------
        $where_reservation_block_ids = $this->getMigrationConfig('reservations', 'nc2_export_where_reservation_block_ids');
        if (empty($where_reservation_block_ids)) {
            $nc2_reservation_blocks_query = Nc2ReservationBlock::query();
        } else {
            $nc2_reservation_blocks_query = Nc2ReservationBlock::whereIn('reservation_block.block_id', $where_reservation_block_ids);
        }

        $nc2_reservation_blocks = $nc2_reservation_blocks_query->select('reservation_block.*', 'blocks.block_name', 'pages.page_name', 'page_rooms.page_name as room_name')
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
        if ($nc2_reservation_blocks->isEmpty()) {
            return;
        }

        // エクスポート対象の施設予約名をページ名から取得する（指定がなければブロックタイトルがあればブロックタイトル。なければページ名）
        $reservation_name_is_page_name = $this->getMigrationConfig('reservations', 'nc2_export_reservation_name_is_page_name');

        // NC2施設予約ブロックのループ
        foreach ($nc2_reservation_blocks as $nc2_reservation_block) {

            // NC2 施設予約ブロック（表示方法）設定
            $ini = "";
            $ini .= "[reservation_block]\n";

            // 表示方法
            // 1: 月表示(施設別)
            // 2: 週表示(施設別)
            // 3: 日表示(カテゴリ別)
            $ini .= "display_type = " . $nc2_reservation_block->display_type . "\n";

            // （表示する）カテゴリ（「最初に表示する施設」を絞り込むための設定）
            // 0:全て表示
            // 1:カテゴリなし
            // 2以降: 任意のカテゴリ
            $ini .= "category_id = " . $nc2_reservation_block->category_id . "\n";

            // 最初に表示する施設
            // ※ 表示方法=月・週表示のみ設定される
            $ini .= "location_id = " . $nc2_reservation_block->location_id . "\n";

            // 時間枠表示
            // 0:表示しない
            // 1:表示する
            // $ini .= "display_timeframe = " . $nc2_reservation_block->display_timeframe . "\n";

            // 表示開始時
            // default: 閲覧時刻により変動
            // default以外（0900等）：時間固定
            // $ini .= "display_start_time = " . $nc2_reservation_block->display_start_time . "\n";

            // 表示幅
            // $ini .= "display_interval = " .  $nc2_reservation_block->display_interval . "\n";

            // 施設予約の名前は、ブロックタイトルがあればブロックタイトル。なければページ名。
            $reservation_name = '無題';
            if (!empty($nc2_reservation_block->page_name)) {
                $reservation_name = $nc2_reservation_block->page_name;
            }
            if (empty($reservation_name_is_page_name)) {
                if (!empty($nc2_reservation_block->block_name)) {
                    $reservation_name = $nc2_reservation_block->block_name;
                }
            }
            $ini .= "reservation_name = \""  . $reservation_name . "\"\n";

            // NC2 情報
            $ini .= "\n";
            $ini .= "[source_info]\n";
            $ini .= "reservation_block_id = " . $nc2_reservation_block->block_id . "\n";
            $ini .= "room_id = " . $nc2_reservation_block->room_id . "\n";
            $ini .= "room_name = \"" . $nc2_reservation_block->room_name . "\"\n";
            $ini .= "module_name = \"reservation\"\n";

            // 施設予約の設定を出力
            $this->storagePut($this->getImportPath('reservations/reservation_block_') . $this->zeroSuppress($nc2_reservation_block->block_id) . '.ini', $ini);
        }
    }

    /**
     * NC2：固定リンク（abbreviate_url）の移行
     */
    private function nc2ExportAbbreviateUrl($redo)
    {
        $this->putMonitor(3, "Start nc2ExportAbbreviateUrl.");

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
        migrate_source = "NetCommons2"
        */

        // NC2固定リンク（abbreviate_url）を移行する。
        $nc2_abbreviate_urls = Nc2AbbreviateUrl::orderBy('insert_time')->get();

        // 空なら戻る
        if ($nc2_abbreviate_urls->isEmpty()) {
            return;
        }

        // ini ファイル用変数
        $permalinks_ini = "[permalinks]\n";

        // NC2固定リンクのループ（インデックス用）
        $index = 0;
        foreach ($nc2_abbreviate_urls as $nc2_abbreviate_url) {
            $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc2_abbreviate_url->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            $permalinks_ini .= "permalink[" . $index . "] = \"" . $nc2_abbreviate_url->short_url . "\"\n";
            $index++;
        }

        // NC2固定リンクのループ（データ用）
        foreach ($nc2_abbreviate_urls as $nc2_abbreviate_url) {
            $room_ids = $this->getMigrationConfig('basic', 'nc2_export_room_ids');
            // ルーム指定があれば、指定されたルームのみ処理する。
            if (empty($room_ids)) {
                // ルーム指定なし。全データの移行
            } elseif (!empty($room_ids) && in_array($nc2_abbreviate_url->room_id, $room_ids)) {
                // ルーム指定あり。指定ルームに合致する。
            } else {
                // ルーム指定あり。条件に合致せず。移行しない。
                continue;
            }

            if (!isset($this->plugin_name[$nc2_abbreviate_url->dir_name])) {
                continue;
            }

            $permalink  = "\n";
            $permalink .= "[\"" . $nc2_abbreviate_url->short_url . "\"]\n";

            $plugin_name     = $this->plugin_name[$nc2_abbreviate_url->dir_name];
            $permalink .= "plugin_name    = \"" . $plugin_name . "\"\n";

            if ($plugin_name == 'blogs') {
                $permalink .= "action         = \"show\"\n";
            } elseif ($plugin_name == 'databases') {
                $permalink .= "action         = \"detail\"\n";
            } elseif ($plugin_name == 'bbses') {
                $permalink .= "action         = \"show\"\n";
            }

            // 新 unique_id
            if (!empty($plugin_name)) {
                $unique_id = 0;
                $migration_mappings = MigrationMapping::where('target_source_table', $plugin_name . '_post')->where('source_key', $nc2_abbreviate_url->unique_id)->first();
                if (empty($migration_mappings)) {
                    continue;
                }
                $permalink .= "unique_id      = " . $migration_mappings->destination_key .  "\n";
            }

            $permalink .= "migrate_source = \"NetCommons2\"\n";
            $permalinks_ini .= $permalink;
        }

        // Userデータの出力
        //Storage::put($this->getImportPath('permalinks/permalinks.ini'), $permalinks_ini);
        $this->storagePut($this->getImportPath('permalinks/permalinks.ini'), $permalinks_ini);
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
        $nc2_toppage_display_sequence = $this->getMigrationConfig('basic', 'nc2_toppage_display_sequence', 1);
        if ($nc2_page->permalink == '' && $nc2_page->display_sequence == 1 && $nc2_page->space_type == 1 && $nc2_page->private_flag == 0 ||
            // トップページが削除されている場合も考慮
            $nc2_page->room_id == 1 && $nc2_page->root_id == 1 && $nc2_page->parent_id == 1 && $nc2_page->thread_num == 1 && $nc2_page->display_sequence == $nc2_toppage_display_sequence && $nc2_page->space_type == 1 && $nc2_page->private_flag == 0
        ) {
            // 指定されたページ内のブロックを取得
            $nc2_common_blocks_query = Nc2Block::select('blocks.*', 'pages.page_name')
                                               ->join('pages', 'pages.page_id', '=', 'blocks.page_id')
                                               ->whereIn('pages.page_name', ['Header Column', 'Left Column', 'Right Column']);

            if (!empty($export_ommit_blocks)) {
                $nc2_common_blocks_query->whereNotIn('block_id', $export_ommit_blocks);
            }

            $nc2_common_blocks = $nc2_common_blocks_query->orderBy('page_id', 'desc')
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
            $nc2_block->route_path = $this->getRouteBlockStr($nc2_block, $nc2_sort_blocks, $nc2_page, false);
            $nc2_sort_blocks[$this->getRouteBlockStr($nc2_block, $nc2_sort_blocks, $nc2_page, true)] = $nc2_block;
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

            // NC2 ブロック強制上書き設定があれば反映
            $nc2_block = $this->overrideNc2Block($nc2_block);

            $frame_index++;
            $frame_index_str = sprintf("%'.04d", $frame_index);

            // フレーム設定の保存用変数
            $frame_ini = "[frame_base]\n";
            $frame_ini .= "area_id = " . $this->nc2BlockArea($nc2_block) . "\n";

            // フレームタイトル＆メニューの特別処理
            if ($nc2_block->getModuleName() == 'menu') {
                $frame_ini .= "frame_title = \"\"\n";
            } else {
                $frame_ini .= "frame_title = \"" . $nc2_block->block_name . "\"\n";
            }

            if ($nc2_block->getModuleName() == 'menu') {
                $frame_ini .= "frame_design = \"none\"\n";
            } elseif (!empty($nc2_block->frame_design)) {
                $frame_ini .= "frame_design = \"" . $nc2_block->frame_design . "\"\n";
            } else {
                $frame_ini .= "frame_design = \"" . $nc2_block->getFrameDesign($this->getMigrationConfig('frames', 'export_frame_default_design', 'default')) . "\"\n";
            }
            $frame_ini .= "plugin_name = \"" . $nc2_block->getPluginName() . "\"\n";

            // グルーピングされているブロックの考慮
            // 同じ親で同じ行（row_num）に配置されているブロックの数を12で計算する。
            // 親（parent_id）= 0 でcol_num があるデータがある。NC2 の場合は、親にグループが居るはず。
            $row_block_count = $nc2_blocks->where('parent_id', $nc2_block->parent_id)->where('row_num', $nc2_block->row_num)->count();
            $row_block_parent = $nc2_blocks->where('block_id', $nc2_block->parent_id)->first();

            if (!empty($nc2_block->frame_col)) {
                $frame_ini .= "frame_col = " . $nc2_block['frame_col'] . "\n";
            } elseif ($row_block_count > 1 && $row_block_count <= 12 && $row_block_parent && $row_block_parent->action_name == 'pages_view_grouping') {
                $frame_ini .= "frame_col = " . floor(12 / $row_block_count) . "\n";
            }

            // 各項目
            if ($nc2_block->getModuleName() == 'calendar') {
                $calendar_block_ini = null;
                $calendar_display_type = null;

                // カレンダーブロックの情報取得
                if (Storage::exists($this->getImportPath('calendars/calendar_block_') . $this->zeroSuppress($nc2_block->block_id) . '.ini')) {
                    $calendar_block_ini = parse_ini_file(storage_path() . '/app/' . $this->getImportPath('calendars/calendar_block_') . $this->zeroSuppress($nc2_block->block_id) . '.ini', true);
                }

                if (!empty($calendar_block_ini) && array_key_exists('calendar_block', $calendar_block_ini) && array_key_exists('display_type', $calendar_block_ini['calendar_block'])) {
                    // NC2 のcalendar の display_type
                    $calendar_display_type = $this->getArrayValue($calendar_block_ini, 'calendar_block', 'display_type', null);
                }

                // frame_design 変換 (key:nc2)display_type => (value:cc)template
                // (NC2)初期値 = 月表示（縮小）= 2
                // (CC) 初期値 = 月表示（大）= default
                $display_type_to_frame_designs = [
                    1 => 'default',     // 1:年間表示
                    2 => 'small_month', // 2:月表示（縮小）
                    3 => 'default',     // 3:月表示（拡大）
                    4 => 'default',     // 4:週表示
                    5 => 'day',         // 5:日表示
                    6 => 'day',         // 6:スケジュール（時間順）
                    7 => 'day',         // 7:スケジュール（会員順）
                ];
                $frame_design = $display_type_to_frame_designs[$calendar_display_type] ?? 'default';
                $frame_ini .= "template = \"" . $frame_design . "\"\n";
            } elseif (!empty($nc2_block->template)) {
                $frame_ini .= "template = \"" . $nc2_block->template . "\"\n";
            } else {
                $frame_ini .= "template = \"" . $this->nc2BlockTemp($nc2_block) . "\"\n";
            }
            if (!empty($nc2_block->browser_width)) {
                $frame_ini .= "browser_width = \"" . $nc2_block->browser_width . "\"\n";
            }
            if (!empty($nc2_block->disable_whatsnews)) {
                $frame_ini .= "disable_whatsnews = " . $nc2_block->disable_whatsnews . "\n";
            }
            if (!empty($nc2_block->page_only)) {
                $frame_ini .= "page_only = " . $nc2_block->page_only . "\n";
            }
            if (!empty($nc2_block->default_hidden)) {
                $frame_ini .= "default_hidden = " . $nc2_block->default_hidden . "\n";
            }
            if (!empty($nc2_block->classname)) {
                $frame_ini .= "classname = \"" . $nc2_block->classname . "\"\n";
            }
            if (!empty($nc2_block->none_hidden)) {
                $frame_ini .= "none_hidden = " . $nc2_block->none_hidden . "\n";
            }
            if (!empty($nc2_block->display_sequence)) {
                $frame_ini .= "display_sequence = " . $nc2_block->display_sequence . "\n";
            }

            // モジュールに紐づくメインのデータのID
            $frame_ini .= $this->nc2BlockMainDataId($nc2_block);

            // NC2 情報
            $frame_nc2 = "\n";
            $frame_nc2 .= "[source_info]\n";
            $frame_nc2 .= "source_key = \"" . $nc2_block->block_id . "\"\n";
            $frame_nc2 .= "target_source_table = \"" . $nc2_block->getModuleName() . "\"\n";
            $frame_nc2 .= "insert_time = \"" . $this->getCCDatetime($nc2_block->insert_time) . "\"\n";
            $frame_nc2 .= "update_time = \"" . $this->getCCDatetime($nc2_block->update_time) . "\"\n";
            $frame_ini .= $frame_nc2;

            // フレーム設定ファイルの出力
            // メニューの場合は、移行完了したページデータを参照してインポートしたいので、insert 側に出力する。
            if ($nc2_block->getModuleName() == 'menu') {
                //Storage::put($this->getImportPath('pages/', '@insert/') . $this->zeroSuppress($new_page_index) . "/frame_" . $frame_index_str . '.ini', $frame_ini);
                $this->storagePut($this->getImportPath('pages/', '@insert/') . $this->zeroSuppress($new_page_index) . "/frame_" . $frame_index_str . '.ini', $frame_ini);
            } else {
                //Storage::put($this->getImportPath('pages/') . $this->zeroSuppress($new_page_index) . "/frame_" . $frame_index_str . '.ini', $frame_ini);
                $this->storagePut($this->getImportPath('pages/') . $this->zeroSuppress($new_page_index) . "/frame_" . $frame_index_str . '.ini', $frame_ini);
            }

            //echo $nc2_block->block_name . "\n";

            // ブロックのモジュールデータをエクスポート
            $this->nc2BlockExport($nc2_page, $nc2_block, $new_page_index, $frame_index_str);

            // ページ、ブロック構成を最後に出力するために保持
            $this->nc2BlockTree($nc2_page, $nc2_block);

            // Connect-CMS のプラグイン名の取得
            $plugin_name = $nc2_block->getPluginName();
            if ($plugin_name == 'Development' || $plugin_name == 'Abolition' || $plugin_name == 'searchs') {
                // 移行できなかったモジュール
                $this->putError(3, "no migrate module", "モジュール = " . $nc2_block->getModuleName(), $nc2_block);
            }
        }
    }

    /**
     * NC2：NC2 のブロックの上書き
     */
    private function overrideNc2Block($nc2_block)
    {
        // @nc2_override/blocks/{block_id}.ini があれば処理
        $nc2_override_block_path = $this->migration_base . '@nc2_override/blocks/' . $nc2_block->block_id . '.ini';
        if (Storage::exists($nc2_override_block_path)) {
            $nc2_override_block = parse_ini_file(storage_path() . '/app/' . $nc2_override_block_path, true);

            // ブロックタイトル
            //if (array_key_exists('block', $nc2_override_block) && array_key_exists('block_name', $nc2_override_block['block'])) {
            //    $nc2_block->block_name = $nc2_override_block['block']['block_name'];
            //}

            // ブロック属性（@nc2_override/blocks の中の属性で上書き）
            if (array_key_exists('block', $nc2_override_block)) {
                foreach ($nc2_override_block['block'] as $column_name => $column_value) {
                    $nc2_block->$column_name = $column_value;
                }
            }
        }
        return $nc2_block;
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
            // ブロックがあり、ブログがない場合は対象外
            if (!empty($nc2_journal_block)) {
                $ret = "blog_id = \"" . $this->zeroSuppress($nc2_journal_block->journal_id) . "\"\n";
            }
        } elseif ($module_name == 'bbs') {
            $nc2_bbs_block = Nc2BbsBlock::where('block_id', $nc2_block->block_id)->first();
            // ブロックがあり、掲示板がない場合は対象外
            if (!empty($nc2_bbs_block)) {
                $ret = "blog_id = \"bbs_" . $this->zeroSuppress($nc2_bbs_block->bbs_id) . "\"\n";
            }
        } elseif ($module_name == 'faq') {
            $nc2_faq_block = Nc2FaqBlock::where('block_id', $nc2_block->block_id)->first();
            $ret = "faq_id = \"" . $this->zeroSuppress($nc2_faq_block->faq_id) . "\"\n";
        } elseif ($module_name == 'linklist') {
            $nc2_linklist_block = Nc2LinklistBlock::where('block_id', $nc2_block->block_id)->first();
            // ブロックがあり、リンクリストがない場合は対象外
            if (!empty($nc2_linklist_block)) {
                $ret = "linklist_id = \"" . $this->zeroSuppress($nc2_linklist_block->linklist_id) . "\"\n";
            }
        } elseif ($module_name == 'multidatabase') {
            $nc2_multidatabase_block = Nc2MultidatabaseBlock::where('block_id', $nc2_block->block_id)->first();
            if (empty($nc2_multidatabase_block)) {
                $this->putError(3, "Nc2MultidatabaseBlock not found.", "block_id = " . $nc2_block->block_id, $nc2_block);
            } else {
                $ret = "database_id = \"" . $this->zeroSuppress($nc2_multidatabase_block->multidatabase_id) . "\"\n";
            }
        } elseif ($module_name == 'registration') {
            $nc2_registration_block = Nc2RegistrationBlock::where('block_id', $nc2_block->block_id)->first();
            // ブロックがあり、登録フォームがない場合は対象外
            if (!empty($nc2_registration_block)) {
                $ret = "form_id = \"" . $this->zeroSuppress($nc2_registration_block->registration_id) . "\"\n";
            }
        } elseif ($module_name == 'whatsnew') {
            $nc2_whatsnew_block = Nc2WhatsnewBlock::where('block_id', $nc2_block->block_id)->first();
            $ret = "whatsnew_block_id = \"" . $this->zeroSuppress($nc2_whatsnew_block->block_id) . "\"\n";
        } elseif ($module_name == 'cabinet') {
            $nc2_cabinet_block = Nc2CabinetBlock::where('block_id', $nc2_block->block_id)->first();
            // ブロックがあり、キャビネットがない場合は対象外
            if (!empty($nc2_cabinet_block)) {
                $ret = "cabinet_id = \"" . $this->zeroSuppress($nc2_cabinet_block->cabinet_id) . "\"\n";
            }
        } elseif ($module_name == 'menu') {
            // メニューの詳細設定（非表示設定が入っている）があれば、設定を加味する。
            $nc2_menu_details = Nc2MenuDetail::select('menu_detail.*', 'pages.permalink')
                                             ->join('pages', 'pages.page_id', '=', 'menu_detail.page_id')
                                             ->where("block_id", $nc2_block->block_id)
                                             ->orderBy('page_id', 'asc')
                                             ->get();
            if (empty($nc2_menu_details)) {
                $ret .= "\n";
                $ret .= "[menu]\n";
                $ret .= "select_flag       = \"0\"\n";
                $ret .= "page_ids          = \"\"\n";
                $ret .= "folder_close_font = \"0\"\n";
                $ret .= "folder_open_font  = \"0\"\n";
                $ret .= "indent_font       = \"0\"\n";
            } else {
                // この時点では、ページはエクスポート途中のため、新との変換はできない。
                // そのため、旧データで対象外を記載しておき、import の際に変換する。

                // 選択しないページを除外
                $ommit_nc2_pages = array();
                foreach ($nc2_menu_details as $nc2_menu_detail) {
                    // 下層ページを含めて取得
                    $ommit_pages = Nc2Page::where('permalink', 'like', $nc2_menu_detail->permalink . '%')->get();
                    if ($ommit_pages->isNotEmpty()) {
                        $ommit_nc2_pages = $ommit_nc2_pages + $ommit_pages->pluck('page_id')->toArray();
                    }
                }
                $ret .= "\n";
                $ret .= "[menu]\n";
                $ret .= "select_flag        = \"1\"\n";
                $ret .= "page_ids           = \"\"\n";
                $ret .= "folder_close_font  = \"0\"\n";
                $ret .= "folder_open_font   = \"0\"\n";
                $ret .= "indent_font        = \"0\"\n";
                if (!empty($ommit_nc2_pages)) {
                    asort($ommit_nc2_pages);
                    $ret .= "ommit_page_ids_nc2 = \"" . implode(",", $ommit_nc2_pages) . "\"\n";
                }
            }
        } elseif ($module_name == 'counter') {
            $nc2_counter = Nc2Counter::where('block_id', $nc2_block->block_id)->first();
            // ブロックがあり、カウンターがない場合は対象外
            if (!empty($nc2_counter)) {
                $ret = "counter_block_id = \"" . $this->zeroSuppress($nc2_counter->block_id) . "\"\n";
            }
        } elseif ($module_name == 'calendar') {
            $nc2_calendar_block = Nc2CalendarBlock::where('block_id', $nc2_block->block_id)->first();
            // ブロックがあり、カレンダーがない場合は対象外
            if (!empty($nc2_calendar_block)) {
                $ret = "calendar_block_id = \"" . $this->zeroSuppress($nc2_calendar_block->block_id) . "\"\n";
            }
        } elseif ($module_name == 'slides') {
            $nc2_slides = Nc2Slides::where('block_id', $nc2_block->block_id)->first();
            // ブロックがあり、スライダーがない場合は対象外
            if (!empty($nc2_slides)) {
                $ret = "slideshows_block_id = \"" . $this->zeroSuppress($nc2_slides->block_id) . "\"\n";
            }
        } elseif ($module_name == 'simplemovie') {
            $nc2_simplemovie = Nc2Simplemovie::where('block_id', $nc2_block->block_id)->first();
            // ブロックがあり、スライダーがない場合は対象外
            if (!empty($nc2_simplemovie)) {
                $ret = "simplemovie_block_id = \"" . $this->zeroSuppress($nc2_simplemovie->block_id) . "\"\n";
            }
        } elseif ($module_name == 'reservation') {
            $nc2_reservation_block = Nc2ReservationBlock::where('block_id', $nc2_block->block_id)->first();
            // ブロックがあり、施設予約がない場合は対象外
            if (!empty($nc2_reservation_block)) {
                $ret = "reservation_block_id = \"" . $this->zeroSuppress($nc2_reservation_block->block_id) . "\"\n";
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
            $this->nc2BlockExportContents($nc2_page, $nc2_block, $new_page_index, $frame_index_str);
        } elseif ($plugin_name == 'menus') {
            // メニュー
            // 今のところ、メニューの追加設定はなし。
        } elseif ($plugin_name == 'databases') {
            // データベース
            $this->nc2BlockExportDatabases($nc2_page, $nc2_block, $new_page_index, $frame_index_str);
        } elseif ($plugin_name == 'bbses') {
            // 掲示板
            $this->nc2BlockExportBbses($nc2_page, $nc2_block, $new_page_index, $frame_index_str);
        }
    }

    /**
     * NC2：固定記事（お知らせ）のエクスポート
     */
    private function nc2BlockExportContents($nc2_page, $nc2_block, $new_page_index, $frame_index_str)
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
        $save_folder = $this->getImportPath('pages/') . $this->zeroSuppress($new_page_index);
        $content_filename = "frame_" . $frame_index_str . '.html';
        $ini_filename = "frame_" . $frame_index_str . '.ini';

        $this->nc2Wysiwyg($nc2_block, $save_folder, $content_filename, $ini_filename, $content, 'announcement', $nc2_page);

        //echo "nc2BlockExportContents";
    }

    /**
     * NC2：汎用データベースのブロック特有部分のエクスポート
     */
    private function nc2BlockExportDatabases($nc2_page, $nc2_block, $new_page_index, $frame_index_str)
    {
        // NC2 ブロック設定の取得
        $nc2_multidatabase_block = Nc2MultidatabaseBlock::where('block_id', $nc2_block->block_id)->first();
        if (empty($nc2_multidatabase_block)) {
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
        if ($nc2_multidatabase_block->default_sort == 'seq') {
            $this->putError(3, 'データベースのソートが未対応順（カスタマイズ順）', "nc2_multidatabase_block = " . $nc2_multidatabase_block->block_id);
        } elseif ($nc2_multidatabase_block->default_sort == 'date') {
            $default_sort_flag = 'created_desc';
        } elseif ($nc2_multidatabase_block->default_sort == 'date_asc') {
            $default_sort_flag = 'created_asc';
        } else {
            $this->putError(3, 'データベースのソートが未対応順', "nc2_multidatabase_block = " . $nc2_multidatabase_block->block_id);
        }
        $frame_ini .= "default_sort_flag = \"" . $default_sort_flag . "\"\n";
        $frame_ini .= "view_count = "          . $nc2_multidatabase_block->visible_item . "\n";
        //Storage::append($save_folder . "/"     . $ini_filename, $frame_ini);
        $this->storageAppend($save_folder . "/"     . $ini_filename, $frame_ini);
    }

    /**
     * NC2：掲示板のブロック特有部分のエクスポート
     */
    private function nc2BlockExportBbses($nc2_page, $nc2_block, $new_page_index, $frame_index_str)
    {
        // NC2 ブロック設定の取得
        $nc2_bbs_block = Nc2BbsBlock::where('block_id', $nc2_block->block_id)->first();
        if (empty($nc2_bbs_block)) {
            return;
        }

        $ini_filename = "frame_" . $frame_index_str . '.ini';

        $save_folder = $this->getImportPath('pages/') . $this->zeroSuppress($new_page_index);

        // 表示形式 変換
        // (nc) 0:スレッド,1:フラット
        // (cc) 0:フラット形式,1:スレッド形式
        // (key:nc2)expand => (value:cc)view_format
        $convert_view_formats = [
            0 => 1,
            1 => 0,
        ];
        if (isset($convert_view_formats[$nc2_bbs_block->expand])) {
            $view_format = $convert_view_formats[$nc2_bbs_block->expand];
        } else {
            $view_format = '';
            $this->putError(3, '掲示板の表示形式が未対応の形式', "nc2_bbs_block = " . $nc2_bbs_block->block_id);
        }

        $frame_ini = "[bbs]\n";
        $frame_ini .= "view_count = {$nc2_bbs_block->visible_row}\n";
        $frame_ini .= "view_format = {$view_format}\n";
        $this->storageAppend($save_folder . "/"     . $ini_filename, $frame_ini);
    }

    /**
     * コンテンツのクリーニング
     */
    private function cleaningContent($content, $nc2_module_name)
    {
        // 改行コードが含まれる場合があるので置換
        $content = str_replace(array("\r", "\n"), '', $content);

        $plugin_name = $this->nc2GetPluginName($nc2_module_name);

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
     * NC2：WYSIWYG の記事の保持
     *
     * 保存するディレクトリ：migration の下を指定
     * コンテンツファイル名
     * iniファイル名
     */
    private function nc2Wysiwyg($nc2_block, $save_folder, $content_filename, $ini_filename, $content, $nc2_module_name = null, $nc2_page = null)
    {
        // コンテンツのクリーニング
        $content = $this->cleaningContent($content, $nc2_module_name);

        // 画像を探す
        $img_srcs = $this->getContentImage($content);
        // var_dump($img_srcs);

        // 画像の中のcommon_download_main をエクスポートしたパスに変換する。
        $content = $this->nc2MigrationCommonDownloadMain($nc2_block, $save_folder, $ini_filename, $content, $img_srcs, '[upload_images]');

        // CSS の img-fluid を自動で付ける最小の画像幅
        $img_fluid_min_width = $this->getMigrationConfig('wysiwyg', 'img_fluid_min_width', 0);

        // 画像全体にレスポンシブCSS を適用する。
        $img_srcs = $this->getContentImageTag($content);

        if (!empty($img_srcs)) {
            $img_srcs_0 = array_unique($img_srcs[0]);
            foreach ($img_srcs_0 as $key => $img_src) {
                if (stripos($img_src, '../../uploads') !== false && stripos($img_src, 'class=') === false) {
                    // 画像のファイル名。$file_name には、最初に "/" がつく。
                    $last_slash_pos = mb_strripos($img_srcs[1][$key], '/');
                    $file_name = mb_substr($img_srcs[1][$key], $last_slash_pos);
                    $file_path = storage_path() . '/app/' . $this->getImportPath('uploads' . $file_name);
                    // 画像が存在し、img_fluid_min_width で指定された大きさ以上なら、img-fluid クラスをつける。
                    if (File::exists($file_path)) {
                        $imagesize = getimagesize($file_path);
                        if (is_array($imagesize) && $imagesize[0] >= $img_fluid_min_width) {
                            $new_img_src = str_replace('<img ', '<img class="img-fluid" ', $img_src);
                            $content = str_replace($img_src, $new_img_src, $content);
                        }
                    }
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

        // Google Map 埋め込み時のスマホ用対応。widthを 100% に変更
        $iframe_srces = $this->getIframeSrc($content);
        if (!empty($iframe_srces)) {
            // iFrame のsrc を取得（複数の可能性もあり）
            $iframe_styles = $this->getIframeStyle($content);
            if (!empty($iframe_styles)) {
                foreach ($iframe_styles as $iframe_style) {
                    $width_pos = strpos($iframe_style, 'width');
                    $width_length = strpos($iframe_style, ";", $width_pos) - $width_pos + 1;
                    $iframe_style_width = substr($iframe_style, $width_pos, $width_length);
                    if (!empty($iframe_style_width)) {
                        $content = str_replace($iframe_style_width, "width:100%;", $content);
                    }
                }
            }
        }

        // 添付ファイルを探す
        $anchors = $this->getContentAnchor($content);
        //var_dump($anchors);

        // 添付ファイルの中のcommon_download_main をエクスポートしたパスに変換する。
        $content = $this->nc2MigrationCommonDownloadMain($nc2_block, $save_folder, $ini_filename, $content, $anchors, '[upload_files]');

        // HTML からa タグの 相対パスリンクを絶対パスに修正
        //$content = $this->changeFullPath($content, $nc2_page);


        // HTML content の保存
        if ($save_folder) {
            //Storage::put($save_folder . "/" . $content_filename, $content);
            $this->storagePut($save_folder . "/" . $content_filename, $content);
        }

        // フレーム設定ファイルの追記
        if ($ini_filename) {
            $contents_ini = "[contents]\n";
            $contents_ini .= "contents_file = \"" . $content_filename . "\"\n";
            //Storage::append($save_folder . "/" . $ini_filename, $contents_ini);
            $this->storageAppend($save_folder . "/" . $ini_filename, $contents_ini);
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
            $ini_text .= 'upload[' . $export_key . "] = \"" . $export_path . "\"\n";
        }

        // 記事ごとにini ファイルが必要な場合のみ出力する。
        if ($ini_filename) {
            //Storage::append($save_folder . "/" . $ini_filename, $ini_text);
            $this->storageAppend($save_folder . "/" . $ini_filename, $ini_text);
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
//                            $export_path = '../../uploads/' . $this->uploads_ini[$param_split[1]]['temp_file_name'];
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
                            $export_path = '../../uploads/' . $this->uploads_ini[$param_split[1]]['temp_file_name'];

                            // [upload_images] or [upload_files] 内の画像情報の追記
                            $export_paths[$param_split[1]] = $export_path;

                            // ファイルのパスの修正
                            // ファイル指定の前後の " も含めないと、upload_id=1 を変換した際、 upload_id=14 も含まれる。
                            //$content = str_replace($path, $export_path, $content);
                            $content = str_replace('"' . $path . '"', '"' .$export_path . '"', $content);
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

    /**
     * NC2の日時をConnect-CMCの日時に変換する
     *
     * @param string $datetime 日時(YmdHis)
     * @return string 日時(Y-m-d H:i:s)
     */
    private function convertNc2Datetime(string $datetime) : ?string
    {
        if (empty($datetime)) {
            return null;
        }

        // NC2 の投稿日時はGMT のため、9時間プラスする NC2=20151020122600
        $time = mktime(
            substr($datetime, 8, 2),
            substr($datetime, 10, 2),
            substr($datetime, 12, 2),
            substr($datetime, 4, 2),
            substr($datetime, 6, 2),
            substr($datetime, 0, 4)
        );

        // 変換失敗なら空文字を返却する
        if ($time === false) {
            return null;
        }
        return  date('Y-m-d H:i:s', $time + (60 * 60 * 9));
    }
}
