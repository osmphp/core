<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Samples\App;
use Osm\Runtime\Apps;
use Osm\Runtime\Compilation\Compiler;
use PHPUnit\Framework\TestCase;

class test_02_class_loading extends TestCase
{
    public function test_that_app_class_is_loaded() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you check App class reflection
            // THEN its there
            $this->assertArrayHasKey(App::class, $compiler->app->classes);
        });
    }

    public function test_that_module_classes_are_loaded() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you check reflections of module classes
            // THEN only classes of loaded modules are there
            $this->assertArrayHasKey(\Osm\Core\Samples\Some\Module::class,
                $compiler->app->classes);
            $this->assertArrayNotHasKey(\Osm\Core\Samples\Excluded\Module::class,
                $compiler->app->classes);
        });
    }

    public function test_that_external_classes_are_loaded() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you check reflection of a class mentioned in
            // Module::$classes property

            // THEN it's there
            $this->assertArrayHasKey(\Osm\Runtime\Object_::class,
                $compiler->app->classes);
        });
    }
}