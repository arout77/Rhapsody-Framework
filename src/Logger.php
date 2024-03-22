<?php
namespace Src;

/**
 * File:    /src/Logger.php
 * Purpose: Handles system and user-defined event logging
 */

class Logger {

	protected $config;

	public function __construct($app)
	{
		$this->config = $app['config'];
	}

	/**
	 * @param $message
	 * @param NULL $logfile
	 */
	public function save($message = NULL, $logfile = 'system.log') {

		// if (!is_dir(LOG_PATH))
		// {
		// 	mkdir(LOG_PATH, 0755, true);
		// 	chmod(LOG_PATH, 0755);
		// }

		# Logging function for both user-defined and system errors
		if (!is_null($message)) {

			if ($message instanceof Exception) {

				$print_to_file = "EXCEPTION OCCURED\r\nDate\Time: " . date("Y-m-d H:i:s") . "\r\n
	            File name: $message->getFile()\r\nLine Number: $message->getLine()\nMessage: $message->getMessage()" . PHP_EOL;

				$open = fopen(LOG_PATH . $logfile, "a+");
				fwrite($open, $print_to_file);
				fclose($open);

				return true;
			} else {

				$print_to_file = "### Manual Error Log Entry ###\r\nDate\Time: " . date("Y-m-d H:i:s") . "\r\n$message\r\n ### End Manual Logging ###" . PHP_EOL;

				$open = fopen(LOG_PATH . $logfile, "a+");
				fwrite($open, $print_to_file);
				fclose($open);

				return true;
			}

		} else {
			$print_to_file = "### NOTICE ###\nDate\Time: " . date("Y-m-d H:i:s") . "\n
	            Cannot print a <null> message" . PHP_EOL;

			$open = fopen(LOG_PATH . $logfile, "a+");
			fwrite($open, $print_to_file);
			fclose($open);

			return false;
		}
	}

	public function archive() 
	{
		if (php_sapi_name() != "cli")
		{
			self::save("Log cleaning can only be run from command line.");
			exit("Log cleaning can only be run from command line. Exiting...\r\n");
		}

		# Maximum size of log file allowed
		$max_size = $this->config->setting('log_file_max_size') * 1000000;
		$max_size_mb = $this->config->setting('log_file_max_size');

		chdir(LOG_PATH);

		$archiveDir = $this->config->setting("log_path") . '/archive';
		$logFileArr = [];
		$errors = [];
		$date = date("Y-m-d");

		foreach (glob("*.log") as $_file) 
		{
			if (filesize($_file) >= $max_size) 
			{
				$tar = new \PharData(basename($_file, ".log") . '-' . $date . '-' . time() .'-error-log-archive.tar');
				$tar->addFile($_file);
				$tar->compress(\Phar::GZ);

				$_file = str_replace(".log", "", $_file);

				# Move tarball to archives folder once complete
				if(!is_dir('archive'))
				{
					mkdir('archive', 0644);
				}

				if(!rename($_file . '-' . $date . '-' . time() .'-error-log-archive.tar.gz', 
				'archive/' . $_file . '-' . $date . '-' . time() .'-error-log-archive.tar.gz'))
				{
					exit("An error occurred while processing {$_file}.log");
				}

				# Delete old log file
				if (!unlink($_file.".log")) 
				{ 
					$errors["$_file"] = $_file; 
				} 
				else { 
					$logFileArr["$_file"] = $_file; 
				}

				if(!unlink($_file . '-' . $date . '-' . time() .'-error-log-archive.tar'))
				{
					echo "$_file . '-' . $date . '-' . time() .'-error-log-archive.tar' was not deleted.
					 Please remove manually.";
				}

			}
		}

		if( empty($errors) && empty($logFileArr) )
		{
			echo "No log files larger than {$max_size_mb}MB found. Exiting...\r\n";
		}

		if( empty($errors) && !empty($logFileArr) )
		{
			foreach($logFileArr as $lfile)
				echo "{$lfile}.log archived and moved to $archiveDir directory...\r\n";
		}

		if( !empty($errors) && empty($logFileArr) )
		{
			foreach($errors as $error)
				echo "An error occurred while trying to process {$error}.log ...\r\n";
		}

		if( !empty($errors) && !empty($logFileArr) )
		{
			foreach($logFileArr as $lfile)
				echo "{$lfile}.log moved to $archiveDir directory...\r\n";

			foreach($errors as $error)
				echo "An error occurred while trying to process {$error}.log ...\r\n";
		}
	}

	public function delete(array $files = []) 
	{
		if (php_sapi_name() != "cli")
		{
			self::save("Log cleaning can only be run from command line.");
			exit("Log cleaning can only be run from command line. Exiting...\r\n");
		}

		chdir(LOG_PATH);

		$archiveDir = $this->config->setting("log_path") . '/archive';
		$logFileArr = [];
		$errors = [];

		if( !empty($files) )
		{
			foreach (glob("{$files}.log") as $_file) 
			{
				# Delete old log file
				if (!unlink($_file.".log")) 
				{ 
					$errors["$_file"] = $_file; 
				} 
				else { 
					$logFileArr["$_file"] = $_file; 
				}
			}
		} else {
			foreach (glob("*.log") as $_file) 
			{
				# Delete old log file
				if (!unlink($_file)) 
				{ 
					$errors["$_file"] = $_file; 
				} 
				else { 
					$logFileArr["$_file"] = $_file; 
				}
			}
		}

		if( empty($errors) && empty($logFileArr) )
		{
			echo "No log files found. Exiting...\r\n";
		}

		if( empty($errors) && !empty($logFileArr) )
		{
			foreach($logFileArr as $lfile)
				echo "{$lfile}.log was deleted...\r\n";
		}

		if( !empty($errors) && empty($logFileArr) )
		{
			foreach($errors as $error)
				echo "An error occurred while trying to process {$error} ...\r\n";
		}

		if( !empty($errors) && !empty($logFileArr) )
		{
			foreach($logFileArr as $lfile)
				echo "{$lfile}.log was deleted...\r\n";

			foreach($errors as $error)
				echo "An error occurred while trying to process {$error}.log ...\r\n";
		}
	}
}