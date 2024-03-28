<?php
namespace Src;

class Config {

	public $setting = [];
	private $db;
	const DS = DIRECTORY_SEPARATOR;

	public function __construct($env) {

		# Database Connection
		$this->setting['db_type'] = $env->get_global_configuration('db_type');
		$this->setting['db_host'] = $env->get_global_configuration('db_host');
		$this->setting['db_port'] = $env->get_global_configuration('db_port');
		$this->setting['db_name'] = $env->get_global_configuration('db_name');
		$this->setting['db_user'] = $env->get_global_configuration('db_user');
		$this->setting['db_pass'] = $env->get_global_configuration('db_pass');

		# SMTP settings
		// $this->setting['smtp_host'] = $env->get_global_configuration('smtp_host');
		// $this->setting['smtp_name'] = $env->get_global_configuration('smtp_name');
		// $this->setting['smtp_username'] = $env->get_global_configuration('smtp_username');
		// $this->setting['smtp_password'] = $env->get_global_configuration('smtp_password');
		// $this->setting['smtp_port'] = $env->get_global_configuration('smtp_port');
		// $this->setting['smtp_auth'] = $env->get_global_configuration('smtp_auth');
		// $this->setting['smtp_secure'] = $env->get_global_configuration('smtp_secure');
		// $this->setting['smtp_html'] = $env->get_global_configuration('smtp_html');
		// $this->setting['smtp_debug'] = $env->get_global_configuration('smtp_debug');
		$this->setting['mailer_dsn'] = $env->get_global_configuration('MAILER_DSN');

		# Default controller
		$this->setting['default_controller'] = $env->get_global_configuration('default_controller');

		# Define the site name
		$this->setting['site_name'] = $env->get_global_configuration('site_name');

		# Does your website/company have a tagline or slogan?
		$this->setting['site_slogan'] = $env->get_global_configuration('site_slogan');

		# Customer service or support email address
		$this->setting['site_email'] = $env->get_global_configuration('site_email');

		# Address
		$this->setting['street_address'] = $env->get_global_configuration('street_address');
		$this->setting['city'] = $env->get_global_configuration('city');
		$this->setting['state'] = $env->get_global_configuration('state');
		$this->setting['zipcode'] = $env->get_global_configuration('zipcode');

		# Phone
		$this->setting['telephone'] = $env->get_global_configuration('telephone');

		# Time Zone
		$this->setting['time_zone'] = $env->get_global_configuration('time_zone');

		$this->setting['error_reports'] = $env->get_global_configuration('error_reports');

		$this->setting['debug_mode'] = $env->get_global_configuration('debug_mode');
		$this->setting['debug_toolbar'] = $env->get_global_configuration('debug_toolbar');

		$this->setting['log_errors'] = $env->get_global_configuration('log_errors');
		$this->setting['log_path'] = $env->get_global_configuration('base_path').DS.$env->get_global_configuration('log_path');
		$this->setting['log_file_max_size'] = $env->get_global_configuration('log_file_max_size');

		# Name of the directory storing template files ( css/js/img, etc. )
		$this->setting['template_name'] = $env->get_global_configuration('template_name');

		# Put site in maintenance mode
		$this->setting['maintenance_mode'] = $env->get_global_configuration('maintenance_mode');

		# Check for common issues preventing system from running
		$this->setting['system_startup_check'] = $env->get_global_configuration('system_startup_check');

		$this->setting['compression'] = $env->get_global_configuration('compression');

		$this->setting['site_url'] = $env->get_global_configuration('site_url');

		#== Global system settings ==#

		# Name of subdirectory folder, if installed in subdirectory
		$this->setting['subdir'] = $env->get_global_configuration('subdir');

		# Encode or remove dangerous tags in forms
		$this->setting['html_encode_tags'] = $env->get_global_configuration('html_encode_tags');

		# Sanitize or remove all html tags
		$this->setting['strip_all_tags'] = $env->get_global_configuration('strip_all_tags');

		# Base directory where application should begin search for files
		# if folders were moved
		if($env->get_global_configuration('base_path') == "")
		{
			if($this->setting['subdir'] == "")
				$this->setting['base_path'] = $_SERVER['DOCUMENT_ROOT'] . self::DS;
			else 
				$this->setting['base_path'] = $_SERVER['DOCUMENT_ROOT'] . self::DS . $this->setting['subdir'];
		} else {
			$this->setting['base_path'] = $env->get_global_configuration('base_path') . self::DS;
		}

		# Location of app folder
		$this->setting['app_path'] = $this->setting['base_path'] . $env->get_global_configuration('app_path');

		# Location of the src directory
		$this->setting['system_path'] = $this->setting['base_path'] . $env->get_global_configuration('system_folder');

		# Location of the plugins directory
		$this->setting['plugins_path'] = $this->setting['system_path'] . self::DS . 'middleware' . self::DS;

		# Location of the public directory
		$this->setting['public_path'] = $env->get_global_configuration('public_path');

		# Controllers directory
		$this->setting['controllers_path'] = $this->setting['app_path'] . self::DS . 'controllers' . self::DS;

		# Models directory
		$this->setting['models_path'] = $this->setting['app_path'] . self::DS . 'models' . self::DS;

		# Var folder
		$this->setting['var_path'] = $this->setting['base_path'] . self::DS . $env->get_global_configuration('var_path');

		# Vendor folder
		$this->setting['vendor_folder'] = $this->setting['base_path'] . self::DS . 'vendor' . self::DS;

		# Location of template directory
		$this->setting['template_folder'] = $this->setting['app_path'] . 'template/views/';

		# Template URL for fetching CSS / JS / IMG files
		$this->setting['template_url'] = $this->setting['site_url'] . 'public/';

		# Enable / disable Memcached helper
		if (extension_loaded('memcached')) {
			$this->setting['memcached'] = TRUE;
		} else {
			$this->setting['memcached'] = FALSE;
		}

		# Measure script execution time
		$this->setting['execution_time'] = (microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"]);

		# Session settings
		# Session cookie name
		# Give this a unique name
		$this->setting['session.name'] = $env->get_global_configuration('session.name');
		# Recommended to leave this enabled for session security. 0 = disabled 1 = enabled
		$this->setting['session.use_strict_mode'] = $env->get_global_configuration('session.use_strict_mode');
		# Default setting is zero; i.e. until browser is closed
		# Set this value in seconds if you wish to change the default behavior
		$this->setting['session.cookie_lifetime'] = $env->get_global_configuration('session.cookie_lifetime');
		# Leave blank for default settings; otherwise you can specify the host name of your server here
		$this->setting['session.cookie_domain'] = $env->get_global_configuration('session.cookie_domain');
		# Marks the cookie as accessible only through the HTTP protocol.
		# This means that the cookie won't be accessible by scripting languages, such as JavaScript.
		# This setting can effectively help to reduce identity theft through XSS attacks (although it is not supported by all browsers).
		$this->setting['session.cookie_httponly'] = $env->get_global_configuration('session.cookie_httponly');
		# Default is nocache. [nocache, private, private_no_expire, public]
		# See http://php.net/manual/en/function.session-cache-limiter.php for more information about each setting.
		$this->setting['session.cache_limiter'] = $env->get_global_configuration('session.cache_limiter');

		# Release version
		$this->setting['software_version'] = '0.92.1';
	}

	public final function setting($setting) {
		return $this->setting["$setting"];
	}

	/**
	 * Private clone method to prevent cloning of the instance
	 *
	 * @return void
	 */
	private function __clone() {}

	/**
	 * Unserialize method to prevent unserializing
	 *
	 * @return void
	 */
	public function __wakeup() {}

}
