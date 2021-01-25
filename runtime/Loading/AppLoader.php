<?php

declare(strict_types=1);

namespace Osm\Runtime\Loading;

use Osm\Core\App;
use Osm\Runtime\Attributes\Runs;
use Osm\Runtime\Factory;
use Osm\Runtime\Hints\ComposerLock;
use Osm\Runtime\Hints\PackageHint;
use Osm\Runtime\Object_;

/**
 * Computed:
 *
 * @property \stdClass|PackageHint $composer_json
 * @property \stdClass|ComposerLock $composer_lock
 * @property bool $load_dev
 *
 * Dependencies:
 *
 * @property App $app
 */
class AppLoader extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_composer_json(): \stdClass {
        global $osm_factory; /* @var Factory $osm_factory */

        return json_decode(file_get_contents(
            "{$osm_factory->project_path}/composer.json"));
    }

    /** @noinspection PhpUnused */
    protected function get_composer_lock(): \stdClass {
        global $osm_factory; /* @var Factory $osm_factory */

        return json_decode(file_get_contents(
            "{$osm_factory->project_path}/composer.lock"));
    }

    /** @noinspection PhpUnused */
    protected function get_load_dev(): ?bool {
        global $osm_factory; /* @var Factory $osm_factory */

        return $osm_factory->load_dev;
    }

    /** @noinspection PhpUnused */
    protected function get_app(): App {
        global $osm_factory; /* @var Factory $osm_factory */

        return $osm_factory->app;
    }

    public function load(): void {
        $this->loadSection('packages');

        if ($this->load_dev) {
            $this->loadSection('packages-dev');
        }

        $this->loadPackage($this->composer_json, name: '');

        $this->prunePackagesHavingNoModuleGroups();
        $this->sortPackages();
    }

    protected function loadSection(string $section) {
        foreach ($this->composer_lock->$section as $package) {
            $this->loadPackage($package);
        }
    }

    #[Runs(PackageLoader::class)]
    protected function loadPackage(\stdClass|PackageHint $package,
        ?string $name = null): void
    {
        $data = [
            'package' => $package,
            'app_loader' => $this,
        ];

        if ($name !== null) {
            $data['name'] = $name;
        }

        PackageLoader::new($data)->load();
    }

    protected function prunePackagesHavingNoModuleGroups() {
        $mentionedPackages = [];

        foreach ($this->app->module_groups as $moduleGroup) {
            $mentionedPackages[$moduleGroup->package_name] = true;
        }

        foreach (array_keys($this->app->packages) as $packageName) {
            if (!isset($mentionedPackages[$packageName])) {
                unset($this->app->packages[$packageName]);
            }
        }
    }

    #[Runs(PackageSorter::class)]
    protected function sortPackages(): void {
        PackageSorter::new()->sort();
    }
}