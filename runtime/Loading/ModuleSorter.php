<?php

declare(strict_types=1);

namespace Osm\Runtime\Loading;

use Osm\Runtime\App\App;
use Osm\Runtime\App\Module;
use Osm\Runtime\Exceptions\CircularDependency;
use Osm\Runtime\Factory;
use Osm\Runtime\Object_;

/**
 * Constructor parameters:
 *
 * @property int[] $module_group_positions
 *
 * Dependencies:
 *
 * @property App $app
 *
 * Temporary:
 *
 * @property int[] $positions
 */
class ModuleSorter extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_app(): App {
        global $osm_factory; /* @var Factory $osm_factory */

        return $osm_factory->app;
    }

    public function sort(): void {
        $count = count($this->app->modules);
        $this->positions = [];

        for ($position = 0; $position < $count; $position++) {
            $moduleClassName = $this->findItemWithAlreadyResolvedDependencies();
            if (!$moduleClassName) {
                throw $this->circularDependency();
            }

            $this->positions[$moduleClassName] = $position;
        }

        uasort($this->app->modules, function (Module $a, Module $b) {
            $result = $this->module_group_positions[$a->module_group_class_name]
                <=> $this->module_group_positions[$b->module_group_class_name];

            return $result != 0 ?
                $result
                : $this->positions[$a->class_name] <=> $this->positions[$b->class_name];
        });
    }

    protected function findItemWithAlreadyResolvedDependencies(): ?string {
        foreach ($this->app->modules as $moduleClassName => $module) {
            if (isset($this->positions[$moduleClassName])) {
                continue;
            }

            if ($this->hasUnresolvedDependency($module)) {
                continue;
            }

            return $moduleClassName;
        }

        return null;
    }

    protected function hasUnresolvedDependency(Module $module): bool {
        foreach ($module->after as $dependency) {
            if (!isset($this->app->modules[$dependency])) {
                // no such module - don't consider it a dependency
                continue;
            }

            if (!isset($this->positions[$dependency])) {
                // dependencies not added to the position array are not
                // resolved yet
                return true;
            }
        }

        return false;
    }

    protected function circularDependency(): CircularDependency {
        $circular = [];

        foreach ($this->app->modules as $moduleClassName => $module) {
            if (!isset($this->positions[$moduleClassName])) {
                $circular[] = $moduleClassName;
            }
        }
        return new CircularDependency(
            sprintf('Modules with circular dependencies found: %s',
            implode(', ', $circular)));
    }
}