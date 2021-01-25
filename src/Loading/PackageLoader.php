<?php

declare(strict_types=1);

namespace Osm\Runtime\Loading;

use Osm\Runtime\Factory;
use Osm\Runtime\Hints\Package;
use Osm\Runtime\Object_;

/**
 * Constructor parameters:
 *
 * @property AppLoader $app_loader
 * @property Package $package
 * @property string $name
 *
 * Computed:
 *
 * @property string $path
 * @property bool $load_from_autoload_dev
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
    protected function get_load_from_autoload_dev(): ?bool {
        global $osm_factory; /* @var Factory $osm_factory */

        return $osm_factory->load_from_autoload_dev;
    }

    public function load(): void {
        $this->loadSection('autoload');

        if ($this->load_from_autoload_dev) {
            $this->loadSection('autoload-dev');
        }
    }

    protected function loadSection(string $section): void {
        foreach ($this->package->$section->{"psr-4"} ?? [] as $namespace => $path) {
            if ($this->path) {
                $path = "{$this->path}/{$path}";
            }

            ModuleGroupLoader::new([
                'package_loader' => $this,
                'namespace' => $namespace,
                'path' => $path,
            ])->load();
        }
    }
}