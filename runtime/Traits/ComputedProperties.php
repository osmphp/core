<?php

declare(strict_types=1);

namespace Osm\Runtime\Traits;

trait ComputedProperties
{
    public function __get(string $property): mixed {
        return $this->$property = $this->default($property);
    }

    protected function default(string $property): mixed {
        $method = "get_{$property}";
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return null;
    }
}