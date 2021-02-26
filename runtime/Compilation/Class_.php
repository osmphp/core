<?php

declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Runtime\Compilation\Properties\Merged as MergedProperty;
use Osm\Runtime\Compilation\Methods\Merged as MergedMethod;
use Osm\Runtime\Object_;
use Osm\Runtime\Traits\Serializable;
use phpDocumentor\Reflection\DocBlock\Tags\Property as PhpDocProperty;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Context;
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
 * @property Class_ $parent_class
 * @property Class_[] $static_traits
 * @property Class_[] $actual_dynamic_traits
 * @property Class_[] $dynamic_traits
 * @property Class_[] $traits
 * @property Property[] $inherited_properties
 * @property Property[] $doc_comment_properties
 * @property Property[] $actual_properties
 * @property Property[] $properties
 * @property Method[] $actual_methods
 * @property Method[] $methods
 * @property Node[] $ast
 * @property PhpQuery $imports
 * @property Context $type_context
 * @property bool $generated
 * @property ?string $generated_name
 * @property ?string $generated_namespace
 * @property string $short_name
 * @property bool $abstract
 * @property string $namespace
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
    protected function get_parent_class(): ?Class_ {
        global $osm_app; /* @var Compiler $osm_app */

        if (!($class = $this->reflection->getParentClass())) {
            return null;
        }

        return $osm_app->app->classes[$class->getName()] ?? null;
    }

    /** @noinspection PhpUnused */
    protected function get_static_traits(): array {
        global $osm_app; /* @var Compiler $osm_app */

        $traits = [];

        foreach ($this->reflection->getTraits() ?? [] as $trait) {
            if (isset($osm_app->app->classes[$trait->getName()])) {
                $traits[$trait->getName()] =
                    $osm_app->app->classes[$trait->getName()];
            }
        }

        return $traits;
    }

    /** @noinspection PhpUnused */
    protected function get_actual_dynamic_traits(): array {
        global $osm_app; /* @var Compiler $osm_app */

        $traits = [];

        foreach ($osm_app->app->modules as $module) {
            foreach ($module->traits as $className => $traitName) {
                if ($this->name == $className) {
                    $traits[$traitName] = $osm_app->app->classes[$traitName];
                }
            }
        }

        return $traits;
    }

    /** @noinspection PhpUnused */
    protected function get_dynamic_traits(): array {
        global $osm_app; /* @var Compiler $osm_app */

        $traits = [];

        foreach ($osm_app->app->modules as $module) {
            foreach ($module->traits as $className => $traitName) {
                if (is_a($this->name, $className, true)) {
                    $traits[$traitName] = $osm_app->app->classes[$traitName];
                }
            }
        }

        return $traits;
    }

    /** @noinspection PhpUnused */
    protected function get_traits(): array {
        return array_merge($this->static_traits, $this->dynamic_traits);
    }

    /** @noinspection PhpUnused */
    protected function get_doc_comment_properties() : array {
        $properties = [];

        if (!($comment = $this->reflection->getDocComment())) {
            return [];
        }

        $factory = DocBlockFactory::createInstance();
        try {
            $docBlock = $factory->create($comment, $this->type_context);
        }
        catch (\Exception $e) {
            throw new \Exception("{$this->name}: {$e->getMessage()}",
                $e->getCode(), $e);
        }

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
        $properties = $this->parent_class?->properties ?? [];

        foreach ($this->traits as $trait) {
            foreach ($trait->properties as $name => $property) {
                $properties[$name] = $this->mergeProperty($properties[$name] ?? null,
                    $property);
            }
        }

        foreach ($this->actual_properties as $name => $property) {
            $properties[$name] = $this->mergeProperty($properties[$name] ?? null,
                $property);
        }

        foreach ($this->doc_comment_properties as $name => $property) {
            $properties[$name] = $this->mergeProperty($properties[$name] ?? null,
                $property);
        }

        return $properties;
    }

    /** @noinspection PhpUnused */
    protected function get_actual_methods(): array {
        $methods = [];

        foreach ($this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC |
            \ReflectionMethod::IS_PROTECTED) as $reflection)
        {
            if ($reflection->isStatic()) {
                continue;
            }

            if ($reflection->getDeclaringClass() != $this->reflection) {
                continue;
            }

            $methods[$reflection->getName()] = $this->loadActualMethod($reflection);
        }

        return $methods;
    }

    protected function loadActualMethod(\ReflectionMethod $method): Method {
        return Methods\Reflection::new([
            'class' => $this,
            'name' => $method->getName(),
            'reflection' => $method,
        ]);
    }


    /** @noinspection PhpUnused */
    protected function get_methods() : array {
        $methods = [];

        foreach ($this->parent_class?->methods ?? [] as $name => $method) {
            $methods[$name] = $this->mergeMethod(null, $method);
        }

        foreach ($this->traits as $trait) {
            foreach ($trait->methods as $name => $method) {
                $methods[$name] = $this->mergeMethod($methods[$name] ?? null, $method);
            }
        }

        foreach ($this->actual_methods as $name => $method) {
            $methods[$name] = $this->mergeMethod($methods[$name] ?? null, $method);
        }

        return $methods;
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

    protected function mergeProperty(?Property $base, Property $derived): Property {
        if (!$base) {
            return $derived;
        }

        $base = MergedProperty::new([
            'name' => $base->name,
            'class' => $this,
            'properties' => $base instanceof MergedProperty ?
                $base->properties :
                [$base],
        ]);

        $base->properties[] = $derived;

        return $base;
    }

    protected function mergeMethod(?Method $base, Method $derived): Method {
        $derivedMethods = $derived instanceof MergedMethod
            ? $derived->methods
            : [$derived];

        if (!$base) {
            return MergedMethod::new([
                'name' => $derived->name,
                'class' => $this,
                'methods' => $derivedMethods,
            ]);
        }

        $baseMethods = $base instanceof MergedMethod ? $base->methods : [$base];

        foreach ($baseMethods as $method) {
            if ($method->reflection->getFileName() ==
                $derived->reflection->getFileName())
            {
                return $base;
            }

        }

        $base = MergedMethod::new([
            'name' => $base->name,
            'class' => $this,
            'methods' => $baseMethods,
        ]);

        $base->methods[] = $derived;

        return $base;
    }

    /** @noinspection PhpUnused */
    protected function get_generated(): bool {
        if ($this->abstract) {
            return false;
        }
        if (!empty($this->dynamic_traits)) {
            return true;
        }

        return false;
    }

    /** @noinspection PhpUnused */
    protected function get_generated_name(): ?string {
        global $osm_app; /* @var Compiler $osm_app */

        return $this->generated
            ? "{$osm_app->app->name}\\{$this->name}"
            : null;
    }

    /** @noinspection PhpUnused */
    protected function get_generated_namespace(): ?string {
        global $osm_app; /* @var Compiler $osm_app */

        return $this->generated
            ? "{$osm_app->app->name}\\{$this->namespace}"
            : null;
    }

    /** @noinspection PhpUnused */
    protected function get_short_name(): ?string {
        return $this->reflection->getShortName();
    }

    /** @noinspection PhpUnused */
    protected function get_abstract(): bool {
        return $this->reflection->isAbstract();
    }

    /** @noinspection PhpUnused */
    protected function get_namespace(): string {
        return $this->reflection->getNamespaceName();
    }
}