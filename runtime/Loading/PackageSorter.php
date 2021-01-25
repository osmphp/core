<?php

declare(strict_types=1);

namespace Osm\Runtime\Loading;

use Osm\Core\App;
use Osm\Core\Base\Package;
use Osm\Runtime\Attributes\Runs;
use Osm\Runtime\Exceptions\CircularDependency;
use Osm\Runtime\Factory;
use Osm\Runtime\Object_;

/**
 * Dependencies:
 *
 * @property App $app
 *
 * Temporary:
 *
 * @property int[] $positions
 */
class PackageSorter extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_app(): App {
        global $osm_factory; /* @var Factory $osm_factory */

        return $osm_factory->app;
    }

    public function sort(): void {
        $count = count($this->app->packages);

        $this->positions = [];

        for ($position = 0; $position < $count; $position++) {
            $packageName = $this->findItemWithAlreadyResolvedDependencies();
            if (!$packageName) {
                throw $this->circularDependency();
            }

            $this->positions[$packageName] = $position;
        }

        uasort($this->app->packages, function (Package $a, Package $b) {
            return $this->positions[$a->name] <=> $this->positions[$b->name];
        });

        $this->sortModuleGroups();
    }

    protected function findItemWithAlreadyResolvedDependencies(): ?string {
        foreach ($this->app->packages as $packageName => $package) {
            if (isset($this->positions[$packageName])) {
                continue;
            }

            if ($this->hasUnresolvedDependency($package)) {
                continue;
            }

            return $packageName;
        }

        return null;
    }

    protected function hasUnresolvedDependency(Package $package): bool {
        foreach ($package->after as $dependency) {
            if (!isset($this->app->packages[$dependency])) {
                // no such package - don't consider it a dependency
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

        foreach ($this->app->packages as $packageName => $package) {
            if (!isset($this->positions[$packageName])) {
                $circular[] = $packageName;
            }
        }
        return new CircularDependency(
            sprintf('Packages with circular dependencies found: %s',
            implode(', ', $circular)));
    }

    #[Runs(ModuleGroupSorter::class)]
    protected function sortModuleGroups(): void {
        ModuleGroupSorter::new([
            'package_positions' => $this->positions,
        ])->sort();
    }
}