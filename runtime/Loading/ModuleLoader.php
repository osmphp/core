<?php

declare(strict_types=1);

namespace Osm\Runtime\Loading;

use Osm\App\Module as CoreModule;
use Osm\Runtime\App\App;
use Osm\Runtime\App\Module;
use Osm\Runtime\Attributes\Creates;
use Osm\Runtime\Factory;
use Osm\Runtime\Object_;

/**
 * Constructor parameters:
 *
 * @property ModuleGroupLoader $module_group_loader
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
        global $osm_factory; /* @var Factory $osm_factory */

        return rtrim("{$osm_factory->project_path}/{$this->path}", "/\\");
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
        global $osm_factory; /* @var Factory $osm_factory */

        $instance = $osm_factory->downgrade(CoreModule::new([
            'class_name' => $this->class,
            'module_group_class_name' => $this->module_group_loader->class,
            'path' => rtrim($this->path, "/\\"),
        ]));

        if (!($instance instanceof Module)) {
            return null;
        }

        if (!$osm_factory->appMatches($instance->app_class_names)) {
            return null;
        }

        return $instance;
    }

    /** @noinspection PhpUnused */
    protected function get_app(): App {
        global $osm_factory; /* @var Factory $osm_factory */

        return $osm_factory->app;
    }

    public function load(): void {
        if (!is_file($this->filename)) {
            return; // there is no Module class
        }

        if (!$this->instance) {
            return; // the class doesn't extend base Module class
        }

        $this->app->modules[$this->class] = $this->instance;

    }
}