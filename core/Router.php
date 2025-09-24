<?php

namespace Core;

class Router
{
    /**
     * The collection of registered routes.
     *
     * @var Route[]
     */
    protected static array $routes = [];

    /**
     * Registers a GET route.
     *
     * @param string $path
     * @param mixed  $callback
     *
     * @return void
     */
    public static function get( string $path, mixed $callback ): void
    {
        self::add( 'get', $path, $callback );
    }

    /**
     * Registers a POST route.
     *
     * @param string $path
     * @param mixed  $callback
     *
     * @return void
     */
    public static function post( string $path, mixed $callback ): void
    {
        self::add( 'post', $path, $callback );
    }

    /**
     * Adds a route to the static routing table.
     *
     * @param string $method
     * @param string $path
     * @param mixed  $callback
     *
     * @return void
     */
    protected static function add( string $method, string $path, mixed $callback ): void
    {
        self::$routes[] = new Route( $method, $path, $callback );
    }

    /**
     * Finds the matching route and dispatches it.
     *
     * @param Request $request
     *
     * @return Response
     */
    public static function dispatch( Request $request ): Response
    {
        $path   = $request->getPath();
        $method = $request->getMethod();

        foreach ( self::$routes as $route )
        {
            if ( $route->matches( $method, $path ) )
            {
                return self::execute( $route, $request );
            }
        }

        return self::handleNotFound();
    }

    /**
     * Executes the callback for a matched route.
     *
     * @param Route $route
     *
     * @return Response
     */
    protected static function execute( Route $route, Request $request ): Response
    {
        $callback = $route->getCallback();
        $params   = $route->getParams();

        if ( is_array( $callback ) )
        {
            $controller = new $callback[0]();
            $action     = $callback[1];

            // Prepend the request object to the list of arguments
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
     * Handles the case where no route is found.
     *
     * @return Response
     */
    protected static function handleNotFound(): Response
    {
        // 1. Create a new Response object
        $response = new Response();

        // 2. Configure it with a 404 status and content
        $response->setStatusCode( 404 );
        $response->setContent( "<h1>404 Not Found</h1>" );

        // 3. Return the configured object
        return $response;
    }
}
