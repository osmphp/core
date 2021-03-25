<?php

declare(strict_types=1);

namespace Osm\Core\Traits;

use Osm\Core\App;
use Osm\Core\Class_;

/**
 * @property Class_ $__class
 */
trait Reflection
{
    /** @noinspection PhpUnused */
    protected function get___class(): Class_ {
        global $osm_app; /* @var App $osm_app */

        $className = $this::class;
        if (str_starts_with($className, $osm_app->name)) {
            $className = substr($className, strlen($osm_app->name) + 1);
        }

        return $osm_app->classes[$className];
    }
}