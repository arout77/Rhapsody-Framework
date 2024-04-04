#!/usr/bin/env php
<?php 
namespace Var\Scripts;
require_once '../../vendor/autoload.php';
require_once '../../src/KernelApi.php';

use Src\KernelApi;

class Make extends KernelApi
{
    public function getInput($question)
    {
        return readline($question . ": ");
    }

    public function verifyClassName($name)
    {
        $allowed_chars = "/[^a-zA-Z0-9_-]+/";
        return preg_match($allowed_chars, $name);
    }

    public function make_controller(): bool|null
    {
        $name = self::getInput("Enter a name for your controller");
        $verify = self::verifyClassName($name);
        
        while( $verify === 1 )
        {
            $name = self::getInput("Controller names may only contain alphanumeric, dashes or underscore characters. Enter a name for your controller");
            $verify = self::verifyClassName($name);
        }

        if( $verify === 0 )
        {
            self::createControllerFile($controller, $makeModel = false, $makeView = false);
            echo "{$name}_Controller.php was created \r\n";
            return true;
        }

        return null;
    }

    public function createControllerFile($controller, $makeModel = false, $makeView = false)
    {
        $contents = 
<<<EOT
<?php
namespace App\Controller;
use Src\Controller\Base_Controller;

class {$controller}_Controller extends Base_Controller
{
	public function index()
	{
        {if( $makeModel != false ):}
        # Model was created and stored at: /app/models/{$makeModel}
        {endif;}
        {if( $makeView != false ):}
        # View was created and stored at: /app/template/views/{$makeView}
        {endif;}
	}
}
EOT;
    }

    public function make_model()
    {
        return "model ran successfully\r\n\r\n";
    }

    public function make_view()
    {
        return "view ran successfully\r\n\r\n";
    }

    public function help()
    {
        return <<<EOT
        
        Commands
        --------
        -c, -controller:    Create a new controller. Pass controller class name as argument 
                            (without leading dash)

        -m, -model:         Create a new model. Pass model class name as argument 
                            (without leading dash)

        -v, -view:          Create a new view. Pass view folder and file name as argument 
                            (without leading dash) in the following format:

                            folder_name/view_name.html.twig

        -h, -help           View these instructions 

        EOT;
    }

    public function run($argv) 
    {
        unset($argv[0]);

        foreach( $argv as $parameter )
        {
            $parameter = strtolower($parameter);

            if( $parameter === '-h' || $parameter === '-help' || $parameter === '--h' || $parameter === '--help' )
            {
                echo self::help();
                exit;
            }

            exit("Please enter a valid option. Run the script again and type --help for more information. \r\n");
        }

        $task = self::getInput("Enter -c to create a new controller, -m to create a model or -v to create a view");

        match($task)
        {
            '-c'=> self::make_controller(),
            '-m'=> self::make_model(),
            '-v'=> self::make_view(),

            default=> exit("Please enter a valid option. Run the script again and type --help for more information. \r\n")
        };


        return;

        // $parameter = str_replace("-", "", $parameter);

            // match ($parameter) {
            //     'c' => $parameter = 'make_controller',
            //     'controller' => $parameter = 'make_controller',
            //     'm' => $parameter = 'make_model',
            //     'model' => $parameter = 'make_model',
            //     'v' => $parameter = 'make_view',
            //     'view' => $parameter = 'make_view',
            // };

            // if( method_exists($this, "{$parameter}") )
            // {
            //     self::{$parameter}();
            //     echo self::class . "::" . $parameter . "() was called \r\n";
            // } else {
            //     echo self::class . "::" . $parameter . "() does not exist! \r\n";
            // }

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
 * View the methods above, and pass the method as an
 * argument in the command line:
 * 
 * php archive-logs.php -archive
 * 
 */

// Instantiate our class
$kapi = new Make;
// Import some Kernel functionality
$kapi->init($app);
// Run class methods that were passed as CLI parameters
$kapi->run($argv);