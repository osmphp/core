<?php

declare(strict_types=1);

namespace Osm\Runtime\Classes;

use Osm\Core\App;
use Osm\Core\Base\ModuleGroup;
use Osm\Core\Classes\Class_;
use Osm\Core\Object_;
use Osm\Runtime\Factory;

/**
 * Constructor parameters:
 *
 * @property ModuleGroup $module_group
 *
 * Dependencies:
 *
 * @property App $app
 *
 * Computed:
 *
 * @property string $path
 *
 */
class ModuleGroupLoader extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_app(): App {
        global $osm_factory; /* @var Factory $osm_factory */

        return $osm_factory->app;
    }

    /** @noinspection PhpUnused */
    protected function get_path(): string {
        global $osm_factory; /* @var Factory $osm_factory */

        return "{$osm_factory->project_path}/{$this->module_group->path}";
    }

    public function load(string $path = ''): void {
        $absolutePath = $path ? "{$this->path}/{$path}" : $this->path;

        foreach (new \DirectoryIterator($absolutePath) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if (!preg_match('/^[A-Z]/', $fileInfo->getFilename())) {
                continue;
            }

            if ($fileInfo->isDir()) {
                $this->load(($path ? "{$path}/" : '') . $fileInfo->getFilename());
                continue;
            }

            if ($fileInfo->getExtension() != 'php') {
                continue;
            }

            $className = $this->module_group->namespace . '\\' .
                str_replace('/', '\\',
                    ($path ? "{$path}/" : '') .
                    pathinfo($fileInfo->getFilename(), PATHINFO_FILENAME)
                );

            $this->app->classes[$className] = Class_::new([
                'app' => $this->app,
                'name' => $className,
                'traits' => $this->loadTraits($className),
            ]);
        }
    }

    protected function loadTraits(string $className): array{
        $result = [];

        foreach ($this->app->modules as $module) {
            if (isset($module->traits[$className])) {
                $result[$module->traits[$className]] = true;
            }
        }

        return array_keys($result);
    }
}