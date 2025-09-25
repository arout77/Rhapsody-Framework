<?php

namespace Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class BaseController
{
    /**
     * Renders a view file using Twig.
     *
     * @param string $view The view file to render (e.g., 'home.twig').
     * @param array  $args Associative array of data to pass to the view.
     *
     * @return Response
     */
    protected function view( string $view, array $args = [] ): Response
    {
        $config   = require __DIR__ . '/../config.php';
        $loader   = new FilesystemLoader( __DIR__ . '/../views' );
        $cacheDir = __DIR__ . '/../storage/cache';
        if ( !is_dir( $cacheDir ) )
        {
            mkdir( $cacheDir, 0755 );
        }

        $twig = new Environment( $loader, [
            'cache' => $cacheDir, // Enable the cache
            'debug' => true,
        ] );

        // Add the base_url from our config as a global variable in Twig.
        // Now, every single Twig template can access it using {{ base_url }}.
        $twig->addGlobal( 'base_url', $config['base_url'] );
        $output = $twig->render( $view, $args );

        $response = new Response();
        // Use the new setContent method
        $response->setContent( $output );
        return $response;
    }

    /**
     * Creates and returns a JSON response.
     *
     * @param array $data The data to be encoded as JSON.
     * @param int $statusCode The HTTP status code for the response (defaults to 200 OK).
     * @return Response
     */
    protected function json( array $data, int $statusCode = 200 ): Response
    {
        $response = new Response();
        $response->setStatusCode( $statusCode );
        $response->setHeader( 'Content-Type', 'application/json' );
        $response->setContent( json_encode( $data, JSON_PRETTY_PRINT ) ); // JSON_PRETTY_PRINT makes it readable
        return $response;
    }
}
