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
            $params = explode("-", $this->route->parameter[1]);
            $controllerName = ucwords( $params[0] );
            $methodName = $params[1];

            $this->template->render( 'error/controller.html.twig',
                [
                    'controller' => $controllerName,
                    'method'     => $methodName,
                ]
            );
        }

        public function method()
        {
            $params = explode("-", $this->route->parameter[1]);
            $controllerName = ucwords( $params[0] );
            $methodName = $params[1];

            $this->template->render( 'error/method.html.twig',
                [
                    'controller' => $controllerName,
                    'method'     => $methodName,
                ]
            );
        }

        public function index()
        {
            $this->template->render('error/index.html.twig');
        }

        public function not_found()
        {
            $this->template->render('error/index.html.twig');
        }
    }
}