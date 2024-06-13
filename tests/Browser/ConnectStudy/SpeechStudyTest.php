<?php

namespace Tests\Browser\ConnectStudy;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

use App\User;

use App\Enums\ManualCategory;
use App\Enums\PluginName;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Core\Dusks;

/**
 * SpeechStudyテスト
 */
class SpeechStudyTest extends DuskTestCase
{
    /**
     * SpeechStudyテスト
     */
    public function test()
    {
        $this->category = 'study';

        $this->init();
        $this->index();
        $this->hearing();
    }

    /**
     * 初期処理
     */
    private function init()
    {
        $this->login(1);

        // データクリア
        $this->initPlugin('speechstudies', '/study/speech');

        // 最初にマニュアルの順番確定用にメソッドを指定する。
        $this->reserveManualMin('study', 'speechstudies', ['index', 'hearing']);

        // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('speechstudies', '/study/speech', 2);

        $this->logout();
    }

    /**
     * インデックス
     */
    private function index()
    {
        // スクリーンショット取得
        $this->browse(function (Browser $browser) {
            $browser->visit('/study/speech')
                    ->screenshot('study/speechstudies/index/images/index')
                    ->click('#testCompany1_' . $this->test_frame->id)
                    ->screenshot('study/speechstudies/index/images/company1')
                    ->click('#testCompany2_' . $this->test_frame->id)
                    ->screenshot('study/speechstudies/index/images/company2');
        });

        // マニュアル用データ出力
        $dusk = Dusks::updateOrCreate([
            'plugin_name' => 'speechstudies',
            'method_name' => 'index'
        ], [
            'category' => 'study',
            'sort' => 6,
            'plugin_name' => 'speechstudies',
            'plugin_title' => 'SpeechStudy',
            'plugin_desc' => 'AIによる、音声合成を体験できます。<br />SpeechStudyはConnect-CMSのオプションプラグインで、外部サービス設定が必要です。',
            'method_name' => 'index',
            'method_title' => '音声合成',
            'method_desc' => '入力したテキストと選択した声や速度で、音声合成を体験できます。',
            'method_detail' => 'サーバにある「AI音声合成プログラム」で入力したテキストを音声に変換します。',
            'html_path' => 'study/speechstudies/index/index.html',
            'img_args' => '[
                {"path": "study/speechstudies/index/images/index",
                 "name": "音声合成画面",
                 "comment": "<ul class=\"mb-0\"><li>読み上げボタンを押すことで、指定したテキストが音声合成されます。実は、私、' . ManualCategory::getVoiceId("study") . 'もこの音声合成で作成されています。</li></ul>"
                },
                {"path": "study/speechstudies/index/images/company1",
                 "name": "株式会社のアクセントその１",
                 "comment": "<ul class=\"mb-0\"><li>株式会社のアクセントが少しおかしいことを体験できます。</li></ul>"
                },
                {"path": "study/speechstudies/index/images/company2",
                 "name": "株式会社のアクセントその２",
                 "comment": "<ul class=\"mb-0\"><li>株式会社にアクセント記号を追加して、発声を補正している例です。</li></ul>"
                }
            ]',
            'level' => null,
            'test_result' => 'OK',
        ]);
    }

    /**
     * 音声入力
     */
    private function hearing()
    {
        $this->login(1);

        // プラグインがなければ追加(テストするFrameとページのインスタンス変数への保持も)
        $this->addPluginFirst('speechstudies', '/study/hearing', 2);

        $this->test_frame->template = 'recognition';
        $this->test_frame->save();

        $this->logout();

        // スクリーンショット取得
        $this->browse(function (Browser $browser) {
            $browser->visit('/study/hearing')
                    ->screenshot('study/speechstudies/hearing/images/index');
        });

        // マニュアル用データ出力
        $dusk = Dusks::updateOrCreate([
            'plugin_name' => 'speechstudies',
            'method_name' => 'hearing'
        ], [
            'category' => 'study',
            'sort' => 6,
            'plugin_name' => 'speechstudies',
            'plugin_title' => 'SpeechStudy',
            'plugin_desc' => 'AIによる、音声合成を体験できます。<br />SpeechStudyはConnect-CMSのオプションプラグインで、外部サービス設定が必要です。',
            'method_name' => 'hearing',
            'method_title' => '音声入力',
            'method_desc' => '音声入力の体験ができます。音声入力したテキストと選択した声や速度で、音声合成を体験できます。',
            'method_detail' => '音声入力で入力した内容で、サーバにある「AI音声合成プログラム」で入力したテキストを音声に変換します。',
            'html_path' => 'study/speechstudies/hearing/index.html',
            'img_args' => '[
                {"path": "study/speechstudies/hearing/images/index",
                 "name": "音声入力・合成画面",
                 "comment": "<ul class=\"mb-0\"><li>音声入力開始ボタンを押すことで、音声を入力できるようになります。</li></ul>"
                }
            ]',
            'level' => null,
            'test_result' => 'OK',
        ]);
    }
}
