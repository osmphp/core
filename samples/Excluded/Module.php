<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Excluded;

use Osm\App\Module as BaseModule;

/** @noinspection PhpUnused */
class Module extends BaseModule
{
    public array $app_class_names = ['non_existent'];
}