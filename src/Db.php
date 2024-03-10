<?php 
namespace Src;

use RedBeanPHP\R as R;

/**
 * Documentation: https://redbeanphp.com/index.php?p=/tutorial
 */
class Db extends R
{
    public function init($app)
    {
        $host = $app['config']->setting("db_host");
        $db = $app['config']->setting("db_name");
        $user = $app['config']->setting("db_user");
        $pass = $app['config']->setting("db_pass");

        return R::setup( 'mysql:host='.$host.';dbname='.$db.'', $user, $pass ); //for both mysql or mariaDB
    }
}