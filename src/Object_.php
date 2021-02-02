<?php

declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Attributes\Serialized;
use Osm\Runtime\Object_ as BaseObject;

/**
 * @property Class_ $__class
 */
class Object_ extends BaseObject
{
    /** @noinspection PhpUnused */
    protected function get___class(): Class_ {
        global $osm_app; /* @var App $osm_app */

        return $osm_app->classes[$this::class];
    }

    protected static function createInstance(string $className, array $data): static {
        global $osm_app; /* @var App $osm_app */

        $data['__class'] = $class = $osm_app->classes[$className];

        return parent::createInstance($class->generated_name ?? $className, $data);
    }

    public function __sleep(): array {
        $propertyNames = [];

        foreach (get_object_vars($this) as $propertyName => $value) {
            if (isset($this->__class->properties[$propertyName]
                ->attributes[Serialized::class]))
            {
                $propertyNames[] = $propertyName;
            }
        }

        return $propertyNames;
    }
}