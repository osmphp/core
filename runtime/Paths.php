<?php

declare(strict_types=1);

namespace Osm\Runtime;

use Osm\Runtime\Compilation\Compiler;
use Osm\Runtime\Exceptions\Required;

/**
 * Constructor parameters:
 *
 * @property string $app_class_name
 * @property string $project_path
 *
 * Computed:
 *
 * @property string $app_name
 *
 * Computed paths:
 *
 * @property string $generated A directory where all the generated files
 *      (serialized application object, classes with applied traits,
 *      and hint classes) are created. By default, {$project_path}/generated
 * @property string $classes_php
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
    protected function get_project_path(): string {
        throw new Required(__METHOD__);
    }

    /** @noinspection PhpUnused */
    protected function get_app_name(): string {
        $result = $this->app_class_name;

        if (str_ends_with($result, '\\App')) {
            $result = mb_substr($result, 0,
                mb_strlen($result) - mb_strlen('\\App'));
        }

        return str_replace('\\', '_', $result);
    }

    /** @noinspection PhpUnused */
    protected function get_generated(): string {
        return "{$this->project_path}/generated";
    }

    /** @noinspection PhpUnused */
    protected function get_classes_php(): string {
        return "{$this->generated}/{$this->app_name}/classes.php";
    }

    /** @noinspection PhpUnused */
    protected function get_app_ser(): string {
        return "{$this->generated}/{$this->app_name}/app.ser";
    }

    /** @noinspection PhpUnused */
    protected function get_compiler(): string {
        return "{$this->generated}/" . Compiler::class;
    }

    /** @noinspection PhpUnused */
    protected function get_compiler_locks(): string {
        return "{$this->compiler}/locks";
    }
}