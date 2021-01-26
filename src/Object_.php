<?php

declare(strict_types=1);

namespace Osm\Core;

use Osm\Runtime\Object_ as BaseObject;

class Object_ extends BaseObject
{
    protected static function createInstance(string $class, array $data): static {
        global $osm_app; /* @var App $osm_app */

        //$class = $osm_app->classes[$class]->actual_name;

        return parent::createInstance($class, $data);
    }

    public function __sleep(): array {
        $result = [];

        foreach (get_object_vars($this) as $property => $value) {

            if (isset($this->getProperty($property)['part'])) {
                $result[] = $property;
            }
        }

        return $result;
    }
}