<?php
namespace Blaze\WP;

/**
 * Displays a WordPress pagination controls
 *
 * @copyright   Copyright (c) 2016, Kirill Latyshev
 * @author      Kirill Latyshev <kirill@yula.media>
 */
class Pagination
{

	/**
	 * Initialize the class
	 */
	public function __construct()
	{
		add_shortcode('blaze-bs-pagination', array('\Blaze\WP\Pagination', 'bootstrapPagination'));
	}

	public static function bootstrapPagination($args = array())
	{
		$defaults = array(
			'range' => 4,
			'custom_query' => FALSE,
			'previous_string' => __('<i class="fa fa-angle-left"></i>', 'blaze-core'),
			'next_string' => __('<i class="fa fa-angle-right"></i>', 'blaze-core'),
			'before_output' => '<div class="post-nav container"><ul class="pagination">',
			'after_output' => '</ul></div>'
		);

		$args = wp_parse_args(
			$args,
			apply_filters('wp_bootstrap_pagination_defaults', $defaults)
		);

		$args['range'] = (int)$args['range'] - 1;
		if (!$args['custom_query'])
			$args['custom_query'] = @$GLOBALS['wp_query'];
		$count = (int)$args['custom_query']->max_num_pages;
		$page = intval(get_query_var('paged'));
		$ceil = ceil($args['range'] / 2);

		if ($count <= 1)
			return FALSE;

		if (!$page)
			$page = 1;

		if ($count > $args['range']) {
			if ($page <= $args['range']) {
				$min = 1;
				$max = $args['range'] + 1;
			} elseif ($page >= ($count - $ceil)) {
				$min = $count - $args['range'];
				$max = $count;
			} elseif ($page >= $args['range'] && $page < ($count - $ceil)) {
				$min = $page - $ceil;
				$max = $page + $ceil;
			}
		} else {
			$min = 1;
			$max = $count;
		}

		$html = '';
		$previous = intval($page) - 1;
		$previous = esc_attr(get_pagenum_link($previous));

		$firstpage = esc_attr(get_pagenum_link(1));
		if ($firstpage && (1 != $page))
			$html .= '<li class="previous"><a href="' . $firstpage . '">' . __('First', 'text-domain') . '</a></li>';

		if ($previous && (1 != $page))
			$html .= '<li><a href="' . $previous . '" title="' . __('previous', 'text-domain') . '">' . $args['previous_string'] . '</a></li>';

		if (!empty($min) && !empty($max)) {
			for ($i = $min; $i <= $max; $i++) {
				if ($page == $i) {
					$html .= '<li class="active"><span class="active">' . str_pad((int)$i, 2, '0', STR_PAD_LEFT) . '</span></li>';
				} else {
					$html .= sprintf('<li><a href="%s">%002d</a></li>', esc_attr(get_pagenum_link($i)), $i);
				}
			}
		}

		$next = intval($page) + 1;
		$next = esc_attr(get_pagenum_link($next));
		if ($next && ($count != $page))
			$html .= '<li><a href="' . $next . '" title="' . __('next', 'text-domain') . '">' . $args['next_string'] . '</a></li>';

		$lastpage = esc_attr(get_pagenum_link($count));
		if ($lastpage) {
			$html .= '<li class="next"><a href="' . $lastpage . '">' . __('Last', 'text-domain') . '</a></li>';
		}

		if (isset($html))
			return $args['before_output'] . $html . $args['after_output'];
	}
}