<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Attributes\Serialized;

/**
 * Constructor parameters:
 *
 * @property string $class_name #[Serialized]
 * @property string $name #[Serialized]
 * @property string $path #[Serialized]
 * @property string $module_group_class_name
 */
class Module extends Object_
{
    public static ?string $app_class_name = null;

    /**
     * @var string[]
     */
    public static array $requires = [];

    /**
     * @var string[]
     */
    public static array $after = [];

    /**
     * @var string[]
     */
    public static array $traits = [];
}