<?php

declare(strict_types=1);

namespace Osm\Core\Traits;

trait Observable
{
    protected array $_events = [];

    public function on(string $event, callable $callback): static {
        if (!isset($this->_events[$event])) {
            $this->_events[$event] = [];
        }

        $this->_events[$event][] = $callback;
        return $this;
    }

    protected function fire(string $event, ...$args): void {
        if (!isset($this->_events[$event])) {
            return;
        }

        foreach ($this->_events[$event] as $callback) {
            $callback(...$args);
        }
    }

    protected function fireFunction(string $event, $result, ...$args): mixed {
        if (!isset($this->_events[$event])) {
            return $result;
        }

        foreach ($this->_events[$event] as $callback) {
            $result = $callback($result, ...$args);
        }

        return $result;
    }
}