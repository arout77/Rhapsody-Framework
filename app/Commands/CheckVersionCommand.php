<?php

namespace App\Commands;

use Core\Cache;
use Core\Mailer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckVersionCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:check-version';

    // *** Define the default repository ***
    private string $defaultRepository = 'arout77/Rhapsody-Framework';

    /**
     * @param array $config
     * @param Mailer $mailer
     * @param Cache $cache
     */
    public function __construct(
        protected array $config,
        protected Mailer $mailer,
        protected Cache $cache
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName( 'app:check-version' )
            ->setDescription( 'Checks GitHub for the latest release of a repository.' )
            // *** FIX: Change argument to be optional ***
            ->addArgument( 'repository', InputArgument::OPTIONAL, 'Optional: The GitHub repository to check in "owner/repo" format.', $this->defaultRepository );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        // *** FIX: Get the repository from the argument, which now has a default ***
        $repository = $input->getArgument( 'repository' );

        $currentVersion = $this->config['app_version'];
        $apiUrl         = "https://api.github.com/repos/{$repository}/releases/latest";

        $output->writeln( "<comment>Current version:</comment> {$currentVersion}" );
        $output->writeln( "<comment>Checking for latest release at {$repository}...</comment>" );

        $response = $this->fetchFromApi( $apiUrl );
        if ( !$response )
        {
            $output->writeln( "<error>Failed to fetch data from GitHub API. Check repository name.</error>" );
            return Command::FAILURE;
        }

        $data = json_decode( $response, true );
        if ( !isset( $data['tag_name'] ) )
        {
            $output->writeln( "<error>Could not find release tag in the API response.</error>" );
            return Command::FAILURE;
        }

        $latestVersion = $data['tag_name'];
        $output->writeln( "<comment>Latest release found:</comment> {$latestVersion}" );

        if ( version_compare( $latestVersion, $currentVersion, '>' ) )
        {
            $output->writeln( "<info>A new version ({$latestVersion}) is available!</info>" );
            $notificationCacheKey = 'notified_version_' . str_replace( '.', '_', $latestVersion );

            if ( $this->config['app_env'] === 'development' )
            {
                $output->writeln( "Development mode: Caching notification for the navbar." );
                $this->cache->put( 'update_available', $latestVersion, 1440 );
            }
            else
            {
                if ( $this->cache->has( $notificationCacheKey ) )
                {
                    $output->writeln( "<comment>An email notification for version {$latestVersion} has already been sent. Skipping.</comment>" );
                }
                else
                {
                    $output->writeln( "Production mode: Attempting to send email notification..." );
                    $wasSent = $this->sendEmailNotification( $latestVersion, $data['html_url'], $output );
                    if ( $wasSent )
                    {
                        $this->cache->put( $notificationCacheKey, true, 43200 );
                    }
                }
            }
        }
        else
        {
            $output->writeln( "Application is up to date. Clearing any update caches." );
            $this->cache->forget( 'update_available' );
        }

        return Command::SUCCESS;
    }

    /**
     * @param string $url
     */
    private function fetchFromApi( string $url ): string | false
    {
        $options = ['http' => ['header' => "User-Agent: Rhapsody-Framework-Version-Checker\r\n"]];
        $context = stream_context_create( $options );
        return @file_get_contents( $url, false, $context );
    }

    /**
     * @param string $newVersion
     * @param string $releaseUrl
     * @param OutputInterface $output
     */
    private function sendEmailNotification( string $newVersion, string $releaseUrl, OutputInterface $output ): bool
    {
        $to = $_ENV['MAIL_ADMIN_EMAIL'] ?? null;
        if ( !$to )
        {
            $output->writeln( "<error>MAIL_ADMIN_EMAIL is not set in your .env file. Cannot send notification.</error>" );
            return false;
        }
        try {
            $subject  = "New Rhapsody Version Available: {$newVersion}";
            $htmlBody = "<p>A new version of your application is available: <strong>{$newVersion}</strong>.</p>";
            $htmlBody .= "<p>View the release notes and update here: <a href='{$releaseUrl}'>{$releaseUrl}</a></p>";
            $this->mailer->send( $to, $subject, $htmlBody );
            $output->writeln( "<info>Email notification sent successfully to {$to}.</info>" );
            return true;
        }
        catch ( \Exception $e )
        {
            error_log( "CheckVersionCommand Mailer Error: " . $e->getMessage() );
            $output->writeln( "<error>Failed to send email. Check your server's error log for details.</error>" );
            return false;
        }
    }
}
