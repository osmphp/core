<?php

declare(strict_types=1);

namespace Osm\Core\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class Runs
{
    public string $class_name;

    public function __construct($className) {
        $this->class_name = $className;
    }
}