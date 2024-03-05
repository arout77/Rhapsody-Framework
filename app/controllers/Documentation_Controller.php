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

        public function test()
        {
            $this->template->render(
                'docs/test.html.twig',
                array(
                    'message'   => 'Page Not Found',
                    'site_name' => self::data( 'Rhapsody' )
                )
            );
        }
    }
}
