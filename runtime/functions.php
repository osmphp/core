<?php

declare(strict_types=1);

namespace Osm {

    use Osm\Core\App;
    use Osm\Core\Class_;

    function make_dir($dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
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
        $classes = [];

        foreach (get_descendant_classes($baseClassName) as $class) {
            $className = $class->name;

            /** @noinspection PhpUndefinedFieldInspection */
            $classes[$className::$name] = $className;
        }

        return $classes;
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
}

