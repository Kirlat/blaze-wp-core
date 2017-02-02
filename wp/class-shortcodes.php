<?php
namespace Blaze\WP;

/**
 * Shortcodes definitions
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Shortcodes
{

	/**
	 * Initialize the class
	 */
	public function __construct()
	{
	}

	/**
	 * Display a Bootstrap menu
	 * Usage: '[blaze-wp-menu menu-name="menu-slug" format="bootstrap3" levels="3"]'
	 * Two additional parameters are optional
	 *
	 */
	public static function menu($atts)
	{
		$id = empty($atts['menu']) ? null : $atts['menu'];
		$site =  Site::getInstance();
		return $site->getMenu($id, $atts);
	}

	public static function loginToHereLink()
	{
		return wp_login_url(get_permalink());
	}

	public static function postModifiedDate($atts, $content = '')
	{
		$format = $atts['format'];

		return the_modified_date($format, '', '', false);
	}

	public static function filterTerms($atts, $content = '')
	{
		$terms = $atts['terms'];
		$count = $atts['count'];
		$terms_array = explode(",", $terms, $count + 1);
		$terms_sliced = array_slice($terms_array, 0, $count);
		$terms_filtered = implode(", ", $terms_sliced);

		return $terms_filtered;
	}

	public static function createNonceFunc($atts, $content = '')
	{
		$action = $atts['action'];
		$nonce = wp_create_nonce($action);

		return $nonce;
	}
}