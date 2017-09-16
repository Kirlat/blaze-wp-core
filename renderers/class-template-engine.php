<?php
namespace Blaze\Renderers;
use Blaze\Utils\Utility;

/**
 * Template Engine Base class
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Template_Engine {
	public $templateDir;
	public $compiledTemplateDir;

	public function __construct($template_dir, $compiled_template_dir, array $params = null)
	{
		$this->templateDir = Utility::forceTrailingSlash($template_dir);
		$this->compiledTemplateDir = Utility::forceTrailingSlash($compiled_template_dir);
	}

	/**
	 * Render in memory, without creating any precompiled files
	 *
	 * @param $template string A template in a string
	 * @param $data Data to use for rendering
	 */
	public function render($template, $data)
	{

	}

	public function renderFromFile($template_file_name, $data)
	{

	}
}