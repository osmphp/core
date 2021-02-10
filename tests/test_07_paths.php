<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Samples\App;
use Osm\Runtime\Apps;
use PHPUnit\Framework\TestCase;

class test_07_paths extends TestCase
{
    public function test_that_applied_advice_gets_called() {
        // GIVEN a compiled sample app
        Apps::compile(App::class);

        Apps::run(Apps::create(App::class), function (App $app) {
            // WHEN you check the project path
            $path = $app->paths->project;

            // THEN its the current directory
            $this->assertEquals(getcwd(), $path);
        });
    }
}