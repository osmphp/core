<?php

declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Runtime\Object_;
use Osm\Runtime\Traits\Serializable;

/**
 * @property string $package_name
 * @property string $class_name
 * @property string $name
 * @property string $path
 * @property string[] $after
 * @property string[] $traits
 * @property ?string $app_class_name
 * @property string[] $requires
 * @property string $namespace
 */
class Module extends Object_
{
    use Serializable;

    protected function get_serialized_class_name(): string {
        return $this->class_name;
    }

    /** @noinspection PhpUnused */
    protected function get_namespace(): string {
        return substr($this->class_name, 0,
            strrpos($this->class_name, '\\'));
    }
}