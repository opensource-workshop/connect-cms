<?php

namespace Tests\Browser\User;

use App\Enums\FormColumnType;
use App\Enums\FormMode;
use App\Enums\FormStatusType;
use App\Enums\PluginName;
use App\Enums\StatusType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Uploads;
use App\Models\User\Bbses\Bbs;
use App\Models\User\Bbses\BbsPost;
use App\Models\User\Blogs\Blogs;
use App\Models\User\Blogs\BlogsPosts;
use App\Models\User\Faqs\Faqs;
use App\Models\User\Faqs\FaqsPosts;
use App\Models\User\Forms\Forms;
use App\Models\User\Forms\FormsColumns;
use App\Models\User\Forms\FormsInputCols;
use App\Models\User\Forms\FormsInputs;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * フォームの他プラグイン連携テスト
 *
 * @see https://github.com/opensource-workshop/connect-cms/wiki/Dusk#テスト実行 [How to test]
 */
class FormsPluginLinkageTest extends DuskTestCase
{
    private const LINKAGE_TEXT = "フォーム連携の本文です。\n2行目の本文です。\n3行目の本文です。";

    /** @var Frame フォームフレーム */
    private $form_frame;

    /** @var Frame ブログ連携フレーム */
    private $blogs_frame;

    /** @var Frame 非連携のブログフレーム（同じバケツを共有） */
    private $second_blogs_frame;

    /** @var Frame 掲示板連携フレーム */
    private $bbses_frame;

    /** @var Frame 2ページ目の掲示板フレーム（同じバケツを共有） */
    private $second_bbses_frame;

    /** @var Frame FAQ連携フレーム */
    private $faqs_frame;

    /** @var Forms フォーム */
    private $form;

    /** @var FormsColumns 連携本文用の項目 */
    private $text_column;

    /** @var FormsColumns 1行テキスト項目 */
    private $name_column;

    /** @var FormsColumns ファイル項目 */
    private $file_column;

    /**
     * フォームのブログ・掲示板・FAQ連携を検証する
     *
     * @group user
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests
     */
    public function testOtherPluginsLinkage()
    {
        $this->init();
        $this->login(1);

        $this->createLinkageTargets();
        $this->createForm();
        $this->configureLinkage();

        $this->logout();
        $this->submitForm();

        $this->login(1);
        $this->registerOtherPlugins();
        $this->assertLinkagePosts();
    }

    /**
     * テストデータの初期処理
     */
    private function init()
    {
        Forms::truncate();
        FormsColumns::truncate();
        FormsInputCols::truncate();
        FormsInputs::truncate();

        BlogsPosts::truncate();
        BbsPost::truncate();
        FaqsPosts::truncate();

        Blogs::truncate();
        Bbs::truncate();
        Faqs::truncate();

        Uploads::truncate();
        Storage::deleteDirectory(config('connect.directory_base'));

        $this->initPlugin(PluginName::getPluginName(PluginName::forms), '/test/form-linkage', 2);
        $this->initPlugin(PluginName::getPluginName(PluginName::blogs), '/test/linkage-blog', 2);
        $this->initPlugin(PluginName::getPluginName(PluginName::bbses), '/test/linkage-bbs', 2);
        $this->initPlugin(PluginName::getPluginName(PluginName::faqs), '/test/linkage-faq', 2);
    }

    /**
     * 連携先フレームとバケツを作成する
     */
    private function createLinkageTargets()
    {
        $this->createTarget(PluginName::getPluginName(PluginName::blogs), 'linkage-blog', '連携先ブログ', new Blogs());

        // 同じバケツを別ページにも配置
        $this->addPluginFirst(PluginName::getPluginName(PluginName::blogs), '/test/linkage-blog2', 2);
        $this->test_frame->bucket_id = $this->blogs_frame->bucket_id;
        $this->test_frame->save();
        $this->second_blogs_frame = Frame::find($this->test_frame->id);

        $this->createTarget(PluginName::getPluginName(PluginName::bbses), 'linkage-bbs', '連携先掲示板', new Bbs());

        // 同じバケツを別ページにも配置
        $this->addPluginFirst(PluginName::getPluginName(PluginName::bbses), '/test/linkage-bbs2', 2);
        $this->test_frame->bucket_id = $this->bbses_frame->bucket_id;
        $this->test_frame->save();
        $this->second_bbses_frame = Frame::find($this->test_frame->id);

        $this->createTarget(PluginName::getPluginName(PluginName::faqs), 'linkage-faq', '連携先FAQ', new Faqs());
    }

    /**
     * 指定プラグインの連携先を作成する
     */
    private function createTarget($plugin_name, $linkage_name, $bucket_name, $target)
    {
        $this->addPluginFirst($plugin_name, '/test/' . $linkage_name, 2);

        $bucket = Buckets::create([
            'bucket_name' => $bucket_name,
            'plugin_name' => $plugin_name,
            'container_page_id' => $this->test_frame->page_id,
        ]);

        $target->bucket_id = $bucket->id;
        if ($plugin_name === PluginName::getPluginName(PluginName::blogs)) {
            // blog
            $target->blog_name = $bucket_name;
        } elseif ($plugin_name === PluginName::getPluginName(PluginName::bbses)) {
            // bbs
            $target->name = $bucket_name;
        } else {
            // faq
            $target->faq_name = $bucket_name;
            $target->view_count = 10;
        }
        $target->save();

        $this->test_frame->bucket_id = $bucket->id;
        $this->test_frame->save();
        $this->{$plugin_name . '_frame'} = Frame::find($this->test_frame->id);
    }

    /**
     * フォームと本文項目を作成する
     */
    private function createForm()
    {
        $this->addPluginFirst(PluginName::getPluginName(PluginName::forms), '/test/form-linkage', 2);

        $bucket = Buckets::create([
            'bucket_name' => '連携テストフォーム',
            'plugin_name' => PluginName::getPluginName(PluginName::forms),
            'container_page_id' => $this->test_frame->page_id,
        ]);

        $this->test_frame->bucket_id = $bucket->id;
        $this->test_frame->save();
        $this->form_frame = Frame::find($this->test_frame->id);

        $this->form = Forms::create([
            'bucket_id' => $bucket->id,
            'forms_name' => '連携テストフォーム',
            'form_mode' => FormMode::form,
            'data_save_flag' => 1,
            'after_message' => '連携テストの送信ありがとうございます。',
        ]);

        $this->text_column = FormsColumns::create([
            'forms_id' => $this->form->id,
            'column_type' => FormColumnType::textarea,
            'column_name' => '連携本文',
            'required' => 1,
            'display_sequence' => 1,
        ]);

        $this->name_column = FormsColumns::create([
            'forms_id' => $this->form->id,
            'column_type' => FormColumnType::text,
            'column_name' => '登録者名',
            'required' => 1,
            'display_sequence' => 2,
        ]);

        $this->file_column = FormsColumns::create([
            'forms_id' => $this->form->id,
            'column_type' => FormColumnType::file,
            'column_name' => '添付ファイル',
            'required' => 1,
            // 'rule_file_extensions' => 'pdf',
            // 'rule_file_max_kb' => 10240,
            'display_sequence' => 3,
        ]);
    }

    /**
     * フォーム設定画面で3種類の連携先を設定する
     */
    private function configureLinkage()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/plugin/forms/editBuckets/' . $this->form_frame->page_id . '/' . $this->form_frame->id)
                    ->pause(300);

            $browser->driver->executeScript("var el = document.querySelector('.phpdebugbar'); if (el) el.remove();");

            $browser->assertSee('他プラグイン連携')
                    ->click('label[for=other_plugins_register_use_flag]')
                    ->pause(500)
                    ->scrollIntoView('#collapse_other_plugins' . $this->form_frame->id)
                    ->pause(300)
                    ->click('a[href="#blogs' . $this->form_frame->id . '"]')
                    ->pause(300)
                    ->click('label[for=target_plugins_frame_' . $this->blogs_frame->id . ']')
                    ->click('a[href="#bbses' . $this->form_frame->id . '"]')
                    ->pause(300)
                    ->click('label[for=target_plugins_frame_' . $this->bbses_frame->id . ']')
                    ->click('label[for=target_plugins_frame_' . $this->second_bbses_frame->id . ']')
                    ->click('a[href="#faqs' . $this->form_frame->id . '"]')
                    ->pause(300)
                    ->click('label[for=target_plugins_frame_' . $this->faqs_frame->id . ']')
                    ->press('変更確定')
                    ->pause(500);
        });

        // 設定が変更できたか
        //
        $this->form->refresh();
        $this->assertEquals(1, $this->form->other_plugins_register_use_flag);

        $target_ids = explode(',', $this->form->target_frame_ids);
        $this->assertContains((string) $this->blogs_frame->id, $target_ids);
        $this->assertContains((string) $this->bbses_frame->id, $target_ids);
        $this->assertContains((string) $this->second_bbses_frame->id, $target_ids);
        $this->assertEquals($this->bbses_frame->bucket_id, $this->second_bbses_frame->bucket_id);
        $this->assertContains((string) $this->faqs_frame->id, $target_ids);
        $this->assertNotContains((string) $this->second_blogs_frame->id, $target_ids);
        $this->assertEquals($this->blogs_frame->bucket_id, $this->second_blogs_frame->bucket_id);
    }

    /**
     * 一般画面からフォームを送信する
     */
    private function submitForm()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/test/form-linkage')
                    ->assertSee('連携テストフォーム')
                    ->type('forms_columns_value[' . $this->name_column->id . ']', '連携テスト太郎')
                    ->type('forms_columns_value[' . $this->text_column->id . ']', self::LINKAGE_TEXT)
                    ->attach('forms_columns_value[' . $this->file_column->id . ']', __DIR__.'/form/ドキュメント.pdf')
                    ->press('確認画面へ')
                    ->pause(500)
                    ->assertSee('連携テスト太郎')
                    ->assertSee('フォーム連携の本文です。')
                    ->assertSee('2行目の本文です。')
                    ->assertSee('3行目の本文です。')
                    ->assertSee('ドキュメント.pdf');
        });

        $this->browse(function (Browser $browser) {
            $browser->press('送信')
                    ->pause(1000);
        });

        $this->browse(function (Browser $browser) {
            $browser->visit('/test/form-linkage')
                    ->pause(500);
        });

        $input = FormsInputs::where('forms_id', $this->form->id)->latest('id')->first();
        $this->assertNotNull($input);
        $this->assertEquals(FormStatusType::active, $input->status);

        $input_col = FormsInputCols::where('forms_inputs_id', $input->id)
            ->where('forms_columns_id', $this->text_column->id)
            ->first();
        $this->assertNotNull($input_col);
        $this->assertEquals(self::LINKAGE_TEXT, $this->normalizeLineEndings($input_col->value));

        $name_col = FormsInputCols::where('forms_inputs_id', $input->id)
            ->where('forms_columns_id', $this->name_column->id)
            ->first();
        $this->assertNotNull($name_col);
        $this->assertEquals('連携テスト太郎', $name_col->value);

        $file_col = FormsInputCols::where('forms_inputs_id', $input->id)
            ->where('forms_columns_id', $this->file_column->id)
            ->first();
        $this->assertNotNull($file_col);
        $upload = Uploads::find($file_col->value);
        $this->assertNotNull($upload);
        $this->assertEquals('ドキュメント.pdf', $upload->client_original_name);
    }

    /**
     * 登録一覧画面から他プラグインへ連携する
     */
    private function registerOtherPlugins()
    {
        $input = FormsInputs::where('forms_id', $this->form->id)->latest('id')->first();

        $this->browse(function (Browser $browser) use ($input) {
            $browser->visit('/plugin/forms/listInputs/' . $this->form_frame->page_id . '/' . $this->form_frame->id)
                    ->assertSee('連携')
                    ->click('button[onclick="submit_register_other_plugins(' . $input->id . ');"]')
                    ->acceptDialog()
                    ->pause(1000);
        });
    }

    /**
     * 連携先に一時保存記事が作成されたことを検証する
     */
    private function assertLinkagePosts()
    {
        $title = 'フォーム「連携テストフォーム」から連携';

        // blog
        //
        $blog_post = BlogsPosts::where('blogs_id', Blogs::where('bucket_id', $this->blogs_frame->bucket_id)->first()->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($blog_post, 'ブログ連携記事なし、連携が失敗しています。ログ等を確認してください。');
        $this->assertEquals(StatusType::temporary, $blog_post->status);
        $this->assertEquals($title, $blog_post->post_title);
        $blog_text = $this->normalizeLineEndings($blog_post->post_text);
        $this->assertStringContainsString("フォーム連携の本文です。<br />\n", $blog_text);
        $this->assertStringContainsString('2行目の本文です。', $blog_text);
        $this->assertStringContainsString('3行目の本文です。', $blog_text);
        $this->assertMatchesRegularExpression('/<a href="[^"]+\/file\/\d+"[^>]*>ドキュメント\.pdf<\/a>/', $blog_text);

        $this->assertSame(1, preg_match('/\/file\/(\d+)/', $blog_text, $matches));
        $linked_upload = Uploads::find((int) $matches[1]);
        $this->assertNotNull($linked_upload, 'ブログ連携本文のアップロードファイルレコードがありません。');
        $this->assertNotEquals($this->form_frame->page_id, $linked_upload->page_id);
        // 同じバケツを2つのページに配置し、片方だけを連携先に選んだとき、複製ファイルの page_id が選んだフレームのページになる
        $this->assertEquals($this->blogs_frame->page_id, $linked_upload->page_id, 'ブログ連携本文のアップロードファイルのページIDが、選択したブログフレームのページIDであること。');

        // bbs
        //
        $bbs_id = Bbs::where('bucket_id', $this->bbses_frame->bucket_id)->first()->id;
        // 同じバケツのフレームを2つとも連携先に選んでも、投稿は1件だけ作られる（二重投稿の防止が保たれている）
        $this->assertEquals(1, BbsPost::where('bbs_id', $bbs_id)->count(), '同じバケツへ2フレームから連携しても、掲示板連携記事は1件であること。');

        $bbs_post = BbsPost::where('bbs_id', $bbs_id)
            ->latest('id')
            ->first();
        $this->assertNotNull($bbs_post, 'BBS連携記事なし、連携が失敗しています。ログ等を確認してください。');
        $this->assertEquals(StatusType::temporary, $bbs_post->status);
        $this->assertEquals($title, $bbs_post->title);
        $bbs_body = $this->normalizeLineEndings($bbs_post->body);
        $this->assertStringContainsString("フォーム連携の本文です。<br />\n", $bbs_body);
        $this->assertStringContainsString('2行目の本文です。', $bbs_body);
        $this->assertStringContainsString('3行目の本文です。', $bbs_body);
        $this->assertMatchesRegularExpression('/<a href="[^"]+\/file\/\d+"[^>]*>ドキュメント\.pdf<\/a>/', $bbs_body);

        // faq
        //
        $faq_post = FaqsPosts::where('faqs_id', Faqs::where('bucket_id', $this->faqs_frame->bucket_id)->first()->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($faq_post, 'FAQ連携記事なし、連携が失敗しています。ログ等を確認してください。');
        $this->assertEquals(StatusType::temporary, $faq_post->status);
        $this->assertEquals($title, $faq_post->post_title);
        $faq_text = $this->normalizeLineEndings($faq_post->post_text);
        $this->assertStringContainsString("フォーム連携の本文です。<br />\n", $faq_text);
        $this->assertStringContainsString('2行目の本文です。', $faq_text);
        $this->assertStringContainsString('3行目の本文です。', $faq_text);
        $this->assertMatchesRegularExpression('/<a href="[^"]+\/file\/\d+"[^>]*>ドキュメント\.pdf<\/a>/', $faq_text);
    }

    /**
     * CRLFをLFへ正規化する
     */
    private function normalizeLineEndings($text)
    {
        $text = str_replace("\r\n", "\n", $text);
        return str_replace("\r", "\n", $text);
    }
}
