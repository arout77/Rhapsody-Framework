<?php

namespace App\Controllers;

use Core\BaseController;
use Core\Response;

/**
 * Handles rendering of the framework documentation pages.
 */
class DocsController extends BaseController
{
    /**
     * Shows the main documentation index page.
     */
    public function index(): Response
    {
        return $this->view( 'docs/index.twig' );
    }

    /**
     * Shows the installation and setup guide.
     */
    public function installation(): Response
    {
        return $this->view( 'docs/installation.twig' );
    }

    /**
     * Shows the routing documentation.
     */
    public function routing(): Response
    {
        return $this->view( 'docs/routing.twig' );
    }

    /**
     * Shows the controllers documentation.
     */
    public function controllers(): Response
    {
        return $this->view( 'docs/controllers.twig' );
    }

    /**
     * Shows the models and database documentation.
     */
    public function models(): Response
    {
        return $this->view( 'docs/models.twig' );
    }

    /**
     * Shows the views and templating documentation.
     */
    public function views(): Response
    {
        return $this->view( 'docs/views.twig' );
    }

    /**
     * Shows the validation documentation.
     */
    public function validation(): Response
    {
        return $this->view( 'docs/validation.twig' );
    }

    /**
     * Shows the authentication and middleware documentation.
     */
    public function middleware(): Response
    {
        return $this->view( 'docs/middleware.twig' );
    }
}
