<?php

namespace App\Controller;

use \Src\Controller\Base_Controller;
use \Src\Middleware\EmailHelper;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class Home_Controller extends Base_Controller 
{
    public function index()
    {
        $template = 'emails/test.html.twig';
        $email = (new Email())
            ->from('mygmail@address.com')
            ->to('example@gmail.com')
            ->bcc()
            ->cc()
            ->subject('Time for Symfony Mailer!')
            ->html($template);
        // Send the email by uncommenting below
        // EmailHelper::send('emails/test.html.twig', $email);

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