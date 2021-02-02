<?php

/** @noinspection PhpUnusedAliasInspection */
namespace Osm\Runtime\Compilation;

use Osm\Core\Attributes\Expected;
use Osm\Runtime\Object_;

/**
 * @property Class_ $class #[Expected]
 */
class Generator extends Object_
{
    const WIDTH = 120;

    public function generate(): string {
       return <<<EOT

namespace {$this->class->generated_namespace} {
    class {$this->class->short_name} extends \\{$this->class->name}{
{$this->generateUseStatements()}
{$this->generateMethods()}
    }
}
EOT;
    }

    protected function generateUseStatements(): string {
        $output = '';
        $length = 8;

        foreach ($this->class->dynamic_traits as $trait) {
            if (!$output) {
                $output .= '        use ';
                $length += strlen('use ');
            }
            else {
                $output .= ', ';
                $length += strlen(', ');
                if ($length + strlen($trait->name) >= static::WIDTH - strlen(', ')) {
                    $output .= "\n            ";
                    $length = 12;
                }
            }
            $output .= "\\" . $trait->name;
            $length += strlen("\\" . $trait->name);
        }

        $output .= $this->generateAliases();

        return $output;
    }

    protected function generateAliases(): string {
        $output = '';

        /* @var string[] $defaultClasses */
        $defaultClasses = [];
        foreach ($this->class->methods as $method) {
            foreach ($method->around as $around) {
                if (isset($defaultClasses[$around->name])) {
                    $defaultClassName = $defaultClasses[$around->name];
                    $output .= "\n            \\{$defaultClassName}::{$around->name} insteadof \\{$around->class->name};";
                }
                else {
                    $defaultClasses[$around->name] = $around->class->name;
                }
            }
        }

        foreach ($this->class->methods as $method) {
            foreach ($method->around as $around) {
                $output .= "\n            \\{$around->class->name}::{$around->name} as {$around->alias};";
            }
        }

        return $output ?
            "\n        {{$output}\n        }"
            : ';';
    }

    protected function generateMethods(): string {
        $output = '';

        foreach ($this->class->methods as $method) {
            if (empty($method->around)) {
                continue;
            }

            if ($method->uses_func_get_args) {
                $output .= "\n\n        protected function __parent_{$method->name} (...\$args){$method->returns} {" .
                    "\n            return parent::{$method->name}(...\$args);\n        }" .
                    "\n\n        {$method->access} function {$method->name} ({$method->parameters}){$method->returns} {" .
                    "\n            \$args = func_get_args();" .
                    "{$this->generateOpenParameterListTraitCall($method, 0)}\n        }";
            }
            else {
                $output .= "\n\n        protected function __parent_{$method->name} ({$method->parameters}){$method->returns} {" .
                    "\n            return parent::{$method->name}({$method->arguments});\n        }" .
                    "\n\n        {$method->access} function {$method->name} ({$method->parameters}){$method->returns} {" .
                    "{$this->generateTraitCall($method, 0)}\n        }";
            }
        }

        return $output;
    }

    protected function generateTraitCall(Method $method, int $adviceIndex): string {
        $indent = str_repeat(' ', ($adviceIndex + 3) * 4);

        if ($adviceIndex >= count($method->around)) {
            return "\n{$indent}return \$this->__parent_{$method->name}({$method->arguments});";
        }

        $around = $method->around[count($method->around) - $adviceIndex - 1];
        $comma = $method->arguments ? ', ' : '';

        $output = "\n{$indent}return \$this->{$around->alias}(function({$method->parameters}) {";
        $output .= $this->generateTraitCall($method, $adviceIndex + 1);
        $output .= "\n{$indent}}{$comma}{$method->arguments});";

        return $output;
    }

    /**
     * @param Method $method
     * @param int $adviceIndex
     * @return string
     */
    protected function generateOpenParameterListTraitCall(Method $method,
        int $adviceIndex): string
    {
        $indent = str_repeat(' ', ($adviceIndex + 3) * 4);

        if ($adviceIndex >= count($method->around)) {
            return "\n{$indent}return \$this->__parent_{$method->name}(...\$args);";
        }

        $around = $method->around[count($method->around) - $adviceIndex - 1];
        $comma = $method->arguments ? ', ' : '';

        $output = "\n{$indent}return \$this->{$around->alias} (function({$method->parameters}) use (\$args){";
        $output .= $this->generateOpenParameterListTraitCall($method, $adviceIndex + 1);
        $output .= "\n{$indent}}{$comma}{$method->arguments});";

        return $output;
    }
}