<?php

/** @noinspection PhpUnusedAliasInspection */
declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Runtime\Object_;
use Osm\Runtime\Traits\Serializable;
use Osm\Core\Attributes\Expected;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Node;

/**
 * @property Class_ $class #[Expected]
 * @property string $name #[Expected]
 * @property array|object[] $attributes
 * @property \ReflectionMethod $reflection
 * @property Method[] $around
 * @property string $alias
 *
 * @property bool $uses_func_get_args
 * @property string $access
 * @property string $arguments
 * @property string $parameters
 * @property string $returns
 */
class Method extends Object_
{
    use Serializable;

    protected function get_serialized_class_name(): string {
        return \Osm\Core\Method::class;
    }

    /** @noinspection PhpUnused */
    protected function get_around(): array {
        $around = [];

        // walk through class hierarchy , take all dynamic traits implementing
        // the around advice for this method

        for ($class = $this->class; $class; $class = $class->parent_class) {
            $classAdvices = [];

            foreach ($class->dynamic_traits as $trait) {
                if (isset($trait->methods["around_{$this->name}"])) {
                    $classAdvices[] = $trait->methods["around_{$this->name}"];
                }
            }

            $around = array_merge($classAdvices, $around);
        }

        return $around;
    }

    /** @noinspection PhpUnused */
    protected function get_alias(): string {
        return str_replace('\\', '_', $this->class->name) .
            '__' . $this->name;

    }

    /** @noinspection PhpUnused */
    protected function get_uses_func_get_args(): bool {
        $query = (new PhpQuery($this->class->ast))
            ->each(new NameResolver())
            ->find(function($node) {
                if (!($node instanceof Node\Expr\FuncCall)) {
                    return false;
                }

                if (!($node->name instanceof Node\Name)) {
                    return false;
                }

                if ($node->name->toString() != 'func_get_args') {
                    return false;
                }

                if ($node->getAttribute('endLine') < $this->reflection->getStartLine()) {
                    return false;
                }

                if ($node->getAttribute('startLine') > $this->reflection->getEndLine()) {
                    return false;
                }

                return true;
            });

        return count($query->stmts) > 0;
    }

    /** @noinspection PhpUnused */
    protected function get_access(): string {
        return $this->reflection->getModifiers() & \ReflectionMethod::IS_PUBLIC != 0
            ? 'public'
            : 'protected';
    }

    /** @noinspection PhpUnused */
    protected function get_arguments(): string {
        $this->parseParameters();
        return $this->arguments;
    }

    /** @noinspection PhpUnused */
    protected function get_parameters(): string {
        $this->parseParameters();
        return $this->parameters;
    }

    protected function parseParameters(): void {
        global $osm_app; /* @var Compiler $osm_app */

        $params = '';
        $args = '';
        $use = '';

        foreach ($this->reflection->getParameters() as $parameter) {
            /* @var \ReflectionParameter $parameter */
            if ($args) {
                $params .= ', ';
                $args .= ', ';
                $use .= ', ';
            }

            if ($type = $parameter->getType()) {
                $params .= $osm_app->app->parseType($type) . ' ';
            }

            if ($parameter->isPassedByReference()) {
                $params .= '&';
                $use .= '&';
            }

            if ($parameter->isVariadic()) {
                $params .= '...';
                $args .= '...';
                $use .= '...';
            }

            $params .= '$' . $parameter->getName();
            $args .= '$' . $parameter->getName();
            $use .= '$' . $parameter->getName();

            if ($parameter->isDefaultValueAvailable()) {
                $params .= ' = ' . var_export($parameter->getDefaultValue(), true);
            }
        }

        //$this->use_parameters = $use ? "use ($use) " : '';
        $this->parameters = $params;
        $this->arguments = $args;
    }

    /** @noinspection PhpUnused */
    protected function get_returns(): string {
        global $osm_app; /* @var Compiler $osm_app */

        return ($type = $this->reflection->getReturnType())
            ? ': '. $osm_app->app->parseType($type)
            : '';
    }
}