<?php

namespace Src\Middleware;

// As of PHP 8.2.0, creating class properties dynamically
// has been deprecated. The following annotation re-enables
// that functionality. All children classes inherit this.
#[\AllowDynamicProperties]

class Helper 
{

	/***********************************************************\
	| This class is the base class that all middlewares which need
	| access to system functionality must extend.
	\***********************************************************/

	/**
	 * @var mixed
	 */
	protected $app;
	/**
	 * @var mixed
	 */
	protected $config;
	/**
	 * @var mixed
	 */
	protected $db;
	/**
	 * @var mixed
	 */
	public $loader;

	/**
	 * @param $db
	 */
	public function __construct(\Pimple\Container $app) 
	{
		$this->config 	= $app['config'];
		$this->db     	= $app['database'];
		$this->loader   = $app['load'];
		$this->app   	= $app['app'];
	}

	/**
	 * @param $middleware_name
	 * @return object
	 */
	public function get($middleware_name): object 
	{
		# Load a middleware
		return $this->app["$middleware_name"];
	}

	/**
	 * @param $middleware_name
	 * @return object
	 */
	public static function getView($file) 
	{
		# Load a middleware
		return self::loadView("$file");
	}

	/**
	 * @param $model
	 * @return object
	 */
	public function model($model) 
	{
		return $this->load->model("$model");
	}

	/**
	 * @param $file
	 * @return object
	 */
	public static function loader() 
	{
		return self::$loader;
	}

	/**
	 * @param $file
	 */
	public static function loadView($file) 
	{
		return self::loader()->view("$file");
	}

	/**
	 * @param $url
	 */
	public function redirect($url) 
	{
		if ($url === 'http_referer') {
			return header('Location: ' . $_SERVER['HTTP_REFERER']);
			exit;
		}
		return header('Location: ' . SITE_URL . $url);
	}
}