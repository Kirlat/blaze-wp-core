<?php
namespace Blaze\WP;

/**
 * Close comments for old posts
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class CloseComments {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_filter( 'comments_open', array( $this, 'closeComments' ), 10, 2 );
		add_filter( 'pings_open',    array( $this, 'closeComments' ), 10, 2 );
	}

	/**
     * Close comments
     *
     * @since  1.0.0
     * @access public
     * @return bool return true if post meets the conditions
     */
	function closeComments( $open, $post_id, $template = 'page-snippet.php' ) {
		global $wp_query;
		if ( !$post_id ) {
			$post_id = $wp_query->post->ID;
		}
		if ( get_post_meta($post_id, '_wp_page_template', true) == $template && get_option('close_comments_for_old_posts') ) {
			return true;
		}
		return $open;
	}
	
}