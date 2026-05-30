<?php
namespace Core;

use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class Mailer
{
    protected SymfonyMailer $mailer;
    protected array $config;

    public function __construct()
    {
        $this->config = require dirname(__DIR__) . '/config.php';
        $mailConfig   = $this->config['mailer'];

        // Check if the host is empty; if so, skip initialization or use a null transport
        if (empty($mailConfig['host'])) {
            // You can either throw a custom exception or just return early
            // Or set a mock transport if needed
            return;
        }

        $dsn          = "{$mailConfig['transport']}://{$mailConfig['username']}:{$mailConfig['password']}@{$mailConfig['host']}:{$mailConfig['port']}";
        $transport    = Transport::fromDsn($dsn);
        $this->mailer = new SymfonyMailer($transport);
    }

    /**
     * Sends an email.
     *
     * @param string $to The recipient's email address.
     * @param string $subject The email subject.
     * @param string $htmlBody The HTML content of the email.
     * @param string|null $plainTextBody Optional plain text content.
     * @return void
     */
    public function send(string $to, string $subject, string $htmlBody, ?string $plainTextBody = null): void
    {
        if (! $this->mailer) {
            throw new \RuntimeException('Mailer not configured. Please set MAIL_HOST in .env');
        }
        $fromAddress = $this->config['mailer']['from_address'];
        $fromName    = $this->config['mailer']['from_name'];

        $email = (new Email())
            ->from("{$fromName} <{$fromAddress}>")
            ->to($to)
            ->subject($subject)
            ->html($htmlBody);

        if ($plainTextBody) {
            $email->text($plainTextBody);
        }

        $this->mailer->send($email);
    }
}
