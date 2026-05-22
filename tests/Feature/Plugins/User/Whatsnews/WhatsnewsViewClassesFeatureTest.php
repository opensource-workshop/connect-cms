<?php

namespace Tests\Feature\Plugins\User\Whatsnews;

use App\Enums\UseType;
use App\Enums\WhatsnewFrameConfig;
use App\Models\Core\FrameConfig;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\TestCase;

/**
 * 新着情報の表示テンプレートに付与されるCSS用クラスを検証するFeatureテスト。
 *
 * 利用者が日付・カテゴリ・タイトル・投稿者・本文・サムネイルを個別にスタイル指定できるよう、
 * DBではなくBladeの公開された描画結果を対象に、テンプレートごとのHTML契約を確認する方針。
 */
class WhatsnewsViewClassesFeatureTest extends TestCase
{
    /**
     * 標準テンプレートで、新着を構成する各要素にCSSで指定できるクラスが付与されること。
     */
    public function testDefaultTemplateRendersClassesForEachWhatsnewPart(): void
    {
        $html = $this->renderWhatsnewsTemplate('plugins.user.whatsnews.default.whatsnews');

        $this->assertWhatsnewPartClasses($html);
    }

    /**
     * 1行表示テンプレートで、表示項目の有無に左右されず各要素をクラスで指定できること。
     */
    public function testOnerowTemplateRendersClassesForEachWhatsnewPart(): void
    {
        $html = $this->renderWhatsnewsTemplate('plugins.user.whatsnews.onerow.whatsnews');

        $this->assertWhatsnewPartClasses($html);
    }

    /**
     * カード表示テンプレートで、既存のカード用HTMLにも各要素のCSS用クラスが維持されること。
     */
    public function testCardTemplateRendersClassesForEachWhatsnewPart(): void
    {
        $html = $this->renderWhatsnewsTemplate('plugins.user.whatsnews.card_04.whatsnews');

        $this->assertWhatsnewPartClasses($html);
    }

    /**
     * 新着情報テンプレートの描画に必要な最小限のデータを用意する。
     */
    private function renderWhatsnewsTemplate(string $template): string
    {
        $frame = (object) [
            'id' => 1,
            'bucket_id' => 1,
            'frame_design' => 'default',
            'classname_body' => '',
        ];

        $whatsnews_frame = (object) [
            'rss' => UseType::not_use,
            'whatsnew_name' => 'テスト新着',
            'view_posted_at' => UseType::use,
            'view_posted_name' => UseType::use,
            'read_more_use_flag' => UseType::not_use,
            'read_more_btn_transparent_flag' => UseType::not_use,
            'read_more_btn_color_type' => 'primary',
            'read_more_btn_type' => '',
            'read_more_name' => 'もっと見る',
        ];

        $whatsnews = collect([
            (object) [
                'posted_at' => Carbon::parse('2026-05-22 10:00:00'),
                'category' => 'お知らせ',
                'classname' => 'notice',
                'plugin_name' => 'blogs',
                'page_id' => 1,
                'frame_id' => 2,
                'post_id' => 3,
                'post_title' => 'テストタイトル',
                'post_title_strip_tags' => 'テストタイトル',
                'post_detail_strip_tags' => 'テスト本文',
                'first_image_path' => '/file/10',
                'posted_name' => '投稿者名',
            ],
        ]);

        return view($template, [
            'frame' => $frame,
            'frame_id' => $frame->id,
            'page' => (object) ['id' => 1],
            'whatsnews' => $whatsnews,
            'whatsnews_frame' => $whatsnews_frame,
            'whatsnews_total_count' => 1,
            'link_pattern' => ['blogs' => 'show_page_frame_post'],
            'link_base' => ['blogs' => '/plugin/blogs/show'],
            'frame_configs' => $this->createVisiblePartFrameConfigs(),
        ])->render();
    }

    /**
     * 本文・サムネイル・罫線を表示状態にして、対象クラスがHTMLに現れるようにする。
     */
    private function createVisiblePartFrameConfigs(): EloquentCollection
    {
        return new EloquentCollection([
            new FrameConfig(['name' => WhatsnewFrameConfig::post_detail, 'value' => UseType::use]),
            new FrameConfig(['name' => WhatsnewFrameConfig::thumbnail, 'value' => UseType::use]),
            new FrameConfig(['name' => WhatsnewFrameConfig::border, 'value' => UseType::use]),
            new FrameConfig(['name' => WhatsnewFrameConfig::async, 'value' => UseType::not_use]),
        ]);
    }

    /**
     * CSS適用対象として公開する6種類のクラスが描画結果に含まれることを確認する。
     */
    private function assertWhatsnewPartClasses(string $html): void
    {
        $this->assertStringContainsString('whatsnew_posted_at', $html);
        $this->assertStringContainsString('whatsnew_category', $html);
        $this->assertStringContainsString('whatsnew_title', $html);
        $this->assertStringContainsString('whatsnew_posted_name', $html);
        $this->assertStringContainsString('whatsnew_post_detail', $html);
        $this->assertStringContainsString('whatsnew_thumbnail', $html);
    }
}
