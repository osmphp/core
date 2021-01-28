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
 * @property int $depth
 * @property string[] $after
 * @property string $namespace
 */
class ModuleGroup extends Object_
{
    use Serializable;

    protected function get_serialized_class_name(): string {
        return $this->class_name;
    }

    /** @noinspection PhpUnused */
    protected function get_namespace(): string {
        return mb_substr($this->class_name, 0,
            mb_strrpos($this->class_name, '\\'));
    }
}