<?php
namespace Blaze\WP;

/**
 * WordPress menu element
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Menu_Element
{
	public $ID;
	public $menu_item_parent;
	protected $link;
	protected $title;
	protected $level;
	public $isParent;

	public $children;

	public function __construct($item)
	{
		$this->ID = $item->ID;
		$this->menu_item_parent = $item->menu_item_parent;
		$this->link = $item->url;
		$this->title = $item->title;
		$this->isParent = false;
		$this->children = array();
		if ($item->classes && !empty($item->classes)) {
			$this->classes = ' ' . implode(' ', $item->classes);
		}
	}

	public function html()
	{
		$html = '';
		if ($this->isParent) {
			// A parent item that has a submenu
			$html .= "<li class='menu-item{$this->classes}'><a href='{$this->link}'>{$this->title}</a></li>";
			// dropdown-submenu class is for compatibility with Tooset's generated Bootstrap menus.
			// // Shall be removed if not needed.
			$html .= '<ul class="sub-menu">';
			foreach ($this->children as $child) {
				$html .= $child->html();
			}
			$html .= "</ul>";
		} else {
			// A regular menu item
			$html .= "<li class='menu-item{$this->classes}'><a href='{$this->link}'>{$this->title}</a></li>";
		}
		return $html;
	}

	public function bootstrap3Html()
	{
		$html = '';
		if ($this->isParent) {
			// A parent item that has a submenu
			$html .= "<li class='menu-item dropdown{$this->classes}'><a href='{$this->link}' class='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false'>{$this->title} <span class='caret'></span></a>";
			$html .= "<ul class='dropdown-menu'>";
			foreach ($this->children as $child) {
				$html .= $child->bootstrap3Html();
			}
			$html .= '</ul>';
		} else {
			// A regular menu item
			$html .= "<li class='menu-item{$this->classes}'><a href='{$this->link}'>{$this->title}</a></li>";
		}
		return $html;
	}
}