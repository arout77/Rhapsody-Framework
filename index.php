<?php
declare ( strict_types = 1 );

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

// 1. Register the Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// 2. Use necessary classes
use Core\Request;
use Core\Router;

// 3. Create the Request object
$request = new Request();

// 4. Load the application routes (populates the static Router)
require_once __DIR__ . '/routes/web.php';

// 5. Dispatch the request using the static method
$response = Router::dispatch( $request );

// 6. Send the response back to the client
$response->send();
