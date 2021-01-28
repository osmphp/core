<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Attributes\Serialized;
use Osm\Core\Attributes\Required;


/**
 * Constructor parameters:
 *
 * @property string $class_name #[Serialized, Required]
 * @property string $name #[Serialized, Required]
 * @property string $path #[Serialized, Required]
 * @property string $package_name #[Serialized, Required]
 *
 * Computed:
 *
 * @property string $namespace #[Serialized, Required]
 */
class ModuleGroup extends Object_
{
    public static string $app_class_name = App::class;

    // 1 means that each direct subdirectory contains a module
    public static int $depth = 1;

    /**
     * @var string[]
     */
    public static array $after = [];

    /** @noinspection PhpUnused */
    protected function get_namespace(): string {
        return mb_substr($this->class_name, 0,
            mb_strrpos($this->class_name, '\\'));
    }
}