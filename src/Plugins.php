<?php

namespace Src;

class Plugins {

/***********************************************************\
| This class is the base class that all plugins which need
| access to system functionality must extend.
\***********************************************************/

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
	protected $load;
	/**
	 * @var mixed
	 */
	public $plugin;

	/**
	 * @param $db
	 * @param $plugin
	 */
	public function __construct($app) 
	{
		$this->config = $app['config'];
		$this->db     = $app['database'];
		$this->load   = $app['load'];
		$this->plugin = $app['toolbox'];
	}

	/**
	 * @param $plugin_name
	 * @return mixed
	 */
	public function plugin($plugin_name) {
		# Load a plugin helper
		return $this->plugin["$plugin_name"];
	}

	/**
	 * @param $model
	 * @return mixed
	 */
	public function model($model) {
		return $this->load->model("$model");
	}

	/**
	 * @param $url
	 */
	public function redirect($url) {
		if ($url === 'http_referer') {
			return header('Location: ' . $_SERVER['HTTP_REFERER']);
			exit;
		}
		return header('Location: ' . SITE_URL . $url);
	}
}