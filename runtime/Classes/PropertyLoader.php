<?php

declare(strict_types=1);

namespace Osm\Runtime\Classes;

use Osm\Runtime\Object_;

/**
 * Constructor parameters:
 *
 * @property Class_ $class
 */
class PropertyLoader extends Object_
{
    protected function get_comment(): ?string {
        return null;
    }

    public function load() {
        if ($this->class->properties) {
            // the loader has already run on this class
            return;
        }

        $this->class->properties = [];
        $this->parseComments();
    }

    protected function parseComments() {

    }
}