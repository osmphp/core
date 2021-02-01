<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Runtime\Object_;
use Osm\Runtime\Traits\Serializable;
use Osm\Core\Attributes\Expected;

/**
 * @property Class_ $class #[Expected]
 * @property string $name #[Expected]
 * @property array|object[] $attributes
 * @property \ReflectionMethod $reflection
 * @property Method[] $around
 */
class Method extends Object_
{
    use Serializable;

    protected function get_serialized_class_name(): string {
        return \Osm\Core\Method::class;
    }

    /** @noinspection PhpUnused */
    protected function get_around(): array {
        $around = [];

        // walk through class hierarchy , take all dynamic traits implementing
        // the around advice for this method

        for ($class = $this->class; $class; $class = $class->parent_class) {
            $classAdvices = [];

            foreach ($class->dynamic_traits as $trait) {
                if (isset($trait->methods["around_{$this->name}"])) {
                    $classAdvices[] = $trait->methods["around_{$this->name}"];
                }
            }

            $around = array_merge($classAdvices, $around);
        }

        return $around;
    }
}