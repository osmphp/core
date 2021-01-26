<?php

declare(strict_types=1);

namespace Osm\App;

use Osm\Object_;

/**
 * Constructor parameters:
 *
 * @property string $class_name
 * @property string $path
 * @property string $module_group_class_name
 */
class Module extends Object_
{
    public string $runtime_class_name = \Osm\Runtime\App\Module::class;

    /**
     * @var string[]
     */
    public array $app_class_names = [App::class];

    /**
     * @var string[]
     */
    public array $after = [];

    /**
     * @var string[]
     */
    public array $traits = [];
}