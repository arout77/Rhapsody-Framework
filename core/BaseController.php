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
     * @param string $view The view file to render.
     * @param array  $args Associative array of data to pass to the view.
     * @param array  $meta SEO metadata for the page (e.g., ['title' => 'My Title']).
     * @return Response
     */
    protected function view( string $view, array $args = [], array $meta = [] ): Response
    {
        // Add default values for meta tags
        $defaults = [
            'title'       => 'Step into the ring with IWF - The Ultimate Online Wrestling Simulator',
            'description' => 'Create dream matches, play through a career mode, and book your own shows with a roster of wrestling legends.',
        ];
        $args['meta'] = array_merge( $defaults, $meta );

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
