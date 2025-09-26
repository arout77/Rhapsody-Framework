<?php

namespace Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

abstract class BaseController
{
    // The Twig environment is now a property injected by the container.
    protected Environment $twig;

    /**
     * @param Environment $twig
     */
    public function __construct( Environment $twig )
    {
        $this->twig = $twig;
    }

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
        $output = $this->twig->render( $view, $args );

        $response = new Response();
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
