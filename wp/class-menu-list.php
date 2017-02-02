<?php
namespace Blaze\WP;

/**
 * List of WordPress menu elements
 *
 * @copyright   Copyright (c) 2016, Kirill Latyshev
 * @author      Kirill Latyshev <kirill@yula.media>
 */
class Menu_List
{
	public $items;
	private $menu_slug;
	private $max_levels;
	private $align;
	private $classNames;

	public function __construct(array &$elements, $menu_slug, $max_levels, $align, $classNames)
	{
		$this->items = array();
		foreach ($elements as &$element) {
			array_push($this->items, new Menu_Element($element));
		}
		$this->items = $this->buildTree($this->items);
		$this->menu_slug = $menu_slug;
		$this->max_levels = $max_levels;
		$this->align = $align;
		$this->classNames = $classNames;
	}

	// TODO: Implement menu depth limit
	public function buildTree(array &$elements, $parent_id = 0)
	{
		$branch = array();
		foreach ($elements as &$element) {
			if ($element->menu_item_parent == $parent_id) {
				$children = $this->buildTree($elements, $element->ID);
				if ($children) {
					$element->isParent = true;
					$element->children = $children;
				}

				$branch[$element->ID] = $element;
				unset($element);
			}
		}
		return $branch;
	}

	public function html()
	{
		$html = '<ul id="menu-{$this->menu_slug}"';
		foreach ($this->items as $item) {
			$html .= $item->html();
		}
		$html .= "</ul>";
		return $html;
	}

	public function bootstrap3Html()
	{
		$top_level_classes = '';
		if ($this->align === 'left') {
			$top_level_classes = ' navbar-left';
		} else if ($this->align === 'right') {
			$top_level_classes = ' navbar-right';
		}
		else {
			$top_level_classes = '';
		}
		if (!empty($this->classNames))
			$top_level_classes .= ' ' . $this->classNames;

		$html = "<ul id='menu-{$this->menu_slug}' class='nav navbar-nav{$top_level_classes}'>";
		foreach ($this->items as $item) {
			$html .= $item->bootstrap3Html();
		}
		$html .= "</ul>";
		return $html;
	}
}