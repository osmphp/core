<?php

declare(strict_types=1);

namespace Osm\Runtime\Loading;

use Osm\Runtime\Factory;
use Osm\Runtime\Hints\ComposerLock;
use Osm\Runtime\Hints\Package;
use Osm\Runtime\Object_;

/**
 * Computed:
 *
 * @property \stdClass|Package $composer_json
 * @property \stdClass|ComposerLock $composer_lock
 */
class AppLoader extends Object_
{
    /** @noinspection PhpUnused */
    protected function get_composer_json(): \stdClass {
        global $osm_factory; /* @var Factory $osm_factory */

        return json_decode(file_get_contents(
            "{$osm_factory->project_path}/composer.json"));
    }

    /** @noinspection PhpUnused */
    protected function get_composer_lock(): \stdClass {
        global $osm_factory; /* @var Factory $osm_factory */

        return json_decode(file_get_contents(
            "{$osm_factory->project_path}/composer.lock"));
    }

    public function load(): void {
        foreach ($this->composer_lock->packages as $package) {
            PackageLoader::new([
                'package' => $package,
                'app_loader' => $this,
            ])->load();
        }

        PackageLoader::new([
            'name' => '',
            'package' => $this->composer_json,
            'app_loader' => $this,
        ])->load();
    }
}