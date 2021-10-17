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

        if (str_starts_with($className, "{$osm_app->name}\\")) {
            $className = mb_substr($className, mb_strlen($osm_app->name) + 1);
        }

        $data['__class'] = $class = $osm_app->classes[$className];

        return parent::createInstance($class->generated_name ?? $className, $data);
    }

    public function __sleep(): array {
        $propertyNames = [];

        foreach ($this->__class->properties as $property) {
            if (!isset($property->attributes[Serialized::class])) {
                continue;
            }

            /** @noinspection PhpExpressionResultUnusedInspection */
            $this->{$property->name};

            $propertyNames[] = $property->name;
        }

        return $propertyNames;
    }
}