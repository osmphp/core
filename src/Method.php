<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Attributes\Serialized;

/**
 * @property string $class_name #[Serialized]
 * @property string $name #[Serialized]
 * @property Class_ $class
 * @property array|object[] $attributes #[Serialized]
 * @property \ReflectionMethod $reflection
 */
class Method extends Object_
{
    protected function get_class(): Class_ {
        global $osm_app; /* @var App $osm_app */

        return $osm_app->classes[$this->class_name];
    }

    protected function get_reflection(): \ReflectionMethod {
        $className = $this->class->generated_name ?? $this->class->name;
        return new \ReflectionMethod($className, $this->name);
    }
}