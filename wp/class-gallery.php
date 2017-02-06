<?php
namespace Blaze\WP;

/**
 * Display WordPress gallery
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Gallery {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		//add_filter('post_gallery', 'filterGallery', 10, 2);
	}

	function filterGallery($output, $attr)
	{
		global $post;
		$gallery_pagination = false;
		$colorbox_support = false;
		$order = 'DESC'; //Default value

		//GALLERY SETUP STARTS HERE----------------------------------------//
		if (isset($attr['orderby'])) {
			$attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
			if (!$attr['orderby'])
				unset($attr['orderby']);
		}
		//print_r($attr);
		extract(shortcode_atts(array(
			'order' => 'ASC',
			'orderby' => 'menu_order ID',
			'id' => $post->ID,
			'itemtag' => 'dl',
			'icontag' => 'dt',
			'captiontag' => 'dd',
			'columns' => 3,
			'size' => 'thumbnail',
			'include' => '',
			'exclude' => ''
		), $attr));

		//$id = intval($id);
		if ('RAND' == $order) $orderby = 'none';

		if (!empty($include)) {
			$include = preg_replace('/[^0-9,]+/', '', $include);
			$_attachments = get_posts(array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));

			$attachments = array();
			foreach ($_attachments as $key => $val) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		}
		if (empty($attachments)) return '';
		//GALLERY SETUP END HERE------------------------------------------//

		//PAGINATION SETUP START HERE-------------------------------------//
		$current = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$total = sizeof($attachments);
		$per_page = 8;
		//$offset = ($page-1) * $per_page;
		$offset = ($current - 1) * $per_page;
		$big = 999999999; // need an unlikely integer


//    $total_pages = round($total/$per_page);
//    if($total_pages < ($total/$per_page))
//    {   $total_pages = $total_pages+1;
//    }
		if ($gallery_pagination == false) {
			$per_page = $total;
			$total_pages = 1;
		} else {
			$total_pages = ceil($total / $per_page);
		}
		//PAGINATION SETUP END HERE-------------------------------------//


		//GALLERY OUTPUT START HERE---------------------------------------//
		$colorbox_class = '';
		if ($colorbox_support) $colorbox_class = ' gallery-colorbox';
		$output = '';

		$output .= "<div class='carousel slide gallery-images{$colorbox_class}' id='id-image-carousel' data-ride='carousel' data-interval='false'>";
		if ($gallery_pagination == true) {
			$output .= '<div class="carousel-inner gallery-container" role="listbox">';
		}

		$counter = 0;
		$pos = 0;
		foreach ($attachments as $id => $attachment) {
			$pos++;
			//$img = wp_get_attachment_image_src($id, 'medium');
			//$img = wp_get_attachment_image_src($id, 'thumbnail');
			//$img = wp_get_attachment_image_src($id, 'full');

			if (($counter < $per_page) && ($pos > $offset)) {
				$counter++;
				$largetitle = get_the_title($attachment->ID);
				$largeimg = wp_get_attachment_image_src($id, 'large');
				$img = wp_get_attachment_image_src($id, 'thumbnail');
				/*$output .= "<figure class='gallery-item' itemprop='associatedMedia' itemscope='' itemtype='http://schema.org/ImageObject'>";
				$output .= " <a href=\"{$largeimg[0]}\" class='colorbox' data-lightbox=\"example-set\" title=\"{$largetitle}\"><img src=\"{$img[0]}\" width=\"{$img[1]}\" height=\"{$img[2]}\" alt=\"\" /></a>\n";
				$output .= '</figure>';*/
			}

			$largetitle = get_the_title($attachment->ID);
			$largeimg = wp_get_attachment_image_src($id, 'full');
			$img = wp_get_attachment_image_src($id, 'thumbnail');
			$caption = pippin_excerpt_by_id($attachment->ID);
			$altText = get_post_meta(get_post_thumbnail_id($attachment->ID), '_wp_attachment_image_alt', true);
			$slideStart = false;
			$slideEnd = false;
			if ( ($pos % $per_page) == 1) {
				$slideStart = true;
			}
			if ( ($pos % $per_page) == 0 ) {
				$slideEnd = true;
			}
			if ($gallery_pagination == true && $slideStart) {
				$slideStatus = '';
				if ($pos == 1) $slideStatus = ' active';
				$output .= "<div class='item{$slideStatus}'>
		<div>";
			}

			$output .= "<figure class='gallery-item' itemprop='associatedMedia' itemscope='' itemtype='http://schema.org/ImageObject'>";
			$output .= "<img class='gallery-item_img' src=\"{$img[0]}\" alt=\"{$altText}\" />\n";
			$output .= " <figcaption><p>{$caption}</p><a href=\"{$largeimg[0]}\" class='colorbox' data-lightbox=\"example-set\" title=\"{$largetitle}\"></a></figcaption>\n";
			$output .= '</figure>';
			if ($gallery_pagination == true && $slideEnd) {
				$output .= "</div><!--4--></div><!--5-->";
			}

		}
		if ($gallery_pagination == true && !$slideEnd) {
			$output .= "</div><!--3--></div><!--2-->";
		}
		if ($gallery_pagination == true) {
			$output .= '</div><!--1-->';
		}
		if ($total_pages > 1) {
			$output .= "<ol class='carousel-indicators'>";
			for ($index = 0; $index < $total_pages; $index++) {
				$active = ($index == 0)? "class='active'": "";
				$output .= "<li data-target='#id-image-carousel' data-slide-to='{$index}' {$active}></li>";
			}
			$output .= "</ol>";
		}
		$output .= "<div class=\"clear\"></div>\n";
		$output .= "</div>\n";
		//GALLERY OUTPUT ENDS HERE---------------------------------------//


		//PAGINATION OUTPUT START HERE-------------------------------------//
		/*$output .= paginate_links( array(
			'base' => str_replace($big,'%#%',esc_url(get_pagenum_link($big))),
			'format' => '?paged=%#%',
			'current' => $current,
			'total' => $total_pages,
			'prev_text'    => __('&laquo;'),
			'next_text'    => __('&raquo;')
		) );*/
		//PAGINATION OUTPUT ENDS HERE-------------------------------------//

		return $output;
	}
}