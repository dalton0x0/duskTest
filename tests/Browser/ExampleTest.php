<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     */
    public function testBasicExample(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://iticparis.ymag.cloud/index.php/')
                ->waitFor('', 5)
                ->screenshot('screenshot-login')
                ->type('input[name="login"]', '')
                ->type('input[name="password"]', '')
                ->waitFor('', 5)
                ->screenshot('screenshot-submit-login')
                ->click('input[name="btnSeConnecter"]')
                ->waitFor('', 5)
                ->screenshot('screenshot-if-connected');
        });
    }
}
