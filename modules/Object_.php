<?php

declare(strict_types=1);

namespace Osm\Core;

use Osm\Runtime\Object_ as BaseObject;

class Object_ extends BaseObject
{
    protected static function createInstance(string $class, array $data): static {
        global $osm_app; /* @var App $osm_app */

        $class = $osm_app->generated_classes[$class] ?? $class;

        return parent::createInstance($class, $data);
    }
}