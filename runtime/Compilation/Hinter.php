<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Core\Attributes\Expected;
use Osm\Runtime\Object_;

/**
 * @property Class_ $class #[Expected]
 */
class Hinter extends Object_
{
    public function hint(): string {
        $properties = $this->propertyHints();
        $methods = $this->methodHints();
        if (!$properties && !$methods) {
            return '';
        }

        return <<<EOT

namespace {$this->class->namespace} {
    /**
{$properties}
     */
    abstract class {$this->class->short_name} {
{$methods}
    }
}
EOT;
    }

    protected function propertyHints(): string {
        $output = '';

        foreach ($this->class->actual_dynamic_traits as $trait) {
            foreach ($trait->properties as $property) {
                $type = $property->type
                    ? (class_exists($property->type)
                        ? '\\' . $property->type
                        : $property->type
                    )
                    : '';

                $output .= <<<EOT
     * @property {$type} \${$property->name}
     * @see \\{$trait->name}::\${$property->name}

EOT;
            }
        }

        return $output;
    }

    protected function methodHints(): string {
        $output = '';

         foreach ($this->class->actual_dynamic_traits as $trait) {
             foreach ($trait->methods as $method) {
//                 if ($method->access != 'public') {
//                     continue;
//                 }

                $output .= <<<EOT
        /* @see \\{$trait->name}::{$method->name}() */
        abstract {$method->access} function {$method->name} ({$method->parameters}){$method->returns}; 

EOT;
             }
         }

        return $output;
    }
}