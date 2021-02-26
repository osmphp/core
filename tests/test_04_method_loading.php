<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Attributes\Regression;
use Osm\Core\Object_;
use Osm\Core\Samples\App;
use Osm\Core\Samples\Attributes\Marker;
use Osm\Core\Samples\Attributes\Repeatable;
use Osm\Core\Samples\Some\Other;
use Osm\Core\Samples\Some\Some;
use Osm\Runtime\Apps;
use Osm\Runtime\Compilation\Compiler;
use PHPUnit\Framework\TestCase;

class test_04_method_loading extends TestCase
{
    public function test_method_reflection() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you access a method,
            // AND it is automatically loaded
            $sqr = $compiler->app->classes[Some::class]->methods['sqr'];

            // THEN its information can be found in its properties
            $this->assertTrue(isset($sqr->attributes[Repeatable::class]));
            $this->assertEquals('method', $sqr->attributes[Repeatable::class][0]->name);
        });
    }

    public function test_parent_class_method() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you access a method,that it is automatically copied
            // from the parent class
            $sqr = $compiler->app->classes[Other::class]->methods['sqr'];

            // THEN its information can be found in its properties
            $this->assertTrue(isset($sqr->attributes[Repeatable::class]));
            $this->assertEquals('method', $sqr->attributes[Repeatable::class][0]->name);
        });
    }

    public function test_trait_method() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you access a method, that is automatically copied
            // from the trait
            $get_pi = $compiler->app->classes[Some::class]->methods['get_pi'];

            // THEN their information can be found in its properties
            /* @var \ReflectionNamedType $type */
            $type = $get_pi->reflection->getReturnType();
            $this->assertEquals('float', $type->getName());
        });
    }

    public function test_property_getter() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you access a property, that has a getter method
            $pi = $compiler->app->classes[Some::class]->properties['pi'];

            // THEN method's information can be found via `getter`
            // property
            $type = $pi->getter->reflection->getReturnType();
            $this->assertEquals('float', $type->getName());
        });
    }

    public function test_around_advice() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you access a method, having around advices applied to it
            $get_pi = $compiler->app->classes[Some::class]->methods['get_pi'];

            // THEN their information can be found in its properties
            $this->assertCount(1, $get_pi->around);
        });
    }

    #[Regression]
    public function test_object_around_advice() {
        // GIVEN a compiler configured to compile a sample app
        // AND `\Osm\Core\Samples\Some\Traits\ObjectTrait::around_default()`
        // is applicable to every class derived from Object_
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            foreach ($compiler->app->classes as $class) {
                if (!is_a($class->name, Object_::class, true)) {
                    continue;
                }

                // WHEN you access a default() method in a class
                $default = $class->methods['default'];

                // THEN it should have exactly one advice
                $this->assertCount(1, $default->around);
            }
        });
    }
}