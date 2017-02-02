<?php
namespace Blaze\WP;
use Blaze\Utils;

/**
 * Display a WordPress sidebar
 *
 * @copyright   Copyright (c) 2016, Kirill Latyshev
 * @author      Kirill Latyshev <kirill@yula.media>
 */
class Sidebar
{
	private $ID;
	private $name;
	private $class;
	private $description;
	private $beforeWidget;
	private $afterWidget;
	private $beforeTitle;
	private $afterTitle;

	public function __construct(array $params)
	{
		$this->ID = !isset($params['id']) ? Utils\Utility::getRandomString(): $params['id'];
		$this->name = !isset($params['name']) ? 'Anonymous Sidebar': $params['name'];
		$this->description = !isset($params['description']) ? '': $params['description'];
		$this->class = !isset($params['class']) ? '': $params['class'];
		$this->beforeWidget = !isset($params['beforeWidget']) ? '': $params['beforeWidget'];
		$this->afterWidget = !isset($params['afterWidget']) ? '': $params['afterWidget'];
		$this->beforeTitle = !isset($params['beforeTitle']) ? '': $params['beforeTitle'];
		$this->afterTitle = !isset($params['afterTitle']) ? '': $params['afterTitle'];
		if ((isset($params['hideTitle']) || array_key_exists('hideTitle', $params)) && $params['hideTitle']) {
			$this->beforeTitle = '<div class="hidden">';
			$this->afterTitle = '</div>';
		}
		add_action('widgets_init', array($this, 'init'));
	}

	public function init()
	{
		register_sidebar( array(
			'name' => $this->name,
			'id' => $this->ID,
			'description' => $this->description,
			'class' => $this->class,
			'before_widget' => $this->beforeWidget,
			'after_widget'  => $this->afterWidget,
			'before_title'  => $this->beforeTitle,
			'after_title'   => $this->afterTitle,
		) );
	}

	public function get() {
		$sidebar_html = '';
		if ( is_active_sidebar($this->ID)) {
			ob_start();
			dynamic_sidebar($this->ID);
			$sidebar_html = ob_get_contents();
			ob_end_clean();
		}
		return $sidebar_html;
	}
}