<?php

namespace Osm\Runtime\Compilation;

use Osm\Core\Exceptions\NotSupported;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;

class PhpQuery implements NodeVisitor
{
    protected mixed $processor;
    protected mixed $predicate;
    protected array $found;
    /**
     * @var Node\Stmt[]
     */
    public $stmts;

    public static function new($stmts): static {
        return new static($stmts);
    }

    public function __construct($stmts) {
        $this->stmts = $stmts;
    }

    public function each($processor) {
        $this->clear();

        $traverser = new NodeTraverser();
        if (is_callable($processor)) {
            $this->processor = $processor;
            $traverser->addVisitor($this);
        }
        else {
            $traverser->addVisitor($processor);
        }

        $this->stmts = $traverser->traverse($this->stmts);

        return $this;
    }

    public function find($predicate) {
        $this->clear();

        $traverser = new NodeTraverser();
        $this->predicate = $predicate;
        $traverser->addVisitor($this);

        $this->stmts = $traverser->traverse($this->stmts);

        return new static($this->found);
    }

    public function findOne(callable $predicate) {
        $found = $this->find($predicate);

        if (count($found->stmts) != 1) {
            throw new NotSupported("Single query result expected");
        }

        return $found;
    }

    public function toString() {
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard();

        $result = $prettyPrinter->prettyPrintFile($this->stmts);

        return strpos($result, "<?php\n\n") === 0
            ? substr($result, strlen("<?php\n\n"))
            : $result;
    }

    public function beforeTraverse(array $nodes) {
    }

    public function enterNode(Node $node) {
    }

    public function leaveNode(Node $node) {
        if ($this->predicate) {
            if (call_user_func($this->predicate, $node)) {
                $this->found[] = $node;
            }
        }

        if ($this->processor) {
            return call_user_func($this->processor, $node);
        }
    }

    public function afterTraverse(array $nodes) {
    }

    protected function clear() {
        $this->found = [];
        $this->predicate = null;
        $this->processor = null;
    }
}