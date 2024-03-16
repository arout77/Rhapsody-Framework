<?php

namespace Src;
use \Twig\Environment;
use \Twig\Loader\ArrayLoader;
use \Twig\Loader\FilesystemLoader;
use \Pimple\Container as ServiceLocator;

class Template {

	protected $_settings;
	protected $app;
	public string $path_to_template_files;
	public string $path_to_cache;
	public $twigLoader;
	public $twigEnv;
	public $current_page;

	public function __construct(ServiceLocator $app) {
		$this->app = $app;
		$this->_settings = $app['config'];
		$this->path_to_template_files = $this->_settings->setting('template_folder');
		$this->path_to_cache = $this->_settings->setting('var_path').'/cache/';

		$this->twigLoader = new \Twig\Loader\FilesystemLoader($this->path_to_template_files);
		$this->twigEnv = new \Twig\Environment($this->twigLoader, [
			'auto_reload' => true,
			'cache' => $this->path_to_cache,
			'debug' => true,
			
		]);
		// Pass some global vars to Twig templates
		$this->twigEnv->addGlobal('base_url', $this->_settings->setting('site_url'));
		$this->twigEnv->addGlobal('current_page', $app['router']->controller);
	}

	public function render($template_file, $vars = [])
	{
		echo $this->twigEnv->render($template_file, $vars);
	}
}
