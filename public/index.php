<?php
// FILE: /public/index.php

// Start session
session_start();

// Load configuration
require_once '../config/config.php';

// Autoloader
spl_autoload_register(function ($class) {
    $paths = [
        APP . '/core/',
        APP . '/controllers/',
        APP . '/models/',
        APP . '/helpers/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Initialize and run the application
$app = new App();
