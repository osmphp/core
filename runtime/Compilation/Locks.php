<?php

declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Runtime\Exceptions\Required;
use Osm\Runtime\Object_;
use Symfony\Component\Lock\BlockingStoreInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\FlockStore;

/**
 * Constructor parameters:
 *
 * @property string $path
 *
 * Computed:
 *
 * @property BlockingStoreInterface $store
 * @property LockFactory $factory
 *
 * @property LockInterface $compiling
 * @property LockInterface $aborting
 */
class Locks extends Object_
{
    protected array $items = [];

    /** @noinspection PhpUnused */
    protected function get_path(): string {
        throw new Required(__METHOD__);
    }

    /** @noinspection PhpUnused */
    protected function get_store(): BlockingStoreInterface {
        return new FlockStore($this->path);
    }

    /** @noinspection PhpUnused */
    protected function get_factory(): LockFactory {
        return new LockFactory($this->store);
    }

    public function get(string $lockName): LockInterface {
        return $this->items[$lockName]
            ?? $this->items[$lockName] = $this->factory->createLock($lockName);
    }

    /** @noinspection PhpUnused */
    protected function get_compiling(): LockInterface {
        global $osm_app; /* @var Compiler $osm_app */

        return $this->get("compiling_{$osm_app->app_name}");
    }

    /** @noinspection PhpUnused */
    protected function get_aborting(): LockInterface {
        global $osm_app; /* @var Compiler $osm_app */

        return $this->get("aborting_{$osm_app->app_name}");
    }
}