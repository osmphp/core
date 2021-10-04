<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Some\Traits;

use Osm\Core\Attributes\UseIn;
use Symfony\Component\Lock\Store\InMemoryStore;

#[UseIn(InMemoryStore::class)]
trait InMemoryStoreTrait
{
    public function testMethod(): bool {
        return true;
    }
}