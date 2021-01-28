<?php

declare(strict_types=1);

namespace Osm\Runtime\Classes;

use Osm\Runtime\App\App;
use Osm\Runtime\App\ModuleGroup;
use Osm\Runtime\Attributes\Runs;
use Osm\Runtime\OldCompiler;
use Osm\Runtime\Object_;

/**
 * Dependencies:
 *
 * @property App $app
 */
class ClassLoader extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_app(): App {
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_compiler->app;
    }

    public function load(): void {
        foreach ($this->app->module_groups as $moduleGroup) {
            $this->loadModuleGroup($moduleGroup);
        }
    }

    #[Runs(ModuleGroupLoader::class)]
    protected function loadModuleGroup(ModuleGroup $moduleGroup) {
        ModuleGroupLoader::new(['module_group' => $moduleGroup])->load();
    }
}