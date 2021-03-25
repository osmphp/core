<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Some;

use Osm\Core\Attributes\Name;
use Osm\Core\BaseModule;
use Osm\Core\Object_;
use Symfony\Component\Lock\Store\InMemoryStore;

/** @noinspection PhpUnused */
#[Name('sample-some')]
class Module extends BaseModule
{
    public static array $traits = [
        Object_::class => Traits\ObjectTrait::class,
        InMemoryStore::class => Traits\InMemoryStoreTrait::class,
    ];

    public static array $classes = [
        InMemoryStore::class,
    ];
}