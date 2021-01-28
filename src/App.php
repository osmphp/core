<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Attributes\Serialized;
use Osm\Core\Attributes\Required;

/**
 * Constructor parameters:
 *
 * @property string $class_name #[Serialized, Required]
 * @property string $name #[Serialized, Required]
 * @property Package[] $packages #[Serialized, Required]
 * @property ModuleGroup[] $module_groups #[Serialized, Required]
 * @property Module[] $modules #[Serialized, Required]
 * @property Class_[] $classes #[Serialized, Required]
 */
class App extends Object_ {
    public static bool $load_dev_sections = false;
}