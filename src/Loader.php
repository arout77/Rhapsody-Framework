<?php
namespace Src;

class Loader {

	# The file being requested
	/**
	 * @var mixed
	 */
	public $_file;
	# The directory containing requested file
	/**
	 * @var mixed
	 */
	protected $_dir;
	/**
	 * @var mixed
	 */
	protected $db;
	/**
	 * @var mixed
	 */
	public $toolbox;
	/**
	 * @var mixed
	 */
	public $data;
	/**
	 * @var mixed
	 */
	protected $config;
	/**
	 * @var mixed
	 */
	protected $session;
	/**
	 * @var mixed
	 */
	protected $log;

	/**
	 * @param $c
	 */
	public function __construct($c) {

		$this->db      = $c['database'];
		$this->toolbox = $c['toolbox'];
		$this->config  = $c['config'];
		$this->session = $c['session'];
		$this->log     = $c['log'];
	}

	/**
	 * @param $file
	 * @param $data
	 */
	public function block($file, $data = NULL) {

		$dir = BLOCKS_DIR;

		if (is_readable($dir . $file)) {
			require_once $dir . $file;
		} else {
			$filename = $dir . $file;
			$this->log->save("Error opening {$filename}", 'system.log');
			self::viewerror($filename, $data);
		}
	}

	/**
	 * @param $file
	 * @param $full_path
	 */
	public function file($file, $full_path = false) {

		if (!$full_path) {
			# Start file search from root directory by default
			$filename = BASE_PATH . $file;
		} else {
			# Or search full path, if specified
			$filename = $file;
		}

		if (is_readable($filename)) {
			require_once $filename;
		} else {
			$this->log->save("Error opening {$filename}", 'system.log');
			echo '<div class="alert alert-danger"><h1>Fatal Error</h1>
			<h4>Could not load file: ' . $filename . '</h4>
			Please ensure that the file exists and permission to read the file (644)
			</div>';
		}
	}

	/**
	 * @param $file
	 * @return mixed
	 */
	public function model($file) {

		$dir  = MODELS_PATH;
		$file = ucwords($file);

		if (is_readable(PUBLIC_OVERRIDE_PATH . 'models/' . $file . 'Model.php')) {
			require_once PUBLIC_OVERRIDE_PATH . 'models/' . $file . 'Model.php';
			$this->model        = $file . 'Model';
			return $this->model = new $this->model($this->toolbox);
		} elseif (is_readable($dir . $file . 'Model.php')) {
			require_once $dir . $file . 'Model.php';
			$this->model        = '\Web\Model\\' . $file . 'Model';
			return $this->model = new $this->model($this->toolbox);
		} else {
			$filename = $dir . $file . 'Model.php';
			$this->log->save("Error opening {$filename}", 'system.log');
			require_once $dir . 'errors/model.php';
		}
	}

	/**
	 * @param $helper
	 * @return mixed
	 */
	public function toolbox($helper) {
		# Load a Toolbox helper
		return $this->toolbox["$helper"];
	}

	/**
	 * @param $file
	 * @param $data
	 */
	public function view($file, $data = NULL) {

		$dir = VIEWS_PATH;

		if (is_readable($dir . $file . '.tpl')) {
			require_once $dir . $file . '.tpl';
		} else {
			$filename = $dir . $file . '.tpl';
			$this->log->save("Error opening {$filename}", 'system.log');
			self::viewerror($filename, $data);
		}
	}

	/**
	 * @param $filename
	 * @param $data
	 */
	public function viewerror($filename, $data = NULL) {

		if (is_readable($filename)) {
			require_once $filename;
		} else {
			//require_once(VIEWS_DIR.'error/view.php');
		}
	}

	/**
	 * @param $file
	 * @param $data
	 * @param NULL $app
	 */
	public function template($file, $data = NULL, $app = null) {

		$dir  = $this->config->setting('template_folder');
		$file = $dir . $file;

		if (is_readable($file)) {
			require_once $file;
		} else {
			$this->log->save("Error opening {$file}", 'system.log');
			self::viewerror('errors/template.php', $data);
		}
	}

	/**
	 * @param $file
	 * @param $data
	 * @param NULL $app
	 */
	public function admin_template($file, $data = NULL, $app = null) {

		$dir  = $this->config->setting('admin_template_folder');
		$file = $dir . $file;

		if (is_readable($file)) {
			require_once $file;
		} else {
			$this->log->save("Error opening {$file}", 'system.log');
			self::viewerror('errors/template.php', $data);
		}
	}
}
