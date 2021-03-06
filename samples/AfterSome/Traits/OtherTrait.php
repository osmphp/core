<?php

declare(strict_types=1);

namespace Osm\Core\Samples\AfterSome\Traits;

trait OtherTrait
{
    use DynamicTrait;

    public function newMethod(): bool {
        return true;
    }
}