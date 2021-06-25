<?php

namespace Tests\Browser\Core;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PageNotFoundTest extends DuskTestCase
{
    /**
     * ページなし表示のテスト
     *
     * @return void
     *
     * @group core
     * @see https://readouble.com/laravel/6.x/ja/dusk.html#running-tests `php artisan dusk --group=core`
     */
    public function testPageNotFound()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/not-found')
                    ->assertSee('404 Not found');
            parent::screenshot($browser);
        });
    }
}
