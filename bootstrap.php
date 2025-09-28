<?php

// bootstrap.php

use Core\Container;
use Core\Database;
use Core\Mailer;
use Core\Session;
use Core\Validator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// 1. Create a new Service Container instance.
$container = new Container();

// 2. Bind core services into the container.

// Twig Environment: This closure now accepts the container as an argument.
$container->bind( Environment::class, function ( Container $c )
{ // <-- THE FIX: Accept the container as '$c'
    $config = require __DIR__ . '/config.php';
    $loader = new FilesystemLoader( __DIR__ . '/views' );
    $twig   = new Environment( $loader, [
        // 'cache' => __DIR__ . '/storage/cache',
        'debug' => true,
    ] );

    // Make the current request object available in Twig
    $twig->addGlobal( 'app', [
        'request' => $c->resolve( \Core\Request::class ), // <-- THE FIX: Use '$c' instead of '$container'
    ] );

    // Add APP_URL from the .env file as a global variable
    $twig->addGlobal( 'app_url', $_ENV['APP_URL'] ?? '' );

    $auth = [
        'check' => Session::has( 'user_id' ),
        'user'  => Session::has( 'user_id' ) ? ( new \App\Models\User() )->getUserById( Session::get( 'user_id' ) ) : null,
    ];
    $twig->addGlobal( 'auth', $auth );
    $twig->addGlobal( 'base_url', $config['base_url'] );
    $twig->addGlobal( 'flash', [
        'success' => Session::getFlash( 'success' ),
        'error'   => Session::getFlash( 'error' ),
    ] );

    $twig->addFunction( new \Twig\TwigFunction( 'csrf_field', function ()
{
        $token = \Core\Session::csrfToken();
        return new \Twig\Markup( '<input type="hidden" name="_token" value="' . $token . '">', 'UTF-8' );
    } ) );

    return $twig;
} );

// Other services can be auto-resolved, but we can bind them for clarity or if they need special setup.
$container->bind( Mailer::class );
$container->bind( Validator::class );
$container->bind( \Core\Request::class, fn() => new \Core\Request() ); // Bind Request as a singleton

// 3. Return the fully configured container.
return $container;
