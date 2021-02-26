<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Samples\App;
use Osm\Runtime\Apps;
use Osm\Runtime\Compilation\Compiler;
use PHPUnit\Framework\TestCase;

class test_02_class_loading extends TestCase
{
    public function test_that_module_group_classes_are_loaded() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN a module group class is NOT a part of any module
            // THEN its should be in the class list
            $this->assertArrayHasKey(App::class, $compiler->app->classes);
        });
    }

    public function test_that_only_app_module_classes_are_loaded() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN a module group class is NOT a part of any module
            // THEN its should be in the class list
            $this->assertArrayNotHasKey(\Osm\Core\Samples\Excluded\Module::class,
                $compiler->app->classes);
        });
    }
}