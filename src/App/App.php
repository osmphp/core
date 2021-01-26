<?php

declare(strict_types=1);

namespace Osm\App;

use Osm\Attributes\Part;
use Osm\Classes\Class_;
use Osm\Object_;

/**
 * Constructor parameters:
 *
 * @property string $class_name
 * @property string $name
 * @property string $env_name
 */
class App extends Object_
{
    public string $runtime_class_name = \Osm\Runtime\App\App::class;

    /**
     * @var Package[]
     */
    #[Part]
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