<?php
namespace Core;

use App\Middleware\VerifyCsrfTokenMiddleware;
use Core\Exceptions\HttpException;

/**
 * The Rhapsody Router.
 *
 * Responsible for matching incoming requests to controller actions,
 * executing middleware, and using the service container to resolve controllers
 * and their dependencies.
 */
class Router
{
    /**
     * The collection of registered routes.
     * @var Route[]
     */
    protected static array $routes = [];

    /**
     * A map of middleware keys to their fully qualified class names.
     * @var array
     */
    protected static array $middlewareMap = [];

    /**
     * Global middleware runs on every matched request.
     * @var array
     */
    protected static array $globalMiddleware = [
        VerifyCsrfTokenMiddleware::class,
    ];

    /**
     * The route that was successfully matched.
     * @var Route|null
     */
    protected static ?Route $matchedRoute = null;

    /**
     * Set middleware configuration from the application.
     *
     * @param array $map Associative array of key => class name.
     * @param array $global List of global middleware class names.
     */
    public static function setMiddlewareConfig(array $map, array $global): void
    {
        self::$middlewareMap    = $map;
        self::$globalMiddleware = $global;
    }

    /**
     * Adds a new route to the collection.
     */
    protected static function add(string $method, string $path, mixed $callback): Route
    {
        $route          = new Route($method, $path, $callback);
        self::$routes[] = $route;
        return $route;
    }

    /**
     * Registers a GET route.
     */
    public static function get(string $path, mixed $callback): Route
    {
        return self::add('get', $path, $callback);
    }

    /**
     * Registers a POST route.
     */
    public static function post(string $path, mixed $callback): Route
    {
        return self::add('post', $path, $callback);
    }

    /**
     * Finds the matching route, executes middleware, and dispatches it.
     *
     * Global middleware runs only after a route is matched.
     * Middleware can short‑circuit by returning a Response object.
     *
     * @param Request   $request   The incoming request object.
     * @param Container $container The application's service container.
     * @return Response
     */
    public static function dispatch(Request $request, Container $container): Response
    {
        $path   = $request->getPath();
        $method = $request->getMethod();

        foreach (self::$routes as $route) {
            if ($route->matches($method, $path)) {
                self::$matchedRoute = $route;

                // Run global middleware (can return Response)
                foreach (self::$globalMiddleware as $middlewareClass) {
                    $middleware = $container->resolve($middlewareClass);
                    $response   = $middleware->handle($request);
                    if ($response instanceof Response) {
                        return $response;
                    }
                }

                // Run route‑specific middleware (resolved via container)
                $middlewareKey = $route->getMiddleware();
                if ($middlewareKey) {
                    $middlewareClass = self::$middlewareMap[$middlewareKey] ?? null;
                    if ($middlewareClass && class_exists($middlewareClass)) {
                        $middleware = $container->resolve($middlewareClass);
                        $response   = $middleware->handle($request);
                        if ($response instanceof Response) {
                            return $response;
                        }
                    }
                }

                return self::execute($route, $request, $container);
            }
        }

        return self::handleNotFound();
    }

    /**
     * Executes the controller action for a matched route.
     *
     * @param Route     $route     The matched route object.
     * @param Request   $request   The incoming request object.
     * @param Container $container The application's service container.
     * @return Response
     */
    protected static function execute(Route $route, Request $request, Container $container): Response
    {
        $callback = $route->getCallback();
        $params   = $route->getParams();

        if (is_array($callback)) {
            $controllerClass = $callback[0];
            $action          = $callback[1];

            $controller = $container->resolve($controllerClass);
            return $controller->{$action}($request, ...$params);
        }

        if ($callback instanceof \Closure) {
            $response = new Response();
            $response->setContent(call_user_func($callback, $request, ...$params));
            return $response;
        }

        return self::handleNotFound();
    }

    /**
     * Handles the case where no route is found.
     */
    protected static function handleNotFound(): Response
    {
        throw new HttpException(404, 'Page not found');
    }

    /**
     * Returns the last successfully matched route.
     */
    public static function getMatchedRoute(): ?Route
    {
        return self::$matchedRoute;
    }

    public static function getRoutes(): array
    {
        return self::$routes;
    }

    public static function setRoutes(array $routes): void
    {
        self::$routes = $routes;
    }
}
