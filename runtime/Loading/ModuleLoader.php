<?php

declare(strict_types=1);

namespace Osm\Runtime\Loading;

use Osm\App\Module as CoreModule;
use Osm\Runtime\App\App;
use Osm\Runtime\App\Module;
use Osm\Runtime\App\ModuleGroup;
use Osm\Runtime\Attributes\Creates;
use Osm\Runtime\OldCompiler;
use Osm\Runtime\Object_;

/**
 * Constructor parameters:
 *
 * @property ModuleGroup $module_group
 * @property string $namespace
 * @property string $path
 *
 * Computed:
 *
 * @property string $module_path
 * @property string $filename
 * @property string $class
 * @property Module $instance
 *
 * Dependencies:
 *
 * @property App $app
 */
class ModuleLoader extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_module_path(): string {
        global $osm_app; /* @var Compiler $osm_app */

        return rtrim("{$osm_compiler->project_path}/{$this->path}", "/\\");
    }

    /** @noinspection PhpUnused */
    protected function get_filename(): string {
        return "{$this->module_path}/Module.php";
    }

    /** @noinspection PhpUnused */
    protected function get_class(): string {
        return "{$this->namespace}Module";
    }

    /** @noinspection PhpUnused */
    #[Creates(Module::class)]
    protected function get_instance(): ?object {
        global $osm_app; /* @var Compiler $osm_app */

        $instance = $osm_compiler->downgrade(CoreModule::new([
            '__class_name' => $this->class,
            'module_group_class_name' => $this->module_group->class_name,
            'path' => rtrim($this->path, "/\\"),
        ]));

        if (!($instance instanceof Module)) {
            return null;
        }

        if (!$osm_compiler->appMatches($instance->app_class_names)) {
            return null;
        }

        return $instance;
    }

    /** @noinspection PhpUnused */
    protected function get_app(): App {
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_compiler->app;
    }

    public function load(): ?Module {
        if (!is_file($this->filename)) {
            return null; // there is no Module class
        }

        if (!$this->instance) {
            return null; // the class doesn't extend base Module class
        }

        return $this->app->modules[$this->class] = $this->instance;

    }
}