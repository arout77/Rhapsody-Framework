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

    /**
     * @param array $config
     */
    public function __construct(protected array $config)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:update')
            ->setDescription('Updates the application to the latest version from Git.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rootPath        = dirname(__DIR__, 2);
        $maintenanceFile = $rootPath . '/storage/framework/down';
        $configPath      = $rootPath . '/config.php';

        $output->writeln('<comment>Checking for new releases...</comment>');
        $currentVersion    = $this->config['app_version'] ?? '1.5.0';
        $latestVersionData = $this->getLatestRelease('arout77/Rhapsody-Framework', $output);

        if (! $latestVersionData) {
            $output->writeln('<error>Failed to fetch release data from GitHub.</error>');
            return Command::FAILURE;
        }

        $latestVersion = $latestVersionData['tag_name'];

        // Check if we are already current
        if (version_compare($currentVersion, $latestVersion, '=')) {
            $output->writeln("<info>You are already on the latest version ({$currentVersion}).</info>");
            return Command::SUCCESS;
        }

        $output->writeln("<comment>New version detected: {$latestVersion} (Current: {$currentVersion})</comment>");
        if (! empty($latestVersionData['body'])) {
            $output->writeln("<info>Release Notes:</info>\n" . $latestVersionData['body'] . "\n");
        }

        // 1. Enter Maintenance Mode
        $output->writeln('<comment>Bringing the application down for maintenance...</comment>');
        if (! file_exists(dirname($maintenanceFile))) {
            mkdir(dirname($maintenanceFile), 0755, true);
        }
        touch($maintenanceFile);

        try {
            // 2. Fetch latest changes and release tags from remote tracking
            $this->runProcess(['git', 'fetch', 'origin', '--tags'], $output, 'Fetching remote updates and tags...');

            // 3. Stash local changes to prevent collision during checkout transitions
            $this->runProcess(['git', 'stash'], $output, 'Stashing any temporary local changes...');

            // 4. Checkout the specified tag release natively (pulls only changed bytes)
            $this->runProcess(['git', 'checkout', $latestVersion], $output, "Switching codebase to version {$latestVersion}...");

            // 5. Optimize Composer dependencies if file configuration exists
            if (file_exists($rootPath . '/composer.json')) {
                $this->runProcess(['composer', 'install', '--no-dev', '--optimize-autoloader'], $output, 'Optimizing vendor dependencies...');
            }

            // 6. Clear framework application and views cache
            $cacheDir = $rootPath . '/storage/framework/cache';
            if (is_dir($cacheDir)) {
                $output->writeln('<comment>Clearing internal framework caches...</comment>');
                $this->clearDirectory($cacheDir);
            }

            $output->writeln("\n<info>Application successfully updated to version {$latestVersion}!</info>");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $output->writeln("\n<error>Update failed: " . $e->getMessage() . "</error>");
            return Command::FAILURE;
        } finally {
            // 7. Lift Maintenance Mode
            if (file_exists($maintenanceFile)) {
                $output->writeln('<comment>Bringing the application back live...</comment>');
                unlink($maintenanceFile);
            }
        }
    }

    /**
     * @param string $repo
     * @param OutputInterface $output
     * @return array|null
     */
    private function getLatestRelease(string $repo, OutputInterface $output): ?array
    {
        $apiUrl     = "https://api.github.com/repos/{$repo}/releases/latest";
        $caCertPath = dirname(__DIR__, 2) . '/cacert.pem';

        if (! file_exists($caCertPath)) {
            $caCertPath = null;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Rhapsody-Framework-Updater');
        if ($caCertPath) {
            curl_setopt($ch, CURLOPT_CAINFO, $caCertPath);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $output->writeln("<error>cURL Error: " . curl_error($ch) . "</error>");
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * @param array $command
     * @param OutputInterface $output
     * @param string|null $message
     */
    private function runProcess(array $command, OutputInterface $output, ?string $message = null): void
    {
        $process = new Process($command);
        $process->setTimeout(300);
        $output->writeln("\n<info>" . ($message ?: "Running: " . implode(' ', $command)) . "</info>");

        $process->run(function ($type, $buffer) use ($output) {
            if (Process::ERR === $type) {
                $output->write("<error>$buffer</error>");
            } else {
                $output->write($buffer);
            }
        });

        if (! $process->isSuccessful()) {
            throw new \RuntimeException("Command failed: " . implode(' ', $command));
        }
    }

    /**
     * Helper to recursively empty a cache directory
     * * @param string $dir
     */
    private function clearDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }
    }
}
