<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Some;

use Osm\Core\Object_;
use Osm\Core\Samples\Attributes\Marker;

/**
 * @property mixed|string|int $name #[Marker('marker')]
 */
class Some extends Object_
{
    protected function sqr(int $x): int {
        return $x * $x;
    }
}