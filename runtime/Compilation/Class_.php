<?php

declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Runtime\Object_;
use Osm\Runtime\Traits\Serializable;
use phpDocumentor\Reflection\DocBlock\Tags\Property as PhpDocProperty;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Node;
use function Osm\merge;

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
 * @property Property[] $actual_properties
 * @property Property[] $properties
 * @property Node[] $ast
 * @property PhpQuery $imports
 * @property Context $type_context
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
        $docBlock = $factory->create($comment, $this->type_context);

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
    protected function get_actual_properties(): array {
        $properties = [];

        foreach ($this->reflection->getProperties(\ReflectionProperty::IS_PUBLIC)
            as $reflection)
        {
            if ($reflection->isStatic()) {
                continue;
            }

            if ($reflection->getDeclaringClass() != $this->reflection) {
                continue;
            }

            $properties[$reflection->getName()] =
                $this->loadActualProperty($reflection);
        }

        return $properties;
    }

    protected function loadActualProperty(\ReflectionProperty $property): Property {
        return Properties\Reflection::new([
            'class' => $this,
            'name' => $property->getName(),
            'reflection' => $property,
        ]);
    }


    /** @noinspection PhpUnused */
    protected function get_properties() : array {
        return merge($this->actual_properties, $this->doc_comment_properties);
    }

    /** @noinspection PhpUnused */
    protected function get_ast(): array {
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_app->php_parser->parse(file_get_contents($this->filename));
    }

    /** @noinspection PhpUnused */
    protected function get_imports() : PhpQuery {
        return PhpQuery::new($this->ast)
            ->find(fn (Node $node) => $node instanceof Node\Stmt\Use_);
    }

    /** @noinspection PhpUnused */
    protected function get_type_context(): Context {
        $aliases = [];

        foreach ($this->imports->stmts as $import) {
            /* @var Node\Stmt\Use_ $import */
            foreach ($import->uses as $use) {
                $a = 1;
                $alias = $use->getAlias()?->toString() ??
                    $use->name->parts[count($use->name->parts) - 1];
                $aliases[$alias] = $use->name->toString();
            }
        }

        return new Context($this->reflection->getNamespaceName(), $aliases);
    }
}