<?php

/**
 * Rhapsody Framework
 * Front Controller
 */

// 1. Register the Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// 2. Define the project root path for clarity and reliability.
$rootPath = dirname( __FILE__ );

// 3. Load environment variables from the .env file located at the project root.
try {
    $dotenv = Dotenv\Dotenv::createImmutable( $rootPath );
    $dotenv->load();
}
catch ( \Dotenv\Exception\InvalidPathException $e )
{
    die( 'Could not find .env file. Please ensure it exists in the project root.' );
}

// 4. Start the session
\Core\Session::start();

// 5. Use necessary core classes
use Core\Request;
use Core\Router;

// 6. Create the Request object
$request = new Request();

// 7. Load the application routes
require_once $rootPath . '/routes/api.php';
require_once $rootPath . '/routes/web.php';

// 8. Dispatch the request through the router
$response = Router::dispatch( $request );

// 9. Send the response back to the client
$response->send();
