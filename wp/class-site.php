<?php
namespace Blaze\WP;
use Blaze\Renderers\Renderer;
use Blaze\Utils\Messages;
use Blaze\Utils\Utility;

/**
 * Site functions
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Site {
	protected static $instance;

	private $pageType;
	private $postType;

	public $renderer;
	public $serveStaticData;

	public $wooCommerceSupport;
	public $colorboxSupport;

	public $themeDir;
	public $themeURI;
	public $pathSeparator;
	public $templateDir;
	public $compiledTemplateDir;
	public $siteURL;
	public $siteName;

	public $excerptLength;

	public $mainMenuID;
	private $menus = array();

	private $sidebars = array();

	private $widgets = array();

	private $shortcodes;

	private $dataItems = array();

	public $customizer;

	private $metaboxes = array();

	/**
	 * Returns the instance of WpSite class.
	 *
	 * @return mixed
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * WpSite via the `new` operator from outside of this class.
	 * Sets default configuration parameters some of them acn be changed later, and some cannot
	 */
	protected function __construct()
	{
		// These parameters cannot be reconfigured
		$this->mainMenuID = "main_menu";
		$this->createMainMenu();

		// This parameters can be reconfigured any time and will be applied to all pages
		// created after reconfiguration. Use config() method to change them.
		$this->pageType = '\Blaze\WP\Page';
		$this->postType = '\Blaze\WP\Post';

		$this->pathSeparator = '/';
		$this->themeDir = get_stylesheet_directory();
		$this->themeURI = get_stylesheet_directory_uri();
		$this->siteName = get_bloginfo();
		$this->siteURL = get_bloginfo('url');
		$this->colorboxSupport = false;
		$this->wooCommerceSupport = false;
		$this->templateDir = Utility::forceTrailingSlash($this->themeDir).'views/templates/';
		$this->compiledTemplateDir = Utility::forceTrailingSlash($this->themeDir).'views/compiled/';

		$this->shortcodes = new Shortcodes();

		$this->customizer = new Theme_Customizer();

		$this->renderer = new Renderer($this->templateDir, $this->compiledTemplateDir);
		$this->serveStaticData = false;

		$this->messages = new Messages();
	}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * WpSite instance.
	 *
	 * @return void
	 */
	private function __clone()
	{
	}

	/**
	 * Private unserialize method to prevent unserializing of the WpSite
	 * instance.
	 *
	 * @return void
	 */
	private function __wakeup()
	{
	}

	/**
	 * Changes configuration parameters
	 *
	 * @param array $params
	 */
	public function config(array $params = array()) {
		if (isset($params['pageType'])) $this->pageType = $params['pageType'];
		if (isset($params['postType'])) $this->postType = $params['postType'];
		if (isset($params['pathSeparator'])) $this->pathSeparator = $params['pathSeparator'];
		if (isset($params['templateDir'])) $this->templateDir = $params['templateDir'];
		if (isset($params['themeDir'])) $this->themeDir = $params['themeDir'];
		if (isset($params['themeURI'])) $this->themeURI = $params['themeURI'];
		if (isset($params['siteName'])) $this->siteName = $params['siteName'];
		if (isset($params['siteURL'])) $this->siteURL = $params['siteURL'];
		if (isset($params['colorboxSupport'])) $this->colorboxSupport = $params['colorboxSupport'];
		if (isset($params['wooCommerceSupport'])) $this->wooCommerceSupport = $params['wooCommerceSupport'];
		if (isset($params['serveStaticData'])) $this->serveStaticData = $params['serveStaticData'];
	}

	// Define development mode in wp-config.php as:
	// define('BLAZE_DEV_MODE', true);
	// With this mode on theme loads non-minified versions of CSS and JS files
	public function isDevMode() {
		return defined('BLAZE_DEV_MODE') && BLAZE_DEV_MODE;
	}

	/**
	 * Creates a page with no data in it.
	 *
	 * No DB queries executed
	 *
	 * @param $page_type
	 * @return mixed
	 */
	public function createEmptyPage($page_type)
	{
		$page = new $page_type(self::$instance);
		//$page->cloneConfig($this);
		return $page;
	}

	/**
	 * Creates a page and executes getPosts() method of WpPage to retrieve
	 * post data using WP's standard loop
	 *
	 * @param $page_type string A page class name
	 * @param $post_type string A post class used inside page for the main loop
	 * @return mixed
	 */
	public function createPage($page_type = null, $post_type = null)
	{
		$page_type = empty($page_type)? $this->pageType : $page_type;
		$post_type = empty($post_type)? $this->postType : $post_type;

		$page = new $page_type(self::$instance);
		$page->getPosts($post_type);
		return $page;
	}

	public function createMenu($id) {
		$this->menus[$id] =  new Menu($id);
	}

	public function createMainMenu() {
		$this->menus[$this->mainMenuID] =  new Menu($this->mainMenuID);
	}

	public function getMenu($id, array $params = null)
	{
		return $this->menus[$id]->get($params);
	}

	public function getMainMenu(array $params = null)
	{
		return $this->menus[$this->mainMenuID]->get($params);
	}

	public function createSidebar($id, array $params = null) {
		$params["id"] = $id;
		$this->sidebars[$id] =  new Sidebar($params);
	}

	public function getSidebar($id) {
		return $this->sidebars[$id]->get();
	}

	/**
	 * Creates a new widget
	 *
	 * @param $widget_type - a fully qualified widget class name, i.e. '\Blaze\WP\Widgets\Posts_Widget'
	 * @return mixed - if widget is created successfully, a newly created widget instance is returned.
	 *                 Otherwise, a return value is null
	 */
	public function createWidget($widget_type) {

		// Cannot create widget if type is not provided
		if (!empty($widget_type)) {
			$widget = new $widget_type();
			$id = $widget->getID();
			$this->widgets[$id] =  $widget;
			return $widget;
		}
		return null;
	}

	/**
	 * Type is a name of a function inside WpShortocdes class that will handle it
	 *
	 * @param $name
	 * @param $type
	 */
	public function createShortcode($name, $type) {
		add_shortcode($name, array('\Blaze\WP\Shortcodes', $type));
	}

	public function createDataItem($id, array $params = null) {
		$this->dataItems[$id] =  new Data_Item($id, $params);
		return $this->dataItems[$id];
	}

	public function getDataItem($id, $post_id = null) {
		return $this->dataItems[$id]->getValue($post_id);
	}

	public function getPostDataItem($id, $post_id) {
		return $this->dataItems[$id]->getValue($post_id);
	}

	/**
	 * Creates a new metabox that will be attached to the post/ page
	 * @param $id metabox id
	 * @param array|null $params metabox parameters (optional)
	 * @return mixed
	 */
	public function createMetabox($id, array $params = null) {
		$this->metaboxes[$id] =  new Meta_Box($id, $params);
		return $this->metaboxes[$id];
	}

	// Post URLs to IDs function, supports custom post types - borrowed and modified from url_to_postid() in wp-includes/rewrite.php
	function customURLToPostID($url)
	{
		global $wp_rewrite;

		$url = apply_filters('url_to_postid', $url);

		// First, check to see if there is a 'p=N' or 'page_id=N' to match against
		if ( preg_match('#[?&](p|page_id|attachment_id)=(\d+)#', $url, $values) )	{
			$id = absint($values[2]);
			if ( $id )
				return $id;
		}

		// Check to see if we are using rewrite rules
		$rewrite = $wp_rewrite->wp_rewrite_rules();

		// Not using rewrite rules, and 'p=N' and 'page_id=N' methods failed, so we're out of options
		if ( empty($rewrite) )
			return 0;

		// Get rid of the #anchor
		$url_split = explode('#', $url);
		$url = $url_split[0];

		// Get rid of URL ?query=string
		$url_split = explode('?', $url);
		$url = $url_split[0];

		// Add 'www.' if it is absent and should be there
		if ( false !== strpos(home_url(), '://www.') && false === strpos($url, '://www.') )
			$url = str_replace('://', '://www.', $url);

		// Strip 'www.' if it is present and shouldn't be
		if ( false === strpos(home_url(), '://www.') )
			$url = str_replace('://www.', '://', $url);

		// Strip 'index.php/' if we're not using path info permalinks
		if ( !$wp_rewrite->using_index_permalinks() )
			$url = str_replace('index.php/', '', $url);

		if ( false !== strpos($url, home_url()) ) {
			// Chop off http://domain.com
			$url = str_replace(home_url(), '', $url);
		} else {
			// Chop off /path/to/blog
			$home_path = parse_url(home_url());
			$home_path = isset( $home_path['path'] ) ? $home_path['path'] : '' ;
			$url = str_replace($home_path, '', $url);
		}

		// Trim leading and lagging slashes
		$url = trim($url, '/');

		$request = $url;
		// Look for matches.
		$request_match = $request;
		foreach ( (array)$rewrite as $match => $query) {
			// If the requesting file is the anchor of the match, prepend it
			// to the path info.
			if ( !empty($url) && ($url != $request) && (strpos($match, $url) === 0) )
				$request_match = $url . '/' . $request;

			if ( preg_match("!^$match!", $request_match, $matches) ) {
				// Got a match.
				// Trim the query of everything up to the '?'.
				$query = preg_replace("!^.+\?!", '', $query);

				// Substitute the substring matches into the query.
				$query = addslashes(\WP_MatchesMapRegex::apply($query, $matches));

				// Filter out non-public query vars
				global $wp;
				parse_str($query, $query_vars);
				$query = array();
				foreach ( (array) $query_vars as $key => $value ) {
					if ( in_array($key, $wp->public_query_vars) )
						$query[$key] = $value;
				}

				// Taken from class-wp.php
				foreach ( $GLOBALS['wp_post_types'] as $post_type => $t )
					if ( $t->query_var )
						$post_type_query_vars[$t->query_var] = $post_type;

				foreach ( $wp->public_query_vars as $wpvar ) {
					if ( isset( $wp->extra_query_vars[$wpvar] ) )
						$query[$wpvar] = $wp->extra_query_vars[$wpvar];
					elseif ( isset( $_POST[$wpvar] ) )
						$query[$wpvar] = $_POST[$wpvar];
					elseif ( isset( $_GET[$wpvar] ) )
						$query[$wpvar] = $_GET[$wpvar];
					elseif ( isset( $query_vars[$wpvar] ) )
						$query[$wpvar] = $query_vars[$wpvar];

					if ( !empty( $query[$wpvar] ) ) {
						if ( ! is_array( $query[$wpvar] ) ) {
							$query[$wpvar] = (string) $query[$wpvar];
						} else {
							foreach ( $query[$wpvar] as $vkey => $v ) {
								if ( !is_object( $v ) ) {
									$query[$wpvar][$vkey] = (string) $v;
								}
							}
						}

						if ( isset($post_type_query_vars[$wpvar] ) ) {
							$query['post_type'] = $post_type_query_vars[$wpvar];
							$query['name'] = $query[$wpvar];
						}
					}
				}

				// Do the query
				$query = new \WP_Query($query);
				if ( !empty($query->posts) && $query->is_singular )
					return $query->post->ID;
				else
					return 0;
			}
		}
		return 0;
	}
}