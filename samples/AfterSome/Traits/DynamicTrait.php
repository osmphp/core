<?php

declare(strict_types=1);

namespace Osm\Core\Samples\AfterSome\Traits;

use Osm\Core\App;
use Osm\Core\Attributes\UseIn;
use Osm\Core\Samples\Some\Some;

/**
 * @property int $width
 * @property int $area_size
 * @property bool $round_pi
 * @property ?App $app
 */
#[UseIn(Some::class)]
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
        return $this->round_pi ? 3.0 : $proceed();
    }

    public function newMethod(): bool {
        return false;
    }

    protected function around_voidMethod(callable $proceed): void {
        $proceed();
    }
}