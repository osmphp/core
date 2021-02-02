<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Samples\App;
use Osm\Core\Samples\Some\Some;
use Osm\Runtime\Apps;
use PHPUnit\Framework\TestCase;

class test_06_advice_compilation extends TestCase
{
    public function test_that_applied_advice_gets_called() {
        // GIVEN a compiled sample app
        Apps::compile(App::class);

        Apps::run(Apps::create(App::class), function (App $app) {
            // WHEN you instantiate a class with an applied advice
            $some = Some::new(['round_pi' => true]);

            // THEN the applied advice works as expected
            $this->assertEquals(3.0, $some->pi);
        });
    }

    public function test_proceeding_with_default_behavior() {
        // GIVEN a compiled sample app
        Apps::compile(App::class);

        Apps::run(Apps::create(App::class), function (App $app) {
            // WHEN you instantiate a class with an applied advice
            $some = Some::new();

            // THEN the applied advice works as expected
            $this->assertEquals(pi(), $some->pi);
        });
    }
}