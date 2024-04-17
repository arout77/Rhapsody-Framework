#!/usr/bin/env php
<?php
namespace Var\Scripts;
require_once '../../vendor/autoload.php';
require_once '../../src/KernelApi.php';

use Src\KernelApi;

class Make extends KernelApi
{
	private $controller_dir;

	private $files_created = [];

	public function createControllerFile( $controller, $makeModel = false, $makeView = false )
	{
		$modelFileCreated = '';
		$viewFileCreated  = '';
		$renderTemplate   = '';

		if ( $makeModel != false )
		{
			$modelFileCreated = "// Model was created and stored at: /app/models/{$makeModel}Model.php";
		}

		if ( $makeView != false )
		{
			$viewFileCreated = "// View was created and stored at: /app/template/views/" . strtolower( $controller ) . "/" . $makeView . ".html.twig";
			$renderTemplate  = '$this->template->render("' . strtolower( $controller ) . DS . $makeView . '.html.twig");';
		}

		$controller    = ucwords( $controller );
		$controllerDir = $this->config->setting( 'app_path' ) . "controllers" . DS;

		if ( file_exists( $controllerDir . $controller . "_Controller.php" ) )
		{
			exit( $controllerDir . $controller . "_Controller.php" . " already exists. Exiting...\r\n" );
		}

		$new_url                           = $this->config->setting( 'site_url' ) . strtolower( $controller );
		$this->files_created['controller'] = "{$controllerDir}{$controller}_Controller.php";
		$file                              = new \SplFileObject( "{$controllerDir}{$controller}_Controller.php", "w" );
		$contents                          =
			<<<EOT
<?php
namespace App\Controller;
use Src\Controller\Base_Controller;

class {$controller}_Controller extends Base_Controller
{
    public function index()
    {
        {$modelFileCreated}
        {$viewFileCreated}
        {$renderTemplate}
    }
}
EOT;

		try {
			$written = $file->fwrite( $contents );
		}
		catch ( \Exception $e )
		{
			$e->getMessage();
		}

		echo "{$controllerDir}{$controller}_Controller.php was created...\r\n";

		if ( $makeModel != false )
		{
			self::make_model( $makeModel );
		}

		if ( $makeView != false )
		{
			self::make_view( $controller . DS . $makeView );
		}
	}

	public function getInput( $question )
	{
		return readline( $question . ": " );
	}

	public function help()
	{
		return <<<EOT

        Commands
        --------
        -c, -controller:    Create a new controller.

        -h, -help           View these instructions

        EOT;
	}

	public function make_controller(): void
	{
		// These vars get passed to createController(). Set to false by default.
		$makeModel = false;
		$makeView  = false;

		$controller       = self::getInput( "Enter a name for your controller" );
		$verifyController = self::verifyClassName( $controller );

		while ( $verifyController === 1 )
		{
			$name             = self::getInput( "Controller names must start with a letter, and may only contain alphanumeric, dashes or underscore characters. Enter a name for your controller" );
			$verifyController = self::verifyClassName( $name );
		}

		$createModel = self::getInput( "Do you want to create a model for this controller? Y/N [default is 'Y']" );

		if ( strtoupper( $createModel ) == 'Y' || strtoupper( $createModel ) == 'YES' )
		{
			$makeModel   = self::getInput( "Enter a name for your model" );
			$verifyModel = self::verifyClassName( $makeModel );

			while ( $verifyModel === 1 )
			{
				$makeModel   = self::getInput( "Model names must start with a letter, and may only contain alphanumeric, dashes or underscore characters. Enter a name for your model" );
				$verifyModel = self::verifyClassName( $makeModel );
			}
		}

		$createView = self::getInput( "Do you want to create a view for this controller? Y/N [default is 'Y']" );

		if ( strtoupper( $createView ) == 'Y' || strtoupper( $createView ) == 'YES' )
		{
			$makeView   = self::getInput( "Enter a name for your view" );
			$verifyView = self::verifyTemplateName( $makeView );

			while ( $verifyView === 1 )
			{
				$makeView   = self::getInput( "Controller names must start with a letter, and may only contain alphanumeric, dashes or underscore characters. Enter a name for your view" );
				$verifyView = self::verifyTemplateName( $makeView );
			}
		}

		if ( $verifyController === 0 )
		{
			self::createControllerFile( $controller, $makeModel, $makeView );
		}
	}

	public function make_model( $name = '' )
	{
		if ( $name != '' )
		{
			$model    = ucwords( $name );
			$modelDir = $this->config->setting( 'app_path' ) . "models" . DS;

			if ( file_exists( $modelDir . $model . "Model.php" ) )
			{
				exit( $modelDir . $model . "Model.php" . " already exists. Exiting...\r\n" );
			}

			$new_url                      = $this->config->setting( 'site_url' ) . strtolower( $model );
			$this->files_created['model'] = "{$modelDir}{$model}Model.php";
			$file                         = new \SplFileObject( "{$modelDir}{$model}Model.php", "w" );
			$contents                     =
				<<<EOT
<?php
namespace App\Model;
use Src\Model\System_Model;

class {$model}Model extends System_Model
{

}
EOT;

			try {
				$written = $file->fwrite( $contents );
			}
			catch ( \Exception $e )
			{
				$e->getMessage();
			}

			echo "{$modelDir}{$model}Model.php was created...\r\n";
		}
	}

	public function make_view( $name = '' )
	{
		if ( $name != '' )
		{
			// $name being passed is string: "folder-name/viewfile-name"
			$pathToViewFile = $name . '.html.twig'; # folder-name/viewfile-name.html.twig
			$name           = explode( DS, $pathToViewFile );
			$view           = strtolower( $name[1] ); # viewfile-name.html.twig
			$viewDir        = $this->config->setting( 'template_folder' ) . strtolower( $name[0] ) . DS;

			if ( file_exists( $viewDir . $view ) )
			{
				exit( $viewDir . $view . " already exists. Exiting...\r\n" );
			}

			if ( !file_exists( $viewDir ) )
			{
				try {
					mkdir( $viewDir, octdec( 0755 ) );
				}
				catch ( \Exception $e )
				{
					$e->getMessage();
				}
			}

			$new_url                     = $this->config->setting( 'site_url' ) . strtolower( $view );
			$this->files_created['view'] = $viewDir . $view;
			$file                        = new \SplFileObject( $viewDir . $view, "w" );
			$contents                    = '';

			try {
				$written = $file->fwrite( $contents );
			}
			catch ( \Exception $e )
			{
				$e->getMessage();
			}

			echo "{$viewDir}{$view} was created...\r\n";
		}
	}

	public function run( $argv )
	{
		unset( $argv[0] );

		foreach ( $argv as $parameter )
		{
			$parameter = strtolower( $parameter );

			// Validating args do not contain any harmful or illegal characters
			$secCheck = self::verifyArgs( $parameter );
			if ( $secCheck === 1 )
			{
				exit( "Please enter a valid option. Run the script again and type --help for more information. \r\n" );
			}

			if ( $parameter === '-h' || $parameter === '-help' || $parameter === '--h' || $parameter === '--help' )
			{
				echo self::help();
				exit;
			}

			exit( "Please enter a valid option. Run the script again and type --help for more information. \r\n" );
		}

		$task = self::getInput( "Enter -c to create a new controller" );

		switch ( $task )
		{
		case '-c':self::make_controller();
		case '-m':self::make_model();
		case '-v':self::make_view();
		};

		return;
	}

	public function verifyArgs( $arguments )
	{
		$allowed_chars = "/[^a-zA-Z-]+/";
		return preg_match( $allowed_chars, $arguments );
	}

	public function verifyClassName( $name )
	{
		$allowed_chars = "/[^a-zA-Z0-9_-]+/";
		return preg_match( $allowed_chars, $name );
	}

	public function verifyTemplateName( $name ): bool
	{
		$allowed_chars = "/[^a-zA-Z0-9_-]+/";

		if ( preg_match( $allowed_chars, $name ) === 1 )
		{
			return false;
		}

		return true;
	}
}
/**
 * How to use this script
 * ----------------------
 *
 * Open a terminal, and change to this directory:
 *
 * cd /path-to-install/var/scripts
 *
 * php make.php -h
 *
 */

// Instantiate our class
$kapi = new Make;
// Import some Kernel functionality
$kapi->init( $app );
// Run class methods that were passed as CLI parameters
$kapi->run( $argv );