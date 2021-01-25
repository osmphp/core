<?php

declare(strict_types=1);

namespace Osm\Runtime;

use Osm\Runtime\Traits\HasComputedProperties;

/**
 * Generic base class
 */
class Object_
{
    use HasComputedProperties;

    public static function new(array $data = []): static {
        if (isset($data['class'])) {
            $class = $data['class'];
            unset($data['class']);
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
