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

### Example Plugin

```PHP
<?php

namespace AntCMS\Plugins\Example;

use AntCMS\AbstractPlugin;
use Flight;

class Controller extends AbstractPlugin
{
    public function __construct()
    {
        Flight::route("GET /hi", function (): void {
            echo "Hello!";
        });
    }
}
```

<hr/>

## Hooks

Creating, registering, and firing hooks all go through the `AntCMS\HookController` class.
You may register and fire a hook as well as register callbacks from anywhere within your code.

### Real-world example

```PHP
<?php

use AntCMS\HookController;

class myCoolClass
{
    public function __construct()
    {
        // Registers a hook and sets the description for it
        HookController::registerHook('myCoolHook', 'The helpful description of my hook');

        /**
         * Register a callback for the hook you just created
         * You could also directly to this without calling `registerHook`, which will register the hook without a description
         * If `registerHook` is later called, the description of the hook will be updated
         */
        HookController::registerCallback('myCoolHook', [$this, 'hookCallback']);

        // Fire a hook
        $data = ['some', 'data', 'in', 'an', 'array'];
        HookController::fire('myCoolHook', $data);
    }

    public function hookCallback(array &$data)
    {
        error_log(print_r($data, true));
    }
}
```
