<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Runtime\Compilation\Methods;

use Osm\Runtime\Compilation\Method;
use Osm\Core\Attributes\Expected;
use function Osm\merge;

/**
 * @property Method[] $methods #[Expected]
 */
class Merged extends Method
{
    /** @noinspection PhpUnused */
    protected function get_attributes(): array {
        $attributes = [];

        foreach ($this->methods as $method) {
            $attributes = merge($attributes, $method->attributes);
        }

        return $attributes;
    }

    /** @noinspection PhpUnused */
    protected function get_reflection(): \ReflectionMethod {
        return $this->methods[count($this->methods) - 1]->reflection;
    }
}