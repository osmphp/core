<?php

declare(strict_types=1);

namespace Osm\Runtime\Generation;

use Osm\Classes\Class_;
use Osm\Exceptions\NotImplemented;
use Osm\Runtime\Object_;

/**
 * @property Class_ $class
 */
class ClassGenerator extends Object_
{

    public function generate(): string {
        //throw new NotImplemented();
        return '';
    }
}