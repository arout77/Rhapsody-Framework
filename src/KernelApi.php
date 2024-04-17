<?php
namespace Src;

define( 'DS', DIRECTORY_SEPARATOR );

if ( !defined( 'BASE_PATH' ) )
{
	$dir = dirname( __FILE__ );
	$dir = chop( $dir );
	$dir = chop( $dir, "/" );
	define( 'BASE_PATH', $dir . DS . '/../' );
}

define( 'ENV_PATH', BASE_PATH . '.env' );

$fp = fileperms( ENV_PATH );
if ( file_exists( ENV_PATH ) )
{
	if ( substr( sprintf( '%o', $fp ), -4 ) != 0644 )
	{
		chmod( ENV_PATH, 0644 );
	}
}

if ( !is_readable( ENV_PATH ) )
{
	exit( '<h3>Either the <span style="color: red;">.env</span> global configuration file was not found,
        or the file does not have read permissions. Exiting...</h3>' );
}

unset( $fp );

require_once BASE_PATH . 'vendor' . DS . 'autoload.php';

$system_folder = BASE_PATH . 'src' . DS;

// Import service locator
require_once $system_folder . 'Factory.php';
// Load path definitions
require_once $system_folder . 'Paths.php';
// Check if system check was requested
if ( $app['config']->setting( 'system_startup_check' ) == 'TRUE' )
{
	require_once SYSTEM_PATH . 'system_startup_check.php';
}
// Needed for router to build routes
$subdir = $app['config']->setting( 'subdir' );

class KernelApi
{
	protected $config;

	protected $db;

	protected $load;

	protected $log;

	protected $route;

	protected $session;

	public function init( $app )
	{
		$this->config  = $app['config'];
		$this->db      = $app['database'];
		$this->load    = $app['load'];
		$this->log     = $app['log'];
		$this->route   = $app['router'];
		$this->session = $app['session'];
	}
}
