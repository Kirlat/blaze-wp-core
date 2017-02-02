<?php
namespace Blaze\WP;

/**
 * Display WordPress breadcrumbs
 *
 * @copyright   Copyright (c) 2016, Kirill Latyshev
 * @author      Kirill Latyshev <kirill@yula.media>
 */
class Breadcrumbs
{

    /**
     * Initialize the class
     */
    public function __construct()
    {
        add_shortcode('blaze-bs-breadcrumbs', array('\Blaze\WP\Breadcrumbs', 'bootstrapBreadcrumbs'));
    }

    /**
     * Display a breadcrumbs for the current page
     * Usage: '[blaze-bs-breadcrumbs]'
     */
    /**
     * @param $atts not used as of now
     * @return string a breadcrumb HTML code
     */
    public static function bootstrapBreadcrumbs($atts)
    {
        //$breadcumbs_format = $atts['format'];

        // Need to assign class "active" to the last element
        global $post;
        $html = '';

        $html .= '<ol class="breadcrumb">';
        $html .= '<li><a href="' . get_option('home') . '">' . __( 'Home', 'blaze-core' ) . '</a></li>';
        if (is_single()) {
            $html .= '<li class="active">' . get_the_category_list(", ", "") . '</li>';

            if (is_single()) {
                $html .= '<li class="active">' . get_the_title() . '</li>';
            }
        } elseif (is_category()) {
            $html .= '<li class="active">' . single_cat_title('', false) . '</li>';
        } elseif (is_page() && (!is_front_page())) {
            $current_post = $post->post_parent;
            $parents = array();
            while (!empty($current_post)) {
                $parent_title = get_the_title($current_post);
                $parent_permalink = get_the_permalink($current_post);
                array_unshift($parents, "<li><a href='{$parent_permalink}'>{$parent_title}</a></li>");
                if (empty($current_post->post_parent))
                    break;
                $current_post = $current_post->post_parent;
            }
            $html .= implode($parents);

            $html .= '<li class="active">' . get_the_title() . '</li>';
        } elseif (is_tag()) {
            $html .= '<li class="active">' . __( 'Tag', 'blaze-core' ) . ': ' . single_tag_title('', false) . '</li>';
        } elseif (is_day()) {
            $html .= '<li class="active">' . __( 'Archive for', 'blaze-core' ) . ' ' . get_the_time('F jS, Y') . '</li>';
        } elseif (is_month()) {
            $html .= '<li class="active">' . __( 'Archive for', 'blaze-core' ) . ' ' . get_the_time('F, Y') . '</li>';
        } elseif (is_year()) {
            $html .= '<li class="active">' . __( 'Archive for', 'blaze-core' ) . ' ' . get_the_time('Y') . '</li>';
        } elseif (is_author()) {
            $html .= '<li class="active">' . __( 'Author Archives', 'blaze-core' ) . '</li>';
        } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) {
            $html .= '<li class="active">' . __( 'Blog Archives', 'blaze-core' ) . '</li>';
        } elseif (is_search()) {
            $html .= '<li class="active">' . __( 'Search Results', 'blaze-core' ) . '</li>';
        }
        $html .= '</ol>';
        return $html;
    }
}