<?php

namespace App\Controllers;

use Core\BaseController;
use Core\Response;
use Twig\Environment;

/**
 * Handles rendering of the framework documentation pages.
 */
class DocsController extends BaseController
{
    /**
     * @param Environment $twig
     */
    public function __construct( Environment $twig )
    {
        parent::__construct( $twig );
    }

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

    /**
     * @return mixed
     */
    public function cli(): Response
    {
        return $this->view( 'docs/cli.twig' );
    }

    /**
     * @return mixed
     */
    public function mailer(): Response
    {
        return $this->view( 'docs/mailer.twig' );
    }

    /**
     * @return mixed
     */
    public function seo(): Response
    {
        return $this->view( 'docs/seo.twig' );
    }

    /**
     * @return mixed
     */
    public function pagination(): Response
    {
        return $this->view( 'docs/pagination.twig' );
    }

    /**
     * @return mixed
     */
    public function fileUploader(): Response
    {
        return $this->view( 'docs/file-uploader.twig' );
    }

    /**
     * @return mixed
     */
    public function security(): Response
    {
        return $this->view( 'docs/security.twig' );
    }

    /**
     * @return mixed
     */
    public function performance(): Response
    {
        return $this->view( 'docs/performance.twig' );
    }
}
