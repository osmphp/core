<?php

declare(strict_types=1);

use Osm\Runtime\Compilation\Compiler;
use Osm\Runtime\Apps;
use Osm\Core\Samples\App;

/**
 * This is a simple script for manual testing of the Factory::lock() method,
 * which is used to make sure that a) only one compilation runs at the
 * same time and b) whenever a new compilation request arrives, it aborts the
 * currently running one, and only then starts.
 *
 * This script runs indefinitely unless it is signaled to abort. Run
 * the script from the package directory using `php bin/test_locking.php`
 * command.
 *
 * Run these tests in various operating systems.
 *
 * Test that it indeed listens to the abort signal:
 *
 * 1. Run this script in one console.
 * 2. Start it in another console.
 * 3. The first one should stop.
 *
 * Test that it is resilient to unexpected process termination:
 *
 * 1. Run the script.
 * 2. Terminate it using Ctrl+C.
 * 3. Start it again.
 * 4. It should start normally - the lock used by the first run should be
 *    automatically released.
 *
 * Test that while aborting, any further attempts silently quit:
 *
 * 1. In code, temporarily increase `$wait` and `$timeout` to 100s.
 * 2. Run this script in one console.
 * 3. Start it in another console.
 * 4. Start it in the third console.
 * 5. The third script should silently stop.
 * 6. After terminating the first script manually, the second one starts running.
 */

require 'vendor/autoload.php';

Apps::$project_path = dirname(__DIR__);
$compiler = Compiler::new(['app_class_name' => App::class]);

Apps::run($compiler, function(Compiler $compiler) {
    echo "Starting\n";

    $compiler->lock(function(Compiler $compiler) {
        $wait = 100; //ms

        echo "Running\n";
        while (true) {
            $compiler->abortIfSignaled();
            usleep(($wait + random_int(-10, 10)) * 1000);
        }
    });
});

