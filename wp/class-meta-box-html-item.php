<?php
namespace Blaze\WP;

/**
 * Meta Box HTML item
 *
 * Can be inserted between Meta Box controls (i.e. section title).
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Meta_Box_HTML_Item {
	private $html;

	public function __construct($html) {
		$this->html = $html;
	}

	public function getHtml() {
		return $this->html;
	}

	public function getID() {
		return '';
	}
}