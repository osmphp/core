<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD |
    \Attribute::IS_REPEATABLE)]
class Repeatable
{
    public function __construct(public string $name) {}
}