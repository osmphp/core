<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Excluded;

use Osm\Core\BaseModule;

/** @noinspection PhpUnused */
class Module extends BaseModule
{
    // you can reference non-existent app class, or as in this case, app-less
    // module is not referenced by any other module
    //public static ?string $app_class_name = 'non_existent';
}