<?php

namespace App\Commands;

use Core\Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheClearCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'cache:clear';

    // The Cache manager is now injected via the constructor
    /**
     * @param Cache $cache
     */
    public function __construct( protected Cache $cache )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName( 'cache:clear' )->setDescription( 'Flush the application cache.' );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        // 1. Flush the application key-value cache (storage/cache/app/)
        $this->cache->flush();
        $output->writeln( '<info>Application cache cleared.</info>' );

        // 2. Clear the Twig compiled template cache (storage/cache/twig/)
        // Without this, stale compiled templates are served after a git pull,
        // even if the source .twig files have changed.
        $twigCachePath = dirname( __DIR__, 2 ) . '/storage/cache/twig';
        if ( is_dir( $twigCachePath ) ) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator( $twigCachePath, \FilesystemIterator::SKIP_DOTS ),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ( $files as $file ) {
                $file->isDir() ? rmdir( $file->getRealPath() ) : unlink( $file->getRealPath() );
            }
            $output->writeln( '<info>Twig template cache cleared.</info>' );
        }

        // 3. Invalidate OPcache if available so updated PHP files are picked up immediately.
        if ( function_exists( 'opcache_reset' ) ) {
            opcache_reset();
            $output->writeln( '<info>OPcache reset.</info>' );
        }

        return Command::SUCCESS;
    }
}
