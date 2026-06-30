<?php
declare(strict_types=1);

// Error reporting settings
require_once __DIR__ . '/../config.php';

if (DEV_MODE) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Load Composer Autoloader
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die("Spusťte prosím 'composer install' pro instalaci závislostí.");
}
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Request;
use App\Core\Router;
use App\Controllers\AdminController;

// Start session for administrators
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$request = new Request();
$router = new Router();

// Redirect base /admin to /admin/dashboard
$router->get('/admin', function(Request $req) {
    header('Location: /admin/dashboard');
    exit;
});

// Authentication
$router->get('/admin/login', [AdminController::class, 'login']);
$router->post('/admin/login', [AdminController::class, 'login']);
$router->get('/admin/logout', [AdminController::class, 'logout']);

// Main Sections
$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/settings', [AdminController::class, 'settings']);

// Trips Section
$router->get('/admin/trips', [AdminController::class, 'trips']);
$router->get('/admin/trips/add', [AdminController::class, 'addTrip']);
$router->post('/admin/trips/add', [AdminController::class, 'addTrip']);
$router->get('/admin/trips/edit/{id}', [AdminController::class, 'editTrip']);
$router->post('/admin/trips/edit/{id}', [AdminController::class, 'editTrip']);
$router->get('/admin/trips/delete/{id}', [AdminController::class, 'deleteTrip']);

// Blog Posts Section
$router->get('/admin/posts', [AdminController::class, 'posts']);
$router->get('/admin/posts/add', [AdminController::class, 'addPost']);
$router->post('/admin/posts/add', [AdminController::class, 'addPost']);
$router->get('/admin/posts/edit/{id}', [AdminController::class, 'editPost']);
$router->post('/admin/posts/edit/{id}', [AdminController::class, 'editPost']);
$router->get('/admin/posts/delete/{id}', [AdminController::class, 'deletePost']);

// Comments Management
$router->get('/admin/comments', [AdminController::class, 'comments']);
$router->post('/admin/comments', [AdminController::class, 'comments']);

// Administrators Section
$router->get('/admin/admins', [AdminController::class, 'admins']);
$router->post('/admin/admins', [AdminController::class, 'admins']);

// 404 handler
$router->setNotFound(function(Request $req) {
    http_response_code(404);
    echo "Stránka v administraci nebyla nalezena (404)";
});

// Resolve the route
$router->resolve($request);
