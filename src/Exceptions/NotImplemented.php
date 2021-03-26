<?php

namespace Osm\Core\Exceptions;

class NotImplemented extends \Exception
{
    public function __construct(string|object $message = "", int $code = 0,
        \Throwable $previous = null)
    {
        $caller = $this->getTrace()[0];

        if (is_object($message)) {
            $class = $message::class;
            $message = "'{$class}::{$caller['function']}()' not implemented";
        }
        elseif (!$message) {
            $message = "'{$caller['class']}::{$caller['function']}()' not implemented";
        }

        parent::__construct($message, $code, $previous);
    }
}