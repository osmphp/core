<?php

declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Runtime\App;
use Osm\Runtime\Exceptions\Abort;
use Osm\Runtime\Exceptions\AbortTimeout;
use Osm\Runtime\Exceptions\Required;
use Osm\Runtime\Paths;
use function Osm\make_dir;

/**
 * Constructor parameters:
 *
 * @property Paths $paths
 * @property int $timeout
 *
 * Computed:
 *
 * @property string $app_class_name
 * @property string $app_name
 * @property Locks $locks
 */
class Compiler extends App
{
    /** @noinspection PhpUnused */
    protected function get_paths(): Paths {
        throw new Required(__METHOD__);
    }

    /** @noinspection PhpUnused */
    protected function get_timeout(): int {
        throw new Required(__METHOD__);
    }

    /** @noinspection PhpUnused */
    protected function get_app_class_name(): string {
        return $this->paths->app_class_name;
    }

    /** @noinspection PhpUnused */
    protected function get_app_name(): string {
        return $this->paths->app_name;
    }

    /** @noinspection PhpUnused */
    protected function get_locks(): Locks {
        return Locks::new(['path' => make_dir($this->paths->compiler_locks)]);
    }

    public function lock(callable $callback) {
        if ($this->locks->compiling->acquire()) {
            // this process has just got an exclusive right to compile
            // the application. No other process will compile while this
            // process does it. Yet, an other process may signal that
            // it's aborting the current compilation process, so inside
            // the callback, there should frequent checks for that using
            // `$osm_compiler->abortIfSignaled()`
            try {
                $callback($this);
            }
            catch (Abort) {
                // some other process has signalled the this compilation
                // process should be aborted, and the callback aborted it
                // using `$osm_compiler->abortIfSignaled()`. Just saying -
                // there is nothing more to do here
            }
            finally {
                $this->locks->compiling->release();
            }
        }
        elseif ($this->locks->aborting->acquire()) {
            // some process is running compilation, and this process
            // has just signaled it to abort. Now this process have
            // to wait until the first process has aborted
            $timeout = $this->timeout * 1000; // ms
            while ($timeout > 0 && !$this->locks->compiling->acquire()) {
                $wait = (100 + random_int(-10, 10)) * 1000; // ms
                $timeout -= $wait;
                usleep($wait);
            }

            if ($timeout <= 0) {
                throw new AbortTimeout();
            }

            // by now, the previous process has aborted, and this process
            // got the exclusive right to compile. Run the callback with
            // the same precautions as in the first if
            try {
                $callback($this);
            }
            catch (Abort) {
            }
            finally {
                $this->locks->compiling->release();
            }
        }
        //else {
            // some process is running compilation, and some other process
            // is trying to abort it and start a new one. This process has
            // nothing left to do but to end
        //}
    }

    public function abortIfSignaled() {
        if (!$this->locks->aborting->acquire()) {
            throw new Abort();
        }

        $this->locks->aborting->release();
    }

    public function compile() {

    }
}