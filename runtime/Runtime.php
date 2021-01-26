<?php

declare(strict_types=1);

namespace Osm\Runtime;

use Osm\Core\Exceptions\NotSupported;

class Runtime extends Object_
{
    protected static Runtime $instance;

    public static function new(array $data = []): static {
        if (isset(static::$instance)) {
            return static::$instance;
        }

        return static::$instance ?? static::$instance = parent::new($data);
    }

    public function factory(array $data, callable $callback): void {
        global $osm_factory; /* @var Factory $osm_factory */

        if (isset($osm_factory)) {
            throw new NotSupported("Having more than one application " .
                "factories working simultaneously not supported");
        }

        $data['runtime'] = $this;
        $osm_factory = Factory::new($data);
        try {
            $callback($osm_factory);
        }
        finally {
            $osm_factory = null;
        }
    }
}