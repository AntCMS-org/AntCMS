<?php

use AntCMS\AntPlugin;

class AdminPlugin extends AntPlugin
{
    public function displayRoute($route)
    {
        die("This is a test!");
    }

    public function getName()
    {
        return 'Admin';
    }
}
