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
     * 実際のパスの取得
     */
    private function getRealPath($disk, $path)
    {
        // リアルパスに変換
        $path = \Storage::disk($disk)->path($path);

        // Windows のffmpeg が / だとディレクトリを認識してくれない部分があるので、/ を \ に変換
        // エラーになっていたのは動画結合時の mp4_list のパス
        return str_replace('/', '\\', $path);
    }

    /**
     * 動画編集に必要なファイルパスや文章などを組み立てる。
     */
    private function getMaterials($method)
    {
        /* 戻り値の例
        (
            [0] => Array
                (
                    [mp3_file_disk]   => user/blogs/index/mp3/mizuki/index.mp3
                    [mp4_dir_disk]    => user/blogs/index/mp4/mizuki
                    [mp4_list_disk]   => user/blogs/index/mp4/mizuki/_mp4list.txt
                    [mp4_fade_disk]   => user/blogs/index/mp4/mizuki/fade_index.mp4
                    [mp4_final_disk]  => user/blogs/index/mp4/mizuki/_video.mp4
                    [img_file_real]   => C:\***\tests\Browser\screenshots\user\blogs\index\images\index.png
                    [mp3_file_real]   => C:\***\tests\tmp\user\blogs\index\mp3\mizuki\index.mp3
                    [mp4_nofade_real] => C:\***\tests\tmp\user\blogs\index\mp4\mizuki\nofade_index.mp4
                    [mp4_fade_real]   => C:\***\tests\tmp\user\blogs\index\mp4\mizuki\fade_index.mp4
                    [mp4_fade_real2]  => C:\\SitesLaravel\\connect-cms\\htdocs\\test.localhost\\tests\\tmp\\user\\blogs\\index\\mp4\\mizuki\\fade_index.mp4
                    [mp4_list_real]   => C:\***\tests\tmp\user\blogs\index\mp4\mizuki\_mp4list.txt
                    [mp4_final_real]  => C:\***\tests\tmp\user\blogs\index\mp4\mizuki\_video.mp4
                    [mp4_manual_real] => C:\SitesLaravel\connect-cms-manual\user/blogs/index/mp4/mizuki/_video.mp4
                    [comment]         => 記事は新しいものから表示されます。
                )
            [1] => Array
        )
        */

        /* img_args の例
        [
            {"path": "user/blogs/index/images/index",
             "name": "記事の一覧",
             "comment": "<ul class=\"mb-0\"><li>記事は新しいものから表示されます。</li></ul>"
            },
            {"path": "user/blogs/index/images/index2",
             "name": "記事のコピー",
             "comment": "<ul class=\"mb-0\"><li>編集権限がある場合、記事の編集ボタンの右にある▼ボタンで、記事のコピーができます。</li></ul>"
            }
        ]
        */

        $materials = array();

        // 最初の動画の説明
        $first_comment  = 'ここでは、' . $method->plugin_title . '　プラグインの　' . $method->method_title . '　機能を紹介します。';
        $first_comment .= empty($method->method_desc) ? '' : '機能概要、' . $method->method_desc;
        $first_comment .= empty($method->method_detail) ? '' : '機能詳細、' . $method->method_detail;
        $first_comment .= '　続いて、画面を説明します。';

        // img_args が json か。
        if (json_decode($method->img_args)) {
            $json_paths = json_decode($method->img_args);
            foreach ($json_paths as $json_path) {
                // 画像にコメントがない場合は、動画を生成しない。
                if (property_exists($json_path, 'comment') && !empty($json_path->comment)) {
                    // 処理を続ける。
                } else {
                    continue;
                }

                // mp4 パスの組み立て（ファイル名にfade_, nofade_ を付けたい）
                $mp4_path_array = explode('/', str_replace('images/', 'mp4/mizuki/', $json_path->path));
                $mp4_path_array[array_key_last($mp4_path_array)] = 'nofade_' . $mp4_path_array[array_key_last($mp4_path_array)];
                $mp4_nofade_path = implode('/', $mp4_path_array) . '.mp4';
                $mp4_fade_path = str_replace('nofade_', 'fade_', $mp4_nofade_path);

                // 画面毎のナレーション文章
                $image_comment = property_exists($json_path, 'name') ? '画像　' . $json_path->name . 'を説明します。' : '';

                // ナレーション文章や必要なファイルパスを組み立て
                $materials[] = [
                    'mp3_file_disk'   => str_replace('images/', 'mp3/mizuki/', $json_path->path) . '.mp3',
                    'mp4_dir_disk'    => dirname($mp4_fade_path),
                    'mp4_list_disk'   => substr($mp4_fade_path, 0, strrpos($mp4_fade_path, '/')) . '/_mp4list.txt',
                    'mp4_fade_disk'   => $mp4_fade_path,
                    'mp4_final_disk'  => substr($mp4_fade_path, 0, strrpos($mp4_fade_path, '/')) . '/_video.mp4',
                    'img_file_real'   => $this->getRealPath('screenshot', $json_path->path . '.png'),
                    'mp3_file_real'   => $this->getRealPath('tests_tmp', str_replace('images/', 'mp3/mizuki/', $json_path->path) . '.mp3'),
                    'mp4_nofade_real' => $this->getRealPath('tests_tmp', $mp4_nofade_path),
                    'mp4_fade_real'   => $this->getRealPath('tests_tmp', $mp4_fade_path),
                    'mp4_fade_real2'  => str_replace('\\', '\\\\', $this->getRealPath('tests_tmp', $mp4_fade_path)), // ffmpeg concatは2重\が必要
                    'mp4_list_real'   => $this->getRealPath('tests_tmp', substr($mp4_fade_path, 0, strrpos($mp4_fade_path, '/')) . '/_mp4list.txt'),
                    'mp4_final_real'  => $this->getRealPath('tests_tmp', substr($mp4_fade_path, 0, strrpos($mp4_fade_path, '/')) . '/_video.mp4'),
                    'mp4_manual_real' => config('connect.manual_put_base') . substr($mp4_fade_path, 0, strrpos($mp4_fade_path, '/')) . '/_video.mp4',
                    'comment'         => $first_comment . $image_comment . $this->cleaningText($json_path->comment),
                ];
                $first_comment = '';
            }
        }
        return $materials;
    }

    /**
     * 動画生成
     */
    private function createMovie($materials)
    {
        // 画像とコメントのループ
        foreach ($materials as $index => $material) {
            // mp3 生成（ない場合のみ）
            if (!\Storage::disk('tests_tmp')->exists($material['mp3_file_disk'])){ 
                \Storage::disk('tests_tmp')->put($material['mp3_file_disk'], $this->createMp3('<speak>' . $material['comment'] . '</speak>'));
            }
            // mp4 生成（完成した mp4 がない場合）
            if (!\Storage::disk('tests_tmp')->exists($material['mp4_final_disk'])) {
                \Storage::disk('tests_tmp')->makeDirectory(dirname($material['mp4_fade_disk']));

                // mp3 と画像を合成してmp4 を生成
                $ffmpg_cmd = config('connect.FFMPEG_PATH') . ' -y -loop 1 -i ' . $material['img_file_real'] . ' -i ' . $material['mp3_file_real'] . ' -vcodec libx264 -acodec aac -ab 160k -ac 2 -ar 48000 -pix_fmt yuv420p -shortest ' . $material['mp4_nofade_real'];
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
        // 動画の結合
        $ffmpg_cmd = config('connect.FFMPEG_PATH') . ' -y -f concat -safe 0 -i ' . $materials[0]['mp4_list_real'] . ' -c copy ' . $materials[0]['mp4_final_real'];
        system($ffmpg_cmd);

        // 動画のマニュアルディレクトリへのコピー
        if (!\File::exists(dirname($materials[0]['mp4_manual_real']))) {
            \File::makeDirectory(dirname($materials[0]['mp4_manual_real']), 0755, true);
        }
        \File::copy($materials[0]['mp4_final_real'], $materials[0]['mp4_manual_real']);
    }

    /**
     * カテゴリ出力
     *
     * @return void
     */
    private function outputCategory($dusks, $category)
    {
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
        // 動画生成に必要な内容を編集
        $materials = $this->getMaterials($method);
        //print_r($materials);
//\Log::debug($method->method_title);
//\Log::debug($materials);

        // 動画生成(音声がない場合は動画も生成しない)
        if (!empty($materials)) {
            $this->createMovie($materials);
        }
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
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertTitleContains('Connect-CMS');
        });

        // 全データ取得
//        $dusks = Dusks::orderBy("id", "asc")->get();
//        $dusks = Dusks::where('plugin_name', 'blogs')->where('method_name', 'index')->orderBy("id", "asc")->get();
//        $dusks = Dusks::where('plugin_name', 'blogs')->orderBy("id", "asc")->get();
//        $dusks = Dusks::whereIn('plugin_name', ['blogs', 'photoalbums'])->orderBy("id", "asc")->get();
        $dusks = Dusks::whereIn('plugin_name', ['photoalbums'])->orderBy("id", "asc")->get();
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
            //$this->outputCategory($dusks, $category[0]);

            // プラグインのループ
            foreach ($dusks->where('category', $category[0]->category)->where('method_name', 'index') as $plugin) {
                //$this->outputPlugin($dusks, $category[0], $plugin);

                // メソッドのループ
                foreach ($dusks->where('category', $category[0]->category)->where('plugin_name', $plugin->plugin_name) as $method) {
                    $this->outputMethod($method);
                }
            }
        }
    }
}
