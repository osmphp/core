<?php

declare(strict_types=1);

namespace Osm\Core\Base;

use Osm\Core\App;
use Osm\Core\Object_;

/**
 * Constructor parameters:
 *
 * @property string $class_name
 * @property string $path
 */
class ModuleGroup extends Object_
{
    /**
     * @var string[]
     */
    public array $app_class_names = [App::class];

    // 1 means that each direct subdirectory contains a module
    public int $depth = 1;
}