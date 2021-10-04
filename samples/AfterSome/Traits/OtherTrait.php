<?php

declare(strict_types=1);

namespace Osm\Core\Samples\AfterSome\Traits;

use Osm\Core\Attributes\UseIn;
use Osm\Core\Samples\Some\Other;

#[UseIn(Other::class)]
trait OtherTrait
{
    use DynamicTrait;

    public function newMethod(): bool {
        return true;
    }
}