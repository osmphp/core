<?php

declare(strict_types=1);

namespace Osm\Runtime\Compilation\Properties;

use Osm\Runtime\Compilation\Compiler;
use Osm\Runtime\Compilation\PhpQuery;
use Osm\Runtime\Compilation\Property;
use phpDocumentor\Reflection\DocBlock\Tags\Property as PhpDocProperty;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\NodeVisitor\NameResolver;

/**
 * @property PhpDocProperty $phpdoc
 */
class PhpDoc extends Property
{
    /** @noinspection PhpUnused */
    protected function get_type(): string {
        return (string)$this->phpdoc->getType();
    }

    /** @noinspection PhpUnused */
    protected function get_array(): bool {
        return false;
    }

    /** @noinspection PhpUnused */
    protected function get_nullable(): bool {
        return true;
    }

    /** @noinspection PhpUnused */
    protected function get_attributes(): array {
        global $osm_app; /* @var Compiler $osm_app */

        if (!($description = $this->phpdoc->getDescription())) {
            return [];
        }

        if (!preg_match('/^(?<attributes>#\[[^\]]+\])/u',
            $description->getBodyTemplate(), $match))
        {
            return [];
        }

        $ast = $osm_app->php_parser->parse(<<<EOT
<?php

{$this->class->imports}

class Test {
    {$match['attributes']}
    public \$test;
}
EOT
);
        $attributes = [];

        $ast = PhpQuery::new($ast)
            ->each(new NameResolver())
            ->find(fn(Node $node) => $node instanceof Attribute)
            ->stmts;

        $evaluator = new ConstExprEvaluator();

        foreach ($ast as $node) {
            /* @var Attribute $node */
            $class = $node->name->toString();
            $args = [];
            foreach ($node->args ?? [] as $arg) {
                $args[] = $evaluator->evaluateSilently($arg->value);
            }

            $attributes[$class] = new $class(...$args);
        }

        return $attributes;
    }
}