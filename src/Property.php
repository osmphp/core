<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Attributes\Serialized;

/**
 * @property string $class_name #[Serialized]
 * @property Class_ $class
 * @property string $name #[Serialized]
 * @property ?string $type #[Serialized]
 * @property bool $array #[Serialized]
 * @property bool $nullable #[Serialized]
 * @property array|object[] $attributes #[Serialized]
 * @property ?Method $getter
 * @property ?string $module_class_name #[Serialized]
 */
class Property extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_class(): Class_ {
        global $osm_app; /* @var App $osm_app */

        return $osm_app->classes[$this->class_name];
    }

    /** @noinspection PhpUnused */
    protected function get_getter(): ?Method {
        return $this->class->methods["get_{$this->name}"] ?? null;
    }
}