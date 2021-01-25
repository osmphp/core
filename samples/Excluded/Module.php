<?php

declare(strict_types=1);

namespace Osm\Core\Samples\Excluded;

use Osm\Core\Base\Module as BaseModule;

class Module extends BaseModule
{
    public string $app_class_name = 'non_existent';
}