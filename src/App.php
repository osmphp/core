<?php

declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Base\ModuleGroup as BaseModuleGroup;
use Osm\Core\Base\Module as BaseModule;
use Osm\Core\Base\Package;
use Osm\Core\Classes\Class_;

/**
 * Constructor parameters:
 *
 * @property string $class_name
 * @property string $name
 * @property string $env_name
 */
class App extends Object_
{
    /**
     * @var Package[]
     */
    public array $packages = [];

    /**
     * @var BaseModuleGroup[]
     */
    public array $module_groups = [];

    /**
     * @var BaseModule[]
     */
    public array $modules = [];

    /**
     * @var Class_[]
     */
    public array $classes = [];
}