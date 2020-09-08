<?php

namespace Osm\Core;

class Promise
{
    public $object;
    public $method;
    public $args;

    public function __construct($object, $method, $args) {
        $this->method = $method;
        $this->args = $args;
        $this->object = $object;
    }

    public function get($method = null) {
        global $osm_app; /* @var App $osm_app */

        $object = $this->object ? $osm_app->{$this->object} : $osm_app;
        if (!$method) {
            $method = $this->method;
        }

        return call_user_func_array([$object, $method], $this->args);
    }

    /**
     * @return string
     */
    public function __toString() {
        global $osm_app; /* @var App $osm_app */

        try {
            return $this->get();
        }
        catch (\Throwable $e) {
            if (!$osm_app->pending_exception) {
                $osm_app->pending_exception = $e;
            }
            return '';
        }
    }

    public function toArray() {
        return osm_array($this->get());
    }

    public function toObject() {
        return osm_object($this->get());
    }
}