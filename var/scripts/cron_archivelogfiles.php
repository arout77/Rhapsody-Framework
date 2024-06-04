<?php
namespace Var\Scripts;
require_once '../../vendor/autoload.php';
require_once '../../src/KernelApi.php';

use Src\KernelApi;

class Archiver extends KernelApi
{
	/**
	 * This cronjob will compress all .log files under /var/logs folder
	 * Use archive-logs.php to manually manage log files
	 */
	public function __construct()
	{
		try {
			$logDir = $this->config->setting( "var_folder" . DS . "logs" . DS );
			$files  = glob( $logDir . '*' );
			$zip    = new ZipArchive;
			$name   = date( "m-d-Y" ) . '_logs.zip';
			if ( $zip->open( $logDir . $name, ZipArchive::CREATE ) === TRUE )
			{
				foreach ( $files as $file )
				{
					// Add files to the zip file
					$zip->addFile( $file );
					$this->log->save( 'Log file ' . $file . ' compressed and stored in /var/logs/ directory...', 'log-archives.log' );
				}
				// All files are added, so close the zip file.
				$zip->close();
			}
		}
		catch ( Exception $e )
		{
			$this->log->save( "There was an issue running the log archiving cron. See error message below: \r\n" . $e->getMessage(), 'cron.log' );
		}
	}
}

$archive = new Archiver;
