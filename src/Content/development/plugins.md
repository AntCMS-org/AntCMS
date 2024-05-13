--AntCMS--
Title: Plugins & Hooks
Author: The AntCMS Team
Description: Learn how hooks and plugins work with AntCMS.
NavItem: true
--AntCMS--

# Plugins and Hooks in AntCMS

AntCMS allows you to extend it utilizing hooks and plugins.

Please keep in mind in their current state plugins are in a very early implementation.

---

## Plugins

All plugins reside under the `Plugins` folder.

- Plugins should be given a namespace under `AntCMS\Plugins`. Example: `AntCMS\Plugins\Example`.
- Be sure to have a Controller class so AntCMS can register your plugin routes & hooks.
- Plugin controllers should extend the `AntCMS\AbstractPlugin` class.
- AntCMS uses [FlightPHP](https://docs.flightphp.com/?lang=en) for routing, routes should be registered per their docs.
- Create a `Templates` directory in your plugin folder to have it automatically be added to the twig loader.

## Hooks

 - Hooks should be created, registered, and fired using the `AntCMS\HookController` class.
 - Hooks may be interacted with anywhere in your code and point to any function.
 - When fired, hook callbacks will be fired in the order they were registered.
 - You may register a callback before the hook itself has been registered.
 - Registering a hook for a second time will simply update the description.

---

## Example Plugin

An example AntCMS plugin is available.
This plugin covers basic functionality such as plugin templates, hooks, adding API endpoints, and registering routes.

Check it out on the [AntCMS-org/Example-Plugin](https://github.com/AntCMS-org/Example-Plugin) repository.