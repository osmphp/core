<?php

declare(strict_types=1);

namespace Osm\Core\Exceptions;

class AttributeRequired extends \Exception
{
    public function __construct(string $attribute, string $class) {
        parent::__construct("Required attribute '$attribute' not specified for class '$class'");
    }
}