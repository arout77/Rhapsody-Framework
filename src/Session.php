<?php
namespace Src;

// Full documentation for Zebra Session handler can be found here: 
// https://stefangabos.github.io/Zebra_Session/Zebra_Session/Zebra_Session.html

/*-------------------------------------------
 |  Zebra methods available
 |-------------------------------------------
 |
 |	get_active_sessions()
 |	get_settings()
 |	regenerate_id()
 |	set_flashdata()
 |	stop()
 |
 |-----------------------------------------*/

class Session {
	/**
	 * @var mixed
	 */
	public $id;
	/**
	 * @var array
	 */
	public $errors = [];
	/**
	 * @var mixed
	 */
	private $config;

	/**
	 * @param $config
	 */
	public function __construct($config, $db) {
		$this->config = $config;
		$securitycode = "D1@m0ndPhp_By_Andrew-R0ut";
		$cookie_lifetime = $this->config->setting('session.cookie_lifetime');
		// parent::__construct($db, $securitycode, $cookie_lifetime, true, false, '', '', 'session_data', 25, true, false);

	}

	/*--------------------------------------------
		 *	Session handling functions
	*/
	public function data() {
		$data = $_SESSION;

		if (!empty($data)) {

			foreach ($data as $key => $value) {
				echo '<tr><td><strong>' . $key . ':</strong></td><td>' . $value . '</td></tr>';
			}
		} else {
			echo 'No Session Information Available';
		}

	}

	/**
	 * @param $key
	 */
	public function delete($key) {
		# Delete single item from $_SESSION
		$data = $_SESSION[$key];
		session_unset($data);
	}

	public function flush() {
		# Destroy entire session
		self::start();
		$this->id = FALSE;
		session_unset();

		return session_destroy();
	}

	/**
	 * @param $key
	 * @return mixed
	 */
	public function get($key) {
		if (isset($_SESSION["$key"])) {
			return $_SESSION["$key"];
		} else {
			return FALSE;
		}

	}

	/**
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public function set($key, $value) {
		return $_SESSION["$key"] = $value;
	}

	public function start() {

		if (session_status() < 2) {
			session_start([
				'cache_limiter'   => $this->config->setting('session.cache_limiter'),
				'cookie_domain'   => $this->config->setting('session.cookie_domain'),
				'cookie_httponly' => $this->config->setting('session.cookie_httponly'),
				'cookie_lifetime' => $this->config->setting('session.cookie_lifetime'),
				'use_strict_mode' => $this->config->setting('session.use_strict_mode'),
			]);

			$this->id = session_regenerate_id();
		}
	}

}
