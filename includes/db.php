<?php
// includes/db.php

// 1. Check if we are on Railway by looking for the DATABASE_URL environment variable
$databaseUrl = getenv('DATABASE_URL');

if ($databaseUrl) {
    // --- RAILWAY CONFIGURATION ---
    $url = parse_url($databaseUrl);
    
    define('DB_HOST', $url['host']);
    define('DB_PORT', $url['port']);
    define('DB_NAME', ltrim($url['path'], '/'));
    define('DB_USER', $url['user']);
    define('DB_PASS', $url['pass']);
} else {
    // --- LOCAL CONFIGURATION (Laragon) ---
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', '5432');
    define('DB_NAME', 'bright');
    define('DB_USER', 'postgres');
    define('DB_PASS', 'icecream');
}

global $pdo;

try {
    // Create PDO connection using the defined constants
    $pdo = new PDO(
        "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // On Railway, if this fails, check your "Variables" tab
    die("Connection failed: " . $e->getMessage());
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}