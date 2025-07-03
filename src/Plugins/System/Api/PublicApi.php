<?php

/**
 * Copyright 2025 AntCMS
 */

namespace AntCMS\Plugins\System\Api;

use AntCMS\ApiResponse as Response;

class PublicApi
{
    /**
     * @param array<string, mixed> $data
     */
    public function status(array $data): Response
    {
        return new Response('okay');
    }
}
