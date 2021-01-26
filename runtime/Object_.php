<?php

declare(strict_types=1);

namespace Osm\Runtime;

use Osm\Runtime\Traits\ComputedProperties;

/**
 * Generic base class
 */
class Object_
{
    use ComputedProperties;

    public static function new(array $data = []): static {
        if (isset($data['class_name'])) {
            $class = $data['class_name'];
        }
        else {
            $class = static::class;
        }

        return static::createInstance($class, $data);
    }

    protected static function createInstance(string $class, array $data): static {
        return new $class($data);
    }

    public function __construct(array $data = []) {
        foreach ($data as $property => $value) {
            $this->$property = $value;
        }
    }
}
