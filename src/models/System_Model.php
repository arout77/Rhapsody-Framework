<?php
namespace Src\Model;

class System_Model {
	/**
	 * @var mixed
	 */
	protected $block;
	/**
	 * @var mixed
	 */
	protected $db;
	/**
	 * @var mixed
	 */
	protected $config;
	/**
	 * @var mixed
	 */
	public $session;
	// Data accessed by views / controllers
	/**
	 * @var mixed
	 */
	public $data;
	/**
	 * @var mixed
	 */
	public $hash;
	/**
	 * @var mixed
	 */
	public $log;
	/**
	 * @var mixed
	 */
	protected $helper;
	/**
	 * @var mixed
	 */
	protected $load;

	/**
	 * @param $db
	 * @param $plugin
	 * @param $config
	 */
	public function __construct(\Pimple\Container $app) {
		$this->db      = $app['database'];
		$this->config  = $app['config'];
		$this->helper  = $app['plugin_core'];
		$this->log     = $app['log'];
		$this->load     = $app['load'];
		$this->session = self::session();
		//$this->hash         = self::hash();
	}

	/**
	 * @param $string
	 * @return mixed
	 */
	public function encrypt($string) {
		# Encrypt using password_hash()
		$hash = new \App\Plugin\Hash;
		return $hash->encrypt($string);
	}

	/**
	 * @param $string
	 * @param $base
	 * @return mixed
	 */
	public function verify($string, $base) {
		# Decrypt hash from encrypt()
		$hash = new \App\Plugin\Hash;
		return $hash->verify($string, $base);
	}

	/**
	 * @return mixed
	 */
	public function session() {
		return $this->load->helper('SessionHelper');
	}

	/**
	 * @param $helper
	 * @return mixed
	 */
	public function plugin($helper) {
		# Load a plugin helper
		return $this->helper->get("$helper");
	}

}