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
// These are the "recipes" for how to build essential objects.

// Database: We bind it as a singleton to ensure only one connection.
$container->bind( Database::class, fn() => Database::getInstance() );

// Twig Environment: Centralize the setup logic here.
$container->bind( Environment::class, function ()
{
    $config = require __DIR__ . '/config.php';
    $loader = new FilesystemLoader( __DIR__ . '/views' );
    $twig   = new Environment( $loader, [
        'cache' => __DIR__ . '/storage/cache',
        'debug' => true,
    ] );

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

    // --- ADD CSRF TWIG FUNCTION ---
    $twig->addFunction( new \Twig\TwigFunction( 'csrf_field', function ()
    {
        $token = \Core\Session::csrfToken();
        return new \Twig\Markup( '<input type="hidden" name="_token" value="' . $token . '">', 'UTF-8' );
    }, ['is_safe' => ['html']] ) );
    // --- END ---

    return $twig;
} );

// Other services can often be resolved automatically, but we can bind them for clarity.
$container->bind( Mailer::class );
$container->bind( Validator::class );

// 3. Return the fully configured container.
return $container;
