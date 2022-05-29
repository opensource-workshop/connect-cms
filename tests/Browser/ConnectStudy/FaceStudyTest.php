<?php

namespace Tests\Browser\ConnectStudy;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\User;

use App\Enums\PluginName;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Core\Dusks;

/**
 * FaceStudyテスト
 */
class FaceStudyTest extends DuskTestCase
{
    /**
     * FaceStudyテスト
     */
    public function test()
    {
        $this->category = 'study';

        $this->init();
        $this->index();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        // データクリア
        $this->initPlugin('facestudies', '/study/face');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManualMin('study', 'facestudies', ['index']);

        // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('facestudies', '/study/face', 2);
    }

    /**
     * インデックス
     */
    private function index()
    {
        // スクリーンショット取得
        $this->browse(function (Browser $browser) {
            $browser->visit('/study/face')
                    ->screenshot('study/facestudies/index/images/index')
                    ->attach('photo_' . $this->test_frame->id, __DIR__.'/face_and_dog.jpg')
                    ->screenshot('study/facestudies/index/images/attach_file')
                    ->press("アップロード＆判定")
                    ->pause(2000)
                    ->screenshot('study/facestudies/index/images/face_result')
                    ->scrollIntoView('footer')
                    ->screenshot('study/facestudies/index/images/face_result2')
                    ->scrollIntoView('#ccMainArea')
                    ->click('#label_method_eye_rectangle_' . $this->test_frame->id)
                    ->press("アップロード＆判定")
                    ->pause(2000)
                    ->screenshot('study/facestudies/index/images/face_eye')
                    ->click('#label_method_smile_' . $this->test_frame->id)
                    ->press("アップロード＆判定")
                    ->pause(2000)
                    ->screenshot('study/facestudies/index/images/face_smile');
        });

        // マニュアル用データ出力
        $dusk = Dusks::updateOrCreate([
            'plugin_name' => 'facestudies',
            'method_name' => 'index'
        ], [
            'category' => 'study',
            'sort' => 6,
            'plugin_name' => 'facestudies',
            'plugin_title' => 'FaceStudy',
            'plugin_desc' => 'AIによる、顔認識を体験できます。<br />FaceStudyはConnect-CMSのオプションプラグインで、外部サービス設定が必要です。',
            'method_name' => 'index',
            'method_title' => '顔認識',
            'method_desc' => '写真や画像で顔認識を体験できます。',
            'method_detail' => 'サーバにある「AI顔認識プログラム」で、アップロードした画像（写真）から顔を探します。',
            'html_path' => 'study/facestudies/index/index.html',
            'img_args' => '[
                {"path": "study/facestudies/index/images/index",
                 "name": "写真指定画面",
                 "comment": "<ul class=\"mb-0\"><li>写真や画像を指定してアップロードすることで、AIが顔を認識して、顔部分を四角で囲います。</li></ul>"
                },
                {"path": "study/facestudies/index/images/face_result",
                 "name": "顔認識の結果",
                 "comment": "<ul class=\"mb-0\"><li>背景など、一部のご認識もありますが、顔を認識しています。</li></ul>"
                },
                {"path": "study/facestudies/index/images/face_result2",
                 "name": "顔と犬の認識の結果",
                 "comment": "<ul class=\"mb-0\"><li>犬の顔は認識せず、人の顔は認識していることがわかります。</li></ul>"
                },
                {"path": "study/facestudies/index/images/face_eye",
                 "name": "目認識の結果",
                 "comment": "<ul class=\"mb-0\"><li>目を認識していることがわかります。</li></ul>"
                },
                {"path": "study/facestudies/index/images/face_smile",
                 "name": "笑顔の認識の結果",
                 "comment": "<ul class=\"mb-0\"><li>四角で示した顔の中で、笑顔と認識した部分が丸で表示されます。口元で笑顔を認識しているのがわかります。</li></ul>"
                }
            ]',
            'level' => null,
            'test_result' => 'OK',
        ]);
    }
}
