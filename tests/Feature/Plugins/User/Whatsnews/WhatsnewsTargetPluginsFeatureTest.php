<?php

namespace Tests\Feature\Plugins\User\Whatsnews;

use App\Enums\ContentOpenType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\Common\Uploads;
use App\Models\User\Cabinets\Cabinet;
use App\Models\User\Cabinets\CabinetContent;
use App\Models\User\Calendars\Calendar;
use App\Models\User\Calendars\CalendarPost;
use App\Models\User\Photoalbums\Photoalbum;
use App\Models\User\Photoalbums\PhotoalbumContent;
use App\Plugins\User\Cabinets\CabinetsPlugin;
use App\Plugins\User\Calendars\CalendarsPlugin;
use App\Plugins\User\Photoalbums\PhotoalbumsPlugin;
use App\Plugins\User\Whatsnews\WhatsnewTargetPluginTool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 新着情報の対象プラグイン追加を検証する。
 *
 * 設定画面に表示される対象プラグイン一覧と、各プラグインが公開する新着用クエリの契約を検証する。
 * 新着情報本体の描画ではなく、対象追加によって壊れやすい境界を公開メソッド経由で確認する方針。
 */
class WhatsnewsTargetPluginsFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 各テストでプラグイン一覧などの初期データを使えるようにする。
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * キャビネット・カレンダー・フォトアルバムが、新着情報設定の対象プラグインとして選択できること。
     */
    public function testTargetPluginsIncludeCabinetsCalendarsAndPhotoalbums(): void
    {
        $target_plugins = WhatsnewTargetPluginTool::getMembers();

        $this->assertArrayHasKey('cabinets', $target_plugins);
        $this->assertArrayHasKey('calendars', $target_plugins);
        $this->assertArrayHasKey('photoalbums', $target_plugins);
    }

    /**
     * 追加対象の各プラグインが、新着情報の共通列を持つ公開済みコンテンツを返すこと。
     */
    public function testAddedPluginsReturnWhatsnewRows(): void
    {
        $this->createCabinetContent('学校だより.pdf', '学校だよりをアップしました。');
        $this->createCalendarPost('授業参観', '授業参観のお知らせです。');
        $this->createPhotoalbumContent('遠足写真', '遠足の写真です。');

        [$cabinet_query, $cabinet_link_pattern, $cabinet_link_base] = CabinetsPlugin::getWhatsnewArgs();
        [$calendar_query, $calendar_link_pattern, $calendar_link_base] = CalendarsPlugin::getWhatsnewArgs();
        [$photoalbum_query, $photoalbum_link_pattern, $photoalbum_link_base] = PhotoalbumsPlugin::getWhatsnewArgs();

        $cabinet_row = $cabinet_query->where('cabinet_contents.name', '学校だより.pdf')->first();
        $calendar_row = $calendar_query->where('calendar_posts.title', '授業参観')->first();
        $photoalbum_row = $photoalbum_query->where('photoalbum_contents.name', '遠足写真')->first();

        $this->assertSame('show_page_frame_post', $cabinet_link_pattern);
        $this->assertSame('/plugin/cabinets/show', $cabinet_link_base);
        $this->assertSame('cabinets', $cabinet_row->plugin_name);
        $this->assertSame('学校だより.pdf', $cabinet_row->post_title);
        $this->assertSame('学校だよりをアップしました。', $cabinet_row->post_detail);

        $this->assertSame('show_page_frame_post', $calendar_link_pattern);
        $this->assertSame('/plugin/calendars/show', $calendar_link_base);
        $this->assertSame('calendars', $calendar_row->plugin_name);
        $this->assertSame('授業参観', $calendar_row->post_title);
        $this->assertSame('授業参観のお知らせです。', $calendar_row->post_detail);

        $this->assertSame('show_page_frame_post', $photoalbum_link_pattern);
        $this->assertSame('/plugin/photoalbums/show', $photoalbum_link_base);
        $this->assertSame('photoalbums', $photoalbum_row->plugin_name);
        $this->assertSame('遠足写真', $photoalbum_row->post_title);
        $this->assertSame('遠足の写真です。', $photoalbum_row->post_detail);
    }

    /**
     * キャビネットの新着リンク先アクションが、リダイレクトレスポンスではなく表示画面を返すこと。
     */
    public function testCabinetWhatsnewLinkTargetReturnsViewWithoutRedirectResponse(): void
    {
        $content = $this->createCabinetContent('学校だより.pdf', '学校だよりをアップしました。');
        $frame = $this->getFrameByBucketId(Cabinet::find($content->cabinet_id)->bucket_id);
        \Request::merge(['frame_configs' => new \Illuminate\Database\Eloquent\Collection()]);
        $plugin = new CabinetsPlugin(Page::find($frame->page_id), $frame, Page::defaultOrder()->get());

        $response = $plugin->show(request(), $frame->page_id, $frame->id, $content->id);

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertNotInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertSame('plugins.user.cabinets.default.index', $response->getName());
        $this->assertTrue($response->getData()['cabinet_contents']->contains('id', $content->id));
    }

    /**
     * フォトアルバムの新着リンク先アクションが、リダイレクトレスポンスではなく表示画面を返すこと。
     */
    public function testPhotoalbumWhatsnewLinkTargetReturnsViewWithoutRedirectResponse(): void
    {
        $content = $this->createPhotoalbumContent('遠足写真', '遠足の写真です。');
        $frame = $this->getFrameByBucketId(Photoalbum::find($content->photoalbum_id)->bucket_id);
        \Request::merge(['frame_configs' => new \Illuminate\Database\Eloquent\Collection()]);
        $plugin = new PhotoalbumsPlugin(Page::find($frame->page_id), $frame, Page::defaultOrder()->get());

        $response = $plugin->show(request(), $frame->page_id, $frame->id, $content->id);

        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertNotInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
        $this->assertSame('plugins.user.photoalbums.default.index', $response->getName());
        $this->assertTrue($response->getData()['photoalbum_image_items']->contains('id', $content->id));
    }

    /**
     * 指定プラグイン用のページ・バケツ・フレームをまとめて作成する。
     */
    private function createPluginFrame(string $plugin_name): array
    {
        $page = Page::factory()->create([
            'permanent_link' => "/test-{$plugin_name}",
        ]);

        $bucket = Buckets::factory()->create([
            'bucket_name' => "Test {$plugin_name}",
            'plugin_name' => $plugin_name,
        ]);

        $frame = Frame::factory()->create([
            'page_id' => $page->id,
            'area_id' => 2,
            'frame_title' => "Test {$plugin_name}",
            'plugin_name' => $plugin_name,
            'plug_name' => $plugin_name,
            'bucket_id' => $bucket->id,
            'content_open_type' => ContentOpenType::always_open,
        ]);

        return [$page, $bucket, $frame];
    }

    /**
     * 指定バケツを配置しているフレームを取得する。
     */
    private function getFrameByBucketId(int $bucket_id): Frame
    {
        return Frame::where('bucket_id', $bucket_id)->firstOrFail();
    }

    /**
     * キャビネットに新着対象のファイルを作成する。
     */
    private function createCabinetContent(string $name, string $comment): CabinetContent
    {
        [$page, $bucket] = $this->createPluginFrame('cabinets');

        $cabinet = Cabinet::create([
            'bucket_id' => $bucket->id,
            'name' => 'Test Cabinet',
            'upload_max_size' => 1024,
        ]);

        $root = CabinetContent::create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => null,
            'name' => $cabinet->name,
            'is_folder' => CabinetContent::is_folder_on,
            'parent_id' => null,
        ]);

        $upload = Uploads::create([
            'client_original_name' => $name,
            'mimetype' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 123,
            'plugin_name' => 'cabinets',
            'page_id' => $page->id,
            'temporary_flag' => 0,
        ]);

        $content = $root->children()->create([
            'cabinet_id' => $cabinet->id,
            'upload_id' => $upload->id,
            'name' => $name,
            'is_folder' => CabinetContent::is_folder_off,
        ]);
        $content->comment = $comment;
        $content->save();

        return $content;
    }

    /**
     * カレンダーに新着対象の予定を作成する。
     */
    private function createCalendarPost(string $title, string $body): CalendarPost
    {
        [, $bucket] = $this->createPluginFrame('calendars');

        $calendar = Calendar::create([
            'bucket_id' => $bucket->id,
            'name' => 'Test Calendar',
        ]);

        return CalendarPost::create([
            'calendar_id' => $calendar->id,
            'allday_flag' => 1,
            'start_date' => now()->toDateString(),
            'start_time' => '00:00:00',
            'end_date' => now()->toDateString(),
            'end_time' => '23:59:59',
            'title' => $title,
            'body' => $body,
        ]);
    }

    /**
     * フォトアルバムに新着対象の画像を作成する。
     */
    private function createPhotoalbumContent(string $name, string $description): PhotoalbumContent
    {
        [$page, $bucket] = $this->createPluginFrame('photoalbums');

        $photoalbum = Photoalbum::create([
            'bucket_id' => $bucket->id,
            'name' => 'Test Photoalbum',
            'image_upload_max_size' => 1024,
            'image_upload_max_px' => 1024,
            'video_upload_max_size' => 1024,
        ]);

        $root = PhotoalbumContent::create([
            'photoalbum_id' => $photoalbum->id,
            'upload_id' => null,
            'poster_upload_id' => null,
            'name' => $photoalbum->name,
            'is_folder' => PhotoalbumContent::is_folder_on,
            'parent_id' => null,
        ]);

        $upload = Uploads::create([
            'client_original_name' => "{$name}.jpg",
            'mimetype' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 123,
            'plugin_name' => 'photoalbums',
            'page_id' => $page->id,
            'temporary_flag' => 0,
        ]);

        return $root->children()->create([
            'photoalbum_id' => $photoalbum->id,
            'upload_id' => $upload->id,
            'poster_upload_id' => null,
            'name' => $name,
            'description' => $description,
            'is_folder' => PhotoalbumContent::is_folder_off,
            'is_cover' => PhotoalbumContent::is_cover_off,
            'mimetype' => 'image/jpeg',
        ]);
    }
}
