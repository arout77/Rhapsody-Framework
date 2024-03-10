<?php

namespace Src\Middleware;

// As of PHP 8.2.0, creating class properties dynamically
// has been deprecated. The following annotation re-enables
// that functionality. All children classes inherit this.
#[\AllowDynamicProperties]

class Helper 
{

	/***********************************************************\
	| This class is the base class that all helpers which need
	| access to system functionality must extend.
	\***********************************************************/

	/**
	 * @var mixed
	 */
	protected static $app;
	/**
	 * @var mixed
	 */
	protected static $config;
	/**
	 * @var mixed
	 */
	protected static $db;
	/**
	 * @var mixed
	 */
	public static $loader;

	/**
	 * @param $db
	 */
	public function __construct(\Pimple\Container $app) 
	{
		self::$config = $app['config'];
		//self::$db     = $app['database'];
		self::$loader   = $app['load'];
		self::$app   = $app['app'];
	}

	/**
	 * @param $helper_name
	 * @return object
	 */
	public function get($helper_name): object 
	{
		# Load a helper
		return $this->load->helper("$helper_name");
	}

	/**
	 * @param $helper_name
	 * @return object
	 */
	public static function getView($file) 
	{
		# Load a helper
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