<?php

namespace AntCMS\Plugins\System\Api;

use AntCMS\ApiResponse as Response;

class PublicApi
{
    public function status(array $data): Response
    {
        return new Response('okay');
    }
}
