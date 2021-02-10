<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Attributes\Serialized;
use Osm\Runtime\Apps;

/**
 * Constructor parameters:
 *
 * @property string $class_name #[Serialized]
 * @property string $name #[Serialized]
 * @property Package[] $packages #[Serialized]
 * @property ModuleGroup[] $module_groups #[Serialized]
 * @property Module[] $modules #[Serialized]
 * @property Class_[] $classes #[Serialized]
 * @property Paths $paths
 */
class App extends Object_ {
    public static bool $load_dev_sections = false;

    /** @noinspection PhpUnused */
    protected function get_paths(): Paths {
        return Paths::new();
    }
}