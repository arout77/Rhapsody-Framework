<?php

// bootstrap.php

use Core\Cache;
use Core\Container;
use Core\Mailer;
use Core\Session;
use Core\Validator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// 1. Create a new Service Container instance.
$container = new Container();
$config    = require __DIR__ . '/config.php';

// 2. Bind core services into the container.
$container->bind( Environment::class, function ( Container $c ) use ( $config )
{
    $loader = new FilesystemLoader( __DIR__ . '/views' );
    $twig   = new Environment( $loader, ['debug' => true] );

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

    // Make the update notification available to all Twig templates.
    $twig->addGlobal( 'update_available', Cache::get( 'update_available' ) );

    $twig->addFunction( new \Twig\TwigFunction( 'csrf_field', function ()
    {
        $token = \Core\Session::csrfToken();
        return new \Twig\Markup( '<input type="hidden" name="_token" value="' . $token . '">', 'UTF-8' );
    } ) );

    return $twig;
} );

// Bind other core services
$container->bind( Mailer::class );
$container->bind( Validator::class );
$container->bind( Cache::class );
$container->bind( \Core\Request::class, fn() => new \Core\Request() );

// Bind commands that have constructor dependencies
$container->bind( App\Commands\UpdateCommand::class, function () use ( $config )
{
    return new App\Commands\UpdateCommand( $config );
} );

$container->bind( App\Commands\CheckVersionCommand::class, function ( $c ) use ( $config )
{
    return new App\Commands\CheckVersionCommand(
        $config,
        $c->resolve( Mailer::class ),
        $c->resolve( Cache::class )
    );
} );

// 3. Return the fully configured container.
return $container;
