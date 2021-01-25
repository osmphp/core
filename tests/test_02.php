<?php

declare(strict_types=1);

namespace Osm\Runtime\Tests;

use Osm\Core\App;
use Osm\Core\Module;
use Osm\Runtime\Factory;
use Osm\Runtime\Runtime;
use PHPUnit\Framework\TestCase;

class test_02 extends TestCase
{
    public function test_that_app_is_ready() {
        $config = [
            'app_name' => 'custom',
            'load_from_autoload_dev' => true,
        ];

        Runtime::new()->factory($config, function (Factory $factory) {
            // GIVEN no preconditions

            // WHEN you build an app for a specific environment
            $factory->compile();

            // THEN it is ready
    //        $this->assertInstanceOf("Generated\\custom\\production\\" . App::class,
    //            $factory->create());
            $this->assertInstanceOf(App::class, $app = $factory->create());
            $this->assertArrayHasKey(Module::class, $app->modules);
        });
    }
}