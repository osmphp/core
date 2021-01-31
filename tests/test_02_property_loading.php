<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Attributes\Serialized;
use Osm\Core\Samples\App;
use Osm\Core\Samples\Attributes\Marker;
use Osm\Core\Samples\Attributes\Repeatable;
use Osm\Core\Samples\Some\Other;
use Osm\Core\Samples\Some\Some;
use Osm\Runtime\Apps;
use Osm\Runtime\Compilation\Compiler;
use PHPUnit\Framework\TestCase;

class test_02_property_loading extends TestCase
{
    public function test_doc_comment_property() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you access a property,
            // AND it is automatically loaded
            $name = $compiler->app->classes[Some::class]->properties['name'];

            // THEN its information can be found in its properties
            $this->assertEquals('string', $name->type);
            $this->assertNotTrue($name->array);
            $this->assertFalse($name->nullable);
            $this->assertTrue(isset($name->attributes[Marker::class]));
            $this->assertEquals('marker', $name->attributes[Marker::class]->name);
        });
    }

    public function test_reflection_property() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you access a property,
            // AND it is automatically loaded
            $children = $compiler->app->classes[Some::class]->properties['children'];

            // THEN its information can be found in its properties
            $this->assertEquals(Some::class, $children->type);
            $this->assertTrue($children->array);
            $this->assertFalse($children->nullable);
            $this->assertTrue(isset($children->attributes[Marker::class]));
            $this->assertEquals('owns', $children->attributes[Marker::class]->name);
        });
    }

    public function test_repeatable_attributes() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            foreach (['type', 'note'] as $propertyName) {
                // WHEN you access a property,
                // AND it is automatically loaded
                $property = $compiler->app->classes[Some::class]->properties[$propertyName];

                // THEN its information can be found in its properties
                $this->assertEquals('string', $property->type);
                $this->assertNotTrue($property->array);
                $this->assertFalse($property->nullable);
                $this->assertTrue(isset($property->attributes[Repeatable::class]));
                $this->assertTrue(is_array($property->attributes[Repeatable::class]));
                $this->assertCount(2, $property->attributes[Repeatable::class]);
            }
        });
    }

    public function test_merged_property() {
        // GIVEN a compiler configured to compile a sample app
        $compiler = Compiler::new(['app_class_name' => App::class]);

        Apps::run($compiler, function(Compiler $compiler) {
            // WHEN you access a property, that is automatically copied
            // from the parent class
            $name = $compiler->app->classes[Other::class]->properties['name'];

            // AND you access a property, that is automatically merged
            // with the parent class
            $children = $compiler->app->classes[Other::class]->properties['children'];

            // THEN their information can be found in its properties
            $this->assertEquals('string', $name->type);
            $this->assertNotTrue($name->array);
            $this->assertFalse($name->nullable);
            $this->assertTrue(isset($name->attributes[Marker::class]));
            $this->assertEquals('marker', $name->attributes[Marker::class]->name);

            $this->assertEquals(Some::class, $children->type);
            $this->assertTrue($children->array);
            $this->assertFalse($children->nullable);
            $this->assertTrue(isset($children->attributes[Marker::class]));
            $this->assertEquals('owns', $children->attributes[Marker::class]->name);
        });
    }

}