<?php
namespace Blaze\Renderers;
use Blaze\Utils\Utility;


/**
 * Renderer
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Renderer {
	private $engines =  array();
	private $defaultEngine;

	private $templateDir;
	private $compiledTemplateDir;

	/**
	 * Default constructor
	 *
	 * @param $template_dir string A directory of where template files are
	 * @param $compiled_template_dir string A directory of where compiled template files are
	 */
	public function __construct($template_dir, $compiled_template_dir)
	{
		$this->templateDir = Utility::forceTrailingSlash($template_dir);
		$this->compiledTemplateDir = Utility::forceTrailingSlash($compiled_template_dir);
	}

	public function register($name, $templateEngine, $params)
	{
		$this->engines[$name] = new $templateEngine($this->templateDir, $this->compiledTemplateDir, $params);
		$isDefault = !isset($params['default']) ? false : $params['default'];
		if ($isDefault || empty($this->engines))
			// Set engine as default on first registration or if specified in parameters
			$this->defaultEngine = $name;
	}

	public function setDefault($name)
	{
		if (isset($this->engines[$name]) || array_key_exists($name, $this->engines)) {
			$this->defaultEngine = $name;
		}
	}

	/**
	 * Renders a template from a template string provided
	 *
	 * In this mode a template will always be compiled on the fly
	 *
	 * @param $template string Template in a string
	 * @param $data array Associative array of data elements (name/ value)
	 * @param null $engine Template engine to use to render a template. If null, default one will be used
	 * @return mixed A rendered template in a string
	 */
	public function render($template, $data, $engine = null)
	{
		if (is_null($engine))
			$engine = $this->defaultEngine;

		// Return an empty string if no rendering engine exists
		if (!isset($this->engines[$engine]) && !array_key_exists($engine, $this->engines))
			return '';

		return $this->engines[$engine]->render($template, $data);
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
	public function renderFromFile($template_file, $data, $engine = null)
	{
		if (is_null($engine))
			$engine = $this->defaultEngine;

		// Return an empty string if no rendering engine exists
		if (!isset($this->engines[$engine]) && !array_key_exists($engine, $this->engines))
			return '';

		return $this->engines[$engine]->renderFromFile($template_file, $data);
	}
}