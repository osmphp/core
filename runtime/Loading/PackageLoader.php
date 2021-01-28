<?php

declare(strict_types=1);

namespace Osm\Runtime\Loading;

use Osm\App\Package as CorePackage;
use Osm\Runtime\App\App;
use Osm\Runtime\App\ModuleGroup;
use Osm\Runtime\App\Package;
use Osm\Runtime\Attributes\Creates;
use Osm\Runtime\Attributes\Runs;
use Osm\Runtime\OldCompiler;
use Osm\Runtime\Hints\PackageHint;
use Osm\Runtime\Object_;

/**
 * Constructor parameters:
 *
 * @property PackageHint $package
 * @property bool $root
 *
 * Computed:
 *
 * @property string $path
 * @property bool $load_dev
 * @property Package $instance
 *
 * Dependencies:
 *
 * @property App $app
 */
class PackageLoader extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_path(): string {
        return $this->root ? '' : "vendor/{$this->package->name}";
    }

    /** @noinspection PhpUnused */
    protected function get_load_dev(): ?bool {
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_compiler->load_dev;
    }

    /** @noinspection PhpUnused */
    #[Creates(Package::class)]
    protected function get_instance(): ?object {
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_compiler->downgrade(CorePackage::new([
            'name' => $this->package->name,
            'path' => $this->path,
            'after' => array_merge(
                array_keys((array)($this->package->require ?? [])),
                array_keys((array)($this->package->{'require-dev'} ?? [])),
            ),
        ]));
    }

    /** @noinspection PhpUnused */
    protected function get_app(): App {
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_compiler->app;
    }

    public function load(): Package {
        $this->app->packages[$this->package->name] = $this->instance;

        $this->loadSection('autoload');

        if ($this->load_dev) {
            $this->loadSection('autoload-dev');
        }

        return $this->instance;
    }

    protected function loadSection(string $section): void {
        foreach ($this->package->$section->{"psr-4"} ?? [] as $namespace => $path) {
            if ($this->path) {
                $path = "{$this->path}/{$path}";
            }

            $this->loadModuleGroup($namespace, $path);
        }
    }

    #[Runs(ModuleGroupLoader::class)]
    protected function loadModuleGroup(string $namespace, string $path): ?ModuleGroup {
        return ModuleGroupLoader::new([
            'package' => $this->instance,
            'namespace' => $namespace,
            'path' => $path,
        ])->load();
    }
}