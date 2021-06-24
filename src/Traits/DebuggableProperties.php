<?php

declare(strict_types=1);

namespace Osm\Core\Traits;

use Osm\Runtime\Traits\ComputedProperties;

trait DebuggableProperties
{
    use ComputedProperties;

    protected array $__data = [];

    public function __construct(array $data = []) {
        $this->__data = $data;
    }

    public function __set(string $property, $value): void {
        $this->__data[$property] = $value;
    }

    public function __get(string $property): mixed {
        return array_key_exists($property, $this->__data)
            ? $this->__data[$property]
            : ($this->__data[$property] = $this->default($property));
    }
}