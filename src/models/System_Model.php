<?php
namespace Src\Model;

use RedBeanPHP\R;

class System_Model extends R
{
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
	public $session;

	/**
	 * @var mixed
	 */
	protected $block;

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
	protected $middleware;

	/**
	 * @param $db
	 * @param $plugin
	 * @param $config
	 */
	public function __construct( \Pimple\Container $app )
	{
		$this->db     = $app['database'];
		$this->config = $app['config'];
		$this->log    = $app['log'];
		//$this->hash         = self::hash();
	}

	/**
	 * @param $string
	 * @return mixed
	 */
	public function encrypt( $string )
	{
		# Encrypt using password_hash()
		$hash = new \App\Plugin\Hash;
		return $hash->encrypt( $string );
	}

	/**
	 * @param $string
	 * @param $base
	 * @return mixed
	 */
	public function verify( $string, $base )
	{
		# Decrypt hash from encrypt()
		$hash = new \App\Plugin\Hash;
		return $hash->verify( $string, $base );
	}
}