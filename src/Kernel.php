<?php 

namespace Src;

use \Pimple\Container as ServiceLocator;

// As of PHP 8.2.0, creating class properties dynamically
// has been deprecated. The following annotation re-enables
// that functionality. All children classes inherit this.
#[\AllowDynamicProperties]

class Kernel {

    /**
	 * @var string
	 */
	protected $action;
	/**
	 * @var string
	 */
	protected $base_url;
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
	 * @var mixed
	 */
	protected $core;
	/**
	 * @var mixed
	 */
	protected $cron;
	/**
	 * @var mixed
	 */
	protected $data;
	/**
	 * @var mixed
	 */
	protected $db;
	/**
	 * @var mixed
	 */
	protected $db_info;
	/**
	 * @var mixed
	 */
	protected $dispatcher;
	/**
	 * @var mixed
	 */
	protected $event_manager;

	protected $html_purify;
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
	protected $model;
	/**
	 * @var mixed
	 */
	protected $parameter;
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
	protected $route;
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
	 * @param $app
	 */

    function __construct(protected ServiceLocator $app)
    {
		$this->base_url 			= $app['config']->setting("site_url");
        $this->config 			= $app['config'];
		//$this->controller 		= $app['router']->controller;
		$this->core   			= $app;
		// $this->cron       = $app['cron'];
		$this->db = $app['database'];
		$this->db_info 			= $app['database_info'];
		// $this->dispatcher = $app['dispatcher'];
		$this->event_manager	= $app['event_manager'];
		// $this->html_purify 		= $app['html_purify'];
		$this->load     		= $app['load'];
		$this->log      		= $app['log'];
		$this->model    		= $app['system_model'];
		$this->parameter    	= $app['router']->parameter;
		$this->profiler 		= $app['profiler'];
		$this->route    		= $app['router'];
		$this->session  		= $app['session'];
		$this->template 		= $app['template'];
		$this->plugin   		= $app['toolbox'];
    }

}