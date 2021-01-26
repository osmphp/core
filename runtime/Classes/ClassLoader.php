<?php

declare(strict_types=1);

namespace Osm\Runtime\Classes;

use Osm\Core\App;
use Osm\Core\Base\ModuleGroup;
use Osm\Runtime\Attributes\Runs;
use Osm\Runtime\Factory;
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
        global $osm_factory; /* @var Factory $osm_factory */

        return $osm_factory->app;
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