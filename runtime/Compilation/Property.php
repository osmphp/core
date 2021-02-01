<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Runtime\Object_;
use Osm\Runtime\Traits\Serializable;
use Osm\Core\Attributes\Expected;

/**
 * @property Class_ $class #[Expected]
 * @property string $name #[Expected]
 * @property ?string $type
 * @property bool $array
 * @property bool $nullable
 * @property array|object[] $attributes
 * @property Method? $getter
 */
class Property extends Object_
{
    use Serializable;

    protected function get_serialized_class_name(): string {
        return \Osm\Core\Property::class;
    }

    protected function parsePhpDocType(string $phpDocType): void {
        foreach (explode('|', $phpDocType) as $type) {
            $type = trim($type);
            $type = ltrim($type, '\\');

            if (!$type || $type == 'mixed') {
                continue;
            }

            if (str_starts_with($type, '?')) {
                $this->nullable = true;
                $type = substr($type, strlen('?'));
            }

            if ($type == 'array') {
                $this->array = true;
                continue;
            }

            if (str_ends_with($type, '[]')) {
                $this->array = true;
                $type = substr($type, 0, strlen($type) - strlen('[]'));
            }

            $this->type = $type;
            break;
        }
    }

    /** @noinspection PhpUnused */
    protected function get_getter(): ?Method {
        return $this->class->methods["get_{$this->name}"] ?? null;
    }
}