<?php

namespace Tests\Manual\src;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\ManualCategory;
use App\Models\Core\Dusks;

/**
 * > php artisan dusk tests\Manual\src\ManualVideo.php
 *
 * tmp
 * +---mp3
 *     +---manage.mp3              # カテゴリ動画  （一通りのプラグインの概要を紹介 - プラグインの最初の画像）
 *     +---manage_index.mp3        # プラグイン動画（一通りの機能の概要を紹介 - 機能の最初の画像）
 *     +---manage_index_index.mp3  # 機能動画      （個別の機能を紹介 - 機能の全ての画像）
 *     +---manage_page.mp3         # ページ管理プラグイン動画
 *     +---manage_page_index.mp3   # ページ一覧
 *     \---manage_page_edit.mp3    # ページ編集
 * +---mp4
 *
 * tmp 内は、画像単位の音声、動画
 * fade をかけるのは、tmp からmanual ディレクトリへコピーする際
 * fade、動画リストの生成、結合 ＞ manual ディレクトリへ出力
 * tmp
 * +---_work                     (fade、結合用の一時ディレクトリ)
 *     +---ファイルは生成後、移動して、作業後はここは空の状態にする。
 * +---user
 *     +---_mp4_list.txt         (user カテゴリの動画リスト)
 *     +---blog
 *         +---_mp4_list.txt     (blog プラグインの動画リスト)
 *         +---index
 *             +---_mp4list.txt  (このディレクトリのmp4リスト)
 *             +---index.mp3     (index.png  に対応したmp3)
 *             +---index2.mp3    (index2.png に対応したmp3)
 *             +---index.mp4     (index.png  に対応したmp4)
 *             +---index2.mp4    (index2.png に対応したmp4)
 *         +---show
 *             +---_mp4list.txt  (このディレクトリのmp4リスト)
 *             +---show.mp3      (show.png  に対応したmp3)
 *             +---show.mp4      (show.png  に対応したmp4)
 *         +---create
 *             +---_mp4list.txt  (このディレクトリのmp4リスト)
 *             +---create.mp3    (create.png  に対応したmp3)
 *             +---create2.mp3   (create2.png に対応したmp3)
 *             +---create.mp4    (create.png  に対応したmp4)
 *             +---create2.mp4   (create2.png に対応したmp4)
 *
 * manual
 * +---user
 *     +---blog
 *         +---mp4
 *             +---mizuki
 *                 +---fast
 *                 +---medium
 *                     +---index.mp4     (blog プラグインの動画(各メソッドの1個目))
 *                 +---slow
 *             +---takumi
 *         +---index
 *             +---mp4
 *                 +---mizuki
 *                     +---fast
 *                     +---medium
 *                         +---index.mp4 (index.png & index2.png に対応したmp4)
 *         +---show
 *             +---mp4
 *                 +---mizuki
 *                     +---fast
 *                     +---medium
 *                         +---show.mp4  (show.png  に対応したmp4)
 *         +---create
 *             +---mp4
 *                 +---mizuki
 *                     +---fast
 *                     +---medium
 *                         +---create.mp4 (create.png & create2.png に対応したmp4)
 *
 */
class ManualVideo extends DuskTestCase
{
    /**
     * スクリーンショット保存ルートパス
     */
    private $screenshots_root;

    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 概要出力
     *
     * @return void
     */
    private function outputDescription($pdf)
    {
        // 全体ビデオ？
    }

    /**
     * ナレーション用に文章のクリーニング
     *
     * @return void
     */
    private function cleaningText($text)
    {
        // HTML タグの除去
        $text = strip_tags($text);

        // （）の中身も含めた除去
        if (mb_strpos($text, '（') !== false && mb_strpos($text, '）') !== false) {
            $trim_start = mb_strpos($text, '（');
            $trim_end = mb_strpos($text, '）');
            $text = mb_substr($text, 0, $trim_start) . mb_substr($text, $trim_end + 1);
        }

        return $text;
    }

    /**
     * カテゴリ出力
     *
     * @return void
     */
    private function outputCategory($dusks, $category)
    {
        if ($category->category != 'manage') {
            return;
        }

        // ビデオ用のナレーションと画像
        $video_params = array();

        // カテゴリの出力
        $plugins_index = $dusks->where('category', $category->category)->where('method_name', 'index');
        foreach ($plugins_index as $method) {
            // img_args が json か。
            if (json_decode($method->img_args)) {
                $json_paths = json_decode($method->img_args);
                foreach ($json_paths as $json_path) {
                    // ナレーション文章を組み立て
                    $video_params[$method->plugin_name] = ['img_path' => $json_path->path, 'narration' => $this->cleaningText($method->method_desc . $method->method_detail)];

                    // 1つ目の画像と説明でOK
                    continue;
                }
            } else {
                foreach (explode(',', $method->img_args) as $img_path) {
                    // ナレーション文章を組み立て
                    $video_params[$method->plugin_name] = ['img_path' => $img_path, 'narration' => $this->cleaningText($method->method_desc . $method->method_detail)];

                    // 1つ目の画像と説明でOK
                    continue;
                }
            }
        }

//\Log::debug($video_params);
    }

    /**
     * プラグイン出力
     *
     * @return void
     */
    private function outputPlugin($dusks, $category, $plugin)
    {
        // プラグインの出力
    }

    /**
     * メソッド出力
     *
     * @return void
     */
    private function outputMethod($method)
    {
        // メソッドの出力
    }

    /**
     * マニュアルビデオ出力用クラス
     *
     * @return void
     */
    public function testVideo()
    {
        // Laravel がコンストラクタでbase_path など使えないので、ここで。
        $this->screenshots_root = base_path('tests/Browser/screenshots/');

        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertTitleContains('Connect-CMS');
        });

        // 全データ取得
        $dusks = Dusks::orderBy("id", "asc")->get();

        // マニュアル表紙
        //$pdf->writeHTML(view('manual.pdf.cover')->render(), false);

        // 概要
        //$this->outputDescription($pdf);

        // マニュアル用データをループ
        // マニュアルHTML と違い、カテゴリ、プラグイン、メソッドの3重ループで処理する。
        // マニュアルHTML は、カテゴリ、プラグイン、メソッドをそれぞれ独立でループした。（メニューの生成のため）

        // カテゴリのループ
        // echo "\n";
        foreach ($dusks->groupBy('category') as $category) {
            $this->outputCategory($dusks, $category[0]);

            // プラグインのループ
            foreach ($dusks->where('category', $category[0]->category)->where('method_name', 'index') as $plugin) {
//                $this->outputPlugin($dusks, $category[0], $plugin);

                // メソッドのループ
                foreach ($dusks->where('category', $category[0]->category)->where('plugin_name', $plugin->plugin_name) as $method) {
//                    $this->outputMethod($method);
                }
            }
        }
    }
}
