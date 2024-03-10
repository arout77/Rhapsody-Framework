<?php
namespace App\Controller {
    use Src\Controller\Base_Controller;

    class Documentation_Controller extends Base_Controller
    {
        public function index()
        {
            $this->template->render(
                'docs/index.html.twig',
                array(
                    'message'   => 'Page Not Found',
                    'site_name' => 'Rhapsody Framework'
                 )
            );
        }

        public function getting_started()
        {
            $page = $this->route->parameter[1];

            $this->template->render(
                'docs/getting-started/'.$page.'.html.twig',
                array(
                    'message'   => 'Page Not Found',
                )
            );
        }

        public function components()
        {
            $page = $this->route->parameter[1];

            $this->template->render(
                'docs/components/'.$page.'.html.twig',
                array(
                    'message'   => 'Page Not Found',
                )
            );
        }
    }
}
