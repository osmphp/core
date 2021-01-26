<?php

declare(strict_types=1);

namespace Osm\Runtime\Classes;

use Osm\Runtime\Object_;

/**
 * @property string $name
 * @property string $type
 * @property Class_ $class
 * @property bool $array
 */
class Property extends Object_
{
    public string $upgrade_to_class_name = \Osm\Classes\Property::class;
}