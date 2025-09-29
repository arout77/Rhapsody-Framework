<?php

use App\Controllers\AuthController;
use App\Controllers\DocsController;
use App\Controllers\PageController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use Core\Router;

// Define your application routes using the static Router methods.

// --- The routes below can be viewed by visitors and logged in users
// --- DOCUMENTATION ROUTES ---
Router::get( '/docs', [DocsController::class, 'index'] );
Router::get( '/docs/installation', [DocsController::class, 'installation'] );
Router::get( '/docs/routing', [DocsController::class, 'routing'] );
Router::get( '/docs/controllers', [DocsController::class, 'controllers'] );
Router::get( '/docs/models', [DocsController::class, 'models'] );
Router::get( '/docs/views', [DocsController::class, 'views'] );
Router::get( '/docs/validation', [DocsController::class, 'validation'] );
Router::get( '/docs/middleware', [DocsController::class, 'middleware'] );
Router::get( '/docs/cli', [DocsController::class, 'cli'] );
Router::get( '/docs/mailer', [DocsController::class, 'mailer'] );
Router::get( '/docs/seo', [DocsController::class, 'seo'] );
Router::get( '/docs/pagination', [DocsController::class, 'pagination'] );
Router::get( '/docs/file-uploader', [DocsController::class, 'fileUploader'] );
Router::get( '/docs/performance', [DocsController::class, 'performance'] );
Router::get( '/docs/security', [DocsController::class, 'security'] );
Router::get( '/docs/updating', [DocsController::class, 'updating'] );

Router::get( '/', [PageController::class, 'index'] );
Router::get( '/about', [PageController::class, 'about'] );
Router::get( '/contact', [App\Controllers\PageController::class, 'contact'] );
Router::post( '/contact', [App\Controllers\PageController::class, 'handleContact'] );

Router::get( '/sitemap.xml', [SitemapController::class, 'generate'] );

// You can also still use closures for simple, one-off routes if you wish.
Router::get( '/hello', function ()
{
    return "<h1>Hello, World!</h1>";
} );

Router::get( '/logout', [AuthController::class, 'logout'] );

// This will match URLs like /posts/hello-world or /posts/123
Router::get( '/posts/{slug}', [PageController::class, 'showPost'] );

// --- PROTECTED ROUTES ---
// This route should only be accessible to authenticated users.
Router::get( '/dashboard', [PageController::class, 'dashboard'] )->middleware( 'auth' );
Router::get( '/upload', [App\Controllers\PageController::class, 'showUploadForm'] )->middleware( 'auth' );
Router::post( '/upload', [App\Controllers\PageController::class, 'handleUpload'] )->middleware( 'auth' );
Router::get( '/users', [PageController::class, 'showUsers'] );
Router::get( '/users/{user_id}', [PageController::class, 'viewUser'] );

// These routes should only be accessible to guests.
Router::get( '/login', [AuthController::class, 'showLoginForm'] )->middleware( 'guest' );
Router::post( '/login', [AuthController::class, 'login'] )->middleware( 'guest' );
Router::get( '/register', [AuthController::class, 'showRegisterForm'] )->middleware( 'guest' );
Router::post( '/register', [AuthController::class, 'register'] )->middleware( 'guest' );
