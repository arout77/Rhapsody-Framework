<?php

// bootstrap.php

use App\Services\NotificationService;
use Core\Cache;
use Core\Cache\CacheInterface;
use Core\Cache\FileCacheDriver;
use Core\Cache\RedisCacheDriver;
use Core\Container;
use Core\Mailer;
use Core\Session;
use Core\Validator;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Predis\Client as RedisClient;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// 1. Create a new Service Container instance.
$container = new Container();
$config    = require __DIR__ . '/config.php';

// --- DOCTRINE ENTITY MANAGER BINDING ---
$container->bind( EntityManager::class, function () use ( $config ) {
    $paths          = [__DIR__ . '/app/Entities'];
    $isDevMode      = ( $config['app_env'] ?? 'production' ) === 'development';
    $cache          = $isDevMode ? new ArrayAdapter() : new FilesystemAdapter( '', 0, __DIR__ . '/storage/cache/doctrine' );
    $doctrineConfig = ORMSetup::createAttributeMetadataConfiguration( $paths, $isDevMode, null, $cache );
    $dbParams       = [
        'driver'   => 'pdo_mysql',
        'host'     => $_ENV['DB_HOST'],
        'user'     => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASS'],
        'dbname'   => $_ENV['DB_NAME'],
        'charset'  => 'utf8mb4',
    ];
    $connection = DriverManager::getConnection( $dbParams, $doctrineConfig );
    return new EntityManager( $connection, $doctrineConfig );
} );

// --- CACHE SYSTEM BINDING ---
$container->bind( CacheInterface::class, function () use ( $config ) {
    if ( $config['cache']['driver'] === 'redis' ) {
        $redisClient = new RedisClient( [
            'scheme'   => 'tcp',
            'host'     => $config['redis']['host'],
            'port'     => $config['redis']['port'],
            'password' => $config['redis']['password'] ?: null,
        ] );
        return new RedisCacheDriver( $redisClient );
    }
    return new FileCacheDriver();
} );

$container->bind( Cache::class, function ( Container $c ) {
    return new Cache( $c->resolve( CacheInterface::class ) );
} );

// --- TWIG BINDING ---
$container->bind( Environment::class, function ( Container $c ) use ( $config ) {
    $loader = new FilesystemLoader( __DIR__ . '/views' );
    $twig   = new Environment( $loader, ['debug' => true] );
    $twig->addGlobal( 'app_url', $_ENV['APP_URL'] ?? '' );
    $auth = [
        'check' => Session::has( 'user_id' ),
        'user'  => Session::has( 'user_id' ) ? ( new \App\Models\User() )->getUserById( Session::get( 'user_id' ) ) : null,
    ];
    $twig->addGlobal( 'auth', $auth );
    $twig->addGlobal( 'base_url', $config['base_url'] );
    $twig->addGlobal( 'flash', ['success' => Session::getFlash( 'success' ), 'error' => Session::getFlash( 'error' )] );

    $cache = $c->resolve( Cache::class );
    $twig->addGlobal( 'update_available', $cache->get( 'update_available' ) );

    $twig->addFunction( new \Twig\TwigFunction( 'csrf_field', function () {
        $token = \Core\Session::csrfToken();
        return new \Twig\Markup( '<input type="hidden" name="_token" value="' . $token . '">', 'UTF-8' );
    } ) );
    return $twig;
} );

// --- OTHER CORE SERVICES ---
$container->bind( Mailer::class );
$container->bind( Validator::class );
$container->bind( \Core\Request::class, fn() => new \Core\Request() );
$container->bind( NotificationService::class, function ( Container $c ) {
    return new NotificationService( $c->resolve( Cache::class ) );
} );

// --- COMMAND BINDINGS ---
$container->bind( App\Commands\UpdateCommand::class, function () use ( $config ) {
    return new App\Commands\UpdateCommand( $config );
} );

$container->bind( App\Commands\CheckVersionCommand::class, function ( $c ) use ( $config ) {
    return new App\Commands\CheckVersionCommand( $config, $c->resolve( Mailer::class ), $c->resolve( Cache::class ) );
} );

// 3. Return the fully configured container.
return $container;
