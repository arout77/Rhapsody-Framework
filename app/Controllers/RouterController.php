<?php
namespace App\Controllers;

use Core\Container;
use Core\Exceptions\HttpException;
use Core\Request;

class RouterController extends \Core\Router
{
    public static function dispatch(Request $request, Container $container): \Core\Response
    {
        $response = parent::dispatch($request, $container);

        // Convert 404 responses into exceptions so the error handler can show a custom page
        if ($response->getStatusCode() === 404) {
            throw new HttpException(404, 'Page not found');
        }

        if ($response->getStatusCode() === 500) {
            throw new HttpException(500, 'Server error');
        }

        return $response;
    }
}
