<?php

declare(strict_types=1);

namespace Osm\Core;

use Osm\Core\Exceptions\UndefinedArrayKey;

class Array_ implements \ArrayAccess, \IteratorAggregate
{
    public function __construct(
        public array $items,
        protected string|\Closure $message)
    {
    }

    #region ArrayAccess
    public function offsetExists($offset) {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset) {
        try {
            return $this->items[$offset];
        }
        catch (\Throwable $e) {
            $message = $this->message;
            $message = is_callable($message)
                ? $message($offset)
                : str_replace(':key', $offset, $message);

            throw new UndefinedArrayKey($message, 0, $e);
        }
    }

    public function offsetSet($offset, $value) {
        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->items[$offset]);
    }
    #endregion

    #region IteratorAggregate
    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator($this->items);
    }
    #endregion

    public function map(callable $callback): static {
        return new static(array_map($callback, $this->items), $this->message);
    }
}