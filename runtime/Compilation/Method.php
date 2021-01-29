<?php

declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Runtime\Object_;
use Osm\Runtime\Traits\Serializable;

class Method extends Object_
{
    use Serializable;

    protected function get_serialized_class_name(): string {
        return \Osm\Core\Method::class;
    }

}