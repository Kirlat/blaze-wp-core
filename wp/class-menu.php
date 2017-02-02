<?php
namespace Blaze\WP;

/**
 * Display a WordPress menu
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Menu
{
	private $ID;

	/**
	 * @param $id
	 */
	public function __construct($id)
	{
		$this->ID = $id;
		add_action('after_setup_theme', array($this, 'init'));
	}

	public function init()
	{
		register_nav_menu($this->ID, 'Main Menu');
	}

	public function get(array $params = null)
	{
		$menu_slug = $this->ID;
		$menu_format = !isset($params['format']) ? 'bootstrap3' : $params['format'];
		$levels = !isset($params['levels']) ? 10 : $params['levels']; // Show at most 10 levels by default
		$align = !isset($params['align']) ? 'none' : $params['align'];
		$classes = !isset($params['classNames']) ? '' : $params['classNames'];

		$locations = get_nav_menu_locations();

		if ($locations && isset($locations[$menu_slug])) {

			$menu = wp_get_nav_menu_object($locations[$menu_slug]);
			$menu_items = wp_get_nav_menu_items($menu->term_id, array('order' => 'DESC'));

			if (!isset($menu_items)) return ('<ul><li>Menu items are not defined.</li></ul>');

			$mlist = new Menu_List($menu_items, $menu_slug, $levels, $align, $classes);

			if ($menu_format === 'bootstrap3') {
				return $mlist->bootstrap3Html();

			} else if ($menu_format === 'list') {
				return $mlist->html();
			} else {
				// Display a default WP menu
				ob_start();
				wp_nav_menu(array('menu' => $menu_slug));
				$html = ob_get_contents();
				ob_end_clean();
				return $html;
			}
		} else {
			return '<ul><li>Menu "' . $menu_slug . '" is not defined.</li></ul>';
		}
	}
}