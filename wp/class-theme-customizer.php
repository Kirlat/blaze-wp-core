<?php
namespace Blaze\WP;

/**
 * A WP theme customizer
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Theme_Customizer {
	private $panels = array();

	public function __construct() {
		$this->panels["defualt"] = new Theme_Customizer_Panel();

		add_action('customize_register', array($this, 'register'));
	}

	public function addSectionDefault($id, array $params = null) {
		return $this->panels["defualt"]->addSection($id, $params);
	}

	public function register(\WP_Customize_Manager $wp_customize) {
		foreach ($this->panels as $panel) {
			$panel->register($wp_customize);
		}
	}
}