<?php
namespace Blaze\Renderers;
use Blaze\Utils\Utility;

/**
 * Lightn_Candy Template Engine class
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Lightn_Candy extends Template_Engine {
	private $autoloader;
	private $libPath;
	private $autoloaderPath;

	private $mode;
	const MD_PRECOMPILE = 'precompiled';
	const MD_COMPILE = 'compile';
	const MD_AUTO = 'auto';

	public function __construct($template_dir, $compiled_template_dir, array $params = null) {
		parent::__construct($template_dir, $compiled_template_dir, $params);
		$this->autoloader = !isset($params['autoloader']) ? 'src/loader.php' : $params['autoloader'];
		$this->libPath = !isset($params['libPath']) ? dirname(dirname(dirname( __FILE__ ))) . '/lightncandy-0.9.4/' : $params['libPath'];
		$this->libPath = Utility::forceTrailingSlash($this->libPath);
		$this->autoloaderPath = $this->libPath . $this->autoloader;
		require $this->autoloaderPath;

		$this->mode = !isset($params['mode']) ? Lightn_Candy::MD_AUTO : $params['mode'];
	}

	private function compile($template) {
	   return \LightnCandy\LightnCandy::compile($template, Array(
		   'flags' => \LightnCandy\LightnCandy::FLAG_MUSTACHELOOKUP
));
	}

	public function compileToFile($template_file_name, $compiled_file_name)
	{
		if (!is_file($template_file_name))
			return;

		$template = file_get_contents($template_file_name);
		$php_str = '<?php ';
		$php_str .= $this->compile($template);  // set compiled PHP code into a string
		$php_str .= '?>';

		// Save the compiled PHP code into a php file
		file_put_contents($compiled_file_name, $php_str);
	}

	public function renderFromCompiledFile($compiled_file_name, $data)
	{
		if (!is_file($compiled_file_name))
			return '';

		$renderer = include $compiled_file_name;
		/** @var callable $renderer */
		$html = '';
		if (!is_null($renderer)) {
			$html = $renderer($data);
		}
		return $html;
	}

	public function getCompiledFileName($template_file)
	{
		return $template_file . '.php';
	}

	public function render($template, $data) {
		$renderer = null;
		$random_string = Utility::getRandomString();
		// Create a temporary file, save compiled template there and then delete it
		// TODO: find a way to avoid creating a temporary file
		$file_name = $this->compiledTemplateDir . 'cttmp-' . $random_string . '.php';
		$php_str = '<?php ';
		$php_str .= $this->compile($template);  // set compiled PHP code into a string
		$php_str .= '?>';

		// Save the compiled PHP code into a php file
		file_put_contents($file_name, $php_str);

		// Get the render function from the php file
		if (is_file($file_name)) {
			$renderer = include $file_name;
		}

		// Delete a temporary file
		if (is_file($file_name)) {
			unlink($file_name);
		}
		/** @var callable $renderer */
		$html = '';
		if (!is_null($renderer)) {
			$html = $renderer($data);
		}
		return $html;

	}

	/**
	 * Renders a template from a file
	 *
	 * Depending on the mode value:
	 *   MD_COMPILE: template will always be compiled before rendering. Compiled template will be saved in a file according to path settings
	 *   MD_PRECOMPILE: a precompiled template file will be used to render a template. If no precompiled file exists, templated will be compiled
	 *                  and saved to that file first
	 *   MD_AUTO: if precompiled file exists, and its modification date is no older than the template file modification date, a precompiled
	 *            template will be used for rendering. In all other cases a template will be compiled and saved to a file before rendering
	 *
	 * @param $template_file string Name of a template file
	 * @param $data $data array Associative array of data elements (name/ value)
	 * @param string $mode Rendering mode: MD_COMPILE, MD_PRECOMPILE, MD_AUTO
	 * @param null $engine Template engine to use to render a template. If null, default one will be used
	 * @return mixed A rendered template in a string
	 */
	public function renderFromFile($template_file, $data)
	{
		// TODO: create subdirectories in compiled folder if does not exist
		$template_file_name = $this->templateDir . $template_file;
		// check if template file exists
		if (!is_file($template_file_name))
			return '';
		$compiled_file_name = $this->compiledTemplateDir . $this->getCompiledFileName($template_file);
		if ($this->mode == Lightn_Candy::MD_COMPILE) {
			$this->compileToFile($template_file_name, $compiled_file_name);
			return $this->renderFromCompiledFile($compiled_file_name, $data);
		}
		else if ($this->mode == Lightn_Candy::MD_PRECOMPILE) {
			// If no precompiled file exists, create one
			if (!is_file($compiled_file_name)) {
				$this->compileToFile($template_file_name, $compiled_file_name);
			}
			return $this->renderFromCompiledFile($compiled_file_name, $data);
		}
		else if ($this->mode == Lightn_Candy::MD_AUTO) {
			// If no precompiled file exists, create one
			if (!is_file($compiled_file_name)) {
				$this->compileToFile($template_file_name, $compiled_file_name);
			}
			else {
				// If no precompiled file exist check if template is newer or not
				if (filemtime($template_file_name) > filemtime($compiled_file_name)) {
					// If template is newer, recompile it
					$this->compileToFile($template_file_name, $compiled_file_name);
				}

			}
			return $this->renderFromCompiledFile($compiled_file_name, $data);
		}
		else {
			// Unknown mode specified
			return '';
		}
	}
}