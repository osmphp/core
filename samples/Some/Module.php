<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Some;

use Osm\Core\Module as BaseModule;
use Osm\Core\Object_;

/** @noinspection PhpUnused */
class Module extends BaseModule
{
    public static array $traits = [
        Object_::class => Traits\ObjectTrait::class,
    ];
}