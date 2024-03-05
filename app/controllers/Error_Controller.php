<?php
namespace App\Controller {
    use Src\Controller\Base_Controller;

/*
 * File:    /src/controllers/Error_Controller.php
 * Purpose: Display error pages to browser
 */

    class Error_Controller extends Base_Controller
    {
        public function controller()
        {
            $controllerName = ucwords( $this->route->parameter[1] );

            $this->template->render(
                'error/controller.html.twig',
                array(
                    'controller' => $controllerName
                )
            );
        }

        public function index()
        {
            $this->template->render(
                'error/index.html.twig',
                array(
                    'message'   => 'Page Not Found',
                    'site_name' => 'Rhapsody Framework'
                )
            );
        }

        public function not_found()
        {
            print 'The page you were looking for does not exist';
        }
    }
}