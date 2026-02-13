<?php

namespace Tests\Unit\Controllers\Core;

use App\Http\Controllers\Core\DefaultController;
use App\Models\Common\Page;
use App\Models\Core\Configs;
use Tests\TestCase;

/**
 * DefaultController::getLayout() の継承判定とフォールバックを検証する。
 */
class DefaultControllerLayoutTest extends TestCase
{
    protected function setSharedConfigs(array $configs): void
    {
        $request = app('request');
        $request->attributes->set('configs', collect($configs));
    }

    protected function newConfig(string $name, ?string $value): Configs
    {
        $config = new Configs();
        $config->name = $name;
        $config->value = $value;
        return $config;
    }

    protected function callGetLayout(Page $page, $page_tree): string
    {
        $controller = new DefaultController();
        $method = new \ReflectionMethod(DefaultController::class, 'getLayout');
        $method->setAccessible(true);

        return $method->invoke($controller, $page, $page_tree);
    }

    protected function tearDown(): void
    {
        $this->setSharedConfigs([]);
        parent::tearDown();
    }

    /**
     * 祖先の layout_inherit_flag=0 を継承対象から除外すること。
     */
    public function testGetLayoutSkipsAncestorsWithLayoutInheritFlagOff(): void
    {
        $current = new Page();
        $current->id = 3;
        $current->layout = null;
        $current->layout_inherit_flag = 1;

        $parent = new Page();
        $parent->id = 2;
        $parent->layout = '1|1|1|1';
        $parent->layout_inherit_flag = 0;

        $grandparent = new Page();
        $grandparent->id = 1;
        $grandparent->layout = '0|1|0|1';
        $grandparent->layout_inherit_flag = 1;

        $this->setSharedConfigs([
            $this->newConfig('base_layout', '1|0|0|1'),
        ]);

        $layout = $this->callGetLayout($current, collect([$current, $parent, $grandparent]));

        $this->assertSame('0|1|0|1', $layout);
    }

    /**
     * ツリー内にレイアウトが無い場合は基本レイアウトを採用すること。
     */
    public function testGetLayoutFallsBackToBaseLayoutWhenNoLayoutInTree(): void
    {
        $current = new Page();
        $current->id = 1;
        $current->layout = null;

        $parent = new Page();
        $parent->id = 2;
        $parent->layout = null;

        $this->setSharedConfigs([
            $this->newConfig('base_layout', '1|0|0|1'),
        ]);

        $layout = $this->callGetLayout($current, collect([$current, $parent]));

        $this->assertSame('1|0|0|1', $layout);
    }

    /**
     * 基本レイアウトが空なら初期値にフォールバックすること。
     */
    public function testGetLayoutFallsBackToDefaultWhenBaseLayoutEmpty(): void
    {
        $current = new Page();
        $current->id = 1;
        $current->layout = null;

        $this->setSharedConfigs([
            $this->newConfig('base_layout', ''),
        ]);

        $layout = $this->callGetLayout($current, collect([$current]));

        $this->assertSame(config('connect.BASE_LAYOUT_DEFAULT'), $layout);
    }
}
