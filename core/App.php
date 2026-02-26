<?php

class App {
    protected $controller = 'AuthController';
    protected $method = 'index'; // Default to index
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // Check if controller exists
        if (file_exists('../controllers/' . $url[0] . 'Controller.php')) {
            $this->controller = $url[0] . 'Controller';
            unset($url[0]);
        }

        require_once '../controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // Check if method exists in the controller
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        } else {
            // If no method specified, we use 'index' as default
            // except for AuthController which we might want to default to 'login'
            // but for consistency across all MVC, 'index' is better.
            if (!method_exists($this->controller, $this->method)) {
                // Fallback for Auth if index is missing
                if (method_exists($this->controller, 'login')) {
                    $this->method = 'login';
                }
            }
        }

        $this->params = $url ? array_values($url) : [];

        // Protection against calling non-existent methods
        if (method_exists($this->controller, $this->method)) {
            call_user_func_array([$this->controller, $this->method], $this->params);
        } else {
            // Handle 404 or default to login
            header('Location: ' . BASE_URL . '/Auth/login');
        }
    }

    public function parseUrl() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = trim($uri, '/');
        
        if ($uri == "") {
            return ['Auth', 'login'];
        }

        $url = explode('/', filter_var($uri, FILTER_SANITIZE_URL));
        
        if (isset($url[0])) {
            $url[0] = ucfirst($url[0]);
        }
        
        return $url;
    }
}
