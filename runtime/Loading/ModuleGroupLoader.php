<?php

declare(strict_types=1);

namespace Osm\Runtime\Loading;

use Osm\App\ModuleGroup as CoreModuleGroup;
use Osm\Runtime\App\App;
use Osm\Runtime\App\Module;
use Osm\Runtime\App\ModuleGroup;
use Osm\Runtime\App\Package;
use Osm\Runtime\Attributes\Creates;
use Osm\Runtime\Attributes\Runs;
use Osm\Runtime\OldCompiler;
use Osm\Runtime\Object_;

/**
 * Constructor parameters:
 *
 * @property Package $package
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
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_compiler->project_path;
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
        global $osm_app; /* @var Compiler $osm_app */

        $instance = $osm_compiler->downgrade(CoreModuleGroup::new([
            'package_name' => $this->package->name,
            '__class_name' => $this->class,
            'path' => rtrim($this->path, "/\\"),
        ]));

        if (!($instance instanceof ModuleGroup)) {
            return null;
        }

        if (!$osm_compiler->appMatches($instance->app_class_names)) {
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
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_compiler->app;
    }

    public function load(): ?ModuleGroup {
        if (!is_file($this->filename)) {
            return null; // there is no ModuleGroup class
        }

        if (!$this->instance) {
            return null; // the class doesn't extend base ModuleGroup class
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

        return $this->instance;
    }

    #[Runs(ModuleLoader::class)]
    protected function loadModule(string $namespace, string $path): ?Module {
        return ModuleLoader::new([
            'module_group' => $this->instance,
            'namespace' => $namespace,
            'path' => $path,
        ])->load();
    }
}