<?php

declare(strict_types=1);

namespace Osm\Runtime\Loading;

use Osm\Core\App;
use Osm\Core\Base\ModuleGroup;
use Osm\Runtime\Attributes\Creates;
use Osm\Runtime\Attributes\Runs;
use Osm\Runtime\Factory;
use Osm\Runtime\Object_;

/**
 * Constructor parameters:
 *
 * @property PackageLoader $package_loader
 * @property string $namespace
 * @property string $path
 *
 * Computed:
 *
 * @property string $project_path
 * @property string $module_group_path
 * @property string $filename
 * @property string $class
 * @property ModuleGroup $instance
 * @property string $module_glob_pattern
 *
 * Dependencies:
 *
 * @property App $app
 */
class ModuleGroupLoader extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_project_path(): string {
        global $osm_factory; /* @var Factory $osm_factory */

        return $osm_factory->project_path;
    }

    /** @noinspection PhpUnused */
    protected function get_module_group_path(): string {
        return rtrim("{$this->project_path}/{$this->path}", "/\\");
    }

    /** @noinspection PhpUnused */
    protected function get_filename(): string {
        return "{$this->module_group_path}/ModuleGroup.php";
    }

    /** @noinspection PhpUnused */
    protected function get_class(): string {
        return "{$this->namespace}ModuleGroup";
    }

    /** @noinspection PhpUnused */
    #[Creates(ModuleGroup::class)]
    protected function get_instance(): ?object {
        global $osm_factory; /* @var Factory $osm_factory */

        $new = "{$this->class}::new";
        $instance = $new([
            'package_name' => $this->package_loader->instance->name,
            'class_name' => $this->class,
            'path' => rtrim($this->path, "/\\"),
        ]);

        if (!($instance instanceof ModuleGroup)) {
            return null;
        }

        if (!$osm_factory->appMatches($instance->app_class_names)) {
            return null;
        }

        return $instance;
    }

    /** @noinspection PhpUnused */
    protected function get_module_glob_pattern(): string {
        $pattern = str_repeat('/*', $this->instance->depth);
        return "{$this->module_group_path}{$pattern}";
    }

    /** @noinspection PhpUnused */
    protected function get_app(): App {
        global $osm_factory; /* @var Factory $osm_factory */

        return $osm_factory->app;
    }

    public function load(): void {
        if (!is_file($this->filename)) {
            return; // there is no ModuleGroup class
        }

        if (!$this->instance) {
            return; // the class doesn't extend base ModuleGroup class
        }

        $this->app->module_groups[$this->class] = $this->instance;

        foreach (glob($this->module_glob_pattern,
            GLOB_ONLYDIR | GLOB_MARK) as $path)
        {
            $namespace = $this->namespace . str_replace('/', '\\',
                ltrim(mb_substr($path, mb_strlen($this->module_group_path)), "/\\"));
            $path = ltrim(mb_substr($path, mb_strlen($this->project_path)), "/\\");

            $this->loadModule($namespace, $path);
        }
    }

    #[Runs(ModuleLoader::class)]
    protected function loadModule(string $namespace, string $path): void {
        ModuleLoader::new([
            'module_group_loader' => $this,
            'namespace' => $namespace,
            'path' => $path,
        ])->load();
    }
}