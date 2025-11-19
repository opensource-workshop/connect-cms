<?php

namespace Tests\Browser\Top;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Models\Common\Frame;
use App\Models\Core\Dusks;
use App\Models\User\Photoalbums\Photoalbum;
use App\Models\User\Photoalbums\PhotoalbumContent;
use App\Enums\PluginName;

/**
 * トップページ・動画用の初期クラス
 *
 */
class IndexTopTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        // Laravel がコンストラクタでbase_path など使えないので、ここで。
        $this->screenshots_root = base_path('tests/Browser/screenshots/');

        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->assertTrue(true);

        // 開く画面のデータを取得しておく。
        $args = array();
        $args['photoalbum_frame'] = Frame::where('plugin_name', 'photoalbums')->first();
        $args['photoalbum'] = Photoalbum::where('bucket_id', $args['photoalbum_frame']->bucket_id)->first();
        $args['photoalbum_folder_photo'] = PhotoalbumContent::where('photoalbum_id', $args['photoalbum']->id)->where('name', '写真用アルバム')->first();
        $args['photoalbum_folder_movie'] = PhotoalbumContent::where('photoalbum_id', $args['photoalbum']->id)->where('name', '動画用アルバム')->first();
        $args['database_frame'] = Frame::where('plugin_name', 'databases')->first();
        $args['form_frame'] = Frame::where('plugin_name', 'forms')->first();

        // 画面キャプチャの取得
        $this->browse(function (Browser $browser) use ($args) {
            // ブログ、フォトアルバム、カレンダー、スライドショー、開館カレンダー、データベース（閲覧）
            $browser->visit('/test/blog')
                    ->screenshot('top/index/index/images/blog')
                    ->visit('/')
                    ->screenshot('top/index/index/images/content')
                    ->visit('/test/menu')
                    ->screenshot('top/index/index/images/menu')
                    ->visit('/test/calendar')
                    ->screenshot('top/index/index/images/calendar')
                    ->visit('/test/slideshow')
                    ->waitFor('.carousel')
                    ->screenshot('top/index/index/images/slideshow')
                    ->visit('/test/openingcalendar')
                    ->screenshot('top/index/index/images/openingcalendar')
                    ->visit('/test')
                    ->screenshot('top/index/index/images/whatsnew')
                    ->visit('/test/faq')
                    ->screenshot('top/index/index/images/faq')
                    ->visit('/test/linklist')
                    ->screenshot('top/index/index/images/linklist')
                    ->visit('/test/cabinet')
                    ->screenshot('top/index/index/images/cabinet')
                    ->visit('/plugin/photoalbums/changeDirectory/' . $args['photoalbum_frame']->page_id . '/' . $args['photoalbum_frame']->id . '/' . $args['photoalbum_folder_photo']->id)
                    ->screenshot('top/index/index/images/photoalbum_photo')
                    ->visit('/plugin/photoalbums/changeDirectory/' . $args['photoalbum_frame']->page_id . '/' . $args['photoalbum_frame']->id . '/' . $args['photoalbum_folder_movie']->id)
                    ->screenshot('top/index/index/images/photoalbum_movie')
                    ->visit('/test/database')
                    ->screenshot('top/index/index/images/database')
                    ->visit('/test/form')
                    ->screenshot('top/index/index/images/form')
                    ->visit('/test/questionnaire')
                    ->screenshot('top/index/index/images/questionnaire')
                    ->visit('/test/counter')
                    ->screenshot('top/index/index/images/counter')
                    ->visit('/plugin/searchs/search/18/20?search_keyword=%E3%83%86%E3%82%B9%E3%83%88')
                    ->screenshot('top/index/index/images/search')
                    ->visit('/test/bbs')
                    ->screenshot('top/index/index/images/bbs')
                    ->visit('/test/reservation')
                    ->screenshot('top/index/index/images/reservation');
            /*
            $this->login(1);
            $browser->visit('/plugin/databases/editColumn/' . $args['database_frame']->page_id . '/' . $args['database_frame']->id)
                    ->screenshot('top/index/index/images/database_edit_column')
                    ->visit('/plugin/forms/editColumn/' . $args['form_frame']->page_id . '/' . $args['form_frame']->id)
                    ->screenshot('top/index/index/images/form_edit_column');
            $this->logout();
            */
        });

        // マニュアル用データ出力
        // ブログ、固定記事、メニュー、カレンダー、スライドショー、開館カレンダー、新着情報、FAQ、リンクリスト、キャビネット、フォトアルバム、データベース、フォーム、アンケート、カウンター、サイト内検索
        $dusk = Dusks::putManualData(
            ['html_path' => 'top/index/index/index.html'],
            ['category' => 'top',
             'sort' => 2,
             'plugin_name' => 'index',
             'plugin_title' => 'Connect-CMS紹介',
             'plugin_desc' => 'Connect-CMSへようこそ。　ホームページの作成や組織内の情報共有に使用できる、オープンソースCMSの、Connect-CMSを紹介します。　Connect-CMSをご理解いただくために、Connect-CMSの代表的な機能を紹介します。',
             'method_name' => 'index',
             'method_title' => 'Connect-CMS紹介',
             'method_desc' => '',
             'method_detail' => '',
             'html_path' => 'top/index/index/index.html',
             'img_args' => '[
                 {"path": "top/index/index/images/blog",
                  "name": "ブログ・プラグイン",
                  "comment": "<ul class=\"mb-0\"><li>例えば、ブログ機能があります。</li><li>ブログ形式で情報発信できます。</li><li>パスワード付きページやメンバーシップページに配置することで、組織内だけの情報共有にも使用できます。</li></ul>"
                 },
                 {"path": "top/index/index/images/content",
                  "name": "固定記事・プラグイン",
                  "comment": "<ul class=\"mb-0\"><li>サイト上に文字や画像を配置できるプラグインです。例えばヘッダーエリアのバナーなどは固定記事プラグインです。</li></ul>"
                 },
                 {"path": "top/index/index/images/menu",
                  "name": "メニュー・プラグイン",
                  "comment": "<ul class=\"mb-0\"><li>ページ設定を元にメニューを表示できるプラグインです。</li></ul>"
                 },
                 {"path": "top/index/index/images/calendar",
                  "name": "カレンダー機能",
                  "comment": "<ul class=\"mb-0\"><li>カレンダー機能は、予定の周知や共有ができます。</li></ul>"
                 },
                 {"path": "top/index/index/images/slideshow",
                  "name": "スライドショー",
                  "comment": "<ul class=\"mb-0\"><li>Webサイトを魅力的に見るためのスライドショーもあります。</li></ul>"
                 },
                 {"path": "top/index/index/images/openingcalendar",
                  "name": "開館カレンダー",
                  "comment": "<ul class=\"mb-0\"><li>Connect-CMSらしい機能の一つです。図書館や施設の開館情報をわかりやすく提示するための機能があります。</li></ul>"
                 },
                 {"path": "top/index/index/images/whatsnew",
                  "name": "新着情報",
                  "comment": "<ul class=\"mb-0\"><li>新着情報を作成できるプラグインです。サイト内のプラグインから新着記事を集めます。</li></ul>"
                 },
                 {"path": "top/index/index/images/faq",
                  "name": "FAQ",
                  "comment": "<ul class=\"mb-0\"><li>FAQを作成できるプラグインです。質問と回答をわかりやすく表示できます。</li></ul>"
                 },
                 {"path": "top/index/index/images/linklist",
                  "name": "リンクリスト",
                  "comment": "<ul class=\"mb-0\"><li>リンクリストを作成できるプラグインです。よく使うサイトのURLを登録します。</li></ul>"
                 },
                 {"path": "top/index/index/images/cabinet",
                  "name": "キャビネット",
                  "comment": "<ul class=\"mb-0\"><li>キャビネット・プラグインは、ファイルの管理にとても便利です。</li></ul>"
                 },
                 {"path": "top/index/index/images/photoalbum_photo",
                  "name": "フォトアルバム",
                  "comment": "<ul class=\"mb-0\"><li>フォトアルバム機能は、写真や動画を管理することができます。</li><li>イベントで撮った写真や動画をアルバム形式で表示することができ、楽しい思い出を見やすくできます。</li><li>パスワード付きページやメンバーシップページに配置することで、安心して写真共有もできます。</li></ul>"
                 },
                 {"path": "top/index/index/images/photoalbum_movie",
                  "name": "フォトアルバムの動画アルバム",
                  "comment": "<ul class=\"mb-0\"><li>フォトアルバム機能で動画を共有している例です。</li></ul>"
                 },
                 {"path": "top/index/index/images/database",
                  "name": "データベース",
                  "comment": "<ul class=\"mb-0\"><li>Webサイト上に、自由に項目を設定してデータベースを作ることができます。</li></ul>"
                 },
                 {"path": "top/index/index/images/form",
                  "name": "フォーム",
                  "comment": "<ul class=\"mb-0\"><li>Webサイト上に、自由に項目を設定して入力フォームを作ることができます。</li><li>問い合わせフォームやアンケートなどに使用できます。</li></ul>"
                 },
                 {"path": "top/index/index/images/questionnaire",
                  "name": "アンケート",
                  "comment": "<ul class=\"mb-0\"><li>項目を自由に作成して、アンケートを取ることができます。</li></ul>"
                 },
                 {"path": "top/index/index/images/counter",
                  "name": "カウンター",
                  "comment": "<ul class=\"mb-0\"><li>日ごとの履歴もダウンロードできるカウンターです。</li></ul>"
                 },
                 {"path": "top/index/index/images/search",
                  "name": "サイト内検索",
                  "comment": "<ul class=\"mb-0\"><li>サイト内のコンテンツを検索できます。</li></ul>"
                 },
                 {"path": "top/index/index/images/bbs",
                  "name": "掲示板",
                  "comment": "<ul class=\"mb-0\"><li>スレッド形式記事を管理できる掲示板があります。</li><li>ユーザ同士で情報交換する場合に便利な機能です。</li></ul>"
                 },
                 {"path": "top/index/index/images/reservation",
                  "name": "施設予約",
                  "comment": "<ul class=\"mb-0\"><li>施設を登録して、予約管理できる機能です。</li></ul>"
                 }
             ]',
            'level' => 'basic',
            'test_result' => 'OK']
        );
    }
}
