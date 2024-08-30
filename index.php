<?php
header('Location: login.php');
session_start();

// Function to sanitize and retrieve the request URI
function getRequestUri() {
    $uri = $_SERVER['REQUEST_URI'];
    $uri = explode('?', $uri, 2)[0]; // Remove any query string
    $uri = rtrim($uri, '/'); // Normalize trailing slash
    return $uri;
}

$requestUri = getRequestUri();

// Define  routes and their corresponding file names, setting login.php as the default
$routes = [
    '' => 'login.php', // Now pointing to login.php as the homepage
    '/register' => 'register.php',
    '/productivity' => 'productivity.php',
    '/knowledge' => 'knowledge.php',
    '/manager' => 'manager.php',
    '/change-password' => 'change-password.php',
];

// Routing logic
if (array_key_exists($requestUri, $routes)) {
    include $routes[$requestUri];
} else {
    http_response_code(404);
    include('404.php');
    exit;
}

