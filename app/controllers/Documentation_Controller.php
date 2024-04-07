<?php
namespace App\Controller {
    use Src\Controller\Base_Controller;

    class Documentation_Controller extends Base_Controller
    {
        public function index()
        {
            $this->template->render( 'docs/index.html.twig',[
                    'message'   => 'Page Not Found',
                    'site_name' => 'Rhapsody Framework'
            ]);
        }

        public function architecture()
        {
            $page = $this->route->parameter[1];

            $db = $this->model("Documentation");
            
            // Save this page to DB if it isn't already
            if( empty( $db->getDocPage( "Architecture", ucwords($page) ) ) ) 
            {
                $db->addDocPage( "Architecture", ucwords($page) );
            }

            $this->template->render(
                'docs/architecture/'.$page.'.html.twig'
            );
        }

        public function components()
        {
            $page = $this->route->parameter[1];

            $db = $this->model("Documentation");
            
            // Save this page to DB if it isn't already
            if( empty( $db->getDocPage( "Components", ucwords($page) ) ) ) 
            {
                $db->addDocPage( "Components", ucwords($page) );
            }

            $this->template->render(
                'docs/components/'.$page.'.html.twig'
            );
        }
        
        public function getting_started()
        {
            $page = $this->route->parameter[1];

            $db = $this->model("Documentation");
            
            // Save this page to DB if it isn't already
            if( empty( $db->getDocPage( "Getting Started", ucwords($page) ) ) ) 
            {
                $db->addDocPage( "Getting Started", ucwords($page) );
            }

            $page = $this->route->parameter[1];

            $this->template->render(
                'docs/getting-started/'.$page.'.html.twig'
            );
        }

        public function middleware()
        {
            $page = $this->route->parameter[1];

            $db = $this->model("Documentation");
            
            // Save this page to DB if it isn't already
            if( empty( $db->getDocPage( "Middleware", ucwords($page) ) ) ) 
            {
                $db->addDocPage( "Middleware", ucwords($page) );
            }

            $this->template->render(
                'docs/middleware/'.$page.'.html.twig'
            );
        }
    }
}
