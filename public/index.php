<?php
require_once '../config.php';
require_once APPROOT . '/Core/Database.php';
require_once APPROOT . '/Core/Router.php';

// Helper for UUID generation (Crucial for offline architecture)
require_once APPROOT . '/Core/UuidHelper.php'; 

$router = new Router();

// --- WEB ROUTES (Views) ---
$router->get('/login', 'views/auth/login.php');
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