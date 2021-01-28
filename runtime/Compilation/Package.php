<?php

declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Runtime\Hints\PackageHint;
use Osm\Runtime\Object_;
use Osm\Runtime\Traits\Serializable;

/**
 * @property string $name
 * @property string $path
 * @property \stdClass|PackageHint $json
 * @property string[] $after
 */
class Package extends Object_
{
    use Serializable;

    protected function get_serialized_class_name(): string {
        return \Osm\Core\Package::class;
    }

}