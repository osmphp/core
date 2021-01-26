<?php

declare(strict_types=1);

namespace Osm\Core\Classes;

use Osm\Core\App;
use Osm\Core\Object_;

/**
 * Constructor parameters:
 *
 * @property App $app
 * @property string $name
 *
 * Computed:
 *
 * @property \ReflectionClass $reflection
 * @property string[] $direct_trait_names
 * @property string[] $all_trait_names
 * @property string $actual_name
 */
class Class_ extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_reflection(): \ReflectionClass {
        return new \ReflectionClass($this->name);
    }

    /** @noinspection PhpUnused */
    protected function get_direct_trait_names(): array {
        $result = [];

        foreach ($this->app->modules as $module) {
            if (isset($module->traits[$this->name])) {
                $result[$module->traits[$this->name]] = true;
            }
        }

        return array_keys($result);
    }

    /** @noinspection PhpUnused */
    protected function get_all_trait_names(): array {
        $result = [];

        for ($class = $this; $class; ) {
            $result = array_merge(array_flip($class->direct_trait_names),
                $result);

            if ($class->reflection->getParentClass()) {
                $parentClassName = $class->reflection->getParentClass()->getName();
                $class = $this->app->classes[$parentClassName] ?? null;
            }
            else {
                $class = null;
            }
        }

        return array_keys($result);
    }

    /** @noinspection PhpUnused */
    protected function get_actual_name(): string {
        if (empty($this->all_trait_names)) {
            return $this->name;
        }

        return "Generated\\{$this->app->name}\\{$this->app->env_name}\\{$this->name}";
    }
}