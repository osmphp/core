<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Attributes\Serialized;
use Osm\Core\Samples\App;
use Osm\Core\Samples\Attributes\Marker;
use Osm\Core\Samples\Some\Some;
use Osm\Runtime\Apps;
use Osm\Runtime\Compilation\Compiler;
use PHPUnit\Framework\TestCase;

class test_02_property_loading extends TestCase
{
    public function test_loading_from_doc_comment() {
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
}