<?php

declare(strict_types=1);

namespace Osm\Runtime\Classes;

use Osm\Runtime\App\App;
use Osm\Runtime\OldCompiler;
use Osm\Runtime\Object_;

/**
 * Constructor parameters:
 *
 * @property string $name
 *
 * Dependencies:
 *
 * @property App $app
 *
 * Computed:
 *
 * @property \ReflectionClass $reflection
 * @property string[] $direct_trait_names
 * @property string[] $all_trait_names
 * @property string $generated_name
 * @property Property[] $properties
 */
class Class_ extends Object_
{
    public string $upgrade_to_class_name = \Osm\Classes\Class_::class;

    /** @noinspection PhpUnused */
    protected function get_app(): App {
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_compiler->app;
    }

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
    protected function get_generated_name(): ?string {
        if (empty($this->all_trait_names)) {
            return null;
        }

        return "Generated\\{$this->app->name}\\{$this->app->env_name}\\{$this->name}";
    }
}