<?php

declare(strict_types=1);

namespace Osm\Core\Samples\AfterSome\Traits;

use Osm\Core\Samples\Some\Some;

/**
 * @property int $width
 * @property int $area_size
 */
trait DynamicTrait
{
    /** @noinspection PhpUnused */
    protected function get_width(): int {
        return 10;
    }

    /** @noinspection PhpUnused */
    protected function get_area_size(): int {
        /* @var Some|static $this */

        return $this->sqr($this->width);
    }

    /** @noinspection PhpUnused */
    protected function around_get_pi(callable $proceed): float {
        return $proceed();
    }
}