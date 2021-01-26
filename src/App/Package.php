<?php

declare(strict_types=1);

namespace Osm\App;

use Osm\Object_;

/**
 * Constructor parameters:
 *
 * @property string $name
 * @property string $path
 */
class Package extends Object_
{
    public string $runtime_class_name = \Osm\Runtime\App\Package::class;

    /**
     * @var string[]
     */
    public array $after = [];
}