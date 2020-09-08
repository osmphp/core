<?php

namespace Osm\Core\ErrorHandling;

use ErrorException;
use Osm\Core\Object_;
use Symfony\Component\ErrorHandler\Error\FatalError;

class ErrorHandler extends Object_
{
    public function __construct($data = []) {
        parent::__construct($data);
        $this->start();
    }

    public function start() {
        error_reporting(-1);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);

        ini_set('display_errors', 'Off');
    }

    public function handleError($level, $message, $file = '', $line = 0, $context = []) {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * @param \Throwable $e
     */
    public function handleException($e) {
        $message = $e->getMessage() . "\n" . $e->getTraceAsString();
        echo "{$message}\n";
        osm_core_log($message, 'exception.log');
    }

    public function handleShutdown() {
        if (!is_null($error = error_get_last()) &&
            in_array($error['type'], [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]))
        {
            $this->handleException(
                new FatalError($error['message'], 0, $error));
        }
    }
}