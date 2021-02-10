<?php

declare(strict_types=1);

namespace Osm\Core;

use Osm\Runtime\Apps;

/**
 * Constructor parameters:
 *
 * @property string $project The project directory
 * @property string $generated A directory where all the generated files
 *      (serialized application object, classes with applied traits,
 *      and hint classes) are created. By default, {$project_path}/generated
 */
class Paths extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_project(): string {
        $this->initialize();
        return $this->project;
    }

    /** @noinspection PhpUnused */
    protected function get_generated(): string {
        $this->initialize();
        return $this->generated;
    }

    protected function initialize() {
        global $osm_app; /* @var App $osm_app */

        $paths = Apps::paths($osm_app->class_name);
        $this->project = $paths->project;
        $this->generated = $paths->generated;
    }
}