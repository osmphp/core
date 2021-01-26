<?php

declare(strict_types=1);

namespace Osm;

use Osm\App\App;
use Osm\Attributes\Part;
use Osm\Classes\Class_;
use Osm\Runtime\Object_ as BaseObject;

/**
 * @property Class_ $class
 */
class Object_ extends BaseObject
{
    protected function get_class(): Class_ {
        global $osm_app; /* @var App $osm_app */

        return $osm_app->classes[$this::class];
    }

    protected static function createInstance(string $class, array $data): static {
        global $osm_app; /* @var App $osm_app */

        if (isset($osm_app->classes[$class]->actual_name)) {
            $class = $osm_app->classes[$class]->actual_name;
        }

        return parent::createInstance($class, $data);
    }

    public function __sleep(): array {
        $result = [];

        foreach (get_object_vars($this) as $property => $value) {
            if (isset($this->class->properties[$property]->attributes[Part::class])) {
                $result[] = $property;
            }
        }

        return $result;
    }
}