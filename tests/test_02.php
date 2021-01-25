<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Module as CoreModule;
use Osm\Core\Samples\Excluded\Module as ExcludedModule;
use Osm\Runtime\Factory;
use Osm\Runtime\Runtime;
use Osm\Core\Samples\App;
use PHPUnit\Framework\TestCase;

class test_02 extends TestCase
{
    public function test_that_app_is_ready() {
        $config = [
            'app_class_name' => App::class,
            'autoload_dev' => true,
        ];

        Runtime::new()->factory($config, function (Factory $factory) {
            // GIVEN no preconditions

            // WHEN you build an app for a specific environment
            $factory->compile();

            // THEN it is ready
    //        $this->assertInstanceOf("Generated\\custom\\production\\" . App::class,
    //            $factory->create());
            $this->assertInstanceOf(App::class, $app = $factory->create());
            $this->assertArrayHasKey(CoreModule::class, $app->modules);
            $this->assertArrayNotHasKey(ExcludedModule::class, $app->modules);
        });
    }
}