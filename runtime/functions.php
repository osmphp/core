<?php

declare(strict_types=1);

namespace Osm {

    use Osm\Core\App;
    use Osm\Core\Attributes\Name;
    use Osm\Core\Attributes\Serialized;
    use Osm\Core\Class_;
    use Osm\Core\Exceptions\NotSupported;
    use Osm\Runtime\Exceptions\CircularDependency;

    function make_dir($dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        return $dir;
    }

    function make_dir_for($filename) {
        make_dir(dirname($filename));
        return $filename;
    }

    function touch($filename) {
        if (is_file($filename)) {
            return $filename;
        }

        make_dir_for($filename);
        \touch($filename);

        return $filename;
    }

    function merge($target, ...$sources) {
        foreach ($sources as $source) {
            $target = mergeFromSource($target, $source);
        }

        return $target;
    }

    function mergeFromSource($target, $source) {
        if (is_object($target)) {
            return mergeIntoObject($target, $source);
        }
        elseif (is_array($target)) {
            return mergeIntoArray($target, $source);
        }
        else {
            return $source;
        }
    }

    function mergeIntoObject($target, $source) {
        foreach ($source as $key => $value) {
            if (property_exists($target, $key)) {
                $target->$key = merge($target->$key, $value);
            }
            else {
                $target->$key = $value;
            }
        }

        return $target;
    }

    function mergeIntoArray($target, $source) {
        foreach ($source as $key => $value) {
            if (is_numeric($key)) {
                $target[] = $value;
            }
            elseif (isset($target[$key])) {
                $target[$key] = merge($target[$key], $value);
            }
            else {
                $target[$key] = $value;
            }
        }

        return $target;
    }

    /**
     * @param string $baseClassName
     * @return Class_[]
     */
    function get_descendant_classes(string $baseClassName): array {
        global $osm_app; /* @var App $osm_app */

        $classes = [];

        foreach ($osm_app->classes as $class) {
            if (!is_subclass_of($class->name, $baseClassName, true)) {
                continue;
            }

            $classes[] = $class;
        }

        return $classes;
    }

    function get_descendant_classes_by_name(string $baseClassName): array {
        $classNames = [];

        foreach (get_descendant_classes($baseClassName) as $class) {
            /* @var Name $name */
            if ($name = $class->attributes[Name::class] ?? null) {
                $classNames[$name->name] = $class->name;
            }
        }

        return $classNames;
    }

    function handle_errors() {
        ini_set('display_errors', 'Off');
        error_reporting(-1);
        set_error_handler(function($level, $message, $file = '', $line = 0, $context = []) {
            if (error_reporting() & $level) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        });
    }

    function dehydrate(mixed $value): mixed {
        global $osm_app; /* @var App $osm_app */

        if (is_object($value)) {
            $result = new \stdClass();

            $class = $value->__class ?? $osm_app->classes[$value::class] ?? null;
            if ($class) {
                foreach ($class->properties as $property) {
                    if (isset($property->attributes[Serialized::class])) {
                        $dehydrated = dehydrate($value->{$property->name});
                        if ($dehydrated !== null) {
                            $result->{$property->name} = $dehydrated;
                        }
                    }
                }
            }
            else {
                foreach ($value as $key => $item) {
                    $dehydrated = dehydrate($item);
                    if ($dehydrated !== null) {
                        $result->$key = $dehydrated;
                    }
                }
            }

            return $result;
        }

        if (is_array($value)) {
            return array_map(fn($item) => dehydrate($item), $value);
        }
        if ($value instanceof \Traversable) {
            return dehydrate(iterator_to_array($value));
        }

        return $value;
    }

    function hydrate(string $className, mixed $value, bool $array = false) {
        global $osm_app; /* @var App $osm_app */

        if ($value === null) {
            return null;
        }

        if ($array) {
            return array_map(fn($item) => hydrate($className, $item),
                (array)$value);
        }

        if (!is_object($value)) {
            throw new NotSupported("Can't instantiate '{$className}' " .
                "from a non-object value");
        }

        $class = $osm_app->classes[$className];
        $class = $osm_app->classes[
            $class->getTypeClassName($value->type ?? null)];
        $new = "{$class->name}::new";

        $data = (array)$value;

        foreach ($data as $propertyName => &$propertyValue) {
            if (!($property = $class->properties[$propertyName] ?? null)) {
                continue;
            }

            if (!isset($osm_app->classes[$property->type])) {
                continue;
            }

            $propertyValue = hydrate($property->type, $propertyValue,
                $property->array);
        }

        $object = $new($data);
        if (method_exists($object, '__wakeup')) {
            $object->__wakeup();
        }

        return $object;
    }

    /**
     * Sorts the keyed array of items by dependency.
     *
     * @param array $items
     * @param string $pluralTitle
     * @param callable $callback
     *
     * @return array
     */
    function sort_by_dependency(array $items, string $pluralTitle,
        callable $callback): array
    {
        $count = count($items);

        $positions = [];

        for ($position = 0; $position < $count; $position++) {
            $key = findItemWithAlreadyResolvedDependencies($items, $positions);
            if (!$key) {
                throw circularDependency($items, $positions, $pluralTitle);
            }

            $positions[$key] = $position;
        }

        uasort($items, $callback($positions));

        return $items;
    }

    function findItemWithAlreadyResolvedDependencies(array $items,
        array $positions): ?string
    {
        foreach ($items as $key => $item) {
            if (isset($positions[$key])) {
                continue;
            }

            if (hasUnresolvedDependency($item, $items, $positions)) {
                continue;
            }

            return $key;
        }

        return null;
    }

    function hasUnresolvedDependency(object $item, array $items,
        array $positions): bool
    {
        foreach ($item->after as $key) {
            if (!isset($items[$key])) {
                // no such package - don't consider it a dependency
                continue;
            }

            if (!isset($positions[$key])) {
                // dependencies not added to the position array are not
                // resolved yet
                return true;
            }
        }

        return false;
    }

    function circularDependency(array $items, array $positions,
        string $pluralTitle): CircularDependency
    {
        $circular = [];

        foreach ($items as $key => $item) {
            if (!isset($positions[$key])) {
                $circular[] = $key;
            }
        }

        return new CircularDependency(
            sprintf('%s with circular dependencies found: %s',
            $pluralTitle, implode(', ', $circular)));
    }
}
