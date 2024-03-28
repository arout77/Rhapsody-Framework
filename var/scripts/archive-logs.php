<?php 
namespace Var\Scripts;
require_once '../../vendor/autoload.php';
require_once '../../src/KernelApi.php';

use Src\KernelApi;

class LogHelper extends KernelApi
{
    public function archive(): string|null 
    {
        return $this->log->archive();
    }

    public function delete($files = []): string|null 
    {
        return $this->log->delete($files);
    }

    public function run($argv) 
    {
        unset($argv[0]);
        
        foreach( $argv as $method )
        {
            $method = str_replace("-", "", $method);
            $method = strtolower($method);

            if( method_exists($this, "$method") )
            {
                self::{$method}();
                echo self::class . "::" . $method . "() was called \r\n";
            } else {
                echo self::class . "::" . $method . "() does not exist! \r\n";
            }
        }
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
$kapi = new LogHelper;
// Import some Kernel functionality
$kapi->init($app);
// Run class methods that were passed as CLI parameters
$kapi->run($argv);