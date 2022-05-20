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
                    ->screenshot('top/index/index/images/blog_index')
                    ->visit('/plugin/photoalbums/changeDirectory/' . $args['photoalbum_frame']->page_id . '/' . $args['photoalbum_frame']->id . '/' . $args['photoalbum_folder_photo']->id)
                    ->screenshot('top/index/index/images/photoalbum_photo')
                    ->visit('/plugin/photoalbums/changeDirectory/' . $args['photoalbum_frame']->page_id . '/' . $args['photoalbum_frame']->id . '/' . $args['photoalbum_folder_movie']->id)
                    ->screenshot('top/index/index/images/photoalbum_movie')
                    ->visit('/test/calendar')
                    ->screenshot('top/index/index/images/calendar')
                    ->visit('/test/slideshow')
                    ->screenshot('top/index/index/images/slideshow')
                    ->visit('/test/openingcalendar')
                    ->screenshot('top/index/index/images/openingcalendar')
                    ->visit('/test/database')
                    ->screenshot('top/index/index/images/database_index')
                    ->visit('/test/form')
                    ->screenshot('top/index/index/images/form_index')
                    ->visit('/test/bbs')
                    ->screenshot('top/index/index/images/bbs_index')
                    ->visit('/test/reservation')
                    ->screenshot('top/index/index/images/reservationreservation_index');

            $this->login(1);
            $browser->visit('/plugin/databases/editColumn/' . $args['database_frame']->page_id . '/' . $args['database_frame']->id)
                    ->screenshot('top/index/index/images/database_edit_column')
                    ->visit('/plugin/forms/editColumn/' . $args['form_frame']->page_id . '/' . $args['form_frame']->id)
                    ->screenshot('top/index/index/images/form_edit_column');
            $this->logout();
        });

        // マニュアル用データ出力
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
                 {"path": "top/index/index/images/blog_index",
                  "name": "ブログ機能",
                  "comment": "<ul class=\"mb-0\"><li>例えば、ブログ機能があります。</li><li>ブログ形式で情報発信できます。</li><li>パスワード付きページやメンバーシップページに配置することで、組織内だけの情報共有にも使用できます。</li></ul>"
                 },
                 {"path": "top/index/index/images/photoalbum_photo",
                  "name": "フォトアルバム機能",
                  "comment": "<ul class=\"mb-0\"><li>フォトアルバム機能は、写真や動画を管理することができます。</li><li>イベントで撮った写真や動画をアルバム形式で表示することができ、楽しい思い出を見やすくできます。</li><li>パスワード付きページやメンバーシップページに配置することで、安心して写真共有もできます。</li></ul>"
                 },
                 {"path": "top/index/index/images/photoalbum_movie",
                  "name": "フォトアルバム機能の動画アルバム",
                  "comment": "<ul class=\"mb-0\"><li>フォトアルバム機能で動画を共有している例です。</li></ul>"
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
                  "name": "会館カレンダー",
                  "comment": "<ul class=\"mb-0\"><li>Connect-CMSらしい機能の一つです。図書館や施設の開館情報をわかりやすく提示するための機能があります。</li></ul>"
                 },
                 {"path": "top/index/index/images/database_index",
                  "name": "データベース",
                  "comment": "<ul class=\"mb-0\"><li>Webサイト上に、自由に項目を設定してデータベースを作ることができます。</li></ul>"
                 },
                 {"path": "top/index/index/images/form_index",
                  "name": "フォーム",
                  "comment": "<ul class=\"mb-0\"><li>Webサイト上に、自由に項目を設定して入力フォームを作ることができます。</li><li>問い合わせフォームやアンケートなどに使用できます。</li></ul>"
                 },
                 {"path": "top/index/index/images/bbs_index",
                  "name": "掲示板",
                  "comment": "<ul class=\"mb-0\"><li>スレッド形式記事を管理できる掲示板があります。</li><li>ユーザ同士で情報交換する場合に便利な機能です。</li></ul>"
                 },
                 {"path": "top/index/index/images/reservationreservation_index",
                  "name": "施設予約",
                  "comment": "<ul class=\"mb-0\"><li>施設を登録して、予約管理できる機能です。</li></ul>"
                 }
             ]',
            'level' => 'basic',
            'test_result' => 'OK']
        );
    }
}
