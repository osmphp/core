<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Runtime\Object_;
use Osm\Runtime\Traits\Serializable;
use Osm\Core\Attributes\Expected;

/**
 * Constructor parameters:
 *
 * @property Class_ $class #[Expected]
 * @property string $name #[Expected]
 *
 * Computed:
 *
 * @property ?string $type
 * @property bool $array
 * @property bool $nullable
 * @property object[] $attributes
 */
class Property extends Object_
{
    use Serializable;

    protected function get_serialized_class_name(): string {
        return \Osm\Core\Property::class;
    }

}