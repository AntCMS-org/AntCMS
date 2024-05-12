--AntCMS--
Title: Plugins & Hooks
Author: The AntCMS Team
Description: Learn how hooks and plugins work with AntCMS.
NavItem: true
--AntCMS--

# Plugins and Hooks in AntCMS

AntCMS allows you to extend it utilizing hooks and plugins.

Please keep in mind in their current state plugins are in a very early implementation.

<hr/>

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

## Example Plugin with Hooks

The following is an example AntCMS plugin. It should be installed to `Plugins/Example/Controller.php`.

This plugin has the following examples:

 - The usage of hooks.
 - Registering routes & outputting data to the browser.
 - Adding a new entry to the `sitemap.xml` file.
 - Add allow / deny entries to the `robots.txt` file.

```PHP
<?php

namespace AntCMS\Plugins\Example;

use AntCMS\AbstractPlugin;
use AntCMS\HookController;
use Flight;

class Controller extends AbstractPlugin
{
    public function __construct()
    {
        // Register a hook and sets the description for it
        HookController::registerHook('myCoolHook', 'The helpful description of my hook');

        /**
         * Register a callback for the hook you just created
         * You could also directly to this without calling `registerHook`, which will register the hook without a description
         * If `registerHook` is later called, the description of the hook will be updated
         */
        HookController::registerCallback('myCoolHook', [$this, 'hookCallback']);

        // Register a route
        Flight::route("GET /hi", function (): void {
            // write out content to the user
            echo "<h1>Hello!</h1>";
            echo "<p>This is an example page for a custom AntCMS plugin!</p>";

            // We can also fire that hook we made when this page is loaded
            HookController::fire('myCoolHook', ['some', 'data', 'in', 'an', 'array']);

            echo "<h2>Hooks</h2>";
            // And we list the registered hooks
            $hooks = HookController::getHookList();
            foreach ($hooks as $hook) {
                echo "<p><strong>$hook->name:</strong> $hook->description</p>";
            }
        });

        // Add a new sitemap entry
        $this->appendSitemap('/hi');

        // Disallow it from being indexed via the robots.txt file
        $this->addDisallow('/hi');

        // Or we could explicitly allow indexing it
        //$this->addAllow('/hi');
    }

    public function hookCallback(array $data)
    {
        error_log(print_r($data, true));
    }
}
```
