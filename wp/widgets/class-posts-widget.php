<?php
namespace Blaze\WP\Widgets;
use Blaze\WP\Widget;
use Blaze\WP\Data_Item;
use Blaze\WP\Query;

/**
 * Displays several latest posts
 *
 * @copyright   Copyright (c) 2016-2017, Kirill Latyshev
 * @author      Kirill Latyshev <kirlat@yula.media>
 */
class Posts_Widget extends Widget {


    function __construct() {

        parent::__construct(
            'blaze-posts-widget',
            array(
                'name' => 'Blaze Posts Widget',
                'description' => 'A widget that displays several latest posts',
                'className' => 'blaze-posts-widget',
            ),
            array(
                'title' => new Data_Item('title', array(
                    'itemType' => Data_Item::DIT_WIDGET_FIELD,
                    'dataType' => Data_Item::DDT_STRING,
                    'label' => 'Title',
                    'description' => 'Will be hidden if empty',
                    'defaultValue' => ''
                )),
                'quantity' => new Data_Item('quantity', array(
                    'itemType' => Data_Item::DIT_WIDGET_FIELD,
                    'dataType' => Data_Item::DDT_STRING,
                    'label' => 'Quantity of posts to show',
                    'description' => 'Posts are shown from all categories, in reverse chronological order',
                    'defaultValue' => '5'
                )),
            )
        );

        add_action( 'widgets_init', function() {
            register_widget( '\Blaze\WP\Widgets\Posts_Widget' );
        });

    }


    public function widget($args, $instance) {
        $post_num = $instance['quantity'];

        // Custom query parameters
        $queryArgs = array(
            'post_type' => 'post',
            'posts_per_page' => $post_num,
            'order_by' => 'date',
            'order' => 'DESC'
        );

        $q = new Query($queryArgs);
        $recent_posts = $q->getPosts('\Blaze\WP\Post');
        usort($recent_posts, '\Blaze\BlazePage::titleAscComp');

        $widget_container_classes = $this->wDataItems['widget-container-classes']->getValue($instance);
        $widget_title_classes = $this->wDataItems['widget-title-classes']->getValue($instance);
        $widget_body_classes = $this->wDataItems['widget-body-classes']->getValue($instance);
        $content = "<div class='blaze-widget {$widget_container_classes}'>";
        if ( !empty( $instance['title'] ) ) {
            $content .= "<h4 class='blaze-widget__title {$widget_title_classes}'>" . apply_filters( 'widget_title', $instance['title'] ) . "</h4>";
        }
        $content .= "<div class='blaze-widget__body {$widget_body_classes}'>";

        foreach($recent_posts as $recent_post) {
            $content .= "<a class='blaze-widget__body-post-link' href='{$recent_post->permalink}'><h5 class='blaze-widget__body-post-title'>{$recent_post->title}</h5></a>";
        }

        $content .= "</div>";
        $content .= "</div>";
        echo $content;
    }
}