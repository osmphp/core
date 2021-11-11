<?php

namespace Osm\Core\Traits;

use Osm\Core\Attributes\Serialized;
use Osm\Core\Attributes\Type;
use Osm\Core\Object_;

/**
 * @property string $type #[Serialized]
 */
trait SubTypes
{
    protected function get_type(): ?string {
        /* @var Object_|static $this */
        /* @var Type $type */

        return ($type = $this->__class->attributes[Type::class] ?? null)
            ? $type->name
            : null;
    }
}