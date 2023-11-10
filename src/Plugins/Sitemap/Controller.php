<?php

namespace Plugins\Sitemap;

use \Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Controller
{
    public function registerRoutes(App $app)
    {
        $app->get('/sitemap.xml', function (Request $request, Response $response) {
            $sitemap = new Sitemap();
            $sitemap->setRequest($request);
            $sitemap->SetResponse($response);
            return $sitemap->returnSitemap();
        });
    }
}
