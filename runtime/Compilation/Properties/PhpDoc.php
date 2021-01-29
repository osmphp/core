<?php

/** @noinspection PhpUnusedAliasInspection */
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
use Osm\Core\Attributes\Expected;

/**
 * @property PhpDocProperty $phpdoc #[Expected]
 */
class PhpDoc extends Property
{
    /** @noinspection PhpUnused */
    protected function get_type(): ?string {
        $this->parse();
        return $this->type;
    }

    /** @noinspection PhpUnused */
    protected function get_array(): bool {
        $this->parse();
        return $this->array;
    }

    /** @noinspection PhpUnused */
    protected function get_nullable(): bool {
        $this->parse();
        return $this->nullable;
    }

    /** @noinspection PhpUnused */
    protected function get_attributes(): array {
        $this->parse();
        return $this->attributes;
    }

    protected function parse(): void {
        global $osm_app; /* @var Compiler $osm_app */

        $this->type = null;
        $this->array = false;
        $this->nullable = false;
        $this->attributes = [];

        foreach (explode('|', (string)$this->phpdoc->getType()) as $type) {
            $type = trim($type);

            if (!$type || $type == 'mixed') {
                continue;
            }

            if (str_starts_with($type, '?')) {
                $this->nullable = true;
                $type = substr($type, strlen('?'));
            }

            if ($type == 'array') {
                $this->array = true;
                continue;
            }

            if (str_ends_with($type, '[]')) {
                $this->array = true;
                $type = substr($type, 0, strlen($type) - strlen('[]'));
            }

            $this->type = $type;
            break;
        }

        $description = $this->phpdoc->getDescription()?->getBodyTemplate() ?? '';
        $attributes = preg_match('/^(?<attributes>#\[[^\]]+\])/u',
            $description, $match) ? $match['attributes'] : '';

        $stmt = $osm_app->php_parser->parse(<<<EOT
<?php

{$this->class->imports}

class Test {
    {$attributes}
    public {$this->type} \$test;
}
EOT
);
        /* @var Node\Stmt\Property $stmt */
        $stmt = PhpQuery::new($stmt)
            ->each(new NameResolver())
            ->findOne(fn(Node $node) => $node instanceof Node\Stmt\Property)
            ->stmts[0];

        $this->type = $stmt->type->toString();

        $evaluator = new ConstExprEvaluator();

        foreach ($stmt->attrGroups[0]->attrs ?? [] as $node) {
            /* @var Attribute $node */
            $class = $node->name->toString();
            $args = [];
            foreach ($node->args ?? [] as $arg) {
                $args[] = $evaluator->evaluateSilently($arg->value);
            }

            $this->attributes[$class] = new $class(...$args);
        }
    }
}