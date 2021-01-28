<?php

declare(strict_types=1);

namespace Osm\Runtime\Loading;

use Osm\Attributes\Part;
use Osm\Runtime\App\App;
use Osm\Runtime\App\Package;
use Osm\Runtime\Attributes\Runs;
use Osm\Runtime\OldCompiler;
use Osm\Runtime\Hints\ComposerLock;
use Osm\Runtime\Hints\PackageHint;
use Osm\Runtime\Object_;

/**
 * Computed:
 *
 * @property \stdClass|PackageHint $composer_json
 * @property \stdClass|ComposerLock $composer_lock #[\Osm\Attributes\Part]
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
        global $osm_app; /* @var Compiler $osm_app */

        return json_decode(file_get_contents(
            "{$osm_compiler->project_path}/composer.json"));
    }

    /** @noinspection PhpUnused */
    protected function get_composer_lock(): \stdClass {
        global $osm_app; /* @var Compiler $osm_app */

        return json_decode(file_get_contents(
            "{$osm_compiler->project_path}/composer.lock"));
    }

    /** @noinspection PhpUnused */
    protected function get_load_dev(): ?bool {
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_compiler->load_dev;
    }

    /** @noinspection PhpUnused */
    protected function get_app(): App {
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_compiler->app;
    }

    public function load(): void {
        $this->loadSection('packages');

        if ($this->load_dev) {
            $this->loadSection('packages-dev');
        }

        $this->loadPackage($this->composer_json, root: true);

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
        bool $root = false): Package
    {
        $data = [
            'package' => $package,
            'root' => $root,
        ];

        return PackageLoader::new($data)->load();
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