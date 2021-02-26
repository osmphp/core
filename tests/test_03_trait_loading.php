<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Samples\AfterSome\Traits\DynamicTrait;
use Osm\Core\Samples\App;
use Osm\Core\Samples\Attributes\Marker;
use Osm\Core\Samples\Attributes\Repeatable;
use Osm\Core\Samples\Some\Other;
use Osm\Core\Samples\Some\Some;
use Osm\Core\Samples\Some\Traits\StaticTrait;
use Osm\Runtime\Apps;
use Osm\Runtime\Compilation\Compiler;
use PHPUnit\Framework\TestCase;

class test_03_trait_loading extends TestCase
{
    public function test_static_trait() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you access a static trait,
            // AND it is automatically loaded
            $trait = $compiler->app->classes[Some::class]->traits[StaticTrait::class];

            // THEN its information can be found in its properties
            $this->assertEquals(StaticTrait::class, $trait->name);
        });
    }

    public function test_dynamic_trait() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you access a static trait,
            // AND it is automatically loaded
            $trait = $compiler->app->classes[Some::class]->traits[DynamicTrait::class];

            // THEN its information can be found in its properties
            $this->assertEquals(DynamicTrait::class, $trait->name);
        });
    }

    public function test_inherited_trait() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you access a static trait,
            // AND it is automatically loaded
            $trait = $compiler->app->classes[Other::class]->traits[DynamicTrait::class];

            // THEN its information can be found in its properties
            $this->assertEquals(DynamicTrait::class, $trait->name);
        });
    }
}