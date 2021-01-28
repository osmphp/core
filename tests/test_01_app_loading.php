<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Samples\App;
use Osm\Core\Samples\ModuleGroup;
use Osm\Core\Samples\Some\Module;
use Osm\Runtime\Compilation\Compiler;
use Osm\Runtime\Apps;
use PHPUnit\Framework\TestCase;

class test_01_app_loading extends TestCase
{
    public function test_package_loading() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you access a package,
            // AND it is automatically loaded
            $package = $compiler->app->unsorted_packages['osmphp/core'];

            // THEN its information can be found in its properties
            $this->assertEquals('osmphp/core', $package->name);
            $this->assertEquals('', $package->path);
            $this->assertEquals(\Osm\Core\Package::class,
                $package->serialized_class_name);
            $this->assertNotFalse(array_search('ext-mbstring',
                $package->after));
        });
    }

    public function test_module_group_loading() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you access a module group,
            // AND it is automatically loaded
            $moduleGroup = $compiler->app->unsorted_module_groups[ModuleGroup::class];

            // THEN its information can be found in its properties
            $this->assertEquals('samples', $moduleGroup->path);
            $this->assertEquals(ModuleGroup::class,
                $moduleGroup->class_name);
            $this->assertEquals(ModuleGroup::class,
                $moduleGroup->serialized_class_name);
            $this->assertEmpty($moduleGroup->after);
        });
    }

    public function test_module_loading() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you access a module,
            // AND it is automatically loaded
            $module = $compiler->app->unsorted_modules[Module::class];

            // THEN its information can be found in its properties
            $this->assertEquals('samples/Some', $module->path);
            $this->assertEquals(Module::class, $module->class_name);
            $this->assertEquals(Module::class, $module->serialized_class_name);
            $this->assertEmpty($module->after);
        });
    }
}