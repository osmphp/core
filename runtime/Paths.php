<?php

declare(strict_types=1);

namespace Osm\Runtime;

use Osm\Core\Exceptions\Required;
use Osm\Runtime\Compilation\Compiler;

/**
 * Constructor parameters:
 *
 * @property string $app_class_name
 *
 * Computed:
 *
 * @property string $project
 * @property string $app_name
 *
 * Computed paths:
 *
 * @property string $generated A directory where all the generated files
 *      (serialized application object, classes with applied traits,
 *      and hint classes) are created. By default, {$project_path}/generated
 * @property string $classes_php
 * @property string $hints_php
 * @property string $app_ser
 * @property string $compiler
 * @property string $compiler_locks
 */
class Paths extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_app_class_name(): string {
        throw new Required(__METHOD__);
    }

    /** @noinspection PhpUnused */
    protected function get_project(): string {
        return Apps::$project_path
            ?? dirname(dirname(dirname(__DIR__)));
    }

    /** @noinspection PhpUnused */
    protected function get_app_name(): string {
        return $this->className($this->app_class_name, '\\App');
    }

    /** @noinspection PhpUnused */
    protected function get_generated(): string {
        return "{$this->project}/generated";
    }

    /** @noinspection PhpUnused */
    protected function get_classes_php(): string {
        return "{$this->generated}/{$this->app_name}/classes.php";
    }

    /** @noinspection PhpUnused */
    protected function get_hints_php(): string {
        return "{$this->generated}/hints.php";
    }

    /** @noinspection PhpUnused */
    protected function get_app_ser(): string {
        return "{$this->generated}/{$this->app_name}/app.ser";
    }

    /** @noinspection PhpUnused */
    protected function get_compiler(): string {
        return "{$this->generated}/" . $this->className(Compiler::class);
    }

    /** @noinspection PhpUnused */
    protected function get_compiler_locks(): string {
        return "{$this->compiler}/locks";
    }

    public function className(string $className, string $suffix = null): string {
        if ($suffix && str_ends_with($className, $suffix)) {
            $className = mb_substr($className, 0,
                mb_strlen($className) - mb_strlen($suffix));
        }

        return mb_strpos($className, '\\') !== false
            ? str_replace('\\', '_', $className)
            : $className . '_';
    }
}