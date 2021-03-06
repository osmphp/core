<p align="center">
    <a href="https://github.com/osmphp/core/actions"><img src="https://github.com/osmphp/core/workflows/tests/badge.svg" alt="Build Status"></a>
    <a href="https://packagist.org/packages/osmphp/core"><img src="https://img.shields.io/packagist/dt/osmphp/core" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/osmphp/core"><img src="https://img.shields.io/packagist/v/osmphp/core" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/osmphp/core"><img src="https://img.shields.io/packagist/l/osmphp/core" alt="License"></a>
</p>

* [About osmphp/core](#about-osmphpcore) 
* [Prerequisites](#prerequisites) 
* [Installation](#installation) 
* [Getting Started](#getting-started)
* [Using The Library](#using-the-library)
* [Contributing](#contributing)
* [License](#license)
* [Commercial License & Support](#commercial-license--support)

## About `osmphp/core`

`osmphp/core` enables modular software development in any PHP project - dividing
the application code into modules - reusable, extensible and pluggable parts.

A module is a directory responsible for a single feature or concept of your
application. For example one module may handle products in e-commerce
application, another - authorize users into the application's restricted area,
yet another one - enable the application to be used in the command line, and so
on.

`osmphp/core` is different from other frameworks in several ways:

* **Unprecedented extensibility**. From your module, add new and modify
  existing (even protected) methods of any other module class.
* **Smaller footprint, faster execution**. Class property values are computed
  only when (and if) they are accessed, unless you explicitly assign them.
* **Simple object instantiation**. Classes know and use their default
  dependencies, you don't have to pass them when creating an object. Unless you
  want to, for example, when mocking a dependency in a unit test. It also
  completely removes the hassle of configuring a dependency injection container.
* **Auto-wiring**. Plug-in your class into the application just by extending a
  certain class, or by adding an attribute. The application intimately knows the
  class definitions of all the modules, and wires them together without
  additional configuration files.

## Prerequisites

This library requires:

* PHP 8
    * `mbstring` extension
* Composer

Install them if necessary.

## Installation

Install the `osmphp/core` Composer package:

        composer require osmphp/core

## Getting Started 

Prepare the project for using the library as described below. In the future, this package will come preinstalled with a project template, and you will not have to write this boilerplate code.

1. Create an application class in the `src/App.php` file (assuming that your project is configured to autoload the `App\` namespace from the `src/` directory; if it's not the case, adjust the code snippets accordingly):

        <?php
        declare(strict_types=1);
        namespace App;
        use Osm\Core\App as BaseApp;
        
        class App extends BaseApp {
        }

2. Create a module group class and in the `src/ModuleGroup.php`:

        <?php
        
        declare(strict_types=1);
        namespace App;
        use Osm\Core\ModuleGroup as BaseModuleGroup;
        
        class ModuleGroup extends BaseModuleGroup {
        }

3. Compile the application (in Windows, use `\` instead of `/`):

        php vendor/osmphp/core/bin/compile.php App\App

4. In your entry point file `public/index.php` (there may be more than one entry point file, add the following to every one of them), make sure that the code is executed in context of the application object:

        <?php
        
        declare(strict_types=1);
        
        use Osm\Runtime\Apps;
        use App\App;
        
        ...
             
        Apps::$project_path = dirname(__DIR__);
        Apps::run(Apps::create(App::class), function() {
            ...
        });

## Using The Library

Documentation is a work in progress. This section will be updated once it is ready.

## Contributing

Your help is really welcome, be it a reported bug, an occasional pull request, or the full fledged participation. To get started, open an issue and tell us that you want to be a part of it, and we'll get in touch.   

## License

The `osmphp/core` package is open-sourced software licensed under the [GPL v3](LICENSE) license.

## Commercial License & Support

In case the open source license if not a good fit for you, or if you need support, [let me know](https://github.com/osmianski). 