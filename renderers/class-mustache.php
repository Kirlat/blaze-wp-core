<?php
namespace Blaze\Renderers;
use Blaze\Utils;

/**
 * Mustache Template Engine class
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Mustache extends Template_Engine {
	private $engine;
	private $autoloader;
	private $libPath;
	private $autoloaderPath;

	public function __construct($template_dir, $compiled_template_dir, array $params = null) {
		parent::__construct($template_dir, $compiled_template_dir, $params);
		$this->autoloader = !isset($params['autoloader']) ? 'src/Mustache/Autoloader.php' : $params['autoloader'];
		$this->libPath = !isset($params['libPath']) ? dirname(dirname(dirname( __FILE__ ))) . '/mustache.php-2.9.0/' : $params['libPath'];
		$this->libPath = Utils\Utility::forceTrailingSlash($this->libPath);
		$this->autoloaderPath = $this->libPath . $this->autoloader;
		require $this->autoloaderPath;
		\Mustache_Autoloader::register();

		$this->engine = !isset($params['engine']) ? new \Mustache_Engine : $params['engine'];
	}

	public function render($template, $data) {
		return $this->engine->render($template, $data);
	}

	public function renderFromFile($template_file_name, $data)
	{
		// check if template file exists
		if (!is_file($template_file_name))
			return '';

		$template = file_get_contents($template_file_name);

		return $this->render($template, $data);
	}
}