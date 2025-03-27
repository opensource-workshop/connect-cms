<?php

namespace Tests\Unit\Models\Common;

use App\Models\Common\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 初期設定
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * getInheritMembershipPage()テスト
     */
    public function testGetInheritMembershipPage()
    {
        $top_page = Page::factory()->create(['membership_flag' => 0]);
        $child_page = Page::factory()->create(['membership_flag' => 0,'parent_id' => $top_page->id]);
        $grand_child_page = Page::factory()->create(['membership_flag' => 0, 'parent_id' => $child_page->id]);
        Page::fixTree();
        $top_page->refresh();
        $child_page->refresh();
        $grand_child_page->refresh();

        // 親ページをさかのぼってもメンバーシップページがない場合
        $this->assertNull($grand_child_page->getInheritMembershipPage());

        // 二階層上でメンバーシップページが設定されている場合、そのページを返す
        $top_page->membership_flag = 1;
        $top_page->save();
        $grand_child_page->refresh();
        $this->assertEquals($top_page->id, $grand_child_page->getInheritMembershipPage()->id);

        // 一階層上でメンバーシップページが設定されている場合、そのページを返す
        $child_page->membership_flag = 1;
        $child_page->save();
        $grand_child_page->refresh();
        $this->assertEquals($child_page->id, $grand_child_page->getInheritMembershipPage()->id);

        // 当該ページがメンバーシップページの場合、そのページを返す
        $grand_child_page->membership_flag = 1;
        $grand_child_page->save();
        $grand_child_page->refresh();
        $this->assertEquals($grand_child_page->id, $grand_child_page->getInheritMembershipPage()->id);
    }
}
