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
 * @property BaseModule[] $modules #[Serialized]
 * @property Class_[] $classes #[Serialized]
 * @property Paths $paths
 */
class App extends Object_ {
    public static bool $load_dev_sections = false;
    public static bool $load_all = false;

    /** @noinspection PhpUnused */
    protected function get_paths(): Paths {
        return Paths::new();
    }

    public function boot(): void {
        foreach ($this->modules as $module) {
            $module->boot();
        }
    }

    public function terminate(): void {
        foreach (array_reverse($this->modules) as $module) {
            $module->terminate();
        }
    }

    public function create(string $className, ...$args): object {
        $className = $this->classes[$className]->generated_name ?? $className;
        return new $className(...$args);
    }
}