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
 * @property string $module_group_class_name
 */
class Module extends Object_
{
    public static string $app_class_name = App::class;

    /**
     * @var string[]
     */
    public static array $after = [];

    /**
     * @var string[]
     */
    public static array $traits = [];
}