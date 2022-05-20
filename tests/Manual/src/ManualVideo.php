<?php

namespace Tests\Manual\src;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\Enums\ManualCategory;
use App\Models\Core\Dusks;

/**
 * > php artisan dusk tests\Manual\src\ManualVideo.php
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
 *         |   +---mp3
 *         |   |   +---mizuki
 *         |   |       +---index.mp3   (index.png  に対応したmp3)
 *         |   |       +---index2.mp3  (index2.png に対応したmp3)
 *         |   +---mp4
 *         |       +---mizuki
 *         |           +---_mp4list.txt           (このディレクトリのmp4リスト)
 *         |           +---nofade_index.mp4       (index.png  に対応したmp4 - フェードなし)
 *         |           +---nofade_index2_tmp.mp4  (index2.png に対応したmp4 - フェードなし)
 *         |           +---fade_index.mp4         (index.png  に対応したmp4 - フェードあり)
 *         |           +---fade_index2.mp4        (index2.png に対応したmp4 - フェードあり)
 *         +---show
 *
 * manual
 * +---user
 *     +---blogs
 *         +---mp4
 *             +---mizuki
 *                 +---index.mp4     (blog プラグインの動画(各メソッドの1個目))
 *             +---takumi
 *         +---index
 *             +---mp4
 *                 +---mizuki
 *                     +---index.mp4 (index.png & index2.png に対応したmp4)
 *         +---show
 *             +---mp4
 *                 +---mizuki
 *                     +---show.mp4  (show.png  に対応したmp4)
 *         +---create
 *             +---mp4
 *                 +---mizuki
 *                     +---create.mp4 (create.png & create2.png に対応したmp4)
 *
 * --- 処理シーケンス
 * testVideo  // 起点
 * foreach -> outputCategory
 *     foreach -> outputPlugin
 *         foreach -> outputMethod
 *
 * outputMethod
 *     getMethodMaterials($method = dusk)           // 動画素材のまとめ（メソッド用）
 *         getMaterial($json_path, $first_comment)  // 画像１つから、動画素材のまとめ
 *     createMovie($materials)                      // 動画生成
 *
 * outputPlugin
 *     getPluginMaterials($method = dusk)           // 動画素材のまとめ（プラグイン用）
 *         getMaterial($json_path, $first_comment)  // 画像１つから、動画素材のまとめ
 *     createMovie($materials)                      // 動画生成
 *
 * outputCategory
 *     getCategoryMaterials($method = dusk)         // 動画素材のまとめ（カテゴリ用）
 *         getMaterial($json_path, $first_comment)  // 画像１つから、動画素材のまとめ
 *     createMovie($materials)                      // 動画生成
 */
class ManualVideo extends DuskTestCase
{
    /**
     * AWS SDK
     */
    private $sdk;

    /**
     * AWS polly
     */
    private $polly;

    /**
     * スクリーンショット保存ルートパス
     */
    private $screenshots_root;

    /**
     * 動画強制生成
     */
    private $force_create_mp4 = false;

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
     * AWS SDK
     *
     * @return void
     */
    private function createSdk()
    {
        if (empty($this->sdk)) {
            $this->sdk = new \Aws\Sdk([
                'region'   => config('connect.AWS')['region'],
                'version'  => 'latest',
                'credentials' => [
                    'key' => config('connect.AWS')['key'],
                    'secret' => config('connect.AWS')['secret'],
                ],
            ]);
        }
        return $this->sdk;
    }

    /**
     * AWS Polly
     *
     * @return void
     */
    private function createPolly()
    {
        if (empty($this->polly)) {
            $this->polly = $this->sdk->createPolly();
        }
        return $this->polly;
    }

    /**
     * MP3 生成
     *
     * @return void
     */
    private function createMp3($text)
    {
        $response = $this->polly->synthesizeSpeech([
            'OutputFormat'  => 'mp3',
            'Text'          => $text,
            'VoiceId'       => 'Mizuki',
            //'VoiceId'       => 'Takumi',
            'TextType'      => 'ssml',
            //'TextType'      => 'string',
        ]);
        return $response['AudioStream'];
    }

    /**
     * 動画生成
     */
    private function createMovie($materials)
    {
        // 素材がない場合は動画を生成しない。
        if (empty($materials)) {
            return;
        }

        // 強制的に動画生成する際は、リストを削除しておく。
        if ($this->force_create_mp4) {
            \Storage::disk('tests_tmp')->delete($materials[0]['mp4_list_disk']);
        }

        // 画像とコメントのループ
        foreach ($materials as $index => $material) {
            // mp3 生成（ない場合のみ）
            if (!\Storage::disk('tests_tmp')->exists($material['mp3_file_disk'])) {
                \Storage::disk('tests_tmp')->put($material['mp3_file_disk'], $this->createMp3('<speak>' . $material['comment'] . '</speak>'));
            }

            // mp4 生成（mp4 がない場合）
            if (!\Storage::disk('tests_tmp')->exists($material['mp4_final_disk']) || $this->force_create_mp4) {

                \Storage::disk('tests_tmp')->makeDirectory(dirname($material['mp4_fade_disk']));

                // \Log::debug('mp4_final_disk = ' . $material['mp4_final_disk']);

                // mp3 と画像を合成してmp4 を生成
                // この方法だと、音ズレ（絵ズレ？）が起こったので、Webを参考に、別形式に変換後、MP4 を生成する。
                //$ffmpg_cmd = config('connect.FFMPEG_PATH') . ' -y -loop 1 -i ' . $material['img_file_real'] . ' -i ' . $material['mp3_file_real'] . ' -vcodec libx264 -acodec aac -ab 160k -ac 2 -ar 48000 -pix_fmt yuv420p -shortest ' . $material['mp4_nofade_real'];
                //system($ffmpg_cmd);

                // 一旦、mpeg2
                $ffmpg_cmd = config('connect.FFMPEG_PATH') . ' -y -loop 1 -i ' . $material['img_file_real'] . ' -i ' . $material['mp3_file_real'] . ' -vcodec mpeg2video -b:v 12000k -acodec pcm_s16le -shortest ' . str_replace('.mp4', '.avi', $material['mp4_nofade_real']);
                system($ffmpg_cmd);

                $ffmpg_cmd = config('connect.FFMPEG_PATH') . ' -y -i ' . str_replace('.mp4', '.avi', $material['mp4_nofade_real']) . ' -vcodec libx264 -acodec aac -pix_fmt yuv420p ' . $material['mp4_nofade_real'];
                system($ffmpg_cmd);

                // 動画にフェードの処理。最初、最後、真ん中でフェードを変える。
                if ($index === array_key_first($materials)) {
                    // 最初(始端処理)
                    $ffmpg_cmd = config('connect.FFMPEG_PATH') . ' -y -i ' . $material['mp4_nofade_real'] . ' -vf reverse,fade=d=1.0,reverse -c:a copy ' . $material['mp4_fade_real'];
                } elseif ($index === array_key_last($materials)) {
                    $ffmpg_cmd = config('connect.FFMPEG_PATH') . ' -y -i ' . $material['mp4_nofade_real'] . ' -vf fade=d=1.0 -c:a copy ' . $material['mp4_fade_real'];
                } else {
                    $ffmpg_cmd = config('connect.FFMPEG_PATH') . ' -y -i ' . $material['mp4_nofade_real'] . ' -vf fade=d=1.0,reverse,fade=d=0.5,reverse -c:a copy ' . $material['mp4_fade_real'];
                }
                system($ffmpg_cmd);

                // 動画の結合用に動画リストを生成する。
                \Storage::disk('tests_tmp')->append($material['mp4_list_disk'], 'file ' . $material['mp4_fade_real2']);
            }
        }

        // mp4 生成（完成したmp4 がない場合）
        if (!\Storage::disk('tests_tmp')->exists($materials[0]['mp4_final_disk']) || $this->force_create_mp4) {
            // 動画の結合
            $ffmpg_cmd = config('connect.FFMPEG_PATH') . ' -y -f concat -safe 0 -i ' . $materials[0]['mp4_list_real'] . ' -c copy ' . $materials[0]['mp4_final_real'];
            //$ffmpg_cmd = config('connect.FFMPEG_PATH') . ' -y -f concat -safe 0 -i ' . $materials[0]['mp4_list_real'] . ' -c:v libx264 -c:a aac -map 0:v -map 0:a ' . $materials[0]['mp4_final_real'];
            system($ffmpg_cmd);

            // 動画のマニュアルディレクトリへのコピー
            if (!\File::exists(dirname($materials[0]['mp4_manual_real']))) {
                \File::makeDirectory(dirname($materials[0]['mp4_manual_real']), 0755, true);
            }
            // mp4
            \File::copy($materials[0]['mp4_final_real'], $materials[0]['mp4_manual_real']);
            // poster画像
            \File::copy($materials[0]['img_file_real'], $materials[0]['mp4_manual_poster']);
        }
    }

    /**
     * カテゴリ出力
     *
     * @return void
     */
    private function outputCategory($plugins)
    {
        // 動画生成に必要な内容を編集
        $materials = Dusks::getCategoryMaterials($plugins);

        // 動画生成
        $this->createMovie($materials);
    }

    /**
     * プラグイン出力
     *
     * @return void
     */
    private function outputPlugin($methods)
    {
        // 動画生成に必要な内容を編集
        $materials = Dusks::getPluginMaterials($methods);

        // 動画生成
        $this->createMovie($materials);
    }

    /**
     * メソッド出力
     *
     * @return void
     */
    private function outputMethod($method, $first_comment = null)
    {
        // 動画生成に必要な内容を編集
        $materials = $method->getMethodMaterials($first_comment);
        //print_r($materials);

        // 動画生成
        $this->createMovie($materials);
    }

    /**
     * マニュアルビデオ出力用クラス
     *
     * @return void
     */
    public function testVideo()
    {
        require config('connect.REQUIRE_AWS_SDK_PATH');

        $this->createSdk();
        $this->createPolly();

        // Laravel がコンストラクタでbase_path など使えないので、ここで。
        $this->screenshots_root = base_path('tests/Browser/screenshots/');

        // マニュアル出力のために、dusk データベースなど利用するので、アサーションは無条件にOKとしたい。
        $this->assertTrue(true);

        // 全データ取得
        $dusks = Dusks::whereNotIn('category', ['top', 'blueprint'])->orderBy("id", "asc")->get();

        // テスト用データ取得
//        $dusks = Dusks::where('category', 'common')->orderBy("id", "asc")->get();
//        $dusks = Dusks::where('plugin_name', 'blogs')->where('method_name', 'index')->orderBy("id", "asc")->get();
//        $dusks = Dusks::where('plugin_name', 'blogs')->orderBy("id", "asc")->get();
//        $dusks = Dusks::whereIn('plugin_name', ['blogs', 'photoalbums'])->orderBy("id", "asc")->get();
//        $dusks = Dusks::whereIn('plugin_name', ['photoalbums'])->whereIn('method_name', ['index', 'makeFolder'])->orderBy("id", "asc")->get();

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
            $this->outputCategory($dusks->where('category', $category[0]->category)->where('method_name', 'index'));

            // プラグインのループ
            foreach ($dusks->where('category', $category[0]->category)->where('method_name', 'index') as $plugin) {
                $this->outputPlugin($dusks->where('category', $plugin->category)->where('plugin_name', $plugin->plugin_name));

                // メソッドのループ
                foreach ($dusks->where('category', $category[0]->category)->where('plugin_name', $plugin->plugin_name) as $method) {
                    $this->outputMethod($method);
                }
            }
        }

        // トップページ用の動画生成（dsuk レコードは1件の想定）
        $top_method = Dusks::where('category', 'top')->orderBy("id", "asc")->first();
        if (!empty($top_method)) {
            $first_comment = $top_method->plugin_desc;
            $this->outputMethod($top_method, $first_comment);
        }
    }
}
