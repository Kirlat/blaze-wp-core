<?php
namespace Blaze\WP;
use Blaze\Utils\Utility;

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
	 * @var array A sequence of partial templates. A combination of those templates renders a page. Templates must be added
	 * in an order that should match a desired sequence of templates on a page.
	 */
	public $templateParts = array();

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
	 * Adds a template file from an arbitrary location to a template sequence of a page.
	 * This function is chainable.
	 *
	 * @param $template_file string A fully qualified name of a template file
	 * @return $this Page A Page instance (can be used for chaining)
	 */
	public function addTemplate($template_file) {
		$this->templateParts[] = $template_file;
		return $this;
	}

	/**
	 * Adds a template file from a content directory (see Site->contentTemplatesDirName for a default value)
	 * to a template sequence of a page. This function is chainable.
	 *
	 * @param $template_file string A relative path to a template file
	 * @return $this Page A Page instance (can be used for chaining)
	 */
	public function addContentTemplate($template_file) {
		$this->templateParts[] = $this->site->contentTemplateDir . $template_file;
		return $this;
	}

	/**
	 * Adds a template file from a content directory (see Site->contentTemplatesDirName for a default value)
	 * with a name and location that will match a post type, a root relative path, and a name of a post (page).
	 * Location within a template directory is post-type/root-relative-url/page-name.ext
	 * This function is chainable.
	 *
	 * @return $this Page A Page instance (can be used for chaining)
	 */
	public function addMatchingContentTemplate() {
		$post_url = get_permalink();
		$root_relative_url = substr($post_url, strlen($this->site->siteURL));
		$template_file = get_post_type() . substr($root_relative_url, 0, strlen($root_relative_url)-1) . ".mst";
		$this->templateParts[] = $this->site->contentTemplateDir . $template_file;
		return $this;
	}

	/**
	 * Adds a template file from a theme's template directory (see Site->themeTemplatesDirName for a default value)
	 * to a template sequence of a page. This function is chainable.
	 *
	 * @param $template_file string A relative path to a template file
	 * @return $this Page A Page instance (can be used for chaining)
	 */
	public function addThemeTemplate($template_file) {
		$this->templateParts[] = $this->site->themeTemplateDir . $template_file;
		return $this;
	}

	/**
	 * Renders, if necessary, and displays a sequence of page templates on a web page. The sequence is defined in
	 * $templateParts. This is a chainable function.
	 *
	 * @return $this Page Instance of a Page object
	 */
	public function display() {
		$html = '';
		$data = $this->serveStaticData? $this->staticData(): $this;
		foreach($this->templateParts as $file_name) {
			$html .= $this->site->renderer->renderFromFile($file_name, $data);
		}
		get_header();
		echo $html;
		get_footer();
		return $this;
	}

	/**
	 * Warning! This function has been deprecated. Use combinations of addTemplate() and display() functions instead as shown below:
	 * WP\Site::getInstance()->createPage()->addThemeTemplate("header.mst")->addMatchingContentTemplate()->addThemeTemplate("footer.mst")->display();
	 *
	 * Renders a WP page from several template parts located in a theme's template directory
	 *
	 * @param array $template_parts Names of template files in an array
	 */
	public function renderCompositeWpPage(array $template_parts) {
		$html = '';
		$data = $this->serveStaticData? $this->staticData(): $this;
		foreach($template_parts as $file_name) {
			$html .= $this->site->renderer->renderFromFile($this->site->themeTemplateDir . $file_name, $data);
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

	public function searchForm() {
		return get_search_form(false);
	}

	/**
	 * Returns a URL of a current page or false if page does not exist
	 *
	 * @return false|string - URL of a page (with a trailing slash) or false if page does not exist
	 */
	public function getURL() {
		return get_permalink();
	}
}