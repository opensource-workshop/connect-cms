<?php

namespace Tests\Unit\Plugins\Manage\PageManage;

use App\Models\Common\Page;
use App\Plugins\Manage\PageManage\PageManage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * PageManage のページ移動処理を対象にした単体テスト。
 *
 * 管理画面のフォーム送信に相当する公開メソッド経由で、基準ページの上下と配下へ移動する仕様を検証する。
 */
class PageManageMovePageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 基準ページの上へ移動すると、移動元が基準ページと同じ親を持つ直前のページになることを守る。
     */
    public function testMovePageBeforeDestination(): void
    {
        $parent = Page::factory()->create(['page_name' => 'parent', 'permanent_link' => '/parent']);
        $destination = $this->createChildPage($parent, 'destination', '/destination');
        $other = $this->createChildPage($parent, 'other', '/other');
        $source = $this->createChildPage($parent, 'source', '/source');

        $this->movePage($source, $destination, 'before');

        $this->assertSame(
            ['source', 'destination', 'other'],
            $parent->children()->defaultOrder()->pluck('page_name')->all()
        );
        $this->assertSame($parent->id, $source->fresh()->parent_id);
    }

    /**
     * 基準ページの下へ移動すると、移動元が基準ページと同じ親を持つ直後のページになることを守る。
     */
    public function testMovePageAfterDestination(): void
    {
        $parent = Page::factory()->create(['page_name' => 'parent', 'permanent_link' => '/parent']);
        $source = $this->createChildPage($parent, 'source', '/source');
        $destination = $this->createChildPage($parent, 'destination', '/destination');
        $other = $this->createChildPage($parent, 'other', '/other');

        $this->movePage($source, $destination, 'after');

        $this->assertSame(
            ['destination', 'source', 'other'],
            $parent->children()->defaultOrder()->pluck('page_name')->all()
        );
        $this->assertSame($parent->id, $source->fresh()->parent_id);
    }

    /**
     * 基準ページの配下へ移動する既存の挙動を維持し、子孫ページの深さも移動先に合わせて更新されることを守る。
     */
    public function testMovePageToDestinationChild(): void
    {
        $source_parent = Page::factory()->create(['page_name' => 'source parent', 'permanent_link' => '/source-parent']);
        $destination_parent = Page::factory()->create(['page_name' => 'destination parent', 'permanent_link' => '/destination-parent']);
        $destination = $this->createChildPage($destination_parent, 'destination', '/destination');
        $source = $this->createChildPage($source_parent, 'source', '/source');
        $source_child = $this->createChildPage($source, 'source child', '/source-child');

        $this->movePage($source, $destination, 'child');

        $this->assertSame($destination->id, $source->fresh()->parent_id);
        $this->assertSame(['source'], $destination->children()->defaultOrder()->pluck('page_name')->all());
        $this->assertSame($source->fresh()->depth + 1, $source_child->fresh()->depth);
    }

    /**
     * 自分自身や子孫ページを基準にした移動は、階層を壊すため実行されないことを守る。
     */
    public function testMovePageDoesNotMoveIntoDescendant(): void
    {
        $parent = Page::factory()->create(['page_name' => 'parent', 'permanent_link' => '/parent']);
        $source = $this->createChildPage($parent, 'source', '/source');
        $source_child = $this->createChildPage($source, 'source child', '/source-child');

        $this->movePage($source, $source_child, 'child');

        $this->assertSame($parent->id, $source->fresh()->parent_id);
        $this->assertSame($source->id, $source_child->fresh()->parent_id);
    }

    /**
     * 指定した親ページの末尾にテスト用ページを作成する。
     */
    private function createChildPage(Page $parent, string $page_name, string $permanent_link): Page
    {
        $page = Page::factory()->make([
            'page_name' => $page_name,
            'permanent_link' => $permanent_link,
        ]);
        $page->appendToNode($parent)->save();

        return $page;
    }

    /**
     * 管理画面からのページ移動リクエストに相当する入力を組み立てて実行する。
     */
    private function movePage(Page $source, Page $destination, string $move_position): void
    {
        $request = new Request([
            'destination_id' => $destination->id,
            'move_position' => $move_position,
        ]);

        (new PageManage())->movePage($request, $source->id);
    }
}
