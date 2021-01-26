<?php

declare(strict_types=1);

namespace Osm\Runtime\App;

use Osm\Runtime\Classes\Class_;
use Osm\Runtime\Object_;

/**
 * Constructor parameters:
 *
 * @property string $class_name
 * @property string $name
 * @property string $env_name
 * @property string $upgrade_to_class_name
 */
class App extends Object_
{
    /**
     * @var Package[]
     */
    public array $packages = [];

    /**
     * @var ModuleGroup[]
     */
    public array $module_groups = [];

    /**
     * @var Module[]
     */
    public array $modules = [];

    /**
     * @var Class_[]
     */
    public array $classes = [];

}