<?php ob_start();?>

<style>
code { color: red;  }
</style>

<?php

if (!is_readable('src/Config.php'))
{
	ini_set('display_errors', '0');
	error_reporting(0);
	exit("<h3>src/Config.php either does not exist, or does not have read permissions (0644) in <code>src/Config.php</code></h3>");
}

if (!is_readable('src/Factory.php'))
{
	ini_set('display_errors', '0');
	error_reporting(0);
	exit("<h3>src/Factory.php file either does not exist, or does not have read permissions (0644) in <code>src/Factory.php</code></h3>");
}

if (!is_readable('src/Run.php'))
{
	ini_set('display_errors', '0');
	error_reporting(0);
	exit("<h3>src/Run.php file either does not exist, or does not have read permissions (0644) in <code>src/Run.php</code></h3>");
}

if (!is_readable('src/Paths.php'))
{
	ini_set('display_errors', '0');
	error_reporting(0);
	exit("<h3>src/Paths.php file either does not exist, or does not have read permissions (0644) in <code>src/Paths.php</code></h3>");
}

if (!is_readable('src/Loader.php'))
{
	ini_set('display_errors', '0');
	error_reporting(0);
	exit("<h3>src/Loader.php file either does not exist, or does not have read permissions (0644) in <code>src/Loader.php</code></h3>");
}

if (!is_readable('src/controllers/Base_Controller.php'))
{
	ini_set('display_errors', '0');
	error_reporting(0);
	exit("<h3>src/controllers/Base_Controller.php file either does not exist, or does not have read permissions (0644) in <code>src/controllers/Base_Controller.php</code></h3>");
}

if (!array_key_exists('mod_rewrite', $_SERVER) && !array_key_exists('REDIRECT_mod_rewrite', $_SERVER) && !array_key_exists('REDIRECT_HTTP_MOD_REWRITE', $_SERVER))
{
	exit('<h3>Apache module <code>mod_rewrite</code> was not detected. System exiting...</h3>');
}

if (!extension_loaded('pdo'))
{
	exit('<h3><code>PDO extension</code> was not detected. System exiting...</h3>');
}

function php_ver()
{
	// Detect PHP version being run
	return PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
}
if (php_ver() < 8.3)
{
	exit('<h3>You must have PHP version 8.3.0 or higher. Your PHP version: <code>' . php_ver() . '</code> System exiting...</h3>');
}
	
exit('<div class="alert alert-info">
		<h2>No problems could be found in the initial environment and file system checks</h2>
	  </div>
	  <h3>If you are experiencing errors or other problems, turn on error reporting in the <code>.env</code> configuration file, reload this page, and check for any errors reported in <code>/var/logs</code></h3>');

ob_flush();