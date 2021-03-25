<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Attributes\Serialized;

/**
 * @property string $class_name #[Serialized]
 * @property string $name #[Serialized]
 * @property string $path #[Serialized]
 * @property string $package_name #[Serialized]
 * @property string $namespace #[Serialized]
 */
class BaseModule extends Object_
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

    /**
     * @var string[]
     */
    public static array $classes = [];

    public function boot(): void {
    }

    public function terminate(): void {
    }
}