<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Some\Traits;

trait ObjectTrait
{
    /** @noinspection PhpUnused */
    protected function around_default(callable $proceed, ...$args) {
        return $proceed(...$args);
    }
}