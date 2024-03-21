<?php 
namespace Var\Scripts;
require_once '../../vendor/autoload.php';
require_once '../../src/KernelApi.php';

use Src\KernelApi;

class Example extends KernelApi
{
    public function getSiteName(): string
    {
        return $this->config->setting('site_name');
    }

    public function test(): string|null 
    {
        return $this->log->clean();
    }
}

$kapi = new Example;
$kapi->init($app);
$kapi->test();