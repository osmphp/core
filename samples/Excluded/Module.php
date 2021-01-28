<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Excluded;

use Osm\Core\Module as BaseModule;

/** @noinspection PhpUnused */
class Module extends BaseModule
{
    public static string $app_class_name = 'non_existent';
}