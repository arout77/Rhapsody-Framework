<?php
namespace App\Commands;

use Dotenv\Dotenv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;

class CacheWarmCommand extends Command
{
    protected static $defaultName = 'app:cache-warm';

    protected function configure(): void
    {
        $this
            ->setName('app:cache-warm')
            ->setDescription('Warm up the Twig template cache by compiling all templates.')
            ->addOption('method', 'm', InputOption::VALUE_OPTIONAL, 'Only "compile" is supported', 'compile')
            ->addOption('base-url', 'b', InputOption::VALUE_OPTIONAL, 'Not used for compile method');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Load .env file for CLI context
        $rootPath = dirname(__DIR__, 2);
        if (file_exists($rootPath . '/.env')) {
            $dotenv = Dotenv::createImmutable($rootPath);
            $dotenv->load();
            $output->writeln('<comment>.env file loaded.</comment>');
        } else {
            $output->writeln('<comment>.env file not found – using existing environment.</comment>');
        }

        $method = $input->getOption('method');
        if ($method !== 'compile') {
            $output->writeln('<error>Only "compile" method is currently supported.</error>');
            return Command::FAILURE;
        }

        return $this->warmViaCompilation($output);
    }

    private function warmViaCompilation(OutputInterface $output): int
    {
        $twig = $this->getTwigFromContainer();
        if (! $twig) {
            $output->writeln('<error>Could not resolve Twig environment from container.</error>');
            return Command::FAILURE;
        }

        $rootPath    = realpath(dirname(__DIR__, 2));
        $activeTheme = $this->getConfig('theme') ?? 'default';
        $themePaths  = [
            $rootPath . '/views/themes/' . $activeTheme,
            $rootPath . '/views/themes/default',
        ];

        $templates = []; // store relative template names
        foreach ($themePaths as $basePath) {
            if (! is_dir($basePath)) {
                $output->writeln("<comment>Skipping non-existent path: $basePath</comment>");
                continue;
            }
            $files = $this->findTwigFiles($basePath);
            foreach ($files as $file) {
                // Normalize paths to forward slashes
                $normalizedFile       = str_replace('\\', '/', $file);
                $normalizedBase       = str_replace('\\', '/', $basePath);
                $relative             = str_replace($normalizedBase . '/', '', $normalizedFile);
                $templates[$relative] = true; // use as key to avoid duplicates
            }
        }

        $templates = array_keys($templates);
        $output->writeln("<comment>Found " . count($templates) . " Twig templates to compile.</comment>");

        $success = 0;
        foreach ($templates as $template) {
            try {
                $twig->load($template);
                $output->writeln("  <info>✓</info> $template");
                $success++;
            } catch (\Exception $e) {
                $output->writeln("  <error>✗</error> $template - " . $e->getMessage());
            }
        }

        $output->writeln("<info>Compiled $success / " . count($templates) . " templates.</info>");
        return Command::SUCCESS;
    }

    private function findTwigFiles(string $dir): array
    {
        $files    = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'twig') {
                $files[] = $file->getRealPath();
            }
        }
        return $files;
    }

    private function getTwigFromContainer(): ?Environment
    {
        if (isset($GLOBALS['container']) && $GLOBALS['container']->has(Environment::class)) {
            return $GLOBALS['container']->resolve(Environment::class);
        }
        // Fallback: bootstrap container now
        $rootPath = dirname(__DIR__, 2);
        if (file_exists($rootPath . '/bootstrap.php')) {
            $container = require $rootPath . '/bootstrap.php';
            if ($container->has(Environment::class)) {
                return $container->resolve(Environment::class);
            }
        }
        return null;
    }

    private function getConfig(string $key, $default = null)
    {
        $rootPath = dirname(__DIR__, 2);
        if (! file_exists($rootPath . '/config.php')) {
            return $default;
        }
        $config = require $rootPath . '/config.php';
        return $config[$key] ?? $default;
    }
}
