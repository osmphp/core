<?php

declare(strict_types=1);

namespace Osm {

    use Osm\Core\App;
    use Osm\Core\Attributes\Name;
    use Osm\Core\Attributes\Serialized;
    use Osm\Core\Class_;

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
                        $result->{$property->name} =
                            dehydrate($value->{$property->name});
                    }
                }
            }
            else {
                foreach ($value as $key => $item) {
                    $result->$key = dehydrate($item);
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
}
