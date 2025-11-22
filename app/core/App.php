<?php
// FILE: /app/core/App.php

class App {
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // Check for API routes
        if (isset($url[0]) && $url[0] === 'api') {
            $this->handleApiRoute($url);
            return;
        }

        // Handle web routes
        $this->handleWebRoute($url);
    }

    protected function handleApiRoute($url) {
        // Remove 'api' from URL
        array_shift($url);

        // Default API controller
        $controllerName = 'ApiController';

        if (isset($url[0])) {
            $controllerFile = APP . '/controllers/' . ucfirst($url[0]) . 'ApiController.php';
            if (file_exists($controllerFile)) {
                $controllerName = ucfirst($url[0]) . 'ApiController';
                array_shift($url);
            }
        }

        require_once APP . '/controllers/' . $controllerName . '.php';
        $this->controller = new $controllerName;

        if (isset($url[0])) {
            if (method_exists($this->controller, $url[0])) {
                $this->method = $url[0];
                array_shift($url);
            }
        }

        $this->params = $url ? array_values($url) : [];

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    protected function handleWebRoute($url) {
        // Look for controller
        if (isset($url[0])) {
            $controllerFile = APP . '/controllers/' . ucfirst($url[0]) . 'Controller.php';
            if (file_exists($controllerFile)) {
                $this->controller = ucfirst($url[0]) . 'Controller';
                unset($url[0]);
            }
        }

        require_once APP . '/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // Look for method
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }

        // Get parameters
        $this->params = $url ? array_values($url) : [];

        // Call controller method with parameters
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    protected function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
