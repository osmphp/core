<?php

declare(strict_types=1);

namespace Osm\Runtime\Exceptions;

class Required extends \Exception
{
    public function __construct(string $method) {
        $property = str_replace('::get_', '::$', $method);
        parent::__construct("Required property '$property' not set");
    }
}