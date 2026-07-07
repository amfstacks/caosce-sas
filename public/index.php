<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php
require_once '../config.php';
require_once APPROOT . '/Core/Database.php';
require_once APPROOT . '/Core/Router.php';

// Helper for UUID generation (Crucial for offline architecture)
require_once APPROOT . '/Core/UuidHelper.php'; 

$router = new Router();

$router->get('/api/tenant-info', ['AuthController', 'getTenantInfo']);

// --- WEB ROUTES (Views) ---
$router->get('/login', 'views/auth/login.php');
$router->get('/admin/dashboard', 'views/admin/dashboard.php');
// $router->get('/admin/sessions', 'views/admin/sessions.php');
$router->get('/admin/setup', 'views/admin/device_setup.php');



//sessions
$router->get('/admin/sessions', 'views/admin/sessions/index.php');
$router->get('/admin/sessions/manage', 'views/admin/sessions/manage.php');

// device
$router->get('/admin/bind-device', 'views/admin/bind-device.php');
$router->get('/admin/binding-codes', 'views/admin/binding-codes.php');

$router->get('/setup/device', 'views/setup/device.php');


$router->get('/student/cbt', 'views/student/cbt.php');
$router->get('/examiner/rubric', 'views/examiner/rubric.php');

// --- API ROUTES (JSON Controllers) ---
$router->post('/api/login', ['AuthController', 'handleLogin']);
$router->post('/api/sync', ['SyncController', 'handleOfflineData']);
$router->post('/api/exam/payload', ['ExamController', 'getPayload']);

// Dispatch the current request
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

$router->dispatch($requestUri, $requestMethod);