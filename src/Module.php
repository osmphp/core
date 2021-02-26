<?php

declare(strict_types=1);

namespace Osm\Core;

/** @noinspection PhpUnused */
class Module extends BaseModule
{
    public static ?string $app_class_name = App::class;

    public static array $classes = [
        \Osm\Runtime\Object_::class,
    ];
}