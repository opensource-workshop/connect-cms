<?php

namespace Tests\Feature\Plugins\User\Calendars;

use App\Enums\StatusType;
use App\Models\Common\Buckets;
use App\Models\Common\Frame;
use App\Models\Common\Page;
use App\Models\User\Calendars\Calendar;
use App\Models\User\Calendars\CalendarFrame;
use App\Models\User\Calendars\CalendarPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Plugins\User\DefaultBucketRolesFeatureTestTrait;
use Tests\TestCase;

/**
 * Calendars の繰り返し予定登録を検証する。
 *
 * 予定登録の公開HTTP経路を通して、利用者が1回の入力で複数日の予定を
 * 作成できることと、日付の展開規則が崩れないことを守る。
 */
class CalendarsRepeatPostsFeatureTest extends TestCase
{
    use DefaultBucketRolesFeatureTestTrait;
    use RefreshDatabase;

    /**
     * テスト前に初期データを投入する。
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /**
     * 毎週の繰り返し指定では、開始日と同じ曜日の予定が終了日まで作成されること。
     */
    public function testSaveCreatesWeeklyRepeatPosts(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $calendar] = $this->createCalendarFrame();

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/calendars/save/{$page->id}/{$frame->id}",
            $this->makePayload([
                'title' => '毎週の定例会',
                'start_date' => '2026-06-01',
                'start_time' => '10:00',
                'end_date' => '2026-06-01',
                'end_time' => '11:00',
                'repeat_type' => 'weekly',
                'repeat_until' => '2026-06-22',
            ])
        );

        $this->assertContains($response->getStatusCode(), [200, 302]);

        foreach (['2026-06-01', '2026-06-08', '2026-06-15', '2026-06-22'] as $date) {
            $this->assertDatabaseHas('calendar_posts', [
                'calendar_id' => $calendar->id,
                'title' => '毎週の定例会',
                'start_date' => $date,
                'end_date' => $date,
                'status' => StatusType::active,
            ]);
        }

        $this->assertSame(4, CalendarPost::where('calendar_id', $calendar->id)->count());
        $this->assertSame(1, CalendarPost::where('calendar_id', $calendar->id)->distinct()->count('repeat_group_id'));
    }

    /**
     * 毎月第n曜日の繰り返し指定では、開始日と同じ第n曜日だけが作成されること。
     */
    public function testSaveCreatesMonthlyWeekdayRepeatPosts(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $calendar] = $this->createCalendarFrame();

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/calendars/save/{$page->id}/{$frame->id}",
            $this->makePayload([
                'title' => '毎月第2火曜の定例会',
                'start_date' => '2026-05-12',
                'start_time' => '13:00',
                'end_date' => '2026-05-12',
                'end_time' => '14:00',
                'repeat_type' => 'monthly_weekday',
                'repeat_until' => '2026-08-31',
            ])
        );

        $this->assertContains($response->getStatusCode(), [200, 302]);

        foreach (['2026-05-12', '2026-06-09', '2026-07-14', '2026-08-11'] as $date) {
            $this->assertDatabaseHas('calendar_posts', [
                'calendar_id' => $calendar->id,
                'title' => '毎月第2火曜の定例会',
                'start_date' => $date,
                'end_date' => $date,
                'status' => StatusType::active,
            ]);
        }

        $this->assertSame(4, CalendarPost::where('calendar_id', $calendar->id)->count());
        $this->assertSame(1, CalendarPost::where('calendar_id', $calendar->id)->distinct()->count('repeat_group_id'));
    }

    /**
     * 繰り返し予定の「これ以降」削除では、選択した予定より前の予定が残ること。
     */
    public function testDeleteRepeatPostsAfterSelectedDate(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $calendar] = $this->createCalendarFrame();
        $this->createWeeklyRepeatPosts($admin, $page, $frame);

        $target = CalendarPost::where('calendar_id', $calendar->id)
            ->where('start_date', '2026-06-15')
            ->firstOrFail();

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/calendars/delete/{$page->id}/{$frame->id}/{$target->id}",
            [
                'redirect_path' => '/',
                'repeat_delete_type' => 'after',
            ]
        );

        $this->assertContains($response->getStatusCode(), [200, 302]);
        $this->assertSame(
            ['2026-06-01', '2026-06-08'],
            CalendarPost::where('calendar_id', $calendar->id)->orderBy('start_date')->pluck('start_date')->all()
        );
        $this->assertSoftDeleted('calendar_posts', ['id' => $target->id]);
    }

    /**
     * 繰り返し予定の1件を編集しても、同じ繰り返しグループとして削除できる状態が残ること。
     */
    public function testUpdateRepeatPostKeepsRepeatGroup(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $calendar] = $this->createCalendarFrame();
        $this->createWeeklyRepeatPosts($admin, $page, $frame);

        $target = CalendarPost::where('calendar_id', $calendar->id)
            ->where('start_date', '2026-06-08')
            ->firstOrFail();
        $repeat_group_id = $target->repeat_group_id;

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/calendars/save/{$page->id}/{$frame->id}/{$target->id}",
            [
                'redirect_path' => '/',
                'status' => StatusType::active,
                'allday_flag' => 0,
                'title' => '編集後の定例会',
                'start_date' => '2026-06-08',
                'start_time' => '10:30',
                'end_date' => '2026-06-08',
                'end_time' => '11:30',
                'body' => '',
                'location' => '',
                'contact' => '',
            ]
        );

        $this->assertContains($response->getStatusCode(), [200, 302]);
        $this->assertDatabaseHas('calendar_posts', [
            'id' => $target->id,
            'title' => '編集後の定例会',
            'repeat_group_id' => $repeat_group_id,
        ]);
        $this->assertSame(4, CalendarPost::where('repeat_group_id', $repeat_group_id)->count());
        $this->assertSame(1, CalendarPost::where('repeat_group_id', $repeat_group_id)->where('title', '編集後の定例会')->count());
        $this->assertSame(3, CalendarPost::where('repeat_group_id', $repeat_group_id)->where('title', '毎週の定例会')->count());
    }

    /**
     * 繰り返し予定の「これ以降」編集では、選択した予定以降へ同じ差分が反映されること。
     */
    public function testUpdateRepeatPostsAfterSelectedDate(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $calendar] = $this->createCalendarFrame();
        $this->createWeeklyRepeatPosts($admin, $page, $frame);

        $target = CalendarPost::where('calendar_id', $calendar->id)
            ->where('start_date', '2026-06-08')
            ->firstOrFail();

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/calendars/save/{$page->id}/{$frame->id}/{$target->id}",
            [
                'redirect_path' => '/',
                'status' => StatusType::active,
                'allday_flag' => 0,
                'title' => '以降の定例会',
                'start_date' => '2026-06-09',
                'start_time' => '11:00',
                'end_date' => '2026-06-09',
                'end_time' => '12:00',
                'body' => '',
                'location' => '',
                'contact' => '',
                'repeat_edit_type' => 'after',
            ]
        );

        $this->assertContains($response->getStatusCode(), [200, 302]);
        $this->assertSame(
            ['2026-06-01', '2026-06-09', '2026-06-16', '2026-06-23'],
            CalendarPost::where('calendar_id', $calendar->id)->orderBy('start_date')->pluck('start_date')->all()
        );
        $this->assertDatabaseHas('calendar_posts', [
            'calendar_id' => $calendar->id,
            'start_date' => '2026-06-01',
            'title' => '毎週の定例会',
            'start_time' => '10:00:00',
        ]);
        $this->assertSame(3, CalendarPost::where('calendar_id', $calendar->id)->where('title', '以降の定例会')->count());
    }

    /**
     * 繰り返し予定の「すべて」編集では、同じ繰り返しグループの予定がすべて更新されること。
     */
    public function testUpdateAllRepeatPosts(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $calendar] = $this->createCalendarFrame();
        $this->createWeeklyRepeatPosts($admin, $page, $frame);

        $target = CalendarPost::where('calendar_id', $calendar->id)
            ->where('start_date', '2026-06-08')
            ->firstOrFail();
        $repeat_group_id = $target->repeat_group_id;

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/calendars/save/{$page->id}/{$frame->id}/{$target->id}",
            [
                'redirect_path' => '/',
                'status' => StatusType::active,
                'allday_flag' => 0,
                'title' => '全体変更後の定例会',
                'start_date' => '2026-06-08',
                'start_time' => '10:30',
                'end_date' => '2026-06-08',
                'end_time' => '11:30',
                'body' => '全体変更',
                'location' => '会議室A',
                'contact' => '担当者',
                'repeat_edit_type' => 'all',
            ]
        );

        $this->assertContains($response->getStatusCode(), [200, 302]);
        $this->assertSame(4, CalendarPost::where('repeat_group_id', $repeat_group_id)->count());
        $this->assertSame(4, CalendarPost::where('repeat_group_id', $repeat_group_id)->where('title', '全体変更後の定例会')->count());
        $this->assertSame(4, CalendarPost::where('repeat_group_id', $repeat_group_id)->where('location', '会議室A')->count());
    }

    /**
     * 繰り返し予定の「すべて」削除では、同じ繰り返しグループの予定がすべて消えること。
     */
    public function testDeleteAllRepeatPosts(): void
    {
        $admin = $this->createContentAdminUser();
        [$page, $frame, $calendar] = $this->createCalendarFrame();
        $this->createWeeklyRepeatPosts($admin, $page, $frame);

        $target = CalendarPost::where('calendar_id', $calendar->id)
            ->where('start_date', '2026-06-08')
            ->firstOrFail();
        $repeat_group_id = $target->repeat_group_id;

        $response = $this->actingAs($admin)->post(
            "/redirect/plugin/calendars/delete/{$page->id}/{$frame->id}/{$target->id}",
            [
                'redirect_path' => '/',
                'repeat_delete_type' => 'all',
            ]
        );

        $this->assertContains($response->getStatusCode(), [200, 302]);
        $this->assertSame(0, CalendarPost::where('calendar_id', $calendar->id)->count());
        $this->assertSame(
            4,
            CalendarPost::onlyTrashed()->where('repeat_group_id', $repeat_group_id)->count()
        );
    }

    /**
     * 指定フレームに紐づくカレンダーを作成する。
     */
    private function createCalendarFrame(): array
    {
        $page = Page::factory()->create();
        $bucket = Buckets::factory()->create([
            'bucket_name' => '繰り返し予定テスト',
            'plugin_name' => 'calendars',
        ]);
        $calendar = Calendar::create([
            'bucket_id' => $bucket->id,
            'name' => '繰り返し予定テスト',
        ]);
        $frame = Frame::create([
            'page_id' => $page->id,
            'area_id' => 2,
            'plugin_name' => 'calendars',
            'bucket_id' => $bucket->id,
            'template' => 'default',
            'display_sequence' => 1,
        ]);
        CalendarFrame::create([
            'calendar_id' => $calendar->id,
            'frame_id' => $frame->id,
        ]);

        return [$page, $frame, $calendar];
    }

    /**
     * 毎週の繰り返し予定を削除テスト用に作成する。
     */
    private function createWeeklyRepeatPosts($admin, Page $page, Frame $frame): void
    {
        $this->actingAs($admin)->post(
            "/redirect/plugin/calendars/save/{$page->id}/{$frame->id}",
            $this->makePayload([
                'title' => '毎週の定例会',
                'start_date' => '2026-06-01',
                'start_time' => '10:00',
                'end_date' => '2026-06-01',
                'end_time' => '11:00',
                'repeat_type' => 'weekly',
                'repeat_until' => '2026-06-22',
            ])
        );
    }

    /**
     * 予定登録に必要な標準入力を作成する。
     */
    private function makePayload(array $overrides): array
    {
        return array_merge([
            'redirect_path' => '/',
            'status' => StatusType::active,
            'allday_flag' => 0,
            'body' => '',
            'location' => '',
            'contact' => '',
            'repeat_type' => 'none',
        ], $overrides);
    }
}
