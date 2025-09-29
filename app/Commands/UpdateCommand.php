<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class UpdateCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:update';

    // We need the config to know the current version
    /**
     * @param array $config
     */
    public function __construct( protected array $config )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName( 'app:update' )
            ->setDescription( 'Updates the application to the latest version from Git.' );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        $rootPath        = dirname( __DIR__, 2 );
        $maintenanceFile = $rootPath . '/storage/framework/down';
        $configPath      = $rootPath . '/config.php';

        // --- Step 1: Check for new version BEFORE doing anything ---
        $output->writeln( '<comment>Checking for new releases...</comment>' );
        $currentVersion    = $this->config['app_version'];
        $latestVersionData = $this->getLatestRelease( 'arout/rhapsody' ); // Replace with your repo

        if ( !$latestVersionData )
        {
            $output->writeln( '<error>Could not fetch release information from GitHub.</error>' );
            return Command::FAILURE;
        }

        $latestVersion = $latestVersionData['tag_name'];
        $output->writeln( "Current version: <info>{$currentVersion}</info>" );
        $output->writeln( "Latest release: <info>{$latestVersion}</info>" );

        if ( version_compare( $latestVersion, $currentVersion, '<=' ) )
        {
            $output->writeln( '<info>Application is already up to date.</info>' );
            return Command::SUCCESS;
        }

        // --- Step 2: Enter Maintenance Mode ---
        $output->writeln( "\n<comment>New version found. Entering maintenance mode...</comment>" );
        touch( $maintenanceFile );

        try {
            // --- Step 3: Update version in config file ---
            $output->writeln( '<comment>Updating version in config.php...</comment>' );
            $configFileContent    = file_get_contents( $configPath );
            $newConfigFileContent = preg_replace(
                "/'app_version' => '.*?'/",
                "'app_version' => '{$latestVersion}'",
                $configFileContent
            );
            file_put_contents( $configPath, $newConfigFileContent );

            // --- Step 4: Run update commands ---
            $this->runProcess( ['git', 'pull'], $output );
            $this->runProcess( ['composer', 'install', '--no-dev', '--optimize-autoloader'], $output );
            $this->runProcess( [PHP_BINARY, 'rhapsody', 'env:sync'], $output );
            $this->runProcess( [PHP_BINARY, 'rhapsody', 'migrate'], $output );
            $this->runProcess( [PHP_BINARY, 'rhapsody', 'route:cache'], $output );
            $this->runProcess( [PHP_BINARY, 'rhapsody', 'cache:clear'], $output );

        }
        catch ( \Exception $e )
        {
            $output->writeln( '<error>An error occurred during the update process:</error>' );
            $output->writeln( $e->getMessage() );
            $output->writeln( '<comment>The application has been left in maintenance mode for manual inspection.</comment>' );
            return Command::FAILURE;
        }

        // --- Step 5: Exit Maintenance Mode ---
        $output->writeln( '<comment>Exiting maintenance mode...</comment>' );
        unlink( $maintenanceFile );

        $output->writeln( "\n<info>Application successfully updated to version {$latestVersion}!</info>" );
        return Command::SUCCESS;
    }

    /**
     * @param string $repository
     */
    private function getLatestRelease( string $repository ): ?array {
        $apiUrl   = "https://api.github.com/repos/{$repository}/releases/latest";
        $options  = ['http' => ['header' => "User-Agent: Rhapsody-Framework-Updater\r\n"]];
        $context  = stream_context_create( $options );
        $response = @file_get_contents( $apiUrl, false, $context );

        return $response ? json_decode( $response, true ) : null;
    }

    /**
     * @param array $command
     * @param OutputInterface $output
     */
    private function runProcess( array $command, OutputInterface $output ): void
    {
        $process = new Process( $command );
        $process->setTimeout( 300 );
        $output->writeln( "\n<info>Running: " . $process->getCommandLine() . "</info>" );
        $process->run( fn( $type, $buffer ) => $output->write( $buffer ) );
        if ( !$process->isSuccessful() )
        {
            throw new \RuntimeException( $process->getErrorOutput() );
        }
    }
}
