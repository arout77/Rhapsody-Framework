<?php 

namespace Src\Middleware;

use \Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email as SymfonyEmail;

/***************************************************************************
 * 
 * Documentation:  https://symfony.com/doc/current/mailer.html#twig-html-css
 * 
 **************************************************************************/
abstract class Email extends Helper
{
    public static function send($emailTemplateFile, $email) 
    {
        $template = parent::getView($emailTemplateFile);

        $transport = Transport::fromDsn(self::$config->setting("mailer_dsn"));
        $mailer = new Mailer($transport);

        return $mailer->send($email);
    }
}