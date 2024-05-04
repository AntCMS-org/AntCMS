--AntCMS--
Title: Hooks
Author: The AntCMS Team
Description: Learn how hooks work int AntCMS.
NavItem: true
--AntCMS--

# Hooks

Creating, registering, and firing hooks all go through the `AntCMS\HookController` class.

## Real-world example

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

        /**
         * Since our hook modifies the data, our array should now look like this:
         * ['some', 'data', 'in', 'an', 'array', '!']
         */
    }

    public function hookCallback(array &$data)
    {
        // When our callback is called from the hook, we have access to the data associated with it
        print_r($data);

        // Additionally, callbacks may modify the hook data
        $data[] = '!';
    }
}
```