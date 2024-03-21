<?php

namespace App\Controller;

use \Src\Controller\Base_Controller;
use \Src\Middleware\EmailHelper;
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
            // EmailHelper::send('emails/test.html.twig', $email);
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
        $db = R::dispense( 'documentation' );
        $db->category = 'Components';
        $db->subcategory = 'Scripts';
        $db->content = '';
        $id = R::store( $db );

        $this->template->render(
            'home/test.html.twig', 
            [
                'message' => 'Page Not Found',
                'site_name' => self::data('Rhapsody')
            ]
        );
    }

    public function data($name = '')
    {
        return $name;
    }
}