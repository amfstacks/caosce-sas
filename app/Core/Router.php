<?php
class Router {
    private $routes = [];

    public function get($uri, $callback) {
        $this->routes['GET'][$uri] = $callback;
    }

    public function post($uri, $callback) {
        $this->routes['POST'][$uri] = $callback;
    }

    public function dispatch($uri, $method) {
        // Strip query string variables from the URI
        $uri = explode('?', $uri)[0];

        if (isset($this->routes[$method][$uri])) {
            $callback = $this->routes[$method][$uri];

            // If it's a View (e.g., '/login' -> 'views/student/login.php')
            if (is_string($callback) && strpos($callback, 'views/') === 0) {
                require_once '../' . $callback;
                return;
            }

            // If it's a Controller API call (e.g., ['AuthController', 'login'])
            if (is_array($callback)) {
                $controllerName = $callback[0];
                $methodName = $callback[1];
                
                require_once APPROOT . '/Controllers/' . $controllerName . '.php';
                $controller = new $controllerName();
                
                // Pass raw JSON input to the controller for AJAX requests
                $inputData = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                $controller->$methodName($inputData);
                return;
            }
        }

        // 404 Handler
        http_response_code(404);
        echo json_encode(['error' => 'Route not found.']);
    }
}