<?php
namespace Src;

use Pimple\Container as ServiceLocator;
use Validate\Enums\Boolean;
use Validate\Enums\Error_Reports;

// As of PHP 8.2.0, creating class properties dynamically
// has been deprecated. The following annotation re-enables
// that functionality. All children classes inherit this.
#[\AllowDynamicProperties]

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
	protected $_cron;

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
	protected $core;

	/**
	 * @var mixed
	 */
	protected $db;

	/**
	 * @var string
	 */
	protected $debug_mode;

	/**
	 * @var string
	 */
	protected $maintenance_mode;

	/**
	 * @var string
	 */
	protected $system_startup_check;

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
		$this->config = $app['config'];
		$this->core   = $app;

		/**
		 * Run some sanity checks first. Make sure that .env file contains
		 * valid settings and throw error if not.
		 */
		$debug_mode           = self::validate_enum( 'debug_mode', Boolean::class );
		$debug_toolbar        = self::validate_enum( 'debug_toolbar', Boolean::class );
		$err_reporting        = self::validate_enum( 'error_reports', Error_Reports::class );
		$maintenance_mode     = self::validate_enum( 'maintenance_mode', Boolean::class );
		$system_startup_check = self::validate_enum( 'system_startup_check', Boolean::class );

		$this->base_url   = $app['config']->setting( 'site_url' );
		$this->controller = $app['router']->controller;
		// $this->cron          = $app['cron'];
		$this->db            = $app['database'];
		$this->db_info       = $app['database_info'];
		$this->debug_mode    = $debug_mode;
		$this->error_reports = $err_reporting;
		$this->format        = $app['format'];
		// $this->dispatcher    = $app['dispatcher'];
		// $this->event_manager = $app['event_manager'];
		// $this->html_purify   = $app['html_purify'];
		$this->load                 = $app['load'];
		$this->log                  = $app['log'];
		$this->maintenance_mode     = $maintenance_mode;
		$this->middleware           = $app['middleware'];
		$this->model                = $app['system_model'];
		$this->parameter            = $app['router']->parameter;
		$this->profiler             = $app['profiler'];
		$this->route                = $app['router'];
		$this->session              = $app['session'];
		$this->system_startup_check = $system_startup_check;
		$this->template             = $app['template'];
	}

	/**
	 * @param $params
	 */
	public function throwError( $params, $message = '' )
	{
		$e                = new Error( $this->core );
		$e->message       = $message;
		$e->type          = $params['type'];
		$e->category      = $params['category'];
		$e->triggeredBy   = $params['triggeredBy'];
		$e->object        = $params['object'];
		$e->value         = $params['value'];
		$e->valid_options = $params['valid_options'];
		$e->display();
		exit;
	}

	/**
	 * @param $value
	 * @param $enum
	 * @return mixed
	 */
	private function validate_enum( $value, $enum ): bool | string
	{
		$setting = $enum::tryFrom( strtoupper( $this->config->setting["$value"] ) );
		if ( is_null( $setting ) )
		{
			$params = [
				'type'          => 'Enum',
				'category'      => 'Configuration',
				'triggeredBy'   => $enum,
				'object'        => $value,
				'value'         => $this->config->setting["$value"],
				'valid_options' => $enum::cases(),
			];
			self::throwError( $params );
			return FALSE;
		}
		return $setting->value;
	}
}