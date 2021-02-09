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
        if (!$properties) {
            return '';
        }

        return <<<EOT

namespace Hints\\{$this->class->namespace} {
    /**
{$properties}
     */
    class {$this->class->short_name} extends \\{$this->class->name}{
    }
}
EOT;
    }

    protected function propertyHints(): string {
        $output = '';

        foreach ($this->class->dynamic_traits as $trait) {
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
}