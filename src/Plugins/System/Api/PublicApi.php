<?php

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
