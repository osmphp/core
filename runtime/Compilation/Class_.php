<?php

declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Runtime\Object_;
use Osm\Runtime\Traits\Serializable;
use phpDocumentor\Reflection\DocBlock\Tags\Property as PhpDocProperty;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpParser\Node;

/**
 * Constructor parameters
 *
 * @property string $name
 * @property string $filename
 *
 * Computed:
 *
 * @property \ReflectionClass $reflection
 * @property Property[] $doc_comment_properties
 * @property Property[] $properties
 * @property string $imports
 */
class Class_ extends Object_
{
    use Serializable;

    protected function get_serialized_class_name(): string {
        return \Osm\Core\Class_::class;
    }

    /** @noinspection PhpUnused */
    protected function get_reflection(): \ReflectionClass {
        return new \ReflectionClass($this->name);
    }

    /** @noinspection PhpUnused */
    protected function get_doc_comment_properties() : array {
        $properties = [];

        if (!($comment = $this->reflection->getDocComment())) {
            return [];
        }

        $factory = DocBlockFactory::createInstance();
        $docBlock = $factory->create($comment);

        foreach ($docBlock->getTagsByName('property') as $property) {
            /* @var PhpDocProperty $property */
            $properties[$property->getVariableName()] =
                $this->loadDocBlockProperty($property);
        }

        return $properties;
    }

    protected function loadDocBlockProperty(PhpDocProperty $property): Property {
        return Properties\PhpDoc::new([
            'class' => $this,
            'name' => $property->getVariableName(),
            'phpdoc' => $property,
        ]);
    }

    /** @noinspection PhpUnused */
    protected function get_properties() : array {
        return $this->doc_comment_properties;
    }

    /** @noinspection PhpUnused */
    protected function get_imports() : string {
        global $osm_app; /* @var Compiler $osm_app */

        $ast = $osm_app->php_parser->parse(file_get_contents($this->filename));

        return PhpQuery::new($ast)
            ->find(fn (Node $node) => $node instanceof Node\Stmt\Use_)
            ->toString();
    }
}