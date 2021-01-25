<?php

declare(strict_types=1);

namespace Osm\Runtime\Loading;

use Osm\Core\App;
use Osm\Core\Base\Package;
use Osm\Runtime\Attributes\Creates;
use Osm\Runtime\Attributes\Runs;
use Osm\Runtime\Factory;
use Osm\Runtime\Hints\PackageHint;
use Osm\Runtime\Object_;

/**
 * Constructor parameters:
 *
 * @property AppLoader $app_loader
 * @property PackageHint $package
 * @property string $name
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
    protected function get_name(): string {
        return $this->package->name;
    }

    /** @noinspection PhpUnused */
    protected function get_path(): string {
        return $this->name ? "vendor/{$this->name}" : '';
    }

    /** @noinspection PhpUnused */
    protected function get_load_dev(): ?bool {
        global $osm_factory; /* @var Factory $osm_factory */

        return $osm_factory->load_dev;
    }

    /** @noinspection PhpUnused */
    #[Creates(Package::class)]
    protected function get_instance(): ?object {
        return Package::new([
            'name' => $this->package->name,
            'path' => $this->path,
            'after' => array_merge(
                array_keys((array)($this->package->require ?? [])),
                array_keys((array)($this->package->{'require-dev'} ?? [])),
            ),
        ]);
    }

    /** @noinspection PhpUnused */
    protected function get_app(): App {
        global $osm_factory; /* @var Factory $osm_factory */

        return $osm_factory->app;
    }

    public function load(): void {
        $this->app->packages[$this->package->name] = $this->instance;

        $this->loadSection('autoload');

        if ($this->load_dev) {
            $this->loadSection('autoload-dev');
        }
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
    protected function loadModuleGroup(string $namespace, string $path): void {
        ModuleGroupLoader::new([
            'package_loader' => $this,
            'namespace' => $namespace,
            'path' => $path,
        ])->load();
    }
}