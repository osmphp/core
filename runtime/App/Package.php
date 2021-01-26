<?php

declare(strict_types=1);

namespace Osm\Runtime\App;

use Osm\Object_;

/**
 * Constructor parameters:
 *
 * @property string $name
 * @property string $path
 * @property string $upgrade_to_class_name
 */
class Package extends Object_
{
    /**
     * @var string[]
     */
    public array $after = [];
}