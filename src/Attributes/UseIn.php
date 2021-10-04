<?php

namespace Osm\Core\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class UseIn
{
    public function __construct(public string $class_name)
    {
    }
}