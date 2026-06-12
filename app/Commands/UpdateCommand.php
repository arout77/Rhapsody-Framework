<?php
namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class UpdateCommand extends Command
{
    protected static $defaultName = 'app:update';

    public function __construct(protected array $config)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:update')
            ->setDescription('Updates the application to the latest tagged release using Git (incremental update).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rootPath        = dirname(__DIR__, 2);
        $maintenanceFile = $rootPath . '/storage/framework/down';

        // Check if we are in a Git repository
        if (! is_dir($rootPath . '/.git')) {
            $output->writeln('<error>This application is not managed by Git. Cannot update automatically.</error>');
            return Command::FAILURE;
        }

        // Determine current version (Git tag or config fallback)
        $currentVersion = $this->getCurrentGitTag($output);
        if (! $currentVersion) {
            $output->writeln('<comment>Current version not a Git tag. Using config version: ' . ($this->config['app_version'] ?? 'unknown') . '</comment>');
            $currentVersion = $this->config['app_version'] ?? '1.5.0';
        }

        $output->writeln('<comment>Checking for new releases...</comment>');
        $latestVersionData = $this->getLatestRelease('arout77/Rhapsody-Framework', $output);
        if (! $latestVersionData) {
            $output->writeln('<error>Failed to fetch release data from GitHub.</error>');
            return Command::FAILURE;
        }

        $latestVersion = $latestVersionData['tag_name'];

        if (version_compare($currentVersion, $latestVersion, '=')) {
            $output->writeln("<info>You are already on the latest version ({$currentVersion}).</info>");
            return Command::SUCCESS;
        }

        $output->writeln("<comment>New version detected: {$latestVersion} (Current: {$currentVersion})</comment>");
        if (! empty($latestVersionData['body'])) {
            $output->writeln("<info>Release Notes:</info>\n" . $latestVersionData['body'] . "\n");
        }

        // Check for uncommitted changes
        if ($this->hasUncommittedChanges($output)) {
            $output->writeln('<error>You have uncommitted changes. Please commit or stash them before updating.</error>');
            return Command::FAILURE;
        }

        // Enter maintenance mode
        $output->writeln('<comment>Bringing the application down for maintenance...</comment>');
        if (! file_exists(dirname($maintenanceFile))) {
            mkdir(dirname($maintenanceFile), 0755, true);
        }
        touch($maintenanceFile);

        try {
            // Fetch tags from remote (incremental – downloads only new objects)
            $this->runProcess(['git', 'fetch', 'origin', '--tags'], $output, 'Fetching remote tags...');

            // Checkout the new tag (updates only changed files in working directory)
            $this->runProcess(['git', 'checkout', $latestVersion], $output, "Switching to version {$latestVersion}...");

            // Update Composer dependencies
            if (file_exists($rootPath . '/composer.json')) {
                $this->runProcess(['composer', 'install', '--no-dev', '--optimize-autoloader'], $output, 'Optimizing Composer dependencies...');
            }

            // Clear all caches
            $this->clearCaches($rootPath, $output);

            // Update config.php version number
            $this->updateConfigVersion($rootPath, $latestVersion, $output);

            $output->writeln("\n<info>Application successfully updated to version {$latestVersion}!</info>");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $output->writeln("\n<error>Update failed: " . $e->getMessage() . "</error>");
            return Command::FAILURE;
        } finally {
            if (file_exists($maintenanceFile)) {
                $output->writeln('<comment>Bringing the application back live...</comment>');
                unlink($maintenanceFile);
            }
        }
    }

    private function getCurrentGitTag(OutputInterface $output): ?string
    {
        $process = new Process(['git', 'describe', '--tags', '--abbrev=0']);
        $process->run();
        if ($process->isSuccessful()) {
            return trim($process->getOutput());
        }
        return null;
    }

    private function hasUncommittedChanges(OutputInterface $output): bool
    {
        $process = new Process(['git', 'status', '--porcelain']);
        $process->run();
        return ! empty(trim($process->getOutput()));
    }

    private function getLatestRelease(string $repo, OutputInterface $output): ?array
    {
        $apiUrl     = "https://api.github.com/repos/{$repo}/releases/latest";
        $caCertPath = dirname(__DIR__, 2) . '/cacert.pem';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Rhapsody-Framework-Updater');
        if (file_exists($caCertPath)) {
            curl_setopt($ch, CURLOPT_CAINFO, $caCertPath);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch) || $httpCode !== 200) {
            curl_close($ch);
            return null;
        }
        curl_close($ch);
        return json_decode($response, true);
    }

    private function runProcess(array $command, OutputInterface $output, ?string $message = null): void
    {
        $process = new Process($command);
        $process->setTimeout(300);
        $output->writeln("\n<info>" . ($message ?: "Running: " . implode(' ', $command)) . "</info>");
        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });
        if (! $process->isSuccessful()) {
            throw new \RuntimeException("Command failed: " . implode(' ', $command) . "\n" . $process->getErrorOutput());
        }
    }

    private function clearCaches(string $rootPath, OutputInterface $output): void
    {
        $cacheDirs = [
            $rootPath . '/storage/cache/twig',
            $rootPath . '/storage/cache/doctrine',
            $rootPath . '/storage/cache/routes',
            $rootPath . '/storage/framework/cache',
        ];
        foreach ($cacheDirs as $dir) {
            if (is_dir($dir)) {
                $output->writeln("<comment>Clearing cache: {$dir}</comment>");
                $this->clearDirectory($dir);
            }
        }
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $output->writeln("<comment>OPcache reset.</comment>");
        }
    }

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

    private function updateConfigVersion(string $rootPath, string $newVersion, OutputInterface $output): void
    {
        $configPath = $rootPath . '/config.php';
        if (! file_exists($configPath)) {
            return;
        }

        $content = file_get_contents($configPath);
        // Update 'app_version' => 'x.x.x'
        $pattern = "/('app_version'\\s*=>\\s*')([^']+)('/)/";
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, "$1{$newVersion}$3", $content);
            file_put_contents($configPath, $content);
            $output->writeln("<comment>Updated config.php app_version to {$newVersion}</comment>");
        }
    }
}
