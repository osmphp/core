<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Some;

use Osm\Object_;

class Some extends Object_
{
    protected function sqr(int $x): int {
        return $x * $x;
    }
}