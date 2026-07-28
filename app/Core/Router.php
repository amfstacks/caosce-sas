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
        // $uri = str_replace('/pro/caosce_app', '', $uri);
        // $uri = explode('?', $uri)[0];
        
        // // Trim slashes so we don't get empty array elements
        // $segments = explode('/', trim($uri, '/')); 
        
        // $uri = str_replace('/pro/caosce_app', '', $uri);
        $uri = str_replace(BASE_PATH, '', $uri);
    // 2. Strip query string variables
    $uri = explode('?', $uri)[0];
    
    // 3. Trim slashes
    $segments = explode('/', trim($uri, '/')); 
    
    $slug = null;
    $routePath = $uri;

        // We assume any first segment that isn't a core folder/API is a school slug
        // e.g., URL is "yourschool.com/yag/login" -> $segments[0] is 'yag'
        if (count($segments) > 0 && !in_array($segments[0], ['api', 'superadmin', 'assets','login','admin', 'setup'])) {
            $slug = $segments[0];
            
            // Save the slug as a constant so any Model/Controller can query the DB with it!
            define('CURRENT_TENANT_SLUG', $slug); 
            
            // Remove the slug from the array
            array_shift($segments); 
            
            // Rebuild the route path (e.g., becomes "/login")
            $routePath = '/' . implode('/', $segments); 
            
            // If they just typed "yourschool.com/yag", default them to login
            if ($routePath === '/') {
                // $routePath = '/login';
                $routePath = '/landing';
            }
        }
        else {
            // NEW: If no slug is found, define it as null safely
            define('CURRENT_TENANT_SLUG', null);
        }




        // ==========================================
        // ADMIN MIDDLEWARE FIREWALL
        // ==========================================
        // Catch any route that starts with /admin/ or /api/admin/ (except the login pages)
        // if (
        //     (strpos($routePath, '/admin/') === 0 && $routePath !== '/admin/login') || 
        //     (strpos($routePath, '/api/admin/') === 0 && $routePath !== '/api/admin/login')
        // ) {
        //     if (session_status() === PHP_SESSION_NONE) {
        //         session_start();
        //     }
        if (
            (strpos($routePath, '/admin/') === 0 && !in_array($routePath, ['/admin/login', '/admin/fast-login'])) || 
            (strpos($routePath, '/api/admin/') === 0 && $routePath !== '/api/admin/login')
        ) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $isApiRequest = strpos($routePath, '/api/') === 0;
            $loginUrl = defined('CURRENT_TENANT_SLUG') && CURRENT_TENANT_SLUG !== null 
                ? '/' . CURRENT_TENANT_SLUG . '/admin/login' 
                : '/admin/login';

            // Check 1: Are they logged in as an admin?
            if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !in_array($_SESSION['admin_role'], ['superadmin', 'subadmin'])) {
                if ($isApiRequest) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
                    exit;
                }
                header("Location: " . BASE_PATH . $loginUrl);
                exit;
            }

            // Check 2: Do they belong to this specific workspace?
            if (defined('CURRENT_TENANT_SLUG') && CURRENT_TENANT_SLUG !== null) {
                if (!isset($_SESSION['tenant_slug']) || $_SESSION['tenant_slug'] !== CURRENT_TENANT_SLUG) {
                    if ($isApiRequest) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => 'Unauthorized workspace access.']);
                        exit;
                    }
                    header("Location: " . BASE_PATH . '/' . CURRENT_TENANT_SLUG . '/admin/login');
                    exit;
                }
            }
        }

        // CRITICAL FIX: Look for $routePath in our routes array, not $uri!
        if (isset($this->routes[$method][$routePath])) {
            $callback = $this->routes[$method][$routePath];

            // If it's a View (e.g., '/login' -> 'views/student/login.php')
            if (is_string($callback) && strpos($callback, 'views/') === 0) {
                require_once '../' . $callback;
                return;
            }

            // If it's a Controller API call
            if (is_array($callback)) {
                $controllerName = $callback[0];
                $methodName = $callback[1];
                
                require_once APPROOT . '/Controllers/' . $controllerName . '.php';
                $controller = new $controllerName();
                
                $inputData = json_decode(file_get_contents('php://input'), true) ?? $_POST;
                // $controller->$methodName($inputData);
                header('Content-Type: application/json'); // Tell the browser to expect JSON
                echo $controller->$methodName($inputData);
                return;
            }
        }

        // 404 Handler
        http_response_code(404);
        echo json_encode(['error' => 'Route not found.']);
    }
}