<?php

declare(strict_types=1);

namespace Osm\Runtime\Classes;

use Osm\Runtime\App\App;
use Osm\Runtime\App\ModuleGroup;
use Osm\Object_;
use Osm\Runtime\Attributes\Creates;
use Osm\Runtime\OldCompiler;

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
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_compiler->app;
    }

    /** @noinspection PhpUnused */
    protected function get_path(): string {
        global $osm_app; /* @var Compiler $osm_app */

        return "{$osm_compiler->project_path}/{$this->module_group->path}";
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

            $this->app->classes[$className] = $this->createClass($className,
                $this->loadTraits($className));
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

    #[Creates(Class_::class)]
    protected function createClass(string $className, array $traits): Class_ {
        return Class_::new([
            'name' => $className,
            'traits' => $traits,
        ]);
    }
}