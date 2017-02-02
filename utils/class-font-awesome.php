<?php
namespace Blaze\Utils;

/**
 * Font Awesome support
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Font_Awesome {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_shortcode( 'blaze-fa', array( $this, 'faIcon'));
	}

	/**
	 * faIcon shortcode function.
	 *
	 * Shortcode usage: [blaze-fa icon="plus" color="#696969" size="16px"]
	 *
	 * @param $atts
	 * @param string $content
	 * @return string
	 */
	static function faIcon($atts, $content = '') {
		$icon_value = "check";
		if (isset($Arr['icon']) || array_key_exists('icon', $atts)) {
			$icon_value = $atts['icon'];
		}
		$color_value = "#696969";
		if (isset($Arr['color']) || array_key_exists('color', $atts)) {
			$color_value = $atts['color'];
		}
		$font_size_value = "16px";
		if (isset($Arr['size']) || array_key_exists('size', $atts)) {
			$font_size_value = $atts['size'];
		}
		return "<i class='fa fa-{$icon_value}' style='font-size:{$font_size_value};color:{$color_value};'></i>";
	}
}