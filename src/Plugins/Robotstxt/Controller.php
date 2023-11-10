<?php

namespace Plugins\Robotstxt;

use \Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Controller
{
    public function registerRoutes(App $app)
    {
        $app->get('/robots.txt', function (Request $request, Response $response) {
            $sitemap = new Robotstxt();
            $sitemap->setRequest($request);
            $sitemap->SetResponse($response);
            return $sitemap->returnRobotstxt();
        });
    }
}
