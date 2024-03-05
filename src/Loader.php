<?php
namespace Src;

use Src\Template;
// As of PHP 8.2.0, creating class properties dynamically
// has been deprecated. The following annotation re-enables
// that functionality. All children classes inherit this.
#[\AllowDynamicProperties]

class Loader extends Template
{
    /**
     * @param $app
     */
    // protected $app;

    /**
     * @var mixed
     */
    public $data;

    /**
     * @var mixed
     */
    public $file;

    /**
     * @var mixed
     */
    public $helperNamespace = "\Src\Middleware\\";

    /**
     * @var mixed
     */
    protected $_config;

    /**
     * @var mixed
     */
    protected $_db;

    # The directory containing requested file
    /**
     * @var mixed
     */
    protected $_dir;

    /**
     * @var mixed
     */
    protected $log;

    /**
     * @var mixed
     */
    protected $_session;

    /**
     * @param $c
     */
    public function __construct( $c )
    {
        $this->app = $c['app'];
        $this->db      = $c['database'];
        $this->config  = $c['config'];
        //$this->session = $c['session'];
        $this->log     = $c['log'];
    }

    /**
     * @param $file
     * @param $data
     */
    public function block($file, $data = null)
    {
        $dir = BLOCKS_DIR;

        if ( is_readable( $dir.$file ) )
        {
            require_once $dir.$file;
        }
        else
        {
            $filename = $dir.$file;
            $this->log->save( "Error opening {$filename}",
                'system.log' );
            self::viewerror( $filename, $data );
        }
    }

    /**
     * @param $file
     * @param $full_path
     */
    public function file($file, $full_path = false)
    {
        if (  !  $full_path )
        {
            # Start file search from root directory by default
            $filename = BASE_PATH.$file;
        }
        else
        {
            # Or search full path, if specified
            $filename = $file;
        }

        if ( is_readable( $filename ) )
        {
            require_once $filename;
        }
        else
        {
            $this->log->save( "Error opening {$filename}", 'system.log' );
                print '<div class="alert alert-danger"><h1>Fatal Error</h1>
                <h4>Could not load file: '.$filename.'</h4>
                Please ensure that the file exists and permission to read the file (644)
                </div>';
        }
    }

    /**
     * @param  $helper
     * @return mixed
     */
    public function helper( $helperClass )
    {
        # Load a Toolbox helper
        $ns     = $this->helperNamespace;
        $helper = $ns.$helperClass;

        return new $helper($this->app);
    }

    /**
     * @param  $_interface
     * @return mixed
     */
    public function interface( $interface )
    {
        # Return an interface
        return get_class($interface);
    }

    public function vendor( $helperClass )
    {
        return $helperClass;
    }

    /**
     * @param  $file
     * @return mixed
     */
    public function model( $file )
    {
        $dir  = MODELS_PATH;
        $file = ucwords( $file );

        if ( is_readable( PUBLIC_OVERRIDE_PATH.'models/'.$file.'Model.php' ) )
        {
            require_once PUBLIC_OVERRIDE_PATH.'models/'.$file
                .'Model.php';
            $this->model = $file.'Model';

            return $this->model = new $this->model( $this->toolbox );
        }
        elseif ( is_readable( $dir.$file.'Model.php' ) )
        {
            require_once $dir.$file.'Model.php';
            $this->model = '\Web\Model\\'.$file.'Model';

            return $this->model = new $this->model( $this->toolbox );
        }
        else
        {
            $filename = $dir.$file.'Model.php';
            $this->log->save( "Error opening {$filename}",
                'system.log' );
            require_once $dir.'errors/model.php';
        }
    }

    /**
     * @param $file
     * @param $data
     * @param NULL     $app
     */
    public function template(
        $file,
        $data = null,
        $app = null
    )
    {
        $dir  = $this->config->setting( 'template_folder' );
        $file = $dir.$file;

        if ( is_readable( $file ) )
        {
            require_once $file;
        }
        else
        {
            $this->log->save( "Error opening {$file}",
                'system.log' );
            self::viewerror( 'errors/template.php', $data );
        }
    }

    /**
     * @param $file
     * @param $data
     */
    public function view(
        $file,
        $data = null )
    {
        // $dir = VIEWS_PATH;

        if ( is_readable( $file ) )
        {
            $this->template->render( $file );
        }
        else
        {
            $filename = $file;
            $this->log->save( "Error opening {$filename}",
                'system.log' );
            self::viewerror( $filename, $data );
        }
    }

    /**
     * @param $filename
     * @param $data
     */
    public function viewerror(
        $filename,
        $data = null
    )
    {
        if ( is_readable( $filename ) )
        {
            require_once $filename;
        }
        else
        {
            //require_once(VIEWS_DIR.'error/view.php');
        }
    }
}

