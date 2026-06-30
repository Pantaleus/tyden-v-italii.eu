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
use App\Core\Language;
use App\Core\Tracker;
use App\Controllers\PublicController;

// Initialize language settings (CZ/EN/IT)
Language::init();

// Create request & run visitor tracker
$request = new Request();
Tracker::track($request);

// Setup Router & register public routes
$router = new Router();

$router->get('/', [PublicController::class, 'home']);
$router->get('/trips', [PublicController::class, 'trips']);
$router->get('/trips/{id}', [PublicController::class, 'tripDetail']);
$router->get('/blog', [PublicController::class, 'blog']);
$router->get('/blog/{slug}', [PublicController::class, 'blogPost']);
$router->post('/blog/{slug}', [PublicController::class, 'blogPost']);
$router->get('/contact', [PublicController::class, 'contact']);
$router->post('/contact', [PublicController::class, 'contact']);

// Tracker resolution endpoint
$router->post('/track-screen', [PublicController::class, 'trackScreen']);

// SEO Dynamic Files
$router->get('/robots.txt', [PublicController::class, 'robots']);
$router->get('/sitemap.xml', [PublicController::class, 'sitemap']);

// API Endpoints for Mobile Application
use App\Controllers\ApiController;
$router->post('/api/login', [ApiController::class, 'login']);
$router->post('/api/qr-login', [ApiController::class, 'qrLogin']);
$router->get('/api/stats', [ApiController::class, 'stats']);
$router->get('/api/trips', [ApiController::class, 'getTrips']);
$router->post('/api/trips', [ApiController::class, 'createTrip']);
$router->post('/api/trips/{id}', [ApiController::class, 'updateTrip']); // Use POST as fallback for PUT in React Native multipart forms
$router->get('/api/posts', [ApiController::class, 'getPosts']);
$router->post('/api/posts', [ApiController::class, 'createPost']);
$router->post('/api/posts/{id}', [ApiController::class, 'createPost']); // Edit post
$router->get('/api/comments', [ApiController::class, 'getComments']);
$router->post('/api/comments/{id}/approve', [ApiController::class, 'approveComment']);
$router->post('/api/comments/{id}/spam', [ApiController::class, 'spamComment']);
$router->post('/api/comments/{id}/delete', [ApiController::class, 'deleteComment']);
$router->post('/api/register-push', [ApiController::class, 'registerPushToken']);
$router->post('/api/upload', [ApiController::class, 'upload']);


// 404 handler
$router->setNotFound(function(Request $req) {
    http_response_code(404);
    $controller = new PublicController();
    $controller->home($req); // Fallback or render custom 404 inside home/layout
});

// Resolve the route
$router->resolve($request);
