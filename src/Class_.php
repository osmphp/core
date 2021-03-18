<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Attributes\Serialized;

/**
 * Constructor parameters
 *
 * @property string $name #[Serialized]
 * @property ?string $parent_class_name #[Serialized]
 * @property ?Class_ $parent_class
 * @property Property[] $properties #[Serialized]
 * @property Method[] $methods #[Serialized]
 * @property array|object[] $attributes #[Serialized]
 * @property ?string $generated_name #[Serialized]
 */
class Class_ extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_parent_class(): ?Class_ {
        global $osm_app; /* @var App $osm_app */

        if (!$this->parent_class_name) {
            return null;
        }

        return $osm_app->classes[$this->parent_class_name];
    }
}