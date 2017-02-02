<?php
namespace Blaze\WP;

/**
 * A WP theme customizer panel
 *
 * @copyright   Copyright (c) 2016, Kirill Latyshev
 * @author      Kirill Latyshev <kirill@yula.media>
 */
class Theme_Customizer_Panel {
	private $ID;

	private $sections = array();

	public function __construct($id = null) {
		$this->ID = !$id? "default" : $id;
	}

	public function addSection($id, array $params = null)
	{
		$this->sections[$id] = new Theme_Customizer_Section($id, $params);
		return $this->sections[$id];
	}

	public function register(\WP_Customize_Manager $wp_customize) {
		foreach ($this->sections as $section) {
			$section->register($wp_customize);
		}
	}
}