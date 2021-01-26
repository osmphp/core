<?php

declare(strict_types=1);

namespace Osm\Runtime\Classes;

use Osm\Runtime\Object_;

/**
 * Constructor parameters:
 *
 * @property Class_ $class
 */
class PropertyLoader extends Object_
{
    public function load() {
        if ($this->class->properties) {
            return;
        }

        $this->class->properties = [];
        $this->loadActualProperties();
        $this->loadDocCommentProperties();
        $this->mergeParentProperties();
    }

    protected function loadActualProperties(): void {
        $reflections = $this->class->reflection->getProperties(
            \ReflectionProperty::IS_PUBLIC);

        foreach ($reflections as $reflection) {
            if ($reflection->isStatic()) {
                continue;
            }

            if ($reflection->getDeclaringClass() != $this->class->reflection) {
                continue;
            }

            $property = $this->class->properties[$reflection->getName()] ??
                $this->class->properties[$reflection->getName()] = Property::new([
                    'name' => $reflection->getName(),
                    'class' => $this->class,
                ]);

            $type = $this->parseReflectionType($property, $reflection->getType());
            $attributes = [];

            if ($comment = $reflection->getDocComment()) {
                foreach (explode("\n", $comment) as $line) {
                    if (preg_match(static::PROPERTY_TYPE_PATTERN, $line, $matches)) {
                        $type = $this->getPropertyType($matches['type'], $array);
                        if ($array) $attributes['array'] = true;
                    }
                    else {
                        $attributes = array_merge($attributes, $this->getAttributes($line));
                    }
                }
            }

            $this->addProperty($class, $name, $type, $attributes);
        }
    }

    protected function loadDocCommentProperties(): void {
    }

    protected function mergeParentProperties(): void {
    }

    protected function parseReflectionType(Property $property, ?\ReflectionType $type) {
        if (!$type) {
            $property->type = 'mixed';
            return;
        }

        if ($type instanceof \ReflectionUnionType) {
            //
            $this->parseReflectionType($property, $type->getTypes()[0]);
        }
    }
}