<?php
namespace Src;

use Pimple\Container as ServiceLocator;

// As of PHP 8.2.0, creating class properties dynamically
// has been deprecated. The following annotation re-enables
// that functionality. All children classes inherit this.
#[\AllowDynamicProperties ]

class Kernel
{
    /**
     * @var mixed
     */
    public $cache;

    /**
     * @var object
     */
    public $config;

    /**
     * @var mixed
     */
    public $load;

    /**
     * @var mixed
     */
    public $log;

    /**
     * @var mixed
     */
    public $plugin;

    /**
     * @var mixed
     */
    public $profiler;

    /**
     * @var mixed
     */
    public $session;

    /**
     * @var mixed
     */
    public $template;

    /**
     * @var mixed
     */
    public $view;

    /**
     * @var string
     */
    protected $_action;

    /**
     * @var string
     */
    protected $_base_url;

    /**
     * @var mixed
     */
    protected $core;

    /**
     * @var mixed
     */
    protected $_cron;

    /**
     * @var mixed
     */
    protected $_data;

    /**
     * @var mixed
     */
    protected $_db;

    /**
     * @var mixed
     */
    protected $_db_info;

    /**
     * @var mixed
     */
    protected $_dispatcher;

    /**
     * @var mixed
     */
    protected $_event_manager;

    /**
     * @var mixed
     */
    protected $_html_purify;

    /**
     * @var mixed
     */
    protected $_model;

    /**
     * @var mixed
     */
    protected $_parameter;

    /**
     * @var mixed
     */
    protected $_route;

    /**
     * @var mixed
     */
    private $controller;

    /**
     * @var mixed
     */
    private $controller_class;

    /**
     * @var mixed
     */
    private $controller_filename;

    /**
     * @param $app
     */

    public function __construct( protected ServiceLocator $app )
    {
        $this->base_url   = $app[ 'config' ]->setting( 'site_url' );
        $this->config     = $app[ 'config' ];
        $this->controller = $app[ 'router' ]->controller;
        $this->core       = $app;
        // $this->cron       = $app['cron'];
        $this->db      = $app[ 'database' ];
        $this->db_info = $app[ 'database_info' ];
        // $this->dispatcher = $app['dispatcher'];
        $this->event_manager = $app[ 'event_manager' ];
        // $this->html_purify         = $app['html_purify'];
        $this->load      = $app[ 'load' ];
        $this->log       = $app[ 'log' ];
        $this->model     = $app[ 'system_model' ];
        $this->parameter = $app[ 'router' ]->parameter;
        $this->profiler  = $app[ 'profiler' ];
        $this->route     = $app[ 'router' ];
        $this->session   = $app[ 'session' ];
        $this->template  = $app[ 'template' ];
        $this->helper    = $app[ 'plugin_core' ];
    }
}