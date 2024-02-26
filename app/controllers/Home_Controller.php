<?php

namespace App\Controller;

use \Src\Controller\Base_Controller;

class Home_Controller extends Base_Controller {

    public function index()
    {
        $this->template->render(
            'home/index.html.twig', 
            [
                'message' => 'Page Not Found',
                'site_name' => 'Rhapsody Framework'
            ]
        );
    }
}