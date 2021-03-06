<?php

declare(strict_types=1);

namespace Osm\Runtime;

use Osm\Runtime\App as RuntimeApp;
use Osm\Core\App as CoreApp;
use Osm\Core\Attributes\Runs;
use Osm\Runtime\Compilation\Compiler;

final class Apps
{
    public static string $project_path;
    public static int $compilation_timeout = 5000; // ms
    public static string $paths_class_name = Paths::class;
    private static array $apps = [];

    public static function enter(RuntimeApp|CoreApp $app): void {
        global $osm_app; /* @var RuntimeApp|CoreApp $osm_app */

        if ($osm_app) {
            array_push(self::$apps, $osm_app);
        }
        $osm_app = $app;
    }

    public static function leave(): void {
        global $osm_app; /* @var App $osm_app */

        $osm_app = count(self::$apps) ? array_pop(self::$apps) : null;
    }

    public static function run(RuntimeApp|CoreApp $app, callable $callback): mixed {
        return self::switch($app, function(RuntimeApp|CoreApp $app) use ($callback ) {
            $app->boot();
            try {
                return $callback($app);
            }
            finally {
                $app->terminate();
            }
        });
    }

    public static function switch(RuntimeApp|CoreApp $app, callable $callback): mixed {
        self::enter($app);
        try {
            return $callback($app);
        }
        finally {
            self::leave();
        }
    }

    public static function create(string $appClassName): CoreApp {
        $appClassName = self::appClassName($appClassName);
        $paths = self::paths($appClassName);

        /** @noinspection PhpIncludeInspection */
        require_once $paths->classes_php;

        /* @var App $app */
        return unserialize(file_get_contents($paths->app_ser));
    }

    #[Runs(Compiler::class)]
    public static function compile(string $appClassName): void {
        $appClassName = self::appClassName($appClassName);
        $compiler = Compiler::new(['app_class_name' => $appClassName]);

        self::run($compiler, function(Compiler $compiler) {
            $compiler->lock(function(Compiler $compiler) {
                $compiler->compile();
            });
        });
    }

    public static function hint(string $appClassName) {
        $appClassName = self::appClassName($appClassName);
        $compiler = Compiler::new(['app_class_name' => $appClassName]);

        self::run($compiler, function(Compiler $compiler) {
            $compiler->hint();
        });
    }

    public static function paths(string $appClassName): Paths {
        $new = self::$paths_class_name . "::new";

        return $new(['app_class_name' => $appClassName]);
    }

    protected static function appClassName(string $appClassName): string {
        if (class_exists($appClassName)) {
            return $appClassName;
        }

        $appClassName = str_replace('_', '\\', $appClassName);
        if (class_exists($appClassName)) {
            return $appClassName;
        }

        return "{$appClassName}\\App";
    }

}