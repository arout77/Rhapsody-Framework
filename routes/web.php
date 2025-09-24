<?php

use App\Controllers\PageController;
use Core\Router;

// Define your application routes using the static Router methods.
// This is the clean, intended way to define routes.

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

Router::get( '/register', [UserController::class, 'register'] );
Router::post( '/register', [UserController::class, 'registerProcess'] );

Router::get( '/upload', [App\Controllers\PageController::class, 'showUploadForm'] );
Router::post( '/upload', [App\Controllers\PageController::class, 'handleUpload'] );
