<?php
// Database Constants
// define('DB_HOST', 'localhost');
// define('DB_USER', 'root'); //on_God_ca_os_skill
// define('DB_PASS', ''); //X7gtMEd7!@nlqMi1
// define('DB_NAME', 'ca_os_skill');

$is_local = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);

if ($is_local) {
    // ==========================================
    // LOCAL DEVELOPMENT CREDENTIALS
    // ==========================================
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'ca_os_skill');
    define('URLROOT', 'http://localhost/caosce_app'); // Update to your local path
} else {
    // ==========================================
    // LIVE PRODUCTION CREDENTIALS
    // ==========================================
    define('DB_HOST', 'localhost'); // On cPanel, the host is usually still 'localhost'
    define('DB_USER', 'on_God_ca_os_skill');
    define('DB_PASS', 'X7gtMEd7!@nlqMi1');
    define('DB_NAME', 'ca_os_skill');
    define('URLROOT', 'https://caosce.com');
}

// App Constants
define('APPROOT', dirname(__FILE__) . '/app');
// define('URLROOT', 'https://caosce.com');
define('APP_NAME', 'CaOSCE');
define('APP_CAPTION', 'Clinical & Objective Structured Examination Environment');
define('APP_LOGO_PATH', '/assets/img/default-logo.png');


// --- SMTP EMAIL CONFIGURATION ---
define('SMTP_HOST', 'smtp.gmail.com'); // e.g., smtp.gmail.com or your cPanel mail server
define('SMTP_PORT', 465); // 465 for SSL, 587 for TLS
define('SMTP_USER', 'promailerclient@gmail.com'); // Your official email address
define('SMTP_PASS', 'yusuklvvlqwntmrk');
define('SYSTEM_EMAIL', 'amfstacks@gmail.com'); // Where admin notifications go
define('SYSTEM_NAME', 'CAOSCE Systems');
