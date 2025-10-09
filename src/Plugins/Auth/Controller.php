<?php

/**
 * Copyright 2025 AntCMS
 */

namespace AntCMS\Plugins\Auth;

use AntCMS\AbstractPlugin;
use AntCMS\Twig;
use Flight;

class Controller extends AbstractPlugin
{
    public function __construct()
    {
        Flight::route("GET /login", function (): void {
            echo Twig::render('login.html.twig', [
                'AntCMSTitle' => 'Login',
            ]);
        });
    }
}
