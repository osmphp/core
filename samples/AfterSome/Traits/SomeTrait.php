<?php

declare(strict_types=1);

namespace Osm\Core\Samples\AfterSome\Traits;

use Osm\Core\Samples\Some\Some;

/**
 * @property int $width
 * @property int $area_size
 */
trait SomeTrait
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
}