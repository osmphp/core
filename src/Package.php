<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Attributes\Serialized;
use Osm\Core\Attributes\Required;

/**
 * Constructor parameters:
 *
 * @property string $name #[Serialized, Required]
 * @property string $path #[Serialized, Required]
 */
class Package extends Object_ {
}