<?php

declare(strict_types=1);

namespace Osm\Runtime;

use Osm\Core\App;
use Osm\Runtime\Attributes\Runs;
use Osm\Runtime\Exceptions\Abort;
use Osm\Runtime\Exceptions\AbortTimeout;
use Osm\Runtime\Exceptions\PropertyNotSet;
use Osm\Runtime\Loading\AppLoader;
use function Osm\make_dir;
use function Osm\make_dir_for;

/**
 * Constructor parameters:
 *
 * @property string $app_class_name
 * @property string $env_name
 * @property bool $autoload_dev
 * @property Runtime $runtime
 *
 * Computed:
 *
 * @property string $app_name
 * @property string $project_path The project directory. By default,
 *      assumed that the project is in current directory
 * @property string $generated_path A directory where all the generated files
 *      (serialized application object, classes with applied traits,
 *      and hint classes) are created. By default, {$project_path}/generated
 * @property string $classes_php_path
 * @property string $app_ser_path
 * @property array $locks_config
 * @property Locks $locks
 *
 * Temporary:
 *
 * @property App $app
 */
class Factory extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_app_class_name(): string {
        throw new PropertyNotSet(__METHOD__);
    }

    /** @noinspection PhpUnused */
    protected function get_env_name(): string {
        return 'production';
    }

    /** @noinspection PhpUnused */
    protected function get_project_path(): string {
        return getcwd();
    }

    /** @noinspection PhpUnused */
    protected function get_generated_path(): string {
        return "{$this->project_path}/generated";
    }

    /** @noinspection PhpUnused */
    protected function get_app_name(): string {
        $result = $this->app_class_name;

        if (str_ends_with($result, '\\App')) {
            $result = mb_substr($result, 0,
                mb_strlen($result) - mb_strlen('\\App'));
        }

        return str_replace('\\', '_', $result);
    }

    /** @noinspection PhpUnused */
    protected function get_classes_php_path(): string {
        return "{$this->generated_path}/{$this->app_name}/{$this->env_name}/classes.php";
    }

    /** @noinspection PhpUnused */
    protected function get_app_ser_path(): string {
        return "{$this->generated_path}/{$this->app_name}/{$this->env_name}/app.ser";
    }

    /** @noinspection PhpUnused */
    protected function get_locks(): Locks {
        return Locks::new($this->locks_config ?? [
            'path' => make_dir("{$this->generated_path}/runtime/locks"),
        ]);
    }

    /**
     * Create the application object from the compiled files
     *
     * @param array $parameters
     * @return App
     */
    public function create(array $parameters = []): App {
        /** @noinspection PhpIncludeInspection */
        require_once $this->classes_php_path;

        /* @var App $app */
        $app = unserialize(file_get_contents($this->app_ser_path));

        foreach ($parameters as $property => $value) {
            $app->$property = $value;
        }

        return $app;
    }

    public function lock(int $timeout, callable $callback): void {
        if ($this->locks->compiling->acquire()) {
            // this process has just got an exclusive right to compile
            // the application. No other process will compile while this
            // process does it. Yet, an other process may signal that
            // it's aborting the current compilation process, so inside
            // the callback, there should frequent checks for that using
            // `$osm_factory->abortIfSignaled()`
            try {
                $callback();
            }
            catch (Abort) {
                // some other process has signalled the this compilation
                // process should be aborted, and the callback aborted it
                // using `$osm_factory->abortIfSignaled()`. Just saying -
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
            $timeout = $timeout * 1000; // ms
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
                $callback();
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

    /**
     * Compiles the application
     */
    public function compile(): void {
        $new = "{$this->app_class_name}::new";
        $this->app = $new();

        $this->loadApp();

        // collects module groups and modules that are relevant for this app,
        // in their dependency order
        $this->collectModules();

        // collects all the classes in all the modules
        $this->collectClasses();

        // collects all the dynamic traits, and referenced non-module classes
        $this->collectTraits();

        // generates affected classes with applied dynamic traits
        $this->generateClasses();

        // generates app object, adds the info to it from the runtime objects,
        // and serializes it
        $this->generateApp();
    }

    protected function collectModules() {
//        ModuleGroupSorter::new()->sort();
//        ModuleSorter::new()->sort();
    }

    protected function collectClasses() {
    }

    protected function collectTraits() {
    }

    protected function generateClasses() {
        $output = "<?php\n\n";
        file_put_contents(make_dir_for($this->classes_php_path), $output);
    }

    protected function generateApp() {
        file_put_contents(make_dir_for($this->app_ser_path), serialize($this->app));
    }

    #[Runs(AppLoader::class)]
    protected function loadApp(): void {
         AppLoader::new()->load();
    }

    public function appMatches(array $classNames): bool {
        foreach ($classNames as $className) {
            if (!class_exists($className)) {
                continue;
            }

            if (is_a($this->app, $className)) {
                return true;
            }
        }

        return false;
    }
}