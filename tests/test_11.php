<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Samples\Excluded\Module as ExcludedModule;
use Osm\Core\Samples\Some\Module as SomeModule;
use Osm\Core\Samples\AfterSome\Module as AfterSomeModule;
use Osm\Core\Samples\Some\Other;
use Osm\Core\Samples\Some\Some;
use Osm\Runtime\OldCompiler;
use Osm\Runtime\Apps;
use Osm\Core\Samples\App;
use Osm\Runtime\Traits\ComputedProperties;
use PHPUnit\Framework\TestCase;

/**
 * @property array $config
 */
class test_11 extends TestCase
{
    use ComputedProperties;

    protected function get_config(): array {
        return [
            'app_class_name' => App::class,
            'load_dev' => true,
        ];
    }

    protected function setUp(): void {
        Apps::new()->factory($this->config, function (OldCompiler $factory) {
            $factory->compile();
        });
    }

    public function test_that_app_is_loaded() {
        Apps::new()->factory($this->config, function (OldCompiler $factory) {
            // GIVEN a compiled app
            $app = $factory->create();

            // WHEN you don't do anything extra

            // THEN it is of a correct class
            $this->assertInstanceOf(App::class, $app);

            // AND the modules matching the requested app are loaded
            $this->assertArrayHasKey(SomeModule::class, $app->modules);
            $this->assertArrayHasKey(AfterSomeModule::class, $app->modules);

            // AND the modules not matching the requested app are not loaded
            $this->assertArrayNotHasKey(ExcludedModule::class, $app->modules);

            // AND the module order respects $after definitions
            $this->assertTrue(
                array_search(SomeModule::class, array_keys($app->modules)) <
                array_search(AfterSomeModule::class, array_keys($app->modules)));
        });
    }

    public function test_that_dynamic_trait_is_applied() {
        Apps::new()->factory($this->config, function (OldCompiler $factory) {
            // GIVEN a compiled app
            $app = $factory->create();

            // WHEN there is the dynamic trait `SomeTrait` applied to
            // the `Some` class, and the `Other` class extends `Some` class

            // THEN the properties computed in the trait work in `Some` class
            $obj = Some::new();
            $this->assertEquals(10, $obj->width);
            $this->assertEquals(100, $obj->area_size);

            // AND in the derived `Other` class as well
            $obj = Other::new();
            $this->assertEquals(10, $obj->width);
            $this->assertEquals(100, $obj->area_size);
        });
    }
}