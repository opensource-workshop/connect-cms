<?php

namespace Tests\Browser\Manage;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * > tests\bin\connect-cms-test.bat
 */
class ServiceManageTest extends DuskTestCase
{
    /**
     * テストする関数の制御
     *
     * @group manage
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testInvoke()
    {
        $this->login(1);
        $this->index();
        $this->pdf();
        $this->face();
    }

    /**
     * WYSIWYG設定の表示
     */
    private function index()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/service')
                    ->assertTitleContains('Connect-CMS')
                    ->click('#lavel_use_translate_1')
                    ->click('#label_use_pdf_thumbnail_1')
                    ->click('#label_use_face_ai_1')
                    ->press('更新');

            $browser->visit('/manage/service')
                    ->screenshot('manage/service/index/images/index');
        });

        $this->browse(function (Browser $browser) {
            $browser->scrollIntoView('footer');
            $browser->screenshot('manage/service/index/images/index2');
        });

        // マニュアル用データ出力
        $this->putManualData('manage/service/index/images/index,manage/service/index/images/index2');
    }

    /**
     * PDFアップロードの表示
     */
    private function pdf()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/service/pdf')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/service/pdf/images/pdf');
        });

        // マニュアル用データ出力
        $this->putManualData('manage/service/pdf/images/pdf');
    }

    /**
     * AI顔認識アップロードの表示
     */
    private function face()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/manage/service/face')
                    ->assertTitleContains('Connect-CMS')
                    ->screenshot('manage/service/face/images/face');
        });

        // マニュアル用データ出力
        $this->putManualData('manage/service/face/images/face');
    }
}
