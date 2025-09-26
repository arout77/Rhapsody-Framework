<?php

namespace App\Middleware;

use Core\Request;
use Core\Session;

class VerifyCsrfTokenMiddleware extends Middleware
{
    /**
     * @param Request $request
     * @return null
     */
    public function handle( Request $request ): void
    {
        // We only check non-GET requests
        if ( $request->getMethod() !== 'post' )
        {
            return;
        }

        $token = $request->getBody()['_token'] ?? null;

        if ( !Session::verifyCsrfToken( $token ) )
        {
            // If the token is invalid, abort with an error.
            // In a live app, you might show a "Page Expired" view.
            http_response_code( 419 );
            die( 'CSRF token mismatch.' );
        }
    }
}
