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
        $config = require __DIR__ . '/../config.php';
        // ... (twig loader and environment code is the same)
        $loader = new FilesystemLoader( __DIR__ . '/../views' );
        $twig   = new Environment( $loader, ['debug' => true] );
        // Add the base_url from our config as a global variable in Twig.
        // Now, every single Twig template can access it using {{ base_url }}.
        $twig->addGlobal( 'base_url', $config['base_url'] );
        $output = $twig->render( $view, $args );

        $response = new Response();
        // Use the new setContent method
        $response->setContent( $output );
        return $response;
    }
}
