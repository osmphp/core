<?php

declare(strict_types=1);

namespace Osm\Core\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class Type
{
    public function __construct(public string $name) {
    }
}