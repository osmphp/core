* [About osmphp/core](#about-osmphpcore) 
* [Prerequisites](#prerequisites) 
* [Installation](#installation) 

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

1. Install the `osmphp/core` Composer package:

    composer require osmphp/core

2. Create an application class in the `src/App.php` file (assuming that your project is configured to autoload the `App\` namespace from the `src/` directory; if it's not the case, adjust the code snippets accordingly):

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

4. Compile the application (in Windows, you "\" instead of "/"):

        vendor/bin/osmc App\App

5. In your entry point file `public/index.php` (there may be more than one entry point file, add the following to every one of them), make sure that the code is executed in context of the application object:

        <?php
        
        declare(strict_types=1);
        
        use Osm\Runtime\Apps;
        use App\App;
        
        ...
             
        Apps::$project_path = dirname(__DIR__);
        Apps::run(Apps::create(App::class), function() {
            ...
        });

## Contributing

Your help is really welcome, be it a reported bug, an occasional pull request, or the full fledged participation. To get started, open an issue and tell us that you want to be a part of it, and we'll get in touch.   

## License

The `osmphp/core` package is open-sourced software licensed under the [GPL v3](LICENSE) license.

## Commercial License & Support

In case the open source license if not a good fit for you, or if you expect being supported, [let me know](https://github.com/osmianski). 