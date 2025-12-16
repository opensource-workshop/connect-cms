<?php

namespace Tests\Unit;

use App\Models\Common\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageDepthTest extends TestCase
{
    use RefreshDatabase;

    public function testRecalcDepthWithDescendantsUpdatesSubtree(): void
    {
        $root = Page::factory()->create();
        $child = Page::factory()->make();
        $child->appendToNode($root)->save();
        $grandchild = Page::factory()->make();
        $grandchild->appendToNode($child)->save();

        Page::query()->update(['depth' => 0]);

        $base = $root->ancestors()->count();
        $root->recalcDepthWithDescendants();

        $this->assertSame($base, $root->fresh()->depth);
        $this->assertSame($base + 1, $child->fresh()->depth);
        $this->assertSame($base + 2, $grandchild->fresh()->depth);
    }

    public function testRecalcAllDepthsUpdatesAllRoots(): void
    {
        $rootA = Page::factory()->create();
        $childA = Page::factory()->make();
        $childA->appendToNode($rootA)->save();

        $rootB = Page::factory()->create();

        Page::query()->update(['depth' => 5]);

        Page::recalcAllDepths();

        $this->assertSame($rootA->ancestors()->count(), $rootA->fresh()->depth);
        $this->assertSame($rootA->ancestors()->count() + 1, $childA->fresh()->depth);
        $this->assertSame($rootB->ancestors()->count(), $rootB->fresh()->depth);
    }
}
