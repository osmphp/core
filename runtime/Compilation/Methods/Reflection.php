<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Runtime\Compilation\Methods;

use Osm\Runtime\Compilation\Compiler;
use Osm\Runtime\Compilation\Method;
use Osm\Core\Attributes\Expected;

/**
 * @property \ReflectionMethod $reflection #[Expected]
 */
class Reflection extends Method
{
    /** @noinspection PhpUnused */
    protected function get_attributes(): array {
        global $osm_app; /* @var Compiler $osm_app */

        $attributes = [];

        foreach ($this->reflection->getAttributes() as $attribute) {
            $osm_app->app->addAttribute($attributes, $attribute->getName(),
                $attribute->newInstance());
        }

        return $attributes;
    }

}