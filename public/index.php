<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// define('BASE_PATH', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$baseDir = rtrim(preg_replace('/\/public$/', '', $scriptDir), '/');
define('BASE_PATH', $baseDir);
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
$router->get('/admin/session-control', 'views/admin/sessions/session_control.php');

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


// The View Route


// The API Route to fetch the deep data

//admin
//dashboard
$router->get('/api/admin/stats', ['AdminController', 'getDashboardStats']);
$router->get('/api/admin/session-details', ['AdminController', 'getSessionDetails']);
//sessions
$router->get('/api/admin/departments', ['AdminController', 'getDepartments']);
$router->get('/api/admin/sessions', ['AdminController', 'getAllSessions']);
$router->post('/api/admin/sessions/save', ['AdminController', 'saveSession']);
$router->post('/api/admin/workspace/student/upload', ['SessionWorkspaceController', 'uploadBulkRoster']);
//sessions inner
$router->get('/api/admin/workspace/data', ['SessionWorkspaceController', 'getWorkspaceData']);
$router->post('/api/admin/workspace/student/save', ['SessionWorkspaceController', 'saveStudent']);
$router->post('/api/admin/workspace/student/remove', ['SessionWorkspaceController', 'removeStudent']);
$router->post('/api/admin/workspace/station/save', ['SessionWorkspaceController', 'saveStationConfig']);
$router->post('/api/admin/workspace/question/save', ['SessionWorkspaceController', 'saveSingleQuestion']);
$router->post('/api/admin/workspace/question/delete', ['SessionWorkspaceController', 'deleteSingleQuestion']);
$router->post('/api/admin/workspace/question/upload', ['SessionWorkspaceController', 'uploadBulkQuestions']);

//bindings
// Hardware Provisioning (Binding PINs) Routes
$router->get('/api/admin/hardware/data', ['BindingController', 'getBindingData']);
$router->post('/api/admin/hardware/generate', ['BindingController', 'generatePin']);
$router->post('/api/admin/hardware/toggle', ['BindingController', 'togglePin']);
$router->post('/api/admin/hardware/delete', ['BindingController', 'deletePin']);

// Gatekeeper PIN Verification Route
$router->post('/api/setup/verify-pin', ['BindingController', 'verifyPin']);
$router->get('/api/setup/download-payload', ['BindingController', 'downloadOfflinePayload']);


// ==========================================
// OFFLINE & SYNC API ROUTES
// ==========================================

// 1. Real-time background syncing of individual clicks/ticks
$router->post('/api/sync/tick', ['SyncController', 'logTick']);

// 2. Final submission payload (Single student submission or bulk admin push)
$router->post('/api/sync/cbt-score', ['SyncController', 'finalizeScore']);

// The Device Binding screen route
$router->get('/admin/bind-device', 'views/admin/bind-device.php');


// Admin Sync Dashboard
$router->get('/admin/sync', 'views/admin/sync-dashboard.php');


$router->get('/exam', 'views/offline-app/master.php');


// Dispatch the current request
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

$router->dispatch($requestUri, $requestMethod);