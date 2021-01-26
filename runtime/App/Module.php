<?php

declare(strict_types=1);

namespace Osm\Runtime\App;

use Osm\App\App as CoreApp;
use Osm\Object_;

/**
 * Constructor parameters:
 *
 * @property string $path
 * @property string $module_group_class_name
 * @property string $upgrade_to_class_name
 *
 * Computed:
 *
 * @property string $class_name
 */
class Module extends Object_
{
    /**
     * @var string[]
     */
    public array $app_class_names = [CoreApp::class];

    /**
     * @var string[]
     */
    public array $after = [];

    /**
     * @var string[]
     */
    public array $traits = [];

    /** @noinspection PhpUnused */
    protected function get_class_name(): string {
        return $this->upgrade_to_class_name;
    }
}