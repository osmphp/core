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
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset): mixed {
        try {
            return $this->items[$offset];
        }
        catch (\Throwable $e) {
            $message = $this->message;
            $message = is_callable($message)
                ? $message($offset)
                : str_replace(':key', (string)$offset, $message);

            throw new UndefinedArrayKey($message, 0, $e);
        }
    }

    public function offsetSet($offset, $value): void {
        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset): void {
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