<?php
// MIT License

// Copyright (c) 2024 Andrew Rout

// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:

// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.

// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.

/**
 * An open source application development framework designed for PHP 7
 *
 * @package         Rhapsody Framwework
 * @author          Andrew Rout [ arout@diamondphp.org ]
 * @copyright       Copyright (c) 2024, Andrew Rout
 * @license         https://diamondphp.org/support/license
 * @link            https://diamondphp.org
 * @since           Version 1.0.0
 * @filesource
 *
 */
declare ( strict_types = 1 );

define( 'DS', DIRECTORY_SEPARATOR );

// Defines the location of the front controller (this file)
// For security purposes, we recommend the front controller
// to be the only PHP file stored in a publicly accessible folder
if ( !defined( 'BASE_PATH' ) )
{
	$dir = dirname( __FILE__ );
	$dir = chop( $dir );
	$dir = chop( $dir, "/" );
	define( 'BASE_PATH', $dir . DS );
}

// If you moved your .env file to another directory,
// remove BASE_PATH . below and enter the full file path
define( 'ENV_PATH', BASE_PATH . '.env' );

// Check for and attempt to fix read permissions to the .env file
// Will not work on all servers
$fp = fileperms( ENV_PATH );
if ( file_exists( ENV_PATH ) )
{
	if ( substr( sprintf( '%o', $fp ), -4 ) != 0644 )
	{
		chmod( ENV_PATH, 0644 );
	}
}

// Either the ENV_PATH variable above is set incorrectly,
// or we just cannot read the file. Alert user and abort.
if ( !is_readable( ENV_PATH ) )
{
	exit( '<h3>Either the <span style="color: red;">.env</span> global configuration file was not found,
        or the file does not have read permissions. Exiting...</h3>' );
}

unset( $fp );

require_once BASE_PATH . 'vendor' . DS . 'autoload.php';

// If you moved the /src folder, erase the default value below and enter
// the file path to the new location of the 'src' folder.
// Be sure to include the trailing slash.
// Example::
// $system_folder = '/usr/home/foobar/src/';
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

/*-------------------------------------------
 * Process the request
 *
 * Nothing at all has actually been done yet,
 * aside from setting some global definitions
 * -----------------------------------------*/

# Get and set currently used controller, action and parameters
$app['router']->getRoute( $subdir );

if ( $_POST )
{
	// $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);

	// if (!$token || $token !== $_SESSION['token']) {
	// 	// return 405 http status code
	// 	header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
	// 	exit;
	// }
	// $app['router']->interceptPost($_POST, $app);
}

if ( $_GET )
{
	// $app['router']->interceptGet($app);
}

/*-------------------------------------------
 * Start session
 * -----------------------------------------*/
// Be sure to only make changes to session options
// within the .env configuration file.
$app['session']->start();

/*-------------------------------------------
 * Instantiate requested URL
 * -----------------------------------------*/
# Display the requested page
require_once $app['config']->setting( 'system_path' ) . 'Run.php';
$app['base_controller']->__construct( $app );
$app['base_controller']->parse();

unset( $subdir );
