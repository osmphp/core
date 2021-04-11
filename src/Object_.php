<?php

declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Attributes\Serialized;
use Osm\Core\Traits\Reflection;
use Osm\Runtime\Object_ as BaseObject;

class Object_ extends BaseObject
{
    use Reflection;

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
                // force computing the property
                /** @noinspection PhpExpressionResultUnusedInspection */
                $this->{$propertyName};

                $propertyNames[] = $propertyName;
            }
        }

        return $propertyNames;
    }
}