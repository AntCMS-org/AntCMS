<?php

/**
 * Copyright 2026 AntCMS
 */

namespace AntCMS\Api;

use AntCMS\HookController;
use Flight;

class Controller
{
    /**
     * Sends the response to the client
     */
    private function sendResponse(Response $Response): void
    {
        $code = $Response->getCode();
        if ($Response->isError()) {
            $message = $Response->getMessage();
            error_log("({$code}) {$message}");
        }

        Flight::json($Response->getBody(), $code);
    }

    /**
     * Handles actually creating an instance of the correct API class, calling the method, and returning the response
     */
    private function call(string $type, string $plugin, string $method): void
    {
        $requestParams = new RequestParams($plugin, $method);

        $apiFqcn = "\AntCMS\\Plugins\\" . ucfirst($plugin) . "\\Api\\" . ucfirst($type) . 'Api';

        // Send an error if the entrypoint doesn't exist
        if (!class_exists($apiFqcn)) {
            $response = new Response('', true, 404, "API entrypoint '{$type}/{$plugin}' does not exist");
            $this->sendResponse($response);
            return;
        }

        // Now instance the API for that plugin
        $api = new $apiFqcn();

        // Send an error if the endpoint doesn't exist
        if (!method_exists($api, $method)) {
            $response = new Response('', true, 404, "API endpoint '{$type}/{$plugin}/{$method}' does not exist");
            $this->sendResponse($response);
            return;
        }

        $hookData = ['plugin' => ucfirst($plugin), 'method' => $method];
        HookController::fire('onBeforeApiCalled', $hookData);

        // Sanity checks passed, now actually process the request
        try {
            $response = $api->$method($requestParams);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $location = str_replace(PATH_ROOT . DIRECTORY_SEPARATOR, '', $e->getFile()) . ':' . $e->getLine();

            // Create a generic HTTP 500 response
            $response = new Response('', true, 500, "An internal error occured");

            // Log the actual message for aid in debugging
            error_log("Fatal error: {$message} ({$location})");
        }

        $hookData['response'] = $response;
        HookController::fire('onAfterApiCalled', $hookData);

        $this->sendResponse($response);
    }

    public function publicController(string $plugin, string $method): void
    {
        $this->call('public', $plugin, $method);
    }

    public function privateController(string $plugin, string $method): void
    {
        // TODO
        $Response = new Response('', true, 501, "Protected API endpoints are not yet implemented in AntCMS");
        $this->sendResponse($Response);
    }
}
