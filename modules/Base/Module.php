<?php

declare(strict_types=1);

namespace Osm\Core\Base;

use Osm\Core\Object_;

/**
 * Constructor parameters:
 *
 * @property string $class_name
 * @property string $path
 * @property string $module_group_class_name
 */
class Module extends Object_
{
    /**
     * @var string[]
     */
    public array $dependencies = [];

    /**
     * @var string[]
     */
    public array $traits = [];
}