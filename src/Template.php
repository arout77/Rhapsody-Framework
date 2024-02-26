<?php

namespace Src;
use \Twig\Environment;
use \Twig\Loader\ArrayLoader;
use \Twig\Loader\FilesystemLoader;
use \Pimple\Container as ServiceLocator;

class Template {

	private $_settings;
	public string $path_to_template_files;
	public string $path_to_cache;
	public $twigLoader;
	public $twigEnv;

	public function __construct(ServiceLocator $app) {
		$this->_settings = $app['config'];
		$this->path_to_template_files = $this->_settings->setting('template_folder');
		$this->path_to_cache = $this->_settings->setting('var_path').'/cache/';

		$this->twigLoader = new \Twig\Loader\FilesystemLoader($this->path_to_template_files);
		$this->twigEnv = new \Twig\Environment($this->twigLoader, [
			'auto_reload' => true,
			'cache' => $this->path_to_cache,
			'debug' => true,
			
		]);
		$this->twigEnv->addGlobal('base_url', $this->_settings->setting('site_url'));
	}

	public function render($template_file, $vars = [])
	{
		echo $this->twigEnv->render($template_file, $vars);
	}
}
