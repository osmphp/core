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

**TODO**. Have a project template for empty projects, and an instruction (in the
docs) on how to use it in an existing project, with any framework.

Install the `osmphp/core` Composer package:

    composer require osmphp/core

