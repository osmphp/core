<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Osm\Attributes\Part;
use Osm\Core\Samples\App;
use Osm\Core\Samples\Attributes\Marker;
use Osm\Core\Samples\ClassWithAnnotations;
use Osm\Core\Samples\Some\Some;
use Osm\Runtime\App\App as RuntimeApp;
use Osm\Runtime\Classes\ClassLoader;
use Osm\Runtime\Classes\PropertyLoader;
use Osm\Runtime\OldCompiler;
use Osm\Runtime\Loading\AppLoader;
use Osm\Runtime\Apps;
use Osm\Runtime\Traits\ComputedProperties;
use phpDocumentor\Reflection\DocBlockFactory;
use PHPUnit\Framework\TestCase;

/**
 * @property array $config
 */
class test_02_property_loading extends TestCase
{
    use ComputedProperties;

    protected function get_config(): array {
        return [
            'app_class_name' => App::class,
            'load_dev' => true,
        ];
    }

    public function test_loading_from_doc_comment() {
        Apps::new()->factory($this->config, function (OldCompiler $factory) {
            // GIVEN an app with loaded modules and scanned class names
            $app = $factory->app = RuntimeApp::new([
                'upgrade_to_class_name' => $factory->app_class_name,
            ]);
            AppLoader::new()->load();
            ClassLoader::new()->load();

            // GIVEN the class with some @properties in its doc comment
            $loader = PropertyLoader::new([
                'class' => $class = $app->classes[Some::class],
            ]);

            // WHEN you load the properties of the class
            $loader->load();

            // THEN the $name property information is as defined, except for
            // `mixed` type, why doesn't help at all. Note that all doc comment
            // properties are nullables
            $this->assertTrue(isset($class->properties['name']));
            $property = $class->properties['name'];
            $this->assertEquals('string', $property->type);
            $this->assertFalse(array_search('mixed', $property->types));
            $this->assertNotFalse(array_search('int', $property->types));
            $this->assertFalse($property->array);
            $this->assertTrue($property->nullable);
            $this->assertTrue(array_search(Marker::class, $property->attributes));
            /* @var Marker $attribute */
            $attribute = $property->attributes[Marker::class];
            $this->assertEquals('marker', $attribute->name);
        });
    }
    public function test_php_documentor_docblock_parser() {
        $factory = DocBlockFactory::createInstance();
        $class = new \ReflectionClass(ClassWithAnnotations::class);
        $docblock = $factory->create($class->getDocComment());
        $a = 1;
    }
}