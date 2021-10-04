<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Some\Traits;

use Osm\Core\Attributes\UseIn;
use Osm\Core\Object_;

#[UseIn(Object_::class)]
trait ObjectTrait
{
    /** @noinspection PhpUnused */
    protected function around_default(callable $proceed, ...$args) {
        return $proceed(...$args);
    }
}