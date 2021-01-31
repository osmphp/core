<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Runtime\Compilation\Properties;

use Osm\Runtime\Compilation\Property;
use Osm\Core\Attributes\Expected;
use function Osm\merge;

/**
 * @property Property[] $properties #[Expected]
 */
class Merged extends Property
{
    /** @noinspection PhpUnused */
    protected function get_type(): ?string {
        return $this->properties[count($this->properties) - 1]->type;
    }

    /** @noinspection PhpUnused */
    protected function get_array(): bool {
        return $this->properties[count($this->properties) - 1]->array;
    }

    /** @noinspection PhpUnused */
    protected function get_nullable(): bool {
        return $this->properties[count($this->properties) - 1]->nullable;
    }

    /** @noinspection PhpUnused */
    protected function get_attributes(): array {
        $attributes = [];

        foreach ($this->properties as $property) {
            $attributes = merge($attributes, $property->attributes);
        }

        return $attributes;
    }
}