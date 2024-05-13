<?php

namespace AntCMS\Plugins\System\Api;

use AntCMS\ApiResponse as Response;

class PublicApi
{
    public function status(): Response
    {
        return new Response('okay');
    }
}
