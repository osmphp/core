<?php

declare(strict_types=1);

namespace Osm\Runtime\Compilation;

use Osm\Core\App;
use Osm\Core\Attributes\Name;
use Osm\Core\BaseModule;
use Osm\Runtime\Exceptions\CircularDependency;
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
 * @property bool $load_all
 * @property Package[] $unsorted_packages
 * @property Module[] $unsorted_modules
 * @property Module[] $referenced_modules
 * @property Package[] $packages
 * @property Module[] $modules
 * @property Class_[] $classes
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

    protected function get_load_all(): bool {
        $class = $this->class_name; /* @var App $class */

        return $class::$load_all;
    }

    /** @noinspection PhpUnused */
    protected function get_unsorted_packages(): array {
        $packages = [];

        $this->loadPackages($packages, 'packages');

        if ($this->load_dev_sections) {
            $this->loadPackages($packages, 'packages-dev');
        }

        $packages[$this->composer_json->name ?? ''] =
            $this->loadPackage($this->composer_json, root: true);

        return $packages;
    }

    protected function loadPackages(array &$packages, string $section): void {
        foreach ($this->composer_lock->$section as $package) {
            /* @var PackageHint $package */
            $packages[$package->name] = $this->loadPackage($package);
        }
    }

    protected function loadPackage(\stdClass|PackageHint $json,
        bool $root = false): Package
    {
        $require = array_keys((array)($json->require ?? []));
        if ($this->load_dev_sections && $root) {
            $require = array_merge($require,
                array_keys((array)($json->{'require-dev'} ?? [])));
        }

        $sourceRoots = (array)($json->autoload?->{"psr-4"} ?? []);
        if ($this->load_dev_sections && $root) {
            $sourceRoots = array_merge($sourceRoots,
                (array)($json->{'autoload-dev'}?->{"psr-4"} ?? []));

        }
        return Package::new([
            'name' => $json->name ?? '',
            'path' => $root ? '' : "vendor/{$json->name}",
            'json' => $json,
            'after' => $require,
            'source_roots' => $sourceRoots,
        ]);
    }

    /** @noinspection PhpUnused */
    protected function get_unsorted_modules(): array {
        $modules = []; /* @var Module[] $modules */

        foreach ($this->unsorted_packages as $package) {
            foreach ($package->source_roots as $namespace => $path) {
                for ($depth = 0; $depth < 3; $depth++) {
                    $this->loadModules($modules, $package, $namespace,
                        $path, $depth);
                }
            }
        }

        return $modules;
    }

    /** @noinspection PhpUnused */
    protected function get_referenced_modules(): array {
        return $this->load_all
            ? $this->unsorted_modules
            : $this->unloadUnreferencedModules($this->unsorted_modules);
    }

    protected function loadModules(array &$modules, Package $package,
        string $sourceRootNamespace, string $sourceRootPath, int $depth): void
    {
        global $osm_app; /* @var Compiler $osm_app */

        $absolutePath = $osm_app->paths->project;
        if ($package->path) {
            $absolutePath .= '/' . $package->path;
        }
        if ($sourceRootPath) {
            $absolutePath .= '/' . rtrim($sourceRootPath, "/\\");
        }
        $pattern = $absolutePath . str_repeat('/*', $depth);

        foreach (glob($pattern, GLOB_ONLYDIR | GLOB_MARK) as $path) {
            $relativePath = ltrim(mb_substr($path, mb_strlen($osm_app->paths->project)),
                "/\\");
            $namespace = $sourceRootNamespace .
                str_replace('/', '\\', ltrim(
                    mb_substr($path, mb_strlen($absolutePath)),
                    "/\\"));

            $this->loadModule($modules, $package, $namespace, $relativePath);
        }
    }

    protected function loadModule(array &$modules, Package $package,
        string $namespace, string $path): void
    {
        global $osm_app; /* @var Compiler $osm_app */

        $absolutePath = rtrim("{$osm_app->paths->project}/{$path}", "/\\");
        $filename = "{$absolutePath}/Module.php";
        $className = "{$namespace}Module";

        if (!is_file($filename)) {
            return; // there is no Module class
        }

        if (!is_a($className, BaseModule::class, true)) {
            return;
        }

        if (!$this->load_all && !$this->matches($className)) {
            return;
        }

        $reflection = new \ReflectionClass($className);
        $name = isset($reflection->getAttributes(Name::class)[0])
            ? $reflection->getAttributes(Name::class)[0]->newInstance()->name
            : $osm_app->paths->className($className, '\\Module');

        $modules[$className] = Module::new([
            'package_name' => $package->name,
            'class_name' => $className,
            'name' => $name,
            'path' => rtrim($path, "/\\"),
            'after' => array_unique(array_merge($className::$after,
                $className::$requires)),
            'traits' => $className::$traits,
            'requires' => $className::$requires,
            'app_class_name' => $className::$app_class_name,
        ]);
    }

    /**
     * @param string|BaseModule $class
     * @return bool
     * @noinspection PhpDocSignatureInspection
     */
    protected function matches(string $class): bool {
        $appClassName = $class::$app_class_name;

        if (!$appClassName) {
            // if module doesn't explicitly specify the class of apps that
            // should load it, then let it in for now; after all modules are
            // loaded, such app-less modules will be checked once again if
            // they are listed in the `requires` section of any loaded module,
            // and if not, they will be removed
            return true;
        }

        if (!class_exists($appClassName)) {
            return false;
        }

        return is_a($this->class_name, $appClassName, true);
    }

    /** @noinspection PhpUnused */
    protected function get_packages(): array {
        $packages = [];

        foreach ($this->referenced_modules as $module) {
            $packages[$module->package_name] =
                $this->unsorted_packages[$module->package_name];
        }

        return $this->sort($packages, 'Packages', function($positions) {
            return function(Package $a, Package $b) use ($positions) {
                return $positions[$a->name] <=> $positions[$b->name];
            };
        });
    }

    /** @noinspection PhpUnused */
    protected function get_modules(): array {
        return $this->sort($this->referenced_modules, 'Modules', function($positions) {
            $parentKeys = array_flip(array_keys($this->packages));

            return function(Module $a, Module $b) use ($positions, $parentKeys) {
                return ($result = $parentKeys[$a->package_name]
                        <=> $parentKeys[$b->package_name]) != 0
                    ? $result
                    : $positions[$a->class_name] <=> $positions[$b->class_name];
            };
        });
    }

    public function sort(array $items, string $pluralTitle,
        callable $callback): array
    {
        $count = count($items);

        $positions = [];

        for ($position = 0; $position < $count; $position++) {
            $key = $this->findItemWithAlreadyResolvedDependencies($items, $positions);
            if (!$key) {
                throw $this->circularDependency($items, $positions, $pluralTitle);
            }

            $positions[$key] = $position;
        }

        uasort($items, $callback($positions));

        return $items;
    }

    protected function findItemWithAlreadyResolvedDependencies(array $items,
        array $positions): ?string
    {
        foreach ($items as $key => $item) {
            if (isset($positions[$key])) {
                continue;
            }

            if ($this->hasUnresolvedDependency($item, $items, $positions)) {
                continue;
            }

            return $key;
        }

        return null;
    }

    protected function hasUnresolvedDependency(object $item,
        array $items, array $positions): bool
    {
        foreach ($item->after as $key) {
            if (!isset($items[$key])) {
                // no such package - don't consider it a dependency
                continue;
            }

            if (!isset($positions[$key])) {
                // dependencies not added to the position array are not
                // resolved yet
                return true;
            }
        }

        return false;
    }

    protected function circularDependency(array $items, array $positions,
        string $pluralTitle)
    {
        $circular = [];

        foreach ($items as $key => $item) {
            if (!isset($positions[$key])) {
                $circular[] = $key;
            }
        }
        return new CircularDependency(
            sprintf('%s with circular dependencies found: %s',
            $pluralTitle, implode(', ', $circular)));
    }

    /** @noinspection PhpUnused */
    protected function get_classes(): array {
        $classes = [];

        foreach ($this->modules as $module) {
            $this->loadModuleClasses($classes, $module);
            $this->loadExternalClasses($classes, $module);
        }

        // search for app class in source roots not even having
        // a single module in it
        foreach ($this->unsorted_packages as $package) {
            foreach ($package->source_roots as $namespace => $path) {
                $this->loadAppClass($classes, $package, $namespace, $path);
            }
        }

        return $classes;
    }

    protected function loadModuleClasses(array &$classes, Module $module,
        string $path = '')
    {
       global $osm_app; /* @var Compiler $osm_app */

       $modulePath = "{$osm_app->paths->project}/{$module->path}";
       $absolutePath = $path ? "{$modulePath}/{$path}" : $modulePath;

       foreach (new \DirectoryIterator($absolutePath) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if (!preg_match('/^[A-Z]/', $fileInfo->getFilename())) {
                continue;
            }

            if ($fileInfo->isDir()) {
                $this->loadModuleClasses($classes, $module,
                    ($path ? "{$path}/" : '') . $fileInfo->getFilename());
                continue;
            }

            if ($fileInfo->getExtension() != 'php') {
                continue;
            }

            $className = mb_substr($module->class_name, 0,
                mb_strlen($module->class_name) - mb_strlen('Module')) .
                str_replace('/', '\\',
                    ($path ? "{$path}/" : '') .
                    pathinfo($fileInfo->getFilename(), PATHINFO_FILENAME)
                );

            $classes[$className] = Class_::new([
                'name' => $className,
                'filename' => "{$absolutePath}/{$fileInfo->getFilename()}",
            ]);
        }
    }

    protected function loadExternalClasses(array &$result, Module $module): void {
        /* @var string|BaseModule $moduleClassName */
        $moduleClassName = $module->class_name;

        foreach ($moduleClassName::$classes as $className) {
            $result[$className] = Class_::new([
                'name' => $className,
                'filename' => (new \ReflectionClass($className))->getFileName(),
            ]);
        }
    }

    protected function loadAppClass(array &$classes, Package $package,
        string $sourceRootNamespace, string $sourceRootPath): void
    {
        global $osm_app; /* @var Compiler $osm_app */

        $path = $osm_app->paths->project;
        if ($package->path) {
            $path .= '/' . $package->path;
        }
        if ($sourceRootPath) {
            $path .= '/' . rtrim($sourceRootPath, "/\\");
        }
        $path .= "/App.php";

        if (is_file($path)) {
            $className = "{$sourceRootNamespace}App";

            $classes[$className] = Class_::new([
                'name' => $className,
                'filename' => $path,
            ]);
        }
    }

    public function addAttribute(array &$attributes, string $class,
        object $attribute): void
    {
        $reflection = new \ReflectionClass($class);
        $attributeFlags = $reflection->getAttributes(\Attribute::class)[0] ?? null;
        if (($attributeFlags?->newInstance()->flags ?? 0) & \Attribute::IS_REPEATABLE) {
            if (!isset($attributes[$class])) {
                $attributes[$class] = [];
            }
            $attributes[$class][] = $attribute;
        }
        else {
            $attributes[$class] = $attribute;
        }
    }

    public function parseType(\ReflectionType $type): string {
        $types = $type instanceof \ReflectionUnionType
            ? $type->getTypes()
            : [$type];

        $result = '';

        foreach ($types as $type) {
            if ($result) {
                $result .= " | ";
            }
            if (!$type->isBuiltin()) {
                $result .= '\\';
            }
            $result .= $type->getName();
        }

        return $result;
    }

    /**
     * @param Module[] $modules
     * @return Module[]
     */
    protected function unloadUnreferencedModules(array $modules): array {
        $result = [];
        $required = [];

        foreach ($modules as $module) {
            if (!$module->app_class_name) {
                continue;
            }

            $result[$module->class_name] = $module;
            $required = array_unique(array_merge($required, $module->requires));
        }

        do {
            $changed = false;

            foreach ($modules as $module) {
                if (isset($result[$module->class_name])) {
                    continue;
                }

                if (!in_array($module->class_name, $required)) {
                    continue;
                }

                $result[$module->class_name] = $module;
                $required = array_unique(array_merge($required, $module->requires));
                $changed = true;
            }
        }
        while ($changed);

        return $result;
    }
}