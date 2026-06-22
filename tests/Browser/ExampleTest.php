<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    use DatabaseTruncation;

    public function test_basic_example(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->script("localStorage.setItem('has_seen_offline_popup', 'true');");

            $browser->refresh()
                ->waitForText('Smart Edutourism')
                ->assertSee('Smart Edutourism');
        });
    }
}
