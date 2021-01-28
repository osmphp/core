<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Attributes;

/**
 * @Annotation
 */
#[\Attribute]
class Marker
{
    public function __construct(public string $name) {}
}