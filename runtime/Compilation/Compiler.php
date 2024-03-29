<?php

declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Core\Attributes\Runs;
use Osm\Runtime\App;
use Osm\Runtime\Apps;
use Osm\Runtime\Exceptions\Abort;
use Osm\Runtime\Exceptions\AbortTimeout;
use Osm\Core\Exceptions\Required;
use Osm\Runtime\Paths;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use function Osm\make_dir;
use function Osm\make_dir_for;

/**
 * Constructor parameters:
 *
 * @property string $app_class_name
 *
 * Computed:
 *
 * @property Paths $paths
 * @property int $timeout
 * @property string $app_name
 * @property Locks $locks
 * @property CompiledApp $app
 * @property Parser $php_parser
 */
class Compiler extends App
{
    /** @noinspection PhpUnused */
    protected function get_app_class_name(): string {
        throw new Required(__METHOD__);
    }

    /** @noinspection PhpUnused */
    protected function get_paths(): Paths {
        return Apps::paths($this->app_class_name);
    }

    /** @noinspection PhpUnused */
    protected function get_timeout(): int {
        return Apps::$compilation_timeout;
    }

    /** @noinspection PhpUnused */
    protected function get_app_name(): string {
        return $this->paths->app_name;
    }

    /** @noinspection PhpUnused */
    protected function get_locks(): Locks {
        return Locks::new(['path' => make_dir($this->paths->compiler_locks)]);
    }

    /** @noinspection PhpUnused */
    protected function get_app(): CompiledApp {
        return CompiledApp::new();
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
        // generates affected classes with applied dynamic traits
        $this->generateClasses();

        // generates app object, adds the info to it from the runtime objects,
        // and serializes it
        $this->serializeApp();

    }

    public function generateClasses() {
        $output = "<?php\n\n";

        foreach ($this->app->classes as $class) {
            if ($class->generated_name) {
                $output .= $this->generateClass($class);
            }
        }

        file_put_contents(make_dir_for($this->paths->classes_php), $output);

        /** @noinspection PhpIncludeInspection */
        require_once $this->paths->classes_php;

    }

    #[Runs(Generator::class)]
    protected function generateClass(Class_ $class): string {
        return Generator::new(['class' => $class])->generate();
    }

    protected function serializeApp() {
        Apps::run($this->app->serialize(), function($app) {
            file_put_contents(make_dir_for($this->paths->app_ser), serialize($app));
        });
    }

    /** @noinspection PhpUnused */
    protected function get_php_parser(): Parser {
        return (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
    }

    /** @noinspection PhpUnused */
    public function hint() {
        $output = <<<EOT
<?php

/** @noinspection PhpUndefinedClassInspection */
/** @noinspection PhpFullyQualifiedNameUsageInspection */
/** @noinspection PhpInconsistentReturnPointsInspection */


EOT;

        foreach ($this->app->classes as $class) {
            if ($class->generated_name) {
                $output .= $this->generateHint($class);
            }
        }

        file_put_contents(make_dir_for($this->paths->hints_php), $output);
    }

    #[Runs(Hinter::class)]
    protected function generateHint(Class_ $class): string {
        return Hinter::new(['class' => $class])->hint();
    }

}