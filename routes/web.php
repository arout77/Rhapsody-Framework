<?php

use App\Controllers\AuthController;
use App\Controllers\DocsController;
use App\Controllers\PageController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use Core\Router;

// Define your application routes using the static Router methods.
// This is the clean, intended way to define routes.

// --- DOCUMENTATION ROUTES ---
Router::get( '/docs', [DocsController::class, 'index'] );
Router::get( '/docs/installation', [DocsController::class, 'installation'] );
Router::get( '/docs/routing', [DocsController::class, 'routing'] );
Router::get( '/docs/controllers', [DocsController::class, 'controllers'] );
Router::get( '/docs/models', [DocsController::class, 'models'] );
Router::get( '/docs/views', [DocsController::class, 'views'] );
Router::get( '/docs/validation', [DocsController::class, 'validation'] );
Router::get( '/docs/middleware', [DocsController::class, 'middleware'] );

Router::get( '/', [PageController::class, 'index'] );
Router::get( '/about', [PageController::class, 'about'] );

// This GET route will DISPLAY the contact form.
Router::get( '/contact', [App\Controllers\PageController::class, 'contact'] );
// This POST route will PROCESS the submitted form data.
Router::post( '/contact', [App\Controllers\PageController::class, 'handleContact'] );

// You can also still use closures for simple, one-off routes if you wish.
Router::get( '/hello', function ()
{
    return "<h1>Hello, World!</h1>";
} );

// This will match URLs like /posts/hello-world or /posts/123
Router::get( '/posts/{slug}', [PageController::class, 'showPost'] );

Router::get( '/users', [PageController::class, 'showUsers'] );
Router::get( '/users/{user_id}', [PageController::class, 'viewUser'] );

Router::get( '/upload', [App\Controllers\PageController::class, 'showUploadForm'] );
Router::post( '/upload', [App\Controllers\PageController::class, 'handleUpload'] );

// --- PROTECTED ROUTES ---
// This route should only be accessible to authenticated users.
Router::get( '/dashboard', [PageController::class, 'dashboard'] )->middleware( 'auth' );
Router::get( '/upload', [App\Controllers\PageController::class, 'showUploadForm'] )->middleware( 'auth' );
Router::post( '/upload', [App\Controllers\PageController::class, 'handleUpload'] )->middleware( 'auth' );

// These routes should only be accessible to guests.
Router::get( '/login', [AuthController::class, 'showLoginForm'] )->middleware( 'guest' );
Router::post( '/login', [AuthController::class, 'login'] )->middleware( 'guest' );
Router::get( '/register', [AuthController::class, 'showRegisterForm'] )->middleware( 'guest' );
Router::post( '/register', [AuthController::class, 'register'] )->middleware( 'guest' );
