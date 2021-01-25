<?php

declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Base\ModuleGroup as BaseModuleGroup;
use Osm\Core\Base\Module as BaseModule;

class App extends Object_
{
    /**
     * @var BaseModuleGroup[]
     */
    public array $module_groups = [];

    /**
     * @var BaseModule[]
     */
    public array $modules = [];
}