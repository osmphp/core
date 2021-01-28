<?php

declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Core\App;
use Osm\Runtime\Hints\ComposerLock;
use Osm\Runtime\Hints\PackageHint;
use Osm\Runtime\Object_;
use Osm\Runtime\Traits\Serializable;

/**
 * Contains data about the application being compiled
 *
 * Computed:
 *
 * @property string $class_name
 * @property string $name
 * @property \stdClass|PackageHint $composer_json
 * @property \stdClass|ComposerLock $composer_lock
 * @property bool $load_dev_sections
 * @property Package[] $unsorted_packages
 * @property ModuleGroup[] $unsorted_module_groups
 * @property Module[] $unsorted_modules
 */
class CompiledApp extends Object_
{
    use Serializable;

    /** @noinspection PhpUnused */
    protected function get_serialized_class_name(): string {
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_app->app_class_name;
    }

    /** @noinspection PhpUnused */
    protected function get_class_name(): string {
        return $this->serialized_class_name;
    }

    /** @noinspection PhpUnused */
    protected function get_name(): string {
        global $osm_app; /* @var Compiler $osm_app */

        return $osm_app->app_name;
    }

    /** @noinspection PhpUnused */
    protected function get_composer_json(): \stdClass {
        global $osm_app; /* @var Compiler $osm_app */

        return json_decode(file_get_contents(
            "{$osm_app->paths->project}/composer.json"));
    }

    /** @noinspection PhpUnused */
    protected function get_composer_lock(): \stdClass {
        global $osm_app; /* @var Compiler $osm_app */

        return json_decode(file_get_contents(
            "{$osm_app->paths->project}/composer.lock"));
    }

    /** @noinspection PhpUnused */
    protected function get_load_dev_sections(): bool {
        $class = $this->class_name; /* @var App $class */

        return $class::$load_dev_sections;
    }

    /** @noinspection PhpUnused */
    protected function get_unsorted_packages(): array {
        $packages = [];

        $this->loadPackages($packages, 'packages');

        if ($this->load_dev_sections) {
            $this->loadPackages($packages, 'packages-dev');
        }

        $packages[$this->composer_json->name] =
            $this->loadPackage($this->composer_json, root: true);

        return $packages;
    }

    protected function loadPackages(array &$packages, string $section): void {
        foreach ($this->composer_lock->$section as $package) {
            /* @var PackageHint $package */
            $packages[$package->name] = $this->loadPackage($package);
        }
    }

    protected function loadPackage(\stdClass|PackageHint $json, bool $root = false): Package {
        return Package::new([
            'name' => $json->name,
            'path' => $root ? '' : "vendor/{$json->name}",
            'json' => $json,
            'after' => array_merge(
                array_keys((array)($json->require ?? [])),
                $this->load_dev_sections
                    ? array_keys((array)($json->{'require-dev'} ?? []))
                    : [],
            ),
        ]);
    }

    /** @noinspection PhpUnused */
    protected function get_unsorted_module_groups(): array {
        $moduleGroups = [];

        foreach ($this->unsorted_packages as $package) {
            $this->loadModuleGroups($moduleGroups, $package, 'autoload');

            if ($this->load_dev_sections) {
                $this->loadModuleGroups($moduleGroups, $package, 'autoload-dev');
            }
        }

        return $moduleGroups;
    }

    protected function loadModuleGroups(array &$moduleGroups, Package $package,
        string $section): void
    {
        foreach ($package->json->$section->{"psr-4"} ?? [] as $namespace => $path) {
            if ($package->path) {
                $path = "{$package->path}/{$path}";
            }

            $this->loadModuleGroup($moduleGroups, $package, $namespace, $path);
        }
    }

    protected function loadModuleGroup(array &$moduleGroups,
        Package $package, string $namespace, string $path): void
    {
        global $osm_app; /* @var Compiler $osm_app */

        $absolutePath = rtrim("{$osm_app->paths->project}/{$path}", "/\\");
        $filename = "{$absolutePath}/ModuleGroup.php";
        $className = "{$namespace}ModuleGroup";

        if (!is_file($filename)) {
            return; // there is no ModuleGroup class
        }

        if (!is_a($className, \Osm\Core\ModuleGroup::class, true)) {
            return;
        }

        if (!$this->matches($className)) {
            return;
        }

        $moduleGroups[$className] = ModuleGroup::new([
            'package_name' => $package->name,
            'class_name' => $className,
            'name' => $osm_app->paths->className($className, '\\ModuleGroup'),
            'path' => rtrim($path, "/\\"),
            'depth' => $className::$depth,
            'after' => $className::$after,
        ]);
    }

    /** @noinspection PhpUnused */
    protected function get_unsorted_modules(): array {
        $modules = [];

        foreach ($this->unsorted_module_groups as $moduleGroup) {
            $this->loadModules($modules, $moduleGroup);
        }

        return $modules;
    }


    protected function loadModules(array &$modules, ModuleGroup $moduleGroup): void {
        global $osm_app; /* @var Compiler $osm_app */

        $pattern = "{$osm_app->paths->project}/{$moduleGroup->path}" .
            str_repeat('/*', $moduleGroup->depth);

        foreach (glob($pattern, GLOB_ONLYDIR | GLOB_MARK) as $path) {
            $path = ltrim(mb_substr($path, mb_strlen($osm_app->paths->project)),
                "/\\");
            $namespace = $moduleGroup->namespace . '\\' .
                str_replace('/', '\\', ltrim(
                    mb_substr($path, mb_strlen($moduleGroup->path)),
                    "/\\"));

            $this->loadModule($modules, $moduleGroup, $namespace, $path);
        }
    }

    protected function loadModule(array &$modules, ModuleGroup $moduleGroup,
        string $namespace, string $path): void
    {
        global $osm_app; /* @var Compiler $osm_app */

        $absolutePath = rtrim("{$osm_app->paths->project}/{$path}", "/\\");
        $filename = "{$absolutePath}/Module.php";
        $className = "{$namespace}Module";

        if (!is_file($filename)) {
            return; // there is no Module class
        }

        if (!is_a($className, \Osm\Core\Module::class, true)) {
            return;
        }

        if (!$this->matches($className)) {
            return;
        }

        $modules[$className] = Module::new([
            'module_group_class_name' => $moduleGroup->class_name,
            'class_name' => $className,
            'name' => $osm_app->paths->className($className, '\\Module'),
            'path' => rtrim($path, "/\\"),
            'after' => $className::$after,
            'traits' => $className::$traits,
        ]);
    }

    /**
     * @param string|\Osm\Core\ModuleGroup|\Osm\Core\Module $class
     * @return bool
     * @noinspection PhpDocSignatureInspection
     */
    protected function matches(string $class): bool {
        $appClassName = $class::$app_class_name;

        if (!class_exists($appClassName)) {
            return false;
        }

        return is_a($this->class_name, $appClassName, true);
    }

}