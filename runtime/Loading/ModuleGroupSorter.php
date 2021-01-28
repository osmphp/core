<?php

declare(strict_types=1);

namespace Osm\Runtime\Loading;

use Osm\Runtime\App\App;
use Osm\Runtime\App\ModuleGroup;
use Osm\Runtime\Attributes\Runs;
use Osm\Runtime\Exceptions\CircularDependency;
use Osm\Runtime\OldCompiler;
use Osm\Runtime\Object_;

/**
 * Constructor parameters:
 *
 * @property int[] $package_positions
 *
 * Dependencies:
 *
 * @property App $app
 *
 * Temporary:
 *
 * @property int[] $positions
 */
class ModuleGroupSorter extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_app(): App {
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_compiler->app;
    }

    public function sort(): void {
        $count = count($this->app->module_groups);

        $this->positions = [];

        for ($position = 0; $position < $count; $position++) {
            $moduleGroupClassName = $this->findItemWithAlreadyResolvedDependencies();
            if (!$moduleGroupClassName) {
                throw $this->circularDependency();
            }

            $this->positions[$moduleGroupClassName] = $position;
        }

        uasort($this->app->module_groups, function (ModuleGroup $a, ModuleGroup $b) {
            $result = $this->package_positions[$a->package_name]
                <=> $this->package_positions[$b->package_name];

            return $result != 0 ?
                $result
                : $this->positions[$a->class_name] <=> $this->positions[$b->class_name];
        });

        $this->sortModules();
    }

    protected function findItemWithAlreadyResolvedDependencies(): ?string {
        foreach ($this->app->module_groups as $moduleGroupClassName => $moduleGroup) {
            if (isset($this->positions[$moduleGroupClassName])) {
                continue;
            }

            if ($this->hasUnresolvedDependency($moduleGroup)) {
                continue;
            }

            return $moduleGroupClassName;
        }

        return null;
    }

    protected function hasUnresolvedDependency(ModuleGroup $moduleGroup): bool {
        foreach ($moduleGroup->after as $dependency) {
            if (!isset($this->app->module_groups[$dependency])) {
                // no such module group - don't consider it a dependency
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

        foreach ($this->app->module_groups as $moduleGroupClassName => $moduleGroup) {
            if (!isset($this->positions[$moduleGroupClassName])) {
                $circular[] = $moduleGroupClassName;
            }
        }
        return new CircularDependency(
            sprintf('Module groups with circular dependencies found: %s',
            implode(', ', $circular)));
    }

    #[Runs(ModuleSorter::class)]
    protected function sortModules(): void {
        ModuleSorter::new([
            'module_group_positions' => $this->positions,
        ])->sort();
    }
}