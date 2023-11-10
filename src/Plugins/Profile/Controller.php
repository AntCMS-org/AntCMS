<?php

namespace Plugins\Profile;

use \Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Controller
{
    public function registerRoutes(App $app)
    {
        $profilePlugin = new Profile;

        $app->get('/profile', function (Request $request, Response $response) use ($profilePlugin) {
            $profilePlugin->setRequest($request);
            $profilePlugin->SetResponse($response);
            return $profilePlugin->renderIndex();
        });

        $app->get('/profile/firsttime', function (Request $request, Response $response) use ($profilePlugin) {
            $profilePlugin->setRequest($request);
            $profilePlugin->SetResponse($response);
            return $profilePlugin->renderFirstTime();
        });

        $app->post('/profile/submitfirst', function (Request $request, Response $response) use ($profilePlugin) {
            $profilePlugin->setRequest($request);
            $profilePlugin->SetResponse($response);
            return $profilePlugin->submitfirst();
        });

        $app->get('/profile/logout', function (Request $request, Response $response) use ($profilePlugin) {
            $profilePlugin->setRequest($request);
            $profilePlugin->SetResponse($response);
            return $profilePlugin->logout();
        });

        $app->post('/profile/save', function (Request $request, Response $response) use ($profilePlugin) {
            $profilePlugin->setRequest($request);
            $profilePlugin->SetResponse($response);
            return $profilePlugin->save();
        });

        $app->get('/profile/edit', function (Request $request, Response $response) use ($profilePlugin) {
            $profilePlugin->setRequest($request);
            $profilePlugin->SetResponse($response);
            return $profilePlugin->edit();
        });

        $app->get('/profile/resetpassword', function (Request $request, Response $response) use ($profilePlugin) {
            $profilePlugin->setRequest($request);
            $profilePlugin->SetResponse($response);
            return $profilePlugin->resetpassword();
        });
    }
}
