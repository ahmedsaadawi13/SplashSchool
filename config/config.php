<?php
// FILE: /config/config.php

// Load environment variables
function loadEnv($path) {
    if (!file_exists($path)) {
        die('.env file not found');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '//') === 0 || strpos(trim($line), '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// Load .env file
loadEnv(dirname(__DIR__) . '/.env');

// Helper function to get environment variables
function env($key, $default = null) {
    return isset($_ENV[$key]) ? $_ENV[$key] : $default;
}

// Application Configuration
define('APP_NAME', env('APP_NAME', 'SplashSchool'));
define('APP_ENV', env('APP_ENV', 'development'));
define('APP_URL', env('APP_URL', 'http://localhost'));
define('APP_TIMEZONE', env('APP_TIMEZONE', 'UTC'));

// Database Configuration
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'splashschool_db'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');

// Path Configuration
define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/app');
define('CONFIG', ROOT . '/config');
define('PUBLIC_PATH', ROOT . '/public');
define('STORAGE', ROOT . '/storage');
define('UPLOADS', STORAGE . '/uploads');
define('LOGS', STORAGE . '/logs');

// URL Configuration
define('BASE_URL', rtrim(env('APP_URL', 'http://localhost'), '/'));

// Session Configuration
define('SESSION_LIFETIME', env('SESSION_LIFETIME', 7200));

// Security Configuration
define('CSRF_TOKEN_NAME', env('CSRF_TOKEN_NAME', 'csrf_token'));
define('ENCRYPTION_KEY', env('ENCRYPTION_KEY', 'change-this-key-in-production'));

// File Upload Configuration
define('MAX_UPLOAD_SIZE', env('MAX_UPLOAD_SIZE', 5242880)); // 5MB
define('ALLOWED_IMAGE_TYPES', explode(',', env('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif')));
define('ALLOWED_DOCUMENT_TYPES', explode(',', env('ALLOWED_DOCUMENT_TYPES', 'pdf,doc,docx,xls,xlsx')));

// Pagination
define('ITEMS_PER_PAGE', env('ITEMS_PER_PAGE', 20));

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Error reporting based on environment
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS . '/php_errors.log');
}
