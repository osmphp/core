<?php

declare(strict_types=1);

namespace Osm\Runtime\Traits;

use Osm\Core\Attributes\Serialized;
use Osm\Core\Exceptions\Required;
use Osm\Runtime\Compilation\Compiler;

/**
 * @property string $serialized_class_name
 */
trait Serializable
{
    protected function get_serialized_class_name(): string {
        throw new Required(__METHOD__);
    }

    public function serialize() {
        global $osm_app; /* @var Compiler $osm_app */

        $className = $this->serialized_class_name;

        $class = $osm_app->app->classes[$className];
        $className = $class->generated_name ?? $className;

        $data = [];

        foreach ($class->properties as $property) {
            if (!isset($property->attributes[Serialized::class])) {
                continue;
            }

            if (($value = $this->{$property->name}) === null) {
                continue;
            }

            if ($value instanceof static) {
                $value = $value->serialize();
            }
            elseif (is_array($value)) {
                foreach ($value as $key => &$item) {
                    if ($item instanceof static) {
                        $item = $item->serialize();
                    }
                }
            }

            $data[$property->name] = $value;
        }

        return new $className($data);
    }
}