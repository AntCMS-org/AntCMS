<?php

/**
 * Copyright 2025 AntCMS
 */

namespace AntCMS\Plugins\Auth\Api;

use AntCMS\AntAuth;
use AntCMS\ApiResponse as Response;

class PublicApi
{
    /**
     * @param array<string, mixed> $data
     */
    public function login(array $data): Response
    {
        $antAuth = new AntAuth();
        $result = $antAuth->login($data['post']['username'], $data['post']['password']);
        if ($result) {
            return new Response('okay');
        }

        return new Response('failed');
    }

    public function logOut(array $data): Response
    {
        $antAuth = new AntAuth();
        $antAuth->logout();
        return new Response(true);
    }

    public function isLoggedIn(array $data): Response
    {
        $antAuth = new AntAuth();
        return new Response($antAuth->isLoggedIn() ? 'yes' : 'no');
    }
}
