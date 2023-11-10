<?php

namespace Plugins\Admin;

use \Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Controller
{
    public function registerRoutes(App $app)
    {
        $profilePlugin = new Admin;

        $app->get('/admin', function (Request $request, Response $response) use ($profilePlugin) {
            $profilePlugin->setRequest($request);
            $profilePlugin->SetResponse($response);
            return $profilePlugin->renderIndex();
        });
    }
}
