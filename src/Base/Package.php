<?php

declare(strict_types=1);

namespace Osm\Core\Base;

use Osm\Core\Object_;

/**
 * Constructor parameters:
 *
 * @property string $name
 * @property string $path
 */
class Package extends Object_
{
    /**
     * @var string[]
     */
    public array $after = [];
}