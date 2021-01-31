<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Some\Traits;

/**
 * @property float $pi
 */
trait StaticTrait
{
    /** @noinspection PhpUnused */
    protected function get_pi(): float {
        return pi();
    }
}