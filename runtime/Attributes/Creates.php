<?php

declare(strict_types=1);

namespace Osm\Runtime\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Creates
{
    public string $class_name;

    public function __construct($className) {
        $this->class_name = $className;
    }
}