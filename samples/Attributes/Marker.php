<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Attributes;

#[\Attribute]
final class Marker
{
    public function __construct(public string $name) {}
}