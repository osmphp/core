<?php

declare(strict_types=1);

namespace Osm\Core\Samples\AfterSome;

use Osm\Core\Base\Module as BaseModule;
use Osm\Core\Samples\Some\Some;

/** @noinspection PhpUnused */
class Module extends BaseModule
{
    public array $after = [
        \Osm\Core\Samples\Some\Module::class,
    ];

    public array $traits = [
        Some::class => Traits\SomeTrait::class,
    ];
}