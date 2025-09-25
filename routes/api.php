<?php

use App\Controllers\ApiController;
use Core\Router;

// API routes can be prefixed for versioning, e.g., /api/v1
Router::get( '/api/users', [ApiController::class, 'getUsers'] );
Router::get( '/api/users/{id}', [ApiController::class, 'getUser'] );
