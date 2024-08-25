<?php

namespace Src;
use \Pimple\Container as ServiceLocator;

class Template
{
	/**
	 * @var mixed
	 */
	public $current_page;

	public string $path_to_cache;

	public string $path_to_template_files;

	/**
	 * @var mixed
	 */
	public $twigEnv;

	/**
	 * @var mixed
	 */
	public $twigLoader;

	/**
	 * @var mixed
	 */
	protected $_settings;

	/**
	 * @var mixed
	 */
	protected $app;

	/**
	 * @param ServiceLocator $app
	 */
	public function __construct( ServiceLocator $app )
	{
		$this->app                    = $app;
		$this->_settings              = $app['config'];
		$this->path_to_template_files = $this->_settings->setting( 'template_folder' );
		$this->path_to_cache          = $this->_settings->setting( 'var_path' ) . '/cache/';

		$this->twigLoader = new \Twig\Loader\FilesystemLoader( $this->path_to_template_files );
		$this->twigEnv    = new \Twig\Environment( $this->twigLoader, [
			'auto_reload' => true,
			'cache'       => $this->path_to_cache,
			'debug'       => true,

		] );
		// Add var_dump to template files
		$this->twigEnv->addExtension( new \Twig\Extension\DebugExtension() );
		// Pass some global vars to Twig templates
		$this->twigEnv->addGlobal( 'base_url', $this->_settings->setting( 'site_url' ) );
		$this->twigEnv->addGlobal( 'current_page', $app['router']->controller );
		$this->twigEnv->addGlobal( 'route_action', $app['router']->action );
		$this->twigEnv->addGlobal( 'controllers_path', $this->_settings->setting( 'controllers_path' ) );
		$this->twigEnv->addGlobal( 'views_path', $this->_settings->setting( 'template_folder' ) );
		$this->twigEnv->addGlobal( 'debug_mode', $this->_settings->setting( 'debug_mode' ) );
		$this->twigEnv->addGlobal( 'session', $_SESSION ?? '' );
	}

	/**
	 * @param $template_file
	 * @param array $vars
	 */
	public function render( $template_file, $vars = [] )
	{
		echo $this->twigEnv->render( $template_file, $vars );
	}
}
