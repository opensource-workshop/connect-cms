<?php

namespace Tests\Browser\Core;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PageNotFound extends DuskTestCase
{
    /**
     * ページなし表示のテスト
     *
     * @return void
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
