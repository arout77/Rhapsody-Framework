<?php

namespace Core;

use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\Middleware;

class Router
{
    protected static array $routes = [];

    protected static array $middlewareMap = [
        'auth'  => AuthMiddleware::class,
        'guest' => GuestMiddleware::class,
    ];

    /**
     * Adds a route and returns the Route object for chaining.
     */
    protected static function add( string $method, string $path, mixed $callback ): Route
    {
        $route          = new Route( $method, $path, $callback );
        self::$routes[] = $route;
        return $route; // <-- This return is essential
    }

    /**
     * --- THIS IS THE FIX ---
     * The return type is changed from 'void' to 'Route'.
     */
    public static function get( string $path, mixed $callback ): Route
    {
        return self::add( 'get', $path, $callback );
    }

    /**
     * --- THIS IS THE FIX ---
     * The return type is changed from 'void' to 'Route'.
     */
    public static function post( string $path, mixed $callback ): Route
    {
        return self::add( 'post', $path, $callback );
    }

    /**
     * Dispatches the request, resolving and executing middleware first.
     */
    public static function dispatch( Request $request ): Response
    {
        $path   = $request->getPath();
        $method = $request->getMethod();

        foreach ( self::$routes as $route )
        {
            if ( $route->matches( $method, $path ) )
            {
                $middlewareKey = $route->getMiddleware();

                if ( $middlewareKey )
                {
                    $middlewareClass = self::$middlewareMap[$middlewareKey] ?? null;
                    if ( $middlewareClass && class_exists( $middlewareClass ) )
                    {
                        $middleware = new $middlewareClass();
                        $middleware->handle( $request );
                    }
                }

                return self::execute( $route, $request );
            }
        }

        return self::handleNotFound();
    }

    /**
     * @param Route $route
     * @param Request $request
     * @return mixed
     */
    protected static function execute( Route $route, Request $request ): Response
    {
        $callback = $route->getCallback();
        $params   = $route->getParams();

        if ( is_array( $callback ) )
        {
            $controller = new $callback[0]();
            $action     = $callback[1];
            return $controller->{$action}( $request, ...$params );
        }

        if ( $callback instanceof \Closure )
        {
            $response = new Response();
            $response->setContent( call_user_func( $callback, $request, ...$params ) );
            return $response;
        }

        return self::handleNotFound();
    }

    /**
     * @return mixed
     */
    protected static function handleNotFound(): Response
    {
        $response = new Response();
        $response->setStatusCode( 404 );
        $response->setContent( "<h1>404 Not Found</h1>" );
        return $response;
    }
}
