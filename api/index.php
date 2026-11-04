<?php
/**
 * Vercel Serverless Entry Point & Router for StudentHub
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($uri, '/');

// Default home
if (empty($path)) {
    require __DIR__ . '/../index.php';
    exit;
}

// Check direct file with extension
$file = dirname(__DIR__) . '/' . $path;
if (file_exists($file) && is_file($file)) {
    require $file;
    exit;
}

// Check without .php extension
if (file_exists($file . '.php')) {
    require $file . '.php';
    exit;
}

// 404 Fallback
http_response_code(404);
require __DIR__ . '/../index.php';

