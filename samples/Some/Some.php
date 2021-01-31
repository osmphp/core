<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Core\Samples\Some;

use Osm\Core\Object_;
use Osm\Core\Samples\Attributes\Marker;
use Osm\Core\Samples\Attributes\Repeatable;

/**
 * @property mixed|string|int $name #[Marker('marker')]
 * @property mixed|string|int $type #[Repeatable('hi'), Repeatable('world')]
 */
class Some extends Object_
{
    /**
     * @var Some[]
     */
    #[Marker('owns')]
    public array $children = [];

    #[Repeatable('hi'), Repeatable('world')]
    public string $note;

    protected function sqr(int $x): int {
        return $x * $x;
    }
}