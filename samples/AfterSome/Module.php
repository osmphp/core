<?php

declare(strict_types=1);

namespace Osm\Core\Samples\AfterSome;

use Osm\Core\Base\Module as BaseModule;

/** @noinspection PhpUnused */
class Module extends BaseModule
{
    public array $requires = [
        \Osm\Core\Samples\Some\Module::class,
    ];
}