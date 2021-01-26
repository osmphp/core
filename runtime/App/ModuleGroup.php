<?php

declare(strict_types=1);

namespace Osm\Runtime\App;

use Osm\App\App as CoreApp;
use Osm\Object_;

/**
 * Constructor parameters:
 *
 * @property string $class_name
 * @property string $path
 * @property string $package_name
 *
 * Computed:
 *
 * @property string $namespace
 * @property string $upgrade_to_class_name
 */
class ModuleGroup extends Object_
{
    /**
     * @var string[]
     */
    public array $app_class_names = [CoreApp::class];

    // 1 means that each direct subdirectory contains a module
    public int $depth = 1;

    /**
     * @var string[]
     */
    public array $after = [];

    /** @noinspection PhpUnused */
    protected function get_namespace(): string {
        return mb_substr($this->class_name, 0,
            mb_strrpos($this->class_name, '\\'));
    }
}