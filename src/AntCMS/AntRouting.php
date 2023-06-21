<?php

namespace AntCMS;

class AntRouting
{
    private string $baseUrl;
    private string $requestUri;
    private array $uriExploded;

    private array $indexes = ['/', '/index.php', '/index.html', '', 'index.php', 'index.html'];

    /**
     * @param string $baseUrl The base site URL. Ex: domain.com
     * @param string $requestUri The current request URI. Ex: /page/example
     */
    public function __construct(string $baseUrl, string $requestUri)
    {
        $this->baseUrl = $baseUrl;
        $this->requestUri = $requestUri;
        $this->setExplodedUri($requestUri);
    }

    /**
     * @param string $requestUri The current request URI. Ex: /page/example
     */
    public function setRequestUri(string $requestUri): void
    {
        $this->$requestUri = $requestUri;
        $this->setExplodedUri($requestUri);
    }

    /**
     * Used to add to the start of the request URI. Primarially used for plugin routing.
     * For example: this is used internally to rewrite /profile/edit to /plugin/profile/edit
     * 
     * @param string $append What to append to the start of the request URI.
     */
    public function requestUriUnshift(string $append): void
    {
        array_unshift($this->uriExploded, $append);
        $this->requestUri = implode('/', $this->uriExploded);
    }

    /**
     * Used to detect if the current request is over HTTPS. If the request is over HTTP, it'll redirect to HTTPS.
     */
    public function redirectHttps(): void
    {
        $scheme = $_SERVER['HTTPS'] ?? $_SERVER['REQUEST_SCHEME'] ?? $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
        $isHttps = !empty($scheme) && (strcasecmp('on', $scheme) == 0 || strcasecmp('https', $scheme) == 0);

        if (!$isHttps) {
            $url = 'https://' . AntTools::repairURL($this->baseUrl . $this->requestUri);
            header('Location: ' . $url);
            exit;
        }
    }

    /**
     * Used to check if the current request URI matches a specified route.
     * Supports using '*' as a wild-card. Ex: '/admin/*' will match '/admin/somthing' and '/admin'
     * 
     * @param string $uri The Route to compare against the current URI.
     */
    public function checkMatch(string $uri): bool
    {
        $matching =  explode('/', $uri);
        if (empty($matching[0])) {
            array_shift($matching);
        }

        if (count($matching) < count($this->uriExploded) && end($matching) !== '*') {
            return false;
        }

        foreach ($this->uriExploded as $index => $value) {
            if (isset($matching[$index]) && $matching[$index] !== '*' && $matching[$index] !== $value) {
                return false;
            }
        }

        return true;
    }

    /** 
     * Attempts to detect what plugin is associated with the current URI and then routes to the matching one. 
     */
    public function routeToPlugin(): void
    {
        $pluginName = $this->uriExploded[1];
        $pluginLoader = new AntPluginLoader();
        $plugins = $pluginLoader->loadPlugins();

        //Drop the first two elements of the array so the remaining segments are specific to the plugin.
        array_splice($this->uriExploded, 0, 2);

        foreach ($plugins as $plugin) {
            if (strtolower($plugin->getName()) === strtolower($pluginName)) {
                $plugin->handlePluginRoute($this->uriExploded);
                exit;
            }
        }

        // plugin not found
        header("HTTP/1.0 404 Not Found");
        echo ("Error 404");
        exit;
    }

    /**
     * @return bool Returns true if the current request URI is an index request.
     */
    public function isIndex(): bool
    {
        return (in_array($this->requestUri, $this->indexes));
    }

    private function setExplodedUri(string $uri): void
    {
        $exploaded = explode('/', $uri);

        if (empty($exploaded[0])) {
            array_shift($exploaded);
        }

        $this->uriExploded = $exploaded;
    }
}
