<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Runtime\Compilation\Properties;

use Osm\Runtime\Compilation\Compiler;
use Osm\Runtime\Compilation\PhpQuery;
use Osm\Runtime\Compilation\Property;
use phpDocumentor\Reflection\DocBlock\Tags\Property as PhpDocProperty;
use Osm\Core\Attributes\Expected;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\ConstExprEvaluationException;
use PhpParser\NodeVisitor\NameResolver;

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
        $this->type = null;
        $this->array = false;
        $this->nullable = false;
        $this->attributes = [];

        $this->parsePhpDocType((string)$this->phpdoc->getType());

        $description = $this->phpdoc->getDescription()?->getBodyTemplate() ?? '';
        /** @noinspection RegExpRedundantEscape */
        $attributes = preg_match('/^(?<attributes>#\[[^\]]+\])/u',
            $description, $match) ? $match['attributes'] : '';

        if ($attributes) {
            $this->parsePhpDocAttributes($attributes);
        }
    }

    protected function parsePhpDocAttributes(string $attributes): void {
        global $osm_app; /* @var Compiler $osm_app */

        $stmt = $osm_app->php_parser->parse(<<<EOT
<?php

namespace {$this->class->namespace};

{$this->class->imports->toString()}

class Test {
    {$attributes}
    public \$test;
}
EOT
        );
        /* @var Node\Stmt\Property $stmt */
        $stmt = PhpQuery::new($stmt)
            ->each(new NameResolver())
            ->findOne(fn(Node $node) => $node instanceof Node\Stmt\Property)
            ->stmts[0];

        $evaluator = new ConstExprEvaluator(function (Expr $expr) {
            if ($expr instanceof Expr\ClassConstFetch) {
                $class = $expr->class->toString();
                $name = $expr->name->toString();

                return $name == 'class'
                    ? $class
                    : (new \ReflectionClass($class))->getConstant($name);
            }

            throw new ConstExprEvaluationException(
                "Expression of type {$expr->getType()} cannot be evaluated"
            );
        });

        $attributes = $this->attributes;
        foreach ($stmt->attrGroups[0]->attrs ?? [] as $node) {
            /* @var Node\Attribute $node */
            $class = $node->name->toString();
            $args = [];
            $namedArgs = [];
            foreach ($node->args ?? [] as $arg) {
                if ($arg->name) {
                    $namedArgs[$arg->name->name] =
                        $evaluator->evaluateSilently($arg->value);
                }
                else {
                    $args[] = $evaluator->evaluateSilently($arg->value);
                }
            }

            if (count($namedArgs)) {
                $constructor = (new \ReflectionClass($class))->getConstructor();

                foreach ($constructor->getParameters() as $index => $parameter) {
                    if ($index < count($args)) {
                        continue;
                    }

                    if (isset($namedArgs[$parameter->getName()])) {
                        $args[] = $namedArgs[$parameter->getName()];
                        continue;
                    }

                    $args[] = $parameter->getDefaultValue();
                }
            }

            try {
                $osm_app->app->addAttribute($attributes, $class, new $class(...$args));
            }
            catch (\Error $e) {
                throw new \Error("{$this->class->name}: {$e->getMessage()}", $e->getCode(), $e);
            }
        }
        $this->attributes = $attributes;
    }
}