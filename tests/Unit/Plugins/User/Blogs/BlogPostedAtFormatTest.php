<?php

namespace Tests\Unit\Plugins\User\Blogs;

use App\Enums\BlogFrameConfig;
use App\Enums\BlogPostedAtFormat;
use App\Enums\ShowType;
use App\Models\Core\FrameConfig;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

/**
 * ブログの投稿日時表示形式を検証する。
 *
 * DBを使わず表示部品を直接描画し、時刻だけを消せることと日時形式の選択結果が
 * HTMLのクラス分けに反映されることを守る。
 */
class BlogPostedAtFormatTest extends TestCase
{
    /**
     * スラッシュ区切りの形式を選ぶと、日付と時刻が別々のspanで yyyy/mm/dd hh:mm として出力されること。
     */
    public function testPostedAtCanBeDisplayedWithConfiguredFormat(): void
    {
        $html = $this->renderPostedAt([
            BlogFrameConfig::blog_posted_at_format => BlogPostedAtFormat::slash,
            BlogFrameConfig::blog_display_posted_time => ShowType::show,
        ]);

        $this->assertStringContainsString('class="blog-posted-at-date">2026/05/20', $html);
        $this->assertStringContainsString('class="blog-posted-at-time"> 09:30', $html);
    }

    /**
     * 時刻を表示しない設定では、日付だけを残し、時刻用spanを出力しないこと。
     */
    public function testPostedAtCanHideOnlyTime(): void
    {
        $html = $this->renderPostedAt([
            BlogFrameConfig::blog_posted_at_format => BlogPostedAtFormat::slash,
            BlogFrameConfig::blog_display_posted_time => ShowType::not_show,
        ]);

        $this->assertStringContainsString('class="blog-posted-at-date">2026/05/20', $html);
        $this->assertStringNotContainsString('09:30', strip_tags($html));
        $this->assertStringNotContainsString('blog-posted-at-time', $html);
    }

    /**
     * 時刻表示が未設定の場合は、改修前の挙動に合わせて時刻を表示すること。
     */
    public function testPostedAtDisplaysTimeByDefault(): void
    {
        $html = $this->renderPostedAt([
            BlogFrameConfig::blog_posted_at_format => BlogPostedAtFormat::slash,
        ]);

        $this->assertStringContainsString('class="blog-posted-at-date">2026/05/20', $html);
        $this->assertStringContainsString('class="blog-posted-at-time"> 09:30', $html);
    }

    /**
     * テンプレート側が日付のみを既定にしている場合は、時刻設定が未保存でも改修前どおり時刻を出さないこと。
     */
    public function testPostedAtCanUseTemplateDefaultToHideTime(): void
    {
        $html = $this->renderPostedAt(
            [],
            ShowType::not_show
        );

        $this->assertStringContainsString('class="blog-posted-at-date">2026年5月20日', $html);
        $this->assertStringNotContainsString('09:30', strip_tags($html));
        $this->assertStringNotContainsString('blog-posted-at-time', $html);
    }

    /**
     * テンプレート側が日付形式も既定にしている場合は、設定未保存でも既存テンプレートの表示形式を保つこと。
     */
    public function testPostedAtCanUseTemplateDefaultFormat(): void
    {
        $html = $this->renderPostedAt(
            [],
            ShowType::not_show,
            BlogPostedAtFormat::slash
        );

        $this->assertStringContainsString('class="blog-posted-at-date">2026/05/20', $html);
        $this->assertStringNotContainsString('09:30', strip_tags($html));
        $this->assertStringNotContainsString('blog-posted-at-time', $html);
    }

    /**
     * 和暦形式を選ぶと、日付部分が元号と年で表示されること。
     */
    public function testPostedAtCanBeDisplayedWithJapaneseEraFormat(): void
    {
        $html = $this->renderPostedAt([
            BlogFrameConfig::blog_posted_at_format => BlogPostedAtFormat::japanese_era,
            BlogFrameConfig::blog_display_posted_time => ShowType::show,
        ]);

        $this->assertStringContainsString('class="blog-posted-at-date">令和8年5月20日', $html);
        $this->assertStringContainsString('class="blog-posted-at-time"> 09時30分', $html);
    }

    /**
     * 表示部品に渡すフレーム設定を組み立てて、投稿日時のHTMLを返す。
     */
    private function renderPostedAt(array $configs, ?string $default_display_posted_time = null, ?string $default_posted_at_format = null): string
    {
        $frame_configs = new Collection();
        foreach ($configs as $name => $value) {
            $frame_configs->push(new FrameConfig(['name' => $name, 'value' => $value]));
        }

        $view_data = [
            'frame_configs' => $frame_configs,
            'posted_at' => Carbon::parse('2026-05-20 09:30:00'),
        ];
        if (!is_null($default_display_posted_time)) {
            $view_data['default_display_posted_time'] = $default_display_posted_time;
        }
        if (!is_null($default_posted_at_format)) {
            $view_data['default_posted_at_format'] = $default_posted_at_format;
        }

        return view('plugins.user.blogs.default.include_posted_at', $view_data)->render();
    }
}
