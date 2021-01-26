<?php

declare(strict_types=1);

namespace Osm\Core\Tests;

use Osm\Core\Samples\App;
use Osm\Runtime\App\App as RuntimeApp;
use Osm\Runtime\App\ModuleGroup;
use Osm\Runtime\App\Package;
use Osm\Runtime\Factory;
use Osm\Runtime\Loading\ModuleGroupLoader;
use Osm\Runtime\Loading\ModuleLoader;
use Osm\Runtime\Loading\PackageLoader;
use Osm\Runtime\Runtime;
use Osm\Runtime\Traits\ComputedProperties;
use PHPUnit\Framework\TestCase;

/**
 * @property array $config
 */
class test_01_app_loading extends TestCase
{
    use ComputedProperties;

    protected function get_config(): array {
        return [
            'app_class_name' => App::class,
            'load_dev' => true,
        ];
    }

    public function test_package_loading() {
        Runtime::new()->factory($this->config, function (Factory $factory) {
            // GIVEN an app
            $factory->app = RuntimeApp::new([
                'upgrade_to_class_name' => $factory->app_class_name,
            ]);

            // AND the Composer configuration of this very package, `osmphp/core`
            $loader = PackageLoader::new([
                'package' => json_decode(file_get_contents(
                    "{$factory->project_path}/composer.json")),
                'root' => true,
            ]);

            // WHEN you load the package
            $package = $loader->load();

            // THEN its information can be found in its properties
            $this->assertEquals('osmphp/core', $package->name);
            $this->assertEquals('', $package->path);
            $this->assertEquals(\Osm\App\Package::class,
                $package->upgrade_to_class_name);
            $this->assertNotFalse(array_search('ext-mbstring',
                $package->after));
        });
    }

    public function test_module_group_loading() {
        Runtime::new()->factory($this->config, function (Factory $factory) {
            // GIVEN an app
            $app = $factory->app = RuntimeApp::new([
                'upgrade_to_class_name' => $factory->app_class_name,
            ]);

            // AND a package, already loaded into the app
            $package = $app->packages['osmphp/core'] = Package::new([
                'name' => 'osmphp/core',
                'path' => '',
            ]);

            // AND a module group located in the `samples` directory of
            // this very package, `osmphp/core`
            $loader = ModuleGroupLoader::new([
                'package' => $package,
                'namespace' => "Osm\\Core\\Samples\\",
                'path' => "samples/",
            ]);

            // WHEN you load the module group
            $moduleGroup = $loader->load();

            // THEN its information can be found in its properties
            $this->assertEquals('samples', $moduleGroup->path);
            $this->assertEquals(\Osm\Core\Samples\ModuleGroup::class,
                $moduleGroup->class_name);
            $this->assertEquals(\Osm\Core\Samples\ModuleGroup::class,
                $moduleGroup->upgrade_to_class_name);
            $this->assertEmpty($moduleGroup->after);
        });
    }

    public function test_module_loading() {
        Runtime::new()->factory($this->config, function (Factory $factory) {
            // GIVEN an app
            $app = $factory->app = RuntimeApp::new([
                'upgrade_to_class_name' => $factory->app_class_name,
            ]);

            // AND a package, already loaded into the app
            $package = $app->packages['osmphp/core'] = Package::new([
                'name' => 'osmphp/core',
                'path' => '',
            ]);

            // AND a module group, already loaded into the module group
            $moduleGroup = $app->module_groups[\Osm\Core\Samples\ModuleGroup::class] =
                ModuleGroup::new([
                    'package_name' => 'osmphp/core',
                    'path' => 'samples',
                    'upgrade_to_class_name' => \Osm\Core\Samples\ModuleGroup::class,
                ]);

            // AND a module located in the `samples/Some` directory of
            // this very package, `osmphp/core`
            $loader = ModuleLoader::new([
                'module_group' => $moduleGroup,
                'namespace' => "Osm\\Core\\Samples\\Some\\",
                'path' => "samples/Some",
            ]);

            // WHEN you load the module group
            $module = $loader->load();

            // THEN
            $this->assertEquals('samples/Some', $module->path);
            $this->assertEquals(\Osm\Core\Samples\Some\Module::class,
                $module->class_name);
            $this->assertEquals(\Osm\Core\Samples\Some\Module::class,
                $module->upgrade_to_class_name);
            $this->assertEmpty($module->after);
        });
    }
}