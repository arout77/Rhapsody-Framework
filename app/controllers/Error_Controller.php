<?php
namespace App\Controller;

use \Src\Controller\Base_Controller;

/*
 * File:    /src/controllers/Error_Controller.php
 * Purpose: Display error pages to browser
 */

class Error_Controller extends Base_Controller 
{
    public function index()
    {
        $this->template->render(
            'error/index.html.twig', 
            [
                'message' => 'Page Not Found',
                'site_name' => 'Rhapsody Framework'
            ]
        );
    }

    public function _404()
    {
        echo "The page you were looking for does not exist";
    }

    public function controller()
    {
        $controllerName = $this->route->parameter[1];
        echo ucwords($controllerName) . " Controller does not exist";
    }
}