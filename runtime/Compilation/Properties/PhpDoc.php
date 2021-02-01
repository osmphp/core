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

        $evaluator = new ConstExprEvaluator();

        $attributes = $this->attributes;
        foreach ($stmt->attrGroups[0]->attrs ?? [] as $node) {
            /* @var Node\Attribute $node */
            $class = $node->name->toString();
            $args = [];
            foreach ($node->args ?? [] as $arg) {
                $args[] = $evaluator->evaluateSilently($arg->value);
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