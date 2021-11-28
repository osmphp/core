<?php

declare(strict_types=1);

namespace Osm\Core\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
final class Serialized
{
    public function __construct(public ?string $not_having = null)
    {
    }
}