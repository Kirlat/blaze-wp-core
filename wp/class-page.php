<?php
namespace Blaze\WP;

/**
 * A base class for a site page
 *
 * Base class implementation contains all necessary functions to render a WP page.
 * It has a main loop implementation. getPosts() method can be used to execute a main loop.
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Page
{
	/**
	 * @var mixed Internal reference to a Site object. Required to get access to Site configuration.
	 */
	protected $site;

	public $themeDir;
	public $themeURI;
	public $siteName;
	public $siteURL;

	/**
	 * An associative array of data that is passed to a page template
	 *
	 * Contains all variables that will be used by a template engine to render a template.
	 * Array key is the varibale name, and an element value can be either variable or a function that will return
	 * a value to insert into a template.
	 *
	 * @var array associative array
	 */
	public $data = array();

	public $posts = array();
	private $serveStaticData;

	/**
	 * Default constructor
	 *
	 * Does not execute WP's main loop by default. Call getPosts() method to do it.
	 */
	public function __construct()
	{
		$this->site = Site::getInstance();
		$this->data['posts'] = array();
		$this->serveStaticData = $this->site->serveStaticData;

		// Copy site data
		$this->themeDir = $this->site->themeDir;
		$this->themeURI = $this->site->themeURI;
		$this->siteName = $this->site->siteName;
		$this->siteURL = $this->site->siteURL;
	}

	public function staticData()
	{
		// Copy site data
		$this->data['themeDir'] = $this->themeDir;
		$this->data['themeURI'] = $this->themeURI;
		$this->data['siteName'] = $this->siteName;
		$this->data['siteURL'] = $this->siteURL;

		$this->data['pagination'] = $this->pagination();
		$this->data['breadcrumbs'] = $this->breadcrumbs();
		$this->data['commentsTemplate'] = $this->commentsTemplate();

		// Menu functions
		$this->data['mainMenuDefault'] = $this->mainMenuDefault();
		$this->data['mainMenuList'] = $this->mainMenuList();
		$this->data['mainMenuBootstrap3'] = $this->mainMenuBootstrap3();

		foreach ($this->posts as $post)
			array_push($this->data['posts'], $post->staticData());

		return $this->data;
	}

	/**
	 * Renders a WP page from several template parts located in a file system
	 *
	 * @param array $template_parts Names of template files in an array
	 */
	public function renderCompositeWpPage(array $template_parts) {
		$html = '';
		$data = $this->serveStaticData? $this->staticData(): $this;
		foreach($template_parts as $file_name) {
			$html .= $this->site->renderer->renderFromFile($file_name, $data);
		}
		get_header();
		echo $html;
		get_footer();
	}

	/**
	 * Implementation of a standard WP's loop
	 * Please note that there are different getData functions for the post.
	 * If you want to call a different getData method, extend WpPage class
	 * and redefine a getPosts method there
	 *
	 * @param $post_type string A type of post to to get
	 */
	public function getPosts($post_type = '\Blaze\WP\Post')
	{
		// Data retrieval
		if (have_posts()) {
			$post_num = 1;
			while (have_posts()) {
				the_post();
				$current_post = new $post_type;
				$current_post->getData();
				$current_post->setSequenceNum($post_num++);
				array_push($this->posts, $current_post);
			}
		}
	}

	/**
	 * An example of a comparison function for usort(). This one sorts post in a reverse
	 * chronological order. Provide your own function if other sort order is required.
	 *
	 * @param $a
	 * @param $b
	 * @return mixed
	 */
	public function timestampDescComp($a, $b)
	{
		return $b->gmtTimestamp - $a->gmtTimestamp;
	}

	public function pagination()
	{
		return Pagination::bootstrapPagination(null);
	}

	public function breadcrumbs()
	{
		return Breadcrumbs::bootstrapBreadcrumbs(null);
	}

	public function commentsTemplate()
	{
		ob_start();
		comments_template();
		$comments_template_html = ob_get_contents();
		ob_end_clean();
		return $comments_template_html;
	}

	public function mainMenuDefault()
	{
		return $this->site->getMainMenu();
	}

	public function mainMenuList()
	{
		return $this->site->getMainMenu(array(
			"format" => "list"));
	}

	public function mainMenuBootstrap3()
	{
		return $this->site->getMainMenu(array(
			"format" => "bootstrap3"));
	}
}