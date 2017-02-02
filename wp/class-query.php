<?php
namespace Blaze\WP;

/**
 * WP Query wrapper
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Query {
	private $args = null;

	public function __construct($args) {
		$this->args = $args;
	}

	/**
	 * Use this to execute a custom WP query. It takes a post class it will
	 * retrieve as an argument. If not post class provided, it will use a default one.
	 * @param null $post_class a class to which data is retrieved. Default: WpPost
	 * @return array an array of post objects retrieved
	 */
	public function getPosts($post_class = null) {
		$post_list = array();

		// Custom query
		$query = new \WP_Query( $this->args );
		// Check that we have query results.
		if ( $query->have_posts() ) {
			// Start looping over the query results.
			$post_num = 1;
			while ( $query->have_posts() ) {
				$query->the_post();
				if (isset($post_class)) {
					$current_post = new $post_class;
				}
				else {
					$current_post = new Post;
				}
				$current_post->getData();
				$current_post->setSequenceNum($post_num++);
				array_push($post_list, $current_post);
			}
		}
		// Restore original post data.
		wp_reset_postdata();
		return $post_list;
	}
}