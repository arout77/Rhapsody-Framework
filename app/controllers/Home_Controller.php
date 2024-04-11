<?php

namespace App\Controller;

use \Src\Controller\Base_Controller;
use \Src\Middleware\EmailMiddleware;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

use Symfony\Component\Mime\Email;
use RedBeanPHP\R as R;

class Home_Controller extends Base_Controller 
{
    public function index()
    {
        $template = 'emails/test.html.twig';
        try {
            $email = (new Email())
                ->from('mygmail@address.com')
                ->to('example@gmail.com')
                ->bcc()
                ->cc()
                ->subject('Time for Symfony Mailer!')
                ->html($template);
            // Send the email by uncommenting below
            // EmailMiddleware::send('emails/test.html.twig', $email);
        } catch (TransportExceptionInterface $e) {
            // some error prevented the email sending; display an
            // error message or try to resend the message
        }

        $this->template->render(
            'home/index.html.twig', 
            [
                'message' => 'Page Not Found',
                'site_name' => 'Rhapsody Framework'
            ]
        );
    }

    public function test()
    {
        // The $sql array to pass to pagination->config() method
        $sql['table'] = 'documentation';
        $dbQuery = "SELECT * FROM documentation WHERE category = 'Getting Started'";
        $sql['parameters'] = 'Getting Started';
        // How many results per page to display
        $per_page = 20;
        // The url containing data to be paginated. Leave blank or omit if home page
        // otherwise just pass the controller name (and method if applicable)
        $url = '';

        $paginate = $this->middleware->get("pagination");
        $paginate->config($sql, $per_page, $url);
        $results = $paginate->runQuery();
        $paginate = $this->middleware->get("pagination");
        $totalRecords = $paginate->countResults($dbQuery);
        $paginate->total = $totalRecords;
        
        $results = $paginate->runQuery($dbQuery);
        $paginate->paginate();

        $this->template->render(
            'home/test.html.twig', 
            [
                'message' => 'Page Not Found',
                'site_name' => self::data('Rhapsody'),
                'results' => $results,
                'pagLinks' => $paginate->pageNumbers(),
                'pagPerPage' => $paginate->itemsPerPage(),
            ]
        );
    }

    public function data($name = '')
    {
        return $name;
    }
}