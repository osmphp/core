<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Core\Samples\Some;

use Osm\Core\Object_;
use Osm\Core\Samples\Attributes\Marker;
use Osm\Core\Samples\Attributes\Repeatable;
use Osm\Core\Samples\Some\Traits\StaticTrait;

/**
 * @property mixed|string|int $name #[Marker('marker')]
 * @property mixed|string|int $type #[Repeatable('hi'), Repeatable('world')]
 */
class Some extends Object_
{
    use StaticTrait;

    /**
     * @var Some[]
     */
    #[Marker('owns')]
    public array $children = [];

    #[Repeatable('hi'), Repeatable('world')]
    public string $note;

    #[Repeatable('method')]
    protected function sqr(int $x): int {
        return $x * $x;
    }

    protected function voidMethod(): void {
    }
}