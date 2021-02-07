<?php

declare(strict_types=1);

namespace Osm\Core\Samples\AfterSome;

use Osm\Core\Module as BaseModule;
use Osm\Core\Samples\App;
use Osm\Core\Samples\Some\Some;

/** @noinspection PhpUnused */
class Module extends BaseModule
{
    public static ?string $app_class_name = App::class;

    public static array $requires = [
        \Osm\Core\Samples\Some\Module::class,
    ];

    public static array $traits = [
        Some::class => Traits\DynamicTrait::class,
    ];
}