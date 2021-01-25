<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Module as CoreModule;
use Osm\Core\Samples\Excluded\Module as ExcludedModule;
use Osm\Core\Samples\Some\Module as SomeModule;
use Osm\Core\Samples\AfterSome\Module as AfterSomeModule;
use Osm\Runtime\Factory;
use Osm\Runtime\Runtime;
use Osm\Core\Samples\App;
use PHPUnit\Framework\TestCase;

class test_02 extends TestCase
{
    public function test_that_app_is_ready() {
        Runtime::new()->factory([
            'app_class_name' => App::class,
            'autoload_dev' => true,
        ], function (Factory $factory)
        {
            // GIVEN no preconditions

            // WHEN you build the requested app for the requested environment
            $factory->compile();

            // THEN it is ready
            $this->assertInstanceOf(App::class, $app = $factory->create());

            // AND the modules matching the requested app are loaded
            $this->assertArrayHasKey(CoreModule::class, $app->modules);
            $this->assertArrayHasKey(SomeModule::class, $app->modules);
            $this->assertArrayHasKey(AfterSomeModule::class, $app->modules);

            // AND the modules not matching the requested app are not loaded
            $this->assertArrayNotHasKey(ExcludedModule::class, $app->modules);

            // AND the module order respects $requires and $after definitions
            $this->assertTrue(
                array_search(SomeModule::class, $app->modules) <
                array_search(AfterSomeModule::class, $app->modules));
        });
    }
}